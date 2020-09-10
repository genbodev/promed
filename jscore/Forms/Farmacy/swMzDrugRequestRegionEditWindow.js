/**
* swMzDrugRequestRegionEditWindow - окно редактирования заявки для региона
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Rustam Salakhov
* @version      10.2012
* @comment      
*/
sw.Promed.swMzDrugRequestRegionEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['zayavochnaya_kampaniya_redaktirovanie'],
	layout: 'border',
	id: 'MzDrugRequestRegionEditWindow',
	modal: true,
	shim: false,
	width: 700,
	height: 200,
	resizable: false,
	maximizable: false,
	maximized: true,
	default_name: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	setAllowBlankFields: function() {
		var wnd = this;
		var person_register_nick = null;
		var field = wnd.form.findField('PersonRegisterType_id');
		var field_val = wnd.form.findField('PersonRegisterType_id').getValue();
		var drug_group_field = wnd.form.findField('DrugGroup_id');
		var personQuota = wnd.form.findField('DrugRequestQuota_Person');
		var personQuotaFed = wnd.form.findField('DrugRequestQuota_PersonFed');
		var personQuotaReg = wnd.form.findField('DrugRequestQuota_PersonReg');
		var totalQuota = wnd.form.findField('DrugRequestQuota_Total');
		var totalQuotaFed = wnd.form.findField('DrugRequestQuota_TotalFed');
		var totalQuotaReg = wnd.form.findField('DrugRequestQuota_TotalReg');
		var allow_blank = false;

		if (field.getValue() > 0) {
			var idx = field.getStore().findBy(function(record) {
                return record.get('PersonRegisterType_id') == field.getValue();
            });
			if (idx > -1) {
				person_register_nick = field.getStore().getAt(idx).get('PersonRegisterType_SysNick');
			}

			drug_group_field.setAllowBlank(true);
			drug_group_field.setValue(null);
			drug_group_field.ownerCt.hide();
		} else {
			drug_group_field.setAllowBlank(false);
			drug_group_field.ownerCt.show();
		}

		allow_blank = (person_register_nick && person_register_nick.indexOf("common_") > -1);

		if(field_val == 1){
			wnd.list_combo.setAllowBlank(true);
			wnd.list_combo_fed.setAllowBlank(false);
			wnd.list_combo_reg.setAllowBlank(false);
			wnd.list_combo.hideContainer();
			wnd.list_combo_fed.showContainer();
			wnd.list_combo_reg.showContainer();
			if(wnd.action != 'view'){
				wnd.list_combo.disable();
				wnd.list_combo_fed.enable();
				wnd.list_combo_reg.enable();
			}

			personQuota.hideContainer();
			personQuota.setValue('');
			personQuotaFed.showContainer();
			personQuotaReg.showContainer();

			totalQuota.hideContainer();
			totalQuota.setValue('');
			totalQuotaFed.showContainer();
			totalQuotaReg.showContainer();
		} else {
			wnd.list_combo.setAllowBlank(false);
			wnd.list_combo_fed.setAllowBlank(true);
			wnd.list_combo_reg.setAllowBlank(true);
			wnd.list_combo.showContainer();
			wnd.list_combo_fed.hideContainer();
			wnd.list_combo_reg.hideContainer();
			if(wnd.action != 'view'){
				wnd.list_combo.enable();
				wnd.list_combo_fed.disable();
				wnd.list_combo_reg.disable();
			}
			personQuota.showContainer();
			personQuotaFed.hideContainer();
			personQuotaReg.hideContainer();
			personQuotaFed.setValue('');
			personQuotaReg.setValue('');

			totalQuota.showContainer();
			totalQuotaFed.hideContainer();
			totalQuotaReg.hideContainer();
			totalQuotaFed.setValue('');
			totalQuotaReg.setValue('');
		}
		//wnd.form.findField('DrugRequestProperty_id').setAllowBlank(allow_blank);
		//wnd.form.findField('DrugRequestQuota_Person').setAllowBlank(allow_blank);
		//wnd.form.findField('DrugRequestQuota_Total').setAllowBlank(allow_blank);
	},
	setDefaultName: function() {
		var wnd = this;
		var name = "";
		var field = null;
		var idx = null;
		var kind_name = null;
		var period_name = null;
		var person_register_name = null;
		var person_register_nick = null;
		var drug_group_name = null;
		var postfix = null;

		field = wnd.form.findField('PersonRegisterType_id');
		if (field.getValue() > 0) {
			idx = field.getStore().findBy(function(record) {
                return record.get('PersonRegisterType_id') == field.getValue();
            });
			if (idx > -1) {
				person_register_name = field.getStore().getAt(idx).get('PersonRegisterType_Name');
				person_register_nick = field.getStore().getAt(idx).get('PersonRegisterType_SysNick');
				if (person_register_nick.indexOf("common_") > -1) {
					person_register_name = person_register_name.replace(": общетерапевтическая группа", "");
				}
			}
		}

		field = wnd.form.findField('DrugRequestPeriod_id');
		if (field.getValue() > 0) {
			idx = field.getStore().findBy(function(record) {
                return record.get('DrugRequestPeriod_id') == field.getValue();
            });
			if (idx > -1) {
				period_name = field.getStore().getAt(idx).get('DrugRequestPeriod_Name');
			}
		}

		field = wnd.form.findField('DrugRequestKind_id');
		if (field.getValue() > 0) {
			idx = field.getStore().findBy(function(record) {
                return record.get('DrugRequestKind_id') == field.getValue();
            });
			if (idx > -1) {
				kind_name = field.getStore().getAt(idx).get('DrugRequestKind_Name');
			}
		}
		
		field = wnd.form.findField('DrugGroup_id');
		if (field.getValue() > 0) {
			idx = field.getStore().findBy(function(record) {
                return record.get('DrugGroup_id') == field.getValue();
            });
			if (idx > -1) {
				drug_group_name = field.getStore().getAt(idx).get('DrugGroup_Name');
			}
		}

		if (person_register_nick == 'common_rl') {
			postfix = "(890 постановление РФ)";
		}

		if (person_register_nick == 'common_fl') {
			postfix = "(178-ФЗ от 17.07.1999г.)";
		}

		//непосредственнное формирование наименования

		if (!Ext.isEmpty(person_register_name)) {
			name = "Льготная ";
		}

		if (kind_name != null) {
			name += kind_name
		}

		if (!Ext.isEmpty(name)) {
			name += " заявка";
		} else {
			name = "Заявка";
		}

		name += " на лекарственные препараты";

		if (person_register_name != null) {
			name += ' ' + lang['po'] + ' ' + person_register_name;
		}

		if (!Ext.isEmpty(drug_group_name) && drug_group_name != "Все") {
			name += ' ' + lang['po'] + ' ' + drug_group_name;
		}

		if (period_name != null) {
			name += ' ' + lang['na'] + ' ' + period_name;
		}

		if (postfix != null) {
			name += " " + postfix;
		}

		wnd.form.findField('DrugRequest_Name').setValue(name);
	},
	setListFilter: function(options) {
		var wnd = this;
		var period_id = wnd.form.findField('DrugRequestPeriod_id').getValue() || null;
		var person_register_id = wnd.form.findField('PersonRegisterType_id').getValue() || null;

		wnd.list_combo.getStore().clearFilter();
		wnd.list_combo.lastQuery = '';
		
		wnd.list_combo.getStore().filterBy(function(record){
			return ((!period_id || record.get('DrugRequestPeriod_id') == period_id) && (!person_register_id || record.get('PersonRegisterType_id') == person_register_id));
		});

        var idx = wnd.list_combo.getStore().findBy(function(record) {
            return record.get('DrugRequestProperty_id') == wnd.list_combo.getValue();
        });
		if (idx < 0) {
			wnd.list_combo.clearValue();
		}

		// fed

		wnd.list_combo_fed.getStore().clearFilter();
		wnd.list_combo_fed.lastQuery = '';

		wnd.list_combo_fed.getStore().filterBy(function(record){
			return ((!period_id || record.get('DrugRequestPeriod_id') == period_id) && (!person_register_id || record.get('PersonRegisterType_id') == 1) && record.get('DrugFinance_id') == 3);
		});

		if(options && options.reg) {
			wnd.list_combo_fed.getStore().clearFilter();
			wnd.list_combo_fed.getStore().filterBy(function(record){
				return (
					(!period_id || record.get('DrugRequestPeriod_id') == period_id) 
					&& (!person_register_id || record.get('PersonRegisterType_id') == 1) 
					&& record.get('DrugFinance_id') == 3
					&& record.get('DrugRequestProperty_Org') == options.reg
				);
			});
		}

        var idxf = wnd.list_combo_fed.getStore().findBy(function(record) {
            return record.get('DrugRequestProperty_id') == wnd.list_combo_fed.getValue();
        });
		if (idxf < 0) {
			wnd.list_combo_fed.clearValue();
		}

		// reg

		wnd.list_combo_reg.getStore().clearFilter();
		wnd.list_combo_reg.lastQuery = '';
		
		wnd.list_combo_reg.getStore().filterBy(function(record){
			return ((!period_id || record.get('DrugRequestPeriod_id') == period_id) && (!person_register_id || record.get('PersonRegisterType_id') == 1) && record.get('DrugFinance_id') == 27);
		});

		if(options && options.fed) {
			wnd.list_combo_reg.getStore().clearFilter();
			wnd.list_combo_reg.getStore().filterBy(function(record){
				return (
					(!period_id || record.get('DrugRequestPeriod_id') == period_id) 
					&& (!person_register_id || record.get('PersonRegisterType_id') == 1) 
					&& record.get('DrugFinance_id') == 27
					&& record.get('DrugRequestProperty_Org') == options.fed
				);
			});
		}

        var idxr = wnd.list_combo_reg.getStore().findBy(function(record) {
            return record.get('DrugRequestProperty_id') == wnd.list_combo_reg.getValue();
        });
		if (idxr < 0) {
			wnd.list_combo_reg.clearValue();
		}
	},
	setMedPersonalGridParams: function() {
		var wnd = this;
		var period_combo = wnd.form.findField('DrugRequestPeriod_id');
		var period_id = period_combo.getValue();

		wnd.MedPersonalGrid.params.begDate = null;
		wnd.MedPersonalGrid.params.endDate = null;
		wnd.MedPersonalGrid.params.PersonRegisterType_id = wnd.form.findField('PersonRegisterType_id').getValue();
		if (period_id) {
			var idx = period_combo.getStore().findBy(function(record) {
                return record.get('DrugRequestPeriod_id') == period_id;
            });
			if (idx >= 0) {
				var record = period_combo.getStore().getAt(idx);
				wnd.MedPersonalGrid.params.begDate = record.get('DrugRequestPeriod_begDate');
				wnd.MedPersonalGrid.params.endDate = record.get('DrugRequestPeriod_endDate');
			}
		}
	},
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('mdrreMzDrugRequestRegionEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
        this.submit();
		return true;		
	},
	submit: function() {
		var wnd = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		//loadMask.show();
		var params = new Object();
		params.action = wnd.action;
		params.MedPersonalList_JsonData = wnd.MedPersonalGrid.getJSONChangedData();
		params.DrugRequestPeriod_id = wnd.form.findField('DrugRequestPeriod_id').getValue();
		params.PersonRegisterType_id = wnd.form.findField('PersonRegisterType_id').getValue();
		params.DrugRequestKind_id = wnd.form.findField('DrugRequestKind_id').getValue();
		params.DrugGroup_id = wnd.form.findField('DrugGroup_id').getValue();
		params.DrugRequestProperty_id = wnd.form.findField('DrugRequestProperty_id').getValue();
		params.DrugRequestPropertyFed_id = wnd.form.findField('DrugRequestPropertyFed_id').getValue();
		params.DrugRequestPropertyReg_id = wnd.form.findField('DrugRequestPropertyReg_id').getValue();
		params.DrugRequestQuota_Person = wnd.form.findField('DrugRequestQuota_Person').getValue();
		params.DrugRequestQuota_PersonFed = wnd.form.findField('DrugRequestQuota_PersonFed').getValue();
		params.DrugRequestQuota_PersonReg = wnd.form.findField('DrugRequestQuota_PersonReg').getValue();
		params.DrugRequestQuota_Total = wnd.form.findField('DrugRequestQuota_Total').getValue();
		params.DrugRequestQuota_TotalFed = wnd.form.findField('DrugRequestQuota_TotalFed').getValue();
		params.DrugRequestQuota_TotalReg = wnd.form.findField('DrugRequestQuota_TotalReg').getValue();
		params.DrugRequestQuota_IsPersonalOrderObligatory = wnd.form.findField('DrugRequestQuota_IsPersonalOrderObligatory').getValue() ? 1: 0;

		this.form.submit({
			params: params,
			failure: function(result_form, action) 
			{
				//loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action)
			{
				//loadMask.hide();
				wnd.callback(wnd.owner, action.result.DrugRequest_id);
				wnd.hide();
			}
		});
	},
	setDisabled: function(disable) {
		var wnd = this;		
		var field_arr = new Array();
		
		field_arr = ['PersonRegisterType_id', 'DrugRequestPeriod_id'/*, 'DrugGroup_id'*/, 'DrugRequestKind_id'];
		for (var i in field_arr) if (wnd.form.findField(field_arr[i])) {
			if (disable || wnd.action != 'add')
				wnd.form.findField(field_arr[i]).disable();
			else
				wnd.form.findField(field_arr[i]).enable();
		}
		
		field_arr = [
			'DrugRequest_Name',
			'DrugRequestQuota_Person',
			'DrugRequestQuota_PersonFed',
			'DrugRequestQuota_PersonReg',
			'DrugRequestQuota_Total',
			'DrugRequestQuota_TotalFed',
			'DrugRequestQuota_TotalReg',
			'DrugRequestQuota_IsPersonalOrderObligatory',
			'DrugRequestProperty_id',
			'DrugRequestPropertyFed_id',
			'DrugRequestPropertyReg_id'
		];
		for (var i in field_arr) if (wnd.form.findField(field_arr[i])) {
			if (disable)
				wnd.form.findField(field_arr[i]).disable();
			else
				wnd.form.findField(field_arr[i]).enable();
		}
		
		if (disable) {
			wnd.buttons[0].disable();
		} else {
			wnd.buttons[0].enable();
		}
		
		wnd.MedPersonalGrid.setReadOnly(disable);
	},
	show: function() {
        var wnd = this;
        var region_nick = getRegionNick();
		sw.Promed.swMzDrugRequestRegionEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.DrugRequest_id = null;
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
		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}
		if ( arguments[0].DrugRequest_id ) {
			this.DrugRequest_id = arguments[0].DrugRequest_id;
		}
		wnd.MedPersonalGrid.removeAll();
		wnd.setTitle(lang['zayavochnaya_kampaniya']);
		
		wnd.TabPanel.setActiveTab(1);
		wnd.TabPanel.setActiveTab(0);
		
		this.form.reset();
		this.form.findField('PersonRegisterType_id').setValue(null);
		this.form.findField('DrugRequestKind_id').setValue(1); //Плановая
		this.setAllowBlankFields();
		
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		wnd.SearchPanel.doSearch(true);

		wnd.setDisabled(wnd.action == 'view');

		switch (arguments[0].action) {
			case 'add':
				wnd.setTitle(wnd.title + lang['_dobavlenie']);
				wnd.default_name = true;
				if (region_nick == 'ufa') {
                    this.form.findField('DrugRequestQuota_IsPersonalOrderObligatory').setValue(1);
				}
				this.list_combo.getStore().load();
				this.list_combo_fed.getStore().load();
				this.list_combo_reg.getStore().load();
				wnd.setMedPersonalGridParams();
				loadMask.hide();
			break;
			case 'edit':
			case 'view':
				wnd.setTitle(wnd.title + ': ' + (wnd.action == 'edit' ? lang['_redaktirovanie'] : lang['_prosmotr']));
				wnd.default_name = false;
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						wnd.hide();
					},
					params:{
						DrugRequest_id: wnd.DrugRequest_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						wnd.form.setValues(result[0]);
						wnd.setMedPersonalGridParams();

						wnd.form.findField('DrugRequestPeriod_id').setValueById(result[0].DrugRequestPeriod_id);

						if (result[0].DrugRequestKind_id <= 0) {
							wnd.form.findField('DrugRequestKind_id').setValue(1); //Плановая
						}
						if(result[0].PersonRegisterType_id && result[0].PersonRegisterType_id == 1){
							wnd.list_combo_fed.getStore().load({
								callback: function(){
									wnd.setListFilter();
									wnd.list_combo_fed.setValue(result[0].DrugRequestPropertyFed_id);
									if (result[0].DrugRequestPropertyFed_id > 0) {
										var idx = wnd.list_combo_fed.getStore().findBy(function(record) {
	                                        return record.get('DrugRequestProperty_id') == result[0].DrugRequestPropertyFed_id;
	                                    });
										if (idx >= 0) {
											var record = wnd.list_combo_fed.getStore().getAt(idx);
											wnd.form.findField('DrugRequestProperty_OrgName').setValue(record.get('DrugRequestProperty_OrgName'));
										}
									}
								}
							});
							wnd.list_combo_reg.getStore().load({
								callback: function(){
									wnd.setListFilter();
									wnd.list_combo_reg.setValue(result[0].DrugRequestPropertyReg_id);
									if (result[0].DrugRequestPropertyReg_id > 0) {
										var idx = wnd.list_combo_reg.getStore().findBy(function(record) {
	                                        return record.get('DrugRequestProperty_id') == result[0].DrugRequestPropertyReg_id;
	                                    });
										if (idx >= 0) {
											var record = wnd.list_combo_reg.getStore().getAt(idx);
											wnd.form.findField('DrugRequestProperty_OrgName').setValue(record.get('DrugRequestProperty_OrgName'));
										}
									}
								}
							});
						} else {
							wnd.list_combo.getStore().load({
								callback: function(){
									wnd.setListFilter();
									wnd.list_combo.setValue(result[0].DrugRequestProperty_id);
									if (result[0].DrugRequestProperty_id > 0) {
										var idx = wnd.list_combo.getStore().findBy(function(record) {
	                                        return record.get('DrugRequestProperty_id') == result[0].DrugRequestProperty_id;
	                                    });
										if (idx >= 0) {
											var record = wnd.list_combo.getStore().getAt(idx);
											wnd.form.findField('DrugRequestProperty_OrgName').setValue(record.get('DrugRequestProperty_OrgName'));
										}
									}
								}
							});
						}
						
						if (result[0].DrugRequestPeriod_id > 0) {
							wnd.MedPersonalGrid.loadData({
								globalFilters: {
									DrugRequestPeriod_id: result[0].DrugRequestPeriod_id,
									PersonRegisterType_id: result[0].PersonRegisterType_id,
									DrugRequestKind_id: result[0].DrugRequestKind_id,
									DrugGroup_id: result[0].DrugGroup_id
								},
								callback: function() {
									wnd.MedPersonalGrid.getGrid().getStore().each(function(r){
										r.set('state', 'saved');
									});
								},
								options: {
									addEmptyRecord: false
								}
							});
						}

						wnd.setAllowBlankFields();
						loadMask.hide();
					},
					url:'/?c=MzDrugRequest&m=load'
				});				
			break;	
		}
	},
	initComponent: function() {
		var wnd = this;
		
		wnd.list_combo = new Ext.form.ComboBox({
			mode: 'local',
			store: new Ext.data.JsonStore({
				url: '/?c=DrugRequestProperty&m=loadList',
				key: 'DrugRequestProperty_id',
				autoLoad: false,
				fields: [
					{name: 'DrugRequestProperty_id',    type:'int'},
					{name: 'DrugRequestProperty_Name',  type:'string'},
					{name: 'PersonRegisterType_id',  type:'int'},
					{name: 'DrugRequestPeriod_id',  type:'int'},
					{name: 'DrugRequestProperty_OrgName',  type:'string'}
				],
				sortInfo: {
					field: 'DrugRequestProperty_Name'
				}
			}),
			displayField:'DrugRequestProperty_Name',
			valueField: 'DrugRequestProperty_id',
			hiddenName: 'DrugRequestProperty_id',
			fieldLabel: lang['spisok_medikamentov'],
			triggerAction: 'all',
			anchor: '100%',
			allowBlank: false,
			editable: false,
			tpl: '<tpl for="."><div class="x-combo-list-item">'+
				'{DrugRequestProperty_Name}'+
			'</div></tpl>',
			listeners: {
				select: function(combo, record, idx) {
					wnd.form.findField('DrugRequestProperty_OrgName').setValue(record.get('DrugRequestProperty_OrgName'));
				}
			}
		});

		wnd.list_combo_fed = new Ext.form.ComboBox({
			mode: 'local',
			store: new Ext.data.JsonStore({
				url: '/?c=DrugRequestProperty&m=loadList',
				key: 'DrugRequestProperty_id',
				autoLoad: false,
				fields: [
					{name: 'DrugRequestProperty_id',    type:'int'},
					{name: 'DrugRequestProperty_Name',  type:'string'},
					{name: 'PersonRegisterType_id',  type:'int'},
					{name: 'DrugRequestPeriod_id',  type:'int'},
					{name: 'DrugRequestProperty_OrgName',  type:'string'},
					{name: 'DrugRequestProperty_Org', 	type:'int'},
					{name: 'DrugFinance_id', 	type:'int'}
				],
				sortInfo: {
					field: 'DrugRequestProperty_Name'
				}
			}),
			displayField:'DrugRequestProperty_Name',
			valueField: 'DrugRequestProperty_id',
			hiddenName: 'DrugRequestPropertyFed_id',
			fieldLabel: lang['spisok_medikamentov_fed'],
			triggerAction: 'all',
			anchor: '100%',
			allowBlank: false,
			editable: false,
			tpl: '<tpl for="."><div class="x-combo-list-item">'+
				'{DrugRequestProperty_Name}'+
			'</div></tpl>',
			listeners: {
				select: function(combo, record, idx) {
					wnd.form.findField('DrugRequestProperty_OrgName').setValue(record.get('DrugRequestProperty_OrgName'));
				},
				change: function(combo, newVal){
					var Org = {};
					if(!Ext.isEmpty(newVal) && newVal != 0 && combo.getStore().getById(newVal)){
						Org.fed = combo.getStore().getById(newVal).get('DrugRequestProperty_Org');
					}
					wnd.setListFilter(Org);
				}.createDelegate(this)
			}
		});

		wnd.list_combo_reg = new Ext.form.ComboBox({
			mode: 'local',
			store: new Ext.data.JsonStore({
				url: '/?c=DrugRequestProperty&m=loadList',
				key: 'DrugRequestProperty_id',
				autoLoad: false,
				fields: [
					{name: 'DrugRequestProperty_id',    type:'int'},
					{name: 'DrugRequestProperty_Name',  type:'string'},
					{name: 'PersonRegisterType_id',  type:'int'},
					{name: 'DrugRequestPeriod_id',  type:'int'},
					{name: 'DrugRequestProperty_OrgName',  type:'string'},
					{name: 'DrugRequestProperty_Org', 	type:'int'},
					{name: 'DrugFinance_id', 	type:'int'}
				],
				sortInfo: {
					field: 'DrugRequestProperty_Name'
				}
			}),
			displayField:'DrugRequestProperty_Name',
			valueField: 'DrugRequestProperty_id',
			hiddenName: 'DrugRequestPropertyReg_id',
			fieldLabel: lang['spisok_medikamentov_reg'],
			triggerAction: 'all',
			anchor: '100%',
			allowBlank: false,
			editable: false,
			tpl: '<tpl for="."><div class="x-combo-list-item">'+
				'{DrugRequestProperty_Name}'+
			'</div></tpl>',
			listeners: {
				select: function(combo, record, idx) {
					wnd.form.findField('DrugRequestProperty_OrgName').setValue(record.get('DrugRequestProperty_OrgName'));
				},
				change: function(combo, newVal){
					var Org = {};
					if(!Ext.isEmpty(newVal) && newVal != 0 && combo.getStore().getById(newVal)){
						Org.reg = combo.getStore().getById(newVal).get('DrugRequestProperty_Org');
					}
					wnd.setListFilter(Org);
				}.createDelegate(this)
			}
		});
		
		wnd.SearchPanel = new Ext.form.FormPanel({
			bodyStyle: 'padding: 5px',
			border: false,
			region: 'north',
			autoHeight: true,
			frame: true,
			labelWidth: 105,
			labelAlign: 'right',
			items: [{
				layout: 'column',
				labelWidth: 50,
				items: [{
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'Person_Fio',
						fieldLabel: lang['fio'],
						width: 200
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'swlpucombo',
						hiddenName: 'Lpu_id',
						fieldLabel: lang['mo'],
						width: 300,
						listWidth: 400
					}]
				}, {
					layout: 'form',
					bodyStyle:'background:#DFE8F6;padding-left:15px;padding-right:5px;',
					items: [{
						xtype: 'button',
						text: lang['poisk'],
						minWidth: 80,
						handler: function () {
							wnd.SearchPanel.doSearch();
						}
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'button',
						text: lang['sbros'],
						minWidth: 80,
						handler: function () {
							wnd.SearchPanel.doSearch(true);
						}
					}]
				}]
			}],
			doSearch: function(clear) {
				var form = this.getForm();
				var store = wnd.MedPersonalGrid.getGrid().getStore();
				if (clear) {
					form.reset();
					store.clearFilter();
					wnd.MedPersonalGrid.hideDeleted();
				} else {
					var fio = form.findField('Person_Fio').getValue();
					var lpu = form.findField('Lpu_id').getValue();
					store.filterBy(function(record){
						if((lpu <= 0 || record.get('Lpu_id') == lpu) && (fio == '' || record.get('Person_Fio').toUpperCase().indexOf(fio.toUpperCase()) >= 0)) return true;
							return false;
					});
				}
			}
		});
		
		
		wnd.MedPersonalGrid = new sw.Promed.ViewFrame({
			id: this.id + 'ViewFrame',
			actions: [
				{name: 'action_add', text: lang['redaktirovat_spisok'], handler: function() { wnd.MedPersonalGrid.editRecord('add'); }},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', hidden: true},
				{name: 'action_refresh', hidden: true},
				{name: 'action_delete', handler: function() { wnd.MedPersonalGrid.askDeleteRecord(); }},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MzDrugRequest&m=loadLpuList',
			height: 280,
			region: 'center',
			object: 'DrugRequestLpuGroup',
			editformclassname: 'swDrugRequestLpuSelectWindow',
			paging: false,
			saveAtOnce: true,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'DrugRequestLpuGroup_id', type: 'int', header: 'ID', hidden: true, key: true},
				{name: 'state', type: 'string', header: 'state', hidden: true},
				{name: 'Lpu_id', hidden: true},
				{name: 'MedPersonal_id', hidden: true},
				{name: 'Lpu_Name', type: 'string', header: lang['mo'], id: 'autoexpand'},
                {name: 'Person_Fio', type: 'string', header: lang['fio'], width: 300},
				{name: 'Post_Name', type: 'string', header: lang['doljnost'], width: 200},
				{name: 'LpuSectionProfile_Name', type: 'string', header: lang['profil'], hidden: true},
                {name: 'DrugRequestMp_Count', hidden: true}
			],
			title: null,
			toolbar: true,
			params: {
				onSelect: function(selection) {
					var view_frame = wnd.MedPersonalGrid;
					var store = view_frame.getGrid().getStore();
					var data_arr = new Array();
					selection.each(function(r) {
						var data = new Object();
						Ext.apply(data, r.data);
						data_arr.push(data);
					});
					store.loadData(data_arr);
					this.hide();
					view_frame.hideDeleted();
				}
			},
            onRowSelect: function(sm,rowIdx,record) {
                if (record.get('DrugRequestMp_Count') < 1) {
                    this.ViewActions.action_delete.setDisabled(false);
                } else {
                    this.ViewActions.action_delete.setDisabled(true);
                }
            },
			askDeleteRecord: function(){
				var grid = this;
				sw.swMsg.show({
					icon: Ext.MessageBox.QUESTION,
					msg: lang['vyi_hotite_udalit_zapis'],
					title: lang['podtverjdenie'],
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ('yes' == buttonId) {
							grid.deleteRecord();
						}
					}
				});
			},
			deleteRecord: function(){
				var view_frame = this;
				var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
                if (selected_record && selected_record.get('DrugRequestMp_Count') < 1) {
                    if (selected_record.get('state') == 'add') {
                        view_frame.getGrid().getStore().remove(selected_record);
                    } else {
                        selected_record.set('state', 'delete');
                        selected_record.commit();
                        view_frame.hideDeleted();
                    }
                }
			},
			getChangedData: function(){ //возвращает новые и измненные показатели
				var data = new Array();
				this.getGrid().getStore().clearFilter();
				this.getGrid().getStore().each(function(record) {
					if ((record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete')) {						
						data.push(record.data);
					}
				});
				this.hideDeleted();
				return data;
			},						
			getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
				var dataObj = this.getChangedData();
				return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
			},
			hideDeleted: function() {
				var view_frame = this;
				view_frame.getGrid().getStore().filterBy(function(record){
					if(record.data.state == 'delete') return false;
					return true;
				});
			}
		});
		
		wnd.TabPanel = new Ext.TabPanel(
		{
			border: false,
			activeTab:0,
			autoScroll: true,			
			height: 500,
			region: 'center',
			layoutOnTabChange: true,
			items: [{
				title: lang['mo_zayavki'],
				layout: 'fit',
				border:false,
				items: [
					wnd.SearchPanel,
					wnd.MedPersonalGrid
				]
			}]
		});
	
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,
			region: 'center',
			layout: 'border',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				layout: 'form',				
				autoHeight: true,
				id: 'mdrreMzDrugRequestRegionEditForm',
				style: '',
				bodyStyle:'background:#DFE8F6;padding:10px;',
				border: false,
				labelWidth: 253,
				labelAlign:'right',
				collapsible: true,
				region: 'north',
				url:'/?c=MzDrugRequest&m=saveDrugRequestRegion',
				items: [{
					name: 'DrugRequest_id',
					xtype: 'hidden',
					value: 0
				}, {
					name: 'DrugRequestStatus_id',
					xtype: 'hidden'
				}, {
					fieldLabel: lang['rabochiy_period'],
					hiddenName: 'DrugRequestPeriod_id',
					xtype: 'swdynamicdrugrequestperiodcombo',
					allowBlank: false,
					width: 300,
					listeners: {
						'select': function (inp, e){
							if (wnd.default_name) {
								wnd.setDefaultName();
							}
							wnd.setListFilter();
							wnd.setMedPersonalGridParams();
						}
					},
					setValueById: function(id) {
						var combo = this ;
						combo.getStore().load({
							callback: function() {
								combo.setValue(id);
								var index = combo.getStore().findBy(function(record) {
                                    return record.get('DrugRequestPeriod_id') == id;
                                });
								if (index != -1){
									var rec = combo.getStore().getAt(index);
									combo.fireEvent('select', combo, rec, 0);
								}
								wnd.setMedPersonalGridParams();
							}
						});
					}
				}, {
					fieldLabel: lang['vid_zayavki'],
					comboSubject: 'DrugRequestKind',
					id: 'mdrreDrugRequestKind_id',
					name: 'DrugRequestKind_id',
					xtype: 'swcustomobjectcombo',
					width: 300,
					allowBlank:false,
					listeners: {
						'select': function (inp, e){
							if (wnd.default_name) {
								wnd.setDefaultName();
							}
						}
					}
				}, {
					fieldLabel: lang['tip_zayavki_registra_patsientov'],
					comboSubject: 'PersonRegisterType',
					id: 'mdrrePersonRegisterType_id',
					name: 'PersonRegisterType_id',
					xtype: 'swcommonsprcombo',
					width: 300,
					listeners: {
						'select': function (inp, e){
							wnd.setAllowBlankFields();
							if (wnd.default_name) {
								wnd.setDefaultName();
							}
							wnd.setListFilter();
                            wnd.setMedPersonalGridParams();
						}
					},
					moreFields: [{name: 'PersonRegisterType_SysNick', mapping: 'PersonRegisterType_SysNick'}],
					allowBlank: true
				}, {
					layout: 'form',
					items: [{
						fieldLabel: lang['gruppa_medikamentov'],
						comboSubject: 'DrugGroup',
						name: 'DrugGroup_id',
						xtype: 'swcommonsprcombo',
						width: 300,
						listeners: {
							'select': function (inp, e){
								if (wnd.default_name) {
									wnd.setDefaultName();
								}
								wnd.setListFilter();
							}
						},
						allowBlank: false
					}]
				}, {
					fieldLabel: lang['naimenovanie_zayavki'],
					name: 'DrugRequest_Name',
					xtype: 'textfield',
					allowBlank: false,
					enableKeyEvents: true,
					listeners: {
						'keypress': function (inp, e){
							wnd.default_name = false;
						},
						'change': function (inp, e){
							if (inp.getValue() == '') {
								wnd.default_name = true;
							}
						}
					},
					anchor: '100%'
				},
				wnd.list_combo,
				wnd.list_combo_fed,
				wnd.list_combo_reg,
				{
					xtype: 'textfield',
					name: 'DrugRequestProperty_OrgName',
					fieldLabel: lang['koordinator'],
					disabled: true,
					anchor: '100%'
				}, {
					xtype: 'checkbox',
					name: 'DrugRequestQuota_IsPersonalOrderObligatory',
					fieldLabel: 'Персональная разнарядка обязательна '
				}, {
					xtype: 'fieldset',
					title: lang['normativ_finansirovaniya_rub'],
					autoHeight: true,
					labelWidth: 242,
					items: [{
						layout: 'column',
						border: false,
						items:[{
							layout: 'form',
							border: false,
							width: 241,
							items: [{
								xtype: 'label',
								anchor: '100%',
								style: 'text-align:right;display:block;width:100%;padding-top:3px;font-size:12px;',
								text: lang['na_odnogo_lgotopoluchatelya_v_mesyats']
							}]
						}, {
							layout: 'form',
							border: false,
							width: 306,
							labelWidth: 1,
							items: [{
								labelSeparator: '',
								name: 'DrugRequestQuota_Person',
								xtype: 'numberfield',
								anchor: '100%',
								maxValue: 99999999,
								allowNegative: false,
								allowBlank: true
							}]
						}, {
							layout: 'form',
							border: false,
							width: 180,
							labelWidth: 50,
							items: [{
								fieldLabel: lang['fed'],
								name: 'DrugRequestQuota_PersonFed',
								xtype: 'numberfield',
								anchor: '100%',
								maxValue: 99999999,
								allowNegative: false,
								allowBlank: true
							}]
						}, {
							layout: 'form',
							border: false,
							width: 180,
							labelWidth: 50,
							items: [{
								fieldLabel: lang['reg'],
								name: 'DrugRequestQuota_PersonReg',
								xtype: 'numberfield',
								anchor: '100%',
								maxValue: 99999999,
								allowNegative: false,
								allowBlank: true
							}]
						}] 
					}, {
						layout: 'column',
						border: false,
						items:[{
							layout: 'form',
							border: false,
							width: 241,
							items: [{
								xtype: 'label',
								anchor: '100%',
								style: 'text-align:right;display:block;width:100%;padding-top:3px;font-size:12px;',
								text: lang['po_zayavke']
							}]
						}, {
							layout: 'form',
							border: false,
							width: 306,
							labelWidth: 1,
							items: [{
								labelSeparator: '',
								name: 'DrugRequestQuota_Total',
								xtype: 'numberfield',
								width: 300,
								maxValue: 9999999999,
								allowNegative: false,
								allowBlank: true
							}]
						}, {
							layout: 'form',
							border: false,
							width: 180,
							labelWidth: 50,
							items: [{
								fieldLabel: lang['fed'],
								name: 'DrugRequestQuota_TotalFed',
								xtype: 'numberfield',
								anchor: '100%',
								maxValue: 99999999,
								allowNegative: false,
								allowBlank: true
							}]
						}, {
							layout: 'form',
							border: false,
							width: 180,
							labelWidth: 50,
							items: [{
								fieldLabel: lang['reg'],
								name: 'DrugRequestQuota_TotalReg',
								xtype: 'numberfield',
								anchor: '100%',
								maxValue: 99999999,
								allowNegative: false,
								allowBlank: true
							}]
						}] 
					}]
				}]
			},
			wnd.TabPanel],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'DrugRequest_id'}, 
				{name: 'DrugRequestPeriod_id'}
			]),
			url: '/?c=MzDrugRequest&m=saveDrugRequestRegion'
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
			}, 
			{
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
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
		sw.Promed.swMzDrugRequestRegionEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('mdrreMzDrugRequestRegionEditForm').getForm();
	}	
});