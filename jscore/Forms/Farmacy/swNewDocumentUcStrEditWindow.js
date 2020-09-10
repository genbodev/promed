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

var swDocumentUcStorageWorkRows = Ext.extend(Ext.Panel, {
	owner: null,
	onFactQuantityChange: Ext.emptyFn,
	onEndDateChange: Ext.emptyFn,
	onRowDelete: Ext.emptyFn,

	onEmptyRows: function() {
		var readOnly = (this.owner.action == 'view');

		this.add({
			xtype: 'button',
			text: 'Добавить',
			iconCls: 'add16',
			diabled: readOnly,
			handler: function() {
				this.addRow();
			}.createDelegate(this)
		});
	},

	addRow: function(values) {
		var num = ++this.num;

		if (values && values.RecordStatus_Code == 3) {
			this.deletedRowsData.add(num, values);
			return;
		}

		var readOnly = (this.owner.action == 'view');

		var addButton = new Ext.Button({
			text: 'Добавить',
			iconCls: 'add16',
			hidden: readOnly,
			handler: function() {
				this.addRow();
			}.createDelegate(this)
		});

		var deleteButton = new Ext.Button({
			text: 'Удалить',
			iconCls: 'delete16',
			hidden: readOnly,
			handler: function() {
				this.removeRow(num);
			}.createDelegate(this)
		});

		var config = {
			xtype: 'form',
			num: num,
			addButton: addButton,
			deleteButton: deleteButton,
			labelAlign: 'right',
			items: [{
				layout: 'column',
				items: [{
					xtype: 'hidden',
					name: 'DocumentUcStorageWork_id',
					value: -(num+1)
				}, {
					xtype: 'hidden',
					name: 'RecordStatus_Code',
					value: 0
				}, {
					xtype: 'hidden',
					name: 'Person_eid'
				}, {
					xtype: 'hidden',
					name: 'Post_eid'
				}, {
					layout: 'form',
					labelWidth: 35,
					items: [{
						disabled: true,
						xtype: 'swdatefield',
						name: 'DocumentUcStorageWork_insDT',
						fieldLabel: 'Дата',
						width: 90,
						value: new Date()
					}]
				}, {
					layout: 'form',
					labelWidth: 75,
					items: [{
						allowBlank: false,
						editable: true,
						disabled: readOnly,
						xtype: 'swcommonsprcombo',
						comboSubject: 'DocumentUcTypeWork',
						hiddenName: 'DocumentUcTypeWork_id',
						fieldLabel: 'Вид работ',
						width: 140,
						listWidth: 220
					}]
				}, {
					layout: 'form',
					labelWidth: 90,
					items: [{
						allowBlank: false,
						disabled: readOnly,
						xtype: 'swpersonworkcombo',
						hiddenName: 'PersonWork_eid',
						fieldLabel: 'Исполнитель',
						setLinkedFieldValues: function(event_name) {
							var form = this.getRow(num).getForm();
							var rdt = form.findField('PersonWork_eid').getSelectedRecordData();
							var person_id = null;
							var post_id = null;
							if (!Ext.isEmpty(rdt.PersonWork_id)) {
								person_id = rdt.Person_id;
								post_id = rdt.Post_id;
							}
							form.findField('Person_eid').setValue(person_id);
							form.findField('Post_eid').setValue(post_id);
						}.createDelegate(this),
						width: 200,
						listWidth: 440
					}]
				}, {
					layout: 'form',
					labelWidth: 50,
					items: [{
						allowNegative: false,
						disabled: readOnly,
						xtype: 'numberfield',
						name: 'DocumentUcStorageWork_FactQuantity',
						fieldLabel: 'Кол-во',
						listeners: {
							'change': function(field, newValue, oldValue) {
								var base_form = this.getRow(num).getForm();
								base_form.findField('DocumentUcStorageWork_endDate').setAllowBlank(Ext.isEmpty(newValue));
								this.onFactQuantityChange(this.getRow(num), field, newValue, oldValue);
							}.createDelegate(this)
						},
						width: 80
					}]
				}, {
					layout: 'form',
					labelWidth: 85,
					items: [{
						disabled: readOnly,
						xtype: 'textfield',
						name: 'DocumentUcStorageWork_Comment',
						fieldLabel: 'Примечание',
						width: 150
					}]
				}, {
					layout: 'form',
					labelWidth: 75,
					items: [{
						disabled: readOnly,
						xtype: 'datefield',
						name: 'DocumentUcStorageWork_endDate',
						fieldLabel: 'Исполнено',
						plugins: [new Ext.ux.InputTextMask('99.99.9999 99:99', false)],
						stripCharsRe: new RegExp('__.__.____ __:__'),
						format: 'd.m.Y H:i',
						triggerClass: 'x-form-clock-trigger',
						onTriggerClick: function() {
							if (this.disabled) return;
							this.setValue(new Date());
							this.fireEvent('change', this, this.getValue());
						},
						listeners: {
							'change': function(field, newValue, oldValue) {
								var base_form = this.getRow(num).getForm();
								base_form.findField('DocumentUcStorageWork_FactQuantity').setAllowBlank(Ext.isEmpty(newValue));
								this.onEndDateChange(this.getRow(num), field, newValue, oldValue);
							}.createDelegate(this)
						},
						width: 125
					}]
				}, {
					layout: 'form',
					style: 'margin-left: 5px;',
					items: [deleteButton]
				}, {
					layout: 'form',
					style: 'margin-left: 5px;',
					items: [addButton]
				}]
			}]
		};

		if (this.rows.getCount() == 0) this.removeAll();
		var row = this.add(config);

		this.rows.add(num, row);
		this.rows.each(function(row, index, count) {
			row.addButton.setVisible(!readOnly && index == count-1);
		});

		this.doLayout();

		var PersonWorkCombo = row.getForm().findField('PersonWork_eid');
		var DocumenUcTypeWorkCombo = row.getForm().findField('DocumentUcTypeWork_id');

		PersonWorkCombo.defaultBaseParams = {Org_id: getGlobalOptions().org_id};
		PersonWorkCombo.fullReset();

		DocumenUcTypeWorkCombo.setBaseFilter(function(rec) {
			switch(this.owner.DrugDocumentType_Code){
				case 6:
				case 18:
					return rec.get('DocumentUcTypeWork_Code').inlist([1,2]);
				case 10:
					return rec.get('DocumentUcTypeWork_Code').inlist([3]);
				default:
					return true;
			}
		}, this);

		row.getForm().items.each(function(field) {
			field.validate();
		});

		var comboboxes = [DocumenUcTypeWorkCombo];
		loadStores(comboboxes, function() {
			if (values) {
				if (values.DocumentUcStorageWork_id > 0) {
					values.RecordStatus_Code = 2;
				}
				row.getForm().setValues(values);
				if (!Ext.isEmpty(values.PersonWork_eid)) {
					row.getForm().findField('PersonWork_eid').setValueById(values.PersonWork_eid);
				}
				row.getForm().items.each(function(field) {
					field.fireEvent('change', field, field.getValue());
				});
			}
		});
	},

	removeRow: function(num) {
		var row = this.rows.get(num);
		if (!row) return;

		if (row.getForm().findField('RecordStatus_Code').getValue().inlist([1,2])) {
			row.getForm().findField('RecordStatus_Code').setValue(3);
			this.deletedRowsData.add(row.num, row.getForm().getValues());
		}

		this.rows.remove(row);
		this.rows.each(function(row, index, count) {
			row.addButton.setVisible(index == count-1);
		});
		if (this.rows.getCount() == 0) {
			this.onEmptyRows();
		}

		this.remove(row, true);
		this.doLayout();
		this.onRowDelete();
	},

	removeAllRows: function(reset) {
		this.rows.each(function(row) {
			this.removeRow(row.num);
		}.createDelegate(this));
		if (reset) {
			this.num = -1;
			this.rows = new Ext.util.MixedCollection(false);
			this.deletedRowsData = new Ext.util.MixedCollection(false);
		}
	},

	isValid: function() {
		var valid = true;
		this.rows.each(function(row) {
			if (!row.getForm().isValid()) {
				valid = false;
			}
		});
		return valid;
	},

	getFirstInvalidEl: function() {
		var invalidEl = null;
		this.rows.each(function(row) {
			invalidEl = row.getFirstInvalidEl();
			if (!Ext.isEmpty(invalidEl)) return false;
		});
		return invalidEl;
	},

	getRow: function(num) {
		return this.rows.get(num);
	},

	getLastRowData: function() {
		var foundRow = this.rows.itemAt(0);
		this.rows.each(function(row) {
			var foundDT = foundRow.getForm().findField('DocumentUcStorageWork_insDT').getValue();
			var currDT = row.getForm().findField('DocumentUcStorageWork_insDT').getValue();
			if (currDT > foundDT) foundRow = row;
		});
		return foundRow ? foundRow.getForm().getValues() : null;
	},

	getDataForSave: function() {
		var data = [];
		this.rows.each(function(row) {
			var values = row.getForm().getValues();
			if (values.RecordStatus_Code != 1) {
				data.push(values);
			}
		});
		this.deletedRowsData.each(function(values){
			data.push(values);
		});
		return data;
	},

	initComponent: function() {
		swDocumentUcStorageWorkRows.superclass.initComponent.apply(this, arguments);

		this.num = -1;
		this.rows = new Ext.util.MixedCollection(false);
		this.deletedRowsData = new Ext.util.MixedCollection(false);

		this.onEmptyRows();
	}
});

sw.Promed.swNewDocumentUcStrEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: langs('Медикамент'),
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

		if (!name_field.enable_blocked && sh_name != '') {
			//проверяем уникальность в пределах текущего документа
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
								sw.swMsg.alert(langs('Ошибка'), langs('Указанный номер партии уже используется.'));
							}
						} else {
							sw.swMsg.alert(langs('Ошибка'), langs('При проверке номера парти возникла ошибка.'));
						}
					},
					url: '/?c=DocumentUc&m=checkDrugShipmentName'
				});
			} else {
				sw.swMsg.alert(langs('Ошибка'), langs('Указанный номер партии уже используется в пределах текущего документа.'));
			}
		} else {
			callback();
		}
		return is_unique;
	},
	generateDrugShipmentName: function() {
		var wnd = this;
		var name_field = wnd.form.findField('DrugShipment_Name');

        //вместо генерации нового номера, теперь наименование новой партии устанавливается равным идентификатору партии (осуществляется на стороне сервера при сохранении партии)
        if (this.drugshipment_name_enabled) {
            name_field.setValue('set_name_by_id');
        }

		/*var max_num = 0;

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
		}*/
	},
    getRecountKoef: function(combo_from, combo_to) { //возращает коэфицент пересчета количества из ед. измерения из первого комбобокса, в ед. измерения из второго комбобокса
        var from_gu_data = combo_from.getSelectedRecordData();
        var to_gu_data = combo_to.getSelectedRecordData();
        var from_fas = !Ext.isEmpty(from_gu_data.GoodsPackCount_Count) ? from_gu_data.GoodsPackCount_Count : 0;
        var to_fas = !Ext.isEmpty(to_gu_data.GoodsPackCount_Count) ? to_gu_data.GoodsPackCount_Count : 0;
        var koef = null;

        if (from_fas > 0 && to_fas > 0) {
            koef = ((to_fas/from_fas).toFixed(5))*1;
        }
        return koef;
    },
    getBarCodeCount: function() { //возвращает количество активных записей о штрих-кодах для данной строки
        var saved_cnt = this.SavedBarCode_Count*1;
        var add_cnt = this.AddedBarCode_Count*1;
        var cnt = saved_cnt+add_cnt;
        return (cnt > 0 ? cnt : 0);
    },
    roundPrice: function(price) { //специфическое округление цены
        price = Math.floor(price*100)/100;
        return price;
    },
	setDefaultValues: function () { //заполнение формы значениями "по умолчанию"
        var form_values = new Object();
        Ext.apply(form_values, this.DefaultValues);

        //удаляем лишние значения
        delete form_values.StorageZone_id;

		this.form.setValues(form_values);
		this.cert_form.setValues(form_values);
		this.generateDrugShipmentName();
	},
	setDrugDocumentType: function(type_code) { //настройка внешнего вида формы в зависимости от типа документа
        var region_nick = getRegionNick();
		var show_print_button = false;
		var show_regprice_fieldset = false;
		var show_plan_fields = false;

        var cert_fieldset = this.findById('NewDocumentUcStrEditCertForm').ownerCt;
        var post_fieldset = this.findById('NewDocumentUcStrEditPostFieldset');
        var regprice_fieldset = this.findById('NewDocumentUcStrEditRegPriceFieldset');

        var record_state = 'add';
        if (this.owner) {
            var selected_record = this.owner.getGrid().getSelectionModel().getSelected();
            if (selected_record && this.action != 'add') {
                record_state =  selected_record.get('state');
            }
        }

        this.post_fields_autoset_enabled = false; //разрешение на автоматический рассчет и установку значений для полей блока "Поставить на учет"
        this.drugshipment_name_enabled = false; //разрешениет на установку/отображение наименования партии
        this.display_oid_files = false; //отображение файлов прикрпеленных к партии вместо своих, дополнительно блокируется редактирование списка файлов

        this.form_title = null; //переменная для изменения наименования формы, если пустая - то используется наименование по умолчанию

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
        this.form.findField('DocumentUcStr_EdPrice').enable_blocked = false;
		this.form.findField('DrugNds_id').enable_blocked = false;
		this.form.findField('DocumentUcStr_IsNDS').enable_blocked = true;
		this.form.findField('DocumentUcStr_Sum').enable_blocked = false;
		this.form.findField('DocumentUcStr_SumNds').enable_blocked = false;
		this.form.findField('DocumentUcStr_NdsSum').enable_blocked = false;
		this.form.findField('DocumentUcStr_Ser').enable_blocked = false;
		this.form.findField('PrepSeries_GodnDate').enable_blocked = false;
        this.form.findField('StorageZone_id').enable_blocked = false;
		this.cert_form.findField('DocumentUcStr_CertNum').enable_blocked = false;
		this.cert_form.findField('DocumentUcStr_CertOrg').enable_blocked = false;
		this.cert_form.findField('DrugLabResult_Name').enable_blocked = false;
        this.gu_b_combo.enable_blocked = false;
		this.CertDateRange.enable_blocked = false;

        //this.form.findField('GoodsPackCount_Count').allowBlank = true;
        this.form.findField('DocumentUcStr_EdCount').allowBlank = true;
        this.form.findField('DocumentUcStr_EdPrice').allowBlank = true;
        this.form.findField('PrepSeries_GodnDate').allowBlank = true;
		this.form.findField('DocumentUcStr_oid').allowBlank = true;
        this.form.findField('StorageZone_id').allowBlank = true;
		this.form.findField('DocumentUcStr_Reason').allowBlank = true;
		this.form.findField('DocumentUcStr_RegPrice').allowBlank = true;
		this.form.findField('DocumentUcStr_EdRegPrice').allowBlank = true;
		this.form.findField('DocumentUcStr_RegDate').allowBlank = true;
		this.gu_combo.allowBlank = true;
		this.post_gu_b_combo.allowBlank = true;

        this.setFieldLabel(this.sz_combo, langs('Место хранения'));
        this.setFieldLabel(this.post_sz_combo, langs('Место хранения'));
        this.setFieldLabel(this.form.findField('GoodsUnit_id'), langs('Ед. списания'));
        this.setFieldLabel(this.form.findField('DocumentUcStr_OstEdCount'), langs('Остаток (ед. спис.)'));
        this.setFieldLabel(this.form.findField('DocumentUcStr_EdCount'), langs('Кол-во (ед. спис.)'));
        this.setFieldLabel(this.form.findField('DocumentUcStr_EdPrice'), langs('Цена за ед.спис.') + ' (' + getCurrencyName() + ')');

        this.form.findField('DocumentUcStr_PlanKolvo').hideContainer();
        this.form.findField('DocumentUcStr_EdPlanKolvo').hideContainer();
        //this.form.findField('DrugUnit_Name').showContainer();
        this.form.findField('DocumentUcStr_OstCount').showContainer();
        this.form.findField('DocumentUcStr_Count').showContainer();
        this.form.findField('DocumentUcStr_Price').showContainer();
		this.form.findField('DrugNds_id').showContainer();
		this.form.findField('DocumentUcStr_IsNDS').showContainer();
		this.form.findField('DocumentUcStr_SumNds').showContainer();
		this.form.findField('DocumentUcStr_NdsSum').showContainer();
		this.form.findField('PostDrugNds_id').showContainer();
		this.form.findField('PostDocumentUcStr_IsNDS').showContainer();
		this.form.findField('PostDocumentUcStr_SumNds').showContainer();
		this.form.findField('PostDocumentUcStr_NdsSum').showContainer();

		this.form.findField('DocumentUcStr_oid').ownerCt.hide();
		this.form.findField('DrugShipment_Name').ownerCt.hide();
		this.form.findField('DocumentUcStr_Reason').ownerCt.hide();

		this.base_params = new Object();
		this.base_params.DocumentUcStr_oid = new Object();
        this.base_params.StorageZone_id = new Object();
        this.base_params.PostStorageZone_id = new Object();
        this.base_params.GoodsUnit_bid = new Object();
        this.base_params.GoodsUnit_id = new Object();
        this.base_params.PostGoodsUnit_bid = new Object();
        this.base_params.PostGoodsUnit_id = new Object();
		this.drug_combo.store.baseParams = new Object();

        this.base_params.StorageZone_id.isCountEnabled = false;
        this.base_params.DocumentUcStr_oid.PrepSeries_IsDefect = 1; //Признак брака: 1 - Нет
        this.base_params.DocumentUcStr_oid.Sort_Type = 'godn'; //Сортировка по сроку годности
        this.base_params.DocumentUcStr_oid.CheckGodnDate = 'current_date'; //Проверка годности на текущую дату
        this.base_params.GoodsUnit_bid.UserOrg_id = getGlobalOptions().org_id;
        this.base_params.GoodsUnit_bid.UserOrg_Type = getGlobalOptions().orgtype;
        this.base_params.GoodsUnit_id.UserOrg_id = getGlobalOptions().org_id;
        this.base_params.GoodsUnit_id.UserOrg_Type = getGlobalOptions().orgtype;
        this.base_params.PostStorageZone_id.isCountEnabled = false;
        this.base_params.PostGoodsUnit_bid.UserOrg_id = getGlobalOptions().org_id;
        this.base_params.PostGoodsUnit_bid.UserOrg_Type = getGlobalOptions().orgtype;
        this.base_params.PostGoodsUnit_id.UserOrg_id = getGlobalOptions().org_id;
        this.base_params.PostGoodsUnit_id.UserOrg_Type = getGlobalOptions().orgtype;

        this.drug_combo.childrenList = this.drug_combo.defaultChildrenList;

        if (this.doc_status == 1 || this.str_status == 1) {
			this.form.findField('DocumentUcStr_OstCount').showContainer();
            if (this.show_diff_gu) {
                this.form.findField('DocumentUcStr_OstEdCount').showContainer();
            } else {
                this.form.findField('DocumentUcStr_OstEdCount').hideContainer();
            }
		} else {
			this.form.findField('DocumentUcStr_OstCount').hideContainer();
			this.form.findField('DocumentUcStr_OstEdCount').hideContainer();
		}

        //установка видимости полей списания в альтернативных ед учета
        if (this.show_diff_gu) {
            this.gu_combo.ownerCt.show();
            this.post_gu_combo.ownerCt.show();
            this.gu_combo.allowBlank = false;
            this.form.findField('DocumentUcStr_EdCount').ownerCt.show();
            this.form.findField('DocumentUcStr_EdPrice').ownerCt.show();
            this.form.findField('PostDocumentUcStr_EdCount').ownerCt.show();
            this.form.findField('DocumentUcStr_EdRegPrice').ownerCt.show();
            this.base_params.DocumentUcStr_oid.isEdOstEnabled = true; //Отображение в наименовании партии остатка в е.д. списания
            this.base_params.StorageZone_id.isEdOstEnabled = true; //Отображение в наименовании партии остатка в е.д. списания
        } else {
            this.gu_combo.ownerCt.hide();
            this.post_gu_combo.ownerCt.hide();
            this.gu_combo.allowBlank = true;
            this.form.findField('DocumentUcStr_EdCount').ownerCt.hide();
            this.form.findField('DocumentUcStr_EdPrice').ownerCt.hide();
            this.form.findField('PostDocumentUcStr_EdCount').ownerCt.hide();
            this.form.findField('DocumentUcStr_EdRegPrice').ownerCt.hide();
            this.base_params.DocumentUcStr_oid.isEdOstEnabled = false;
            this.base_params.StorageZone_id.isEdOstEnabled = false;
        }

        this.setSpisFildsetBorderVisible(false);

        cert_fieldset.show();
        post_fieldset.hide();

        this.findById('nduse_MiddleBtnBarCodeView').hide();
        this.findById('nduse_BottomBtnBarCodeView').show();

		this.DefaultValues.DocumentUcStr_IsNDS = 1;
		this.DefaultValues.StorageZone_id = null;

		switch(type_code) {
			case 2: //Документ списания
                this.base_params.DocumentUcStr_oid.CheckGodnDate = null;
			case 25: //Списание медикаментов со склада на пациента. СМП
                this.display_oid_files = true;

                if (this.params.StorageZone_sid > 0) {
                    this.DefaultValues.StorageZone_id = this.params.StorageZone_sid;
                }
				this.form.findField('DocumentUcStr_Price').enable_blocked = true;
				this.form.findField('DocumentUcStr_EdPrice').enable_blocked = true;
				this.form.findField('DrugNds_id').enable_blocked = true;
				this.form.findField('DocumentUcStr_Sum').enable_blocked = true;
				this.form.findField('DocumentUcStr_SumNds').enable_blocked = true;
				this.form.findField('DocumentUcStr_NdsSum').enable_blocked = true;
				this.form.findField('DocumentUcStr_Ser').enable_blocked = true;
				this.form.findField('PrepSeries_GodnDate').enable_blocked = true;
				this.cert_form.findField('DocumentUcStr_CertNum').enable_blocked = true;
				this.cert_form.findField('DocumentUcStr_CertOrg').enable_blocked = true;
				this.cert_form.findField('DrugLabResult_Name').enable_blocked = true;
                this.gu_b_combo.enable_blocked = true;
				this.CertDateRange.enable_blocked = true;
				if (this.params.Storage_sid > 0) {
					this.drug_combo.store.baseParams.Storage_id = this.params.Storage_sid;
				} else if (this.params.Contragent_sid > 0) {
					this.drug_combo.store.baseParams.Contragent_id = this.params.Contragent_sid;
				}
				this.drug_combo.store.baseParams.DrugFinance_id = this.params.DrugFinance_id ? this.params.DrugFinance_id : null;
				this.drug_combo.store.baseParams.WhsDocumentCostItemType_id = this.params.WhsDocumentCostItemType_id ? this.params.WhsDocumentCostItemType_id : null;
				this.drug_combo.store.baseParams.DrugShipment_setDT_max = this.params.DocumentUc_didDate ? this.params.DocumentUc_didDate.format('d.m.Y') : null;
				if (this.params.Storage_sid > 0) {
					this.base_params.DocumentUcStr_oid.Storage_id = this.params.Storage_sid;
                    this.base_params.StorageZone_id.Storage_id = this.params.Storage_sid;
				} else if (this.params.Contragent_sid > 0) {
					this.base_params.DocumentUcStr_oid.Contragent_id = this.params.Contragent_sid;
                    this.base_params.StorageZone_id.Contragent_id = this.params.Contragent_sid;
				}
				this.base_params.DocumentUcStr_oid.DrugFinance_id = this.params.DrugFinance_id ? this.params.DrugFinance_id : null;
				this.base_params.DocumentUcStr_oid.WhsDocumentCostItemType_id = this.params.WhsDocumentCostItemType_id ? this.params.WhsDocumentCostItemType_id : null;
                this.base_params.DocumentUcStr_oid.DrugShipment_setDT_max = this.params.DocumentUc_didDate ? this.params.DocumentUc_didDate.format('d.m.Y') : null;
                this.base_params.StorageZone_id.isCountEnabled = true;
				this.form.findField('DocumentUcStr_Reason').allowBlank = false;
				this.form.findField('DocumentUcStr_oid').allowBlank = false;
                this.form.findField('StorageZone_id').allowBlank = false;
				this.form.findField('DocumentUcStr_Reason').ownerCt.show();
				this.form.findField('DocumentUcStr_oid').ownerCt.show();
				break;
			case 3: //Документ ввода остатков
				show_print_button = true;
                this.drugshipment_name_enabled = true;

				this.DefaultValues.DrugNds_id = 2; //10%
                if (this.params.StorageZone_tid > 0) {
                    this.DefaultValues.StorageZone_id = this.params.StorageZone_tid;
                }
                this.form.findField('DocumentUcStr_oid').enable_blocked = true;
				this.form.findField('PrepSeries_GodnDate').allowBlank = false;
				//this.drug_combo.store.baseParams.WhsDocumentUc_id = this.params.WhsDocumentUc_id ? this.params.WhsDocumentUc_id : null;
				if (this.params.Storage_tid > 0) {
					this.base_params.StorageZone_id.Storage_id = this.params.Storage_tid;
				} else if (this.params.Contragent_tid > 0) {
					this.base_params.StorageZone_id.Contragent_id = this.params.Contragent_tid;
				}
				break;
			case 6: //Приходная накладная
			case 32: //Приход в отделение
				show_print_button = true;
                show_regprice_fieldset = (type_code == 6);
                this.drugshipment_name_enabled = true;

				this.DefaultValues.DrugNds_id = 2; //10%
                if (this.params.StorageZone_tid > 0) {
                    this.DefaultValues.StorageZone_id = this.params.StorageZone_tid;
                }
                this.form.findField('DocumentUcStr_oid').enable_blocked = true;
				this.form.findField('PrepSeries_GodnDate').allowBlank = false;
				this.drug_combo.store.baseParams.WhsDocumentUc_id = this.params.WhsDocumentUc_id ? this.params.WhsDocumentUc_id : null;
				if (this.params.Storage_tid > 0) {
					this.base_params.StorageZone_id.Storage_id = this.params.Storage_tid;
				} else if (this.params.Contragent_tid > 0) {
					this.base_params.StorageZone_id.Contragent_id = this.params.Contragent_tid;
				}
				break;
			case 10: //Расходная накладная
                show_plan_fields = true;
                this.display_oid_files = true;

                if (this.params.StorageZone_sid > 0) {
                    this.DefaultValues.StorageZone_id = this.params.StorageZone_sid;
                }
                this.form.findField('DocumentUcStr_Price').enable_blocked = true;
                this.form.findField('DocumentUcStr_EdPrice').enable_blocked = true;
				this.form.findField('DrugNds_id').enable_blocked = true;
				this.form.findField('DocumentUcStr_Sum').enable_blocked = true;
				this.form.findField('DocumentUcStr_SumNds').enable_blocked = true;
				this.form.findField('DocumentUcStr_NdsSum').enable_blocked = true;
				this.form.findField('DocumentUcStr_Ser').enable_blocked = true;
				this.form.findField('PrepSeries_GodnDate').enable_blocked = true;
				this.cert_form.findField('DocumentUcStr_CertNum').enable_blocked = true;
				this.cert_form.findField('DocumentUcStr_CertOrg').enable_blocked = true;
				this.cert_form.findField('DrugLabResult_Name').enable_blocked = true;
                this.gu_b_combo.enable_blocked = true;
				this.CertDateRange.enable_blocked = true;
				if (this.params.Storage_sid > 0) {
					this.drug_combo.store.baseParams.Storage_id = this.params.Storage_sid;
				} else if (this.params.Contragent_sid > 0) {
					this.drug_combo.store.baseParams.Contragent_id = this.params.Contragent_sid;
				}
				this.drug_combo.store.baseParams.WhsDocumentUc_id = this.params.WhsDocumentUc_id ? this.params.WhsDocumentUc_id : null;
				this.drug_combo.store.baseParams.DrugFinance_id = this.params.DrugFinance_id ? this.params.DrugFinance_id : null;
				this.drug_combo.store.baseParams.WhsDocumentCostItemType_id = this.params.WhsDocumentCostItemType_id ? this.params.WhsDocumentCostItemType_id : null;
                this.drug_combo.store.baseParams.DrugShipment_setDT_max = this.params.DocumentUc_didDate ? this.params.DocumentUc_didDate.format('d.m.Y') : null;
				this.base_params.DocumentUcStr_oid.WhsDocumentUc_id = this.params.WhsDocumentUc_id ? this.params.WhsDocumentUc_id : null;
				this.base_params.DocumentUcStr_oid.DrugFinance_id = this.params.DrugFinance_id ? this.params.DrugFinance_id : null;
				this.base_params.DocumentUcStr_oid.WhsDocumentCostItemType_id = this.params.WhsDocumentCostItemType_id ? this.params.WhsDocumentCostItemType_id : null;
				this.base_params.DocumentUcStr_oid.Contragent_id = this.params.Contragent_sid;
                this.base_params.DocumentUcStr_oid.DrugShipment_setDT_max = this.params.DocumentUc_didDate ? this.params.DocumentUc_didDate.format('d.m.Y') : null;
                this.base_params.StorageZone_id.isCountEnabled = true;
				this.form.findField('DocumentUcStr_oid').allowBlank = false;
                this.form.findField('StorageZone_id').allowBlank = false;
				this.form.findField('DocumentUcStr_oid').ownerCt.show();
				if (this.params.Storage_sid > 0) {
					this.base_params.StorageZone_id.Storage_id = this.params.Storage_sid;
					this.base_params.DocumentUcStr_oid.Storage_id = this.params.Storage_sid;
				} else if (this.params.Contragent_sid > 0) {
					this.base_params.StorageZone_id.Contragent_id = this.params.Contragent_sid;
				}
				break;
			case 12: //Документ оприходования
                this.DefaultValues.DrugNds_id = 1; //Без НДС
            case 22: //Учет готовой продукции
				show_print_button = true;
                if (this.params.StorageZone_tid > 0) {
                    this.DefaultValues.StorageZone_id = this.params.StorageZone_tid;
                }
                if (type_code == 22) {
                    this.drug_combo.store.baseParams.Contragent_id = this.params.Contragent_sid;
                }
                if (this.params.Storage_tid > 0) {
					this.base_params.StorageZone_id.Storage_id = this.params.Storage_tid;
				} else if (this.params.Contragent_tid > 0) {
					this.base_params.StorageZone_id.Contragent_id = this.params.Contragent_tid;
				}
				break;
			case 15: //Накладная на внутреннее перемещение
			case 31: //Накладная на перемещение внутри склада
			case 33: //Возврат из отделения
                show_plan_fields = (type_code != 33);
                this.display_oid_files = true;

                if (this.params.StorageZone_sid > 0) {
                    this.DefaultValues.StorageZone_id = this.params.StorageZone_sid;
                }
                this.form.findField('DocumentUcStr_Price').enable_blocked = true;
                this.form.findField('DocumentUcStr_EdPrice').enable_blocked = true;
				this.form.findField('DrugNds_id').enable_blocked = true;
				this.form.findField('DocumentUcStr_Sum').enable_blocked = true;
				this.form.findField('DocumentUcStr_SumNds').enable_blocked = true;
				this.form.findField('DocumentUcStr_NdsSum').enable_blocked = true;
				this.form.findField('DocumentUcStr_Ser').enable_blocked = true;
				this.form.findField('PrepSeries_GodnDate').enable_blocked = true;
				this.cert_form.findField('DocumentUcStr_CertNum').enable_blocked = true;
				this.cert_form.findField('DocumentUcStr_CertOrg').enable_blocked = true;
				this.cert_form.findField('DrugLabResult_Name').enable_blocked = true;
                this.gu_b_combo.enable_blocked = true;
				this.CertDateRange.enable_blocked = true;
				if (this.params.Storage_sid > 0) {
					this.drug_combo.store.baseParams.Storage_id = this.params.Storage_sid;
				} else if (this.params.Contragent_sid > 0) {
					this.drug_combo.store.baseParams.Contragent_id = this.params.Contragent_sid;
				}
				//this.drug_combo.store.baseParams.WhsDocumentUc_id = this.params.WhsDocumentUc_id ? this.params.WhsDocumentUc_id : null;
				this.drug_combo.store.baseParams.DrugFinance_id = this.params.DrugFinance_id ? this.params.DrugFinance_id : null;
				this.drug_combo.store.baseParams.WhsDocumentCostItemType_id = this.params.WhsDocumentCostItemType_id ? this.params.WhsDocumentCostItemType_id : null;
                this.drug_combo.store.baseParams.DrugShipment_setDT_max = this.params.DocumentUc_didDate ? this.params.DocumentUc_didDate.format('d.m.Y') : null;
				//this.base_params.DocumentUcStr_oid.WhsDocumentUc_id = this.params.WhsDocumentUc_id ? this.params.WhsDocumentUc_id : null;
				this.base_params.DocumentUcStr_oid.DrugFinance_id = this.params.DrugFinance_id ? this.params.DrugFinance_id : null;
				this.base_params.DocumentUcStr_oid.WhsDocumentCostItemType_id = this.params.WhsDocumentCostItemType_id ? this.params.WhsDocumentCostItemType_id : null;
				this.base_params.DocumentUcStr_oid.Storage_id = this.params.Storage_sid ? this.params.Storage_sid : null;
				this.base_params.DocumentUcStr_oid.Contragent_id = this.params.Contragent_sid;
                this.base_params.DocumentUcStr_oid.PrepSeries_IsDefect = 0;
                this.base_params.DocumentUcStr_oid.DrugShipment_setDT_max = this.params.DocumentUc_didDate ? this.params.DocumentUc_didDate.format('d.m.Y') : null;
                this.base_params.DocumentUcStr_oid.CheckGodnDate = null;

                this.base_params.StorageZone_id.isCountEnabled = true;
				this.form.findField('DocumentUcStr_oid').allowBlank = false;
                this.form.findField('StorageZone_id').allowBlank = false;
				this.form.findField('DocumentUcStr_oid').ownerCt.show();
				if (this.params.Storage_sid > 0) {
					this.base_params.StorageZone_id.Storage_id = this.params.Storage_sid;
				} else if (this.params.Contragent_sid > 0) {
					this.base_params.StorageZone_id.Contragent_id = this.params.Contragent_sid;
				}
				break;
			case 17: //Возвратная накладная (расходная)
                this.gu_b_combo.enable_blocked = true;
                if (this.params.StorageZone_sid > 0) {
                    this.DefaultValues.StorageZone_id = this.params.StorageZone_sid;
                }
                if (this.params.DocumentUc_pid > 0) {
					this.drug_combo.store.baseParams.DocumentUc_id = this.params.DocumentUc_pid;
					this.base_params.DocumentUcStr_oid.DocumentUc_id = this.params.DocumentUc_pid;
				}
                this.drug_combo.store.baseParams.DrugShipment_setDT_max = this.params.DocumentUc_didDate ? this.params.DocumentUc_didDate.format('d.m.Y') : null;
                this.base_params.DocumentUcStr_oid.DrugShipment_setDT_max = this.params.DocumentUc_didDate ? this.params.DocumentUc_didDate.format('d.m.Y') : null;
                this.base_params.DocumentUcStr_oid.PrepSeries_IsDefect = 0;
                this.base_params.DocumentUcStr_oid.CheckGodnDate = null;
				this.form.findField('DocumentUcStr_oid').ownerCt.show();
				if (this.params.Storage_sid > 0) {
					this.base_params.StorageZone_id.Storage_id = this.params.Storage_sid;
				} else if (this.params.Contragent_sid > 0) {
					this.base_params.StorageZone_id.Contragent_id = this.params.Contragent_sid;
				}
				break;
			case 20: //Пополнение укладки со склада подстанции СМП
                this.display_oid_files = true;

                if (this.params.StorageZone_sid > 0) {
                    this.DefaultValues.StorageZone_id = this.params.StorageZone_sid;
                }
                this.form.findField('DocumentUcStr_Price').enable_blocked = true;
                this.form.findField('DocumentUcStr_EdPrice').enable_blocked = true;
				this.form.findField('DrugNds_id').enable_blocked = true;
				this.form.findField('DocumentUcStr_Sum').enable_blocked = true;
				this.form.findField('DocumentUcStr_SumNds').enable_blocked = true;
				this.form.findField('DocumentUcStr_NdsSum').enable_blocked = true;
				this.form.findField('DocumentUcStr_Ser').enable_blocked = true;
				this.form.findField('PrepSeries_GodnDate').enable_blocked = true;
				this.cert_form.findField('DocumentUcStr_CertNum').enable_blocked = true;
				this.cert_form.findField('DocumentUcStr_CertOrg').enable_blocked = true;
				this.cert_form.findField('DrugLabResult_Name').enable_blocked = true;
                this.gu_b_combo.enable_blocked = true;
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
                this.form.findField('StorageZone_id').allowBlank = false;
				this.form.findField('DocumentUcStr_oid').ownerCt.show();
				if (this.params.Storage_sid > 0) {
					this.base_params.StorageZone_id.Storage_id = this.params.Storage_sid;
				} else if (this.params.Contragent_sid > 0) {
					this.base_params.StorageZone_id.Contragent_id = this.params.Contragent_sid;
				}
				break;
			case 21: // Списание медикаментов со склада на пациента
                this.display_oid_files = true;

                if (this.params.StorageZone_sid > 0) {
                    this.DefaultValues.StorageZone_id = this.params.StorageZone_sid;
                }
                this.form.findField('DocumentUcStr_Price').enable_blocked = true;
                this.form.findField('DocumentUcStr_EdPrice').enable_blocked = true;
				this.form.findField('DrugNds_id').enable_blocked = true;
				this.form.findField('DocumentUcStr_Sum').enable_blocked = true;
				this.form.findField('DocumentUcStr_SumNds').enable_blocked = true;
				this.form.findField('DocumentUcStr_NdsSum').enable_blocked = true;
				this.form.findField('DocumentUcStr_Ser').enable_blocked = true;
				this.form.findField('PrepSeries_GodnDate').enable_blocked = true;
				this.cert_form.findField('DocumentUcStr_CertNum').enable_blocked = true;
				this.cert_form.findField('DocumentUcStr_CertOrg').enable_blocked = true;
				this.cert_form.findField('DrugLabResult_Name').enable_blocked = true;
                this.gu_b_combo.enable_blocked = true;
				this.CertDateRange.enable_blocked = true;
				if (this.params.Storage_sid > 0) {
					this.drug_combo.store.baseParams.Storage_id = this.params.Storage_sid;
				} else if (this.params.Contragent_sid > 0) {
					this.drug_combo.store.baseParams.Contragent_id = this.params.Contragent_sid;
				}
				this.drug_combo.store.baseParams.WhsDocumentUc_id = this.params.WhsDocumentUc_id ? this.params.WhsDocumentUc_id : null;
				this.drug_combo.store.baseParams.DrugFinance_id = this.params.DrugFinance_id ? this.params.DrugFinance_id : null;
				this.drug_combo.store.baseParams.WhsDocumentCostItemType_id = this.params.WhsDocumentCostItemType_id ? this.params.WhsDocumentCostItemType_id : null;
                this.drug_combo.store.baseParams.DrugShipment_setDT_max = this.params.DocumentUc_didDate ? this.params.DocumentUc_didDate.format('d.m.Y') : null;
				this.base_params.DocumentUcStr_oid.WhsDocumentUc_id = this.params.WhsDocumentUc_id ? this.params.WhsDocumentUc_id : null;
				this.base_params.DocumentUcStr_oid.DrugFinance_id = this.params.DrugFinance_id ? this.params.DrugFinance_id : null;
				this.base_params.DocumentUcStr_oid.WhsDocumentCostItemType_id = this.params.WhsDocumentCostItemType_id ? this.params.WhsDocumentCostItemType_id : null;
				this.base_params.DocumentUcStr_oid.Contragent_id = this.params.Contragent_sid;
				this.base_params.DocumentUcStr_oid.Storage_id = this.params.Storage_sid ? this.params.Storage_sid : null;
                this.base_params.DocumentUcStr_oid.DrugShipment_setDT_max = this.params.DocumentUc_didDate ? this.params.DocumentUc_didDate.format('d.m.Y') : null;
                this.base_params.StorageZone_id.isCountEnabled = true;
				this.form.findField('DocumentUcStr_oid').allowBlank = false;
                this.form.findField('StorageZone_id').allowBlank = false;
				this.form.findField('DocumentUcStr_oid').ownerCt.show();
				if (this.params.Storage_sid > 0) {
					this.base_params.StorageZone_id.Storage_id = this.params.Storage_sid;
				} else if (this.params.Contragent_sid > 0) {
					this.base_params.StorageZone_id.Contragent_id = this.params.Contragent_sid;
				}
				break;
            case 23: //Списание в производство
                this.display_oid_files = true;

                if (this.params.StorageZone_sid > 0) {
                    this.DefaultValues.StorageZone_id = this.params.StorageZone_sid;
                }
                this.form.findField('DocumentUcStr_Price').enable_blocked = true;
                this.form.findField('DrugNds_id').enable_blocked = true;
                this.form.findField('DocumentUcStr_Sum').enable_blocked = true;
                this.form.findField('DocumentUcStr_SumNds').enable_blocked = true;
                this.form.findField('DocumentUcStr_NdsSum').enable_blocked = true;
                this.form.findField('DocumentUcStr_Ser').enable_blocked = true;
                this.form.findField('PrepSeries_GodnDate').enable_blocked = true;
                this.cert_form.findField('DocumentUcStr_CertNum').enable_blocked = true;
                this.cert_form.findField('DocumentUcStr_CertOrg').enable_blocked = true;
                this.cert_form.findField('DrugLabResult_Name').enable_blocked = true;
                this.gu_b_combo.enable_blocked = true;
                this.CertDateRange.enable_blocked = true;
                if (this.params.Storage_sid > 0) {
                    this.drug_combo.store.baseParams.Storage_id = this.params.Storage_sid;
                } else if (this.params.Contragent_sid > 0) {
                    this.drug_combo.store.baseParams.Contragent_id = this.params.Contragent_sid;
                }
                this.drug_combo.store.baseParams.DrugFinance_id = this.params.DrugFinance_id ? this.params.DrugFinance_id : null;
                this.drug_combo.store.baseParams.WhsDocumentCostItemType_id = this.params.WhsDocumentCostItemType_id ? this.params.WhsDocumentCostItemType_id : null;
                this.drug_combo.store.baseParams.DrugShipment_setDT_max = this.params.DocumentUc_didDate ? this.params.DocumentUc_didDate.format('d.m.Y') : null;
                if (this.params.Storage_sid > 0) {
                    this.base_params.DocumentUcStr_oid.Storage_id = this.params.Storage_sid;
                } else if (this.params.Contragent_sid > 0) {
                    this.base_params.DocumentUcStr_oid.Contragent_id = this.params.Contragent_sid;
                }
                this.base_params.DocumentUcStr_oid.DrugFinance_id = this.params.DrugFinance_id ? this.params.DrugFinance_id : null;
                this.base_params.DocumentUcStr_oid.WhsDocumentCostItemType_id = this.params.WhsDocumentCostItemType_id ? this.params.WhsDocumentCostItemType_id : null;
                this.base_params.DocumentUcStr_oid.DrugShipment_setDT_max = this.params.DocumentUc_didDate ? this.params.DocumentUc_didDate.format('d.m.Y') : null;
                this.base_params.DocumentUcStr_oid.Sort_Type = 'defect_godn'; //Сортировка сначала по признаку брака, затем по сроку годности
                this.base_params.StorageZone_id.isCountEnabled = true;
                this.form.findField('DocumentUcStr_oid').allowBlank = false;
                this.form.findField('StorageZone_id').allowBlank = false;
                this.form.findField('DocumentUcStr_oid').ownerCt.show();
                if (this.params.Storage_sid > 0) {
					this.base_params.StorageZone_id.Storage_id = this.params.Storage_sid;
				} else if (this.params.Contragent_sid > 0) {
					this.base_params.StorageZone_id.Contragent_id = this.params.Contragent_sid;
				}
				break;
            case 34: //Разукомплектация: постановка на учет
                this.post_fields_autoset_enabled = true;
                this.form_title = "Медикамент для разукомплектации";

                if (this.params.StorageZone_sid > 0) {
                    this.DefaultValues.StorageZone_id = this.params.StorageZone_sid;
                }
                this.form.findField('DocumentUcStr_Price').enable_blocked = true;
                this.form.findField('DocumentUcStr_EdPrice').enable_blocked = true;
                this.form.findField('DrugNds_id').enable_blocked = true;
                this.form.findField('DocumentUcStr_Sum').enable_blocked = true;
                this.form.findField('DocumentUcStr_SumNds').enable_blocked = true;
                this.form.findField('DocumentUcStr_NdsSum').enable_blocked = true;
                this.form.findField('DocumentUcStr_Ser').enable_blocked = true;
                this.form.findField('PrepSeries_GodnDate').enable_blocked = true;

                this.cert_form.findField('DocumentUcStr_CertNum').enable_blocked = true;
                this.cert_form.findField('DocumentUcStr_CertOrg').enable_blocked = true;
                this.cert_form.findField('DrugLabResult_Name').enable_blocked = true;

                this.gu_b_combo.enable_blocked = true;
                this.CertDateRange.enable_blocked = true;

                this.setFieldLabel(this.sz_combo, langs('С места хранения'));
                this.setFieldLabel(this.post_sz_combo, langs('На место хранения'));

                if (this.params.Storage_sid > 0) {
                    this.drug_combo.store.baseParams.Storage_id = this.params.Storage_sid;
                } else if (this.params.Contragent_sid > 0) {
                    this.drug_combo.store.baseParams.Contragent_id = this.params.Contragent_sid;
                }
                this.drug_combo.store.baseParams.DrugFinance_id = this.params.DrugFinance_id ? this.params.DrugFinance_id : null;
                this.drug_combo.store.baseParams.WhsDocumentCostItemType_id = this.params.WhsDocumentCostItemType_id ? this.params.WhsDocumentCostItemType_id : null;
                this.drug_combo.store.baseParams.DrugShipment_setDT_max = this.params.DocumentUc_didDate ? this.params.DocumentUc_didDate.format('d.m.Y') : null;

                this.base_params.DocumentUcStr_oid.DrugFinance_id = this.params.DrugFinance_id ? this.params.DrugFinance_id : null;
                this.base_params.DocumentUcStr_oid.WhsDocumentCostItemType_id = this.params.WhsDocumentCostItemType_id ? this.params.WhsDocumentCostItemType_id : null;
                this.base_params.DocumentUcStr_oid.Storage_id = this.params.Storage_sid ? this.params.Storage_sid : null;
                this.base_params.DocumentUcStr_oid.Contragent_id = this.params.Contragent_sid;
                this.base_params.DocumentUcStr_oid.PrepSeries_IsDefect = 0;
                this.base_params.DocumentUcStr_oid.DrugShipment_setDT_max = this.params.DocumentUc_didDate ? this.params.DocumentUc_didDate.format('d.m.Y') : null;

                this.base_params.StorageZone_id.isCountEnabled = true;
                this.base_params.PostStorageZone_id.isCountEnabled = true;

                this.post_gu_b_combo.allowBlank = false;
                this.form.findField('DocumentUcStr_oid').allowBlank = false;
                this.form.findField('StorageZone_id').allowBlank = false;
                this.form.findField('DocumentUcStr_oid').ownerCt.show();
                if (this.params.Storage_sid > 0) {
                    this.base_params.StorageZone_id.Storage_id = this.params.Storage_sid;
                    this.base_params.PostStorageZone_id.Storage_id = this.params.Storage_sid;
                } else if (this.params.Contragent_sid > 0) {
                    this.base_params.StorageZone_id.Contragent_id = this.params.Contragent_sid;
                    this.base_params.PostStorageZone_id.Contragent_id = this.params.Contragent_sid;
                }

                this.drug_combo.childrenList = ['DocumentUcStr_oid', 'StorageZone_id', 'GoodsUnit_bid', 'GoodsUnit_id', 'PostGoodsUnit_bid', 'PostGoodsUnit_id'];

                this.setSpisFildsetBorderVisible(true);

                cert_fieldset.hide();
                post_fieldset.show();

                this.findById('nduse_MiddleBtnBarCodeView').show();
                this.findById('nduse_BottomBtnBarCodeView').hide();
                break;
		}

		if (this.isAptMu) {
			this.DefaultValues.DrugNds_id = 1; //Без НДС
			this.form.findField('DrugNds_id').hideContainer();
			this.form.findField('DocumentUcStr_IsNDS').hideContainer();
			this.form.findField('DocumentUcStr_SumNds').hideContainer();
			this.form.findField('DocumentUcStr_NdsSum').hideContainer();
			this.form.findField('PostDrugNds_id').hideContainer();
			this.form.findField('PostDocumentUcStr_IsNDS').hideContainer();
			this.form.findField('PostDocumentUcStr_SumNds').hideContainer();
			this.form.findField('PostDocumentUcStr_NdsSum').hideContainer();
		}

        //некоторые региональные настройки
        if (region_nick == 'krym' && show_plan_fields) {
            this.form.findField('DocumentUcStr_PlanKolvo').showContainer();
            if (this.show_diff_gu) {
                this.form.findField('DocumentUcStr_EdPlanKolvo').showContainer();
            }
        }
        if (region_nick == 'kz') {
            show_regprice_fieldset = false;
            this.setFieldLabel(this.form.findField('DocumentUcStr_EdPrice'), langs('Цена ед.спис.') + ' (' + getCurrencyName() + ')');
        }


        //установка блокировки поля место хранения, если для него определено значение по умолчанию
        if (!Ext.isEmpty(this.DefaultValues.StorageZone_id)) {
            this.drug_combo.store.baseParams.StorageZone_id = this.DefaultValues.StorageZone_id;
            this.base_params.DocumentUcStr_oid.StorageZone_id = this.DefaultValues.StorageZone_id;
            this.form.findField('StorageZone_id').enable_blocked = true;
        }

		//установка параметров для комбобоксов
		this.form.findField('DocumentUcStr_oid').getStore().baseParams = this.base_params.DocumentUcStr_oid;
		this.form.findField('StorageZone_id').getStore().removeAll();
		this.form.findField('StorageZone_id').getStore().baseParams = this.base_params.StorageZone_id;
		this.form.findField('PostStorageZone_id').getStore().removeAll();
		this.form.findField('PostStorageZone_id').getStore().baseParams = this.base_params.PostStorageZone_id;
		this.form.findField('GoodsUnit_bid').getStore().baseParams = this.base_params.GoodsUnit_bid;
		this.form.findField('GoodsUnit_id').getStore().baseParams = this.base_params.GoodsUnit_id;
		this.form.findField('PostGoodsUnit_bid').getStore().baseParams = this.base_params.PostGoodsUnit_bid;
		this.form.findField('PostGoodsUnit_id').getStore().baseParams = this.base_params.PostGoodsUnit_id;

		//установка видимости некоторых компонентов
        if (show_regprice_fieldset) {
            /*this.form.findField('DocumentUcStr_RegPrice').allowBlank = false;
            this.form.findField('DocumentUcStr_EdRegPrice').allowBlank = !this.show_diff_gu;
            this.form.findField('DocumentUcStr_RegDate').allowBlank = false;*/
            regprice_fieldset.show();
        } else {
            /*this.form.findField('DocumentUcStr_RegPrice').allowBlank = true;
            this.form.findField('DocumentUcStr_EdRegPrice').allowBlank = true;
            this.form.findField('DocumentUcStr_RegDate').allowBlank = true;*/
            regprice_fieldset.hide();
        }

        if (this.drugshipment_name_enabled && record_state != 'add') {
            this.form.findField('DrugShipment_Name').ownerCt.show();
        } else {
            this.form.findField('DrugShipment_Name').ownerCt.hide();
        }

		if (show_print_button && record_state != 'add') {
			this.buttons[1].show();
		} else {
			this.buttons[1].hide();
		}
	},
    setOstCount: function() { // расчет значения для поля "Остаток (ед. уч.)"
        var ost = 0;
        var oid_ost = 0;
        var sz_ost = 0;
        var res_ost = 0;
        var oid_data = this.oid_combo.getSelectedRecordData();
        var sz_data = this.sz_combo.getSelectedRecordData();

        if (!Ext.isEmpty(oid_data.DocumentUcStr_OstCount)) {
            oid_ost = oid_data.DocumentUcStr_OstCount*1;
        }

        if (!Ext.isEmpty(sz_data.DrugStorageZone_Count)) {
            sz_ost = sz_data.DrugStorageZone_Count*1;
        }

        if (!Ext.isEmpty(this.params.DocumentUcStr_oid_saved) && this.params.DocumentUcStr_oid_saved == this.oid_combo.getValue()) {
            res_ost += (this.params.DocumentUcStr_Count_saved*1);
        }

        ost = oid_ost + res_ost;
        if (sz_data.StorageZone_id >= 0 && ost > sz_ost) {
            ost = sz_ost;
        }

        this.form.findField('DocumentUcStr_OstCount').setValue(ost);
        this.setOstEdCount();
    },
    setOstEdCount: function() {
        var koef = this.getRecountKoef(this.gu_b_combo, this.gu_combo);
        var ost_cnt = this.form.findField('DocumentUcStr_OstCount').getValue() > 0 ? this.form.findField('DocumentUcStr_OstCount').getValue()*1 : 0;
        var ost_ed_cnt = null;

        if (ost_cnt > 0 && koef > 0) {
            ost_ed_cnt = ost_cnt*koef;
        }

        this.form.findField('DocumentUcStr_OstEdCount').setValue(ost_ed_cnt);
    },
	setPlanCount: function() {
		var cnt = this.form.findField('DocumentUcStr_Count').getValue() > 0 ? this.form.findField('DocumentUcStr_Count').getValue()*1 : 0;

		var workIsDone = false;
		this.StorageWorkRows.rows.each(function(row) {
			var DocumentUcTypeWork_Code = row.getForm().findField('DocumentUcTypeWork_id').getFieldValue('DocumentUcTypeWork_Code');
			var DocumentUcStorageWork_endDate = row.getForm().findField('DocumentUcStorageWork_endDate').getValue();
			if (DocumentUcTypeWork_Code == 3 && !Ext.isEmpty(DocumentUcStorageWork_endDate)) {
				workIsDone = true;
			}
		});

		if (!workIsDone) {
			this.form.findField('DocumentUcStr_PlanKolvo').setValue(cnt);
		}
	},
    setEdPlanCount: function() {
        var koef = this.getRecountKoef(this.gu_b_combo, this.gu_combo);
        var cnt = this.form.findField('DocumentUcStr_PlanKolvo').getValue() > 0 ? this.form.findField('DocumentUcStr_PlanKolvo').getValue()*1 : 0;
        var ed_cnt = null;

        if (cnt > 0 && koef > 0) {
            ed_cnt = cnt*koef;
        }

        this.form.findField('DocumentUcStr_EdPlanKolvo').setValue(ed_cnt);
    },
	setCount: function() { // пересчет полей с количеством
        var koef = this.getRecountKoef(this.gu_combo, this.gu_b_combo);
		var ed_cnt = this.form.findField('DocumentUcStr_EdCount').getValue() > 0 ? this.form.findField('DocumentUcStr_EdCount').getValue()*1 : 0;
		var cnt = null;

		if (koef > 0 && ed_cnt > 0) {
    		cnt = Math.round(ed_cnt*koef*10000)/10000;
		}

		this.form.findField('DocumentUcStr_Count').setValue(cnt);
	},
	setEdCount: function() { // пересчет полей с количеством доз
		var ost_cnt = this.form.findField('DocumentUcStr_OstCount').getValue() > 0 ? this.form.findField('DocumentUcStr_OstCount').getValue()*1 : 0;
		var cnt = this.form.findField('DocumentUcStr_Count').getValue() > 0 ? this.form.findField('DocumentUcStr_Count').getValue()*1 : 0;

		var ost_ed_cnt = null;
		var ed_cnt = null;
        var koef = this.getRecountKoef(this.gu_b_combo, this.gu_combo);

		if (koef > 0) {
			if (ost_cnt > 0) {
				ost_ed_cnt = Math.round(ost_cnt*koef*10000)/10000;
			}
			if (cnt > 0) {
				ed_cnt = Math.round(cnt*koef*10000)/10000;
			}
		}

		this.form.findField('DocumentUcStr_OstEdCount').setValue(ost_ed_cnt);
		this.form.findField('DocumentUcStr_EdCount').setValue(ed_cnt);
	},
	setPostEdCount: function() { // пересчет полей с количеством доз
        var koef = this.getRecountKoef(this.gu_b_combo, this.post_gu_combo);
		var cnt = this.form.findField('DocumentUcStr_Count').getValue() > 0 ? this.form.findField('DocumentUcStr_Count').getValue()*1 : 0;
		var ed_cnt = null;

		if (koef > 0 && cnt > 0) {
            ed_cnt = Math.round(cnt*koef*10000)/10000;
        }

		this.form.findField('PostDocumentUcStr_EdCount').setValue(ed_cnt);
	},
    setPrice: function(price_type, source_field, recount_type) { //пересчет цены (универсальная функция)
        var price_recount_type = 1; //метод пересчета цены 1 - цена * кф; 2 - цена / кф
        var koef = this.getRecountKoef(this.gu_b_combo, this.gu_combo);
        var source_price_field = null; //поле на основе которого расчитывается цена
        var result_price_field = 'DocumentUcStr_'+price_type;
        var source_price = 0;
        var result_price = 0;

        if (!Ext.isEmpty(source_field) && !Ext.isEmpty(recount_type)) {
            source_price_field = source_field;
            price_recount_type = recount_type;
        } else {
            switch (price_type) {
                case 'Price':
                    source_price_field = 'DocumentUcStr_EdPrice';
                    price_recount_type = 1;
                    break;
                case 'EdPrice':
                    source_price_field = 'DocumentUcStr_Price';
                    price_recount_type = 2;
                    break;
                case 'RegPrice':
                    source_price_field = 'DocumentUcStr_EdRegPrice';
                    price_recount_type = 1;
                    break;
                case 'EdRegPrice':
                    source_price_field = 'DocumentUcStr_RegPrice';
                    price_recount_type = 2;
                    break;
            }
        }

        source_price = this.form.findField(source_price_field).getValue() > 0 ? this.form.findField(source_price_field).getValue()*1 : 0;

        if (source_price > 0 && koef > 0) {
            result_price = price_recount_type == 1 ? (source_price*koef).toFixed(2) : this.roundPrice(source_price/koef).toFixed(2);
        }

        this.form.findField(result_price_field).setValue(result_price > 0 ? result_price : null);
    },
	setPostRecountKoef: function() { // пересчет цены за ед списания
        if (this.post_fields_autoset_enabled) {
            var koef = this.getRecountKoef(this.gu_b_combo, this.post_gu_b_combo);
            this.form.findField('PostRecount_Koef').setValue(koef);
        }
	},
    setPostFields: function() {
        if (this.post_fields_autoset_enabled) {
            var post_values = new Object();
            var koef = this.form.findField('PostRecount_Koef').getValue();
            var spis_count = this.form.findField('DocumentUcStr_Count').getValue();
            var spis_price = this.form.findField('DocumentUcStr_Price').getValue();

            var is_nds = (this.form.findField('DocumentUcStr_IsNDS').getValue() > 0);
            var nds_id = this.form.findField('DrugNds_id').getValue();
            var nds_koef = 1;

            if (nds_id > 0) {
                var index = this.form.findField('DrugNds_id').getStore().findBy(function(rec) { return rec.get('DrugNds_id') == nds_id; });
                if (index > -1) {
                    var record = this.form.findField('DrugNds_id').getStore().getAt(index);
                    var nds = record.get('DrugNds_Code')*1;
                    nds_koef = (100.0+nds)/100.0;
                }
            }

            var post_count = spis_count > 0 && koef > 0 ? Math.round(spis_count*koef*10000)/10000 : 0;
            var post_price = spis_price > 0 && koef > 0 ? this.roundPrice(spis_price/koef).toFixed(2) : 0;
            var post_sum = post_count > 0 && post_price > 0 ? (post_count*post_price).toFixed(2) : 0;
            var post_nds_sum = is_nds ? post_sum : (post_sum * nds_koef).toFixed(2);
            var post_sum_nds = is_nds && nds_koef > 0 ? (post_sum - (post_sum/nds_koef)).toFixed(2) : (post_nds_sum - post_sum).toFixed(2);

            post_values.PostDocumentUcStr_Count = post_count;
            post_values.PostDocumentUcStr_Price = post_price;
            post_values.PostDocumentUcStr_Sum = post_sum;
            post_values.PostDrugNds_id = nds_id;
            post_values.PostDocumentUcStr_IsNDS = is_nds ? 1 : 0;
            post_values.PostDocumentUcStr_NdsSum = post_nds_sum;
            post_values.PostDocumentUcStr_SumNds = post_sum_nds;


            this.form.setValues(post_values);
        }
    },
	setSumFields: function() { //пересчет полей с суммами
		var is_nds = (this.form.findField('DocumentUcStr_IsNDS').getValue() > 0);
		var cnt = this.form.findField('DocumentUcStr_Count').getValue() > 0 ? this.form.findField('DocumentUcStr_Count').getValue()*1 : 0;
		var ed_cnt = this.form.findField('DocumentUcStr_EdCount').getValue() > 0 ? this.form.findField('DocumentUcStr_EdCount').getValue()*1 : 0;
		var sum = 0;
		var sum_nds = 0;
		var nds_sum = 0;
		var nds_id = this.form.findField('DrugNds_id').getValue() > 0 ? this.form.findField('DrugNds_id').getValue()*1 : 0;
		var nds_koef = 1;
		var price = this.form.findField('DocumentUcStr_Price').getValue() > 0 ? this.form.findField('DocumentUcStr_Price').getValue()*1 : 0;
		var ed_price = this.form.findField('DocumentUcStr_EdPrice').getValue() > 0 ? this.form.findField('DocumentUcStr_EdPrice').getValue()*1 : 0;

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
					callback(callback_params);
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
					callback(data);
				},
				url:'/?c=DocumentUc&m=getPrepSeriesByDrugAndSeries'
			});
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
	setDisabled: function(disable) {
		var wnd = this;

		var field_arr = [
			'DrugNomen_Code',
			'Drug_id',
			'DrugShipment_Name',
			'GoodsUnit_bid',
			'GoodsUnit_id',
			'PostGoodsUnit_bid',
			'PostGoodsUnit_id',
			'DocumentUcStr_oid',
			'DocumentUcStr_PlanKolvo',
			'DocumentUcStr_EdPlanKolvo',
			'DocumentUcStr_Count',
			'DocumentUcStr_EdCount',
			'DocumentUcStr_Price',
			'DocumentUcStr_EdPrice',
			'DrugNds_id',
			'DocumentUcStr_IsNDS',
			'DocumentUcStr_Sum',
			'DocumentUcStr_SumNds',
			'DocumentUcStr_NdsSum',
			'DocumentUcStr_Ser',
			'PrepSeries_GodnDate',
			'DocumentUcStr_Reason',
			'StorageZone_id',
			'PostStorageZone_id',
            'DocumentUcStr_RegPrice',
            'DocumentUcStr_EdRegPrice',
            'DocumentUcStr_RegDate'
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

        this.setBarCodeViewBtnDisabled();

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
            if (wnd.display_oid_files) {
                wnd.FileUploadPanel.disable();
            } else {
                wnd.FileUploadPanel.enable();
            }
			wnd.FileUploadPanel.checkLimitCountCombo();
		}
	},
    setBarCodeViewBtnDisabled: function () { //функция определяет доступность кнопки для просмотра списка штрих-кодов
        var disable = false;
        var gu_b_data = this.gu_b_combo.getSelectedRecordData();

        if (!Ext.isEmpty(gu_b_data.GoodsUnit_Name) && gu_b_data.GoodsUnit_Name != 'упаковка' && this.getBarCodeCount() == 0) {
            disable = true;
        }

        this.findById('nduse_MiddleBtnBarCodeView').setDisabled(disable);
        this.findById('nduse_BottomBtnBarCodeView').setDisabled(disable);
    },
    setSpisFildsetBorderVisible: function(is_visible) { //вспомогательная функция которая скрывает или показывает рамку филдсета "Списать с учета" (используется в документах разукомплектации - тип 34)
        var spis_fieldset = this.findById('NewDocumentUcStrEditSpisFieldset');

        if (is_visible) {
            spis_fieldset.setTitle(spis_fieldset.originalTitle);
            spis_fieldset.removeClass("x-fieldset-noborder");
            spis_fieldset.getEl().setStyle("margin", "0px 0px 10px 1px");
        } else {
            spis_fieldset.setTitle(null);
            spis_fieldset.addClass("x-fieldset-noborder");
            spis_fieldset.getEl().setStyle("margin", "-5px 0px -5px 2px");
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
    checkCount: function() { // проверка количества, количество ен должно превышать остаток
        var result = true

        if (this.oid_combo.getValue() > 0) { // проверка количества актуальна только в случаях когда указана партия
            var cnt = this.form.findField('DocumentUcStr_Count').getValue() > 0 ? this.form.findField('DocumentUcStr_Count').getValue()*1 : 0;
            var ost = this.form.findField('DocumentUcStr_OstCount').getValue() > 0 ? this.form.findField('DocumentUcStr_OstCount').getValue()*1 : 0;

            if (cnt > ost) {
                result = false;
            }
        }

        return result;
    },
    checkBarCodeCount: function() { // проверка количества записей в таблице штрих-кодов (актуально только если едю учета не соответствует значнию "упаковка"
        var result = false;
        var gu_b_data = this.gu_b_combo.getSelectedRecordData();

        if (!Ext.isEmpty(gu_b_data.GoodsUnit_Name) && gu_b_data.GoodsUnit_Name == 'упаковка') { //если ед. учета - упаковка, то количество штрих-кодов нам не важно
            result = true;
        } else if (this.getBarCodeCount() == 0) {
            result = true;
        }

        return result;
    },
	getLastExecutedFactQuantity: function() {
		var lastExecuted = null;
		this.StorageWorkRows.rows.each(function(row) {
			var lastEndDate = lastExecuted?lastExecuted.getForm().findField('DocumentUcStorageWork_endDate').getValue():null;
			var currEndDate = row.getForm().findField('DocumentUcStorageWork_endDate').getValue();
			if (!Ext.isEmpty(currEndDate) && (Ext.isEmpty(lastEndDate) || lastEndDate < currEndDate)) {
				lastExecuted = row;
			}
		});
		var FactQuantity = null;
		if (lastExecuted) {
			FactQuantity = lastExecuted.getForm().findField('DocumentUcStorageWork_FactQuantity').getValue();
		}
		return FactQuantity;
	},
	refreshCountByExecutedStorageWork: function() {
		if (this.DrugDocumentType_Code.inlist([10, 15, 33])) {
			var countField = this.form.findField('DocumentUcStr_Count');
			var count = this.getLastExecutedFactQuantity();
			if (!Ext.isEmpty(count)) {
				countField.setValue(count);
				countField.fireEvent('change', countField, count);
			}
		}
	},
	getDataForMerge: function(data) {
		var wnd = this;
		var merge_data = new Object();

		if (this.owner && this.owner.getGrid()) {
			this.owner.getGrid().getStore().each(function(record) {
				if (
					record.get('DocumentUcStr_id') != wnd.params.DocumentUcStr_id &&
					record.get('Drug_id') == data.Drug_id&&
					((record.get('DocumentUcStr_oid') <= 0 && data.DocumentUcStr_oid <= 0) || record.get('DocumentUcStr_oid') == data.DocumentUcStr_oid) &&
                    (record.get('StorageZone_id') == data.StorageZone_id || (Ext.isEmpty(record.get('StorageZone_id')) && Ext.isEmpty(data.StorageZone_id))) &&
					record.get('DocumentUcStr_Ser') == data.DocumentUcStr_Ser &&
					((record.get('DocumentUcStr_CertNum') <= 0 && data.DocumentUcStr_CertNum <= 0) || record.get('DocumentUcStr_CertNum') == data.DocumentUcStr_CertNum) &&
					record.get('DrugNds_id') == data.DrugNds_id &&
					record.get('DocumentUcStr_IsNDS') == data.DocumentUcStr_IsNDS &&
					record.get('DocumentUcStr_Price') == data.DocumentUcStr_Price &&
                    record.get('GoodsUnit_bid') == data.GoodsUnit_bid  &&
					record.get('GoodsUnit_id') == data.GoodsUnit_id
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

        if (!this.checkCount()) {
            sw.swMsg.alert(langs('Ошибка'), langs('Указано количество, превышающее остаток'));
            return false;
        }

        if (!this.checkBarCodeCount()) {
            sw.swMsg.alert(langs('Ошибка'), langs('Сохранение невозможно. Необходимо очистить список штрих-кодов или выбрать в качестве единицы учета упаковку.'));
            return false;
        }

		if (this.checkGodnDate() || confirm(langs('Внимание! Указан срок годности меньше текущей даты. Продолжить сохранение?'))) {
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
			if ( !this.StorageWorkRows.isValid() ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						wnd.StorageWorkRows.getFirstInvalidEl().focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			this.refreshCountByExecutedStorageWork();

			var index = null;
			var record = null;
			var data = wnd.form.getValues();
			var cert_data = wnd.cert_form.getValues();

			//data.DocumentUcStr_OstCount = wnd.form.findField('DocumentUcStr_OstCount').getValue();
			//data.DocumentUcStr_OstEdCount = wnd.form.findField('DocumentUcStr_OstEdCount').getValue();
			data.Person_id = this.form.findField('Person_id').getValue();
			data.DrugShipment_Name = this.form.findField('DrugShipment_Name').getValue();
			data.DocumentUcStr_oid = this.form.findField('DocumentUcStr_oid').getValue();
			data.StorageZone_id = this.form.findField('StorageZone_id').getValue();
			data.DocumentUcStr_Price = this.form.findField('DocumentUcStr_Price').getValue();
			data.GoodsUnit_bid = this.form.findField('GoodsUnit_bid').getValue();
			data.GoodsUnit_id = this.form.findField('GoodsUnit_id').getValue();
			//data.GoodsPackCount_Count = this.form.findField('GoodsPackCount_Count').getValue();
			data.DocumentUcStr_Sum = this.form.findField('DocumentUcStr_Sum').getValue();
			data.DocumentUcStr_SumNds = this.form.findField('DocumentUcStr_SumNds').getValue();
			data.DocumentUcStr_NdsSum = this.form.findField('DocumentUcStr_NdsSum').getValue();
			data.DrugNds_id = this.form.findField('DrugNds_id').getValue();
			data.DocumentUcStr_IsNDS = this.form.findField('DocumentUcStr_IsNDS').getValue() ? 1 : 0;
			data.DocumentUcStr_Ser = this.form.findField('DocumentUcStr_Ser').getValue();
			data.DocumentUcStr_Reason = this.form.findField('DocumentUcStr_Reason').getValue();
			data.PrepSeries_GodnDate = this.form.findField('PrepSeries_GodnDate').getValue();
			data.DocumentUcStr_RegDate = this.form.findField('DocumentUcStr_RegDate').getValue();
			data.DocumentUcStr_RegPrice = this.form.findField('DocumentUcStr_RegPrice').getValue();

            data.PostStorageZone_id = this.form.findField('PostStorageZone_id').getValue();
            data.PostGoodsUnit_bid = this.form.findField('PostGoodsUnit_bid').getValue();
            data.PostGoodsUnit_id = this.form.findField('PostGoodsUnit_id').getValue();
            data.PostDocumentUcStr_Count = this.form.findField('PostDocumentUcStr_Count').getValue();
            data.PostDocumentUcStr_EdCount = this.form.findField('PostDocumentUcStr_EdCount').getValue();
            data.PostDocumentUcStr_Price = this.form.findField('PostDocumentUcStr_Price').getValue();
            data.PostDocumentUcStr_Sum = this.form.findField('PostDocumentUcStr_Sum').getValue();
            data.PostDrugNds_id = this.form.findField('PostDrugNds_id').getValue();
            data.PostDocumentUcStr_IsNDS = this.form.findField('PostDocumentUcStr_IsNDS').getValue();
            data.PostDocumentUcStr_NdsSum = this.form.findField('PostDocumentUcStr_NdsSum').getValue();
            data.PostDocumentUcStr_SumNds = this.form.findField('PostDocumentUcStr_SumNds').getValue();

            if (!wnd.show_diff_gu) {
                data.GoodsUnit_id = data.GoodsUnit_bid;
                data.DocumentUcStr_EdCount = data.DocumentUcStr_Count;
                data.PostGoodsUnit_id = data.PostGoodsUnit_bid;
                data.PostDocumentUcStr_EdCount = data.PostDocumentUcStr_Count;
            }

			data.DocumentUcStr_CertNum = this.cert_form.findField('DocumentUcStr_CertNum').getValue();
			data.DocumentUcStr_CertOrg = this.cert_form.findField('DocumentUcStr_CertOrg').getValue();
			data.DrugLabResult_Name = this.cert_form.findField('DrugLabResult_Name').getValue();

			data.DrugDocumentStatus_id = this.params.DrugDocumentStatus_id;
			data.DrugDocumentStatus_Code = this.params.DrugDocumentStatus_Code;
			data.DrugDocumentStatus_Name = this.params.DrugDocumentStatus_Name;

            if (!wnd.display_oid_files) {
                data.FileData = wnd.FileUploadPanel.getDataCopy();
                data.FileChangedData = wnd.FileUploadPanel.getChangedData();
            }
			data.BarCode_Count = wnd.getBarCodeCount();
			data.BarCodeChangedData = wnd.BarCodeChangedData;
			data.Drug_Name = null;
			data.Drug_isPKU = null;
            data.StorageZone_Name = null;
			data.GoodsUnit_bName = null;
			data.GoodsUnit_Name = null;
            data.PostGoodsUnit_bName = null;
			data.DrugNds_Code = null;
			data.DocumentUcStr_oName = null;
			data.DocumentUcStr_CertDate = Ext.util.Format.date(wnd.CertDateRange.getValue1(), 'd.m.Y');
			data.DocumentUcStr_CertGodnDate = Ext.util.Format.date(wnd.CertDateRange.getValue2(), 'd.m.Y');

			data.DocumentUcStorageWorkData = Ext.util.JSON.encode(this.StorageWorkRows.getDataForSave());
			var lastStorageWork = this.StorageWorkRows.getLastRowData();
			if (lastStorageWork) {
				data.DocumentUcStorageWork_id = lastStorageWork.DocumentUcStorageWork_id;
				data.DocumentUcStorageWork_FactQuantity = lastStorageWork.DocumentUcStorageWork_FactQuantity;
				data.DocumentUcStorageWork_Comment = lastStorageWork.DocumentUcStorageWork_Comment;
			} else {
				data.DocumentUcStorageWork_id = null;
				data.DocumentUcStorageWork_FactQuantity = null;
				data.DocumentUcStorageWork_Comment = null;
			}

			//проверка на дублирование другой строки в документе учета
			if (this.DrugDocumentType_Code.inlist([21])) {
				var unique = true;
				this.owner.getGrid().getStore().each(function(record) {
					if (
						record.get('DocumentUcStr_id') != wnd.params.DocumentUcStr_id
						&& record.get('DocumentUcStr_oid') == data.DocumentUcStr_oid
						&& record.get('Person_id') == data.Person_id
					) {
						unique = false;
					}
				});
				if (!unique) {
					sw.swMsg.alert(langs('Ошибка'), langs('Строка медикамента по пациенту уже существует'));
					return false;
				}
			} else {
				data.RecordForMerge_id = null;
				var merge_data = this.getDataForMerge(data);
				if (merge_data && merge_data.DocumentUcStr_id > 0) {
					if (confirm(langs('Такая строка уже есть в документе учета, выполнить суммирование количества?'))) {
						data.RecordForMerge_id = merge_data.DocumentUcStr_id;
						data.DocumentUcStr_Sum = data.DocumentUcStr_Sum*1 + merge_data.DocumentUcStr_Sum*1;
						data.DocumentUcStr_SumNds = data.DocumentUcStr_SumNds*1 + merge_data.DocumentUcStr_SumNds*1;
						data.DocumentUcStr_NdsSum = data.DocumentUcStr_NdsSum*1 + merge_data.DocumentUcStr_NdsSum*1;
						data.DocumentUcStr_Count = data.DocumentUcStr_Count*1 + merge_data.DocumentUcStr_Count*1;
						data.DocumentUcStr_EdCount = data.DocumentUcStr_EdCount*1 + merge_data.DocumentUcStr_EdCount*1;

						if (wnd.action == 'edit') {
							//удаляем текущую запись
							wnd.owner.deleteRecord();
						}
					} else {
						return false;
					}
				}
			}

			if (data.Drug_id > 0) {
				index = wnd.drug_combo.getStore().findBy(function(rec) { return rec.get('Drug_id') == data.Drug_id; });
				if (index == -1) {
					return false;
				}
				var comboDrugStore = wnd.drug_combo.getStore();
				record = comboDrugStore.getAt(index);
				data.Drug_Name = record.get('Drug_Name');
				data.Drug_isPKU = record.get('isPKU');

				data.hintTradeName = record.get('hintTradeName');
				data.hintPackagingData = record.get('hintPackagingData');
				data.hintRegistrationData = record.get('hintRegistrationData');
				data.hintPRUP = record.get('hintPRUP');
				data.FirmNames = record.get('FirmNames');
			}

			if (data.DrugNds_id > 0) {
				index = wnd.form.findField('DrugNds_id').getStore().findBy(function(rec) { return rec.get('DrugNds_id') == data.DrugNds_id; });
				if (index == -1) {
					return false;
				}
				record = wnd.form.findField('DrugNds_id').getStore().getAt(index);
				data.DrugNds_Code = record.get('DrugNds_Code');
			}

			if (data.DocumentUcStr_oid > 0) {
				index = wnd.form.findField('DocumentUcStr_oid').getStore().findBy(function(rec) { return rec.get('DocumentUcStr_id') == data.DocumentUcStr_oid; });
				if (index == -1) {
					return false;
				}
				record = wnd.form.findField('DocumentUcStr_oid').getStore().getAt(index);
				data.DocumentUcStr_oName = record.get('DrugShipment_Name')+(!Ext.isEmpty(record.get('AccountType_Name')) ? ' '+record.get('AccountType_Name') : '');
			}

            if (wnd.sz_combo.getValue() > 0) {
                var sz_data = wnd.sz_combo.getSelectedRecordData();
                if (!Ext.isEmpty(sz_data.StorageZone_Address)) {
                    data.StorageZone_Name = sz_data.StorageZone_Address;
                }
            } else {
                data.StorageZone_Name = 'Без места хранения';
            }

            var gu_data = wnd.gu_combo.getSelectedRecordData();
            if (!Ext.isEmpty(gu_data.GoodsUnit_Name)) {
                data.GoodsUnit_Name = gu_data.GoodsUnit_Name;
            }

            var gu_b_data = wnd.gu_b_combo.getSelectedRecordData();
            if (!Ext.isEmpty(gu_b_data.GoodsUnit_Name)) {
                data.GoodsUnit_bName = gu_b_data.GoodsUnit_Name;
            }

            var post_gu_b_data = wnd.post_gu_b_combo.getSelectedRecordData();
            if (!Ext.isEmpty(post_gu_b_data.GoodsUnit_Name)) {
                data.PostGoodsUnit_bName = post_gu_b_data.GoodsUnit_Name;
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
    openBarCodeViewWindow: function() {
        var wnd = this;
        var str_id = !Ext.isEmpty(this.params.DocumentUcStr_id) ? this.params.DocumentUcStr_id : null;

        getWnd('swDrugPackageBarCodeViewWindow').show({
            action: wnd.action,
            DocumentUcStr_id: str_id,
            BarCodeChangedData: wnd.BarCodeChangedData,
            onSave: function(data) {
                wnd.BarCodeChangedData = data.ChangedData;
                wnd.AddedBarCode_Count = data.AddedBarCode_Count;
                wnd.setBarCodeViewBtnDisabled();
            }
        });
    },
    openGoodsPackCountEditWindow: function(combo) {
        var wnd = this;
        var params = new Object()

        params.Drug_id = wnd.drug_combo.getValue();
        params.GoodsUnit_id = combo.getValue();
        params.onSave = function(data) {
            if (!Ext.isEmpty(data.GoodsUnit_id)) {
                combo.set_post_fields = true;
                combo.setValueById(data.GoodsUnit_id);
            }
        }

        if (!Ext.isEmpty(params.Drug_id)) {
            getWnd('swGoodsPackCountEditWindow').show(params);
        }
    },
	show: function() {
        var wnd = this;
		sw.Promed.swNewDocumentUcStrEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.onSave = Ext.emptyFn;
		this.DefaultValues = new Object();
		this.DrugDocumentType_Code = null;
		this.DrugDocumentStatus_Code = null;	//Статус документа
		this.isAptMu = false;
		this.params = new Object();
		this.form_title = null;
		this.doc_status = null;
		this.str_status = null;
		this.curPrepSeries_GodnDate = null;
        this.BarCodeChangedData = null; //для хранения изменений в списке штрих-кодов
        this.SavedBarCode_Count = 0; //для хранения количества сохраненых в бд штрих-кодов
        this.AddedBarCode_Count = 0; //для хранения разницы между количеством добавленных и количеством удаленных кодов штрих-кодов на форме

        this.show_diff_gu = (getDrugControlOptions().doc_uc_different_goods_unit_control && getGlobalOptions().orgtype == 'lpu'); //отображение поле списания в альтернативных ед. измерения

        if (!arguments[0]) {
            sw.swMsg.alert(langs('Ошибка'), langs('Не указаны входные данные'), function() { wnd.hide(); });
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
		if ( arguments[0].DrugDocumentType_Code ) {
			this.DrugDocumentStatus_Code = arguments[0].DrugDocumentStatus_Code;
		}
		if ( arguments[0].isAptMu ) {
			this.isAptMu = arguments[0].isAptMu;
		}
		if (arguments[0].params) {
			this.params = arguments[0].params;

            if (arguments[0].params.BarCodeChangedData) {
                this.BarCodeChangedData = arguments[0].params.BarCodeChangedData;
            }
            if (arguments[0].params.SavedBarCode_Count) {
                this.SavedBarCode_Count = arguments[0].params.SavedBarCode_Count;
            }
		}

		this.doc_status = !Ext.isEmpty(this.DrugDocumentStatus_Code) ? this.DrugDocumentStatus_Code : 1;
		this.str_status = !Ext.isEmpty(this.params.DrugDocumentStatus_Code) ? this.params.DrugDocumentStatus_Code : 1;

		this.findById('NewDocumentUcStrEditStorageWork').hide();
		this.StorageWorkRows.removeAllRows(true);

		var lab_combo = this.cert_form.findField('DrugLabResult_Name');



		this.form.reset();
		this.cert_form.reset();
		this.FileUploadPanel.reset();

        this.drug_combo.fullReset();
        this.oid_combo.fullReset();
        this.sz_combo.fullReset();
        this.post_sz_combo.fullReset();
        this.gu_b_combo.fullReset();
        this.gu_combo.fullReset();
        this.post_gu_b_combo.fullReset();
        this.post_gu_combo.fullReset();

        this.setDrugDocumentType();

        this.FileUploadPanel.restorePanel();

		lab_combo.getStore().removeAll();

		wnd.setDisabled(wnd.action == 'view');

		wnd.setTitle(!Ext.isEmpty(this.form_title) ? this.form_title : "Медикамент");
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:langs('Загрузка...')});
        loadMask.show();
		switch (wnd.action) {
			case 'add':
				wnd.setTitle(this.title + ": Добавление");
				wnd.setDefaultValues();
                if (!Ext.isEmpty(this.DefaultValues.StorageZone_id)) {
                    wnd.sz_combo.setValueById(this.DefaultValues.StorageZone_id);
                }
			case 'edit':
			case 'view':
				if (wnd.action != 'add') {
					wnd.setTitle(this.title + (wnd.action == "edit" ? ": Редактирование" : ": Просмотр"));
				}
				this.form.setValues(wnd.params);
				this.cert_form.setValues(wnd.params);
				wnd.drug_combo.setValueById(wnd.params.Drug_id);
                if (wnd.params.DocumentUcStr_oid > 0) {
                    wnd.oid_combo.setValueById(wnd.params.DocumentUcStr_oid);
                } else { // загрузка мест хранения прописана в setLinkedFieldValues для комбобокса "Партия", поэтому если Партия не загружается то места хранения нужно устанавливать вручную
                    if (!Ext.isEmpty(this.DefaultValues.StorageZone_id)) { //значение по умолчанию для зоны хранения первично
                        wnd.sz_combo.setValueById(this.DefaultValues.StorageZone_id);
                    } else {
                        wnd.sz_combo.setValueById(wnd.params.StorageZone_id);
                    }
                }
                wnd.post_sz_combo.setValueById(wnd.params.PostStorageZone_id);
                if (!Ext.isEmpty(wnd.params.GoodsUnit_bid)) {
                    if (!Ext.isEmpty(wnd.params.Drug_id)) {
                        wnd.gu_b_combo.getStore().baseParams.Drug_id = wnd.params.Drug_id;
                    }
                    wnd.gu_b_combo.setValueById(wnd.params.GoodsUnit_bid);
                }
                if (!Ext.isEmpty(wnd.params.GoodsUnit_id)) {
                    if (!Ext.isEmpty(wnd.params.Drug_id)) {
                        wnd.gu_combo.getStore().baseParams.Drug_id = wnd.params.Drug_id;
                    }
                    wnd.gu_combo.setValueById(wnd.params.GoodsUnit_id);
                }
                if (!Ext.isEmpty(wnd.params.PostGoodsUnit_bid)) {
                    if (!Ext.isEmpty(wnd.params.Drug_id)) {
                        wnd.post_gu_b_combo.getStore().baseParams.Drug_id = wnd.params.Drug_id;
                    }
                    wnd.post_gu_b_combo.setValueById(wnd.params.PostGoodsUnit_bid);
                }
                if (!Ext.isEmpty(wnd.params.PostGoodsUnit_id)) {
                    if (!Ext.isEmpty(wnd.params.Drug_id)) {
                        wnd.post_gu_combo.getStore().baseParams.Drug_id = wnd.params.Drug_id;
                    }
                    wnd.post_gu_combo.setValueById(wnd.params.PostGoodsUnit_id);
                }


				lab_combo.getStore().load({
					callback: function() {
						lab_combo.setValue(lab_combo.getValue());
					}
				});
				if (wnd.params.DocumentUcStr_CertDate && wnd.params.DocumentUcStr_CertGodnDate) {
					wnd.CertDateRange.setValue(wnd.params.DocumentUcStr_CertDate + ' - ' + wnd.params.DocumentUcStr_CertGodnDate);
				}
				if (!Ext.isEmpty(wnd.params.DocumentUcStorageWorkData)) {
					var StorageWorkData = Ext.util.JSON.decode(wnd.params.DocumentUcStorageWorkData);
					if (Ext.isArray(StorageWorkData) && StorageWorkData.length > 0) {
						this.findById('NewDocumentUcStrEditStorageWork').show();
						StorageWorkData.forEach(function(StorageWork){
							wnd.StorageWorkRows.addRow(StorageWork);
						});
					}
				}
				loadMask.hide();
				wnd.curPrepSeries_GodnDate = wnd.form.findField('PrepSeries_GodnDate').getValue();
				if (this.action == 'edit') {
					if (wnd.form.findField('PrepSeries_id').getValue() > 0) {
						wnd.form.findField('PrepSeries_GodnDate').disable();
					} else if (!wnd.form.findField('PrepSeries_GodnDate').enable_blocked) {
						wnd.form.findField('PrepSeries_GodnDate').enable();
					}
					if(isSuperAdmin() || isUserGroup('rlsoper') || isUserGroup('rlsadm')) 
					{
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
            loadByObjectID: function(object_id) {
                if (!Ext.isEmpty(object_id)) {
                    var params = new Object();
                    Ext.apply(params, this.listParams);
                    params.add_empty_combo = !wnd.display_oid_files;
                    params.ObjectID = object_id;
                    this.loadData(params);
                }
            },
			restorePanel: function() {
				var panel = this;
				
				if (wnd.params) {
                    if (wnd.display_oid_files) {
                        if (!Ext.isEmpty(wnd.params.DocumentUcStr_oid)) {
                            panel.loadByObjectID(wnd.params.DocumentUcStr_oid);
                        }
                    } else if (wnd.params.FileData) { //восстанавливаем из копии
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
					} else if (wnd.params.SavedFileCount > 0 && !Ext.isEmpty(wnd.params.DocumentUcStr_id)) { //загружаем данные из БД для партии или самого документа
                        panel.loadByObjectID(wnd.params.DocumentUcStr_id);
					}
				}
			}
		});

        var drug_children_list = ['DocumentUcStr_oid', 'StorageZone_id', 'GoodsUnit_bid', 'GoodsUnit_id'];

        wnd.drug_combo = new sw.Promed.SwCustomOwnerCombo({
            fieldLabel: langs('Торг. наим.'),
            hiddenName: 'Drug_id',
            displayField: 'Drug_Name',
            valueField: 'Drug_id',
            allowBlank: false,
            anchor: '95%',
            triggerAction: 'all',
            trigger2Class: 'x-form-search-trigger',
            ownerWindow: wnd,
            defaultChildrenList: drug_children_list,
            childrenList: drug_children_list,
            heritageList: ['Drug_id', 'isPKU'],
            tpl: new Ext.XTemplate(
            	
                '<tpl for="."><div class="x-combo-list-item" {[this.titleSet(values.hintTradeName, values.hintPackagingData, values.hintRegistrationData, values.hintPRUP, values.FirmNames)]}>',
                '<table style="width:100%;border: 0;"><td style="width:80%;"><h3>{Drug_Name}</h3></td><td style="width:20%;"></td></tr></table>',
                '</div></tpl>',
                {
	                titleSet: function(str1,str2,str3,str4, firmNames){
	                	var mark = '&#8226;';
	                	var br = '&#013;';
	                	var bodyHint = '';
	                	var hintstr1 = (str1) ? str1.replace(/"/g, "&#8242;"): '';
	                	var hintstr2 = '';
	                	if(str2){
	                		if(firmNames){
		                		var search = str2.search(firmNames);
		                		if(search>0){
		                			hintstr2 = str2.substr(0, search-1).replace(/"/g, "&#8242;");
		                		}else if(search < 0){
		                			hintstr2 = str2.replace(/"/g, "&#8242;");
		                		}
		                	}else{
		                		hintstr2 = str2.replace(/"/g, "&#8242;");
		                	}
	                	}
	                	var hintstr3 = (str3) ? str3.replace(/"/g, "&#8242;"): '';
	                	var hintstr4 = (str4) ? str4.replace(/"/g, "&#8242;"): '';

	                	if(hintstr1){
	                		if(hintstr1.slice(-1) == ',') hintstr1=hintstr1.slice(0, -1);
	                		bodyHint += mark + ' Торговое наименование: ' + hintstr1 + br;
	                	}
	                	if(hintstr2){
	                		if(hintstr2.slice(-1) == ',') hintstr2=hintstr2.slice(0, -1);
	                		bodyHint += mark + ' Данные об упаковке: ' + hintstr2 + br;
	                	}
	                	if(hintstr3){
	                		if(hintstr3.slice(-1) == ',') hintstr3=hintstr3.slice(0, -1);
	                		bodyHint += mark + ' Данные о регистрации: ' + hintstr3 + br;
	                	}
	                	if(hintstr4){
	                		bodyHint += mark + ' Пр./Уп.: ' + hintstr4;
	                	}
	                	return 'title="'+bodyHint+'"';
	                }
	            }
            ),
            store: new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                        id: 'Drug_id'
                    },
                    [
                        {name: 'Drug_id', mapping: 'Drug_id'},
                        {name: 'Drug_Name', mapping: 'Drug_Name'},
                        {name: 'DrugNomen_Code', mapping: 'DrugNomen_Code'},
                        {name: 'PrepClass_Name', mapping: 'PrepClass_Name'},
                        {name: 'isPKU', mapping: 'isPKU'},
                        {name: 'Drug_Fas', mapping: 'Drug_Fas'},
                        {name: 'DrugForm_Name', mapping: 'DrugForm_Name'},
                        {name: 'DrugUnit_Name', mapping: 'DrugUnit_Name'},
                        {name: 'hintTradeName', mapping: 'hintTradeName'},
                        {name: 'hintPackagingData', mapping: 'hintPackagingData'},
                        {name: 'hintRegistrationData', mapping: 'hintRegistrationData'},
                        {name: 'hintPRUP', mapping: 'hintPRUP'},
                        {name: 'FirmNames', mapping: 'FirmNames'}

                    ]),
                url: '/?c=DocumentUc&m=loadDrugComboForDocumentUcStr'
            }),
            setLinkedFieldValues: function(event_name) {
                var record_data = this.getSelectedRecordData();

                if (!Ext.isEmpty(record_data.Drug_id)) {
                    wnd.form.findField('DrugNomen_Code').setValue(record_data.DrugNomen_Code);
                    wnd.form.findField('PrepClass_Name').setValue(record_data.PrepClass_Name);
                    wnd.form.findField('isPKU').setValue(record_data.isPKU);
                    //wnd.form.findField('Drug_Fas').setValue(record_data.Drug_Fas);
                    //wnd.form.findField('DrugForm_Name').setValue(record_data.DrugForm_Name);
                    //wnd.form.findField('DrugUnit_Name').setValue(record_data.DrugUnit_Name);
                }
                
                wnd.setOstCount();
                if (event_name != 'set_by_id') {
                    wnd.setEdCount();
                }
            },
            onTrigger2Click: function() {
                if (this.disabled) {
                    return false;
                }

                var searchWindow = 'swEvnPrescrDrugTorgSearchWindow';
                var combo = this;
                combo.disableBlurAction = true;
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
                            PrepClass_Name: drugData.PrepClass_Name,
                            isPKU: drugData.isPKU,
                            Drug_Fas: drugData.Drug_Fas,
                            DrugForm_Name: drugData.DrugForm_Name,
                            DrugUnit_Name: drugData.DrugUnit_Name,
                            hintTradeName: drugData.hintTradeName,
                            hintPackagingData: drugData.hintPackagingData,
                            hintRegistrationData: drugData.hintRegistrationData,
                            hintPRUP: drugData.hintPRUP,
                            FirmNames: drugData.FirmNames
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

                        combo.setLinkedFieldValues();

                        getWnd(searchWindow).hide();
                    }
                });
            }
        });

        wnd.oid_combo = new sw.Promed.SwCustomOwnerCombo({
            fieldLabel: langs('Партия'),
            hiddenName: 'DocumentUcStr_oid',
            displayField: 'DocumentUcStr_Name',
            valueField: 'DocumentUcStr_id',
            editable: false,
            allowBlank: true,
            anchor: '95%',
            listWidth: 800,
            triggerAction: 'all',
            ownerWindow: wnd,
            allowReloadDisabledChildren: true,
            childrenList: ['StorageZone_id'],
            heritageList: ['DocumentUcStr_id', 'DrugShipment_id'],
            tpl: new Ext.XTemplate(
                '<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
                '<td style="padding: 2px; width: 10%;">Партия</td>',
                '<td style="padding: 2px; width: 10%;">Серия</td>',
                '<td style="padding: 2px; width: 10%;">Срок годности</td>',
                '<td style="padding: 2px; width: 10%;">Брак</td>',
                '<td style="padding: 2px; width: 10%;">Цена</td>',
                '<td style="padding: 2px; width: 10%;">Ставка НДС</td>',
                '<td style="padding: 2px; width: 10%;">Остаток</td>',
                '<td style="padding: 2px; width: 15%;">Ист.фин.</td>',
                '<td style="padding: 2px; width: 15%;">Ст.расхода</td>',
                '</tr><tpl for="."><tr class="x-combo-list-item">',
                '<td style="padding: 2px;">{DrugShipment_Name}&nbsp;</td>',
                '<td style="padding: 2px;">{[values.DocumentUcStr_Ser ? (values.PrepSeries_isDefect == 1 ? "<font color=#ff0000>"+values.DocumentUcStr_Ser+"</font>" : values.DocumentUcStr_Ser) : ""]}&nbsp;</td>',
                '<td style="padding: 2px;">{PrepSeries_GodnDate}&nbsp;</td>',
                '<td style="padding: 2px;">{[values.PrepSeries_isDefect == 1 ? "Да" : ""]}&nbsp;</td>',
                '<td style="padding: 2px;">{DocumentUcStr_Price}&nbsp;</td>',
                '<td style="padding: 2px;">{DrugNds_Code}&nbsp;</td>',
                '<td style="padding: 2px; text-align: left;">{[this.getOstStr(values)]}&nbsp;</td>',
                '<td style="padding: 2px;">{DrugFinance_Name}&nbsp;</td>',
                '<td style="padding: 2px;">{WhsDocumentCostItemType_Name}&nbsp;</td>',
                '</tr></tpl>',
                '</table>',
                {
                    isCountEnabled: function() {
                        return wnd.base_params.StorageZone_id.isCountEnabled;
                    },
                    getOstStr: function(values) {
                        var str = "";
                        if (values) {
                            str = values.DocumentUcStr_OstCount+"&nbsp;"+values.GoodsUnit_bNick;
                            if (wnd.show_diff_gu && !Ext.isEmpty(values.GoodsUnit_id) && values.GoodsUnit_bid != values.GoodsUnit_id && values.GoodsUnit_OstCount != "") {
                                str += " / "+values.GoodsUnit_OstCount+"&nbsp;"+values.GoodsUnit_Nick;
                            }
                        }
                        return str;
                    }
                }
            ),
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
                    {name: 'DrugFinance_Name', mapping: 'DrugFinance_Name'},
                    {name: 'WhsDocumentCostItemType_Name', mapping: 'WhsDocumentCostItemType_Name'},
                    {name: 'DrugShipment_Name', mapping: 'DrugShipment_Name'},
                    {name: 'DrugShipment_id', mapping: 'DrugShipment_id'},
                    {name: 'AccountType_Name', mapping: 'AccountType_Name'},
                    {name: 'GoodsUnit_bid', mapping: 'GoodsUnit_bid'},
                    {name: 'GoodsUnit_bNick', mapping: 'GoodsUnit_bNick'},
                    {name: 'GoodsUnit_id', mapping: 'GoodsUnit_id'},
                    {name: 'GoodsUnit_Nick', mapping: 'GoodsUnit_Nick'},
                    {name: 'GoodsUnit_OstCount', mapping: 'GoodsUnit_OstCount'}
                ]),
                url: '/?c=DocumentUc&m=loadDocumentUcStrOidCombo'
            }),
            setLinkedFieldValues: function(event_name) {
                wnd.form.findField('DocumentUcStr_OstCount').setValue('');
                wnd.form.findField('DocumentUcStr_OstEdCount').setValue('');
                wnd.form.findField('DocumentUcStr_Price').setValue('');
                wnd.form.findField('DrugNds_id').setValue(!Ext.isEmpty(wnd.DefaultValues.DrugNds_id) ? wnd.DefaultValues.DrugNds_id : '');
                wnd.form.findField('DocumentUcStr_IsNDS').setValue('');
                wnd.form.findField('DocumentUcStr_Sum').setValue('');
                wnd.form.findField('DocumentUcStr_SumNds').setValue('');
                wnd.form.findField('DocumentUcStr_NdsSum').setValue('');
                wnd.form.findField('DocumentUcStr_Ser').setValue('');
                wnd.form.findField('PrepSeries_GodnDate').setValue('');
                wnd.form.findField('PrepSeries_isDefect').setValue('');
                wnd.cert_form.findField('DocumentUcStr_CertNum').setValue('');
                wnd.cert_form.findField('DocumentUcStr_CertOrg').setValue('');
                wnd.cert_form.findField('DrugLabResult_Name').setValue('');
                wnd.CertDateRange.setValue('');

                var record_data = this.getSelectedRecordData();
                var str_id = null;

                if (!Ext.isEmpty(record_data.DocumentUcStr_id)) {
                    str_id = record_data.DocumentUcStr_id;

                    wnd.form.findField('DocumentUcStr_Price').setValue(record_data.DocumentUcStr_Price);
                    wnd.form.findField('DrugNds_id').setValue(record_data.DrugNds_id);
                    wnd.form.findField('DocumentUcStr_IsNDS').setValue(record_data.DocumentUcStr_IsNDS);
                    wnd.form.findField('DocumentUcStr_Ser').setValue(record_data.DocumentUcStr_Ser);

                    wnd.form.findField('PrepSeries_GodnDate').setValue(record_data.PrepSeries_GodnDate);
					wnd.curPrepSeries_GodnDate = wnd.form.findField('PrepSeries_GodnDate').getValue();
					if(isSuperAdmin() || isUserGroup('rlsoper') || isUserGroup('rlsadm'))
					{
						wnd.form.findField('PrepSeries_GodnDate').enable_blocked = false;
						wnd.form.findField('PrepSeries_GodnDate').enable();
					}

                    wnd.form.findField('PrepSeries_isDefect').setValue(record_data.PrepSeries_isDefect);
                    wnd.cert_form.findField('DocumentUcStr_CertNum').setValue(record_data.DocumentUcStr_CertNum);
                    wnd.cert_form.findField('DocumentUcStr_CertOrg').setValue(record_data.DocumentUcStr_CertOrg);
                    wnd.cert_form.findField('DrugLabResult_Name').setValue(record_data.DrugLabResult_Name);
                    if (record_data.DocumentUcStr_CertDate && record_data.DocumentUcStr_CertGodnDate) {
                        wnd.CertDateRange.setValue(record_data.DocumentUcStr_CertDate + ' - ' + record_data.DocumentUcStr_CertGodnDate);
                    }

                    wnd.gu_b_combo.set_post_fields = true;
                    wnd.gu_b_combo.setValueById(record_data.GoodsUnit_bid);
                }

                wnd.setPrice('EdPrice');

                if (event_name == 'set_by_id') { // после инициализации Партии, нужно перезагрузить содержимое комбобокса с местами хранения
                    if (!Ext.isEmpty(wnd.DefaultValues.StorageZone_id)) { //значение по умолчанию для зоны хранения первично
                        wnd.sz_combo.setValueById(wnd.DefaultValues.StorageZone_id);
                    } else {
                        wnd.sz_combo.setValueById(wnd.params.StorageZone_id);
                    }
                } else {
                    //если это не загрузка конкретного значения, то устанавливаем в поле ед. списания значение по умолчанию (при соотвтетсвующих условиях)
                    wnd.gu_b_combo.selectDefaultValue();
                    wnd.gu_combo.selectDefaultValue();
                }

                if (wnd.display_oid_files && event_name != 'set_by_id') {
                    wnd.FileUploadPanel.reset();
                    if(!Ext.isEmpty(str_id)) {
                        wnd.FileUploadPanel.loadByObjectID(str_id);
                    }
                }

                wnd.setOstCount();
                if (event_name != 'set_by_id') {
                    wnd.setEdCount();
                }
                wnd.setSumFields();
            },
            selectDefaultValue: function() {//установка значения по умолчанию
                if (this.getStore().getCount() == 1) { //если загрузилась одна запись, выбираем её
                    var id = this.getStore().getAt(0).get(this.valueField);

                    this.setValue(id);
                    this.fireEvent('change', this, id);
                }
            },
            onLoadData: function() {
                this.selectDefaultValue();
            }
        });

        wnd.sz_combo = new sw.Promed.SwCustomRemoteCombo({
            fieldLabel: langs('Место хранения'),
            hiddenName: 'StorageZone_id',
            displayField: 'DrugStorageZone_Name',
            valueField: 'StorageZone_id',
            editable: false,
            allowBlank: true,
            anchor: null,
            width: 737,
            listWidth: 800,
            triggerAction: 'all',
            tpl: new Ext.XTemplate(
                '<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
                '<td style="padding: 2px; width: 50%;">Адрес</td>',
                '<td style="padding: 2px; width: 20%;">Наименование</td>',
                '<td style="padding: 2px; width: 10%;{[this.isCountEnabled() ? "" : " display: none;"]}">Количество</td>',
                //'<td style="padding: 2px; width: 10%;">Ед.учета</td>',
                '</tr><tpl for="."><tr class="x-combo-list-item">',
                '<td style="padding: 2px;">{StorageZone_Address}&nbsp;</td>',
                '<td style="padding: 2px;">{StorageUnitType_Name}&nbsp;</td>',
                '<td style="padding: 2px;{[this.isCountEnabled() ? "" : " display: none;"]}">{[this.getOstStr(values)]}&nbsp;</td>',
                //'<td style="padding: 2px;">{GoodsUnit_Name}&nbsp;</td>',
                '</tr></tpl>',
                '</table>',
                {
                    isCountEnabled: function() {
                        return wnd.base_params.StorageZone_id.isCountEnabled;
                    },
                    getOstStr: function(values) {
                        var str = "";
                        if (values) {
                            //str = values.DrugStorageZone_Count+" уп. "+(values.GoodsUnit_Nick != "" && values.GoodsUnit_Nick != "уп." && values.GoodsPackCount_Count > 0 ? "/ "+(values.DrugStorageZone_Count*values.GoodsPackCount_Count)+" "+values.GoodsUnit_Nick : "");
                            str = values.DrugStorageZone_Count+" "+(values.GoodsUnit_Nick != "" ? values.GoodsUnit_Nick : "уп.");
                            if (!Ext.isEmpty(values.EdOst_Count)) {
                                str += " / "+values.EdOst_Count+" "+values.EdOst_GoodsUnit_Nick;
                            }
                        }
                        return str;
                    }
                }
            ),
            store: new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'StorageZone_id'
                }, [
                    {name: 'StorageZone_id', mapping: 'StorageZone_id'},
                    {name: 'StorageUnitType_Name', mapping: 'StorageUnitType_Name'},
                    {name: 'DrugStorageZone_Count', mapping: 'DrugStorageZone_Count'},
                    {name: 'StorageZone_Address', mapping: 'StorageZone_Address'},
                    {name: 'GoodsUnit_Name', mapping: 'GoodsUnit_Name'},
                    {name: 'GoodsUnit_Nick', mapping: 'GoodsUnit_Nick'},
                    {name: 'DrugStorageZone_Name', mapping: 'DrugStorageZone_Name'},
                    {name: 'EdOst_Count', mapping: 'EdOst_Count'},
                    {name: 'EdOst_GoodsUnit_Nick', mapping: 'EdOst_GoodsUnit_Nick'}
                ]),
                url: '/?c=DocumentUc&m=loadStorageZoneByDrugIdCombo'
            }),
            setLinkedFieldValues: function(event_name) {
                wnd.setOstCount();
                if (event_name != 'set_by_id') {
                    wnd.setEdCount();
                }
                wnd.setSumFields();
            },
            onLoadData: function() {
                var sz_id = null;
                var idx = -1;

                if (this.getStore().getCount() > 0) {
                    //ищем среди загруженых значение по умолчанию
                    if (!Ext.isEmpty(wnd.DefaultValues.StorageZone_id)) {
                        sz_id = wnd.DefaultValues.StorageZone_id;
                        idx = this.getStore().findBy(function(rec) { return rec.get('StorageZone_id') == sz_id; });
                    }

                    //пытаемся выбрать значение "Без места хранения"
                    if (idx < 0 && wnd.oid_combo.getValue() > 0) {
                        sz_id = 0;
                        idx = this.getStore().findBy(function(rec) { return rec.get('StorageZone_id') == sz_id; });
                    }

                    if (idx > -1) {
                        wnd.sz_combo.setValue(sz_id);
                        wnd.sz_combo.fireEvent('change', wnd.sz_combo, wnd.sz_combo.getValue());
                    }
                }
            }
        });

        wnd.post_sz_combo = new sw.Promed.SwCustomRemoteCombo({
            fieldLabel: langs('Место хранения'),
            hiddenName: 'PostStorageZone_id',
            displayField: 'DrugStorageZone_Name',
            valueField: 'StorageZone_id',
            editable: false,
            allowBlank: true,
            anchor: null,
            width: 737,
            listWidth: 800,
            triggerAction: 'all',
            tpl: new Ext.XTemplate(
                '<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
                '<td style="padding: 2px; width: 50%;">Адрес</td>',
                '<td style="padding: 2px; width: 20%;">Наименование</td>',
                '<td style="padding: 2px; width: 10%;{[this.isCountEnabled() ? "" : " display: none;"]}">Количество</td>',
                //'<td style="padding: 2px; width: 10%;">Ед.учета</td>',
                '</tr><tpl for="."><tr class="x-combo-list-item">',
                '<td style="padding: 2px;">{StorageZone_Address}&nbsp;</td>',
                '<td style="padding: 2px;">{StorageUnitType_Name}&nbsp;</td>',
                '<td style="padding: 2px;{[this.isCountEnabled() ? "" : " display: none;"]}">{[this.getOstStr(values)]}&nbsp;</td>',
                //'<td style="padding: 2px;">{GoodsUnit_Name}&nbsp;</td>',
                '</tr></tpl>',
                '</table>',
                {
                    isCountEnabled: function() {
                        return wnd.base_params.PostStorageZone_id.isCountEnabled;
                    },
                    getOstStr: function(values) {
                        var str = "";
                        if (values) {
                            //str = values.DrugStorageZone_Count+" уп. "+(values.GoodsUnit_Nick != "" && values.GoodsUnit_Nick != "уп." && values.GoodsPackCount_Count > 0 ? "/ "+(values.DrugStorageZone_Count*values.GoodsPackCount_Count)+" "+values.GoodsUnit_Nick : "");
                            str = values.DrugStorageZone_Count+" "+(values.GoodsUnit_Nick != "" ? values.GoodsUnit_Nick : "уп.");
                            if (!Ext.isEmpty(values.EdOst_Count)) {
                                str += " / "+values.EdOst_Count+" "+values.EdOst_GoodsUnit_Nick;
                            }
                        }
                        return str;
                    }
                }
            ),
            store: new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'StorageZone_id'
                }, [
                    {name: 'StorageZone_id', mapping: 'StorageZone_id'},
                    {name: 'StorageUnitType_Name', mapping: 'StorageUnitType_Name'},
                    {name: 'DrugStorageZone_Count', mapping: 'DrugStorageZone_Count'},
                    {name: 'StorageZone_Address', mapping: 'StorageZone_Address'},
                    {name: 'GoodsUnit_Name', mapping: 'GoodsUnit_Name'},
                    {name: 'GoodsUnit_Nick', mapping: 'GoodsUnit_Nick'},
                    {name: 'DrugStorageZone_Name', mapping: 'DrugStorageZone_Name'},
                    {name: 'EdOst_Count', mapping: 'EdOst_Count'},
                    {name: 'EdOst_GoodsUnit_Nick', mapping: 'EdOst_GoodsUnit_Nick'}
                ]),
                url: '/?c=DocumentUc&m=loadStorageZoneByDrugIdCombo'
            }),
            onLoadData: function() {
                var sz_id = null;
                var idx = -1;

                if (this.getStore().getCount() > 0) {
                    //пытаемся выбрать значение "Без места хранения"
                    if (idx < 0 && wnd.oid_combo.getValue() > 0) {
                        sz_id = 0;
                        idx = this.getStore().findBy(function(rec) { return rec.get('StorageZone_id') == sz_id; });
                    }
                    if (idx > -1) {
                        wnd.post_sz_combo.setValue(sz_id);
                        wnd.post_sz_combo.fireEvent('change', wnd.post_sz_combo, wnd.post_sz_combo.getValue());
                    }
                }
            }
        });

        wnd.gu_b_combo = new sw.Promed.SwCustomRemoteCombo({
            fieldLabel: langs('Ед. учета'),
            hiddenName: 'GoodsUnit_bid',
            displayField: 'GoodsUnit_Str',
            valueField: 'GoodsUnit_id',
            editable: true,
            allowBlank: false,
            listWidth: 200,
            triggerAction: 'all',
            store: new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'GoodsUnit_id'
                }, [
                    {name: 'GoodsUnit_id', mapping: 'GoodsUnit_id'},
                    {name: 'GoodsUnit_Name', mapping: 'GoodsUnit_Name'},
                    {name: 'GoodsUnit_Str', mapping: 'GoodsUnit_Str'},
                    {name: 'GoodsPackCount_Count', mapping: 'GoodsPackCount_Count'},
                    {name: 'GoodsPackCount_Source', mapping: 'GoodsPackCount_Source'}
                ]),
                url: '/?c=DocumentUc&m=loadGoodsUnitCombo'
            }),
            trigger2Class: 'x-form-plus-trigger',
            onTrigger2Click: function() {
                if (!this.disabled) {
                    wnd.openGoodsPackCountEditWindow(this);
                }
            },
            setLinkedFieldValues: function(event_name) {
                wnd.setPostRecountKoef();
                if (event_name == 'set_by_id') {
                    wnd.setPrice('EdPrice');
                    wnd.setPrice('EdRegPrice');
                    if (this.hiddenName.substr(-3) == "bid" && this.set_post_fields) {
                        wnd.setPostFields();
                        this.set_post_fields = false;
                    }
                } else {
                    wnd.setPostFields();
                    wnd.setPrice('EdPrice');
                    wnd.setPrice('EdRegPrice');
                    wnd.setEdCount();
                    wnd.setOstEdCount();
                    wnd.setEdPlanCount();
                    wnd.setSumFields();
                }
                wnd.setBarCodeViewBtnDisabled();
            },
            selectDefaultValue: function() {//установка значения по умолчанию
                if (Ext.isEmpty(wnd.params.GoodsUnit_bid) && wnd.gu_b_combo.getStore().getCount() > 0) { //значение по умолчанию устанавливается только если нет сохраненного в гриде значения
                    var gu_id = null;
                    var idx = -1;
                    var str_id = wnd.oid_combo.getValue();

                    if (str_id > 0) { //если установлено значение партии
                        var str_data = wnd.oid_combo.getSelectedRecordData();
                        if (!Ext.isEmpty(str_data.GoodsUnit_bid)) {
                            gu_id = str_data.GoodsUnit_bid;
                            if (gu_id > 0) { //если в партии указана единица списания, пытаемся выбрать её
                                idx = wnd.gu_b_combo.getStore().findBy(function(rec) { return rec.get('GoodsUnit_id') == gu_id; });
                            }
                        }
                    }

                    if (idx < 0) {
                        if (getGlobalOptions().orgtype == 'lpu' || wnd.DrugDocumentType_Code == 6) { // 6 - Приходная накладная
                            idx = wnd.gu_b_combo.getStore().findBy(function(rec) { return rec.get('GoodsUnit_Name') == "упаковка"; }); //выбираем значение "упаковка"
                        } else {
                            idx = 0; //выбираем первую запись в списке
                        }
                        if (idx >= 0) {
                            gu_id = wnd.gu_b_combo.getStore().getAt(idx).get('GoodsUnit_id');
                        }
                    }

                    if (idx > -1 && gu_id > 0) {
                        wnd.gu_b_combo.setValue(gu_id);
                        wnd.gu_b_combo.fireEvent('change', wnd.gu_b_combo, wnd.gu_b_combo.getValue());
                    }
                }
            },
            onLoadData: function() {
                this.selectDefaultValue();
            }
        });

        wnd.gu_combo = new sw.Promed.SwCustomRemoteCombo({
            fieldLabel: langs('Ед. списания'),
            hiddenName: 'GoodsUnit_id',
            displayField: 'GoodsUnit_Str',
            valueField: 'GoodsUnit_id',
            editable: true,
            allowBlank: true,
            listWidth: 200,
            triggerAction: 'all',
            store: new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'GoodsUnit_id'
                }, [
                    {name: 'GoodsUnit_id', mapping: 'GoodsUnit_id'},
                    {name: 'GoodsUnit_Name', mapping: 'GoodsUnit_Name'},
                    {name: 'GoodsUnit_Str', mapping: 'GoodsUnit_Str'},
                    {name: 'GoodsPackCount_Count', mapping: 'GoodsPackCount_Count'},
                    {name: 'GoodsPackCount_Source', mapping: 'GoodsPackCount_Source'}
                ]),
                url: '/?c=DocumentUc&m=loadGoodsUnitCombo'
            }),
            trigger2Class: 'x-form-plus-trigger',
            onTrigger2Click: function() {
                if (!this.disabled) {
                    wnd.openGoodsPackCountEditWindow(this);
                }
            },
            setLinkedFieldValues: function(event_name) {
                if (event_name == 'set_by_id') {
                    //так как стоимость ед. списания в БД не сохраняется, во время загрузки данных строки, небоходимо как можно быстрее (при наличии данных о количестве ед. в упаковке) расчитать стоимость ед. списания от стоимости упаковки
                    /*wnd.setPrice('EdPrice');
                    wnd.setSumFields();*/
                } else {
                    wnd.setCount();
                    wnd.setPostFields();
                }
                wnd.setOstEdCount();
                wnd.setEdPlanCount();
                wnd.setPrice('EdPrice');
                wnd.setPrice('EdRegPrice');
                wnd.setSumFields();
            },
            selectDefaultValue: function() {//установка значения по умолчанию
                if (Ext.isEmpty(wnd.params.GoodsUnit_id) && wnd.gu_combo.getStore().getCount() > 0) { //значение по умолчанию устанавливается только если нет сохраненного в гриде значения
                    var gu_id = null;
                    var idx = -1;
                    var str_id = wnd.oid_combo.getValue();

                    if (str_id > 0) { //если установлено значение партии
                        var str_data = wnd.oid_combo.getSelectedRecordData();
                        if (!Ext.isEmpty(str_data.GoodsUnit_id) || !Ext.isEmpty(str_data.GoodsUnit_bid)) {
                            gu_id = !Ext.isEmpty(str_data.GoodsUnit_id) ? str_data.GoodsUnit_id : str_data.GoodsUnit_bid;
                            if (gu_id > 0) { //если в партии указана единица списания, пытаемся выбрать её
                                idx = wnd.gu_combo.getStore().findBy(function(rec) { return rec.get('GoodsUnit_id') == gu_id; });
                            }
                        }
                    }

                    if (idx < 0) { //пытаемся выбрать значение указанное в поле "Ед. учета"
                        gu_id = wnd.gu_b_combo.getValue();
                        if (gu_id > 0) {
                            idx = wnd.gu_combo.getStore().findBy(function(rec) { return rec.get('GoodsUnit_id') == gu_id; }); //выбираем значение "упаковка"
                        }
                    }

                    if (idx < 0) {
                        if (getGlobalOptions().orgtype == 'lpu') {
                            idx = 0; //выбираем первую запись в списке
                        } else {
                            idx = wnd.gu_combo.getStore().findBy(function(rec) { return rec.get('GoodsUnit_Name') == "упаковка"; }); //выбираем значение "упаковка"
                        }
                        gu_id = wnd.gu_combo.getStore().getAt(idx).get('GoodsUnit_id');
                    }

                    if (idx > -1 && gu_id > 0) {
                        wnd.gu_combo.setValue(gu_id);
                        wnd.gu_combo.fireEvent('change', wnd.gu_combo, wnd.gu_combo.getValue());
                    }
                }
            },
            onLoadData: function() {
                this.selectDefaultValue();
            }
        });

        wnd.post_gu_b_combo = new sw.Promed.SwCustomRemoteCombo({
            fieldLabel: langs('Ед. учета'),
            hiddenName: 'PostGoodsUnit_bid',
            displayField: 'GoodsUnit_Str',
            valueField: 'GoodsUnit_id',
            editable: true,
            allowBlank: false,
            listWidth: 200,
            triggerAction: 'all',
            store: new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'GoodsUnit_id'
                }, [
                    {name: 'GoodsUnit_id', mapping: 'GoodsUnit_id'},
                    {name: 'GoodsUnit_Name', mapping: 'GoodsUnit_Name'},
                    {name: 'GoodsUnit_Str', mapping: 'GoodsUnit_Str'},
                    {name: 'GoodsPackCount_Count', mapping: 'GoodsPackCount_Count'},
                    {name: 'GoodsPackCount_Source', mapping: 'GoodsPackCount_Source'}
                ]),
                url: '/?c=DocumentUc&m=loadGoodsUnitCombo'
            }),
            trigger2Class: 'x-form-plus-trigger',
            onTrigger2Click: function() {
                if (!this.disabled) {
                    wnd.openGoodsPackCountEditWindow(this);
                }
            },
            setLinkedFieldValues: function(event_name) {
                wnd.setPostRecountKoef();
                if (event_name == 'set_by_id') {
                    if (this.set_post_fields) {
                        wnd.setPostFields();
                        this.set_post_fields = false;
                    }
                } else {
                    wnd.setPostFields();
                    wnd.setPostEdCount();
                }
            },
            selectDefaultValue: function() {//установка значения по умолчанию

            }
        });

        wnd.post_gu_combo = new sw.Promed.SwCustomRemoteCombo({
            fieldLabel: langs('Ед. списания'),
            hiddenName: 'PostGoodsUnit_id',
            displayField: 'GoodsUnit_Str',
            valueField: 'GoodsUnit_id',
            editable: true,
            allowBlank: true,
            listWidth: 200,
            triggerAction: 'all',
            store: new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'GoodsUnit_id'
                }, [
                    {name: 'GoodsUnit_id', mapping: 'GoodsUnit_id'},
                    {name: 'GoodsUnit_Name', mapping: 'GoodsUnit_Name'},
                    {name: 'GoodsUnit_Str', mapping: 'GoodsUnit_Str'},
                    {name: 'GoodsPackCount_Count', mapping: 'GoodsPackCount_Count'},
                    {name: 'GoodsPackCount_Source', mapping: 'GoodsPackCount_Source'}
                ]),
                url: '/?c=DocumentUc&m=loadGoodsUnitCombo'
            }),
            trigger2Class: 'x-form-plus-trigger',
            onTrigger2Click: function() {
                if (!this.disabled) {
                    wnd.openGoodsPackCountEditWindow(this);
                }
            },
            selectDefaultValue: function() {//установка значения по умолчанию

            },
            setLinkedFieldValues: function(event_name) {
                wnd.setPostRecountKoef();
                if (event_name != 'set_by_id') {
                    wnd.setPostEdCount();
                }
            }
        });


		this.CertDateRange = new Ext.form.DateRangeField({
			width: 177,
			fieldLabel: langs('Период действия'),
			hiddenName: 'CertDateRange',
			plugins: [
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			]
		});

		this.StorageWorkRows = new swDocumentUcStorageWorkRows({
			owner: this,
			onFactQuantityChange: function() {
				this.refreshCountByExecutedStorageWork();
			}.createDelegate(this),
			onEndDateChange: function() {
				this.refreshCountByExecutedStorageWork();
			}.createDelegate(this),
			onRowDelete: function() {
				this.refreshCountByExecutedStorageWork();
			}.createDelegate(this)
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
						xtype: 'hidden',
						name: 'Person_id'
					}, {
                        layout: 'column',
                        items: [{
                            layout: 'form',
                            items: [{
                                xtype: 'textfield',
                                fieldLabel: langs('Код'),
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
                            }]
                        }, {
                            layout: 'form',
                            labelWidth: 147,
                            items: [{
                                xtype: 'checkbox',
                                name: 'isPKU',
                                fieldLabel: 'ПКУ',
                                disabled: true
                            }]
                        }, {
                            layout: 'form',
                            labelWidth: 5,
                            items: [{
                                xtype: 'textfield',
                                name: 'PrepClass_Name',
                                labelSeparator: '',
                                disabled: true,
                                width: 312
                            }]
                        }]
                    },
					wnd.drug_combo,
                    {
                        layout: 'form',
                        items: [{
                            xtype: 'textfield',
                            fieldLabel: langs('Наименование партии'),
                            name: 'DrugShipment_Name',
                            anchor: '95%'
                        }]
                    }, {
                        layout: 'form',
                        items: [
                            wnd.oid_combo
                        ]
                    }, {
                        layout: 'column',
                        items: [{
                            layout: 'form',
                            items: [{xtype: 'textfield', fieldLabel: langs('Остаток (ед. уч.)'), name: 'DocumentUcStr_OstCount', disabled: true}]
                        }, {
                            layout: 'form',
                            items: [{xtype: 'textfield', fieldLabel: langs('Остаток (ед. спис.)'), name: 'DocumentUcStr_OstEdCount', disabled: true}]
                        }, {
                            layout: 'form',
                            items: [{
                                xtype: 'numberfield',
                                fieldLabel: 'Кол-во план. (ед.уч.)',
                                name: 'DocumentUcStr_PlanKolvo',
                                allowBlank: true,
                                allowNegative: false,
                                decimalPrecision: 2,
                                listeners: {
                                    'change': function(field, newValue) {
                                        wnd.setEdPlanCount();
                                    }
                                }
                            }]
                        }, {
                            layout: 'form',
                            items: [{
                                xtype: 'numberfield',
                                fieldLabel: 'Кол-во план. (ед.спис.)',
                                name: 'DocumentUcStr_EdPlanKolvo',
                                allowBlank: true,
                                allowNegative: false,
                                decimalPrecision: 2
                            }]
                        }]
                    }, {
                        layout: 'column',
                        items: [{
                            layout: 'form',
                            items: [{
                                xtype: 'textfield',
                                fieldLabel: langs('Серия'),
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
                                                } else if (godn_combo.disabled) {
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
                                fieldLabel: langs('Срок годности до'),
                                name: 'PrepSeries_GodnDate',
                                format: 'd.m.Y',
                                width: 127,
                                plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                                listeners: {
                                    'change': function(cmp,new_value, old_value) {

                                        var new_date = new_value.getDate();
                                        var new_month = new_value.getMonth() + 1;
                                        var new_year = new_value.getFullYear();
                                        var new_date_str = new_year + "-" + new_month + "-" + new_date;

                                        // var cur_date = wnd.curPrepSeries_GodnDate.getDate();
                                        // var cur_month = wnd.curPrepSeries_GodnDate.getMonth() + 1;
                                        // var cur_year = wnd.curPrepSeries_GodnDate.getFullYear();
                                        var curDate = wnd.form.findField('PrepSeries_GodnDate').getValue();
                                        var cur_date = curDate.getDate();
                                        var cur_month = curDate.getMonth() + 1;
                                        var cur_year = curDate.getFullYear();

                                        var cur_date_str = cur_year + "-" + cur_month + "-" + cur_date;

                                        if(new_date_str != cur_date_str)
                                        //if(d_1.format("yyyy-mm-dd") != d_2.format("yyyy-mm-dd"))
                                        {
                                            Ext.Ajax.request({
                                                url: '/?c=DocumentUc&m=checkPrepSeries',
                                                params: {
                                                    DocumentUcStr_Ser: wnd.form.findField('DocumentUcStr_Ser').getValue()
                                                },
                                                success: function(response){
                                                    var result = Ext.util.JSON.decode(response.responseText);
                                                    if(result.checkResult == 1)
                                                    {
                                                        sw.swMsg.alert(langs('Ошибка'), 'В регистре остатков есть медикаменты с  выбранной серией выпуска и указанным сроком годности');
                                                        wnd.form.findField('PrepSeries_GodnDate').setValue(wnd.curPrepSeries_GodnDate);
                                                    }
                                                }
                                            });
                                        }
                                    }.createDelegate(this)
                                }
                            }, {
                                xtype: 'hidden',
                                name: 'PrepSeries_isDefect'
                            }, {
                                xtype: 'hidden',
                                name: 'PrepSeries_id'
                            }]
                        }]
                    }, {
                        xtype: 'fieldset',
                        title: langs('Списать с учета'),
                        originalTitle: langs('Списать с учета'),
                        id: 'NewDocumentUcStrEditSpisFieldset',
                        autoHeight: true,
                        layout: 'form',
                        labelAlign: 'right',
                        style: 'margin: 0px 0px 10px 1px; padding-top: 0px; padding-bottom: 0px;',
                        bodyStyle:'background:#DFE8F6;padding:5px;',
                        items: [{
                            layout: 'form',
                            labelWidth: 153,
                            items: [
                                wnd.sz_combo
                            ]
                        }, {
                            layout: 'column',
                            items: [{
                                layout: 'form',
                                labelWidth: 153,
                                items: [
                                    wnd.gu_b_combo
                                ]
                            }, {
                                layout: 'form',
                                labelWidth: 116,
                                items: [
                                    wnd.gu_combo
                                ]
                            }/*, {
                                layout: 'form',
                                labelWidth: 116,
                                hidden: true,
                                items: [{ //TODO: избавиться от поля
                                    xtype: 'numberfield',
                                    fieldLabel: langs('Кол-во в упак.'),
                                    name: 'GoodsPackCount_Count',
                                    allowDecimals: true,
                                    allowNegative: false,
                                    decimalPrecision: 3,
                                    minValue: 0.001,
                                    disabled: true,
                                    listeners: {
                                        'change': function(field, newValue) {
                                            field.setLinkedFieldValues();
                                        }
                                    },
                                    setLinkedFieldValues: function() {
                                        //если количество ед. спсиания в упаковке не указано - обнуляем поля солответствующей группы
                                        if ((this.getValue()*1) <= 0) {
                                            wnd.form.findField('DocumentUcStr_EdCount').setValue(null);
                                            wnd.form.findField('DocumentUcStr_OstEdCount').setValue(null);
                                            wnd.form.findField('DocumentUcStr_EdPrice').setValue(null);
                                        }

                                        wnd.setPrice('EdPrice');
                                        wnd.setEdCount();
                                        wnd.setOstEdCount();
                                        wnd.setSumFields();
                                    }
                                }, {
                                    //вспомогательное поле, хранит информацию о том, откуда взято значение поля GoodsPackCount_Count
                                    //возможные значения: table - из таблицы GoodsPackCount; drug_fas - фасовка из rls.Drug; fixed_value - фиксированное значение (например для упаковки равное 1)
                                    xtype: 'hidden',
                                    name: 'GoodsPackCount_Source'
                                }]
                            }*/]
                        }, {
                            layout: 'column',
                            items: [{
                                layout: 'form',
                                labelWidth: 153,
                                items: [{
                                    xtype: 'trigger',
                                    fieldLabel: langs('Кол-во (ед.уч.)'),
                                    name: 'DocumentUcStr_Count',
                                    allowBlank: false,
                                    enableKeyEvents: true,
                                    triggerClass: 'x-form-plus-trigger',
                                    validateOnBlur: false,
                                    onValueChange: function() {
                                        wnd.setEdCount();
                                        wnd.setSumFields();
                                        wnd.setPlanCount();
                                        wnd.setEdPlanCount();
                                        wnd.setPostFields();
                                    },
                                    onTriggerClick: function() {
                                        var field = wnd.form.findField('DocumentUcStr_Count');
                                        var gu_data = wnd.form.findField('GoodsUnit_bid').getSelectedRecordData();

                                        if (!Ext.isEmpty(gu_data.GoodsPackCount_Count)) {
                                            getWnd('swDocumentUcStrCountEditWindow').show({
                                                params: {
                                                    GoodsUnit_Name: gu_data.GoodsUnit_Name,
                                                    GoodsPackCount_Count: gu_data.GoodsPackCount_Count
                                                },
                                                onSave: function(data) {
                                                    field.setValue(!Ext.isEmpty(data.GoodsUnit_Count) ? data.GoodsUnit_Count : null);
                                                    field.onValueChange();
                                                }
                                            });
                                        }
                                    },
                                    listeners: {
                                        'change': function(field, newValue) {
                                            if (newValue == 0) {
                                                field.setValue(null);
                                            }
                                            field.onValueChange();
                                        }
                                    }
                                }]
                            }, {
                                layout: 'form',
                                labelWidth: 173,
                                items: [{
                                    xtype: 'numberfield',
                                    fieldLabel: langs('Кол-во (ед. спис.)'),
                                    name: 'DocumentUcStr_EdCount',
                                    allowNegative: false,
                                    decimalPrecision: 6,
                                    listeners: {
                                        'change': function(field, newValue) {
                                            if (newValue == 0) {
                                                field.setValue(null);
                                            }
                                            wnd.setCount();
                                            wnd.setSumFields();
                                            wnd.setPlanCount();
                                            wnd.setEdPlanCount();
                                        }
                                    }
                                }]
                            }]
                        }, {
                            layout: 'column',
                            items: [{
                                layout: 'form',
                                labelWidth: 153,
                                items: [{
                                    xtype: 'numberfield',
                                    fieldLabel: langs('Цена за ед.уч.') + ' (' + getCurrencyName() + ')',
                                    name: 'DocumentUcStr_Price',
                                    allowBlank: false,
                                    allowNegative: false,
                                    decimalPrecision: 2,
                                    listeners: {
                                        'change': function(field, newValue, oldValue) {
                                            wnd.setPrice('EdPrice');
                                            wnd.setSumFields();
                                        }
                                    }
                                }]
                            }, {
                                layout: 'form',
                                items: [{
                                    xtype: 'numberfield',
                                    fieldLabel: langs('Цена за ед.спис.') + ' (' + getCurrencyName() + ')',
                                    name: 'DocumentUcStr_EdPrice',
                                    allowBlank: true,
                                    allowNegative: false,
                                    decimalPrecision: 2,
                                    listeners: {
                                        'change': function(field, newValue, oldValue) {
                                            wnd.setPrice('Price');
                                            wnd.setSumFields();
                                        }
                                    }
                                }]
                            }, {
                                layout: 'form',
                                labelWidth: 153,
                                items: [{
                                    name: 'DocumentUcStr_IsNDS',
                                    fieldLabel: langs('НДС в том числе'),
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
                            labelWidth: 153,
                            items: [{
                                layout: 'form',
                                items: [{xtype: 'numberfield', fieldLabel: langs('Сумма') + ' (' + getCurrencyName() + ')', name: 'DocumentUcStr_Sum', allowBlank: false, allowNegative: false}]
                            }, {
                                layout: 'form',
                                items: [{
                                    xtype: 'swdrugndscombo',
                                    fieldLabel: langs('Ставка НДС'),
                                    name: 'DrugNds_id',
                                    hiddenName: 'DrugNds_id',
                                    width: 127,
                                    allowBlank: false,
                                    listeners: {
                                        'change': function(field, newValue, oldValue) {
                                            wnd.setSumFields();
                                            wnd.setPostFields();
                                        }
                                    }
                                }]
                            }, {
                                layout: 'form',
                                items: [{xtype: 'textfield', fieldLabel: langs('Сумма НДС'), name: 'DocumentUcStr_SumNds'}]
                            }]
                        },
                        {xtype: 'textfield', fieldLabel: langs('Сумма с НДС') + ' (' + getCurrencyName() + ')', name: 'DocumentUcStr_NdsSum', allowBlank: false},
                        {
                            layout: 'form',
                            labelWidth: 153,
                            items: [{
                                xtype: 'textfield',
                                fieldLabel: langs('Причина списания'),
                                name: 'DocumentUcStr_Reason',
                                maxLength: 100,
                                anchor: '95%'
                            }]
                        }, {
                            xtype: 'button',
                            id: 'nduse_MiddleBtnBarCodeView',
                            text: 'Список штрих-кодов',
                            iconCls: 'add16',
                            handler: function() {
                                wnd.openBarCodeViewWindow();
                            }
                        }]
                    }, {
                        xtype: 'fieldset',
                        title: langs('Поставить на учет'),
                        id: 'NewDocumentUcStrEditPostFieldset',
                        autoHeight: true,
                        layout: 'form',
                        labelAlign: 'right',
                        style: 'margin: 0px 0px 10px 1px; padding-top: 0px; padding-bottom: 0px;',
                        bodyStyle:'background:#DFE8F6;padding:5px;',
                        items: [{
                            layout: 'form',
                            labelWidth: 153,
                            items: [
                                wnd.post_sz_combo
                            ]
                        }, {
                            layout: 'column',
                            items: [{
                                layout: 'form',
                                labelWidth: 153,
                                items: [
                                    wnd.post_gu_b_combo
                                ]
                            }, {
                                layout: 'form',
                                labelWidth: 116,
                                items: [
                                    wnd.post_gu_combo
                                ]
                            }]
                        }, {
                            layout: 'form',
                            labelWidth: 153,
                            items: [
                                {xtype: 'textfield', fieldLabel: langs('Коэф. пересчета'), name: 'PostRecount_Koef', disabled: true}
                            ]
                        }, {
                            layout: 'column',
                            items: [{
                                layout: 'form',
                                labelWidth: 153,
                                items: [
                                    {xtype: 'textfield', fieldLabel: langs('Кол-во (ед. уч.)'), name: 'PostDocumentUcStr_Count', disabled: true}
                                ]
                            }, {
                                layout: 'form',
                                labelWidth: 170,
                                items: [
                                    {xtype: 'textfield', fieldLabel: langs('Кол-во (ед. спис.)'), name: 'PostDocumentUcStr_EdCount', disabled: true}
                                ]
                            }]
                        }, {
                            layout: 'column',
                            items: [{
                                layout: 'form',
                                labelWidth: 153,
                                items: [
                                    {xtype: 'textfield', fieldLabel: langs('Цена') + ' (' + getCurrencyName() + ')', name: 'PostDocumentUcStr_Price', disabled: true}
                                ]
                            }, {
                                layout: 'form',
                                labelWidth: 170,
                                items: [
                                    {xtype: 'checkbox', fieldLabel: langs('НДС в том числе'), name: 'PostDocumentUcStr_IsNDS', disabled: true}
                                ]
                            }]
                        }, {
                            layout: 'column',
                            items: [{
                                layout: 'form',
                                labelWidth: 153,
                                items: [
                                    {xtype: 'textfield', fieldLabel: langs('Сумма') + ' (' + getCurrencyName() + ')', name: 'PostDocumentUcStr_Sum', disabled: true}
                                ]
                            }, {
                                layout: 'form',
                                labelWidth: 170,
                                items: [{
                                    xtype: 'swdrugndscombo',
                                    fieldLabel: langs('Ставка НДС'),
                                    name: 'PostDrugNds_id',
                                    hiddenName: 'PostDrugNds_id',
                                    width: 127,
                                    disabled: true
                                }]
                            }, {
                                layout: 'form',
                                labelWidth: 153,
                                items: [
                                    {xtype: 'textfield', fieldLabel: langs('Сумма НДС'), name: 'PostDocumentUcStr_SumNds', disabled: true}
                                ]
                            }, {
                                layout: 'form',
                                labelWidth: 170,
                                items: [
                                    {xtype: 'textfield', fieldLabel: langs('Сумма с НДС') + ' (' + getCurrencyName() + ')', name: 'PostDocumentUcStr_NdsSum', disabled: true}
                                ]
                            }]
                        }]
                    }, {
                        xtype: 'fieldset',
                        title: langs('Цена производителя или импортера'),
                        id: 'NewDocumentUcStrEditRegPriceFieldset',
                        autoHeight: true,
                        layout: 'form',
                        labelAlign: 'right',
                        style: 'margin: 0px 0px 10px 1px; padding-top: 0px; padding-bottom: 0px;',
                        bodyStyle:'background:#DFE8F6;padding:5px;',
                        items: [{
                            layout: 'column',
                            items: [{
                                layout: 'form',
                                labelWidth: 153,
                                items: [
                                    {xtype: 'numberfield', fieldLabel: langs('Цена (ед.уч.)'), name: 'DocumentUcStr_RegPrice', allowNegative: false, decimalPrecision: 2, listeners: {
                                        'change': function(field, newValue, oldValue) {
                                            wnd.setPrice('EdRegPrice');
                                        }
                                    }}
                                ]
                            }, {
                                layout: 'form',
                                labelWidth: 170,
                                items: [
                                    {xtype: 'numberfield', fieldLabel: langs('Цена (ед.спис.)'), name: 'DocumentUcStr_EdRegPrice', allowNegative: false, decimalPrecision: 2, listeners: {
                                        'change': function(field, newValue, oldValue) {
                                            wnd.setPrice('RegPrice');
                                        }
                                    }}
                                ]
                            }, {
                                layout: 'form',
                                labelWidth: 170,
                                items: [{
                                    xtype: 'swdatefield',
                                    fieldLabel: langs('Дата регистрации цены'),
                                    name: 'DocumentUcStr_RegDate',
                                    format: 'd.m.Y',
                                    width: 127,
                                    plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
                                }]
                            }]
                        }

                        ]
                    }
				]
			}, {
				xtype: 'fieldset',
				title: langs('Сертификат'),
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
                        xtype: 'textfield',
                        anchor: '100%',
                        fieldLabel: langs('№'),
                        name: 'DocumentUcStr_CertNum'
                    },
                    this.CertDateRange,
                    {
						xtype: 'textfield',
						fieldLabel: langs('Выдан'),
						name: 'DocumentUcStr_CertOrg',
						width: 479
					}, {
						xtype: 'swdruglabresultcombo',
						fieldLabel: langs('Рез.лаб.исслед.'),
						name: 'DrugLabResult_Name',
						forceSelection: false,
						width: 479
					}]
				}, {
					xtype: 'fieldset',
					title: langs('Файл'),
					autoHeight: true,
					items: [this.FileUploadPanel]
				}]
			}, {
				xtype: 'fieldset',
				title: 'Выполнение работ и операций',
                id: 'NewDocumentUcStrEditStorageWork',
				autoHeight: true,
				style: 'margin-left: 0.5em; margin-right: 0.5em; padding-top: 10px; padding-bottom: 10px;',
				labelAlign: 'right',
				items: [this.StorageWorkRows]
			}, {
                xtype: 'button',
                id: 'nduse_BottomBtnBarCodeView',
                text: 'Список штрих-кодов',
                iconCls: 'add16',
                handler: function() {
                    wnd.openBarCodeViewWindow();
                }
            }/*, {
                xtype: 'button',
                text: '�?зменения штрих-кодов',
                iconCls: 'search16',
                handler: function() {
                    swalert(wnd.BarCodeChangedData);
                }
            }*/]
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
			{
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
	}
});