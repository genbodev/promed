/**
* swWhsDocumentSupplyAdditionalEditWindow - окно редактирования дополнительного соглашения для гос. контракта
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Salakhov R.
* @version      05.2013
* @comment      
*/
sw.Promed.swWhsDocumentSupplyAdditionalEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['dopolnitelnoe_soglashenie_redaktirovanie'],
	layout: 'border',
	id: 'WhsDocumentSupplyAdditionalEditWindow',
	modal: true,
	shim: false,
	height: 505,
	width: 650,
	resizable: false,
	maximizable: false,
	maximized: true,
	onHide: Ext.emptyFn,
	loadSpecGrid: function(mode){
		var wnd = this;

		if (mode == 'saved_data') {
			wnd.SpecGrid.loadData({
				globalFilters: {
					WhsDocumentSupply_id: wnd.WhsDocumentSupply_id
				}
			});
		}
		if (mode == 'supply') {
			var supply_id = null;
			var idx = wnd.supply_combo.getStore().findBy(function(rec) { return rec.get('WhsDocumentUc_id') == wnd.supply_combo.getValue(); });
			if (idx > -1) {
				supply_id = wnd.supply_combo.getStore().getAt(idx).get('WhsDocumentSupply_id');
			}

			if (supply_id > 0) {
				wnd.SpecGrid.deleteAllRecords();
				wnd.SpecGrid.getLoadMask(lang['zagruzka_zapisey']).show();
				Ext.Ajax.request({
					url: wnd.SpecGrid.dataUrl,
					params: {
						WhsDocumentSupply_id: supply_id
					},
					callback: function (opt, success, response) {
						wnd.SpecGrid.getLoadMask().hide();
						if (success) {
							var store = wnd.SpecGrid.getGrid().getStore();
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj.length > 0 && store.getCount() == 1 && Ext.isEmpty(store.getAt(0).get('WhsDocumentSupplySpec_id'))) {
								wnd.SpecGrid.removeAll({ addEmptyRecord: false });
							}

							for(var i = 0; i < response_obj.length; i++) {
								response_obj[i].WhsDocumentSupplySpec_id = Math.floor(Math.random()*1000000); //генерируем временный идентификатор
								response_obj[i].state = 'add';
							}

							wnd.SpecGrid.getGrid().getStore().loadData(response_obj, true);
						}
					}
				});
			}
		}
	},
	doSign: function() {
		var wnd = this;

		// сначала сохраняем, потом подписываем.
		var options = new Object();
		options.onlySign = true;
		options.callback = function() {
			wnd.getLoadMask(lang['podojdite_idet_podpisanie']).show();
			Ext.Ajax.request({
				url: '?c=WhsDocumentSupply&m=signWhsDocumentSupplyAdditional',
				params: {
					WhsDocumentSupply_id: wnd.WhsDocumentSupply_id
				},
				failure: function() {
					wnd.getLoadMask().hide();
				},
				success: function(response, action) {
					wnd.getLoadMask().hide();
					if (response && response.responseText) {
						var answer = Ext.util.JSON.decode(response.responseText);
						if (answer.success) {
							Ext.Msg.alert(lang['soobschenie'], lang['dokument_uspeshno_podpisan']);
							wnd.setDisabled(true);
							wnd.callback(wnd.owner, wnd.WhsDocumentSupply_id);
						}
					} else {
						Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_podpisanii_otsutstvuet_otvet_servera']);
					}
				}
			});
		}

		if (confirm(lang['posle_podpisaniya_redaktirovanie_dokumenta_stanet_nedostupno_prodoljit'])) {
			this.doSave(options);
		}
	},
	doSave:  function(options) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		var wnd = this;
		if (!this.form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('WhsDocumentSupplyAdditionalEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		this.submit(options);
		return true;		
	},
	submit: function(options) {

		var wnd = this;
		wnd.getLoadMask(lang['podojdite_idet_sohranenie']).show();

		var params = new Object();
		params.WhsDocumentSupply_id = wnd.WhsDocumentSupply_id;
		params.WhsDocumentType_id = wnd.form.findField('WhsDocumentType_id').getValue();
		params.SupplySpecJSON = wnd.SpecGrid.getJSONChangedData();

		if (wnd.action == 'add') {
			params.WhsDocumentStatusType_Code = 1; // 1 - Новый
		}

		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
				if (typeof wnd.callback == 'function' ) {
					wnd.callback(wnd.owner, action.result.WhsDocumentSupply_id);
				}
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();

				if (action.result && action.result.WhsDocumentSupply_id > 0) {
					wnd.WhsDocumentSupply_id = action.result.WhsDocumentSupply_id;
				}

				if (!options.onlySign) {
					if (typeof wnd.callback == 'function') {
						wnd.callback(wnd.owner, wnd.WhsDocumentSupply_id);
					}
					wnd.hide();
				} else { //если окно после сохранения не закрывается нужно принудительно перезагрузить грид
                    wnd.SpecGrid.removeAll();
                    wnd.loadSpecGrid('saved_data');
                }

				if (typeof options.callback == 'function' ) {
					options.callback();
				}
			}
		});
	},
	setDisabled: function(disable) {
		var wnd = this;
		var field_arr = ['WhsDocumentUc_pid', 'WhsDocumentUc_Num', 'WhsDocumentUc_Name', 'WhsDocumentUc_Date'];
		for (var i in field_arr) if (wnd.form.findField(field_arr[i])) {
			if (disable)
				wnd.form.findField(field_arr[i]).disable();
			else
				wnd.form.findField(field_arr[i]).enable();
		}

		this.SpecGrid.setReadOnly(disable);

		if (disable) {
			wnd.buttons[0].disable();
			wnd.buttons[1].disable();
		} else {
			wnd.buttons[0].enable();
			wnd.buttons[1].enable();
		}
	},
	setAction: function(action) {
		var title = "Дополнительное соглашение";

		if (!Ext.isEmpty(action)) {
			this.action = action;
		}

		switch (this.action) {
			case 'add':
				this.setTitle(title + lang['_dobavlenie']);
				this.setDisabled(false);
				break;
			case 'edit':
				this.setTitle(title + lang['_redaktirovanie']);
				this.setDisabled(false);
				break;
			case 'view':
				this.setTitle(title + lang['_prosmotr']);
				this.setDisabled(true);
				break;
		}
	},
	show: function() {
        var wnd = this;
		sw.Promed.swWhsDocumentSupplyAdditionalEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.WhsDocumentSupply_id = null;
		this.dataState = null;
		this.ARMType = null;
		this.ImportDT_Exists = false;
		this.ImportDT = '';

        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
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
		if ( arguments[0].WhsDocumentSupply_id ) {
			this.WhsDocumentSupply_id = arguments[0].WhsDocumentSupply_id;
		}
		if ( arguments[0].ARMType ) {
			this.ARMType = arguments[0].ARMType;
		}

		this.form.reset();
		this.SpecGrid.removeAll();

		wnd.supply_combo.getStore().baseParams.WhsDocumentStatusType_Code =	2; //2 - Действующий.
		wnd.supply_combo.getStore().baseParams.WhsDocumentType_CodeList = '1,3,6'; //1 - Договор поставки; 3 - Контракт на поставку; 6 - Контракт на поставку и отпуск
		wnd.supply_combo.getStore().baseParams.OrgFilter_Type = null; //Тип фильтра по организации
        wnd.supply_combo.getStore().baseParams.OrgFilter_Org_cid = null; //Заказчик
        wnd.supply_combo.getStore().baseParams.OrgFilter_Org_pid = null; //Плательщик

        switch(this.ARMType) {
            //case '': //АРМ Заведующего аптекой МО - пока не реализован (#88871)
            case 'hn': //АРМ Главной медсестры МО
                wnd.supply_combo.getStore().baseParams.OrgFilter_Type = 'or';
                wnd.supply_combo.getStore().baseParams.OrgFilter_Org_cid = getGlobalOptions().org_id;
                wnd.supply_combo.getStore().baseParams.OrgFilter_Org_pid = getGlobalOptions().org_id;
                break;
            case 'minzdravdlo': //АРМ Специалиста ЛЛО ОУЗ
            case 'adminllo': //АРМ Администратора ЛЛО
                wnd.supply_combo.getStore().baseParams.OrgFilter_Type = 'and';
                wnd.supply_combo.getStore().baseParams.OrgFilter_Org_cid = getGlobalOptions().minzdrav_org_id;
                wnd.supply_combo.getStore().baseParams.OrgFilter_Org_pid = getGlobalOptions().minzdrav_org_id;
                break;
        }

        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();

		switch (this.action) {
			case 'add':
				wnd.setAction();

				var type_combo = wnd.form.findField('WhsDocumentType_id');
				var idx = type_combo.getStore().findBy(function(rec) { return rec.get('WhsDocumentType_Code') == 13; }); //13 - Доп соглашение.
				if (idx > -1) {
					type_combo.setValue(type_combo.getStore().getAt(idx).get('WhsDocumentType_id'));
				}

				wnd.form.findField('WhsDocumentUc_Num').focus(true, 500);
				loadMask.hide();
			break;
			case 'edit':
			case 'view':
				Ext.Ajax.request({
					params:{
						WhsDocumentSupply_id: wnd.WhsDocumentSupply_id
					},
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						wnd.hide();
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) {
							return false
						}

						wnd.form.setValues(result[0]);
						if (!Ext.isEmpty(result[0].WhsDocumentUc_pid)) {
							wnd.supply_combo.setValueById(result[0].WhsDocumentUc_pid);
						}
						wnd.ImportDT_Exists = (result[0].ImportDT_Exists == 'true');
						wnd.ImportDT = result[0].ImportDT;
						wnd.loadSpecGrid('saved_data');
						wnd.form.findField('ImportDT').hideContainer();
						wnd.form.findField('ImportDT').setValue('');
						if (false && !Ext.isEmpty(result[0].WhsDocumentStatusType_Code) && result[0].WhsDocumentStatusType_Code == 2) { // 2 - Действующий
							wnd.setAction('view');
						} else {
							wnd.setAction();
							if(wnd.ImportDT_Exists) //Дизаблим всё, кроме наименования
							{
								wnd.form.findField('WhsDocumentType_id').disable();
								wnd.supply_combo.disable();
								wnd.form.findField('WhsDocumentUc_Num').disable();
								wnd.form.findField('WhsDocumentUc_Date').disable();
								wnd.form.findField('ImportDT').showContainer();
								wnd.form.findField('ImportDT').setValue(wnd.ImportDT);
								wnd.SpecGrid.setActionDisabled('action_add',true);
							}
							else
							{
								wnd.form.findField('WhsDocumentType_id').enable();
								wnd.supply_combo.enable();
								wnd.form.findField('WhsDocumentUc_Num').enable();
								wnd.form.findField('WhsDocumentUc_Date').enable();
								wnd.SpecGrid.setActionDisabled('action_add',false);
							}
						}
						wnd.form.findField('WhsDocumentUc_Num').focus(true, 500);
						wnd.dataState = 'loaded';
						loadMask.hide();
					},
					url:'/?c=WhsDocumentSupply&m=loadWhsDocumentSupplyAdditional'
				});
			break;	
		}
	},
	initComponent: function() {
		var wnd = this;

		wnd.supply_combo = new sw.Promed.SwBaseRemoteCombo({
			width: 415,
			allowBlank: true,
			enableKeyEvents: true,
			fieldLabel: lang['kontrakt'],
			forceSelection: false,
			hiddenName: 'WhsDocumentUc_pid',
			valueField: 'WhsDocumentUc_id',
			displayField: 'WhsDocumentUc_Num',
			loadingText: lang['idet_poisk'],
			queryDelay: 250,
			minChars: 1,
			minLength: 1,
			mode: 'remote',
			trigger2Class: 'x-form-search-trigger',
			resizable: true,
			selectOnFocus: true,
			triggerAction: '',
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'<table style="width:100%;border: 0;"><td style="width:80%;"><h3>{WhsDocumentUc_Num}</h3></td><td style="width:20%;"></td></tr></table>',
				'</div></tpl>'
			),
			listeners: {
				select: function(combo, record) {
					if (wnd.action == 'add' || wnd.dataState == 'loaded') {
						wnd.loadSpecGrid('supply');
					}
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
							WhsDocumentCostItemType_id: data.WhsDocumentCostItemType_id,
							DrugNds_Code: data.DrugNds_Code,
							WhsDocumentProcurementRequest_id: data.WhsDocumentProcurementRequest_id
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

						getWnd(searchWindow).hide();
					}
				});
			},
			setValueById: function(document_id) {
				var combo = this;
				combo.store.baseParams.WhsDocumentUc_id = document_id;
				combo.store.load({
					callback: function(){
						combo.setValue(document_id);
						combo.store.baseParams.WhsDocumentUc_id = null;
					}
				});
			},
			initComponent: function() {
				sw.Promed.SwBaseRemoteCombo.prototype.initComponent.apply(this, arguments);
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
							{name: 'WhsDocumentCostItemType_id', mapping: 'WhsDocumentCostItemType_id'},
							{name: 'DrugNds_Code', mapping: 'DrugNds_Code'},
							{name: 'WhsDocumentProcurementRequest_id', mapping: 'WhsDocumentProcurementRequest_id'}
						]),
					url: '/?c=Farmacy&m=loadWhsDocumentSupplyList'
				});
			}
		});

		this.SpecGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function() { wnd.SpecGrid.editGrid('add') }},
				{name: 'action_edit', handler: function() { wnd.SpecGrid.editGrid('edit') }},
				{name: 'action_view', handler: function() { wnd.SpecGrid.editGrid('view') }},
				{name: 'action_delete', handler: function() { wnd.SpecGrid.deleteRecord() }},
				{name: 'action_refresh', hidden: true},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=WhsDocumentSupplySpec&m=loadList',
			height: 180,
			region: 'center',
			object: 'WhsDocumentSupplySpec',
			editformclassname: 'swWhsDocumentSupplySpecEditWindow',
			id: 'wdsaeWhsDocumentSupplySpecGrid',
			paging: false,
			style: 'margin-bottom: 0px',
			stringfields: [
				{name: 'WhsDocumentSupplySpec_id', type: 'int', header: 'ID', key: true},
				{name: 'state', type: 'string', header: 'state', hidden: !this.debug_mode},
				{name: 'WhsDocumentSupply_id', type: 'int', hidden: !this.debug_mode},
				{name: 'Drug_id', type: 'int', header: 'Drug_id', hidden: !this.debug_mode},
				{name: 'DrugComplexMnn_id', type: 'int', header: 'DrugComplexMnn_id', hidden: !this.debug_mode},
				{name: 'FIRMNAMES_id', type: 'int', header: 'FIRMNAMES_id', hidden: !this.debug_mode},
				{name: 'DRUGPACK_id', type: 'int', header: 'DRUGPACK_id', hidden: !this.debug_mode},
				{name: 'Okei_id', type: 'int', header: 'Okei_id', hidden: !this.debug_mode},
				{name: 'graph_data', type: 'string', header: 'graph_data', hidden: true},

				{name: 'WhsDocumentSupplySpec_KolvoForm', type: 'float', header: lang['kolichestvo_edinits_form_vyipuska_v_upakovke'], hidden: !this.debug_mode, width: 120},
				{name: 'Okei_id_Name', type: 'string', header: lang['edinitsa_postavki_okei'], hidden: !this.debug_mode, width: 120},
				{name: 'WhsDocumentSupplySpec_KolvoMin', type: 'float', header: lang['kolichestvo_postavlyaemyih_minimalnyih_upakovok'], hidden: !this.debug_mode, width: 120},
				{name: 'WhsDocumentSupplySpec_ShelfLifePersent', type: 'int', header: lang['ostatochnyiy_srok_hraneniya_ne_menee_%'], hidden: true, width: 120},

				{name: 'WhsDocumentSupplySpec_PosCode', type: 'float', header: lang['№_p_p'], width: 50},
				{name: 'DrugNomen_Code', type: 'string', header: lang['kod'], width: 50},
				{name: 'Drug_Name', type: 'string', hidden: true},
				{name: 'Actmatters_id', type: 'int', hidden: true},
				{name: 'ActMatters_RusName', type: 'string', header: langs('МНН'), width: 125},
				{name: 'Tradename_Name', type: 'string', header: langs('Торг. наименование'), width: 125},
				{name: 'Firm_Name', type: 'string', header: langs('Производитель'), width: 125},
				{name: 'Reg_Num', type: 'string', header: langs('№ РУ'), width: 100},
				{name: 'DrugForm_Name', type: 'string', header: langs('Форма выпуска'), width: 100},
				{name: 'Drug_Dose', type: 'string', header: langs('Дозировка'), width: 75},
				{name: 'Drug_Fas', type: 'string', header: langs('Фасовка'), width: 75},

				{name: 'WhsDocumentSupplySpec_KolvoUnit', type: 'float', header: lang['kol-vo_up'], width: 75},
				{name: 'WhsDocumentSupplySpec_Price', type: 'float', header: lang['opt_tsena_bez_nds'], width: 120},
				{name: 'WhsDocumentSupplySpec_NDS', type: 'float', header: 'НДС', width: 50}, //ставка НДС (%);
				{name: 'WhsDocumentSupplySpec_PriceNDS', type: 'float', header: 'Цена с НДС', width: 100}, //опт. цена с НДС;
				{name: 'WhsDocumentSupplySpec_SumNDS', type: 'float', header: 'Сумма с НДС', width: 100}, //сумма с НДС;
				{name: 'calc_dose_count', type: 'string', header: 'Кол-во доз', hidden: !this.debug_mode}, //кол-во доз - кол-во доз=кол-во уп.*кол-во доз в упковке);
				{name: 'calc_material_count', type: 'string', header: 'Кол-во действ.в-ва', hidden: !this.debug_mode}, //кол-во действ.в-ва  - кол-во действ.в-ва в единице фасовки*кол-во ед.фасовки в упаковке*кол-во упаковок);
                {name: 'WhsDocumentSupplySpec_SuppPrice', type: 'string', hidden: true}
			],
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('WhsDocumentSupplySpec_id') > 0) {
					this.ViewActions.action_edit.setDisabled(this.readOnly);
					this.ViewActions.action_view.setDisabled(false);
					this.ViewActions.action_delete.setDisabled(this.readOnly);
				} else {
					this.ViewActions.action_edit.setDisabled(true);
					this.ViewActions.action_view.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
				}
				if(wnd.ImportDT_Exists)
				{
					this.ViewActions.action_edit.setDisabled(true);
					this.ViewActions.action_view.setDisabled(false);
					this.ViewActions.action_delete.setDisabled(true);
				}
			},
			updateSpecSumm: function(force_update) {
				var view_frame = this;

				view_frame.getGrid().getStore().each(function(record){
					if (record.get('WhsDocumentSupplySpec_id') > 0 && (force_update || record.get('WhsDocumentSupplySpec_NDS') != wnd.nds)) {
						if (record.get('state') != 'add') {
							record.set('state', 'edit');
						}

						var cnt = record.get('WhsDocumentSupplySpec_KolvoUnit') > 0 ? record.get('WhsDocumentSupplySpec_KolvoUnit')*1 : 0;
						var sum = 0;
						var sum_nds = 0;
						var nds = wnd.nds > 0 ? wnd.nds*1 : 0;
						var cost = record.get('WhsDocumentSupplySpec_Price') > 0 ? record.get('WhsDocumentSupplySpec_Price')*1 : 0;
						var cost_nds = 0;

						sum = Math.round(cnt * cost * 100)/100;
						sum_nds = Math.round((cnt * cost * ((100+nds)/100))*100)/100;
						cost_nds = Math.round((cost * ((100+nds)/100))*100)/100;

						if (force_update) {
							record.set('WhsDocumentSupplySpec_Price', record.get('WhsDocumentSupplySpec_Price').toFixed(2));
						}
						record.set('WhsDocumentSupplySpec_NDS', nds);
						record.set('WhsDocumentSupplySpec_SumNDS', sum_nds);
						record.set('WhsDocumentSupplySpec_PriceNDS', cost_nds);
						record.commit();
					}
				});
			},
			editGrid: function (action) {
				var supply_record = new Object();

				if (wnd.supply_combo.getValue() > 0) {
					var idx = wnd.supply_combo.getStore().findBy(function(rec) { return rec.get('WhsDocumentUc_id') == wnd.supply_combo.getValue(); });
					if (idx > -1) {
						supply_record = wnd.supply_combo.getStore().getAt(idx);
					}
				}

				if (Ext.isEmpty(supply_record.data)) {
					Ext.Msg.alert(lang['soobschenie'], lang['neobhodimo_vyibrat_kontrakt']);
					return false;
				}

				if (action == null)	action = 'add';

				var view_frame = this;
				var store = view_frame.getGrid().getStore();

				if (action == 'add') {
					var record_count = store.getCount();
					if ( record_count == 1 && !store.getAt(0).get('WhsDocumentSupplySpec_id') ) {
						view_frame.removeAll({ addEmptyRecord: false });
						record_count = 0;
					}

					var params = new Object();
					params.WhsDocumentProcurementRequest_id = supply_record.get('WhsDocumentProcurementRequest_id');
					params.WhsDocumentSupplySpec_PosCode = record_count + 1;
					params.WhsDocumentUc_Date = wnd.form.findField('WhsDocumentUc_Date').getValue();
					params.WhsDocumentSupplySpec_NDS = supply_record.get('DrugNds_Code');

					getWnd(view_frame.editformclassname).show({
						action: action,
						params: params,
						callback: function(data) {
							if ( record_count == 1 && !store.getAt(0).get('WhsDocumentSupplySpec_id') ) {
								view_frame.removeAll({ addEmptyRecord: false });
							}
							var record = new Ext.data.Record.create(view_frame.jsonData['store']);
							view_frame.clearFilter();
							data.WhsDocumentSupplySpec_id = Math.floor(Math.random()*1000000); //генерируем временный идентификатор
							data.state = 'add';
							store.insert(record_count, new record(data));
							store.sort('WhsDocumentSupplySpec_PosCode','ASC');
							view_frame.setFilter();
						}
					});
				}
				if (action == 'edit' || action == 'view') {
					var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
					if (selected_record.get('WhsDocumentSupplySpec_id') > 0) {
						var params = selected_record.data;
						params.WhsDocumentProcurementRequest_id = supply_record.get('WhsDocumentProcurementRequest_id');
						params.WhsDocumentUc_Date = wnd.form.findField('WhsDocumentUc_Date').getValue();

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
								store.sort('WhsDocumentSupplySpec_PosCode','ASC');
								view_frame.setFilter();
							}
						});
					}
				}
			},
			deleteRecord: function(){
				var view_frame = this;
				var selected_record = view_frame.getGrid().getSelectionModel().getSelected();

                sw.swMsg.show({
                    icon: Ext.MessageBox.QUESTION,
                    msg: lang['vyi_hotite_udalit_zapis'],
                    title: lang['podtverjdenie'],
                    buttons: Ext.Msg.YESNO,
                    fn: function(buttonId, text, obj) {
                        if ('yes' == buttonId) {
                            if (selected_record.get('state') == 'add') {
                                view_frame.getGrid().getStore().remove(selected_record);
                            } else {
                                selected_record.set('state', 'delete');
                                selected_record.commit();
                                view_frame.setFilter();
                            }
                        }
                    }
                });
			},
			deleteAllRecords: function(){
				var view_frame = this;
				view_frame.getGrid().getStore().each(function(record) {
					if (record.get('state') == 'add') {
						view_frame.getGrid().getStore().remove(record);
					} else if (record.get('WhsDocumentSupplySpec_id') > 0) {
						record.set('state', 'delete');
						record.commit();
					}
				});
				view_frame.setFilter();
			},
			getChangedData: function(){ //возвращает новые и измненные показатели
				var data = new Array();
				this.clearFilter();
				this.getGrid().getStore().each(function(record) {
					if ((record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete' || record.data.graph_data != ''))
						data.push(record.data);
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

		var form = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			region: 'north',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'WhsDocumentSupplyAdditionalEditForm',
				border: true,
				labelWidth: 140,
				url:'/?c=WhsDocumentSupply&m=saveWhsDocumentSupplyAdditional',
				items: [{
					name: 'WhsDocumentUc_id',
					xtype: 'hidden'
				}, {
					name: 'WhsDocumentStatusType_id',
					xtype: 'hidden'
				}, {
					xtype: 'swcommonsprcombo',
					comboSubject: 'WhsDocumentType',
					hiddenName: 'WhsDocumentType_id',
					disabled: true,
					fieldLabel: lang['tip'],
					width: 415
				},
				this.supply_combo,
				{
					xtype: 'textfield',
					name: 'WhsDocumentUc_Num',
					fieldLabel: lang['№'],
					maxLength: 50,
					allowBlank: false,
					width: 415
				}, {
					xtype: 'textarea',
					name: 'WhsDocumentUc_Name',
					fieldLabel: lang['naimenovanie'],
					maxLength: 100,
					allowBlank: false,
					width: 415
				}, {
					xtype: 'swdatefield',
					name: 'WhsDocumentUc_Date',
					fieldLabel: lang['data'],
					allowBlank: false,
					width: 100
				},
				{
					xtype: 'textfield',
					name: 'ImportDT',
					disabled: true,
					fieldLabel: 'Дата и время импорта',
					maxLength: 50,
					allowBlank: false,
					width: 215
				}]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.ownerCt.doSign();
				},
				iconCls: 'ok16',
				text: lang['podpisat']
			}, {
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
				form,
				this.SpecGrid
			]
		});
		sw.Promed.swWhsDocumentSupplyAdditionalEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('WhsDocumentSupplyAdditionalEditForm').getForm();
	}	
});