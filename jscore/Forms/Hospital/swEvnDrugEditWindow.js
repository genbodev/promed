/**
* swEvnDrugEditWindow - персонифицированный учет
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Stac
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей (использована форма swEvnDrugEditWindow by Stas Bykov aka Savage)
* @version      апрель 2010
* @comment      Префикс для id компонентов edew (EvnDrugEditWindow)
*								TABINDEX_EDEW: 8600 
*
* @input data: action - действие (add, edit, view)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnDrugEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	id: 'EvnDrugEditWindow',
	draggable: true,
	split: true,
	width: 800,
	codeRefresh: true,
	objectName: 'swEvnDrugEditWindow',
	objectSrc: '/jscore/Forms/Hospital/swEvnDrugEditWindow.js',
	layout: 'form',
	documentUcStrMode:'expenditure',
    openMode: '',
	listeners: 
	{
		beforeshow: function()
		{
			//
		},
		hide: function() 
		{
			this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	keys: 
	[{
		alt: true,
		fn: function(inp, e) 
		{
			var current_window = Ext.getCmp('EvnDrugEditForm');
			e.stopEvent();
			if ( e.browserEvent.stopPropagation ) 
			{
				e.browserEvent.stopPropagation();
			}
			else 
			{
				e.browserEvent.cancelBubble = true;
			}
			if ( e.browserEvent.preventDefault ) 
			{
				e.browserEvent.preventDefault();
			}
			else 
			{
				e.browserEvent.returnValue = false;
			}
			e.returnValue = false;
			if ( Ext.isIE ) 
			{
				e.browserEvent.keyCode = 0;
				e.browserEvent.which = 0;
			}
			switch (e.getKey()) 
			{
				case Ext.EventObject.J:
					current_window.hide();
				break;

				case Ext.EventObject.C:
					current_window.doSave();
				break;
			}
		},
		key: 
		[
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		scope: this,
		stopEvent: false
	}],
    FormLoadState: { //объект для хранения информации о загрузке значений при редактировании формы
        data: {},
        loading_complete: true,
        Fields: ['Mol_id', 'DocumentUcStr_oid'], //список загружаемых полей
        LoadedFields: [],
        reset: function() {
            this.data = new Object();
            this.loading_complete = true;
            this.LoadedFields = new Array();
        },
        beginLoading: function(data) {
            if (data) {
                Ext.apply(this.data, data);
            }
            this.loading_complete = false;
        },
        endLoading: function() {
            this.loading_complete = true;
        },
        isComplete: function() {
            return this.loading_complete;
        },
        isLoaded: function(field_name) {
            return (this.LoadedFields.indexOf(field_name) >= 0);
        },
        markAsLoaded: function(field_name) {
            if (!this.isLoaded(field_name)) {
                this.LoadedFields.push(field_name);
            }
        },
        get: function(field_name) {
            return !Ext.isEmpty(this.data[field_name]) ? this.data[field_name] : null;
        }
    },
    setDefaultGoodsPackValues: function() {
        var bf = this.EditForm.getForm();
        var unit_combo = bf.findField('GoodsUnit_sid');
        var unit_id = null;
        unit_combo.getStore().removeAll();
        unit_combo.getStore().load({callback:function(){
        	unit_combo.getStore().each(function(record) {
	            if (record.get('GoodsUnit_Name') == 'упаковка') {
	                unit_id = record.get('GoodsUnit_id');
	            }
	        });

	        unit_combo.setValue(unit_id);
        }})
        
        bf.findField('GoodsPackCount_sCount').setValue(1);
    },
	show: function() {
		sw.Promed.swEvnDrugEditWindow.superclass.show.apply(this, arguments);
		var bf = this.EditForm.getForm();
		var form = this;
		this.EditForm.getForm().reset();
		this.type = null;
		this.action = null;
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.firstLoad = null;
		this.DrugPackage = false;

		this.center();

		if (!arguments[0]) {
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi'], function() {this.hide();}.createDelegate(this));
			return false;
		}
		bf.findField('EvnDrug_rid').setValue(arguments[0].formParams.EvnDrug_rid);
        this.EvnDrug_rid = arguments[0].formParams.EvnDrug_rid;

		this.clearValues();
		bf.isFirst = 1;
		
		bf.findField('DocumentUcStr_oid').getStore().removeAll();
		bf.findField('Drug_id').getStore().removeAll();

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		
		if ( arguments[0].type ) {
			this.type = arguments[0].type;
		}
		
		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		
		if ( arguments[0].mode ) {
			this.documentUcStrMode = arguments[0].mode;
		}

        if ( arguments[0].onHide ) {
            this.onHide = arguments[0].onHide;
        }

        this.parentEvnClass_SysNick = arguments[0].parentEvnClass_SysNick||'';
        this.openMode = arguments[0].openMode||'';
		
		this.findById('EDEW_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = bf.findField('EvnDrug_begDate');
				//clearDateAfterPersonDeath('personpanelid', 'EDEW_PersonInformationFrame', field); // взрывается 
			}
		});
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

        this.setDefaultGoodsPackValues();
		bf.setValues(arguments[0].formParams);

		bf.findField('EvnDrug_pid').showContainer();
		if (this.parentEvnClass_SysNick == 'EvnUslugaOper') {
			bf.findField('EvnDrug_pid').hideContainer();
		}

		bf.findField('EvnDrug_pid').getStore().removeAll();
		
		if ( arguments[0].parentEvnComboData ) {
			bf.findField('EvnDrug_pid').getStore().loadData(arguments[0].parentEvnComboData);
		}
		
		var evn_combo = bf.findField('EvnDrug_pid');
		var evn_drug_pid = null;
		var set_date = true;

        this.findById('edewEvnPrescrPanel').setVisible(this.openMode == 'prescription');
        if (this.openMode == 'prescription') {
            bf.findField('EvnPrescrTreat_Fact').maxValue = bf.findField('PrescrFactCountDiff').getValue();
            bf.clearInvalid();
        } else {
			bf.findField('EvnPrescrTreat_Fact').maxValue = undefined;
            bf.clearInvalid();
		}

        //сбрасываем параметры загрузки данных
        this.FormLoadState.reset();
        bf.findField('EvnDrug_Kolvo_Show').setValue(null);

		if (this.action!='add') {
			this.firstLoad = 1;
            this.findById('EvnDrugEditForm').getForm().load({
				params: {
					EvnDrug_id: bf.findField('EvnDrug_id').getValue(),
					archiveRecord: form.archiveRecord
				},
				failure: function() {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							form.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
						title: lang['oshibka']
					});
				},
				success: function(result_form, action) {
                    //сохраняем данные полученные при загрузке формы и помечаем начало загрузки
                    if (action && action.result && action.result.data) {
                        this.FormLoadState.beginLoading(action.result.data);
                        var result = Ext.util.JSON.decode(action.response.responseText);
                        this.FormLoadState.data.GoodsUnit_id = result[0].GoodsUnit_id;
                        bf.findField('GoodsUnit_sid').setValue(this.FormLoadState.data.GoodsUnit_id);
                    }

					// Что надо сделать при чтении
					bf.findField('DrugPrepFas_id').getStore().baseParams.DocumentUcStr_id = bf.findField('DocumentUcStr_id').getValue();
					bf.findField('DrugPrepFas_id').getStore().baseParams.date = bf.findField('EvnDrug_setDate').getValue();

                    if (bf.findField('EvnPrescr_id').getValue()) {
                        form.openMode = 'prescription';
                    } else {
                        form.openMode = '';
                    }
                    form.findById('edewEvnPrescrPanel').setVisible(form.openMode == 'prescription');
                    if (form.openMode == 'prescription') {
                        bf.findField('EvnPrescrTreat_Fact').maxValue = bf.findField('PrescrFactCountDiff').getValue();
                    }

                    setCurrentDateTime({
						callback: function() {
							if (set_date) {
								//bf.findField('EvnDrug_setDate').fireEvent('change', bf.findField('EvnDrug_setDate'), bf.findField('EvnDrug_setDate').getValue());
								var newValue = bf.findField('EvnDrug_setDate').getValue();
								form.setFilterLpuSection(newValue);
								var base_form = form.findById('EvnDrugEditForm').getForm();

								var evn_combo = base_form.findField('EvnDrug_pid');
								var lpu_section_combo = base_form.findField('LpuSection_id');
								var lpu_section_id = lpu_section_combo.getValue();
								lpu_section_combo.clearValue();

								var section_filter_params = {};
								/*
								var user_lpu_section_id = this.UserLpuSection_id;
								var user_lpu_sections = this.UserLpuSections;
								*/
								if ( newValue ) {
									section_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
								}

								setLpuSectionGlobalStoreFilter(section_filter_params);

								lpu_section_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

								if (!lpu_section_combo.getStore().getById(lpu_section_id) && !Ext.isEmpty(evn_combo.getValue())) {
									lpu_section_id = evn_combo.getFieldValue('LpuSection_id');
								}

								if ( lpu_section_combo.getStore().getById(lpu_section_id) ) {
									// значение
									lpu_section_combo.setValue(lpu_section_id);
									// и вызов при изменении этого значения
									lpu_section_combo.fireEvent('change', lpu_section_combo, lpu_section_id);
								}

                                /*
                                 log(bf.findField('Drug_id').getStore().baseParams);
                                 log(bf.findField('DrugPrepFas_id').getStore().baseParams);
                                 */
                                base_form.findField('Drug_id').getStore().baseParams = {
                                    Drug_id: !Ext.isEmpty(form.FormLoadState.data.Drug_id) ? form.FormLoadState.data.Drug_id : null,
                                    mode: this.documentUcStrMode,
                                    LpuSection_id: base_form.findField('LpuSection_id').getValue(),
                                    date: Ext.util.Format.date(newValue,'d.m.Y')
                                };
                                base_form.findField('DrugPrepFas_id').getStore().baseParams = {
                                    mode: this.documentUcStrMode,
                                    LpuSection_id: base_form.findField('LpuSection_id').getValue(),
                                    date: Ext.util.Format.date(newValue,'d.m.Y')
                                };
                                form.loadSpr({
                                    callback: function() {
                                        form.FormLoadState.endLoading();
                                    }
                                });
							} else {
								form.loadSpr({
                                    callback: function() {
                                        form.FormLoadState.endLoading();
                                    }
                                });
							}
							bf.findField('LpuSection_id').focus(true, 500);
							//bf.findField('DocumentUcStr_oid').fireEvent('change', bf.findField('DocumentUcStr_oid'), bf.findField('DocumentUcStr_oid').getValue());
						},
						dateField: bf.findField('EvnDrug_setDate'),
						loadMask: false,
						setDate: set_date,
						setDateMaxValue: true,
						setDateMinValue: false,
						setTime: false,
						timeField: bf.findField('EvnDrug_setTime'),
						windowId: this.id
					});
				}.createDelegate(this),
				url: '/?c=EvnDrug&m=loadEvnDrugEditForm'
			});
		} else {
			// Даты и отделение
			if ( evn_combo.getStore().getCount() > 0)
			{
                var evn_combo_rec = null
				if (getRegionNick().inlist(['perm'])) {
					evn_combo_rec = evn_combo.getStore().data.last();
				} else {
					evn_combo_rec = evn_combo.getStore().data.first();
				}
				evn_combo.setValue(evn_combo_rec.get('Evn_id'));
				evn_combo.fireEvent('change', evn_combo, evn_combo_rec.get('Evn_id'), 0);
                if (evn_combo_rec.get('Evn_disDate')) {
                    //TODO: Заполняем дату выписки.
                    bf.findField('EvnDrug_setDate').setValue(evn_combo_rec.get('Evn_disDate'));
                    set_date = false;
                }
			}
            if (!bf.findField('EvnDrug_setDate').getValue()) {
                setCurrentDateTime({
                    callback: function()
                    {
                        bf.findField('EvnDrug_setDate').fireEvent('change', bf.findField('EvnDrug_setDate'), bf.findField('EvnDrug_setDate').getValue());
                        form.loadSpr();
                        bf.findField('LpuSection_id').focus(true, 500);
                    },
                    dateField: bf.findField('EvnDrug_setDate'),
                    loadMask: false,
                    setDate: set_date,
                    setDateMaxValue: true,
                    setDateMinValue: false,
                    setTime: false,
                    timeField: bf.findField('EvnDrug_setTime'),
                    windowId: this.id
                });
            } else {
                bf.findField('EvnDrug_setDate').fireEvent('change', bf.findField('EvnDrug_setDate'), bf.findField('EvnDrug_setDate').getValue());
                form.loadSpr();
                bf.findField('LpuSection_id').focus(true, 500);
            }
		}

		switch ( this.action ) {
			case 'add':
				this.enableEdit(true);
				this.setTitle(lang['stroka_dokumenta_dobavlenie']);
				loadMask.hide();
			break;

			case 'edit':
				this.enableEdit(true);
				this.setTitle(lang['stroka_dokumenta_redaktirovanie']);
				loadMask.hide();
			break;

			case 'view':
				this.enableEdit(false);
				this.setTitle(lang['stroka_dokumenta_prosmotr']);
				loadMask.hide();
				this.buttons[this.buttons.length - 1].focus();
			break;
		}
        this.doLayout();
        this.syncSize();
	},
	doSave: function()
	{
		if (this.action != 'add' && this.action != 'edit')
		{
			return false;
		}
		var bf = this.findById('EvnDrugEditForm').getForm();
		var form = this;
		if (!bf.isValid())
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.findById('EvnDrugEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		var MSF = sw.Promed.MedStaffFactByUser.last;
		loadMask.show();

		bf.submit(
		{
			failure: function(result_form, action)
			{
				loadMask.hide();

				if ( action.result )
				{
					if ( action.result.Error_Msg )
					{
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else
					{
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
					}
				}
			}.createDelegate(this),
			params:
			{	'MSF_LpuSection_id': (MSF)?MSF.LpuSection_id: null,
				'MSF_MedPersonal_id' : (MSF)?MSF.MedPersonal_id : null,
				'MSF_MedService_id' : (MSF)?MSF.MedService_id : null,
				'EvnDrug_Price': bf.findField('EvnDrug_Price').getValue(),
				'EvnDrug_Sum': bf.findField('EvnDrug_Sum').getValue(),
				'GoodsUnit_id': bf.findField('GoodsUnit_sid').getValue()
			},
			success: function(result_form, action) {
				loadMask.hide();

				var data = {};
				bf.findField('EvnDrug_id').setValue(action.result.EvnDrug_id);

				var Drug_Code = '',
                    Drug_Name = '',
                    Mol_Name = '',
                    DrugPrep_Name = '',
                    DocumentUcStr_Name = '',
                    EvnPrescrTreatDrug_FactCount = 1;

                if (action.result.EvnPrescrTreatDrug_FactCount) {
                    EvnPrescrTreatDrug_FactCount = action.result.EvnPrescrTreatDrug_FactCount;
                }

				var record = bf.findField('Drug_id').getStore().getById(bf.findField('Drug_id').getValue());
				if ( record ) {
					Drug_Code = record.get('Drug_Code');
					Drug_Name = record.get('Drug_FullName');
				}

                record = bf.findField('DrugPrepFas_id').getStore().getById(bf.findField('DrugPrepFas_id').getValue());
                if ( record ) {
                    DrugPrep_Name = record.get('DrugPrep_Name');
                }

                record = bf.findField('DocumentUcStr_oid').getStore().getById(bf.findField('DocumentUcStr_oid').getValue());
                if ( record ) {
                    DocumentUcStr_Name = record.get('DocumentUcStr_Name');
                }

                record = bf.findField('Mol_id').getStore().getById(bf.findField('Mol_id').getValue());
                if ( record ) {
                    Mol_Name = record.get('Mol_Name');
                }
                
				data.evnDrugData = {
					'accessType': 'edit',
					'EvnClass_SysNick': 'EvnDrug',
                    'EvnDrug_id': bf.findField('EvnDrug_id').getValue(),
                    'Drug_id': bf.findField('Drug_id').getValue(),
                    'DrugPrepFas_id': bf.findField('DrugPrepFas_id').getValue(),
                    //'DrugPrep_Name': DrugPrep_Name,
                    'DocumentUcStr_oid': bf.findField('DocumentUcStr_oid').getValue(),
                    //'DocumentUcStr_Name': DocumentUcStr_Name,
                    'Mol_id': bf.findField('Mol_id').getValue(),
                    //'Mol_Name': Mol_Name,
					'Drug_Code': Drug_Code,
					'Drug_Name': Drug_Name,
                    'EvnDrug_setDate': bf.findField('EvnDrug_setDate').getValue(),
                    'EvnDrug_setTime': bf.findField('EvnDrug_setTime').getValue(),
                    'EvnDrug_Kolvo': bf.findField('EvnDrug_Kolvo').getValue(),
                    'EvnDrug_KolvoEd': bf.findField('EvnDrug_KolvoEd').getValue(),
                    'EvnPrescrTreatDrug_FactCount': EvnPrescrTreatDrug_FactCount,
					'EvnPrescr_id':bf.findField('EvnPrescr_id').getValue()
				};
				//log(data.evnDrugData);
				this.callback(data);
				this.hide();
			}.createDelegate(this)
		});
        return true;
	},
	enableEdit: function(enable) {
		var bf = this.findById('EvnDrugEditForm').getForm();

		bf.findField('EvnDrug_pid').setDisabled(!enable);
		bf.findField('EvnDrug_setDate').setDisabled(!enable);
		bf.findField('EvnDrug_setTime').setDisabled(!enable);
		bf.findField('LpuSection_id').setDisabled(!enable);
		bf.findField('Mol_id').setDisabled(!enable);
        bf.findField('EvnPrescrTreatDrug_id').setDisabled(!enable);
        bf.findField('EvnPrescrTreat_Fact').setDisabled(!enable);
        bf.findField('DrugPrepFas_id').setDisabled(!enable);
        bf.findField('Drug_id').setDisabled(!enable);
        bf.findField('DocumentUcStr_oid').setDisabled(!enable);
        bf.findField('GoodsUnit_sid').setDisabled(!enable);
        bf.findField('EvnDrug_Kolvo_Show').setDisabled(!enable);
        bf.findField('EvnDrug_Kolvo').setDisabled(!enable);
		bf.findField('EvnDrug_KolvoEd').setDisabled(!enable);

        if (enable) {
            this.buttons[0].enable();
        } else {
            this.buttons[0].disable();
        }
	},
	loadSpr: function(options) {
		var form = this;
        var bf = this.findById('EvnDrugEditForm').getForm();
        var mol_loaded = false;
        var ep_loaded = false;

		form.findById('edewMol_id').getStore().load({
			callback: function() {
				form.setFilterMol(form.findById('edewLpuSection_id').getValue());

                //отмечаем факт завершения загрузки комбобокса, если остальные комбобоксы также закончили загрузку, вызываем callback функции
                mol_loaded = true;
                if (ep_loaded && options && options.callback) {
                    options.callback();
                }
			}
		});

        var ep_combo = bf.findField('EvnPrescrTreatDrug_id');
        ep_combo.getStore().removeAll();
        ep_combo.getStore().baseParams = {};

        if (bf.findField('EvnPrescr_id').getValue()) {
            ep_combo.getStore().baseParams.EvnPrescrTreat_id = bf.findField('EvnPrescr_id').getValue();
        } else if (bf.findField('EvnDrug_pid').getValue()) {
            ep_combo.getStore().baseParams.EvnPrescrTreat_pid = bf.findField('EvnDrug_pid').getValue();
        }

        if (ep_combo.getValue()) {
            ep_combo.getStore().load({
                callback: function() {
                    ep_combo.getStore().each(function(rec){
                        if (rec.get('EvnPrescrTreatDrug_id')==ep_combo.getValue()) {
                            ep_combo.setValue(ep_combo.getValue());
                            if (rec.get('Drug_id') && form.action == 'add') {
                                ep_combo.fireEvent('change', ep_combo, ep_combo.getValue());
                            } else {
                                bf.findField('EvnCourse_id').setValue(rec.get('EvnCourse_id'));
                                bf.findField('EvnCourseTreatDrug_id').setValue(rec.get('EvnCourseTreatDrug_id'));
                                bf.findField('EvnPrescr_id').setValue(rec.get('EvnPrescrTreat_id'));
                                bf.findField('PrescrFactCountDiff').setValue(rec.get('PrescrFactCountDiff'));
                                //bf.findField('EvnPrescrTreat_Fact').setValue((form.action == 'add')?1:null);
                                bf.findField('EvnPrescrTreat_Fact').maxValue = bf.findField('PrescrFactCountDiff').getValue();
                                bf.findField('GoodsUnit_sid').setValue(rec.get('GoodsUnit_sid'));
                                bf.findField('GoodsUnit_sid').fireEvent('change',bf.findField('GoodsUnit_sid'),rec.get('GoodsUnit_sid'));
                                bf.findField('GoodsPackCount_sCount').setValue(rec.get('GoodsPackCount_sCount'));
                                bf.findField('GoodsPackCount_sCount').fireEvent('change',bf.findField('GoodsPackCount_sCount'),rec.get('GoodsPackCount_sCount'));
                            }
                            return false;
                        }
                        return true;
                    });

                    //отмечаем факт завершения загрузки комбобокса, если остальные комбобоксы также закончили загрузку, вызываем callback функции
                    ep_loaded = true;
                    if (mol_loaded && options && options.callback) {
                        options.callback();
                    }
                }
            });
        } else {
            ep_loaded = true;
        }

        if (mol_loaded && ep_loaded && options && options.callback) {
            options.callback();
        }
	},
	setFilterLpuSection: function(newValue) {
		var bf = this.findById('EvnDrugEditForm').getForm();
		var ls_id = bf.findField('LpuSection_id').getValue();

		if (this.parentEvnClass_SysNick != 'EvnUslugaOper') {
			var EvnPS_id = this.EvnDrug_rid;
			if (EvnPS_id != null) {
				if (EvnPS_id != bf.findField('EvnDrug_pid').getValue()) {
					bf.findField('LpuSection_id').clearValue();
				}
			} else {
				bf.findField('LpuSection_id').clearValue();
			}
		}

		if (!newValue) {
			if(this.type != null){
				setLpuSectionGlobalStoreFilter({});
			} else {
				setLpuSectionGlobalStoreFilter({
					isStac: this.openMode != 'prescription'
				});
			}
		} else {
			if(this.type != null){
				setLpuSectionGlobalStoreFilter( {
					onDate: Ext.util.Format.date(newValue, 'd.m.Y')
				});
			} else {
				setLpuSectionGlobalStoreFilter(
				{
					//isStac: this.openMode != 'prescription',
					onDate: Ext.util.Format.date(newValue, 'd.m.Y')
				});
			}
		}
		bf.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

		if (bf.findField('LpuSection_id').getStore().getById(ls_id)) {
			bf.findField('LpuSection_id').setValue(ls_id);
		}
	},
	setFilterMol: function(LpuSection_id) {
		// Устанавливаем фильтр и если по условиям фильтра найдена только одна запись - то устанавливаем эту запись
		var form = this;
		var combo = form.findById('edewMol_id');
		var co = 0;
		var CurrentMol_id = null;
		var OldMol_id = combo.getValue();
		var Yes = false;

        combo.getStore().clearFilter();
        combo.lastQuery = '';

		if (combo.getStore().getCount()>0) {
			combo.getStore().filterBy(function(record) {
                var filtered = ((LpuSection_id == record.get('LpuSection_id') && LpuSection_id > 0 ) || (!form.FormLoadState.isComplete() && form.FormLoadState.get('Mol_id') == record.get('Mol_id')));
				if (filtered) {
					co++;
                    CurrentMol_id = record.get('Mol_id');
					if (OldMol_id == CurrentMol_id) {
						Yes = true;
					}
				}
				return filtered;
			});

            //похоже это сохранение ранее выбранного МОЛ-а, при условии что оно прошло фильтрацию при выборе нового отделения
			if (Yes) {
				form.findById('edewMol_id').setValue(OldMol_id);
			} else {
				if (co==1) {
					form.findById('edewMol_id').setValue(CurrentMol_id);
				} else {
					form.findById('edewMol_id').setValue(null);
				}
			}
		}
	},
	clearValues: function(enable)
	{
		var bf = this.findById('EvnDrugEditForm').getForm();
        bf.reset();
		bf.findField('EvnDrug_id').setValue(null);
		bf.findField('EvnDrug_setDate').setValue(null);
		bf.findField('EvnDrug_setTime').setValue(null);
		bf.findField('EvnDrug_pid').setValue(null);
		bf.findField('Server_id').setValue(null);
		bf.findField('Drug_id').setValue(null);
		bf.findField('LpuSection_id').setValue(null);
		bf.findField('EvnDrug_Price').setValue(null);
		bf.findField('EvnDrug_Sum').setValue(null);
		bf.findField('DocumentUc_id').setValue(null);
		bf.findField('DocumentUcStr_id').setValue(null);
		bf.findField('DocumentUcStr_oid').setValue(null);
		bf.findField('Mol_id').setValue(null);
		bf.findField('EvnDrug_Kolvo').setValue(null);
		bf.findField('EvnDrug_KolvoEd').setValue(null);
		bf.findField('DrugPrepFas_id').setValue(null);
		bf.findField('EvnPrescrTreat_Fact').setValue(null);
		bf.findField('EvnPrescr_id').setValue(null);
		bf.findField('EvnCourse_id').setValue(null);
		bf.findField('EvnPrescrTreatDrug_id').setValue(null);
		bf.findField('EvnCourseTreatDrug_id').setValue(null);
		//bf.findField('EvnDrug_RealKolvo').setValue(null);

	},
	initComponent: function() {
        var wnd = this;

		this.EditForm = new Ext.form.FormPanel(
		{
			autoScroll: true,
			bodyStyle: 'padding: 0.5em;',
			border: false,
			frame: true,
			id: 'EvnDrugEditForm',
			items:
			[{
				name: 'EvnDrug_id',
				value: null,
				xtype: 'hidden'
			},
			{
				name: 'EvnDrug_rid',
				value: null,
				xtype: 'hidden'
			},
			{
				name: 'Person_id',
				value: null,
				xtype: 'hidden'
			},
			{
				name: 'DocumentUc_id',
				value: null,
				xtype: 'hidden'
			},
			{
				name: 'DocumentUcStr_id',
				value: null,
				xtype: 'hidden'
			},
			{
				name: 'PersonEvn_id',
				value: null,
				xtype: 'hidden'
			},
			{
				name: 'Server_id',
				value: -1,
				xtype: 'hidden'
			},
			{
				allowBlank: false,
				displayField: 'Evn_Name',
				editable: false,
				enableKeyEvents: true,
				fieldLabel: lang['otdelenie'],
				tabIndex: TABINDEX_EDEW + 0,
				hiddenName: 'EvnDrug_pid',
				listeners:
				{
					'change': function(combo, newValue, oldValue)
					{
						var base_form = this.findById('EvnDrugEditForm').getForm();
						var record = combo.getStore().getById(newValue);
						if ( record ) {
							if ( base_form.findField('LpuSection_id').getStore().getById(record.get('LpuSection_id')) ) {
								base_form.findField('LpuSection_id').setValue(record.get('LpuSection_id'));
                                base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), record.get('LpuSection_id'));
							}
						}
					}.createDelegate(this),
					'keydown': function (inp, e) {
						if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
							e.stopEvent();
							this.buttons[this.buttons.length - 1].focus();
						}
						else if ( e.getKey() == Ext.EventObject.DELETE ) {
							e.stopEvent();
							inp.clearValue();
						}
					}.createDelegate(this)
				},
				listWidth: 600,
				mode: 'local',
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{ name: 'Evn_id', type: 'int' },
						{ name: 'MedStaffFact_id', type: 'int' },
						{ name: 'LpuSection_id', type: 'int' },
						{ name: 'MedPersonal_id', type: 'int' },
						{ name: 'Evn_Name', type: 'string' },
						{ name: 'Evn_setDate', type: 'date', format: 'd.m.Y' },
                        { name: 'Evn_disDate', type: 'date', format: 'd.m.Y' } // TODO: Дата выписки пациентов, получаем из swEvnPSEditWindow.js
					],
					id: 'Evn_id'
				}),
				tabIndex: TABINDEX_EUCOMEF + 1,
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'{Evn_Name}&nbsp;',
					'</div></tpl>'
				),
				triggerAction: 'all',
				valueField: 'Evn_id',
				anchor: '98%',
				xtype: 'combo'
			},
			{
				border: false,
				layout: 'column',
				items:
				[{
					border: false,
					layout: 'form',
					items:
					[{
						allowBlank: false,
						fieldLabel: lang['data'],
						format: 'd.m.Y',
						listeners:
						{
							'change': function(field, newValue, oldValue)
							{
                                this.setFilterLpuSection(newValue);
								var base_form = this.findById('EvnDrugEditForm').getForm();

								var evn_combo = base_form.findField('EvnDrug_pid');
								var lpu_section_combo = base_form.findField('LpuSection_id');
								var lpu_section_id = lpu_section_combo.getValue();
								lpu_section_combo.clearValue();

								var section_filter_params = {};
								/*
								var user_lpu_section_id = this.UserLpuSection_id;
								var user_lpu_sections = this.UserLpuSections;
								*/
								if ( newValue ) {
									section_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
								}

								setLpuSectionGlobalStoreFilter(section_filter_params);

								lpu_section_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

								if (!lpu_section_combo.getStore().getById(lpu_section_id) && !Ext.isEmpty(evn_combo.getValue())) {
									lpu_section_id = evn_combo.getFieldValue('LpuSection_id');
								}

								if ( lpu_section_combo.getStore().getById(lpu_section_id) ) {
									// значение
									lpu_section_combo.setValue(lpu_section_id);
									// и вызов при изменении этого значения
									lpu_section_combo.fireEvent('change', lpu_section_combo, lpu_section_id);
								}

                                /*
                                 log(bf.findField('Drug_id').getStore().baseParams);
                                 log(bf.findField('DrugPrepFas_id').getStore().baseParams);
                                 */
                                base_form.findField('Drug_id').getStore().baseParams =
                                {
                                    mode: this.documentUcStrMode,
                                    LpuSection_id: base_form.findField('LpuSection_id').getValue(),
                                    date: Ext.util.Format.date(newValue,'d.m.Y')
                                };
                                base_form.findField('DrugPrepFas_id').getStore().baseParams = {
                                    mode: this.documentUcStrMode,
                                    LpuSection_id: base_form.findField('LpuSection_id').getValue(),
                                    date: Ext.util.Format.date(newValue,'d.m.Y')
                                };

							}.createDelegate(this),
							'keydown': function(inp, e)
							{
								if (e.getKey() == Ext.EventObject.TAB && e.shiftKey == true)
								{
									e.stopEvent();
									this.buttons[this.buttons.length - 1].focus();
								}
							}.createDelegate(this)
						},
						name: 'EvnDrug_setDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						selectOnFocus: true,
						tabIndex: TABINDEX_EDEW + 1,
						width: 100,
						xtype: 'swdatefield'
					}]
				},
				{
					border: false,
					labelWidth: 50,
					layout: 'form',
					items:
					[{
						fieldLabel: lang['vremya'],
						listeners:
						{
							'keydown': function (inp, e)
							{
								if ( e.getKey() == Ext.EventObject.F4 )
								{
									e.stopEvent();
									inp.onTriggerClick();
								}
							}
						},
						name: 'EvnDrug_setTime',
						onTriggerClick: function()
						{
							var bf = this.findById('EvnDrugEditForm').getForm();
							var time_field = bf.findField('EvnDrug_setTime');
							if ( time_field.disabled )
							{
								return false;
							}
							setCurrentDateTime(
							{
								dateField: bf.findField('EvnDrug_setDate'),
								loadMask: true,
								setDate: true,
								setDateMaxValue: true,
								setDateMinValue: false,
								setTime: true,
								timeField: time_field,
								windowId: 'EvnDrugEditForm'
							});
						}.createDelegate(this),
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						tabIndex: TABINDEX_EDEW + 2,
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}]
				}]
			},
			{
				allowBlank: false,
				anchor: '98%',
				fieldLabel: 'Отделение МОЛ',
				hiddenName: 'LpuSection_id',
				id: 'edewLpuSection_id',
				lastQuery: '',
				//listWidth: 650,
				linkedElements: [ ],
				tabIndex: TABINDEX_EDEW + 3,
				xtype: 'swlpusectionglobalcombo',
				listeners: {
					change: function(combo) {
						var bf = this.findById('EvnDrugEditForm').getForm();
						bf.findField('Drug_id').getStore().baseParams =
						{
							mode: this.documentUcStrMode,
							LpuSection_id: bf.findField('LpuSection_id').getValue(),
							date: Ext.util.Format.date(bf.findField('EvnDrug_setDate').getValue(),'d.m.Y')
						};
						bf.findField('DrugPrepFas_id').getStore().baseParams =
						{
							mode: this.documentUcStrMode,
							LpuSection_id: bf.findField('LpuSection_id').getValue(),
							date: Ext.util.Format.date(bf.findField('EvnDrug_setDate').getValue(),'d.m.Y')
						};

						combo.ownerCt.findById('edewMol_id').setDisabled(!(combo.getValue()>0) || wnd.action == 'view');

						if (combo.getValue() > 0) {
							// Установка фильтра на MOL
							combo.ownerCt.ownerCt.setFilterMol(combo.getValue());
						} else {
							bf.findField('Mol_id').setValue(null);
						}

						// при изменении отделения список медикаментов тоже перечитываем, поскольку список читается именно на отделение
                        var dpf_combo = bf.findField('DrugPrepFas_id');
                        wnd.DrugPackage = false;
						if (dpf_combo.getValue() > 0) {
                            dpf_combo.getStore().baseParams.DrugPrepFas_id = dpf_combo.getValue();
                            dpf_combo.getStore().baseParams.DocumentUcStr_id = bf.findField('DocumentUcStr_id').getValue();
                            dpf_combo.getStore().load({
								callback: function() {
                                    var index = dpf_combo.getStore().findBy(function(rec) { return rec.get('DrugPrepFas_id') == dpf_combo.getValue(); });
                                    if (index < 0) {
                                        dpf_combo.setValue(null);
                                    }
                                    dpf_combo.setValue(dpf_combo.getValue());
                                    dpf_combo.fireEvent('change', dpf_combo, dpf_combo.getValue());
                                    dpf_combo.getStore().baseParams.DrugPrepFas_id =null;
								}
							});
						}
					}.createDelegate(this)
				}
			},
			{
				allowBlank: false,
				anchor: '98%',
				hiddenName: 'Mol_id',
				id: 'edewMol_id',
				lastQuery: '',
				//listWidth: 650,
				linkedElements: [ ],
				tabIndex: TABINDEX_EDEW + 4,
				xtype: 'swmolcombo'
			},{
                id: 'edewEvnPrescrPanel',
                border: false,
                layout: 'form',
                items: [{
                    allowBlank: true,
                    anchor: '98%',
                    displayField: 'Drug_Name',
                    enableKeyEvents: true,
                    fieldLabel: lang['naznachenie'],
                    forceSelection: true,
                    hiddenName: 'EvnPrescrTreatDrug_id',
                    //tabIndex: TABINDEX_EDEW + 9,
                    value: null,
                    triggerAction: 'all',
                    loadingText: lang['idet_poisk'],
                    minChars: 1,
                    minLength: 1,
                    minLengthText: lang['pole_doljno_byit_zapolneno'],
                    mode: 'remote',
                    resizable: true,
                    selectOnFocus: true,
                    valueField: 'EvnPrescrTreatDrug_id',
                    width: 500,
                    xtype: 'combo',
                    listeners: {
                        'change': function(combo, newValue) {
                            var bf = this.findById('EvnDrugEditForm').getForm();
                            var record = combo.getStore().getById(newValue);
                            if ( record )
                            {
                                //При выборе назначения автоматически
                                // подставлять подходящий медикамент с остатков отделения с возможностью изменить
                                // Подставлять единственную, либо первые попавшие упаковки и соответствующие партии медикамента автоматически.
                                // подставить количество из назначения
                                bf.findField('EvnCourse_id').setValue(record.get('EvnCourse_id'));
                                bf.findField('EvnCourseTreatDrug_id').setValue(record.get('EvnCourseTreatDrug_id'));
                                bf.findField('EvnPrescr_id').setValue(record.get('EvnPrescrTreat_id'));
                                bf.findField('GoodsUnit_sid').setValue(record.get('GoodsUnit_sid'));
                                bf.findField('GoodsUnit_sid').fireEvent('change',bf.findField('GoodsUnit_sid'),record.get('GoodsUnit_sid'));
                                bf.findField('GoodsPackCount_sCount').setValue(record.get('GoodsPackCount_sCount'));

                                // Расчет ед.
                                bf.findField('DocumentUcStr_Count').setValue(record.get('DocumentUcStr_Count'));
                                bf.findField('Drug_Fas').setValue(record.get('Drug_Fas'));
                                //bf.findField('DocumentUcStr_EdCount').setValue((bf.findField('Drug_Fas').getValue() * record.get('DocumentUcStr_Count')).toFixed(2));
                                bf.findField('DocumentUcStr_EdCount').setValue((bf.findField('GoodsPackCount_sCount').getValue() * record.get('DocumentUcStr_Count')).toFixed(4));

                                bf.findField('Drug_id').setValue(record.get('Drug_id'));
                                bf.findField('DrugPrepFas_id').setValue(record.get('DrugPrepFas_id'));
                                bf.findField('EvnDrug_KolvoEd').setValue(record.get('EvnDrug_KolvoEd'));
                                bf.findField('EvnDrug_Kolvo').setValue(record.get('EvnDrug_Kolvo'));
                                bf.findField('DocumentUcStr_oid').setValue(record.get('DocumentUcStr_oid'));
                                bf.findField('Mol_id').setValue(record.get('Mol_id'));
                                bf.findField('PrescrFactCountDiff').setValue(record.get('PrescrFactCountDiff'));
                                bf.findField('EvnPrescrTreat_Fact').maxValue = bf.findField('PrescrFactCountDiff').getValue();
                                if (!bf.findField('EvnPrescrTreat_Fact').getValue()) {
                                    bf.findField('EvnPrescrTreat_Fact').setValue(record.get('EvnPrescrTreat_Fact'))
                                }
                                bf.findField('Drug_id').fireEvent('change', bf.findField('Drug_id'), bf.findField('Drug_id').getValue());
                            }
                            else
                            {
                                combo.setValue(null);
                                bf.findField('EvnCourse_id').setValue(null);
                                bf.findField('EvnCourseTreatDrug_id').setValue(null);
                                bf.findField('EvnPrescr_id').setValue(null);
                                bf.findField('PrescrFactCountDiff').setValue(0);
                                bf.findField('EvnPrescrTreat_Fact').setValue(0);
                            }
                            return true;
                        }.createDelegate(this)
                    },
                    store: new Ext.data.Store({
                        autoLoad: false,
                        reader: new Ext.data.JsonReader({
                            id: 'EvnPrescrTreatDrug_id'
                        }, [
                            { name: 'EvnPrescrTreatDrug_id', mapping: 'EvnPrescrTreatDrug_id' },
                            { name: 'EvnCourse_id', mapping: 'EvnCourse_id' },
                            { name: 'EvnCourseTreatDrug_id', mapping: 'EvnCourseTreatDrug_id' },
                            { name: 'EvnPrescrTreat_id', mapping: 'EvnPrescrTreat_id' },
                            { name: 'EvnPrescrTreat_pid', mapping: 'EvnPrescrTreat_pid' },
                            { name: 'EvnPrescrTreat_setDate', mapping: 'EvnPrescrTreat_setDate', type: 'date', dateFormat: 'd.m.Y' },
                            { name: 'EvnPrescrTreat_PrescrCount', mapping: 'EvnPrescrTreat_PrescrCount' },
                            { name: 'EvnPrescrTreatDrug_FactCount', mapping: 'EvnPrescrTreatDrug_FactCount' },
                            { name: 'EvnPrescrTreatDrug_DoseDay', mapping: 'EvnPrescrTreatDrug_DoseDay' },
                            { name: 'PrescrFactCountDiff', mapping: 'PrescrFactCountDiff' },
                            { name: 'EvnPrescrTreat_Fact', mapping: 'EvnPrescrTreat_Fact' },
                            { name: 'Drug_id', mapping: 'Drug_id' },
                            { name: 'Drug_Fas', mapping: 'Drug_Fas' },
                            { name: 'DocumentUcStr_Count', mapping: 'DocumentUcStr_Count' },
                            { name: 'EvnDrug_KolvoEd', mapping: 'EvnDrug_KolvoEd' },
                            { name: 'EvnDrug_Kolvo', mapping: 'EvnDrug_Kolvo' },
                            { name: 'DrugPrepFas_id', mapping: 'DrugPrepFas_id' },
                            { name: 'DocumentUcStr_oid', mapping: 'DocumentUcStr_oid' },
                            { name: 'DocumentUcStr_Name', mapping: 'DocumentUcStr_Name' },
                            { name: 'Mol_id', mapping: 'Mol_id' },
                            { name: 'Mol_Name', mapping: 'Mol_Name' },
                            { name: 'DrugFinance_Name', mapping: 'DrugFinance_Name' },
                            { name: 'WhsDocumentCostItemType_Name', mapping: 'WhsDocumentCostItemType_Name' },
                            //{ name: 'DrugUnit_Name', mapping: 'DrugUnit_Name' },
                            { name: 'DrugForm_Name', mapping: 'DrugForm_Name' },
                            { name: 'Drug_Name', mapping: 'Drug_Name' },
                            { name: 'GoodsUnit_sid', mapping: 'GoodsUnit_sid' },
                            { name: 'GoodsPackCount_sCount', mapping: 'GoodsPackCount_sCount' }
                        ]),
                        url: '/?c=EvnPrescr&m=loadEvnPrescrTreatDrugCombo'
                    }),
                    /*
                     Медикамент1, дневная доза, Количество выполненных приемов./Количество приемов в сутки
                     */
                    tpl: new Ext.XTemplate(
                        '<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 8pt; font-weight: bold;">',
                        '<td style="padding: 2px; width: 60%;">Медикамент</td>',
                        '<td style="padding: 2px; width: 20%;">Дневная доза</td>',
                        '<td style="padding: 2px; width: 20%;">Приемов</td>',
                        '<tpl for="."><tr class="x-combo-list-item" style="font-family: tahoma; font-size: 8pt;">',
                        '<td style="padding: 2px;">{Drug_Name}&nbsp;</td>',
                        '<td style="padding: 2px;">{EvnPrescrTreatDrug_DoseDay}&nbsp;</td>',
                        '<td style="padding: 2px;">{EvnPrescrTreatDrug_FactCount}/{EvnPrescrTreat_PrescrCount}&nbsp;</td>',
                        '</tr></tpl>',
                        '</table>'
                    )
                },{
                    fieldLabel: lang['spisat_priemov'],
                    name: 'EvnPrescrTreat_Fact',
                    //tabIndex: TABINDEX_EDEW + 8,
                    width: 70,
                    value: 1,
                    minValue: 0,
                    maxValue: 1,
                    allowNegative: false,
                    allowDecimals: false,
                    listeners: {
                        'change': function(field, newValue, oldValue) {
                            var bf = this.findById('EvnDrugEditForm').getForm();
                            if (!oldValue) {
                                oldValue = 1;
                            }
                            if ( newValue>0 && newValue <= field.maxValue && oldValue <= field.maxValue )
                            {
                                var singleKolvoEd = bf.findField('EvnDrug_KolvoEd').getValue()/oldValue;
                                var newKolvoEd = singleKolvoEd*newValue;
                                /*log({
                                    action: 'EvnPrescrTreat_Fact change',
                                    newValue: newValue,
                                    oldValue: oldValue,
                                    EvnDrug_KolvoEd: bf.findField('EvnDrug_KolvoEd').getValue(),
                                    singleKolvoEd: singleKolvoEd,
                                    newKolvoEd: newKolvoEd
                                });*/
                                //bf.findField('EvnDrug_KolvoEd').setValue(newKolvoEd);
                                //bf.findField('EvnDrug_KolvoEd').fireEvent('change', bf.findField('EvnDrug_KolvoEd'), newKolvoEd);
                            }
                            return true;
                        }.createDelegate(this)
                    },
                    xtype: 'numberfield'
                },
                {
                    name: 'PrescrFactCountDiff',//Количество невыполненных приемов
                    value: 1,
                    xtype: 'hidden'
                },
                {
                    name: 'EvnPrescr_id',
                    value: null,
                    xtype: 'hidden'
                },
                {
                    name: 'EvnCourseTreatDrug_id',
                    value: null,
                    xtype: 'hidden'
                },
                {
                    name: 'EvnCourse_id',
                    value: null,
                    xtype: 'hidden'
                }]
            }, { // Первый комбобокс (медикамент)
					hiddenName: 'DrugPrepFas_id',
					anchor: '98%',
					lastQuery: '',
					tabIndex: TABINDEX_EDEW + 5,
					xtype: 'swdrugprepcombo',
					onTrigger1Click: function()
					{
						if (this.disabled)
							return false;
						if (this.getValue() && wnd.DrugPackage){
							sw.swMsg.alert('Сообщение', 'При данных настройках доступен только указанный медикамент');
            				return false;
						}
						var combo = this;
						var params = {};
						if (combo.getValue()>0)
						{
							combo.getStore().baseParams.DrugPrepFas_id= combo.getValue();
						}
						var form = combo.findForm();
						if (form)
						{
							params.Drug_id = (form.getForm().findField('Drug_id'))?form.getForm().findField('Drug_id').getValue():null;
						}
						params.load = 'torg';
						if (!this.isExpanded())
						{
							this.onFocus({});
							var newParams = combo.getStore().baseParams;
							newParams.load = 'torg';
							Ext.Ajax.request({
								url: '/?c=Farmacy&m=loadDrugPrepList',
								params: newParams,
								callback: function(options, success, response) {
									var result = Ext.util.JSON.decode(response.responseText);
									var bf = wnd.findById('EvnDrugEditForm').getForm();
									// если единственный  медикамент-упаковка-партия заполняем все сразу
									if(
										Ext.isEmpty(combo.getValue()) 
										&& result.length == 3 
										&& result[0].DrugPrepFas_id 
										&& result[1].Drug_id 
										&& result[2].DocumentUcStr_id
									) {
										wnd.DrugPackage = true;
										bf.findField('Drug_id').DrugPackage = true;
										var res1 = [];var res2 = [];var res3 = [];
										res1[0] = result[0];res2[0] = result[1];res3[0] = result[2];

										combo.getStore().loadData(res1);
										combo.setValue(result[0].DrugPrepFas_id);
										combo.fireEvent('change',combo,result[0].DrugPrepFas_id);
										combo.collapse();
										bf.findField('Drug_id').getStore().loadData(res2);
										bf.findField('Drug_id').setValue(result[1].Drug_id);
										bf.findField('Drug_id').fireEvent('change',bf.findField('Drug_id'),result[1].Drug_id);

										bf.findField('DocumentUcStr_oid').getStore().loadData(res3);
										bf.findField('DocumentUcStr_oid').setValue(result[2].DocumentUcStr_id);
										bf.findField('DocumentUcStr_oid').fireEvent('change',bf.findField('DocumentUcStr_oid'),result[2].DocumentUcStr_id);
										wnd.FormLoadState.markAsLoaded('DocumentUcStr_oid');
									} else {
										wnd.DrugPackage = false;
										bf.findField('Drug_id').DrugPackage = false;
										combo.getStore().loadData(result);
										combo.expand();
									}
								}
							});
						}
						else 
						{
							this.collapse();
							this.el.focus();
						}
						return false;
					}
				},
				{ // второй комбобокс (упаковка)
					hiddenName: 'Drug_id',
					DrugPackage: false,
					anchor: '98%',
					allowBlank: false,
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var bf = this.findById('EvnDrugEditForm').getForm();
							var record = combo.getStore().getById(newValue);
							bf.findField('Drug_Fas').setRawValue('');
							bf.findField('DrugForm_Name').setRawValue('');
							bf.findField('DrugUnit_Name').setRawValue('');

							if ( !record ) {
								bf.findField('EvnDrug_Kolvo').fireEvent('change', bf.findField('EvnDrug_Kolvo'), bf.findField('EvnDrug_Kolvo').getValue());
								return false;
							}

							bf.findField('Drug_Fas').setRawValue(record.get('Drug_Fas') ? record.get('Drug_Fas') : 1);
							bf.findField('DrugForm_Name').setRawValue(record.get('DrugForm_Name'));
							bf.findField('DrugUnit_Name').setRawValue(record.get('DrugUnit_Name'));

							var LpuSection_id = bf.findField('LpuSection_id').getValue();
							var DocumentUcStr_oid = bf.findField('DocumentUcStr_oid').getValue();

							if ( this.documentUcStrMode != 'expenditure' && !wnd.DrugPackage ) {
								bf.findField('DocumentUcStr_oid').getStore().load({
                                    params: {
                                        LpuSection_id: LpuSection_id,
                                        mode: 'default',
                                        Drug_id: newValue,
                                        is_personal: 1,
                                        Ost_DocumentUcStr_id: !wnd.FormLoadState.isLoaded('DocumentUcStr_oid') ? wnd.FormLoadState.get('DocumentUcStr_oid') : null
                                    },
                                    callback: function() {
                                        wnd.FormLoadState.markAsLoaded('DocumentUcStr_oid');
                                    }
                                });
							}
							if ( this.documentUcStrMode == 'expenditure' && !wnd.DrugPackage ) {
								var document_uc_str_combo = bf.findField('DocumentUcStr_oid');
								var value = document_uc_str_combo.getValue();

								document_uc_str_combo.clearValue();
								document_uc_str_combo.getStore().removeAll();
								document_uc_str_combo.lastQuery = '';

								if ( newValue > 0 ) {
									document_uc_str_combo.getStore().load( {
										params: {
											//LpuSection_id: bf.findField('LpuSection_id').getValue(),
											Drug_id: newValue,
											date: Ext.util.Format.date(bf.findField('EvnDrug_setDate').getValue(),'d.m.Y'),
											LpuSection_id: LpuSection_id,
											//DocumentUc_id: base_form.findField('DocumentUc_id').getValue(),
											DocumentUcStr_id: bf.findField('DocumentUcStr_id').getValue(),
											//DrugMnn_id: record.get('DrugMnn_id'),
											mode: 'default', 
											is_personal: 1,
                                            Ost_DocumentUcStr_id: !wnd.FormLoadState.isLoaded('DocumentUcStr_oid') ? wnd.FormLoadState.get('DocumentUcStr_oid') : null
										},
										callback: function() {
											if (value>0) {
												document_uc_str_combo.setValue(value);
												bf.findField('DocumentUcStr_oid').fireEvent('change', bf.findField('DocumentUcStr_oid'), bf.findField('DocumentUcStr_oid').getValue());
											}
                                            wnd.FormLoadState.markAsLoaded('DocumentUcStr_oid');
										}
									});
								} else {
									document_uc_str_combo.fireEvent('change', document_uc_str_combo, null, 1);
								}
							}
							if ( newValue > 0 ) {
								Ext.Ajax.request({
	                				params:{
	                					Drug_id: newValue
	                				},
									url: '/?c=Farmacy&m=checkGoodsPackCount',
									callback: function(options, success, response) {
										if ( success ) {
											var result = Ext.util.JSON.decode(response.responseText);
											if(result){
												if(result[0] > 0){
													result = result.join();
													bf.findField('GoodsUnit_sid').getStore().load({params:{where:" and gu.GoodsUnit_id in ("+result+")"}});
												} else {
													bf.findField('GoodsUnit_sid').getStore().load();
												}
											} else {
												bf.findField('GoodsUnit_sid').getStore().load();
											}
										}
										if(!bf.findField('EvnPrescrTreatDrug_id').getValue()){
											if(record.get('GoodsUnit_id')){
												bf.findField('GoodsUnit_sid').setValue(record.get('GoodsUnit_id'));
												bf.findField('GoodsUnit_sid').fireEvent('change',bf.findField('GoodsUnit_sid'),record.get('GoodsUnit_id'));
											} else {
												sw.swMsg.alert(lang['vnimanie'],lang['ne_zadany_edinicy_spisania_medikamenta_obratites_k_administratoru_sistemyi']);
											}
										}
									}
								});
							} else {
								bf.findField('GoodsUnit_sid').getStore().load();
								bf.findField('GoodsUnit_sid').setValue('');
							}
							return true;
						}.createDelegate(this)
					},
					tabIndex: TABINDEX_EDEW + 5,
					xtype: 'swdrugpackcombo',
                    onLoadStore: function(store) {
                        //удаляем после загрузки конкретного медикамента при редактировании формы
                        store.baseParams.Drug_id = null;
                    }
				},
                {
                    allowBlank: false,
                    anchor: '98%',
                    displayField: 'DocumentUcStr_Name',
                    enableKeyEvents: true,
                    fieldLabel: lang['partiya'],
                    forceSelection: true,
                    hiddenName: 'DocumentUcStr_oid',
                    tabIndex: TABINDEX_EDEW + 9,
                    triggerAction: 'all',
                    loadingText: lang['idet_poisk'],
                    minChars: 1,
                    minLength: 1,
                    minLengthText: lang['pole_doljno_byit_zapolneno'],
                    mode: 'local',
                    resizable: true,
                    selectOnFocus: true,
                    valueField: 'DocumentUcStr_id',
                    width: 500,
                    xtype: 'combo',
                    listeners: {
                        'beforeselect': function() {
                            return true;
                        }.createDelegate(this),
                        'change': function(combo, newValue, oldValue) {
                            var bf = this.findById('EvnDrugEditForm').getForm();
                            //bf.findField('EvnDrug_KolvoEd').setValue('');
                            bf.findField('EvnDrug_Price').setValue('');
                            bf.findField('DocumentUc_id').setValue(null);
                            //bf.findField('EvnDrug_Kolvo').fireEvent('change', bf.findField('EvnDrug_Kolvo'), '', 1);
                            var record = combo.getStore().getById(newValue);
                            if ( record ) {
                                bf.findField('DocumentUcStr_Count').setValue(record.get('DocumentUcStr_Count'));
                                bf.findField('DocumentUcStr_EdCount').setValue(record.get('DocumentUcStr_EdCount'));
                                bf.findField('EvnDrug_Price').setValue(record.get('EvnDrug_Price'));
                                bf.findField('DocumentUc_id').setValue(record.get('DocumentUc_id'));
                                //bf.findField('EvnDrug_Kolvo_Show').fireEvent('change', bf.findField('EvnDrug_Kolvo'), bf.findField('EvnDrug_Kolvo').getValue());
                                // Расчет ед.
                                //bf.findField('DocumentUcStr_EdCount').setValue((bf.findField('Drug_Fas').getValue() * record.get('DocumentUcStr_Count')).toFixed(2));
                                bf.findField('DocumentUcStr_EdCount').setValue((bf.findField('GoodsPackCount_sCount').getValue() * record.get('DocumentUcStr_Count')).toFixed(4));
                                if (this.openMode == 'prescription' && this.action == 'add' && bf.findField('EvnPrescrTreat_Fact').getValue()) {
                                    bf.findField('EvnPrescrTreat_Fact').fireEvent('change', bf.findField('EvnPrescrTreat_Fact'), bf.findField('EvnPrescrTreat_Fact').getValue(),null);
                                }
                            } else {
                                combo.setValue(null);
                                bf.findField('EvnDrug_Kolvo').setValue('');
                                bf.findField('DocumentUcStr_Count').setValue(null);
                                bf.findField('DocumentUc_id').setValue(null);
                                bf.findField('DocumentUcStr_EdCount').setValue(null);
                                //bf.findField('EvnDrug_Kolvo_Show').fireEvent('change', bf.findField('EvnDrug_Kolvo'), '', 1);
                                //bf.findField('EvnDrug_Kolvo_Show').setValue(null);
                            }
                            return true;
                        }.createDelegate(this)
                    },
                    store: new Ext.data.Store({
                        autoLoad: false,
                        reader: new Ext.data.JsonReader(
                            {
                                id: 'DocumentUcStr_id'
                            },
                            [
                                { name: 'DocumentUcStr_id', mapping: 'DocumentUcStr_id' },
                                { name: 'DocumentUc_id', mapping: 'DocumentUc_id' },
                                { name: 'DocumentUcStr_Name', mapping: 'DocumentUcStr_Name' },
                                { name: 'DrugFinance_id', mapping: 'DrugFinance_id' },
                                { name: 'DocumentUcStr_Count', mapping: 'DocumentUcStr_Count' },
                                { name: 'DocumentUcStr_EdCount', mapping: 'DocumentUcStr_EdCount' },
                                { name: 'DocumentUcStr_Ser', mapping: 'DocumentUcStr_Ser' },
                                { name: 'DrugFinance_Name', mapping: 'DrugFinance_Name' },
                                { name: 'DocumentUcStr_godnDate', mapping: 'DocumentUcStr_godnDate' },
                                { name: 'EvnDrug_Price', mapping: 'EvnDrug_Price' }
                            ]),
                        url: '/?c=Farmacy&m=loadDocumentUcStrList'
                    }),
                    tpl: new Ext.XTemplate(
                        '<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 8pt; font-weight: bold;">',
                        '<td style="padding: 2px; width: 20%;">Срок годности</td>',
                        '<td style="padding: 2px; width: 15%;">Цена</td>',
                        '<td style="padding: 2px; width: 15%;">Остаток</td>',
                        '<td style="padding: 2px; width: 35%;">Источник финансирования</td>',
                        '<td style="padding: 2px; width: 15%;">Серия</td></tr>',
                        '<tpl for="."><tr class="x-combo-list-item" style="font-family: tahoma; font-size: 8pt;">',
                        '<td style="padding: 2px;">{DocumentUcStr_godnDate}&nbsp;</td>',
                        '<td style="padding: 2px;">{EvnDrug_Price}&nbsp;</td>',
                        '<td style="padding: 2px;">{DocumentUcStr_Count}&nbsp;</td>',
                        '<td style="padding: 2px;">{DrugFinance_Name}&nbsp;</td>',
                        '<td style="padding: 2px;">{DocumentUcStr_Ser}&nbsp;</td>',
                        '</tr></tpl>',
                        '</table>'
                    )
                }, {
					border: false,
					layout: 'column',
                    hidden: true,
					items: [{
						border: false,
						labelWidth: 100,
						layout: 'form',
						items: [{
							disabled: true,
							fieldLabel: lang['lek_forma'],
							name: 'DrugForm_Name',
							tabIndex: TABINDEX_EDEW + 7,
							width: 70,
							xtype: 'textfield'
						}]
					}, {
						border: false,
						labelWidth: 100,
						layout: 'form',
						items: [{
							disabled: true,
							fieldLabel: lang['kol-vo_v_upak'],
							name: 'Drug_Fas',
							tabIndex: TABINDEX_EDEW + 8,
							width: 70,
							xtype: 'numberfield'
						}]
					}]
				}, {
                    border: false,
                    layout: 'column',
                    items:
                        [{
                            border: false,
                            layout: 'form',
                            items: [{
                                disabled: true,
                                fieldLabel: lang['ed_ucheta'],
                                name: 'DrugUnit_Name',
                                tabIndex: TABINDEX_EDEW + 6,
                                width: 100,
                                xtype: 'textfield'
                            }]
                        }, {
                            border: false,
                            layout: 'form',
                            labelWidth: 130,
                            items: [{
                                disabled: false,
                                fieldLabel: lang['ed_spisaniia'],
                                name: 'GoodsUnit_sid',
                                tabIndex: TABINDEX_EDEW + 10,
                                width: 100,
                                xtype: 'swgoodsunitcombo',
                                listeners: {
                                	'change':function(combo,newVal,oldVal) {
                                		var win = this;
                                		var bf = this.findById('EvnDrugEditForm').getForm();
                                		var drug = bf.findField('Drug_id').getValue();
                                		if(win.firstLoad == 1) {
                                			var newVal = this.FormLoadState.data.GoodsUnit_id;
                                			combo.setValue(newVal);
                                		}
                                		if(drug && newVal){
                                			Ext.Ajax.request({
                                				params:{
                                					Drug_id: drug,
                                					GoodsUnit_id: newVal
                                				},
												url: '/?c=Farmacy&m=getGoodsPackCount',
												callback: function(options, success, response) {
													if ( success ) {
														var result = Ext.util.JSON.decode(response.responseText);
														if(result && !Ext.isEmpty(result[0]) && !Ext.isEmpty(result[0].GoodsPackCount_Count)){
															bf.findField('GoodsPackCount_sCount').setValue(result[0].GoodsPackCount_Count);
														} else {
															var GoodsUnit_Name = '';
															if(combo.getStore().getById(newVal)) {
																GoodsUnit_Name = combo.getStore().getById(newVal).get('GoodsUnit_Name');
															}
															sw.swMsg.alert(lang['vnimanie'],'Чтобы указать количество '+GoodsUnit_Name+' в потребительской упаковке выбранного медикамента обратитесь к Старшей медсестре или Администратору системы для заполнения справочника «Количество товара в потребительской упаковке»');
															if(newVal == 57) {
																bf.findField('GoodsPackCount_sCount').setValue(1);
															} else {
																bf.findField('GoodsPackCount_sCount').setValue('');
															}
														}
														if(win.firstLoad != 1) {
															bf.findField('GoodsPackCount_sCount').fireEvent('change',bf.findField('GoodsPackCount_sCount'),bf.findField('GoodsPackCount_sCount').getValue());
														}
														var val = bf.findField('DocumentUcStr_Count').getValue() * bf.findField('GoodsPackCount_sCount').getValue();
														val = val.toString();
														var comma = val.indexOf('.');
														comma += 5;
														val = val.substr(0,comma);
														bf.findField('DocumentUcStr_EdCount').setValue(val);
													}
													if(win.firstLoad == 1) {
														win.firstLoad = null;
													}
												}
											});
                                		}
                                	}.createDelegate(this)
                                }
                            }]
                        }, {
                            border: false,
                            layout: 'form',
                            labelWidth: 130,
                            items: [{
                                disabled: true,
                                allowBlank: false,
                                fieldLabel: lang['kol-vo_v_upak'],
                                name: 'GoodsPackCount_sCount',
                                tabIndex: TABINDEX_EDEW + 8,
                                width: 100,
                                xtype: 'numberfield',
                                listeners: {
                                	'change':function(field,newVal,oldVal){
                                		/*var bf = this.findById('EvnDrugEditForm').getForm();
                                		var kol = bf.findField('EvnDrug_KolvoEd').getValue();
                                		if(newVal && kol){
                                			bf.findField('EvnDrug_KolvoEd').setValue(kol*newVal);
                                		} else {
                                			bf.findField('EvnDrug_KolvoEd').setValue('');
                                		}
                                		bf.findField('EvnDrug_KolvoEd').fireEvent('change',bf.findField('EvnDrug_KolvoEd'),bf.findField('EvnDrug_KolvoEd').getValue());*/
                                	}.createDelegate(this)
                                }
                            }]
                        }]
                }, {
					border: false,
					layout: 'column',
					items:
					[{
						border: false,
						layout: 'form',
						items:
						[{
							disabled: true,
							fieldLabel: lang['ostatok_ed_uch'],
							name: 'DocumentUcStr_Count',
							tabIndex: TABINDEX_EDEW + 12,
							width: 100,
							allowDecimals: true,
							allowNegative: false,
							decimalPrecision: 4,
							xtype: 'numberfield'
						}]
					}, {
						border: false,
						labelWidth: 130,
						layout: 'form',
						items:
						[{
							disabled: true,
							fieldLabel: lang['ostatok_ed_spis'],
							name: 'DocumentUcStr_EdCount',
							tabIndex: TABINDEX_EDEW + 11,
							allowDecimals: true,
							allowNegative: false,
							decimalPrecision: 4,
							width: 100,
							xtype: 'numberfield'
						}]
					}]
				},
				{
					border: false,
					layout: 'column',
					items:
					[{
						border: false,
						layout: 'form',
						items:
						[{
							allowBlank: false,
							allowDecimals: true,
							allowNegative: false,
							decimalPrecision: 4,
							fieldLabel: lang['kolichestvo_ed_uch'],
							listeners:
							{
								'change': function(field, newValue, oldValue) {
									var bf = this.findById('EvnDrugEditForm').getForm();
									var price = bf.findField('EvnDrug_Price').getValue();
									if (newValue.toString().length != 0 && newValue.toFixed(4)>bf.findField('DocumentUcStr_Count').getValue()) {
										bf.findField('EvnDrug_Kolvo_Show').setValue(bf.findField('DocumentUcStr_Count').getValue());
										newValue = bf.findField('DocumentUcStr_Count').getValue();
									}
									// Расчет суммы - цена берется из медикамента
									if (newValue.toString().length == 0) {
										bf.findField('EvnDrug_Sum').setValue('');
										bf.findField('EvnDrug_KolvoEd').setValue('');
									} else {
										//var fas = bf.findField('Drug_Fas').getValue() > 0 ? bf.findField('Drug_Fas').getValue() : 1;
                                        var fas = bf.findField('GoodsPackCount_sCount').getValue() > 0 ? bf.findField('GoodsPackCount_sCount').getValue() : 1;
										var kolvo_ed = (fas * newValue).toFixed(4);
										
										bf.findField('EvnDrug_Sum').setValue((price * newValue).toFixed(2));
										bf.findField('EvnDrug_KolvoEd').setValue(kolvo_ed);
										bf.findField('EvnDrug_Kolvo').setValue((kolvo_ed/fas).toFixed(4));
										bf.findField('EvnDrug_Kolvo_Show').setValue(newValue.toFixed(4));
									}
									if(this.firstLoad == 1){
										bf.findField('EvnDrug_Kolvo').setValue(this.FormLoadState.data.EvnDrug_Kolvo);
										bf.findField('EvnDrug_Sum').setValue(this.FormLoadState.data.EvnDrug_Sum);
										bf.findField('EvnDrug_KolvoEd').setValue(this.FormLoadState.data.EvnDrug_KolvoEd);
										bf.findField('EvnDrug_Kolvo_Show').setValue(this.FormLoadState.data.EvnDrug_Kolvo);
									}
								}.createDelegate(this)
							},
							minValue: 0.0001,
							name: 'EvnDrug_Kolvo_Show',
							tabIndex: TABINDEX_EDEW + 13,
							width: 100,
							xtype: 'numberfield'
						}]
					}, {
						border: false,
						labelWidth: 50,
						layout: 'form',
						hidden: true,
						items:
						[{
							allowBlank: false,
							allowDecimals: true,
							allowNegative: false,
							decimalPrecision: 4,
							fieldLabel: 'Real',
							listeners:
							{
								'change': function(field, newValue, oldValue){
									//
								}.createDelegate(this)
							},
							minValue: 0.000001,
							name: 'EvnDrug_Kolvo',
							tabIndex: TABINDEX_EDEW + 12,
							width: 50,							
							xtype: 'numberfield'
						}]
					}, {
						border: false,
						labelWidth: 130,
						layout: 'form',
						items:
						[{
							allowBlank: false,
							allowDecimals: true,
							allowNegative: false,
							disabled: true,
							decimalPrecision: 4,
							fieldLabel: lang['kol-vo_ed_spis'],
							name: 'EvnDrug_KolvoEd',
							tabIndex: TABINDEX_EDEW + 11,
							width: 100,
							xtype: 'numberfield',
							listeners:
							{
								'change': function(field, newValue, oldValue)
								{
									var bf = this.findById('EvnDrugEditForm').getForm();
									var price = bf.findField('EvnDrug_Price').getValue();

									if (newValue>bf.findField('DocumentUcStr_EdCount').getValue()) {
										bf.findField('EvnDrug_KolvoEd').setValue(bf.findField('DocumentUcStr_EdCount').getValue());
										newValue = bf.findField('DocumentUcStr_EdCount').getValue();
									}
									// Расчет суммы - цена берется из медикамента
									if ( newValue.toString().length == 0 ) {
										bf.findField('EvnDrug_Sum').setValue('');
										bf.findField('EvnDrug_Kolvo').setValue('');
										bf.findField('EvnDrug_Kolvo_Show').setValue('');
									} else {
										//var kolvo = newValue/bf.findField('Drug_Fas').getValue();
                                        var kolvo = newValue/bf.findField('GoodsPackCount_sCount').getValue();
										var kolvo_show = kolvo < bf.findField('DocumentUcStr_Count').getValue() ? kolvo : bf.findField('DocumentUcStr_Count').getValue();
                                        /*log({
                                            action: 'Кол-во (ед. доз.) EvnDrug_KolvoEd change',
                                            DocumentUcStr_EdCount: bf.findField('DocumentUcStr_EdCount').getValue(),
                                            Drug_Fas: bf.findField('Drug_Fas').getValue(),
                                            DocumentUcStr_Count: bf.findField('DocumentUcStr_Count').getValue(),
                                            newValue: newValue,
                                            kolvo: kolvo,
                                            kolvoShow: kolvo_show
                                        });*/
										kolvo_show = kolvo_show.toFixed(4);									
										bf.findField('EvnDrug_Kolvo').setValue(kolvo.toFixed(4));
										bf.findField('EvnDrug_Kolvo_Show').setValue(kolvo_show);
										bf.findField('EvnDrug_Sum').setValue((price * kolvo_show).toFixed(2));
									}

								}.createDelegate(this)
							}
						}]
					}]
				}, {
					border: false,
					layout: 'column',
					items:
					[{
						border: false,
						layout: 'form',
						items:
						[{
							allowBlank: false,
							allowDecimals: true,
							allowNegative: false,
							decimalPrecision: 2,
							disabled: true,
							fieldLabel: lang['tsena_ed_uch'],
							listeners:
							{
								'change': function(field, newValue, oldValue)
								{

									var bf = this.findById('EvnDrugEditForm').getForm();
									bf.findField('EvnDrug_Kolvo').fireEvent('change', bf.findField('EvnDrug_Kolvo'), bf.findField('EvnDrug_Kolvo').getValue());
								}.createDelegate(this)
							},
							name: 'EvnDrug_Price',
							tabIndex: TABINDEX_EDEW + 14,
							width: 100,
							xtype: 'numberfield'
						}]
					},
					{
						border: false,
						labelWidth: 130,
						layout: 'form',
						items:
						[{
							allowBlank: false,
							disabled: true,
							fieldLabel: lang['summa'],
							name: 'EvnDrug_Sum',
							tabIndex: TABINDEX_EDEW + 15,
							width: 100,
							xtype: 'numberfield'
						}]
					}]
				}],
				labelAlign: 'right',
				labelWidth: 130,
				reader: new Ext.data.JsonReader(
				{
					success: Ext.emptyFn
				},
				[
					{ name: 'EvnDrug_id' },
					{ name: 'EvnDrug_setDate' },
					{ name: 'EvnDrug_setTime' },
					{ name: 'Person_id' },
                    { name: 'EvnCourse_id' },
                    { name: 'EvnPrescr_id' },
                    { name: 'EvnCourseTreatDrug_id' },
                    { name: 'EvnPrescrTreatDrug_id' },
                    { name: 'EvnPrescrTreat_Fact' },
                    { name: 'PrescrFactCountDiff' },
                    { name: 'EvnDrug_pid' },
					{ name: 'EvnDrug_rid'},
					{ name: 'Server_id' },
					{ name: 'PersonEvn_id' },
					{ name: 'Drug_id' },
					{ name: 'DrugPrepFas_id' },
					{ name: 'LpuSection_id' },
					{ name: 'EvnDrug_Price' },
					{ name: 'EvnDrug_Sum' },
					{ name: 'DocumentUc_id' },
					{ name: 'DocumentUcStr_id' },
					{ name: 'DocumentUcStr_oid' },
					{ name: 'Mol_id' },
					{ name: 'EvnDrug_Kolvo' },
					{ name: 'EvnDrug_Kolvo_Show' , mapping: 'EvnDrug_Kolvo' },
					{ name: 'EvnDrug_KolvoEd' },
					{ name: 'EvnDrug_RealKolvo' }
				]),
				region: 'center',
				trackResetOnLoad: true,
				url: '/?c=EvnDrug&m=saveEvnDrug'
			});
	
	
		Ext.apply(this, 
		{
			buttons: 
			[{
				handler: function() {
					if ( this.action != 'view' ) {
						this.doSave();
					}
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_EDEW + 21,
				text: BTN_FRMSAVE,
				tooltip: lang['sohranit_vvedennyie_dannyie']
			}, 
			{
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() 
				{
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					if ( this.action != 'view' ) {
						this.findById('EvnDrugEditForm').getForm().findField('Drug_id').focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EDEW + 22,
				text: BTN_FRMCANCEL,
				tooltip: lang['zakryit_okno']
			}],
			items: [
				new sw.Promed.PersonInformationPanelShort(
				{
					id: 'EDEW_PersonInformationFrame'
				}),
				this.EditForm]
		});
		sw.Promed.swEvnDrugEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
