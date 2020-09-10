/**
 * swWhsDocumentSpecificationEditWindow - окно редактирования медикамента в заявке
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
/*NO PARSE JSON*/

sw.Promed.swWhsDocumentSpecificationEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swWhsDocumentSpecificationEditWindow',
	width: 640,
	autoHeight: true,
	modal: true,

	doSave: function() {
		var wnd = this;

		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.FormPanel.getFirstInvalidEl().focus(true);
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

		//проверка на дублирование другой строки в документе учета
		/*if (this.DrugDocumentType_Code.inlist([21])) {
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
				sw.swMsg.alert(lang['oshibka'], lang['stroka_medikamenta_po_patsientu_uje_suschestvuet']);
				return false;
			}
		} else {
			data.RecordForMerge_id = null;
			var merge_data = this.getDataForMerge(data);
			if (merge_data && merge_data.DocumentUcStr_id > 0) {
				if (confirm(lang['takaya_stroka_uje_est_v_dokumente_ucheta_vyipolnit_summirovanie_kolichestva'])) {
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
		}*/

		if (data.DrugNomen_id > 0) {
			record = wnd.form.findField('DrugNomen_id').getStore().getById(data.DrugNomen_id);
			if (!record) {
				return false;
			}
			data.DrugComplexMnnCode_Code = record.get('DrugComplexMnnCode_Code');
			data.Drug_Ean = record.get('Drug_Ean');
		}
		if (data.DrugComplexMnn_id > 0) {
			record = wnd.form.findField('DrugComplexMnn_id').getStore().getById(data.DrugComplexMnn_id);
			if (!record) {
				return false;
			}
			data.DrugComplexMnn_Name = record.get('DrugComplexMnn_Name');
			data.DrugComplexMnn_Dose = record.get('DrugComplexMnn_Dose');
			data.RlsClsdrugforms_RusName = record.get('RlsClsdrugforms_RusName');
		}
		if (data.Tradenames_id > 0) {
			record = wnd.form.findField('Tradenames_id').getStore().getById(data.Tradenames_id);
			if (!record) {
				return false;
			}
			data.DrugTorg_Name = record.get('DrugTorg_Name');
		}
		if (data.GoodsUnit_id > 0) {
			var gu_data = wnd.gu_combo.getSelectedRecordData();
			if (gu_data && !Ext.isEmpty(gu_data.GoodsUnit_id)) {
                data.GoodsUnit_Name = gu_data.GoodsUnit_Name;
			}
		}
		data.WhsDocumentSpecification_Note = wnd.form.findField('WhsDocumentSpecification_Note').getValue();

		log(['onSaveData', data]);

		wnd.onSave(data);
		wnd.hide();

		return true;
	},

	refreshDrugNomenParams: function() {
		var drug_nomen_combo = this.form.findField('DrugNomen_id');
		var drug_complex_mnn_combo = this.form.findField('DrugComplexMnn_id');
		var tradenames_combo = this.form.findField('Tradenames_id');

		var baseParams = {queryBy: 'DrugComplexMnnCode_Code'};

		if (!Ext.isEmpty(drug_complex_mnn_combo.getValue())) {
			baseParams.DrugComplexMnn_id = drug_complex_mnn_combo.getValue();
		}
		if (!Ext.isEmpty(tradenames_combo.getValue())) {
			baseParams.Tradenames_id = tradenames_combo.getValue();
		}

		drug_nomen_combo.getStore().baseParams = baseParams;
	},
	refreshTradenamesParams: function() {
		var tradenames_combo = this.form.findField('Tradenames_id');
		var drug_complex_mnn_combo = this.form.findField('DrugComplexMnn_id');

		var baseParams = {};

		if (!Ext.isEmpty(this.params.WhsDocumentSupply_id)) {
			baseParams.WhsDocumentSupply_id = this.params.WhsDocumentSupply_id;
		}
		if (!Ext.isEmpty(drug_complex_mnn_combo.getValue())) {
			baseParams.DrugComplexMnn_id = drug_complex_mnn_combo.getValue();
		}

		tradenames_combo.getStore().baseParams = baseParams;
	},
	refreshEvnCourseTreatParams: function() {
		var evn_course_treat_combo = this.form.findField('EvnCourseTreat_id');
		var drug_complex_mnn_combo = this.form.findField('DrugComplexMnn_id');
		var tradenames_combo = this.form.findField('Tradenames_id');

		var baseParams = {};

		if (!Ext.isEmpty(this.userMedStaffFact.MedService_id)) {
			baseParams.MedService_id = this.userMedStaffFact.MedService_id;
		}
		if (!Ext.isEmpty(drug_complex_mnn_combo.getValue())) {
			baseParams.DrugComplexMnn_id = drug_complex_mnn_combo.getValue();
		}
		if (!Ext.isEmpty(tradenames_combo.getValue())) {
			baseParams.Tradenames_id = tradenames_combo.getValue();
		}

		evn_course_treat_combo.getStore().baseParams = baseParams;
	},
	refreshReceptOtovParams: function() {
		var recept_otov_combo = this.form.findField('ReceptOtov_id');
		var drug_complex_mnn_combo = this.form.findField('DrugComplexMnn_id');
		var tradenames_combo = this.form.findField('Tradenames_id');

		var baseParams = {};

		if (!Ext.isEmpty(this.userMedStaffFact.Org_id)) {
			baseParams.Org_id = this.userMedStaffFact.Org_id;
		}
		if (!Ext.isEmpty(drug_complex_mnn_combo.getValue())) {
			baseParams.DrugComplexMnn_id = drug_complex_mnn_combo.getValue();
		}
		if (!Ext.isEmpty(tradenames_combo.getValue())) {
			baseParams.Tradenames_id = tradenames_combo.getValue();
		}

		recept_otov_combo.getStore().baseParams = baseParams;
	},
	refreshGoodsUnitsParams: function() {
		var drug_complex_mnn_combo = this.form.findField('DrugComplexMnn_id');
		var tradenames_combo = this.form.findField('Tradenames_id');

		this.gu_combo.getStore().baseParams.DrugComplexMnn_id = !Ext.isEmpty(drug_complex_mnn_combo.getValue()) ? drug_complex_mnn_combo.getValue() : null;
		this.gu_combo.getStore().Tradenames_id = !Ext.isEmpty(tradenames_combo.getValue()) ? tradenames_combo.getValue() : null;
	},

	getDrugByDrugNomenCode: function() {
		var DrugNomen_Code = this.form.findField('DrugNomen_Code').getValue();

		if (Ext.isEmpty(DrugNomen_Code)) {
			return false;
		}

		Ext.Ajax.request({
			url: '/?c=DrugNomen&m=getDrugByDrugNomenCode',
			params: {
				DrugNomen_Code: DrugNomen_Code
			},
			success: function(response){
				var result = Ext.util.JSON.decode(response.responseText);
				if (result[0] && result[0].DrugComplexMnn_id) {
					this.form.findField('DrugComplexMnn_id').setValueById(result[0].DrugComplexMnn_id);
				}
			}.createDelegate(this)
		});
		return true;
	},

	show: function() {
		sw.Promed.swWhsDocumentSpecificationEditWindow.superclass.show.apply(this, arguments);

		var wnd = this;
        var region_nick = getRegionNick();

		this.action = 'view';
		this.callback = Ext.emptyFn;
		this.userMedStaffFact = {};
		this.isAptMu = false;
		this.coeff = 1;

		this.form.reset();

		if (!arguments[0]) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
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
		if (arguments[0].params) {
			this.params = arguments[0].params;
		}
		if ( arguments[0].userMedStaffFact ) {
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}

		if (!Ext.isEmpty(this.userMedStaffFact.Lpu_id) && this.userMedStaffFact.MedServiceType_SysNick == 'merch') {
			this.isAptMu = true;
		}

		var drug_nomen_combo = this.form.findField('DrugNomen_id');
		var evn_course_treat_combo = this.form.findField('EvnCourseTreat_id');
		var recept_otov_combo = this.form.findField('ReceptOtov_id');
		var drug_complex_mnn_combo = this.form.findField('DrugComplexMnn_id');
		var tradenames_combo = this.form.findField('Tradenames_id');
		var drug_name_field = this.form.findField('Drug_Name');

		evn_course_treat_combo.hideContainer();
		evn_course_treat_combo.lastQuery = 'This query sample that is not will never appear';
		recept_otov_combo.hideContainer();
		recept_otov_combo.lastQuery = 'This query sample that is not will never appear';

		drug_nomen_combo.getStore().baseParams = {queryBy: 'DrugComplexMnnCode_Code'};
		drug_nomen_combo.lastQuery = 'This query sample that is not will never appear';
		drug_complex_mnn_combo.getStore().baseParams = {hasDrugComplexMnnCode: true};
		drug_complex_mnn_combo.lastQuery = 'This query sample that is not will never appear';
		tradenames_combo.getStore().baseParams = {};
		tradenames_combo.lastQuery = 'This query sample that is not will never appear';

        this.gu_combo.fullReset();
        this.gu_combo.getStore().baseParams.UserOrg_id = getGlobalOptions().org_id;
        this.gu_combo.getStore().baseParams.UserOrg_Type = getGlobalOptions().orgtype;
        this.gu_combo.getStore().baseParams.LpuSection_id = !Ext.isEmpty(this.userMedStaffFact.LpuSection_id) ? this.userMedStaffFact.LpuSection_id : null;

		this.refreshDrugNomenParams();
		this.refreshTradenamesParams();
		this.refreshEvnCourseTreatParams();
		this.refreshReceptOtovParams();
		this.refreshGoodsUnitsParams();

		if (this.isAptMu) {
			evn_course_treat_combo.showContainer();
			evn_course_treat_combo.getStore().baseParams = {
				MedService_id: this.userMedStaffFact.MedService_id
			};
		} else {
			recept_otov_combo.showContainer();
			recept_otov_combo.getStore().baseParams = {
				Org_id: this.userMedStaffFact.Org_id
			};
		}
        drug_name_field.hideContainer();

		wnd.setTitle("Медикамент");
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		switch (wnd.action) {
			case 'add':
				this.setTitle(this.title + ": Добавление");
				this.enableEdit(true);
                this.gu_combo.loadData();
				break;
			case 'edit':
			case 'view':
				this.setTitle(this.title + (wnd.action == "edit" ? ": Редактирование" : ": Просмотр"));
				this.enableEdit(this.action != 'view');
				this.form.setValues(wnd.params);

				if (wnd.params && !Ext.isEmpty(wnd.params.Drug_id)) {
                    drug_name_field.showContainer();
				}

				drug_complex_mnn_combo.setValueById(wnd.params.DrugComplexMnn_id);
				tradenames_combo.setValueById(wnd.params.Tradenames_id);
                wnd.gu_combo.setValueById(wnd.params.GoodsUnit_id);

				this.refreshDrugNomenParams();
				this.refreshGoodsUnitsParams();

				drug_nomen_combo.getStore().load({
					callback: function() {
						var record = drug_nomen_combo.getStore().getById(drug_nomen_combo.getValue());
						if (!record && drug_nomen_combo.getStore().getCount() > 0) {
							record = drug_nomen_combo.getStore().getAt(0);
						}
						if (record) {
							drug_nomen_combo.setValue(record.get('DrugNomen_id'));
						} else {
							drug_nomen_combo.setValue(null);
						}
					}
				});
                this.gu_combo.loadData();

				if (!Ext.isEmpty(wnd.params.EvnCourseTreat_id)) {
					evn_course_treat_combo.setValueById(wnd.params.EvnCourseTreat_id);
				}
				if (!Ext.isEmpty(wnd.params.ReceptOtov_id)) {
					recept_otov_combo.setValueById(wnd.params.ReceptOtov_id);
				}

				break;
		}

        this.syncShadow();
		loadMask.hide();
	},

	initComponent: function() {
		var wnd = this;

		var drug_torg_combo = new sw.Promed.SwBaseRemoteComboSingleTrigger({
			mode: 'remote',
			minChars: 1,
			editable: true,
			triggerAction: 'all',
			hiddenName: 'Tradenames_id',
			displayField: 'DrugTorg_Name',
			valueField: 'DrugTorg_id',
			fieldLabel: 'Торговое наименование',
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">{DrugTorg_Name}&nbsp;</div></tpl>'
			),
			store: new Ext.data.JsonStore({
				url: '/?c=RlsDrug&m=loadDrugTorgList',
				key: 'DrugTorg_id',
				autoLoad: false,
				fields: [
					{name: 'DrugTorg_id',  type:'int'},
					{name: 'DrugTorg_Name',  type:'string'}
				]
			}),
			setValueById: function(id) {
				var combo = this;
				combo.getStore().load({
					params: {DrugTorg_id: id},
					callback: function() {
						combo.setValue(id);

						wnd.refreshDrugNomenParams();
						wnd.refreshEvnCourseTreatParams();
						wnd.refreshReceptOtovParams();
						wnd.refreshGoodsUnitsParams();
					}
				});
			},
			listeners: {
				'select': function(combo, record, index) {
				},
				'change': function(combo, newValue, oldValue) {
					var drug_complex_mnn = this.form.findField('DrugComplexMnn_id');
					drug_complex_mnn.getStore().baseParams.Tradenames_id = newValue;
					drug_complex_mnn.getStore().load({
						//params: {Tradenames_id: newValue},
						callback: function() {
							var record = drug_complex_mnn.getStore().getById(drug_complex_mnn.getValue());
							if (!record && drug_complex_mnn.getStore().getCount() > 0) {
								record = drug_complex_mnn.getStore().getAt(0);
							}
							if (record && !Ext.isEmpty(record.get('DrugComplexMnn_id'))) {
								drug_complex_mnn.setValue(record.get('DrugComplexMnn_id'));
							} else {
								drug_complex_mnn.setValue(null);
							}

							if (Ext.isEmpty(drug_complex_mnn.getValue())) {
								this.form.findField('EvnCourseTreat_id').setValue(null);
								this.form.findField('ReceptOtov_id').setValue(null);
								this.form.findField('WhsDocumentSpecification_Note').setValue(null);
							}

							this.form.findField('EvnCourseTreat_id').lastQuery = 'This query sample that is not will never appear';
							this.form.findField('ReceptOtov_id').lastQuery = 'This query sample that is not will never appear';

							this.refreshDrugNomenParams();
							this.refreshTradenamesParams();
							this.refreshEvnCourseTreatParams();
							this.refreshReceptOtovParams();
							this.refreshGoodsUnitsParams();

							var drug_nomen_combo = this.form.findField('DrugNomen_id');
							var baseParams = drug_nomen_combo.getStore().baseParams;
							if (baseParams.DrugComlexMnn_id || baseParams.Tradenames_id) {
								drug_nomen_combo.getStore().load({
									callback: function() {
										var record = drug_nomen_combo.getStore().getById(drug_nomen_combo.getValue());
										if (!record && drug_nomen_combo.getStore().getCount() > 0) {
											record = drug_nomen_combo.getStore().getAt(0);
										}
										if (record && !Ext.isEmpty(record.get('DrugNomen_id'))) {
											drug_nomen_combo.setValue(record.get('DrugNomen_id'));
										} else {
											drug_nomen_combo.setValue(null);
										}
									}
								});
							} else {
								drug_nomen_combo.setValue(null);
							}
                            this.gu_combo.loadData();
						}.createDelegate(this)
					});
				}.createDelegate(this)
			},
			listWidth: 440,
			width: 440
		});

		var evn_course_treat_combo = new sw.Promed.SwBaseRemoteComboSingleTrigger({
			mode: 'remote',
			minChars: 1,
			editable: true,
			enableKeyEvents: true,
			triggerAction: 'all',
			hiddenName: 'EvnCourseTreat_id',
			displayField: 'Person_Fio',
			valueField: 'EvnCourseTreat_id',
			fieldLabel: 'Пациент',
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
					'{Person_Fio}',
					'{[values.EvnPS_NumCard?", КВС № "+values.EvnPS_NumCard:""]}',
					'{[values.EvnCourseTreat_setDate?", курс лечения с "+values.EvnCourseTreat_setDate.format("d.m.Y"):""]}',
					'{[values.EvnCourseTreat_Duration?", "+values.EvnCourseTreat_Duration+" дн.":""]}',
					'&nbsp;',
				'</div></tpl>'
			),
			store: new Ext.data.JsonStore({
				url: '/?c=WhsDocumentUc&m=loadEvnCourseTreatList',
				key: 'EvnCourseTreat_id',
				autoLoad: false,
				fields: [
					{name: 'EvnCourseTreat_id',  type: 'int'},
					{name: 'EvnCourseTreat_setDate',  type: 'date', dateFormat: 'd.m.Y'},
					{name: 'EvnCourseTreat_Duration',  type:'int'},
					{name: 'Person_id',  type: 'int'},
					{name: 'Person_Fio',  type: 'string'},
					{name: 'EvnPS_NumCard',  type: 'string'}
				]
			}),
			listWidth: 560,
			//width: 440,
			setValueById: function(id) {
				var combo = this;
				combo.getStore().load({
					params: {EvnCourseTreat_id: id},
					callback: function() {
						combo.setValue(id);

						var index = combo.getStore().findBy(function(rec) { return rec.get('EvnCourseTreat_id') == id; });
						var record = combo.getStore().getAt(index);
						combo.fireEvent('select', combo, record, index);
					}
				});
			},
			listeners: {
				'select': function(combo, record, index) {
					var note_field = this.form.findField('WhsDocumentSpecification_Note');

					var text = "";
					if (record && !Ext.isEmpty(record.get('EvnCourseTreat_id'))) {
						text = record.get('Person_Fio')+', КВС № '+record.get('EvnPS_NumCard');
					}
					note_field.setValue(text);
				}.createDelegate(this),
				keydown: function(combo, e) {
					if ( e.getKey() == e.DELETE) {
						combo.setValue(null);
						this.form.findField('WhsDocumentSpecification_Note').setValue(null);
						e.stopEvent();
						return true;
					}
				}.createDelegate(this)
			}
		});

		var recept_otov_combo = new sw.Promed.SwBaseRemoteComboSingleTrigger({
			mode: 'remote',
			minChars: 1,
			editable: true,
			enableKeyEvents: true,
			triggerAction: 'all',
			hiddenName: 'ReceptOtov_id',
			displayField: 'Person_Fio',
			valueField: 'ReceptOtov_id',
			fieldLabel: 'Пациент',
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
					'{Person_Fio}',
					'{[values.EvnRecept_id?", Рецепт серия "+values.EvnRecept_Ser:""]}',
					'{[values.EvnRecept_id?" № "+values.EvnRecept_Num:""]}',
					'{[values.EvnRecept_id?" от "+values.EvnRecept_setDate.format("d.m.Y"):""]}',
					'&nbsp;',
				'</div></tpl>'
			),
			store: new Ext.data.JsonStore({
				url: '/?c=WhsDocumentUc&m=loadReceptOtovList',
				key: 'ReceptOtov_id',
				autoLoad: false,
				fields: [
					{name: 'ReceptOtov_id',  type: 'int'},
					{name: 'EvnRecept_id',  type: 'int'},
					{name: 'EvnRecept_Ser',  type: 'string'},
					{name: 'EvnRecept_Num',  type: 'string'},
					{name: 'EvnRecept_setDate',  type: 'date', dateFormat: 'd.m.Y'},
					{name: 'Person_id',  type: 'int'},
					{name: 'Person_Fio',  type: 'string'}
				]
			}),
			listWidth: 560,
			//width: 440,
			setValueById: function(id) {
				var combo = this;
				combo.getStore().load({
					params: {ReceptOtov_id: id},
					callback: function() {
						combo.setValue(id);

						var index = combo.getStore().findBy(function(rec) { return rec.get('ReceptOtov_id') == id; });
						var record = combo.getStore().getAt(index);
						combo.fireEvent('select', combo, record, index);
					}
				});
			},
			listeners: {
				'select': function(combo, record, index) {
					var note_field = this.form.findField('WhsDocumentSpecification_Note');

					var text = "";
					if (record && !Ext.isEmpty(record.get('ReceptOtov_id'))) {
						text = record.get('Person_Fio')+', Рецепт серия '+record.get('EvnRecept_Ser')+
							' № '+record.get('EvnRecept_Num')+' от '+record.get('EvnRecept_setDate').format('d.m.Y');
					}
					note_field.setValue(text);
				}.createDelegate(this),
				keydown: function(combo, e) {
					if ( e.getKey() == e.DELETE) {
						combo.setValue(null);
						this.form.findField('WhsDocumentSpecification_Note').setValue(null);
						e.stopEvent();
						return true;
					}
				}.createDelegate(this)
			}
		});

        this.gu_combo = new sw.Promed.SwCustomRemoteCombo({
            fieldLabel: langs('Единица  учета'),
            hiddenName: 'GoodsUnit_id',
            displayField: 'GoodsUnit_Str',
            valueField: 'GoodsUnit_id',
            editable: true,
            allowBlank: false,
            listWidth: 200,
			width: 217,
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
                    {name: 'isDefaultValue', mapping: 'isDefaultValue'}
                ]),
                url: '/?c=WhsDocumentUc&m=loadGoodsUnitCombo'
            }),
            trigger2Class: 'hideTrigger',
            setLinkedFieldValues: function(event_name) {
                /*wnd.setPostRecountKoef();
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
                wnd.setBarCodeViewBtnDisabled();*/
            },
            selectDefaultValue: function() {//установка значения по умолчанию
                if (Ext.isEmpty(wnd.params.GoodsUnit_bid) && wnd.gu_combo.getStore().getCount() > 0) { //значение по умолчанию устанавливается только если нет сохраненного в гриде значения
                    var gu_id = null;
                    var idx = -1;

                    if (idx < 0) {
                        if (getGlobalOptions().orgtype != 'lpu') {
                            idx = wnd.gu_combo.getStore().findBy(function(rec) { return rec.get('GoodsUnit_Name') == "упаковка"; }); //выбираем значение "упаковка"
                        } else {
                            idx = wnd.gu_combo.getStore().findBy(function(rec) { return rec.get('isDefaultValue') == "1"; }); //выбираем значение "пол умолчанию"
							if (idx < 0) {
                                idx = 0; //выбираем первую запись в списке
							}
                        }
                        if (idx >= 0) {
                            gu_id = wnd.gu_combo.getStore().getAt(idx).get('GoodsUnit_id');
                        }
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

		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'IEPW_FilterPanel',
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 150,
			items: [{
				xtype: 'hidden',
				name: 'WhsDocumentSpecification_id'
			}, {
				xtype: 'hidden',
				name: 'WhsDocumentSpecificity_id'
			}, {
				xtype: 'swdrugnomencombo',
				hiddenName: 'DrugNomen_id',
				fieldLabel: lang['kod'],
				displayField: 'DrugComplexMnnCode_Code',
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<font color="red">{DrugComplexMnnCode_Code}</font>&nbsp;{DrugNomen_Name}',
					'</div></tpl>'
				),
				listeners: {
					'select': function(combo, record) {
						if (!record || Ext.isEmpty(record.get('DrugNomen_id'))) {
							return false;
						}

						this.form.findField('DrugComplexMnn_id').setValueById(record.get('DrugComplexMnn_id'));
						this.form.findField('Tradenames_id').setValueById(record.get('DrugTorg_id'));
					}.createDelegate(this)
				},
				listWidth: 580,
				width: 200
			}, {
				allowBlank: false,
				hideIsFromDocumentUcOst: true,
				xtype: 'swdrugcomplexmnncombo',
				hiddenName: 'DrugComplexMnn_id',
				fieldLabel: 'Наименование',
				setValueById: function(DrugComplexMnn_id) {
					var combo = this;
					combo.store.load({
						params: {DrugComplexMnn_id: DrugComplexMnn_id},
						callback: function(){
							combo.setValue(DrugComplexMnn_id);

							wnd.refreshDrugNomenParams();
							wnd.refreshTradenamesParams();
							wnd.refreshEvnCourseTreatParams();
							wnd.refreshReceptOtovParams();
							wnd.refreshGoodsUnitsParams();
						}
					});
				},
				listeners: {
					'select': function(combo, record) {
					},
					'change': function(combo, newValue, oldValue) {
						this.form.findField('DrugNomen_id').lastQuery = 'This query sample that is not will never appear';
						this.form.findField('Tradenames_id').lastQuery = 'This query sample that is not will never appear';
						this.form.findField('EvnCourseTreat_id').lastQuery = 'This query sample that is not will never appear';
						this.form.findField('ReceptOtov_id').lastQuery = 'This query sample that is not will never appear';

						combo.getStore().baseParams.Tradenames_id = undefined;
						this.form.findField('Tradenames_id').setValue(null);
						this.form.findField('EvnCourseTreat_id').setValue(null);
						this.form.findField('ReceptOtov_id').setValue(null);
						this.form.findField('WhsDocumentSpecification_Note').setValue(null);

						this.refreshDrugNomenParams();
						this.refreshTradenamesParams();
						this.refreshEvnCourseTreatParams();
						this.refreshReceptOtovParams();
						this.refreshGoodsUnitsParams();

						var drug_nomen_combo = this.form.findField('DrugNomen_id');
						if (drug_nomen_combo.DrugComlexMnn_id || drug_nomen_combo.Tradenames_id) {
							drug_nomen_combo.getStore().load({
								callback: function() {
									var record = drug_nomen_combo.getStore().getById(drug_nomen_combo.getValue());
									if (!record && drug_nomen_combo.getStore().getCount() > 0) {
										record = drug_nomen_combo.getStore().getAt(0);
									}
									if (record && !Ext.isEmpty(record.get('DrugNomen_id'))) {
										drug_nomen_combo.setValue(record.get('DrugNomen_id'));
									} else {
										drug_nomen_combo.setValue(null);
									}
								}
							});
						} else {
							drug_nomen_combo.setValue(null);
						}
						this.gu_combo.loadData();
					}.createDelegate(this)
				},
				width: 440
			},
			drug_torg_combo,
			{
				xtype: 'textfield',
				name: 'Drug_Name',
				fieldLabel: 'Медикамент',
				width: 440
			},
			{
				xtype: 'textfield',
				name: 'WhsDocumentSpecification_Method',
				fieldLabel: 'Способ применения',
				width: 440
			},
			this.gu_combo,
			{
				layout: 'column',
				items: [{
					layout: 'form',
					labelWidth: 150,
					items: [{
						allowBlank: false,
						allowNegative: false,
						xtype: 'numberfield',
						name: 'WhsDocumentSpecification_Count',
						fieldLabel: 'Заявлено количество',
						width: 200
					}]
				}, {
					layout: 'form',
					labelWidth: 46,
					items: [{
						allowNegative: false,
						xtype: 'numberfield',
						name: 'WhsDocumentSpecification_Cost',
						fieldLabel: 'Цена',
						width: 190
					}]
				}]
			},/*{
				xtype: 'fieldset',
				autoHeight: true,
				title: 'Заявлено кол-во',
				style: 'padding: 5px 5px;',
				items: [{
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 80,
						items: [{
							allowBlank: false,
							allowNegative: false,
							xtype: 'numberfield',
							name: 'WhsDocumentSpecification_SetCount',
							fieldLabel: 'Упаковок',
							listeners: {
								'change': function(field, newValue, oldValue) {
									this.calculateCount();
								}.createDelegate(this)
							},
							width: 200
						}]
					}, {
						layout: 'form',
						labelWidth: 100,
						items: [{
							allowBlank: false,
							allowNegative: false,
							xtype: 'numberfield',
							name: 'WhsDocumentSpecification_Count',
							fieldLabel: 'Ед.учета',
							listeners: {
								'change': function(field, newValue, oldValue) {
									this.calculateRCount();
								}.createDelegate(this)
							},
							width: 200
						}]
					}]
				}]
			}, {
				xtype: 'fieldset',
				autoHeight: true,
				title: 'Цена',
				style: 'padding: 5px 5px;',
				items: [{
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 80,
						items: [{
							allowNegative: false,
							xtype: 'numberfield',
							name: 'WhsDocumentSpecification_Price',
							fieldLabel: 'За упаковку',
							listeners: {
								'change': function(field, newValue, oldValue) {
									this.calculateCost();
								}.createDelegate(this)
							},
							width: 200
						}]
					}, {
						layout: 'form',
						labelWidth: 100,
						items: [{
							allowNegative: false,
							xtype: 'numberfield',
							name: 'WhsDocumentSpecification_Cost',
							fieldLabel: 'За ед.изм.',
							listeners: {
								'change': function(field, newValue, oldValue) {
									this.calculateRCost();
								}.createDelegate(this)
							},
							width: 200
						}]
					}]
				}]
			},*/ {
				layout: 'form',
				defaults: {
					width: 440
				},
				items: [
					evn_course_treat_combo,
					recept_otov_combo,
					{
						disabled: true,
						xtype: 'textfield',
						name: 'WhsDocumentSpecification_Note',
						fieldLabel: 'Примечание'
					}
				]
			}],
			reader: new Ext.data.JsonReader({
				success: function(){
					//
				}
			}, [
				{name: 'InvoicePosition_id'},
				{name: 'Invoice_id'},
				{name: 'Shipment_id'},
				{name: 'InvoicePosition_PositionNum'},
				{name: 'InventoryItem_id'},
				{name: 'InvoicePosition_Count'},
				{name: 'InvoicePosition_Price'},
				{name: 'InvoicePosition_Coeff'},
				{name: 'InvoicePosition_Sum'},
				{name: 'InvoicePosition_Comment'}
			])
		});

		Ext.apply(this,{
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'WDSEW_SaveButton',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [this.FormPanel]
		});

		sw.Promed.swWhsDocumentSpecificationEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.FormPanel.getForm();
	}
});