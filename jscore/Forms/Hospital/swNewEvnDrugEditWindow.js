/**
 * swNewEvnDrugEditWindow - персонифицированный учет
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Stac
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Марков Андрей (использована форма swNewEvnDrugEditWindow by Stas Bykov aka Savage)
 * @version      апрель 2010
 * @comment      Префикс для id компонентов edew (EvnDrugEditWindow)
 *								TABINDEX_EDEW: 8600
 *
 * @input data: action - действие (add, edit, view)
 */
/*NO PARSE JSON*/
sw.Promed.swNewEvnDrugEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	id: 'swNewEvnDrugEditWindow',
	draggable: true,
	split: true,
	width: 800,
	codeRefresh: true,
	objectName: 'swNewEvnDrugEditWindow',
	objectSrc: '/jscore/Forms/Hospital/swNewEvnDrugEditWindow.js',
	layout: 'form',
	documentUcStrMode:'expenditure',
	openMode: '',
	listeners:
	{
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
			var current_window = Ext.getCmp('NEDEW_FormPanel');
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
	calculationCountFakt: function () {
		var kol = 0;
		var k = this.arr_time.length;
		for (i = 0; i < k; i++) {
			id = 'tew_Check' + (i);
			if (Ext.getCmp(id).checked)
				kol += 1;
			var bf = this.findById('NEDEW_FormPanel').getForm();
			bf.findField('EvnPrescrTreat_Fact').setValue(kol);
			bf.findField('EvnPrescrTreat_Fact').fireEvent('change', kol, kol);
		}

	},
	createCheckbox: function (panel, index, name) {
		var form = this;
		var id = index;
		var val = 0;

		var element = {
			border: false,
			layout: 'column',
			items: [{
					border: false,
					labelWidth: 5,
					layout: 'form',
					items: [{
							xtype: 'swcheckbox',
							height: 24,
							oId: id,
							border: false,
							tabIndex: TABINDEX_TEW + (2 * id + 1),
							id: 'tew_Check' + id,
							checked: false,
							labelSeparator: '',
							boxLabel: name,
							
							listeners: {
								'change': function (obj, newValue, oldValue) {
									if (newValue != oldValue) {
										form.calculationCountFakt();
									}
								}
							}
						}]
					/*
				},
				{
					border: false,
					labelWidth: 5,
					layout: 'form',
					hidden: true,
					items: [{
							allowBlank: false,
							disabled: true,
							id: 'tew_timefield' + id,
							tabIndex: TABINDEX_TEW + (2 * id + 2),
							plugins: [new Ext.ux.InputTextMask('99:99', true)],
							validateOnBlur: false,
							width: 60,
							xtype: 'swtimefield',
							labelSeparator: '',
							onTriggerClick: function () {
								if (!Ext.getCmp('tew_timefield' + id).disabled) {
									if (id != 0) {
										var idTmp = 'tew_timefield' + (id - 1);
										var val = Ext.getCmp(idTmp).getValue();
										v1 = val.substring(0, 2) * 1 + 1;
										v1 = v1 < 9 ? '0' + v1 : v1;
										val = v1 + val.substring(2, 5);
										this.setValue(val)
									}
								}
							}
						}]
					*/
				}]
		};

		var wrapper = {
			layout: 'form',
			labelWidth: 10,
			items: [element]
		};
		panel.add(wrapper);
		panel.add({height: 5, border: false});
		panel.doLayout();

		Ext.getCmp('tew_Check' + id).setValue(val);
	},
	loadQuestions: function () {
		var form = this;
		if (form.arr_time != null) {
			var k = form.arr_time.length;

			Ext.getCmp('fieldsetForm').removeAll();

			var element = {
									border: false,
									labelWidth: 100,
									layout: 'form',
									items: [{

										disabled: true,
										fieldLabel: langs('Время приемов'),
										id: 'tew_timelbl',
										//name: 'DrugForm_Name',
										//tabIndex: TABINDEX_EDEW + 7,
										width: 70,
										xtype: 'textfield'
									}]
								}	

			var wrapper = {
				layout: 'form',
				labelWidth: 10,
				items: [element]
			};
			Ext.getCmp('fieldsetForm').add(wrapper);
			Ext.getCmp('tew_timelbl').setVisible(false);


			var i;
			for (i = 0; i < k; i++) {
				form.createCheckbox(Ext.getCmp('fieldsetForm'), i, form.arr_time[i].time);
			}
		}
	},
	getDefaultKolvoEd: function () { //функция для определения количество ед. списания по умолчанию (в т.ч. при редактировании и просмотре)
        var bf = this.findById('NEDEW_FormPanel').getForm();
        var ep_combo = bf.findField('EvnPrescrTreatDrug_id');
        var fact = bf.findField('EvnPrescrTreat_Fact').getValue()*1;
        var saved_value = !Ext.isEmpty(this.savedData.EvnDrug_KolvoEd) ? this.savedData.EvnDrug_KolvoEd : null;
        var default_value = 1;

        if (this.action != 'add' && !Ext.isEmpty(saved_value)) { //если режим редактирования/просмотра и есть сохраненное количество, то берем его
        	default_value = saved_value;
		} else if (ep_combo.getValue() > 0) { //иначе пытаемся расчитать по даным назначения и количеству приемов
            var ep_data = ep_combo.getStore().getById(ep_combo.getValue());
            var gu_id = bf.findField('GoodsUnit_id').getValue();
            var gpc_count = bf.findField('GoodsPackCount_Count').getValue()*1 > 0 ? bf.findField('GoodsPackCount_Count').getValue()*1 : 1;

            if (ep_data) {
            	if (gu_id == ep_data.get('GoodsUnit_id')) { //ед. учета соответствует ед. измерения разовой дозы
					default_value = ep_data.get('EvnPrescrTreatDrug_Kolvo')*1.0*fact;
					//если за единицу измерения разовой дозы принята упаковка, считаем какая часть упаковки будет
					//соответствовать количеству ед. списания
					if (ep_data.get('GoodsUnit_id') == '57'){
						Kolvo_Ed = ep_data.get('EvnPrescrTreatDrug_KolvoEd')*1.0;
						Drug_Fas = ep_data.get('Drug_Fas')*1.0;
						default_value = Kolvo_Ed/Drug_Fas*fact;
					}
                } else if (gu_id == ep_data.get('GoodsUnit_sid')) { //ед. учета соответствует ед. измерения разовой дозы
                    default_value = ep_data.get('EvnPrescrTreatDrug_KolvoEd')*1.0*fact;
                } else if (!Ext.isEmpty(gpc_count)) {
                	//делаем пересчет через упаковки
					var s_kolvo = ep_data.get('EvnPrescrTreatDrug_KolvoEd')*1.0;
                    var gpc_s_count = ep_data && ep_data.get('GoodsPackCount_sCount')*1 > 0 ? ep_data.get('GoodsPackCount_sCount')*1 : 1;

                    default_value = (s_kolvo/gpc_s_count)*gpc_count*fact;
                }
			}
		}

        return default_value;
	},
	calculateEdCount: function(GoodsPackCount_Count, DocumentUcStr_Count, GoodsPackCount_bCount) {
		if (Ext.isEmpty(GoodsPackCount_bCount)) {
            GoodsPackCount_bCount = 1;
		}
		return ((Number(GoodsPackCount_Count) * Number(DocumentUcStr_Count))/GoodsPackCount_bCount).toFixed(6);
	},
	calculateEvnDrug: function() {
		var bf = this.findById('NEDEW_FormPanel').getForm();
		var default_kolvo_ed = this.getDefaultKolvoEd();

		bf.findField('EvnDrug_KolvoEd').setValue(default_kolvo_ed);
		bf.findField('EvnDrug_Price').setValue('');
		bf.findField('DocumentUc_id').setValue(null);

		var record = bf.findField('DocumentUcStr_oid').getStore().getById(bf.findField('DocumentUcStr_oid').getValue());
		if (record) {
			bf.findField('DocumentUcStr_Count').setValue(record.get('DocumentUcStr_Count'));
			//bf.findField('DocumentUcStr_EdCount').setValue(record.get('DocumentUcStr_Count')*bf.findField('Drug_Fas').getValue());
			bf.findField('EvnDrug_Price').setValue(record.get('DocumentUcStr_Price'));
			bf.findField('DocumentUc_id').setValue(record.get('DocumentUc_id'));

			var callback = function() {
				// Расчет ед.
				bf.findField('DocumentUcStr_EdCount').setValue(this.calculateEdCount(
					bf.findField('GoodsPackCount_Count').getValue(), record.get('DocumentUcStr_Count'), bf.findField('GoodsPackCount_bCount').getValue()
				));
				if (this.openMode == 'prescription' && this.action == 'add' && !Ext.isEmpty(bf.findField('EvnPrescrTreat_Fact').getValue())) {
					bf.findField('EvnPrescrTreat_Fact').fireEvent('change', bf.findField('EvnPrescrTreat_Fact'), bf.findField('EvnPrescrTreat_Fact').getValue(),null);
				}
                bf.findField('EvnDrug_KolvoEd').onChange(default_kolvo_ed);
			}.createDelegate(this);

			if (record.get('GoodsUnit_id') == bf.findField('GoodsUnit_id').getValue() || this.openMode == 'prescription' || this.init) {
				callback();
			} else {
				bf.findField('GoodsUnit_id').getStore().clearFilter();
				bf.findField('GoodsUnit_id').setValue(record.get('GoodsUnit_id'));
				this.getGoodsPackCount(callback);
			}
		} else {
			bf.findField('DocumentUcStr_oid').setValue(null);
			bf.findField('DocumentUcStr_Count').setValue(null);
			bf.findField('DocumentUc_id').setValue(null);
			bf.findField('DocumentUcStr_EdCount').setValue(null);
            bf.findField('EvnDrug_KolvoEd').onChange('');
		}
	},

	initFormData: function() {
		var form = this;
		var bf = form.findById('NEDEW_FormPanel').getForm();

		var date = bf.findField('EvnDrug_setDate').getValue();

		form.init = true;
		form.loadSpr();

		var evn_drug_id = bf.findField('EvnDrug_id').getValue();
		var lpu_id = bf.findField('EvnDrug_pid').getFieldValue('Lpu_id');
		var document_uc_str_oid = bf.findField('DocumentUcStr_oid').getValue();
		var drug_prep_fas_id = bf.findField('DrugPrepFas_id').getValue();
		var mol_id = bf.findField('Mol_id').getValue();
		var drug_id = bf.findField('Drug_id').getValue();

		if (this.action == 'edit' || this.action == 'view') {
			//сохраняем количество, чтобы можно было его установить после подгрузки комбобоксов
			this.savedData.EvnDrug_KolvoEd = bf.findField('EvnDrug_KolvoEd').getValue();
		}

		form.setFilterLpuSection(date);
		var lpu_section_id = bf.findField('LpuSection_id').getValue();
		bf.findField('LpuSection_id').clearValue();
		var section_filter_params = {};
		section_filter_params.onDate = Ext.util.Format.date(date, 'd.m.Y');
		setLpuSectionGlobalStoreFilter(section_filter_params);
		bf.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		if ( bf.findField('LpuSection_id').getStore().getById(lpu_section_id) ) {
			bf.findField('LpuSection_id').setValue(lpu_section_id);
		}

		var storage_id = bf.findField('Storage_id').getValue();
		bf.findField('Storage_id').getStore().baseParams = {
			date: Ext.util.Format.date(date,'d.m.Y'),
            StructFilterPreset: 'EvnDrug_DocumentUcStr_Storage_id'
		};
		if (!Ext.isEmpty(lpu_section_id)) {
			bf.findField('Storage_id').getStore().baseParams.LpuSection_id = lpu_section_id;
		} else {
			bf.findField('Storage_id').getStore().baseParams.Lpu_id = lpu_id;
		}

		var ep_combo = bf.findField('EvnPrescrTreatDrug_id');
		if (ep_combo.getValue()) {
			ep_combo.getStore().load({
				callback: function() {
					ep_combo.getStore().each(function(rec){
						if (rec.get('EvnPrescrTreatDrug_id')==ep_combo.getValue()) {
							ep_combo.setValue(ep_combo.getValue());
							if (Ext.isEmpty(rec.get('Drug_id')) || form.action != 'add') {
								bf.findField('EvnCourse_id').setValue(rec.get('EvnCourse_id'));
								bf.findField('EvnCourseTreatDrug_id').setValue(rec.get('EvnCourseTreatDrug_id'));
								bf.findField('EvnPrescr_id').setValue(rec.get('EvnPrescrTreat_id'));
								bf.findField('PrescrFactCountDiff').setValue(rec.get('PrescrFactCountDiff'));
								//bf.findField('EvnPrescrTreat_Fact').setValue((form.action == 'add')?1:null);
								bf.findField('EvnPrescrTreat_Fact').maxValue = bf.findField('PrescrFactCountDiff').getValue();
                                bf.findField('GoodsUnit_id').setValue(rec.get('GoodsUnit_id'));
                                bf.findField('GoodsPackCount_Count').setValue(rec.get('GoodsPackCount_Count'));
							}
							ep_combo.fireEvent('change', ep_combo, ep_combo.getValue());
							form.init = false;
							return false;
						}
						form.init = false;
						return true;
					});
				}
			});
		} else if (!Ext.isEmpty(storage_id)) {
			bf.findField('Storage_id').getStore().load({
				callback: function() {
					bf.findField('Storage_id').setValue(storage_id);

					bf.findField('Mol_id').getStore().baseParams = {
						Storage_id: storage_id
					};
					bf.findField('Mol_id').getStore().load({
						callback: function() {bf.findField('Mol_id').setValue(mol_id)}
					});

					bf.findField('DrugPrepFas_id').getStore().baseParams = {
						Storage_id: storage_id,
						date: Ext.util.Format.date(date, 'd.m.Y')
					};
					bf.findField('DrugPrepFas_id').getStore().load({
						callback: function() {bf.findField('DrugPrepFas_id').setValue(drug_prep_fas_id)}
					});

					bf.findField('Drug_id').getStore().baseParams = {
						Storage_id: storage_id,
						date: Ext.util.Format.date(date, 'd.m.Y'),
						DrugPrepFas_id: drug_prep_fas_id
					};
					bf.findField('Drug_id').getStore().load({
						callback: function() {
							var combo = bf.findField('Drug_id');
							combo.setValue(drug_id);
							bf.findField('Drug_Fas').setRawValue(combo.getFieldValue('Drug_Fas') ? combo.getFieldValue('Drug_Fas') : 1);
							bf.findField('DrugForm_Name').setRawValue(combo.getFieldValue('DrugForm_Name'));
							bf.findField('GoodsUnit_bName').setValue(combo.getFieldValue('GoodsUnit_bName'));
						}
					});

					bf.findField('DocumentUcStr_oid').getStore().load({
						params: {
							Drug_id: drug_id,
							Storage_id: storage_id,
							date: Ext.util.Format.date(date, 'd.m.Y'),
							EvnDrug_id: evn_drug_id
						},
						callback: function() {
							bf.findField('DocumentUcStr_oid').setValue(document_uc_str_oid);
                            bf.findField('DocumentUcStr_oid').setLinkedFieldValues();
							form.calculateEvnDrug();

							var ep_combo = bf.findField('EvnPrescrTreatDrug_id');
							if (!Ext.isEmpty(ep_combo.getValue())) {
								ep_combo.fireEvent('change', ep_combo, ep_combo.getValue());
							}
							form.init = false;
						}
					});
				}
			});
		} else {
			form.init = false;
		}

		bf.findField('LpuSection_id').focus(true, 500);
	},

    show: function() {
        var wnd = this;
        var show_arguments = arguments[0];
        var action = !Ext.isEmpty(arguments[0].action) ? arguments[0].action : null;
        var EvnDrug_id = arguments[0].formParams && arguments[0].formParams.EvnDrug_id > 0  ? arguments[0].formParams.EvnDrug_id : null;

		if (Ext.getCmp('fieldsetForm')) Ext.getCmp('fieldsetForm').removeAll();
        this.ARMType = null;
        this.userMedStaffFact = new Object();

        if (arguments[0]) {
            action = !Ext.isEmpty(arguments[0].action) ? arguments[0].action : null;
            EvnDrug_id = arguments[0].formParams && arguments[0].formParams.EvnDrug_id > 0  ? arguments[0].formParams.EvnDrug_id : null;

			if (arguments[0].userMedStaffFact) {
            	this.userMedStaffFact = arguments[0].userMedStaffFact;
                this.ARMType = arguments[0].userMedStaffFact.ARMType
			}
        }

        if (action == 'edit' && !Ext.isEmpty(EvnDrug_id) && getDrugControlOptions().drugcontrol_module == "2") { //2 - учет ведется в АРМ товароведа
            //перед открытием проверяем допустимо ли редактирование
            Ext.Ajax.request({
                params:{
                    EvnDrug_id: EvnDrug_id
                },
                url: '/?c=EvnDrug&m=getExecutedDocumentUcStrForEvnDrug',
                callback: function(options, success, response) {
                    if (success) {
                        var result = Ext.util.JSON.decode(response.responseText);
                        if (Ext.isEmpty(result.DocumentUcStr_id)) { //если для данного EvnDrug нет исполненой строки документа, можно открывать форму на редактирование
                            wnd.show_window(show_arguments);
                        } else {
                            sw.swMsg.alert(lang['oshibka'], "Редактирование использования медикамента невозможно, т.к. медикамент уже списан со склада");
                        }
                    }
                }
            });
        } else {
            this.show_window(show_arguments);
        }
    },

	show_window: function()
	{
		sw.Promed.swNewEvnDrugEditWindow.superclass.show.apply(this, arguments);
		var bf = this.EditForm.getForm();
		var form = this;
		this.EditForm.getForm().reset();
		this.type = null;
		this.action = null;
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
        this.show_diff_gu = (getDrugControlOptions().doc_uc_different_goods_unit_control && getGlobalOptions().orgtype == 'lpu'); //отображение поле списания в альтернативных ед. измерения
		this.savedData = new Object(); //для хранения некоторых данных, загруженных в режиме просмотра/редактирования

		bf.findField('DrugPrepFas_id').getStore().proxy.conn.url = '/?c=EvnDrug&m=loadDrugPrepList';

		this.center();

		if (!arguments[0])
		{
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi'], function() {this.hide();}.createDelegate(this));
			return false;
		}
		bf.findField('EvnDrug_rid').setValue(arguments[0].formParams.EvnDrug_rid);
		this.EvnDrug_rid = arguments[0].formParams.EvnDrug_rid;

		this.clearValues();
		bf.isFirst = 1;

		bf.findField('DocumentUcStr_oid').getStore().removeAll();
		bf.findField('Drug_id').getStore().removeAll();

		if ( arguments[0].action )
		{
			this.action = arguments[0].action;
		}

		if ( arguments[0].type )
		{
			this.type = arguments[0].type;
		}

		if ( arguments[0].callback )
		{
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].owner )
		{
			this.owner = arguments[0].owner;
		}

		if ( arguments[0].mode )
		{
			this.documentUcStrMode = arguments[0].mode;
		}

		this.parentEvnClass_SysNick = arguments[0].parentEvnClass_SysNick||'';
		this.openMode = arguments[0].openMode||'';

		this.findById('NEDEW_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = bf.findField('EvnDrug_begDate');
				//clearDateAfterPersonDeath('personpanelid', 'NEDEW_PersonInformationFrame', field); // взрывается
			}
		});


		if ( arguments[0].onHide )
		{
			this.onHide = arguments[0].onHide;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

        //настройка видимости некоторых элементов
        if (this.show_diff_gu) {
            bf.findField('EvnDrug_KolvoEd').allowBlank = false;
            bf.findField('GoodsUnit_id').ownerCt.show();
            bf.findField('DocumentUcStr_EdCount').ownerCt.show();
            bf.findField('EvnDrug_KolvoEd').ownerCt.show();
        } else {
            bf.findField('EvnDrug_KolvoEd').allowBlank = true;
            bf.findField('GoodsUnit_id').ownerCt.hide();
            bf.findField('DocumentUcStr_EdCount').ownerCt.hide();
            bf.findField('EvnDrug_KolvoEd').ownerCt.hide();
        }

		bf.setValues(arguments[0].formParams);

		bf.findField('EvnDrug_pid').getStore().removeAll();

		if ( arguments[0].parentEvnComboData ) {
			bf.findField('EvnDrug_pid').getStore().loadData(arguments[0].parentEvnComboData);
		}

		var evn_combo = bf.findField('EvnDrug_pid');
		var evn_drug_pid = null;
		var set_date = true;

		this.findById('nedewEvnPrescrPanel').setVisible(this.openMode == 'prescription');
		if (this.openMode == 'prescription') {
			bf.findField('EvnPrescrTreat_Fact').maxValue = bf.findField('PrescrFactCountDiff').getValue();
			bf.clearInvalid();
		}

		if (this.action!='add')
		{
			this.findById('NEDEW_FormPanel').getForm().load(
			{
				params:
				{
					EvnDrug_id: bf.findField('EvnDrug_id').getValue(),
					archiveRecord: form.archiveRecord
				},
				failure: function()
				{
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function()
						{
							form.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
						title: lang['oshibka']
					});
				},
				success: function()
				{
					// Что надо сделать при чтении
					/*bf.findField('DrugPrepFas_id').getStore().baseParams.DocumentUcStr_id = bf.findField('DocumentUcStr_id').getValue();
					bf.findField('DrugPrepFas_id').getStore().baseParams.date = bf.findField('EvnDrug_setDate').getValue();*/

					if (bf.findField('EvnPrescr_id').getValue()) {
						form.openMode = 'prescription';
					} else {
						form.openMode = '';
					}
					form.findById('nedewEvnPrescrPanel').setVisible(form.openMode == 'prescription');
					if (form.openMode == 'prescription') {
						bf.findField('EvnPrescrTreat_Fact').maxValue = bf.findField('PrescrFactCountDiff').getValue();
					}

					setCurrentDateTime({
						callback: function()
						{
							form.initFormData();
						},
						dateField: bf.findField('EvnDrug_setDate'),
						loadMask: false,
						setDate: set_date,
						setDateMaxValue: true,
						setDateMinValue: false,
						setTime: false,
						timeField: bf.findField('EvnDrug_setTime'),
						windowId: form.id
					});
				}.createDelegate(this),
				url: '/?c=EvnDrug&m=loadEvnDrugEditForm'
			});
		}
		else
		{
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
				//evn_combo.fireEvent('change', evn_combo, evn_combo_rec.get('Evn_id'), 0);
				if (evn_combo_rec.get('Evn_disDate')) {
					bf.findField('EvnDrug_setDate').setValue(evn_combo_rec.get('Evn_disDate'));
					set_date = false;
				}
				if (evn_combo_rec.get('LpuSection_id')) {
					bf.findField('LpuSection_id').setValue(evn_combo_rec.get('LpuSection_id'));
					bf.findField('LpuSection_id').fireEvent('change', bf.findField('LpuSection_id'), bf.findField('LpuSection_id').getValue());
				}
			}
			if (!bf.findField('EvnDrug_setDate').getValue()) {
				setCurrentDateTime({
					callback: function()
					{
						form.initFormData();
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
				form.initFormData();
			}
		}

		switch ( this.action )
		{
			case 'add':
				this.enableEdit(true);
				this.setTitle(lang['stroka_dokumenta_dobavlenie']);
				loadMask.hide();
				//bf.clearInvalid();
				break;

			case 'edit':
				this.enableEdit(true);
				this.setTitle(lang['stroka_dokumenta_redaktirovanie']);
				loadMask.hide();
				//bf.clearInvalid();

				break;

			case 'view':
				this.enableEdit(false);
				this.setTitle(lang['stroka_dokumenta_prosmotr']);
				loadMask.hide();
				//bf.clearInvalid();
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
		var bf = this.findById('NEDEW_FormPanel').getForm();
		var form = this;
		
		if ( getGlobalOptions().region.nick == 'ufa' && form.arr_time != null && form.arr_time.length > 0) {
			var fact = bf.findField('EvnPrescrTreat_Fact').getValue();
			var k = this.arr_time.length;
			var kol = 0;
			for (i = 0; i < k; i++) {
				id = 'tew_Check' + (i);
				if (Ext.getCmp(id).checked)
					kol += 1;
			}
		
			if (fact != kol) {
				sw.swMsg.alert('Внимание', 'Значение поля "Списать приемов" не совпадает с количеством времени приемов');
				return false;
			}
		}
		
		
		if (!bf.isValid())
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function()
				{
					form.findById('NEDEW_FormPanel').getFirstInvalidEl().focus(true);
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

		var arr_time = [];
		if ( getGlobalOptions().region.nick == 'ufa' && form.arr_time != null && form.arr_time.length > 0) {
			var val = [];
			for (i = 0; i < form.arr_time.length; i++) {
				id = 'tew_Check' + (i);
				if (Ext.getCmp(id).checked) {
					val.push(form.arr_time[i])
				}
			}
			arr_time = Ext.util.JSON.encode(val);
			//form.createCheckbox(Ext.getCmp('fieldsetForm'), i, form.arr_time[i].time);
		}

        if (!this.show_diff_gu) { //перед сохранением, если ед. списания скрыты, приравниваем их к ед. учета
            bf.findField('GoodsUnit_id').setValue(bf.findField('GoodsUnit_bid').getValue());
            bf.findField('EvnDrug_KolvoEd').setValue(bf.findField('EvnDrug_Kolvo').getValue());
        }

		bf.submit({
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
				'arr_time': arr_time
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
					'Storage_id': bf.findField('Storage_id').getValue(),
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

				this.callback(data);
				this.hide();
			}.createDelegate(this)
		});
		return true;
	},
	enableEdit: function(enable)
	{
		var bf = this.findById('NEDEW_FormPanel').getForm();

        bf.findField('EvnDrug_pid').setDisabled(!enable);
		bf.findField('EvnDrug_setDate').setDisabled(!enable);
		bf.findField('EvnDrug_setTime').setDisabled(!enable);
		bf.findField('LpuSection_id').setDisabled(!enable);
		bf.findField('Storage_id').setDisabled(!enable);
		bf.findField('Mol_id').setDisabled(!enable);
        bf.findField('EvnPrescrTreatDrug_id').setDisabled(!enable);
        bf.findField('EvnPrescrTreat_Fact').setDisabled(!enable);
        bf.findField('DrugPrepFas_id').setDisabled(!enable);
        bf.findField('Drug_id').setDisabled(!enable);
		bf.findField('DocumentUcStr_oid').setDisabled(!enable);
        bf.findField('GoodsUnit_id').setDisabled(!enable/* || this.openMode == 'prescription'*/);
        bf.findField('EvnDrug_Kolvo_Show').setDisabled(getGlobalOptions().region.nick != 'ufa' || !this.show_diff_gu ? !enable : true); //Для Уфы закрываем поле для редактирования
        bf.findField('EvnDrug_Kolvo').setDisabled(!enable);
        bf.findField('EvnDrug_KolvoEd').setDisabled(!enable);

        if (enable) {
            this.buttons[0].enable();
        } else {
            this.buttons[0].disable();
        }
	},
	loadSpr: function()
	{
		var form = this;
		var bf = this.findById('NEDEW_FormPanel').getForm();

		var ep_combo = bf.findField('EvnPrescrTreatDrug_id');
		ep_combo.getStore().removeAll();
		ep_combo.getStore().baseParams = {};
		if (bf.findField('EvnPrescr_id').getValue()) {
			ep_combo.getStore().baseParams.EvnPrescrTreat_id = bf.findField('EvnPrescr_id').getValue();
		} else if (bf.findField('EvnDrug_pid').getValue()) {
			ep_combo.getStore().baseParams.EvnPrescrTreat_pid = bf.findField('EvnDrug_pid').getValue();
		}
	},
	refreshStorageParams: function() {
		//callback = callback || Ext.emptyFn;
		var bf = this.findById('NEDEW_FormPanel').getForm();
		var baseParams = {
            StructFilterPreset: 'EvnDrug_DocumentUcStr_Storage_id'
        };

		storage_combo = bf.findField('Storage_id');
		date_field = bf.findField('EvnDrug_setDate');
		pid_combo = bf.findField('EvnDrug_pid');
		lpu_section_combo = bf.findField('LpuSection_id');
		ep_combo = bf.findField('EvnPrescrTreatDrug_id');

		if (!Ext.isEmpty(date_field.getValue())) {
			baseParams.date = Ext.util.Format.date(date_field.getValue(), 'd.m.Y');
		}

		var lpu_section_id = null;
		if (this.ARMType == 'stacnurse' && !Ext.isEmpty(this.userMedStaffFact.LpuSection_id)) { // stacnurse - АРМ постовой медсестры
            lpu_section_id = this.userMedStaffFact.LpuSection_id;
		} else if (!Ext.isEmpty(lpu_section_combo.getValue())) {
            lpu_section_id = lpu_section_combo.getValue();
		}

		if (!Ext.isEmpty(lpu_section_id)) {
			baseParams.LpuSection_id = lpu_section_id;
		} else if (!Ext.isEmpty(pid_combo.getValue()) && !Ext.isEmpty(pid_combo.getFieldValue('Lpu_id'))) {
			baseParams.Lpu_id = pid_combo.getFieldValue('Lpu_id');
		}

		/*if (!Ext.isEmpty(ep_combo.getValue()) && !Ext.isEmpty(ep_combo.getFieldValue('Storage_id'))) {
			baseParams.Storage_id = ep_combo.getFieldValue('Storage_id');
		}*/
		if (!Ext.isEmpty(ep_combo.getValue())) {
			baseParams.EvnPrescrTreatDrug_id = ep_combo.getValue();
		}

		storage_combo.getStore().baseParams = baseParams;
		//storage_combo.getStore().load({callback: callback});
	},
	refreshDrugPrepFasParams: function() {
		//callback = callback || Ext.emptyFn;
		var bf = this.findById('NEDEW_FormPanel').getForm();
		var baseParams = {};

		dpf_combo = bf.findField('DrugPrepFas_id');
		date_field = bf.findField('EvnDrug_setDate');
		lpu_section_combo = bf.findField('LpuSection_id');
		storage_combo = bf.findField('Storage_id');
		ep_combo = bf.findField('EvnPrescrTreatDrug_id');
		drug_combo = bf.findField('Drug_id');

		if (!Ext.isEmpty(date_field.getValue())) {
			baseParams.date = Ext.util.Format.date(date_field.getValue(), 'd.m.Y');
		}
		if (!Ext.isEmpty(lpu_section_combo.getValue())) {
			//baseParams.LpuSection_id = lpu_section_combo.getValue();
		}
		if (!Ext.isEmpty(storage_combo.getValue())) {
			baseParams.Storage_id = storage_combo.getValue();
		}

		if (!Ext.isEmpty(ep_combo.getValue()) && !Ext.isEmpty(ep_combo.getFieldValue('DrugPrepFas_id'))) {
			baseParams.DrugPrepFas_id = ep_combo.getFieldValue('DrugPrepFas_id');
		} /*else if (!Ext.isEmpty(drug_combo.getValue()) && !Ext.isEmpty(drug_combo.getFieldValue('DrugPrepFas_id'))) {
			baseParams.DrugPrepFas_id = drug_combo.getFieldValue('DrugPrepFas_id');
		}*/

		//dpf_combo.lastQuery = 'This query sample that is not will never appear';
		dpf_combo.getStore().baseParams = baseParams;
		//dpf_combo.getStore().load({callback: callback});
	},
	refreshDrugParams: function() {
		//callback = callback || Ext.emptyFn;
		var bf = this.findById('NEDEW_FormPanel').getForm();
		var baseParams = {};

		drug_combo = bf.findField('Drug_id');
		dpf_combo = bf.findField('DrugPrepFas_id');
		date_field = bf.findField('EvnDrug_setDate');
		lpu_section_combo = bf.findField('LpuSection_id');
		storage_combo = bf.findField('Storage_id');
		ep_combo = bf.findField('EvnPrescrTreatDrug_id');

		if (!Ext.isEmpty(date_field.getValue())) {
			baseParams.date = Ext.util.Format.date(date_field.getValue(), 'd.m.Y');
		}
		if (!Ext.isEmpty(lpu_section_combo.getValue())) {
			//baseParams.LpuSection_id = lpu_section_combo.getValue();
		}
		if (!Ext.isEmpty(storage_combo.getValue())) {
			baseParams.Storage_id = storage_combo.getValue();
		}

		if (!Ext.isEmpty(dpf_combo.getValue()) && !Ext.isEmpty(dpf_combo.getValue())) {
			baseParams.DrugPrepFas_id = dpf_combo.getValue();
		} else if (!Ext.isEmpty(ep_combo.getValue()) && !Ext.isEmpty(ep_combo.getFieldValue('DrugPrepFas_id'))) {
			baseParams.DrugPrepFas_id = ep_combo.getFieldValue('DrugPrepFas_id');
		}

		if (!Ext.isEmpty(ep_combo.getValue()) && !Ext.isEmpty(ep_combo.getFieldValue('Drug_id'))) {
			baseParams.Drug_id = ep_combo.getFieldValue('Drug_id');
		}

		//drug_combo.lastQuery = 'This query sample that is not will never appear';
		drug_combo.getStore().baseParams = baseParams;
		//drug_combo.getStore().load({callback: callback});
	},
	refreshDocumentUcStrParams: function() {
		var bf = this.findById('NEDEW_FormPanel').getForm();
		var baseParams = {};

		dus_combo = bf.findField('DocumentUcStr_oid');
		evn_drug_field = bf.findField('EvnDrug_id');
		date_field = bf.findField('EvnDrug_setDate');
		lpu_section_combo = bf.findField('LpuSection_id');
		storage_combo = bf.findField('Storage_id');
		drug_combo = bf.findField('Drug_id');
		ep_combo = bf.findField('EvnPrescrTreatDrug_id');

		if (!Ext.isEmpty(evn_drug_field.getValue())) {
			baseParams.EvnDrug_id = evn_drug_field.getValue();
		}
		if (!Ext.isEmpty(date_field.getValue())) {
			baseParams.date = Ext.util.Format.date(date_field.getValue(), 'd.m.Y');
		}
		if (!Ext.isEmpty(lpu_section_combo.getValue())) {
			//baseParams.LpuSection_id = lpu_section_combo.getValue();
		}
		if (!Ext.isEmpty(storage_combo.getValue())) {
			baseParams.Storage_id = storage_combo.getValue();
		}

		if (!Ext.isEmpty(drug_combo.getValue())) {
			baseParams.Drug_id = drug_combo.getValue();
		} else if (!Ext.isEmpty(ep_combo.getValue()) && !Ext.isEmpty(ep_combo.getFieldValue('Drug_id'))) {
			baseParams.Drug_id = ep_combo.getFieldValue('Drug_id');
		}

		dus_combo.getStore().baseParams = baseParams;
	},
	loadMol: function() {
		var bf = this.findById('NEDEW_FormPanel').getForm();

		var storage_id = bf.findField('Storage_id').getValue();
		var mol_combo = bf.findField('Mol_id');

		var mol = mol_combo.getStore().getById(mol_combo.getValue());
		if (mol && mol.get('Storage_id') == storage_id) {
			mol_combo.setValue(mol_combo.getValue());
		} else {
			mol_combo.getStore().baseParams = {Storage_id: storage_id};
			mol_combo.getStore().load({callback: function() {
				if (mol_combo.getStore().getCount() == 1) {
					mol_combo.setValue(mol_combo.getStore().getAt(0).id);
				}
			}});
		}
	},
	loadStorageByDrugPrepFas: function() {
		if (this.storageLoading) {return false;}
		this.storageLoading = true;
		var bf = this.findById('NEDEW_FormPanel').getForm();

		var dpf_combo = bf.findField('DrugPrepFas_id');
		var storage_combo = bf.findField('Storage_id');

		var dpf = dpf_combo.getStore().getById(dpf_combo.getValue());
		if (!dpf) {return false;}

		var storage = storage_combo.getStore().getById(dpf.get('Storage_id'));
		if (storage) {
			storage_combo.setValue(dpf.get('Storage_id'));
			//storage_combo.fireEvent('change', storage_combo, storage_combo.getValue());
			this.loadMol();
			this.storageLoading = false;
		} else {
			this.refreshStorageParams();
			storage_combo.getStore().load({
				callback: function() {
					storage = storage_combo.getStore().getById(dpf.get('Storage_id'));
					if (storage) {
						storage_combo.setValue(storage.id);
						this.loadMol();
						//storage_combo.fireEvent('change', storage_combo, storage_combo.getValue());
					}
					this.storageLoading = false;
				}.createDelegate(this)
			});
		}
		return true;
	},
	setFilterLpuSection: function(newValue)
	{
		var bf = this.findById('NEDEW_FormPanel').getForm();
		var ls_id = bf.findField('LpuSection_id').getValue();
		//log(newValue);
		//bf.findField('LpuSection_id').clearValue();

		var EvnPS_id = this.EvnDrug_rid;
		/*if (EvnPS_id != null) {
			if (EvnPS_id != bf.findField('EvnDrug_pid').getValue()) {
				bf.findField('LpuSection_id').clearValue();
			} else {
				bf.findField('EvnDrug_pid').setValue(0);
			}
		} else {
			bf.findField('LpuSection_id').clearValue();
		}*/
		if ( !newValue )
		{
			if(this.type!=null){
				setLpuSectionGlobalStoreFilter(
				{
				});
			}else{
				setLpuSectionGlobalStoreFilter(
				{
					isStac: this.openMode != 'prescription'
				});
			}
		}
		else
		{
			if(this.type!=null){
				setLpuSectionGlobalStoreFilter(
				{
					onDate: Ext.util.Format.date(newValue, 'd.m.Y')
				});
			}else{
				setLpuSectionGlobalStoreFilter(
				{
					isStac: this.openMode != 'prescription',
					onDate: Ext.util.Format.date(newValue, 'd.m.Y')
				});
			}

		}
		bf.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

		if (bf.findField('LpuSection_id').getStore().getById(ls_id))
		{
			bf.findField('LpuSection_id').setValue(ls_id);
		}
	},
	setFilterMol: function(LpuSection_id)
	{
		// Устанавливаем фильтр и если по условиям фильтра найдена только одна запись - то устанавливаем эту запись
		var form = this;
		var combo = form.findById('nedewMol_id');
		combo.getStore().clearFilter();
		combo.lastQuery = '';
		var co = 0;
		var Mol_id =null;
		var OldMol_id = combo.getValue();
		var Yes = false;
		if (combo.getStore().getCount()>0)
		{
			combo.getStore().filterBy(function(record)
			{
				if ((LpuSection_id==record.get('LpuSection_id')) && (LpuSection_id>0))
				{
					co++;
					Mol_id = record.get('Mol_id');
					if (OldMol_id == Mol_id)
					{
						//log(Mol_id);
						Yes = true;
					}
				}
				return ((LpuSection_id==record.get('LpuSection_id')) && (LpuSection_id>0));
			});
			if (Yes)
			{
				form.findById('nedewMol_id').setValue(OldMol_id);
			}
			else
			{
				if (co==1)
				{
					form.findById('nedewMol_id').setValue(Mol_id);
				}
				else
				{
					/*if (OldMol_id>0)
					 {
					 form.findById('nedewMol_id').setValue(OldMol_id);
					 }
					 else
					 */
					{
						form.findById('nedewMol_id').setValue(null);
					}
				}
			}
		}
	},
	getGoodsPackCount: function(callback) {
		var bf = this.findById('NEDEW_FormPanel').getForm();
		var goods_unit_combo = bf.findField('GoodsUnit_id');

		var drug_id = bf.findField('Drug_id').getValue();
		var goods_unit_id = goods_unit_combo.getValue();

		if (Ext.isEmpty(drug_id) || Ext.isEmpty(goods_unit_id)) {
			return;
		}

		Ext.Ajax.request({
			params:{
				Drug_id: drug_id,
				GoodsUnit_id: goods_unit_id
			},
			url: '/?c=Farmacy&m=getGoodsPackCount',
			callback: function(options, success, response) {
				if ( success ) {
					var result = Ext.util.JSON.decode(response.responseText);

					if (result && result[0] && !Ext.isEmpty(result[0].GoodsPackCount_Count)){
						bf.findField('GoodsPackCount_Count').setValue(result[0].GoodsPackCount_Count);
					} else {
						var GoodsUnit_Name = '';
						if (goods_unit_combo.getStore().getById(goods_unit_id)) {
							GoodsUnit_Name = goods_unit_combo.getStore().getById(goods_unit_id).get('GoodsUnit_Name');
						}
						if(goods_unit_id == 57) {
							bf.findField('GoodsPackCount_Count').setValue(1);
						} else {
							bf.findField('GoodsPackCount_Count').setValue('');
							sw.swMsg.alert(lang['vnimanie'],'Чтобы указать количество '+GoodsUnit_Name+' в потребительской упаковке выбранного медикамента обратитесь к Старшей медсестре или Администратору системы для заполнения справочника «Количество товара в потребительской упаковке»');
						}
					}

					if (typeof callback == 'function') {
						callback();
					}
				}
			}.createDelegate(this)
		});
	},
	clearValues: function(enable)
	{
		var bf = this.findById('NEDEW_FormPanel').getForm();
		bf.reset();
		bf.findField('EvnDrug_id').setValue(null);
		bf.findField('EvnDrug_setDate').setValue(null);
		bf.findField('EvnDrug_setTime').setValue(null);
		bf.findField('EvnDrug_pid').setValue(null);
		bf.findField('Server_id').setValue(null);
		bf.findField('EvnPrescr_id').setValue(null);
		bf.findField('EvnPrescrTreatDrug_id').setValue(null);
		bf.findField('Drug_id').setValue(null);
		bf.findField('LpuSection_id').setValue(null);
		bf.findField('EvnDrug_Price').setValue(null);
		bf.findField('EvnDrug_Sum').setValue(null);
		bf.findField('DocumentUc_id').setValue(null);
		bf.findField('DocumentUcStr_id').setValue(null);
		bf.findField('DocumentUcStr_oid').setValue(null);
		bf.findField('Storage_id').setValue(null);
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

		this.EditForm = new Ext.form.FormPanel({
			autoScroll: true,
			bodyStyle: 'padding: 0.5em;',
			border: false,
			frame: true,
			id: 'NEDEW_FormPanel',
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
					displayField: 'Evn_Name',
					editable: false,
					enableKeyEvents: true,
					fieldLabel: lang['otdelenie'],
					tabIndex: TABINDEX_EDEW + 0,
					hiddenName: 'EvnDrug_pid',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = this.findById('NEDEW_FormPanel').getForm();
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
							{ name: 'Lpu_id', type: 'int' },
							{ name: 'LpuSection_id', type: 'int' },
							{ name: 'MedPersonal_id', type: 'int' },
							{ name: 'Evn_Name', type: 'string' },
							{ name: 'Evn_setDate', type: 'date', format: 'd.m.Y' },
							{ name: 'Evn_disDate', type: 'date', format: 'd.m.Y' }
						],
						id: 'Evn_id'
					}),
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
						items: [{
							border: false,
							layout: 'form',
							items: [{
								allowBlank: false,
								fieldLabel: lang['data'],
								format: 'd.m.Y',
								listeners: {
									'change': function(field, newValue, oldValue) {
										this.setFilterLpuSection(newValue);
										var base_form = this.findById('NEDEW_FormPanel').getForm();

										var lpu_section_id = base_form.findField('LpuSection_id').getValue();
										base_form.findField('LpuSection_id').clearValue();

										var section_filter_params = {};
										/*
										 var user_lpu_section_id = this.UserLpuSection_id;
										 var user_lpu_sections = this.UserLpuSections;
										 */
										if ( newValue ) {
											section_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
										}

										setLpuSectionGlobalStoreFilter(section_filter_params);

										base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

										if ( base_form.findField('LpuSection_id').getStore().getById(lpu_section_id) ) {
											// значение
											base_form.findField('LpuSection_id').setValue(lpu_section_id);
											// и вызов при изменении этого значения
											base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), lpu_section_id);
										}

										/*
										 log(bf.findField('Drug_id').getStore().baseParams);
										 log(bf.findField('DrugPrepFas_id').getStore().baseParams);
										 */
										base_form.findField('Drug_id').getStore().baseParams.date = Ext.util.Format.date(newValue,'d.m.Y');
										base_form.findField('DrugPrepFas_id').getStore().baseParams.date = Ext.util.Format.date(newValue,'d.m.Y');
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
								items: [{
									fieldLabel: lang['vremya'],
									listeners: {
										'keydown': function (inp, e) {
											if ( e.getKey() == Ext.EventObject.F4 )
											{
												e.stopEvent();
												inp.onTriggerClick();
											}
										}
									},
									name: 'EvnDrug_setTime',
									onTriggerClick: function() {
										var bf = this.findById('NEDEW_FormPanel').getForm();
										var time_field = bf.findField('EvnDrug_setTime');
										if ( time_field.disabled ) {
											return false;
										}
										setCurrentDateTime({
											dateField: bf.findField('EvnDrug_setDate'),
											loadMask: true,
											setDate: true,
											setDateMaxValue: true,
											setDateMinValue: false,
											setTime: true,
											timeField: time_field,
											windowId: 'NEDEW_FormPanel'
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
						fieldLabel: lang['ispolnenie_naznacheniya'],
						hiddenName: 'LpuSection_id',
						id: 'nedewLpuSection_id',
						lastQuery: '',
						//listWidth: 650,
						linkedElements: [ ],
						tabIndex: TABINDEX_EDEW + 3,
						xtype: 'swlpusectionglobalcombo',
						listeners: {
							change: function(combo, newValue, oldValue) {
								var bf = this.findById('NEDEW_FormPanel').getForm();

								var storage_combo = bf.findField('Storage_id');
								var storage_id = storage_combo.getValue();
								this.refreshStorageParams();
								storage_combo.getStore().load({callback: function() {
									if (!Ext.isEmpty(storage_id)) {
										if (storage_combo.getStore().getById(storage_id)) {
											storage_combo.setValue(storage_id);
										} else {
											storage_combo.setValue(null);
										}
									} else if (storage_combo.getStore().getCount() == 1) {
										storage_combo.setValue(storage_combo.getStore().getAt(0).id);
									}
									storage_combo.fireEvent('change', storage_combo, storage_combo.getValue());
								}});
							}.createDelegate(this)
						}
					},
					{
						allowBlank: false,
						anchor: '98%',
						hiddenName: 'Storage_id',
						id: 'nedewStorage_id',
						labelField: lang['sklad'],
						lastQuery: '',
						//listWidth: 650,
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var bf = this.findById('NEDEW_FormPanel').getForm();

								var mol_combo = bf.findField('Mol_id');
								var dbf_combo = bf.findField('DrugPrepFas_id');
								var drug_combo = bf.findField('Drug_id');
								var dus_combo = bf.findField('DocumentUcStr_oid');

								if (!Ext.isEmpty(newValue)) {
									this.loadMol();

									var dbf_id = dbf_combo.getValue();
									var drug_id = drug_combo.getValue();

									this.refreshDrugPrepFasParams();
									dbf_combo.getStore().load({
										callback: function() {
											if (dbf_combo.getStore().getById(dbf_id)) {
												dbf_combo.setValue(dbf_id);
											} else {
												dbf_combo.setValue(null);
											}

											this.refreshDrugParams();
											drug_combo.getStore().load({
												callback: function() {
													var record = drug_combo.getStore().getById(drug_id);
													if (record) {
														drug_combo.setValue(record.get('Drug_id'));

														bf.findField('Drug_Fas').setRawValue(record.get('Drug_Fas') ? record.get('Drug_Fas') : 1);
														bf.findField('DrugForm_Name').setRawValue(record.get('DrugForm_Name'));
														bf.findField('GoodsUnit_bid').setValue(record.get('GoodsUnit_bid'));
														bf.findField('GoodsUnit_bName').setValue(record.get('GoodsUnit_bName'));
														bf.findField('GoodsPackCount_bCount').setValue(record.get('GoodsPackCount_bCount'));
													} else {
														drug_combo.setValue(null);

														bf.findField('Drug_Fas').setRawValue('');
														bf.findField('DrugForm_Name').setRawValue('');
														bf.findField('GoodsUnit_bid').setValue('');
														bf.findField('GoodsUnit_bName').setValue('');
														bf.findField('GoodsPackCount_bCount').setValue('');
													}

													this.refreshDocumentUcStrParams();
													if (Ext.isEmpty(drug_combo.getValue())) {
														dus_combo.setValue(null);
														dus_combo.getStore().removeAll();
														dus_combo.fireEvent('change', dus_combo, dus_combo.getValue());
													} else {
														dus_combo.getStore().load({
															callback: function() {
																var dus = dus_combo.getStore().getById(dus_combo.getValue());
																if (!dus && dus_combo.getStore().getCount() == 1) {
																	dus = dus_combo.getStore().getAt(0);
																}
																dus_combo.setValue(dus?dus.id:null);
																dus_combo.fireEvent('change', dus_combo, dus_combo.getValue());
															}
														});
													}
												}.createDelegate(this)
											});
										}.createDelegate(this)
									});
								} else {
									mol_combo.setValue(null);
									mol_combo.getStore().removeAll();

									this.refreshDrugPrepFasParams();
									dbf_combo.setValue(null);
									dbf_combo.lastQuery = 'This query sample that is not will never appear';

									this.refreshDrugParams();
									drug_combo.setValue(null);
									drug_combo.lastQuery = 'This query sample that is not will never appear';

									this.refreshDocumentUcStrParams();
									dus_combo.setValue(null);
									dus_combo.getStore().removeAll();
									dus_combo.fireEvent('change', dus_combo, dus_combo.getValue());
								}
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EDEW + 4,
						xtype: 'swstoragecombo'
					},
					{
						//allowBlank: false,
						anchor: '98%',
						hiddenName: 'Mol_id',
						id: 'nedewMol_id',
						lastQuery: '',
						//listWidth: 650,
						listeners: {
							'change': function(combo, newValue, oldValue) {
								/*var bf = this.findById('NEDEW_FormPanel').getForm();

								bf.findField('Drug_id').setValue(null);
								bf.findField('Drug_id').lastQuery = 'This query sample that is not will never appear';
								bf.findField('Drug_id').getStore().baseParams.Storage_id = combo.getFieldValue('Storage_id');
								bf.findField('Drug_id').fireEvent('change', bf.findField('Drug_id'), bf.findField('Drug_id').getValue());

								bf.findField('DrugPrepFas_id').setValue(null);
								bf.findField('DrugPrepFas_id').lastQuery = 'This query sample that is not will never appear';
								bf.findField('DrugPrepFas_id').getStore().baseParams.Storage_id = combo.getFieldValue('Storage_id');
								bf.findField('DrugPrepFas_id').fireEvent('change', bf.findField('DrugPrepFas_id'), bf.findField('DrugPrepFas_id').getValue());*/
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EDEW + 4,
						xtype: 'swmolcombo'
					},{
						id: 'nedewEvnPrescrPanel',
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
									var bf = this.findById('NEDEW_FormPanel').getForm();
									var record = combo.getStore().getById(newValue);

									var lpu_section_combo = bf.findField('LpuSection_id');
									var mol_combo = bf.findField('Mol_id');
									var storage_combo = bf.findField('Storage_id');
									var dpf_combo = bf.findField('DrugPrepFas_id');
									var drug_combo = bf.findField('Drug_id');
									var dus_combo = bf.findField('DocumentUcStr_oid');

									if ( record )
									{
										bf.findField('EvnCourse_id').setValue(record.get('EvnCourse_id'));
										bf.findField('EvnCourseTreatDrug_id').setValue(record.get('EvnCourseTreatDrug_id'));
										bf.findField('EvnPrescr_id').setValue(record.get('EvnPrescrTreat_id'));
										bf.findField('GoodsUnit_id').setValue(record.get('GoodsUnit_id'));
										bf.findField('GoodsPackCount_Count').setValue(record.get('GoodsPackCount_Count'));

										bf.findField('PrescrFactCountDiff').setValue(record.get('PrescrFactCountDiff'));
										bf.findField('EvnPrescrTreat_Fact').maxValue = bf.findField('PrescrFactCountDiff').getValue();
										if (!bf.findField('EvnPrescrTreat_Fact').getValue()) {
											bf.findField('EvnPrescrTreat_Fact').setValue(record.get('EvnPrescrTreat_Fact'))
										}

										var LpuSection = lpu_section_combo.getStore().getById(record.get('LpuSection_id'));
										if (LpuSection) {
											lpu_section_combo.setValue(record.get('LpuSection_id'));
										}

										mol_combo.getStore().removeAll();
										if (record.get('Storage_id')) {
											mol_combo.getStore().baseParams = {Storage_id: record.get('Storage_id')};
											mol_combo.getStore().load({callback: function() {
												if (mol_combo.getStore().getById(record.get('Mol_id'))) {
													mol_combo.setValue(record.get('Mol_id'));
												} else if (mol_combo.getStore().getCount() == 1) {
													mol_combo.setValue(mol_combo.getStore().getAt(0).id);
												}
											}.createDelegate(this)});
										} else {
											mol_combo.setValue(null);
										}

										this.refreshStorageParams();
										storage_combo.getStore().load({
											callback: function() {
												if (storage_combo.getStore().getById(record.get('Storage_id'))) {
													storage_combo.setValue(record.get('Storage_id'));
												} else {
													storage_combo.setValue(null);
												}

												this.refreshDrugPrepFasParams();
												dpf_combo.getStore().load({
													callback: function() {
														if (dpf_combo.getStore().getById(record.get('DrugPrepFas_id'))) {
															dpf_combo.setValue(record.get('DrugPrepFas_id'));
														} else {
															dpf_combo.setValue(null);
														}

														this.refreshDrugParams();
														drug_combo.getStore().load({
															callback: function() {
																var drug = drug_combo.getStore().getById(record.get('Drug_id'));
																if (drug) {
																	drug_combo.setValue(drug.get('Drug_id'));

																	bf.findField('Drug_Fas').setRawValue(drug.get('Drug_Fas') ? drug.get('Drug_Fas') : 1);
																	bf.findField('DrugForm_Name').setRawValue(drug.get('DrugForm_Name'));
																	bf.findField('GoodsUnit_bid').setValue(drug.get('GoodsUnit_bid'));
																	bf.findField('GoodsUnit_bName').setValue(drug.get('GoodsUnit_bName'));
																	bf.findField('GoodsPackCount_bCount').setValue(drug.get('GoodsPackCount_bCount'));
																} else {
																	drug_combo.setValue(null);

																	bf.findField('Drug_Fas').setRawValue('');
																	bf.findField('DrugForm_Name').setRawValue('');
																	bf.findField('GoodsUnit_bid').setValue('');
																	bf.findField('GoodsUnit_bName').setValue('');
																	bf.findField('GoodsPackCount_bCount').setValue('');
																}

																this.refreshDocumentUcStrParams();
																dus_combo.getStore().removeAll();
																dus_combo.getStore().load({
																	callback: function() {
																		var DocumentUcStr = dus_combo.getStore().getById(record.get('DocumentUcStr_oid'));
																		if (DocumentUcStr) {
																			dus_combo.setValue(record.get('DocumentUcStr_oid'));
																		} else {
																			dus_combo.setValue(null);
																		}
                                                                        dus_combo.setLinkedFieldValues();
																		bf.findField('EvnDrug_Kolvo').setValue(record.get('EvnDrug_Kolvo'));
																		this.calculateEvnDrug();
																	}.createDelegate(this)
																});
															}.createDelegate(this)
														});
													}.createDelegate(this)
												});
												this.arr_time = eval(record.get('EvnPrescrTreat_Time'));
												this.loadQuestions();
											}.createDelegate(this)
										});
									}
									else
									{
										combo.setValue(null);
										bf.findField('EvnCourse_id').setValue(null);
										bf.findField('EvnCourseTreatDrug_id').setValue(null);
										bf.findField('EvnPrescr_id').setValue(null);
										bf.findField('PrescrFactCountDiff').setValue(0);
										bf.findField('EvnPrescrTreat_Fact').setValue(0);

										this.refreshStorageParams();
										storage_combo.lastQuery = 'This query sample that is not will never appear';

										this.refreshDrugPrepFasParams();
										dpf_combo.lastQuery = 'This query sample that is not will never appear';
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
									{ name: 'LpuSection_id', mapping: 'LpuSection_id' },
									{ name: 'Storage_id', mapping: 'Storage_id' },
									{ name: 'Mol_id', mapping: 'Mol_id' },
									{ name: 'Mol_Name', mapping: 'Mol_Name' },
									{ name: 'DrugFinance_Name', mapping: 'DrugFinance_Name' },
									{ name: 'WhsDocumentCostItemType_Name', mapping: 'WhsDocumentCostItemType_Name' },
									{ name: 'DrugForm_Name', mapping: 'DrugForm_Name' },
									{ name: 'Drug_Name', mapping: 'Drug_Name' },
									{ name: 'GoodsUnit_id', mapping: 'GoodsUnit_id' },
									{ name: 'GoodsUnit_sid', mapping: 'GoodsUnit_sid' },
									{ name: 'GoodsPackCount_Count', mapping: 'GoodsPackCount_Count' },
                                    { name: 'GoodsPackCount_sCount', mapping: 'GoodsPackCount_sCount' },
									{ name: 'EvnPrescrTreatDrug_KolvoEd', mapping: 'EvnPrescrTreatDrug_KolvoEd' }, //  #142958 Количество из назначения
									{ name: 'EvnPrescrTreatDrug_Kolvo', mapping: 'EvnPrescrTreatDrug_Kolvo' }, //  #142958
									{ name: 'EvnPrescrTreat_Time', mapping: 'EvnPrescrTreat_Time' } //  #160294
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
													border: false,
						layout: 'column',
                        //hidden: true,
						items:
							[{
								border: false,
								labelWidth: 145,
								layout: 'form',
								items: [
									{
										height: 10,
										border: false
									},
									{
						fieldLabel: langs('Списать приемов'),
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
								var bf = this.findById('NEDEW_FormPanel').getForm();

								var ep_combo = bf.findField('EvnPrescrTreatDrug_id');
								var ep_data = ep_combo.getStore().getById(ep_combo.getValue());

								/*if (newValue >= 0 && newValue <= field.maxValue) {
									var singleKolvoEd = 0;
									var newKolvoEd = 0;
									if (ep && !Ext.isEmpty(ep.get('EvnDrug_KolvoEd'))) {
										singleKolvoEd = ep.get('EvnDrug_KolvoEd');
									}
									newKolvoEd = singleKolvoEd*newValue;

									bf.findField('EvnDrug_Kolvo').setValue(singleKolvoEd);
									bf.findField('EvnDrug_KolvoEd').setValue(newKolvoEd);
									bf.findField('EvnDrug_KolvoEd').fireEvent('change', bf.findField('EvnDrug_KolvoEd'), newKolvoEd);
								}*/
								if (newValue >= 0) {
									var fact = (newValue>field.maxValue) ? field.maxValue : newValue;
                                    var kolvo = 0;
                                    var kolvo_field = null;

									var ep_kolvo = ep_data ? ep_data.get('EvnPrescrTreatDrug_Kolvo') : 0;
									var ep_s_kolvo = ep_data ? ep_data.get('EvnPrescrTreatDrug_KolvoEd') : 0;
                                    var ep_gu_id = ep_data && ep_data.get('GoodsUnit_id')*1 > 0 ? ep_data.get('GoodsUnit_id')*1 : 0;
                                    var ep_gu_sid = ep_data && ep_data.get('GoodsUnit_sid')*1 > 0 ? ep_data.get('GoodsUnit_sid')*1 : 0;
                                    var gpc_s_count = ep_data && ep_data.get('GoodsPackCount_sCount')*1 > 0 ? ep_data.get('GoodsPackCount_sCount')*1 : 1;

                                    var gu_id = bf.findField('GoodsUnit_id').getValue()*1 > 0 ? bf.findField('GoodsUnit_id').getValue()*1 : 1;
                                    var gpc_count = bf.findField('GoodsPackCount_Count').getValue()*1 > 0 ? bf.findField('GoodsPackCount_Count').getValue()*1 : 1;
                                    var gu_bid = bf.findField('GoodsUnit_bid').getValue()*1 > 0 ? bf.findField('GoodsUnit_bid').getValue()*1 : 1;
                                    var gpc_b_count = bf.findField('GoodsPackCount_bCount').getValue()*1 > 0 ? bf.findField('GoodsPackCount_bCount').getValue()*1 : 1;

									if (ep_gu_id == gu_id) {
										kolvo = ep_kolvo;
                                        kolvo_field = 'EvnDrug_KolvoEd';
                                    } else if (ep_gu_sid == gu_id) {
										kolvo = ep_s_kolvo;
                                        kolvo_field = 'EvnDrug_KolvoEd';
                                    } else if (ep_gu_id == gu_bid) {
										kolvo = ep_kolvo
                                        kolvo_field = 'EvnDrug_Kolvo_Show';
                                    } else if (ep_gu_sid == gu_bid) {
										kolvo = ep_s_kolvo;
                                        kolvo_field = 'EvnDrug_Kolvo_Show';
                                    } else {
                                        kolvo = (kolvo/gpc_s_count)*gpc_b_count;
                                        kolvo_field = 'EvnDrug_Kolvo_Show';
									}

									if (!Ext.isEmpty(kolvo_field)) {
                                        bf.findField(kolvo_field).setValue(fact*kolvo);
                                        bf.findField(kolvo_field).fireEvent('change', bf.findField(kolvo_field), bf.findField(kolvo_field).getValue());
                                    }}
							}.createDelegate(this)
						},
						xtype: 'numberfield'
				}]
					},
					{
				autoHeight: true,
				//height: 50,
				id: 'fieldsetForm',
				title: '', //langs('Время приема'),
				xtype: 'fieldset',
				border: false,
				width: 500,
				style: 'margin: 10px;',
				//labelWidth: 750,
				layout: 'column',
				items: []
			}]
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
				}, {
					allowBlank: false,
					xtype: 'swevndrugprepcombo',
					anchor: '98%',
					hiddenName: 'DrugPrepFas_id',
					fieldLabel: lang['medikament'],
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var bf = this.findById('NEDEW_FormPanel').getForm();
							var record = combo.getStore().getById(newValue);

							var storage_combo = bf.findField('Storage_id');
							var drug_combo = bf.findField('Drug_id');

							if (record) {
								this.loadStorageByDrugPrepFas();

								this.refreshDrugParams();
								var drug_id = drug_combo.getValue();
								drug_combo.getStore().load({
									callback: function() {
										var drug = drug_combo.getStore().getById(drug_id);
										if (!drug && drug_combo.getStore().getCount() == 1) {
											drug = drug_combo.getStore().getAt(0);
										}
										drug_combo.setValue(drug?drug.id:null);
										bf.findField('Drug_id').fireEvent('change', bf.findField('Drug_id'), bf.findField('Drug_id').getValue());
									}.createDelegate(this)
								});
							} else {
								this.refreshDrugParams();
								bf.findField('Drug_id').setValue(null);
								bf.findField('Drug_id').getStore().removeAll();
								bf.findField('Drug_id').lastQuery = 'This query sample that is not will never appear';
								bf.findField('Drug_id').fireEvent('change', bf.findField('Drug_id'), bf.findField('Drug_id').getValue());
							}
						}.createDelegate(this)
					},
					tabIndex: TABINDEX_EDEW + 4
				},
					{ // второй комбобокс (упаковка)
						allowBlank: false,
						xtype: 'swevndrugpackcombo',
						anchor: '98%',
						fieldLabel: lang['upakovka'],
						hiddenName: 'Drug_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var bf = this.findById('NEDEW_FormPanel').getForm();

								var record = combo.getStore().getById(newValue);

								var storage_combo = bf.findField('Storage_id');
								var dpf_combo = bf.findField('DrugPrepFas_id');
								var dus_combo = bf.findField('DocumentUcStr_oid');

								if (record && !Ext.isEmpty(record.get('DrugPrepFas_id'))) {
									bf.findField('Drug_Fas').setRawValue(record.get('Drug_Fas') ? record.get('Drug_Fas') : 1);
									bf.findField('DrugForm_Name').setRawValue(record.get('DrugForm_Name'));
									bf.findField('GoodsUnit_bid').setValue(record.get('GoodsUnit_bid'));
									bf.findField('GoodsUnit_bName').setValue(record.get('GoodsUnit_bName'));
									bf.findField('GoodsPackCount_bCount').setValue(record.get('GoodsPackCount_bCount'));

									if (Ext.isEmpty(dpf_combo.getValue())) {
										this.refreshDrugPrepFasParams();
										dpf_combo.getStore().load({
											callback: function() {
												dpf_combo.setValue(record.get('DrugPrepFas_id'));
												if (!Ext.isEmpty(dpf_combo.getValue())) {
													this.loadStorageByDrugPrepFas();
												}
											}.createDelegate(this)
										});
									}

									var dus_id = dus_combo.getValue();
									this.refreshDocumentUcStrParams();
									dus_combo.getStore().load({
										callback: function() {
											var dus = dus_combo.getStore().getById(dus_id);
											if (!dus && dus_combo.getStore().getCount() > 0) {
												dus = dus_combo.getStore().getAt(0);
												dus_combo.getStore().each(function(rec) {
													var godnDate1 = Date.parseDate(rec.get('PrepSeries_GodnDate'), 'd.m.Y');
													var godnDate2 = Date.parseDate(dus.get('PrepSeries_GodnDate'), 'd.m.Y');
													if (godnDate1 < godnDate2) dus = rec;
												});
											}
											dus_combo.setValue(dus?dus.id:null);
											dus_combo.fireEvent('change', dus_combo, dus_combo.getValue());
										}
									});
								} else {
									bf.findField('Drug_Fas').setRawValue('');
									bf.findField('DrugForm_Name').setRawValue('');
									bf.findField('GoodsUnit_bid').setValue('');
									bf.findField('GoodsUnit_bName').setValue('');
									bf.findField('GoodsPackCount_bCount').setValue('');

									this.refreshDrugPrepFasParams();
									dpf_combo.getStore().load({
										callback: function() {
											if (!Ext.isEmpty(dpf_combo.getValue())) {
												dpf_combo.setValue(dpf_combo.getValue());
												storage_combo.setValue(dpf_combo.getFieldValue('Storage_id'));
											}
										}
									});

									dus_combo.clearValue();
									dus_combo.getStore().removeAll();
									dus_combo.fireEvent('change', dus_combo, null);
								}
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EDEW + 5
					}, {
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
                        minChars: 3,
                        minLength: 1,
                        minLengthText: lang['pole_doljno_byit_zapolneno'],
                        mode: 'local',
                        resizable: true,
                        selectOnFocus: true,
                        valueField: 'DocumentUcStr_id',
						codeField: 'PrepSeries_Ser',
                        width: 500,
                        xtype: 'combo',
						doQuery: function(q, forceAll) {
							if ( q === undefined || q === null ) {
								q = '';
							}

							var qe = {
								query: q,
								forceAll: forceAll,
								combo: this,
								cancel: false
							};

							if ( this.fireEvent('beforequery', qe) === false || qe.cancel ) {
								return false;
							}

							q = qe.query;
							forceAll = qe.forceAll;

							if (forceAll) {
								this.lastQuery = q;
								this.getStore().clearFilter();
								this.selectedIndex = -1;
								this.onLoad();
							} else if ( q.length >= this.minChars ) {
								if ( this.lastQuery != q ) {
									this.lastQuery = q;
									this.selectedIndex = -1;

									this.getStore().filterBy(function(record, id) {
										var result = true;
										var patt_display = new RegExp(q.toLowerCase());
										var patt_code = new RegExp('^' + q.toLowerCase());

										result = patt_display.test(record.get(this.displayField).toLowerCase());

										if ( !result ) {
											result = patt_code.test(record.get(this.codeField).toLowerCase());
										}

										return result;
									}, this);

									this.onLoad();
								}
								else {
									this.selectedIndex = -1;
									this.onLoad();
								}
							}
						},
                        listeners:
                        {
                            'change': function(combo, newValue, oldValue) {
                                var bf = this.findById('NEDEW_FormPanel').getForm();
                                combo.setLinkedFieldValues();
                                this.calculateEvnDrug();

                                /*var kolvo_field = bf.findField('EvnDrug_Kolvo_Show');
                                kolvo_field.fireEvent('change', kolvo_field, kolvo_field.getValue());*/
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
                                    { name: 'DocumentUcStr_Count', mapping: 'DocumentUcStr_Count' },
                                    { name: 'DocumentUcStr_Price', mapping: 'DocumentUcStr_Price' },
                                    { name: 'DocumentUcStr_Sum', mapping: 'DocumentUcStr_Sum' },
                                    //{ name: 'DocumentUcStr_EdCount', mapping: 'DocumentUcStr_EdCount' },
                                    { name: 'PrepSeries_Ser', mapping: 'PrepSeries_Ser' },
                                    { name: 'PrepSeries_GodnDate', mapping: 'PrepSeries_GodnDate' },
                                    { name: 'DrugFinance_id', mapping: 'DrugFinance_id' },
                                    { name: 'DrugFinance_Name', mapping: 'DrugFinance_Name' },
                                    { name: 'GoodsUnit_id', mapping: 'GoodsUnit_id' },
                                    { name: 'GoodsUnit_bid', mapping: 'GoodsUnit_bid' },
                                    { name: 'GoodsUnit_bNick', mapping: 'GoodsUnit_bNick' },
                                    { name: 'GoodsPackCount_bCount', mapping: 'GoodsPackCount_bCount' }
                                ]),
                            url: '/?c=EvnDrug&m=loadDocumentUcStrList'
                        }),
                        tpl: new Ext.XTemplate(
                            '<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 8pt; font-weight: bold;">',
                            '<td style="padding: 2px; width: 20%;">Срок годности</td>',
                            '<td style="padding: 2px; width: 15%;">Цена</td>',
                            '<td style="padding: 2px; width: 15%;">Остаток</td>',
                            '<td style="padding: 2px; width: 10%;">Ед. уч.</td>',
                            '<td style="padding: 2px; width: 25%;">Источник финансирования</td>',
                            '<td style="padding: 2px; width: 15%;">Серия</td></tr>',
                            '<tpl for="."><tr class="x-combo-list-item" style="font-family: tahoma; font-size: 8pt;">',
                            '<td style="padding: 2px;">{PrepSeries_GodnDate}&nbsp;</td>',
                            '<td style="padding: 2px;">{DocumentUcStr_Price}&nbsp;</td>',
                            '<td style="padding: 2px;">{DocumentUcStr_Count}&nbsp;</td>',
                            '<td style="padding: 2px;">{GoodsUnit_bNick}&nbsp;</td>',
                            '<td style="padding: 2px;">{DrugFinance_Name}&nbsp;</td>',
                            '<td style="padding: 2px;">{PrepSeries_Ser}&nbsp;</td>',
                            '</tr></tpl>',
                            '</table>'
                        ),
						setLinkedFieldValues: function() {
                        	var id = this.getValue();
                            var base_form = wnd.EditForm.getForm();

							if(!Ext.isEmpty(id)) {
                                var record = this.getStore().getById(id);

                                base_form.findField('GoodsUnit_bid').setValue(record.get('GoodsUnit_bid'));
                                base_form.findField('GoodsUnit_bName').setValue(record.get('GoodsUnit_bNick'));
                                base_form.findField('GoodsPackCount_bCount').setValue(record.get('GoodsPackCount_bCount'));
							} else {
                                base_form.findField('GoodsUnit_bid').setValue(null);
                                base_form.findField('GoodsUnit_bName').setValue(null);
                                base_form.findField('GoodsPackCount_bCount').setValue(null);
							}
						}
                    }, {
						border: false,
						layout: 'column',
                        hidden: true,
						items:
							[{
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
							},
							{
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
                                    name: 'GoodsUnit_bid',
                                    xtype: 'hidden'
                                }, {
                                    name: 'GoodsPackCount_bCount',
                                    xtype: 'hidden'
                                }, {
									disabled: true,
									fieldLabel: lang['ed_ucheta'],
									name: 'GoodsUnit_bName',
									tabIndex: TABINDEX_EDEW + 6,
									width: 100,
									xtype: 'textfield'
								}]
							},
							{
								border: false,
								layout: 'form',
                                labelWidth: 130,
								items: [{
									disabled: false,
									fieldLabel: lang['ed_spisaniia'],
									hiddenName: 'GoodsUnit_id',
									tabIndex: TABINDEX_EDEW + 7,
									width: 100,
									xtype: 'swgoodsunitcombo',
									listeners: {
										'change': function(combo, newVal, oldVal) {
											var bf = this.findById('NEDEW_FormPanel').getForm();

											var record = bf.findField('DocumentUcStr_oid').getStore().getById(bf.findField('DocumentUcStr_oid').getValue());

											this.getGoodsPackCount(function() {
												if (record) {
													bf.findField('DocumentUcStr_EdCount').setValue(this.calculateEdCount(
														bf.findField('GoodsPackCount_Count').getValue(), record.get('DocumentUcStr_Count'), bf.findField('GoodsPackCount_bCount').getValue()
													));
													bf.findField('EvnDrug_Kolvo_Show').fireEvent('change', bf.findField('EvnDrug_Kolvo'), bf.findField('EvnDrug_Kolvo').getValue());
												}
											}.createDelegate(this));
										}.createDelegate(this)
									},
                                    onLoadStore(store) { //на случай если значение проставится раньше загрузки комбобокса
										var id = this.getValue();
									    if (!Ext.isEmpty(id)) {
                                            var idx = store.findBy(function(rec) { return rec.get('GoodsUnit_id') == id; });
                                            if (idx > -1) {
                                            	this.setValue(id);
											}
										}
                                    }
								}]
							},
							{
								border: false,
								layout: 'form',
                                labelWidth: 130,
								items: [{
									disabled: true,
									fieldLabel: lang['kol-vo_v_upak'],
									name: 'GoodsPackCount_Count',
									tabIndex: TABINDEX_EDEW + 8,
									width: 100,
									xtype: 'numberfield'
								}]
							}]
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								disabled: true,
								fieldLabel: lang['ostatok_ed_uch'],
								name: 'DocumentUcStr_Count',
								tabIndex: TABINDEX_EDEW + 10,
								width: 100,
								allowDecimals: true,
								allowNegative: false,
								decimalPrecision: 6,
								xtype: 'numberfield'
							}]
						}, {
							border: false,
							labelWidth: 130,
							layout: 'form',
							items: [{
								disabled: true,
								fieldLabel: lang['ostatok_ed_spis'],
								name: 'DocumentUcStr_EdCount',
								tabIndex: TABINDEX_EDEW + 11,
								allowDecimals: true,
								allowNegative: false,
								decimalPrecision: 6,
								width: 100,
								xtype: 'numberfield'
							}]
						}]
					},
					{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items:[{
								allowBlank: false,
								allowDecimals: true,
								allowNegative: false,
								decimalPrecision: 6,
								fieldLabel: lang['kolichestvo_ed_uch'],
								listeners: {
									'change': function(field, newValue, oldValue) {
										var bf = this.findById('NEDEW_FormPanel').getForm();
										var kolvo = newValue*1;
										var dus_kolvo = bf.findField('DocumentUcStr_Count').getValue()*1;

										var price = bf.findField('EvnDrug_Price').getValue();
										if (kolvo.toFixed(6) > dus_kolvo) {
											kolvo = dus_kolvo;
										}

										// Расчет суммы - цена берется из медикамента
										if (kolvo == 0) {
											bf.findField('EvnDrug_Sum').setValue('');
											bf.findField('EvnDrug_KolvoEd').setValue('');
										} else {
                                            var gpc_count = bf.findField('GoodsPackCount_Count').getValue()*1 > 0 ? bf.findField('GoodsPackCount_Count').getValue()*1 : 1;
                                            var gpc_b_count = bf.findField('GoodsPackCount_bCount').getValue()*1 > 0 ? bf.findField('GoodsPackCount_bCount').getValue()*1 : 1;
											var kolvo_ed = ((gpc_count * kolvo)/gpc_b_count).toFixed(6);

											if (kolvo_ed > bf.findField('DocumentUcStr_EdCount').getValue()) {
												kolvo_ed = bf.findField('DocumentUcStr_EdCount').getValue();
											}

											bf.findField('EvnDrug_Sum').setValue((price * newValue).toFixed(2));
											bf.findField('EvnDrug_KolvoEd').setValue(kolvo_ed);
										}

                                        bf.findField('EvnDrug_Kolvo').setValue(kolvo.toFixed(6));
                                        bf.findField('EvnDrug_Kolvo_Show').setValue(kolvo.toFixed(6));
									}.createDelegate(this)
								},
								minValue: 0.001,
								name: 'EvnDrug_Kolvo_Show',
								tabIndex: TABINDEX_EDEW + 12,
								width: 100,
								xtype: 'numberfield'
							}]
						}, {
							border: false,
							labelWidth: 50,
							layout: 'form',
							hidden: true,
							items: [{
								allowBlank: false,
								allowDecimals: true,
								allowNegative: false,
								decimalPrecision: 6,
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
							items: [{
								allowBlank: false,
								minValue: 0.0001,
								allowDecimals: true,
								allowNegative: false,
								disabled: true,
								decimalPrecision: 4,
								fieldLabel: lang['kol-vo_ed_spis'],
								name: 'EvnDrug_KolvoEd',
								tabIndex: TABINDEX_EDEW + 13,
								width: 100,
								xtype: 'numberfield',
								listeners: {
									'change': function(field, newValue, oldValue) {
                                        field.onChange(newValue);
									}.createDelegate(this)
								},
								onChange: function(newValue) {
                                    var bf = wnd.findById('NEDEW_FormPanel').getForm();
                                    var price = bf.findField('EvnDrug_Price').getValue();

                                    var ep_combo = bf.findField('EvnPrescrTreatDrug_id');
                                    var ep_data = ep_combo.getStore().getById(ep_combo.getValue());

                                    if (wnd.action != 'view' &&  newValue > bf.findField('DocumentUcStr_EdCount').getValue()) {
                                        bf.findField('EvnDrug_KolvoEd').setValue(bf.findField('DocumentUcStr_EdCount').getValue());
                                        newValue = bf.findField('DocumentUcStr_EdCount').getValue();
                                    }

                                    // Расчет суммы - цена берется из медикамента
                                    if ( newValue.toString().length == 0 ) {
                                        bf.findField('EvnDrug_Sum').setValue('');
                                        bf.findField('EvnDrug_Kolvo').setValue('');
                                        bf.findField('EvnDrug_Kolvo_Show').setValue('');
                                    } else {
										var gpc_DrugFas = bf.findField('Drug_Fas').getValue()*1 > 0 ? bf.findField('Drug_Fas').getValue()*1 : 1;
                                        var gpc_count = bf.findField('GoodsPackCount_Count').getValue()*1 > 0 ? bf.findField('GoodsPackCount_Count').getValue()*1 : 1;
                                        var gpc_b_count = bf.findField('GoodsPackCount_bCount').getValue()*1 > 0 ? bf.findField('GoodsPackCount_bCount').getValue()*1 : 1;
                                        var kolvo = (newValue/gpc_count)*gpc_b_count;
                                        var kolvo_show = kolvo < bf.findField('DocumentUcStr_Count').getValue() ? kolvo : bf.findField('DocumentUcStr_Count').getValue();

                                        kolvo_show = kolvo_show.toFixed(6);

                                        bf.findField('EvnDrug_Kolvo').setValue(kolvo_show);
                                        bf.findField('EvnDrug_Kolvo_Show').setValue(kolvo_show);
                                        bf.findField('EvnDrug_Sum').setValue((price * kolvo_show).toFixed(2));

                                        //расчет количества приемов
										if (ep_data) {
											/*
											var ep_data_kolvo = ep_data ? ep_data.get('EvnPrescrTreatDrug_KolvoEd') : 0; //количество на один прием в ед учета назначения
                                            var gpc_s_count = ep_data && ep_data.get('GoodsPackCount_sCount')*1 > 0 ? ep_data.get('GoodsPackCount_sCount')*1 : 1;
											var fact = 0;

											if (ep_data_kolvo > 0) {
                                                //пересчитываем количество на один прием из родных ед учета в ед учета партии
                                                var ep_kolvo = (ep_data_kolvo/gpc_s_count)*gpc_b_count;
                                                fact = kolvo_show/ep_kolvo;
											}
											*/
											var fact = bf.findField('EvnPrescrTreat_Fact').getValue();
											var ep_data_kolvo = ep_data.get('EvnDrug_Kolvo') ? ep_data.get('EvnDrug_Kolvo') : 0; //количество на один прием в упаковках
											if (ep_data_kolvo > 0) {  // Если 0 - количество приемов оставляем прежним
												fact = (newValue / (ep_data_kolvo * gpc_count)).toFixed(4);   // (ep_data_kolvo * gpc_count) - Количество на один прием в выбранных ед. измерениях
											}

                                            //округление количества приемов
                                            if (fact > 0 && fact < 1) {
                                                fact = 1;
											} else if (bf.findField('EvnPrescrTreat_Fact').maxValue == 1 && fact >= 2 && newValue <= gpc_count/gpc_DrugFas ) {
												// Если назначено меньше или равно фасовки в лек. форме, а максимальное количество приемов = 1, 
												// даем возможность списать лек. форму в одном приеме
												fact = 1;
                                            } else {
                                                fact = Math.floor(fact); // Отбрасываем дробную часть
                                            }
                                            bf.findField('EvnPrescrTreat_Fact').setValue(fact);
										}
                                    }
								}
							}]
						}]
					},
					{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								allowBlank: false,
								allowDecimals: true,
								allowNegative: false,
								decimalPrecision: 2,
								disabled: true,
								fieldLabel: lang['tsena_ed_uch'],
								listeners: {
									'change': function(field, newValue, oldValue) {
										var bf = this.findById('NEDEW_FormPanel').getForm();
										bf.findField('EvnDrug_Kolvo').fireEvent('change', bf.findField('EvnDrug_Kolvo'), bf.findField('EvnDrug_Kolvo').getValue());
									}.createDelegate(this)
								},
								name: 'EvnDrug_Price',
								tabIndex: TABINDEX_EDEW + 14,
								width: 100,
								xtype: 'numberfield'
							}]
						}, {
							border: false,
							labelWidth: 130,
							layout: 'form',
							items: [{
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
			labelWidth: 145,
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
					{ name: 'Storage_id' },
					{ name: 'Mol_id' },
					{ name: 'EvnDrug_Kolvo' },
					{ name: 'EvnDrug_KolvoEd' },
					{ name: 'EvnDrug_RealKolvo' },
					{ name: 'GoodsUnit_id' }
				]
			),
			region: 'center',
			trackResetOnLoad: true,
			url: '/?c=EvnDrug&m=saveEvnDrug'
		});

		Ext.apply(this, {
			buttons: [
				{
					handler: function()
					{
						if ( this.action != 'view' )
						{
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
							this.findById('NEDEW_FormPanel').getForm().findField('Drug_id').focus(true);
						}
					}.createDelegate(this),
					tabIndex: TABINDEX_EDEW + 22,
					text: BTN_FRMCANCEL,
					tooltip: lang['zakryit_okno']
				}
			],
			items: [
				new sw.Promed.PersonInformationPanelShort({
					id: 'NEDEW_PersonInformationFrame'
				}),
				this.EditForm
			]
		});
		sw.Promed.swNewEvnDrugEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
