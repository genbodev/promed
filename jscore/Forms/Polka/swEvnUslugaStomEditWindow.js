/**
 * swEvnUslugaStomEditWindow - окно редактирования/добавления выполнения стоматологической услуги.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package	  Polka
 * @access	   public
 * @copyright	Copyright (c) 2009 Swan Ltd.
 * @author	   Stas Bykov aka Savage (savage1981@gmail.com)
 * @version	  0.001-22.01.2010
 * @comment	  Префикс для id компонентов EUStomEF (EvnUslugaStomEditForm)
 */
sw.Promed.swEvnUslugaStomEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	Mse_Total: null,
	Mes_Spent: null,
	closable: false,
	closeAction: 'hide',
	collapsible: true,
	toogleParodontogram: function(uc_combo, uc_value)
	{
		var flag = false,
			wrapPanel = this.findById('EUStomEF_Parodontogram'),
			index = uc_combo.getStore().findBy(function(rec) {
				return (rec.get(uc_combo.valueField) == uc_value);
			});
		if ( index >= 0 ) {
			var rec = uc_combo.getStore().getAt(index);
			flag = (rec.get('UslugaComplex_AttributeList').indexOf(sw.Promed.StomHelper.USLUGA_PARODONTOGRAM_ATTR) >= 0);
		}
		if ( flag ) {
			wrapPanel.show();
			this.parodontogramPanel.doLoad();
		} else {
			wrapPanel.hide();
			this.parodontogramPanel.doClear();
		}
	},
	deleteEvent: function(event) {
		if (this.action == 'view') {
			return false;
		}
		if (event != 'EvnAgg') {
			return false;
		}
		var error = '';
		var grid = null;
		var question = '';
		var params = new Object();
		var url = '';
		switch (event) {
			case 'EvnAgg':
				error = 'При удалении осложнения возникли ошибки';
				grid = this.findById('EUStomEF_EvnAggGrid');
				question = 'Удалить осложнение?';
				url = '/?c=EvnAgg&m=deleteEvnAgg';
				break;
		}
		if (!grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(event + '_id')) {
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected();
		params[event + '_id'] = selected_record.get(event + '_id');
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if (buttonId == 'yes') {
					var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление записи..." });
					loadMask.show();
					Ext.Ajax.request({
						failure: function(response, options) {
							loadMask.hide();
							sw.swMsg.alert('Ошибка', error);
						},
						params: params,
						success: function(response, options) {
							loadMask.hide();
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj.success == false) {
								sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : error);
							} else {
								grid.getStore().remove(selected_record);
								if (grid.getStore().getCount() == 0) {
									grid.getTopToolbar().items.items[1].disable();
									grid.getTopToolbar().items.items[2].disable();
									grid.getTopToolbar().items.items[3].disable();
									LoadEmptyRow(grid);
								}
							}
							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						},
						url: url
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: 'Вопрос'
		});
	},
	doSave: function(options) {
		// options @Object
		// options.continueInput @Boolean Признак необходимости продолжить работу с формой для добавления новой услуги
		// options.openChildWindow @Function Открыть дочернее окно после сохранения
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}
		options = options||{};

		this.formStatus = 'save';

		var base_form = this.findById('EvnUslugaStomEditForm').getForm();

		base_form.findField('EvnUslugaStom_Kolvo').fireEvent('change', base_form.findField('EvnUslugaStom_Kolvo'), base_form.findField('EvnUslugaStom_Kolvo').getValue());

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.findById('EvnUslugaStomEditForm').getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var evn_usluga_stom_price = 0;
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
		var med_staff_fact_sid = base_form.findField('MedStaffFact_sid').getValue();
		var params = new Object();
		var PayType_SysNick = '';
		var usluga_complex_id = base_form.findField('UslugaComplex_id').getValue();
		var usluga_complex_code = '';
		var usluga_complex_name = '';

		var i, index, j, record;

		index = base_form.findField('UslugaComplex_id').getStore().findBy(function(rec) {
			return (rec.get('UslugaComplex_id') == usluga_complex_id);
		});
		record = base_form.findField('UslugaComplex_id').getStore().getAt(index);

		if ( record ) {
			usluga_complex_code = record.get('UslugaComplex_Code');
			usluga_complex_name = record.get('UslugaComplex_Name');
		}

		if ( !Ext.isEmpty(base_form.findField('EvnUslugaStom_UED').getValue()) ) {
			evn_usluga_stom_price = evn_usluga_stom_price + Number(base_form.findField('EvnUslugaStom_UED').getValue());
		}

		if ( !Ext.isEmpty(base_form.findField('EvnUslugaStom_UEM').getValue()) ) {
			evn_usluga_stom_price = evn_usluga_stom_price + Number(base_form.findField('EvnUslugaStom_UEM').getValue());
		}

		// MedPersonal_id
		index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
			return (rec.get('MedStaffFact_id') == med_staff_fact_id);
		});

		if ( index >= 0 ) {
			base_form.findField('MedPersonal_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedPersonal_id'));
		}

		// MedPersonal_sid
		index = base_form.findField('MedStaffFact_sid').getStore().findBy(function(rec) {
			return (rec.get('MedStaffFact_id') == med_staff_fact_sid);
		});

		if ( index >= 0 ) {
			base_form.findField('MedPersonal_sid').setValue(base_form.findField('MedStaffFact_sid').getStore().getAt(index).get('MedPersonal_id'));
		}

		// Вид оплаты
		index = base_form.findField('PayType_id').getStore().findBy(function(rec) {
			return (rec.get('PayType_id') == base_form.findField('PayType_id').getValue());
		});

		if ( index >= 0 ) {
			PayType_SysNick = base_form.findField('PayType_id').getStore().getAt(index).get('PayType_SysNick');
		}

		if ( this.formMode == 'morbus' ) {
			base_form.findField('EvnUslugaStom_pid').setValue(base_form.findField('EvnVizitPLStom_id').getValue());
		}

		params.EvnUslugaStom_setDate = Ext.util.Format.date(base_form.findField('EvnUslugaStom_setDate').getValue(), 'd.m.Y');
		params.EvnUslugaStom_setTime = base_form.findField('EvnUslugaStom_setTime').getValue();
		//params.EvnUslugaStom_disDate = Ext.util.Format.date(base_form.findField('EvnUslugaStom_disDate').getValue(), 'd.m.Y');
		//params.EvnUslugaStom_disTime = base_form.findField('EvnUslugaStom_disTime').getValue();
		params.EvnUslugaStom_Price = evn_usluga_stom_price;
		params.EvnUslugaStom_Summa = Number(evn_usluga_stom_price * base_form.findField('EvnUslugaStom_Kolvo').getValue()).toFixed(2);
		params.UslugaPlace_id = base_form.findField('UslugaPlace_id').getValue();
		params.UslugaCategory_id = base_form.findField('UslugaCategory_id').getValue();
		params.LpuSection_uid = base_form.findField('LpuSection_uid').getValue();
		params.LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
		var me = this;
		if ( me.uslugaPanel.isUslugaComplexPackage() ) {
			var uslugaErr = me.uslugaPanel.validateUslugaSelectedList();
			if (uslugaErr) {
				me.formStatus = 'edit';
				sw.swMsg.alert('Ошибка', uslugaErr);
				return false;
			}
			params.UslugaSelectedList = me.uslugaPanel.getUslugaSelectedList(true);
		}
		params.EvnUslugaStom_IsMes = base_form.findField('EvnUslugaStom_IsMes').getValue();
		params.EvnUslugaStom_IsAllMorbus = base_form.findField('EvnUslugaStom_IsAllMorbus').getValue();
		params.EvnUslugaStom_Kolvo = base_form.findField('EvnUslugaStom_Kolvo').getValue();
		params.EvnUslugaStom_UED = base_form.findField('EvnUslugaStom_UED').getValue();
		params.EvnUslugaStom_UEM = base_form.findField('EvnUslugaStom_UEM').getValue();
		params.EvnUslugaStom_id = base_form.findField('EvnUslugaStom_id').getValue();
		params.EvnUslugaStom_pid = base_form.findField('EvnUslugaStom_pid').getValue();
		params.EvnDiagPLStom_id = base_form.findField('EvnDiagPLStom_id').getValue();
		params.MedPersonal_id = base_form.findField('MedPersonal_id').getValue();
		params.MedPersonal_sid = base_form.findField('MedPersonal_sid').getValue();
		params.MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
		params.MedStaffFact_sid = base_form.findField('MedStaffFact_sid').getValue();
		params.PayType_id = base_form.findField('PayType_id').getValue();
		params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
		params.Person_id = base_form.findField('Person_id').getValue();
		params.Server_id = base_form.findField('Server_id').getValue();
		params.accessType = base_form.findField('accessType').getValue();
		params.UslugaComplexTariff_id = base_form.findField('UslugaComplexTariff_id').getValue();
		params.UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
		params.BlackCariesClass_id = base_form.findField('BlackCariesClass_id').getValue();
		params.LpuDispContract_id = base_form.findField('LpuDispContract_id').getValue();
		params.UslugaMedType_id = base_form.findField('UslugaMedType_id').getValue();
		var onEvnUslugaStomSave = function() {
			if ( options && typeof options.openChildWindow == 'function' && me.action == 'add' ) {
				options.openChildWindow();
			} else {
				var data = {};

				data.evnUslugaData = {
					'accessType': 'edit',
					'EvnClass_SysNick': 'EvnUslugaStom',
					'EvnUsluga_Kolvo': base_form.findField('EvnUslugaStom_Kolvo').getValue(),
					'EvnUsluga_id': base_form.findField('EvnUslugaStom_id').getValue(),
					'EvnUsluga_pid': base_form.findField('EvnUslugaStom_pid').getValue(),
					'EvnUsluga_Price': evn_usluga_stom_price,
					'EvnUsluga_setDate': base_form.findField('EvnUslugaStom_setDate').getValue(),
					'EvnUsluga_Summa': Number(evn_usluga_stom_price * base_form.findField('EvnUslugaStom_Kolvo').getValue()).toFixed(2),
					'PayType_id': base_form.findField('PayType_id').getValue(),
					'PayType_SysNick': PayType_SysNick,
					'Usluga_Code': usluga_complex_code,
					'Usluga_Name': usluga_complex_name,
					'clearKSGField': (!Ext.isEmpty(options.ignoreKSGCheck) && options.ignoreKSGCheck === 1) ? 1 : 0
				};

				me.callback(data);

				if ( options && options.continueInput == true ) {
					base_form.findField('EvnUslugaStom_id').setValue(0);
					base_form.findField('EvnUslugaStom_Kolvo').setValue(1);
					base_form.findField('UslugaComplex_id').clearValue();
					base_form.findField('UslugaComplex_id').fireEvent('change', base_form.findField('UslugaComplex_id'), null);

					base_form.clearInvalid();

					base_form.findField('UslugaComplex_id').focus(true);
				}
				else {
					me.hide();
				}
			}
		};
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();
		params.ignoreParentEvnDateCheck = (!Ext.isEmpty(options.ignoreParentEvnDateCheck) && options.ignoreParentEvnDateCheck === 1) ? 1 : 0;
		params.ignoreKSGCheck = (!Ext.isEmpty(options.ignoreKSGCheck) && options.ignoreKSGCheck === 1) ? 1 : 0;
		// по какой-то причине при base_form.submit не отправляется UslugaComplexTariff_id, хотя он выбран
		Ext.Ajax.request({
			callback: function(options, success, response) {
				me.formStatus = 'edit';
				loadMask.hide();
				log([success, response]);
				if (success) {
					var result = Ext.util.JSON.decode(response.responseText);
					if ( result ) {
						if ( result.Alert_Msg ) {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'yes' ) {
										if (result.Error_Code == 109) {
											options.ignoreParentEvnDateCheck = 1;
										}

										if (result.Error_Code == 110) {
											options.ignoreKSGCheck = 1;
										}

										me.doSave(options);
									}
								},
								icon: Ext.MessageBox.QUESTION,
								msg: result.Alert_Msg,
								title: 'Продолжить сохранение?'
							});
						} else if ( result.EvnUslugaStom_id > 0 ) {
							base_form.findField('EvnUslugaStom_id').setValue(result.EvnUslugaStom_id);
							if (me.parodontogramPanel.isAllowSave()) {
								me.parodontogramPanel.setParam('EvnUslugaStom_id', result.EvnUslugaStom_id);
								me.parodontogramPanel.doSave({
									callback: function(){
										onEvnUslugaStomSave();
									}
								});
							} else {
								onEvnUslugaStomSave();
							}
						}
						else {
							sw.swMsg.alert('Ошибка', !Ext.isEmpty(result.Error_Msg) ? result.Error_Msg : 'При сохранении произошли ошибки [Тип ошибки: 3]');
						}
					} else {
						sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 2]');
					}
					log({
						debug: 'doSave',
						result: result,
						options: options,
						isAllowSaveParodontogram: me.parodontogramPanel.isAllowSave()
					});
				} else {
					sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 3]');
				}
			},
			params: params,
			url: '/?c=EvnUsluga&m=saveEvnUslugaStom'
		});
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.findById('EvnUslugaStomEditForm').getForm();
		var form_fields = new Array(
			 'EvnUslugaStom_Kolvo'
			,'EvnVizitPLStom_id'
			,'EvnUslugaStom_UEM'
			,'LpuSection_uid'
			,'LpuSectionProfile_id'
			,'MedStaffFact_id'
			,'MedStaffFact_sid'
			,'PayType_id'
			,'UslugaCategory_id'
			,'UslugaComplex_id'
			,'UslugaComplexTariff_id'
			,'BlackCariesClass_id',
			'UslugaMedType_id'
		);
		var i, j;

		if ( getRegionNick() == 'perm' ) {
			form_fields.push('EvnUslugaStom_IsMes');
		}

		if ( getRegionNick() != 'perm' ) {
			form_fields.push('EvnUslugaStom_UED');
		}

		if ( this.formMode == 'morbus' ) {
			//form_fields.push('EvnDiagPLStom_id');
			//form_fields.push('EvnUslugaStom_IsAllMorbus');
		}

		for ( i = 0; i < form_fields.length; i++ ) {
			if ( enable ) {
				base_form.findField(form_fields[i]).enable();
			}
			else {
				base_form.findField(form_fields[i]).disable();
			}
		}

		if ( enable ) {
			this.buttons[0].show();
		}
		else {
			this.buttons[0].hide();
		}
	},
	loadMedStaffFactCombo: function (fieldName, options, callback) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		var base_form = this.findById('EvnUslugaStomEditForm').getForm(),
			combo = base_form.findField(fieldName),
			value = combo.getValue(),
			medStaffFactFilters = {
				allowLowLevel: 'yes',
				isStom: !getRegionNick().inlist(['kareliya']),// в Карелии разрешили в арм стоматолога заходить не только стоматологам
				isMidMedPersonal: ('MedStaffFact_sid' == fieldName),
				regionCode: getGlobalOptions().region.number
			};
		combo.clearValue();
		
		if (options.onDate) {
			medStaffFactFilters.onDate = options.onDate;
		}
		if ( typeof options.exactIdList == 'object' ) {
			medStaffFactFilters.exactIdList = options.exactIdList;
		}
		setMedStaffFactGlobalStoreFilter(medStaffFactFilters);
		combo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		if ( combo.getStore().getById(value) ) {
			combo.setValue(value);
			if (callback) {
				callback(combo);
			}
			return true;
		}
		
		var index = -1,
			med_personal_id = base_form.findField('MedPersonal_id').getValue(),
			lpu_section_uid = base_form.findField('LpuSection_uid').getValue();
		if (medStaffFactFilters.isMidMedPersonal) {
			med_personal_id = base_form.findField('MedPersonal_sid').getValue();
		}
		if (medStaffFactFilters.isMidMedPersonal) {
			index = combo.getStore().findBy(function(rec, id) {
				return (rec.get('MedPersonal_id') == med_personal_id);
			});
		} else {
			index = combo.getStore().findBy(function(rec, id) {
				return (rec.get('LpuSection_id') == lpu_section_uid && rec.get('MedPersonal_id') == med_personal_id);
			});
		}
		if ( index >= 0 ) {
			combo.setValue(combo.getStore().getAt(index).get('MedStaffFact_id'));
			if (callback) {
				callback(combo);
			}
			return true;
		}
		
		var params = null;
		switch (true) {
			case (value > 0 && this.action != 'view'):
				params = {};
				params.Lpu_id = getGlobalOptions().lpu_id;
				params.MedStaffFact_id = value;
				break;
			case (value > 0 && this.action == 'view'):
				params = {};
				params.Lpu_id = getGlobalOptions().lpu_id; // base_form.findField('Lpu_uid').getValue() || getGlobalOptions().lpu_id
				params.MedStaffFact_id = value;
				break;
			case (med_personal_id > 0 && false == medStaffFactFilters.isMidMedPersonal && this.action != 'view'):
				params = {};
				params.Lpu_id = getGlobalOptions().lpu_id;
				params.MedPersonal_id = med_personal_id;
				params.LpuSection_id = lpu_section_uid;
				break;
			case (med_personal_id > 0 && medStaffFactFilters.isMidMedPersonal && this.action != 'view'):
				params = {};
				params.Lpu_id = getGlobalOptions().lpu_id;
				params.MedPersonal_id = med_personal_id;
				break;
			/*
			case (getGlobalOptions().medpersonal_id > 0 && this.action == 'add'):
				params = {};
				params.Lpu_id = getGlobalOptions().lpu_id;
				params.MedPersonal_id = getGlobalOptions().medpersonal_id;
				if (false == medStaffFactFilters.isMidMedPersonal) {
					params.LpuSection_id = getGlobalOptions().CurLpuSection_id;
				}
				break;
			*/
		}
		if (params) {
			Ext.Ajax.request({
				params: params,
				success: function(response, options) {
					combo.getStore().loadData(Ext.util.JSON.decode(response.responseText), true);
					if (params.MedStaffFact_id) {
						index = combo.getStore().findBy(function(rec, id) {
							return (rec.get('MedStaffFact_id') == params.MedStaffFact_id);
						});
					} else if (!params.LpuSection_id) {
						index = combo.getStore().findBy(function(rec, id) {
							return (rec.get('MedPersonal_id') == params.MedPersonal_id);
						});
					} else {
						index = combo.getStore().findBy(function(rec, id) {
							return (rec.get('LpuSection_id') == params.LpuSection_id && rec.get('MedPersonal_id') == params.MedPersonal_id);
						});
					}
					if ( index >= 0 ) {
						combo.setValue(combo.getStore().getAt(index).get('MedStaffFact_id'));
					}
					if (callback) {
						callback(combo);
					}
				}.createDelegate(this),
				url: C_MEDPERSONAL_LIST
			});
		}
		return false;
	},
	formStatus: 'edit',
	height: 550,
	id: 'EvnUslugaStomEditWindow',
	initComponent: function() {
		var form = this;

		form.uslugaPanel = new sw.Promed.UslugaSelectPanel({
			id: form.getId() + 'UslugaSelectPanel',
			evnClassSysNick: 'EvnUslugaStom',
			getBaseForm: function()
			{
				if (!this._baseForm) {
					this._baseForm = form.findById('EvnUslugaStomEditForm').getForm();
				}
				return this._baseForm;
			},
			isDisableUem: function()
			{
				return Ext.isEmpty(this.getBaseForm().findField('MedStaffFact_sid').getValue());
			},
			isDisableUed: function()
			{
				return Ext.isEmpty(this.getBaseForm().findField('MedStaffFact_id').getValue());
			},
			getEvnUslugaSummaField: function()
			{
				return this.getBaseForm().findField('EvnUslugaStom_Summa');
			},
			getEvnUslugaUEDField: function()
			{
				return this.getBaseForm().findField('EvnUslugaStom_UED');
			},
			getEvnUslugaUEMField: function()
			{
				return this.getBaseForm().findField('EvnUslugaStom_UEM');
			}
		});

		form.parodontogramPanel = new sw.Promed.ParodontogramPanel({});
		
		form.EvnXmlPanel = new sw.Promed.EvnXmlPanel({
			autoHeight: true,
			border: true,
			collapsible: true,
			loadMask: {},
			id: 'EUStomEF_TemplPanel',
			layout: 'form',
			title: '3. Протокол',
			style: 'margin-bottom: 0.5em;',
			ownerWin: this,
			options: {
				XmlType_id: sw.Promed.EvnXml.EVN_USLUGA_PROTOCOL_TYPE_ID, 
				EvnClass_id: 29 // стомат.услуга
			},
			onAfterLoadData: function(panel){
				var bf = this.findById('EvnUslugaStomEditForm').getForm();
				bf.findField('XmlTemplate_id').setValue(panel.getXmlTemplateId());
				panel.expand();
				this.syncSize();
				this.doLayout();
			}.createDelegate(this),
			onAfterClearViewForm: function(panel){
				var bf = this.findById('EvnUslugaStomEditForm').getForm();
				bf.findField('XmlTemplate_id').setValue(null);
			}.createDelegate(this),
			// определяем метод, который должен создать посещение перед созданием документа с помощью указанного метода
			onBeforeCreate: function (panel, method, params) {
				if (!panel || !method || typeof panel[method] != 'function') {
					return false;
				}
				var base_form = this.findById('EvnUslugaStomEditForm').getForm();
				var evn_id = base_form.findField('EvnUslugaStom_id').getValue();
				if (evn_id && evn_id > 0) {
					// услуга была создана ранее
					// все базовые параметры уже должно быть установлены
					panel[method](params);
				} else {
					this.doSave({
						openChildWindow: function() {
							panel.setBaseParams({
								userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
								UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
								Server_id: base_form.findField('Server_id').getValue(),
								Evn_id: base_form.findField('EvnUslugaStom_id').getValue()
							});
							panel[method](params);
						}.createDelegate(this)
					});
				}
				return true;
			}.createDelegate(this)
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					if (!this.findById('EUStomEF_EvnAggPanel').collapsed) {
						this.findById('EUStomEF_EvnAggGrid').getView().focusRow(0);
						this.findById('EUStomEF_EvnAggGrid').getSelectionModel().selectFirstRow();
					} else {
						this.findById('EvnUslugaStomEditForm').getForm().findField('EvnUslugaStom_Kolvo').focus(true);
					}
				}.createDelegate(this),
				onTabAction: function () {
					if ( !this.buttons[1].hidden ) {
						this.buttons[1].focus();
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EUSTOMEF + 24,
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.doSave({
						continueInput: true
					});
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_EUSTOMEF + 25,
				text: BTN_FRMSAVEANDCONTINUE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.onCancelAction();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					if ( !this.buttons[1].hidden ) {
						this.buttons[1].focus();
					}
					else if ( !this.buttons[0].hidden ) {
						this.buttons[0].focus();
					}
					else if ( !this.findById('EUStomEF_EvnAggPanel').collapsed ) {
						this.findById('EUStomEF_EvnAggGrid').getView().focusRow(0);
						this.findById('EUStomEF_EvnAggGrid').getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				onTabAction: function () {
					if (!this.findById('EUStomEF_EvnUslugaStomPanel').collapsed && this.action != 'view') {
						this.findById('EvnUslugaStomEditForm').getForm().findField('LpuSection_uid').focus(true);
					} else if (!this.findById('EUStomEF_EvnAggPanel').collapsed) {
						this.findById('EUStomEF_EvnAggGrid').getView().focusRow(0);
						this.findById('EUStomEF_EvnAggGrid').getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EUSTOMEF + 26,
				text: BTN_FRMCANCEL
			}],
			items: [ new sw.Promed.PersonInformationPanelShort({
				id: 'EUStomEF_PersonInformationFrame',
				region: 'north'
			}),
			new Ext.form.FormPanel({
				autoScroll: true,
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				frame: false,
				id: 'EvnUslugaStomEditForm',
				labelAlign: 'right',
				labelWidth: 180,
				layout: 'form',
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{ name: 'accessType' },
					{ name: 'XmlTemplate_id' },
					{ name: 'EvnUslugaStom_id' },
					{ name: 'EvnUslugaStom_rid' },
					{ name: 'EvnUslugaStom_pid' },
					{ name: 'EvnDiagPLStom_id' },
					{ name: 'Person_id' },
					{ name: 'PersonEvn_id' },
					{ name: 'Server_id' },
					{ name: 'EvnUslugaStom_setDate' },
					{ name: 'EvnUslugaStom_setTime' },
					//{ name: 'EvnUslugaStom_disDate' },
					//{ name: 'EvnUslugaStom_disTime' },
					{ name: 'LpuSection_uid' },
					{ name: 'LpuSectionProfile_id' },
					{ name: 'MedStaffFact_id' },
					{ name: 'MedPersonal_id' },
					{ name: 'MedPersonal_sid' },
					{ name: 'UslugaComplex_id' },
					{ name: 'BlackCariesClass_id' },
					{ name: 'LpuDispContract_id' },
					{ name: 'UslugaComplexTariff_id' },
					{ name: 'EvnUslugaStom_UED' },
					{ name: 'EvnUslugaStom_UEM' },
					{ name: 'PayType_id' },
					{ name: 'EvnUslugaStom_Kolvo' },
					{ name: 'EvnUslugaStom_Summa' },
					{ name: 'EvnUslugaStom_IsMes' },
					{ name: 'EvnUslugaStom_IsAllMorbus' },
					{ name: 'UslugaMedType_id' }
				]),
				region: 'center',
				url: '/?c=EvnUsluga&m=saveEvnUslugaStom',
				items: [{
					name: 'accessType',
					value: '',
					xtype: 'hidden'
				}, {
					name: 'XmlTemplate_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnUslugaStom_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnUslugaStom_rid',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnUslugaStom_pid',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'MedPersonal_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'MedPersonal_sid',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'Person_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'PersonEvn_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					value: -1,
					xtype: 'hidden'
				},
				new sw.Promed.Panel({
					autoHeight: true,
					// bodyStyle: 'padding: 0.5em;',
					border: true,
					collapsible: true,
					id: 'EUStomEF_EvnUslugaStomPanel',
					layout: 'form',
					style: 'margin-bottom: 0.5em;',
					title: '1. Услуга',
					items: [{
						allowBlank: false,
						displayField: 'Evn_Title',
						editable: false,
						enableKeyEvents: true,
						fieldLabel: 'Посещение',
						hiddenName: 'EvnVizitPLStom_id',
						lastQuery: '',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var index = combo.getStore().findBy(function(rec) {
									return (rec.get(combo.valueField) == newValue);
								});
								combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
							},
							'select': function(combo, record, idx) {
								var
									base_form = form.findById('EvnUslugaStomEditForm').getForm(),
									index;

								if ( typeof record == 'object' ) {
									base_form.findField('EvnUslugaStom_setDate').setValue(record.get('Evn_setDate'));
									base_form.findField('EvnUslugaStom_setTime').setValue(record.get('Evn_setTime'));
									if (getRegionNick() != 'kz') base_form.findField('PayType_id').setValue(record.get('PayType_id'));

									base_form.findField('EvnUslugaStom_setDate').fireEvent('change', base_form.findField('EvnUslugaStom_setDate'), base_form.findField('EvnUslugaStom_setDate').getValue());

									index = base_form.findField('LpuSection_uid').getStore().findBy(function(rec) {
										return (rec.get('LpuSection_id') == record.get('LpuSection_id'));
									});

									if ( index >= 0 ) {
										base_form.findField('LpuSection_uid').setValue(record.get('LpuSection_id'));
									}

									index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
										return (rec.get('MedStaffFact_id') == record.get('MedStaffFact_id'));
									});

									if ( index >= 0 ) {
										base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
									}
								}

								if ( !Ext.isEmpty(base_form.findField('LpuSection_uid').getValue()) && getRegionNick() != 'perm' ) {
									base_form.findField('LpuSection_uid').disable();
								}
								else if ( form.action != 'view' ) {
									base_form.findField('LpuSection_uid').enable();
								}

								base_form.findField('LpuSection_uid').fireEvent('change', base_form.findField('LpuSection_uid'), base_form.findField('LpuSection_uid').getValue());

								base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_uid').getValue();

								base_form.findField('PayType_id').fireEvent('change', base_form.findField('PayType_id'), base_form.findField('PayType_id').getValue());
								base_form.findField('PayType_id').fireEvent('select', base_form.findField('PayType_id'), base_form.findField('PayType_id').getStore().getById(base_form.findField('PayType_id').getValue()));
							}
						},
						listWidth: 600,
						mode: 'local',
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'Evn_id', type: 'int' },
								{ name: 'Evn_pid', type: 'int' },
								{ name: 'Evn_rid', type: 'int' },
								{ name: 'LpuSection_id', type: 'int' },
								{ name: 'MedStaffFact_id', type: 'int' },
								{ name: 'MedPersonal_id', type: 'int' },
								{ name: 'PayType_id', type: 'int' },
								{ name: 'Evn_Title', type: 'string' },
								{ name: 'Evn_setDate', type: 'date', dateFormat: 'd.m.Y' },
								{ name: 'Evn_setTime', type: 'string' },
								{ name: 'LpuSection_Name', type: 'string' },
								{ name: 'MedPersonal_Fio', type: 'string' }
							],
							key: 'Evn_id',
							sortInfo: {
								field: 'Evn_setDate'
							},
							url: '/?c=EvnVizit&m=loadEvnVizitCombo'
						}),
						tabIndex: TABINDEX_EUSTOMEF + 1,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">', '{Evn_Title}&nbsp;', '</div></tpl>'
						),
						triggerAction: 'all',
						valueField: 'Evn_id',
						width: 500,
						xtype: 'swbaselocalcombo'
					}, {
						allowBlank: false,
						disabled: true,
						displayField: 'EvnDiagPLStom_Title',
						editable: false,
						enableKeyEvents: true,
						fieldLabel: 'Заболевание',
						hiddenName: 'EvnDiagPLStom_id',
						lastQuery: '',
						listWidth: 600,
						mode: 'local',
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'EvnDiagPLStom_id', type: 'int' },
								{ name: 'EvnDiagPLStom_Title', type: 'string' },
								{ name: 'EvnDiagPLStom_setDate', type: 'date', dateFormat: 'd.m.Y' },
								{ name: 'EvnDiagPLStom_disDate', type: 'date', dateFormat: 'd.m.Y' },
								{ name: 'Diag_Code', type: 'string' },
								{ name: 'Mes_Code', type: 'string' },
								{ name: 'Tooth_Code', type: 'int' }
							],
							key: 'EvnDiagPLStom_id',
							sortInfo: {
								field: 'EvnDiagPLStom_setDate'
							},
							url: '/?c=EvnDiagPLStom&m=loadEvnDiagPLStomCombo'
						}),
						tabIndex: TABINDEX_EUSTOMEF + 2,
						tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">', '{EvnDiagPLStom_Title}&nbsp;', '</div></tpl>'),
						triggerAction: 'all',
						valueField: 'EvnDiagPLStom_id',
						width: 500,
						xtype: 'swbaselocalcombo'
					}, {
						disabled: true,
						fieldLabel: 'Для всех заболеваний',
						hiddenName: 'EvnUslugaStom_IsAllMorbus',
						tabIndex: TABINDEX_EUSTOMEF + 3,
						width: 100,
						xtype: 'swyesnocombo'
					}, {
						layout: 'column',
						border: false,
						items: [{
							border: false,
							layout: 'form',
							items: [{
								allowBlank: false,
								disabled: true,
								fieldLabel: 'Дата начала выполнения',
								format: 'd.m.Y',
								id: 'EUStomEF_EvnUslugaStom_setDate',
								listeners: {
									'change': function(field, newValue, oldValue) {
										if ( blockedDateAfterPersonDeath('personpanelid', 'EUStomEF_PersonInformationFrame', field, newValue, oldValue) )
											return false;

										var base_form = this.findById('EvnUslugaStomEditForm').getForm();

										var lpu_section_id = base_form.findField('LpuSection_uid').getValue();

										base_form.findField('LpuSection_uid').clearValue();

										var lpuSectionFilters = {
											allowLowLevel: 'yes',
											isStom: true,
											regionCode: getGlobalOptions().region.number
										};

										if (
											!Ext.isEmpty(base_form.findField('LpuDispContract_id').getValue())
											&& !Ext.isEmpty(newValue)
											&& !(
												base_form.findField('LpuDispContract_id').getFieldValue('LpuDispContract_setDate') <= newValue
												&& Ext.isEmpty(base_form.findField('LpuDispContract_id').getFieldValue('LpuDispContract_disDate')) || base_form.findField('LpuDispContract_id').getFieldValue('LpuDispContract_disDate') >= newValue
											)
										) {
											base_form.findField('LpuDispContract_id').clearValue();
										}
										base_form.findField('LpuDispContract_id').getStore().baseParams.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
										base_form.findField('LpuDispContract_id').lastQuery = 'This query sample that is not will never appear';
										base_form.findField('LpuDispContract_id').getStore().baseParams.query = '';

										if ( !Ext.isEmpty(newValue) ) {
											lpuSectionFilters.onDate = Ext.util.Format.date(newValue, 'd.m.Y');

											if ( this.action == 'add' || this.action == 'edit' ) {
												base_form.findField('UslugaComplex_id').clearValue();
												base_form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';
												base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = (typeof newValue == 'object' ? Ext.util.Format.date(newValue, 'd.m.Y') : newValue);
												base_form.findField('UslugaComplex_id').getStore().removeAll();
											}

											if (getRegionNick() == 'ekb') {
												var xdate = new Date(2015, 0, 1);
												if (!Ext.isEmpty(base_form.findField('EvnUslugaStom_setDate').getValue()) && base_form.findField('EvnUslugaStom_setDate').getValue() >= xdate) {
													base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([303,304]);
												} else {
													base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([300, 301]);
												}
											}
										}

										this.parodontogramPanel.applyParams(
											base_form.findField('Person_id').getValue(),
											base_form.findField('EvnUslugaStom_id').getValue(),
											field.getRawValue()
										);

										setLpuSectionGlobalStoreFilter(lpuSectionFilters);
										base_form.findField('LpuSection_uid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
										if ( base_form.findField('LpuSection_uid').getStore().getById(lpu_section_id) ) {
											base_form.findField('LpuSection_uid').setValue(lpu_section_id);
										}
										
										this.loadMedStaffFactCombo('MedStaffFact_id', { onDate: (lpuSectionFilters.onDate||null) }, function() {
											this.loadMedStaffFactCombo('MedStaffFact_sid', { onDate: (lpuSectionFilters.onDate||null) }, function() {});
										}.createDelegate(this));

										var uslugacategory_combo = base_form.findField('UslugaCategory_id');
										if (newValue && getRegionNick() == 'perm') {
											if (newValue < new Date('2014-12-31')) {
												uslugacategory_combo.setFieldValue('UslugaCategory_SysNick', 'tfoms');
											} else {
												uslugacategory_combo.setFieldValue('UslugaCategory_SysNick', 'gost2011');
											}
											uslugacategory_combo.fireEvent('select', uslugacategory_combo, uslugacategory_combo.getStore().getById(uslugacategory_combo.getValue()));
										}
										
										base_form.findField('LpuSectionProfile_id').onChangeDateField(field, newValue);
									}.createDelegate(this)
								},
								name: 'EvnUslugaStom_setDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								tabIndex: TABINDEX_EUSTOMEF + 4,
								width: 100,
								xtype: 'swdatefield'
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 50,
							items: [{
								disabled: true,
								fieldLabel: 'Время',
								listeners: {
									'keydown': function (inp, e) {
										if ( e.getKey() == Ext.EventObject.F4 ) {
											e.stopEvent();
											inp.onTriggerClick();
										}
									}
								},
								name: 'EvnUslugaStom_setTime',
								onTriggerClick: function() {
									var base_form = this.findById('EvnUslugaStomEditForm').getForm();

									var time_field = base_form.findField('EvnUslugaStom_setTime');

									if ( time_field.disabled ) {
										return false;
									}

									setCurrentDateTime({
										dateField: base_form.findField('EvnUslugaStom_setDate'),
										loadMask: true,
										setDate: true,
										setDateMaxValue: true,
										setDateMinValue: false,
										setTime: true,
										timeField: time_field,
										windowId: this.id
									});
								}.createDelegate(this),
								plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
								tabIndex: TABINDEX_EUSTOMEF + 5,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield'
							}]
						}/*, {
							layout: 'form',
							style: 'padding-left: 45px',
							border: false,
							items: [{
								xtype: 'button',
								id: 'EUStomEF_ToggleVisibleDisDTBtn',
								text: 'Уточнить период выполнения',
								handler: function() {
									this.toggleVisibleDisDTPanel();
								}.createDelegate(this)
							}]
						}*/]
					}, /*{
						layout: 'column',
						id: 'EUStomEF_EvnUslugaDisDTPanel',
						border: false,
						items: [{
							border: false,
							layout: 'form',
							labelWidth: 180,
							items: [{
								fieldLabel: 'Дата окончания выполнения',
								format: 'd.m.Y',
								name: 'EvnUslugaStom_disDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								tabIndex: TABINDEX_EUSTOMEF + 6,
								width: 100,
								xtype: 'swdatefield'
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 50,
							items: [{
								fieldLabel: 'Время',
								listeners: {
									'keydown': function (inp, e) {
										if ( e.getKey() == Ext.EventObject.F4 ) {
											e.stopEvent();
											inp.onTriggerClick();
										}
									}
								},
								name: 'EvnUslugaStom_disTime',
								onTriggerClick: function() {
									var base_form = this.findById('EvnUslugaStomEditForm').getForm();

									var time_field = base_form.findField('EvnUslugaStom_disTime');

									if ( time_field.disabled ) {
										return false;
									}

									setCurrentDateTime({
										dateField: base_form.findField('EvnUslugaStom_disDate'),
										loadMask: true,
										setDate: true,
										setDateMaxValue: false,
										setDateMinValue: false,
										setTime: true,
										timeField: time_field,
										windowId: this.id
									});
								}.createDelegate(this),
								plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
								tabIndex: TABINDEX_EUSTOMEF + 7,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield'
							}]
						}, {
							layout: 'form',
							border: false,
							items: [{
								xtype: 'button',
								id: 'EUStomEF_DTCopyBtn',
								text: '=',
								handler: function() {
									var base_form = this.findById('EvnUslugaStomEditForm').getForm();

									base_form.findField('EvnUslugaStom_disDate').setValue(base_form.findField('EvnUslugaStom_setDate').getValue());
									base_form.findField('EvnUslugaStom_disTime').setValue(base_form.findField('EvnUslugaStom_setTime').getValue());
								}.createDelegate(this)
							}]
						}]
					},*/ {
						autoHeight: true,
						style: 'padding: 2px 0px 0px 0px;',
						xtype: 'fieldset',
						items: [{
							allowBlank: false,
							disabled: true,
							hiddenName: 'UslugaPlace_id',
							tabIndex: TABINDEX_EUSTOMEF + 8,
							value: 1,
							width: 500,
							xtype: 'swuslugaplacecombo'
						}, {
							allowBlank: false,
							dateFieldId: 'EUStomEF_EvnUslugaStom_setDate',
							enableOutOfDateValidation: true,
							hiddenName: 'LpuSection_uid',
							id: 'EUStomEF_LpuSectionCombo',
							lastQuery: '',
							linkedElements: [
								'EUStomEF_MedPersonalCombo'
							],
							tabIndex: TABINDEX_EUSTOMEF + 9,
							width: 500,
							xtype: 'swlpusectionglobalcombo'
						}, {
							hiddenName: 'LpuSectionProfile_id',
							hidden: true,
							isStom: true,
							lastQuery: '',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if ( getRegionNick() != 'penza' ) {
										return false;
									}

									var base_form = this.findById('EvnUslugaStomEditForm').getForm();

									base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSectionProfile_id = (!Ext.isEmpty(newValue) ? newValue : '');
									base_form.findField('UslugaComplex_id').lastQuery = 'The string than never be... bla-bla';
								}.createDelegate(this)
							},
							onTrigger2Click: function() {
								if ( this.disabled ) {
									return false;
								}

								this.clearValue();
								this.fireEvent('change', this, this.getValue());
							},
							tabIndex: TABINDEX_EUSTOMEF + 10,
							width: 500,
							xtype: 'swlpusectionprofilewithfedcombo'
						}]
					}, {
						autoHeight: true,
						style: 'padding: 2px 0px 0px 0px;',
						title: 'Врач, выполнивший услугу',
						xtype: 'fieldset',
						items: [{
							allowBlank: false,
							dateFieldId: 'EUStomEF_EvnUslugaStom_setDate',
							enableOutOfDateValidation: true,
							fieldLabel: 'Код и ФИО врача',
							hiddenName: 'MedStaffFact_id',
							id: 'EUStomEF_MedPersonalCombo',
							lastQuery: '',
							listWidth: 750,
							parentElementId: 'EUStomEF_LpuSectionCombo',
							tabIndex: TABINDEX_EUSTOMEF + 11,
							width: 500,
							xtype: 'swmedstafffactglobalcombo'
						}, {
							border: false,
							hidden: (getGlobalOptions().region && getGlobalOptions().region.nick == 'perm'),
							layout: 'form',
							items: [{
								allowBlank: true,
								fieldLabel: 'Сред. м/персонал',
								hiddenName: 'MedStaffFact_sid',
								id: 'EUStomEF_MidMedPersonalCombo',
								lastQuery: '',
								listWidth: 750,
								tabIndex: TABINDEX_EUSTOMEF + 12,
								width: 500,
								xtype: 'swmedstafffactglobalcombo'
							}]
						}]
					}, {
						allowBlank: false,
						hiddenName: 'PayType_id',
						listeners: {
							'select': function (combo, record) {
								var base_form = this.findById('EvnUslugaStomEditForm').getForm();
								var usluga_category_combo = base_form.findField('UslugaCategory_id');

								if (getRegionNick() == 'buryatiya') {
									usluga_category_combo.lastQuery = "";
									usluga_category_combo.getStore().clearFilter();
									if (record && record.get('PayType_SysNick') == 'oms'){
										usluga_category_combo.setFieldValue('UslugaCategory_SysNick', 'tfoms');
										usluga_category_combo.fireEvent('select', usluga_category_combo, usluga_category_combo.getStore().getAt(usluga_category_combo.getStore().findBy(function(rec) {
											return (rec.get('UslugaCategory_SysNick') == 'tfoms');
										})));
									} else {
										usluga_category_combo.clearValue();
										usluga_category_combo.fireEvent('select', usluga_category_combo, null);
									}
								}
							}.createDelegate(this),
							'change': function (combo, newValue, oldValue) {
								if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'kareliya' ) {
									var base_form = this.findById('EvnUslugaStomEditForm').getForm();

									base_form.findField('UslugaComplex_id').setPayType(newValue);

									if ( this.action == 'add' || this.action == 'edit' ) {
										base_form.findField('UslugaComplex_id').clearValue();
										base_form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';
										base_form.findField('UslugaComplex_id').getStore().removeAll();
									}
								}

								this.loadUslugaComplexTariffCombo();

								return true;
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EUSTOMEF + 13,
						width: 250,
						fieldLabel: getRegionNick() == 'kz' ? 'Источник финансирования' : 'Вид оплаты',
						xtype: 'swpaytypecombo'
					}, {
						allowBlank: false,
						fieldLabel: 'Категория услуги',
						hiddenName: 'UslugaCategory_id',
						isStom: true,
						listeners: {
							'select': function (combo, record) {
								var base_form = this.findById('EvnUslugaStomEditForm').getForm();

								base_form.findField('UslugaComplex_id').clearValue();
								base_form.findField('UslugaComplex_id').lastQuery = '';
								base_form.findField('UslugaComplex_id').getStore().removeAll();
								base_form.findField('UslugaComplex_id').getStore().baseParams.query = '';

								base_form.findField('UslugaComplex_id').setLpuLevelCode(0);

								if ( !record ) {
									base_form.findField('UslugaComplex_id').setUslugaCategoryList();
									return false;
								}

								base_form.findField('UslugaComplex_id').setUslugaCategoryList([ record.get('UslugaCategory_SysNick') ]);
								base_form.findField('UslugaComplex_id').fireEvent('change', base_form.findField('UslugaComplex_id'), null);

								return true;
							}.createDelegate(this)
						},
						listWidth: 400,
						tabIndex: TABINDEX_EUSTOMEF + 14,
						width: 250,
						xtype: 'swuslugacategorycombo'
					}, {
						border: false,
						layout: 'form',
						hidden: (getRegionNick() != 'perm'),
						items: [{
							fieldLabel: 'Услуги по МЭС',
							hiddenName: 'EvnUslugaStom_IsMes',
							listeners: {
								'change': function (combo, newValue, oldValue) {
									var index = combo.getStore().findBy(function(rec) {
										return (rec.get('YesNo_id') == newValue);
									});

									combo.fireEvent('select', combo, combo.getStore().getAt(index));

									return true;
								}.createDelegate(this),
								'select': function (combo, record) {
									var base_form = this.findById('EvnUslugaStomEditForm').getForm();

									if ( record && record.get('YesNo_Code') == 1 && !Ext.isEmpty(this.Mes_id) ) {
										if ( this.action == 'add' || this.action == 'edit' ) {
											base_form.findField('UslugaComplex_id').clearValue();
											base_form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';
											base_form.findField('UslugaComplex_id').getStore().removeAll();
										}

										base_form.findField('UslugaComplex_id').getStore().baseParams.Mes_id = this.Mes_id;
									}
									else {
										base_form.findField('UslugaComplex_id').getStore().baseParams.Mes_id = null;
									}

									return true;
								}.createDelegate(this)
							},
							tabIndex: TABINDEX_EUSTOMEF + 15,
							width: 100,
							xtype: 'swyesnocombo'
						}]
					}, {
						allowBlank: false,
						fieldLabel: 'Услуга',
						hiddenName: 'UslugaComplex_id',
						to: 'EvnUslugaStom',
						id: 'EUStomEF_UslugaComplexCombo',
						listeners: {
							'change': function (combo, newValue, oldValue) {
								var base_form = this.findById('EvnUslugaStomEditForm').getForm();

								if ( base_form.findField('UslugaComplexTariff_id').params.UslugaComplex_id != newValue ) {
									var index = combo.getStore().findBy(function(rec) {
										return (rec.get(combo.valueField) == newValue);
									});

									combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
								}

								return true;
							}.createDelegate(this)
						},
						listWidth: 600,
						tabIndex: TABINDEX_EUSTOMEF + 16,
						width: 500,
						xtype: 'swuslugacomplexnewcombo'
					}, {
						comboSubject: 'UslugaMedType',
						enableKeyEvents: true,
						hidden: getRegionNick() !== 'kz',
						fieldLabel: langs('Вид услуги'),
						hiddenName: 'UslugaMedType_id',
						allowBlank: getRegionNick() !== 'kz',
						lastQuery: '',
						tabIndex: TABINDEX_EUSTOMEF + 17,
						typeCode: 'int',
						width: 450,
						xtype: 'swcommonsprcombo'
					}, {
						comboSubject: 'BlackCariesClass',
						fieldLabel: 'Класс по Блэку',
						hiddenName: 'BlackCariesClass_id',
						listWidth: 600,
						tabIndex: TABINDEX_EUSTOMEF + 17,
						width: 300,
						xtype: 'swcommonsprcombo'
					}, {
						fieldLabel: 'По договору',
						width: 250,
						listWidth: 700,
						hiddenName: 'LpuDispContract_id',
						tabIndex: TABINDEX_EUSTOMEF + 18,
						xtype: 'swlpudispcontractcombo'
					}, {
						allowBlank: true,
						allowLoadMask: true,
						hiddenName: 'UslugaComplexTariff_id',
						isStom: true,
						listeners: {
							'change': function (combo, newValue, oldValue) {
								var index = combo.getStore().findBy(function(rec) {
									return (rec.get(combo.valueField) == newValue);
								});

								combo.fireEvent('select', combo, combo.getStore().getAt(index));

								return true;
							}.createDelegate(this),
							'select': function (combo, record) {
								var base_form = this.findById('EvnUslugaStomEditForm').getForm();

								if ( record ) {
									if ( !Ext.isEmpty(record.get(combo.valueField)) ) {
										combo.setRawValue(record.get('UslugaComplexTariff_Code') + ". " + record.get('UslugaComplexTariff_Name'));
									}

									if ( this.action != 'view' ) {
										//base_form.findField('EvnUslugaStom_UED').disable();
										//base_form.findField('EvnUslugaStom_UEM').disable();
									}

									if ( !Ext.isEmpty(base_form.findField('MedStaffFact_id').getValue()) ) {
										base_form.findField('EvnUslugaStom_UED').setValue(record.get('UslugaComplexTariff_UED'));
									}
									else {
										base_form.findField('EvnUslugaStom_UED').setValue('');
									}

									if ( !Ext.isEmpty(base_form.findField('MedStaffFact_sid').getValue()) ) {
										base_form.findField('EvnUslugaStom_UEM').setValue(record.get('UslugaComplexTariff_UEM'));
									}
									else {
										base_form.findField('EvnUslugaStom_UEM').setValue('');
									}
								}
								else {
									base_form.findField('EvnUslugaStom_UED').setValue('');
									base_form.findField('EvnUslugaStom_UEM').setValue('');

									if ( this.action != 'view' ) {
										//base_form.findField('EvnUslugaStom_UED').enable();
										//base_form.findField('EvnUslugaStom_UEM').enable();
									}
								}

								base_form.findField('EvnUslugaStom_Kolvo').fireEvent('change', base_form.findField('EvnUslugaStom_Kolvo'), base_form.findField('EvnUslugaStom_Kolvo').getValue());

								return true;
							}.createDelegate(this)
						},
						listWidth: 600,
						loadMaskArea: this,
						tabIndex: TABINDEX_EUSTOMEF + 19,
						width: 500,
						isStom: true,
						xtype: 'swuslugacomplextariffcombo'
					}, {
						//allowBlank: false,
						allowDecimals: true,
						allowNegative: false,
						fieldLabel: 'УЕТ врача',
						disabled: true,
						listeners: {
							'change': function(field, newValue, oldValue) {
								var base_form = this.findById('EvnUslugaStomEditForm').getForm();
								base_form.findField('EvnUslugaStom_Kolvo').fireEvent('change', base_form.findField('EvnUslugaStom_Kolvo'), base_form.findField('EvnUslugaStom_Kolvo').getValue());
							}.createDelegate(this)
						},
						maxValue: sw.Promed.EvnUslugaStom.getMaxUetValue(),
						minValue: sw.Promed.EvnUslugaStom.getMinUetValue(),
						name: 'EvnUslugaStom_UED',
						tabIndex: TABINDEX_EUSTOMEF + 20,
						width: 100,
						xtype: 'numberfield'
					}, {
						border: false,
						hidden: (getGlobalOptions().region && getGlobalOptions().region.nick == 'perm'),
						layout: 'form',
						items: [{
							allowBlank: true,
							allowDecimals: true,
							allowNegative: false,
							fieldLabel: 'УЕТ сред. м/п',
							listeners: {
								'change': function(field, newValue, oldValue) {
									var base_form = this.findById('EvnUslugaStomEditForm').getForm();
									base_form.findField('EvnUslugaStom_Kolvo').fireEvent('change', base_form.findField('EvnUslugaStom_Kolvo'), base_form.findField('EvnUslugaStom_Kolvo').getValue());
								}.createDelegate(this)
							},
							maxValue: sw.Promed.EvnUslugaStom.getMaxUetValue(),
							minValue: sw.Promed.EvnUslugaStom.getMinUetValue(),
							name: 'EvnUslugaStom_UEM',
							tabIndex: TABINDEX_EUSTOMEF + 21,
							width: 100,
							xtype: 'numberfield'
						}]
					}, {
						allowBlank: false,
						allowDecimals: false,
						allowNegative: false,
						enableKeyEvents: true,
						fieldLabel: 'Количество',
						listeners: {
							'change': function(field, newValue, oldValue) {
								var base_form = this.findById('EvnUslugaStomEditForm').getForm();

								base_form.findField('EvnUslugaStom_Summa').setValue('');

								if ( !Ext.isEmpty(newValue) ) {
									var evn_usluga_stom_price = 0;

									if ( !Ext.isEmpty(base_form.findField('EvnUslugaStom_UED').getValue()) ) {
										evn_usluga_stom_price = evn_usluga_stom_price + Number(base_form.findField('EvnUslugaStom_UED').getValue());
									}

									if ( !Ext.isEmpty(base_form.findField('EvnUslugaStom_UEM').getValue()) ) {
										evn_usluga_stom_price = evn_usluga_stom_price + Number(base_form.findField('EvnUslugaStom_UEM').getValue());
									}

									if ( !Ext.isEmpty(evn_usluga_stom_price) ) {
										var multiple = Math.round(evn_usluga_stom_price * newValue * 100) / 100;
										base_form.findField('EvnUslugaStom_Summa').setValue(multiple);
									}
								}
							}.createDelegate(this),
							'keydown': function (inp, e) {
								if ( e.shiftKey == false && e.getKey() == Ext.EventObject.TAB && !this.findById('EUStomEF_EvnAggPanel').collapsed ) {
									e.stopEvent();

									this.findById('EUStomEF_EvnAggGrid').getView().focusRow(0);
									this.findById('EUStomEF_EvnAggGrid').getSelectionModel().selectFirstRow();
								}
							}.createDelegate(this)
						},
						name: 'EvnUslugaStom_Kolvo',
						tabIndex: TABINDEX_EUSTOMEF + 22,
						width: 100,
						xtype: 'numberfield'
					}, {
						allowDecimals: true,
						allowNegative: false,
						disabled: true,
						fieldLabel: 'Сумма (УЕТ)',
						name: 'EvnUslugaStom_Summa',
						tabIndex: TABINDEX_EUSTOMEF + 23,
						width: 100,
						xtype: 'numberfield'
					}]
				}),
				new sw.Promed.Panel({
					collapsible: true,
					autoHeight: true,
					id: 'EUStomEF_Parodontogram',
					style: 'padding: 2px 0px 0px 0px; margin-bottom: 0.5em;',
					title: 'Пародонтограмма',
					layout:'fit',
					items: [form.parodontogramPanel],
					listeners: {
						'expand': function(panel) {
							form.parodontogramPanel.doLoad();
							panel.doLayout();
						}
					}
				}),
				form.uslugaPanel,
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 200,
					id: 'EUStomEF_EvnAggPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if (panel.isLoaded === false) {
								panel.isLoaded = true;
								panel.findById('EUStomEF_EvnAggGrid').getStore().load({
									params: {
										EvnAgg_pid: this.findById('EvnUslugaStomEditForm').getForm().findField('EvnUslugaStom_id').getValue()
									}
								});
							}
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: '2. Осложнения',
					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'AggType_Name',
							header: 'Вид осложнения',
							hidden: false,
							id: 'autoexpand',
							sortable: true
						}, {
							dataIndex: 'AggWhen_Name',
							header: 'Контекст осложнения',
							hidden: false,
							resizable: false,
							sortable: true,
							width: 200
						}, {
							dataIndex: 'EvnAgg_setDate',
							header: 'Дата осложнения',
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 130
						}],
						frame: false,
						id: 'EUStomEF_EvnAggGrid',
						keys: [{
							key: [
								Ext.EventObject.DELETE,
								Ext.EventObject.END,
								Ext.EventObject.ENTER,
								Ext.EventObject.F3,
								Ext.EventObject.F4,
								Ext.EventObject.HOME,
								Ext.EventObject.INSERT,
								Ext.EventObject.PAGE_DOWN,
								Ext.EventObject.PAGE_UP,
								Ext.EventObject.TAB
							],
							fn: function(inp, e) {
								e.stopEvent();
								if (e.browserEvent.stopPropagation)
									e.browserEvent.stopPropagation(); else
									e.browserEvent.cancelBubble = true;
								if (e.browserEvent.preventDefault)
									e.browserEvent.preventDefault(); else
									e.browserEvent.returnValue = false;
								e.returnValue = false;
								if (Ext.isIE) {
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}
								var grid = this.findById('EUStomEF_EvnAggGrid');
								switch (e.getKey()) {
									case Ext.EventObject.DELETE:
										this.deleteEvent('EvnAgg');
										break;
									case Ext.EventObject.END:
										GridEnd(grid);
										break;
									case Ext.EventObject.ENTER:
									case Ext.EventObject.F3:
									case Ext.EventObject.F4:
									case Ext.EventObject.INSERT:
										if (!grid.getSelectionModel().getSelected()) {
											return false;
										}
										var action = 'add';
										if (e.getKey() == Ext.EventObject.F3) {
											action = 'view';
										} else if (e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER) {
											action = 'edit';
										}
										this.openEvnAggEditWindow(action);
										break;
									case Ext.EventObject.HOME:
										GridHome(grid);
										break;
									case Ext.EventObject.PAGE_DOWN:
										GridPageDown(grid);
										break;
									case Ext.EventObject.PAGE_UP:
										GridPageUp(grid);
										break;
									case Ext.EventObject.TAB:
										var base_form = this.findById('EvnUslugaStomEditForm').getForm();
										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());
										if (e.shiftKey == false) {
											if (this.action == 'view') {
												this.buttons[this.buttons.length - 1].focus();
											} else {
												this.buttons[0].focus();
											}
										} else {
											if (!this.findById('EUStomEF_EvnUslugaStomPanel').collapsed && this.action != 'view') {
												base_form.findField('EvnUslugaStom_Kolvo').focus(true);
											} else {
												this.buttons[this.buttons.length - 1].focus();
											}
										}
										break;
								}
							}.createDelegate(this),
							scope: this,
							stopEvent: true
						}],
						listeners: {
							'rowdblclick': function(grid, number, obj) {
								this.openEvnAggEditWindow('edit');
							}.createDelegate(this)
						},
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function(sm, rowIndex, record) {
									var evn_agg_id = null;
									var selected_record = sm.getSelected();
									var toolbar = this.findById('EUStomEF_EvnAggGrid').getTopToolbar();
									if (selected_record) {
										evn_agg_id = selected_record.get('EvnAgg_id');
									}
									toolbar.items.items[1].disable();
									toolbar.items.items[3].disable();
									if (evn_agg_id) {
										toolbar.items.items[2].enable();
										if (this.action != 'view') {
											toolbar.items.items[1].enable();
											toolbar.items.items[3].enable();
										}
									} else {
										toolbar.items.items[2].disable();
									}
								}.createDelegate(this)
							}
						}),
						stripeRows: true,
						store: new Ext.data.Store({
							autoLoad: false,
							listeners: {
								'load': function(store, records, index) {
									if (store.getCount() == 0) {
										LoadEmptyRow(this.findById('EUStomEF_EvnAggGrid'));
									}
									// this.findById('EUStomEF_EvnAggGrid').getView().focusRow(0);
									// this.findById('EUStomEF_EvnAggGrid').getSelectionModel().selectFirstRow();
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'EvnAgg_id'
							}, [{
								mapping: 'EvnAgg_id',
								name: 'EvnAgg_id',
								type: 'int'
							}, {
								mapping: 'EvnAgg_pid',
								name: 'EvnAgg_pid',
								type: 'int'
							}, {
								mapping: 'Person_id',
								name: 'Person_id',
								type: 'int'
							}, {
								mapping: 'PersonEvn_id',
								name: 'PersonEvn_id',
								type: 'int'
							}, {
								mapping: 'Server_id',
								name: 'Server_id',
								type: 'int'
							}, {
								mapping: 'AggType_id',
								name: 'AggType_id',
								type: 'int'
							}, {
								mapping: 'AggWhen_id',
								name: 'AggWhen_id',
								type: 'int'
							}, {
								mapping: 'AggType_Name',
								name: 'AggType_Name',
								type: 'string'
							}, {
								mapping: 'AggWhen_Name',
								name: 'AggWhen_Name',
								type: 'string'
							}, {
								dateFormat: 'd.m.Y',
								mapping: 'EvnAgg_setDate',
								name: 'EvnAgg_setDate',
								type: 'date'
							}, {
								mapping: 'EvnAgg_setTime',
								name: 'EvnAgg_setTime',
								type: 'string'
							}]),
							url: '/?c=EvnAgg&m=loadEvnAggGrid'
						}),
						tbar: new sw.Promed.Toolbar({
							buttons: [{
								handler: function() {
									this.openEvnAggEditWindow('add');
								}.createDelegate(this),
								iconCls: 'add16',
								text: BTN_GRIDADD
							}, {
								handler: function() {
									this.openEvnAggEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: BTN_GRIDEDIT
							}, {
								handler: function() {
									this.openEvnAggEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: BTN_GRIDVIEW
							}, {
								handler: function() {
									this.deleteEvent('EvnAgg');
								}.createDelegate(this),
								iconCls: 'delete16',
								text: BTN_GRIDDEL
							}]
						})
					})]
				}),
				form.EvnXmlPanel
				]
			})]
		});

		sw.Promed.swEvnUslugaStomEditWindow.superclass.initComponent.apply(this, arguments);

		this.findById('EUStomEF_LpuSectionCombo').addListener('change', function(combo, newValue, oldValue) {
			var base_form = this.findById('EvnUslugaStomEditForm').getForm();

			if (
				!Ext.isEmpty(base_form.findField('LpuDispContract_id').getValue())
				&& (base_form.findField('LpuDispContract_id').getStore().getCount() > 0) // если комбик прогрузился (есть record)
				&& !Ext.isEmpty(base_form.findField('LpuSection_uid').getFieldValue('LpuSectionProfile_id'))
				&& base_form.findField('LpuDispContract_id').getFieldValue('LpuSectionProfile_id') != base_form.findField('LpuSection_uid').getFieldValue('LpuSectionProfile_id')
			) {
				base_form.findField('LpuDispContract_id').clearValue();
			}
			base_form.findField('LpuDispContract_id').getStore().baseParams.LpuSectionProfile_id = base_form.findField('LpuSection_uid').getFieldValue('LpuSectionProfile_id');
			base_form.findField('LpuDispContract_id').lastQuery = 'This query sample that is not will never appear';
			base_form.findField('LpuDispContract_id').getStore().baseParams.query = '';

			if ( getRegionNick().inlist([ 'perm', 'ufa' ]) ) {
				switch ( getRegionNick() ) {
					case 'perm':
						base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = newValue;
					break;

					case 'ufa':
						base_form.findField('UslugaCategory_id').fireEvent('change', base_form.findField('UslugaCategory_id'), base_form.findField('UslugaCategory_id').getValue());
					break;
				}

				this.loadUslugaComplexTariffCombo();
			}
			
			if (base_form.findField('UslugaPlace_id').getValue() == 1) {
				base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('LpuSection_uid').getFieldValue('LpuSectionProfile_id'));
			}
			else {
				base_form.findField('LpuSectionProfile_id').setValue(null);
			}
			base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());
			base_form.findField('LpuSectionProfile_id').onChangeLpuSectionId(combo, newValue);
		}.createDelegate(this));

		this.findById('EUStomEF_MedPersonalCombo').addListener('change', function(combo, newValue, oldValue) {
			var base_form = this.findById('EvnUslugaStomEditForm').getForm();

			if (
				!Ext.isEmpty(base_form.findField('LpuDispContract_id').getValue())
				&& (base_form.findField('LpuDispContract_id').getStore().getCount() > 0) // если комбик прогрузился (есть record)
				&& !Ext.isEmpty(base_form.findField('LpuSection_uid').getFieldValue('LpuSectionProfile_id'))
				&& base_form.findField('LpuDispContract_id').getFieldValue('LpuSectionProfile_id') != base_form.findField('LpuSection_uid').getFieldValue('LpuSectionProfile_id')
			) {
				base_form.findField('LpuDispContract_id').clearValue();
			}
			base_form.findField('LpuDispContract_id').getStore().baseParams.LpuSectionProfile_id = base_form.findField('LpuSection_uid').getFieldValue('LpuSectionProfile_id');
			base_form.findField('LpuDispContract_id').lastQuery = 'This query sample that is not will never appear';
			base_form.findField('LpuDispContract_id').getStore().baseParams.query = '';

			if ( getRegionNick().inlist([ 'perm', 'ufa' ]) ) {
				switch ( getRegionNick() ) {
					case 'perm':
						var index = combo.getStore().findBy(function(rec) { return (rec.get('MedStaffFact_id') == newValue); });

						if ( index >= 0 ) {
							base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = combo.getStore().getAt(index).get('LpuSection_id');
						}
					break;

					case 'ufa':
						base_form.findField('UslugaCategory_id').fireEvent('change', base_form.findField('UslugaCategory_id'), base_form.findField('UslugaCategory_id').getValue());
					break;
				}

				this.loadUslugaComplexTariffCombo();
			}

			if ( getRegionNick().inlist([ 'penza' ]) ) {
				base_form.findField('LpuSectionProfile_id').onChangeMsoField(combo, combo.getFieldValue('MedSpecOms_id'));
			}
		}.createDelegate(this));

		this.findById('EUStomEF_MidMedPersonalCombo').addListener('change', function(combo, newValue, oldValue) {
			var base_form = this.findById('EvnUslugaStomEditForm').getForm();

			if ( Ext.isEmpty(newValue) ) {
				base_form.findField('EvnUslugaStom_UEM').setValue('');
			}

			if ( !Ext.isEmpty(base_form.findField('UslugaComplexTariff_id').getValue()) ) {
				base_form.findField('UslugaComplexTariff_id').fireEvent('change', base_form.findField('UslugaComplexTariff_id'), base_form.findField('UslugaComplexTariff_id').getValue());
			}
			else {
				base_form.findField('EvnUslugaStom_Kolvo').fireEvent('change', base_form.findField('EvnUslugaStom_Kolvo'), base_form.findField('EvnUslugaStom_Kolvo').getValue());
			}
		}.createDelegate(this));

		this.findById('EUStomEF_LpuSectionCombo').addListener('keydown', function(inp, e) {
			if (e.getKey() == Ext.EventObject.TAB && e.shiftKey == true) {
				e.stopEvent();
				this.buttons[this.buttons.length - 1].focus();
			}
		}.createDelegate(this));

		this.findById('EUStomEF_UslugaComplexCombo').addListener('select', function(combo, record, index) {
			var base_form = this.findById('EvnUslugaStomEditForm').getForm();

			if ( base_form.findField('UslugaComplexTariff_id').params.UslugaComplex_id != (typeof record == 'object' ? record.get('UslugaComplex_id') : '') ) {
				this.loadUslugaComplexTariffCombo();
				this.toogleParodontogram(combo, (typeof record == 'object' ? record.get('UslugaComplex_id') : ''));

				var UslugaComplexAttributeList = (typeof record == 'object' ? record.get('UslugaComplex_AttributeList') : '');
				var isCommonStom = false;
				var isOperStom = false;

				if ( !Ext.isEmpty(UslugaComplexAttributeList) ) {
					var AttributeArray = UslugaComplexAttributeList.split(',');

					for ( var i in AttributeArray ) {
						if ( AttributeArray[i] == 'obstom' ) {
							isCommonStom = true;
						}
						if ( AttributeArray[i] == 'operstomatusl' ) {
							isOperStom = true;
						}
					}
				}

				if ( getRegionNick() == 'perm' && isCommonStom == true ) {
					base_form.findField('EvnUslugaStom_IsAllMorbus').setValue(2);
				}
				else {
					base_form.findField('EvnUslugaStom_IsAllMorbus').setValue(1);
				}
				
				if (isOperStom) {
					this.EvnXmlPanel.options.XmlType_id = sw.Promed.EvnXml.OPERATION_PROTOCOL_TYPE_ID; // протокол операции
				}
				else {
					this.EvnXmlPanel.options.XmlType_id = sw.Promed.EvnXml.EVN_USLUGA_PROTOCOL_TYPE_ID; // протокол оказания услуги
				}
				
			}

			if ( getRegionNick() == 'penza' && this.action != 'view' ) {
				if ( typeof record == 'object' && record.get('UslugaTypeAttributeValue') == '02' ) {
					base_form.findField('MedStaffFact_id').setValue(base_form.findField('EvnVizitPLStom_id').getFieldValue('MedStaffFact_id'));
					base_form.findField('MedStaffFact_id').disable();
				}
				else {
					base_form.findField('MedStaffFact_id').enable();
				}
			}

			return true;
		}.createDelegate(this));
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnUslugaStomEditWindow');
			switch (e.getKey()) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.G:
					if ( !current_window.buttons[1].hidden ) {
						current_window.doSave({
							continueInput: true
						});
					}
				break;

				case Ext.EventObject.J:
					current_window.onCancelAction();
				break;

				case Ext.EventObject.NUM_ONE:
				case Ext.EventObject.ONE:
					current_window.findById('EUStomEF_EvnUslugaStomPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_TWO:
				case Ext.EventObject.TWO:
					current_window.findById('EUStomEF_EvnAggPanel').toggleCollapse();
				break;
			}
		}.createDelegate(this),
		key: [
			Ext.EventObject.C,
			Ext.EventObject.G,
			Ext.EventObject.J,
			Ext.EventObject.NUM_ONE,
			Ext.EventObject.NUM_TWO,
			Ext.EventObject.ONE,
			Ext.EventObject.TWO
		],
		stopEvent: true
	}, {
		alt: false,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnUslugaStomEditWindow');
			switch (e.getKey()) {
				case Ext.EventObject.ESC:
					current_window.onCancelAction();
				break;
			}
		},
		key: [
			Ext.EventObject.ESC
		],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			win.findById('EUStomEF_EvnAggPanel').doLayout();
			win.findById('EUStomEF_EvnUslugaStomPanel').doLayout();
		},
		'restore': function(win) {
			win.findById('EUStomEF_EvnAggPanel').doLayout();
			win.findById('EUStomEF_EvnUslugaStomPanel').doLayout();
		}
	},
	loadUslugaComplexTariffCombo: function () {
		var base_form = this.findById('EvnUslugaStomEditForm').getForm(),
			combo = base_form.findField('UslugaComplexTariff_id'),
			uc_combo = base_form.findField('UslugaComplex_id'),
			evn_agg_panel = this.findById('EUStomEF_EvnAggPanel'),
			uemField = base_form.findField('EvnUslugaStom_UEM'),
			uedField = base_form.findField('EvnUslugaStom_UED'),
			kolvoField = base_form.findField('EvnUslugaStom_Kolvo'),
			isMes = (2 == base_form.findField('EvnUslugaStom_IsMes').getValue()),
			isPerm = (getGlobalOptions().region && getGlobalOptions().region.nick == 'perm'),
			uc_id = uc_combo.getValue(),
			isPackage = false,
			params = {
				LpuSection_id: base_form.findField('LpuSection_uid').getValue()
				,PayType_id: base_form.findField('PayType_id').getValue()
				,Person_id: base_form.findField('Person_id').getValue()
				,UslugaComplexTariff_Date: base_form.findField('EvnUslugaStom_setDate').getValue()
			},
			uc_rec,
			index;
		if (uc_id) {
			index = uc_combo.getStore().findBy(function(rec) {
				return (rec.get(uc_combo.valueField) == uc_id);
			});
			uc_rec = uc_combo.getStore().getAt(index);
			isPackage = (uc_rec && 9 == uc_rec.get('UslugaComplexLevel_id'));
		}
		this.uslugaPanel.doReset();
		if (isPackage) {
			combo.clearParams();
			this.uslugaPanel.setTariffParams(params);
			if (!Ext.isEmpty(this.Mes_id) && isMes) {
				this.uslugaPanel.setParam('Mes_id', this.Mes_id);
			} else {
				this.uslugaPanel.setParam('Mes_id', null);
			}
			this.uslugaPanel.setParam('EvnUsluga_pid', base_form.findField('EvnUslugaStom_pid').getValue());
			this.uslugaPanel.setParam('UslugaComplex_id', uc_id);
			this.uslugaPanel.setParam('UslugaComplexLevel_id', uc_rec.get('UslugaComplexLevel_id'));
			this.uslugaPanel.doLoad();
			evn_agg_panel.collapse();
			kolvoField.setValue('');
		} else {
			params['UslugaComplex_id'] = uc_id;
			combo.setParams(params);
			kolvoField.setValue(1);
		}
		if ('add' == this.action) {
			combo.setDisabled(isPackage);
			uemField.setDisabled(isPackage);
			uedField.setDisabled(isPackage);
			combo.fireEvent('change', combo, combo.getValue());
		}
		kolvoField.setDisabled(isPackage);
		evn_agg_panel.setDisabled(isPackage);
		this.uslugaPanel.setVisible(isPackage);
		combo.isAllowSetFirstValue = ('add' == this.action);
		if (getRegionNick() == 'perm') {
			combo.getStore().baseParams.UEDAboveZero = 1;
		}
		combo.loadUslugaComplexTariffList();
		return true;
	},
	maximizable: true,
	minHeight: 450,
	minWidth: 900,
	modal: true,
	onCancelAction: function() {
		var me = this;
		var evn_usluga_id = me.findById('EvnUslugaStomEditForm').getForm().findField('EvnUslugaStom_id').getValue();
		if (evn_usluga_id > 0 && this.action == 'add') {
			// удалить услугу
			// закрыть окно после успешного удаления
			var loadMask = new Ext.LoadMask(me.getEl(), { msg: "Удаление услуги..." });
			loadMask.show();
			Ext.Ajax.request({
				callback: function(options, success, response) {
					loadMask.hide();
					if (success) {
						if (me.parodontogramPanel.isAllowDelete()) {
							me.parodontogramPanel.doDelete({
								callback: function(){
									me.hide();
								}
							});
						} else {
							me.hide();
						}
					} else {
						sw.swMsg.alert('Ошибка', 'При удалении услуги возникли ошибки');
					}
				},
				params: {
					'class': 'EvnUslugaStom',
					'id': evn_usluga_id
				},
				url: '/?c=EvnUsluga&m=deleteEvnUsluga'
			});
		} else {
			me.hide();
		}
	},
	onHide: Ext.emptyFn,
	openEvnAggEditWindow: function(action) {
		if (action != 'add' && action != 'edit' && action != 'view') {
			return false;
		}
		var base_form = this.findById('EvnUslugaStomEditForm').getForm();
		var grid = this.findById('EUStomEF_EvnAggGrid');
		if (this.action == 'view') {
			if (action == 'add') {
				return false;
			} else if (action == 'edit') {
				action = 'view';
			}
		}
		if (getWnd('swEvnAggEditWindow').isVisible()) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования осложнения уже открыто');
			return false;
		}
		if (action == 'add' && base_form.findField('EvnUslugaStom_id').getValue() == 0) {
			this.doSave({
				openChildWindow: function() {
					this.openEvnAggEditWindow(action);
				}.createDelegate(this),
				print: false
			});
			return false;
		}
		var params = new Object();
		var person_id = this.findById('EUStomEF_PersonInformationFrame').getFieldValue('Person_id');
		var person_birthday = this.findById('EUStomEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_firname = this.findById('EUStomEF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_secname = this.findById('EUStomEF_PersonInformationFrame').getFieldValue('Person_Secname');
		var person_surname = this.findById('EUStomEF_PersonInformationFrame').getFieldValue('Person_Surname');
		if (action == 'add') {
			params.EvnAgg_id = 0;
			params.EvnAgg_pid = base_form.findField('EvnUslugaStom_id').getValue();
			params.Person_id = base_form.findField('Person_id').getValue();
			params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			params.Server_id = base_form.findField('Server_id').getValue();
		} else {
			var selected_record = grid.getSelectionModel().getSelected();
			if (!selected_record || !selected_record.get('EvnAgg_id')) {
				return false;
			}
			params = selected_record.data;
		}
		getWnd('swEvnAggEditWindow').show({
			action: action,
			callback: function(data) {
				if (!data || !data.EvnAggData) {
					return false;
				}
				var record = grid.getStore().getById(data.EvnAggData[0].EvnAgg_id);
				if (!record) {
					if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnAgg_id')) {
						grid.getStore().removeAll();
					}
					grid.getStore().loadData(data.EvnAggData, true);
				} else {
					var evn_agg_fields = new Array();
					var i = 0;
					grid.getStore().fields.eachKey(function(key, item) {
						evn_agg_fields.push(key);
					});
					for (i = 0; i < evn_agg_fields.length; i++) {
						record.set(evn_agg_fields[i], data.EvnAggData[0][evn_agg_fields[i]]);
					}
					record.commit();
				}
			}.createDelegate(this),
			formParams: params,
			onHide: function() {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}.createDelegate(this),
			Person_id: person_id,
			Person_Birthday: person_birthday,
			Person_Firname: person_firname,
			Person_Secname: person_secname,
			Person_Surname: person_surname
		});
	},
	plain: true,
	resizable: true,
	onLoadLpuSection: function() {
		var base_form = this.findById('EvnUslugaStomEditForm').getForm();
		if (
			!Ext.isEmpty(base_form.findField('LpuDispContract_id').getValue())
			&& (base_form.findField('LpuDispContract_id').getStore().getCount() > 0) // если комбик прогрузился (есть record)
			&& !Ext.isEmpty(base_form.findField('LpuSection_uid').getFieldValue('LpuSectionProfile_id'))
			&& base_form.findField('LpuDispContract_id').getFieldValue('LpuSectionProfile_id') != base_form.findField('LpuSection_uid').getFieldValue('LpuSectionProfile_id')
		) {
			base_form.findField('LpuDispContract_id').clearValue();
		}
		base_form.findField('LpuDispContract_id').getStore().baseParams.LpuSectionProfile_id = base_form.findField('LpuSection_uid').getFieldValue('LpuSectionProfile_id');
		if (
			!Ext.isEmpty(base_form.findField('LpuDispContract_id').getValue())
			&& !Ext.isEmpty(base_form.findField('EvnUslugaStom_setDate').getValue())
			&& !(
				base_form.findField('LpuDispContract_id').getFieldValue('LpuDispContract_setDate') <= base_form.findField('EvnUslugaStom_setDate').getValue()
				&& Ext.isEmpty(base_form.findField('LpuDispContract_id').getFieldValue('LpuDispContract_disDate')) || base_form.findField('LpuDispContract_id').getFieldValue('LpuDispContract_disDate') >= base_form.findField('EvnUslugaStom_setDate').getValue()
			)
		) {
			base_form.findField('LpuDispContract_id').clearValue();
		}
		base_form.findField('LpuDispContract_id').getStore().baseParams.onDate = Ext.util.Format.date(base_form.findField('EvnUslugaStom_setDate').getValue(), 'd.m.Y');
		base_form.findField('LpuDispContract_id').lastQuery = 'This query sample that is not will never appear';
		base_form.findField('LpuDispContract_id').getStore().baseParams.query = '';
	},
	show: function() {
		sw.Promed.swEvnUslugaStomEditWindow.superclass.show.apply(this, arguments);

		var win = this;

		this.EvnDiagPLStom_Title = null;

		this.findById('EUStomEF_EvnAggPanel').collapse();
		this.findById('EUStomEF_EvnUslugaStomPanel').expand();
		this.restore();
		this.center();

		this.buttons[1].hide();

		var base_form = this.findById('EvnUslugaStomEditForm').getForm();
		base_form.reset();

		base_form.findField('UslugaComplex_id').clearBaseParams();
		base_form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ 'stom' ]);

		if ( getRegionNick() == 'penza' ) {
			base_form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ 'stom', 'uslugatype' ]);
		}

		base_form.findField('UslugaComplexTariff_id').clearParams();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formMode = 'classic';
		this.formStatus = 'edit';
		this.Mes_id = null;
		this.onHide = Ext.emptyFn;
		this.isVisibleDisDTPanel = false;

		//this.toggleVisibleDisDTPanel('hide');
		this.findById('EUStomEF_EvnAggGrid').getStore().removeAll();

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert('Сообщение', 'Неверные параметры', function() {
				this.hide();
			}.createDelegate(this));
			return false;
		}

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].EvnDiagPLStom_Title ) {
			this.EvnDiagPLStom_Title = arguments[0].EvnDiagPLStom_Title;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].formMode ) {
			this.formMode = arguments[0].formMode;
		}

		if ( arguments[0].Mes_id ) {
			this.Mes_id = arguments[0].Mes_id;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}
		
		this.isAddParodontogram = arguments[0].isAddParodontogram || false;
		this.hasParodontogram = arguments[0].hasParodontogram || false;
		var disallowedUslugaComplexAttributeList = [];

		if ( this.formMode == 'morbus' ) {
			base_form.findField('EvnDiagPLStom_id').setContainerVisible(true);
			base_form.findField('EvnDiagPLStom_id').setAllowBlank(false);
			base_form.findField('EvnUslugaStom_IsAllMorbus').setContainerVisible(getRegionNick() == 'perm');
			base_form.findField('EvnUslugaStom_IsMes').setFieldLabel('Услуги по КСГ:');
		}
		else {
			base_form.findField('EvnDiagPLStom_id').setContainerVisible(false);
			base_form.findField('EvnDiagPLStom_id').setAllowBlank(true);
			base_form.findField('EvnUslugaStom_IsAllMorbus').setContainerVisible(false);
			base_form.findField('EvnUslugaStom_IsMes').setFieldLabel('Услуги по МЭС:');
		}

		base_form.findField('UslugaMedType_id').fireEvent('change', base_form.findField('UslugaMedType_id'), null);

		base_form.findField('BlackCariesClass_id').setContainerVisible(getRegionNick().inlist([ 'krym' ]));

		if ( getRegionNick().inlist([ 'pskov' ]) || (getRegionNick().inlist(['perm']) && this.formMode == 'morbus') ) {
			disallowedUslugaComplexAttributeList.push('vizit');
		}
		if ( this.hasParodontogram ) {
			disallowedUslugaComplexAttributeList.push(sw.Promed.StomHelper.USLUGA_PARODONTOGRAM_ATTR);
		}
		if ( disallowedUslugaComplexAttributeList.length > 0 ) {
			base_form.findField('UslugaComplex_id').setDisallowedUslugaComplexAttributeList(disallowedUslugaComplexAttributeList);
		}
		
		if ( this.action == 'add' ) {
			this.findById('EUStomEF_EvnAggPanel').isLoaded = true;
			this.findById('EUStomEF_EvnUslugaStomPanel').isLoaded = true;
		}
		else {
			this.findById('EUStomEF_EvnAggPanel').isLoaded = false;
			this.findById('EUStomEF_EvnUslugaStomPanel').isLoaded = false;
		}

		base_form.setValues(arguments[0].formParams);
		
		this.findById('EUStomEF_Parodontogram').hide();
		this.parodontogramPanel.doReset();
		this.parodontogramPanel.applyParams(
			(arguments[0].Person_id || base_form.findField('Person_id').getValue()),
			base_form.findField('EvnUslugaStom_id').getValue(),
			base_form.findField('EvnUslugaStom_setDate').getRawValue()
		);
		this.parodontogramPanel.setReadOnly(this.action == 'view');
		this.uslugaPanel.doReset();
		this.uslugaPanel.setVisible(false);

		this.findById('EUStomEF_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnUslugaStom_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EUStomEF_PersonInformationFrame', field);
			}
		});

		var lpu_section_combo = base_form.findField('LpuSection_uid');
		var med_personal_combo = base_form.findField('MedStaffFact_id');
		var pay_type_combo = base_form.findField('PayType_id');
		var usluga_complex_combo = base_form.findField('UslugaComplex_id');
		var is_mes_combo = base_form.findField('EvnUslugaStom_IsMes');
		var PersonAge = swGetPersonAge(arguments[0].Person_Birthday, new Date()) || null;

		usluga_complex_combo.getStore().baseParams.PersonAge = PersonAge;

		var isPerm = (getGlobalOptions().region && getGlobalOptions().region.nick == 'perm');
		var isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa');

		this.findById('EUStomEF_EvnAggGrid').getStore().removeAll();
		this.findById('EUStomEF_EvnAggGrid').getTopToolbar().items.items[0].enable();
		this.findById('EUStomEF_EvnAggGrid').getTopToolbar().items.items[1].disable();
		this.findById('EUStomEF_EvnAggGrid').getTopToolbar().items.items[2].disable();
		this.findById('EUStomEF_EvnAggGrid').getTopToolbar().items.items[3].disable();

		if ('add' == this.action && base_form.findField('EvnUslugaStom_pid').getValue() > 0) {
			/*
			 При создании событий оказания услуги,
			 у которых родительским событием будет движение или посещение,
			 можно выбрать пакет услуг
			 */
			usluga_complex_combo.getStore().baseParams.withoutPackage = 0;
		} else {
			// во всех остальных случаях НЕЛЬЗЯ
			usluga_complex_combo.getStore().baseParams.withoutPackage = 1;
			this.findById('EUStomEF_EvnAggPanel').setDisabled(false);
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		win.loadCount = 0;

		if (getRegionNick() == 'ekb') {
			usluga_complex_combo.getStore().baseParams.Mes_id = win.Mes_id;
		}

		base_form.findField('EvnDiagPLStom_id').getStore().removeAll();
		base_form.findField('EvnVizitPLStom_id').getStore().removeAll();

		this.EvnXmlPanel.doReset();
		this.EvnXmlPanel.collapse();
		this.EvnXmlPanel.LpuSectionField = lpu_section_combo;
		this.EvnXmlPanel.MedStaffFactField = med_personal_combo;

		base_form.findField('UslugaMedType_id').setContainerVisible(getRegionNick() === 'kz');

		switch ( win.action ) {
			case 'add':
				win.setTitle(WND_POL_EUSTOMADD);
				win.enableEdit(true);

				base_form.findField('EvnUslugaStom_Kolvo').setValue(1);
				base_form.findField('UslugaComplex_id').getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();

				// Для Перми
				if ( isPerm == true ) {
					// Устанавливаем параметр "Услуги по МЭС"
					if ( win.formMode == 'classic' ) {
						if ( !Ext.isEmpty(win.Mes_id) ) {
							is_mes_combo.setValue(2);
						}
						else {
							is_mes_combo.setValue(1);
							// Делаем параметр "Услуги по МЭС" недоступным, если МЭС не задан
							is_mes_combo.disable();
						}
					}
					else {
						is_mes_combo.setValue(1);

						if ( Ext.isEmpty(win.Mes_id) ) {
							is_mes_combo.disable();
						}
					}

					is_mes_combo.fireEvent('change', is_mes_combo, is_mes_combo.getValue());
				}

				if ( win.formMode == 'morbus' ) {
					win.loadCount = 2;
					base_form.findField('EvnVizitPLStom_id').enable();
				}
				else {
					win.loadCount = 1;
					base_form.findField('EvnVizitPLStom_id').disable();
				}

				base_form.findField('EvnVizitPLStom_id').getStore().load({
					callback: function() {
						win.loadCount = win.loadCount - 1;

						base_form.findField('EvnVizitPLStom_id').setValue(base_form.findField('EvnUslugaStom_pid').getValue());
						base_form.findField('EvnVizitPLStom_id').fireEvent('change', base_form.findField('EvnVizitPLStom_id'), base_form.findField('EvnVizitPLStom_id').getValue());

						if ( win.loadCount == 0 ) {
							loadMask.hide();
						}

						if ( base_form.findField('EvnUslugaStom_pid').getValue() ) {
							usluga_complex_combo.getStore().baseParams.EvnUsluga_pid = base_form.findField('EvnUslugaStom_pid').getValue();
							usluga_complex_combo.getStore().baseParams.LpuSection_pid = null;
						}
						else {
							usluga_complex_combo.getStore().baseParams.EvnUsluga_pid = null;
							usluga_complex_combo.getStore().baseParams.LpuSection_pid = null;
						}

						var index;
						var ucat_cmb = base_form.findField('UslugaCategory_id');
						var ucat_rec;

						if ( ucat_cmb.getStore().getCount() == 1 ) {
							ucat_cmb.disable();
							ucat_rec = ucat_cmb.getStore().getAt(0);
							ucat_cmb.setValue(ucat_rec.get('UslugaCategory_id'));
						}
						else if ( getRegionNick() == 'kareliya' ) {
							ucat_cmb.disable();
							ucat_cmb.setFieldValue('UslugaCategory_SysNick', 'stomoms');

							index = ucat_cmb.getStore().findBy(function(rec) {
								return (rec.get('UslugaCategory_SysNick') == 'stomoms');
							});
							ucat_rec = ucat_cmb.getStore().getAt(index);
						}
						else {
							if ( getRegionNick() == 'perm' ) {
								var date1 = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
								var date2 = new Date('2014-12-31');
								if (!Ext.isEmpty(base_form.findField('EvnUslugaStom_setDate').getValue())) {
									date1 = base_form.findField('EvnUslugaStom_setDate').getValue();
								}
								if (date1 < date2) {
									index = ucat_cmb.getStore().findBy(function(rec) {
										return (rec.get('UslugaCategory_SysNick') == 'tfoms');
									});
								} else {
									index = ucat_cmb.getStore().findBy(function(rec) {
										return (rec.get('UslugaCategory_SysNick') == 'gost2011');
									});
								}
								ucat_rec = ucat_cmb.getStore().getAt(index);
							}
							// Для Казахстана по умолчанию подставляем услуги МО
							else if ( getRegionNick() == 'kz' ) {
								index = ucat_cmb.getStore().findBy(function(rec) {
									return (rec.get('UslugaCategory_SysNick') == 'classmedus');
								});
								ucat_rec = ucat_cmb.getStore().getAt(index);
							}
							// Для Крыма по умолчанию подставляем услуги ТФОМС
							else if ( getRegionNick() == 'krym' ) {
								index = ucat_cmb.getStore().findBy(function(rec) {
									return (rec.get('UslugaCategory_SysNick') == 'tfoms');
								});
								ucat_rec = ucat_cmb.getStore().getAt(index);
							}
							// Для регионов где по умолчанию подставляем услуги ГОСТ-2011
							else if ( getRegionNick().inlist([ 'khak', 'penza', 'adygeya'])) {
								index = ucat_cmb.getStore().findBy(function(rec) {
									return (rec.get('UslugaCategory_SysNick') == 'gost2011');
								});
								ucat_rec = ucat_cmb.getStore().getAt(index);
							}
							else if ( getRegionNick().inlist([ 'ekb' ]) ) {
								index = ucat_cmb.getStore().findBy(function(rec) {
									return (rec.get('UslugaCategory_SysNick') == 'tfoms');
								});
								ucat_rec = ucat_cmb.getStore().getAt(index);
							}
							else {
								ucat_rec = ucat_cmb.getStore().getById(ucat_cmb.getValue());
							}
						}

						if ( ucat_rec ) {
							ucat_cmb.fireEvent('select', ucat_cmb, ucat_rec);
							ucat_cmb.fireEvent('select', ucat_cmb, ucat_cmb.getStore().getById(ucat_cmb.getValue()));
						}

						LoadEmptyRow(win.findById('EUStomEF_EvnAggGrid'));

						setCurrentDateTime({
							dateField: base_form.findField('EvnUslugaStom_setDate'),
							loadMask: false,
							setDate: false,
							setDateMaxValue: true,
							setDateMinValue: false,
							setTime: false,
							timeField: base_form.findField('EvnUslugaStom_setTime'),
							windowId: win.id
						});

						if ( win.loadCount == 0 ) {
							loadMask.hide();
						}

						if ( win.isAddParodontogram ) {
							if ( isPerm == true ) {
								// Делаем параметр "Услуги по МЭС" недоступным
								is_mes_combo.disable();
								// убираем фильтр по МЭС
								is_mes_combo.setValue(1);
								is_mes_combo.fireEvent('change', is_mes_combo, is_mes_combo.getValue());
							}
							usluga_complex_combo.getStore().baseParams.withoutPackage = 1;
							usluga_complex_combo.setAllowedUslugaComplexAttributeList([sw.Promed.StomHelper.USLUGA_PARODONTOGRAM_ATTR]);
							usluga_complex_combo.disable();
							ucat_cmb.disable();
							usluga_complex_combo.getStore().load({
								callback: function() {
									if ( usluga_complex_combo.getStore().getCount() == 1 ) {
										var rec = usluga_complex_combo.getStore().getAt(0);
										usluga_complex_combo.setValue(rec.get('UslugaComplex_id'));
										usluga_complex_combo.fireEvent('change', usluga_complex_combo, usluga_complex_combo.getValue());
									} else {
										usluga_complex_combo.enable();
										usluga_complex_combo.focus(true,250);
									}
								}
							});
						} else {
							win.buttons[1].show();
							usluga_complex_combo.focus(true,250);
						}
						base_form.clearInvalid();
					},
					params: {
						rid: base_form.findField('EvnUslugaStom_rid').getValue()
					}
				});

				if ( win.formMode == 'morbus' ) {
					base_form.findField('EvnUslugaStom_IsAllMorbus').setValue(1);

					base_form.findField('EvnDiagPLStom_id').getStore().load({
						callback: function() {
							win.loadCount = win.loadCount - 1;

							if ( win.loadCount == 0 ) {
								loadMask.hide();
							}

							if ( !Ext.isEmpty(base_form.findField('EvnDiagPLStom_id').getValue()) ) {
								usluga_complex_combo.getStore().baseParams.EvnDiagPLStom_id = base_form.findField('EvnDiagPLStom_id').getValue();
								base_form.findField('EvnDiagPLStom_id').setValue(base_form.findField('EvnDiagPLStom_id').getValue());
								if (!Ext.isEmpty(win.EvnDiagPLStom_Title)) {
									base_form.findField('EvnDiagPLStom_id').setRawValue(win.EvnDiagPLStom_Title);
								}
							}
							else {
								usluga_complex_combo.getStore().baseParams.EvnDiagPLStom_id = null;
							}

							base_form.findField('EvnDiagPLStom_id').fireEvent('change', base_form.findField('EvnDiagPLStom_id'), base_form.findField('EvnDiagPLStom_id').getValue());
						},
						params: {
							EvnDiagPLStom_rid: base_form.findField('EvnUslugaStom_rid').getValue()
						}
					});
				}
				
				if (arguments[0].formParams && arguments[0].formParams && arguments[0].formParams.LpuSectionProfile_id) {
					var lpu_section_profile_id = arguments[0].formParams.LpuSectionProfile_id;
					
					base_form.findField('LpuSectionProfile_id').getStore().load({
						callback: function (records, operation, success) {
							console.log('callback was called');
							if(success) {
								base_form.findField('LpuSectionProfile_id').getStore().findBy((rec) => {
									if(rec.json.LpuSectionProfile_id == lpu_section_profile_id) {
										console.log(base_form.findField('LpuSectionProfile_id'));
										base_form.findField('LpuSectionProfile_id').setValue(rec.json.LpuSectionProfile_id);
										base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());
									}
								});
							}
						}
					});
				}

				if (getRegionNick() === 'kz') {
					base_form.findField('UslugaMedType_id').setFieldValue('UslugaMedType_Code', '1400');
					pay_type_combo.disable();
				}
			break;

			case 'edit':
			case 'view':
				var evn_usluga_stom_id = base_form.findField('EvnUslugaStom_id').getValue();

				if ( !evn_usluga_stom_id ) {
					loadMask.hide();
					win.hide();
					return false;
				}

				win.loadCount = 1;

				base_form.load({
					failure: function() {
						loadMask.hide();

						sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() {
							win.hide();
						});
					},
					params: {
						'class': 'EvnUslugaStom',
						'id': evn_usluga_stom_id,
						archiveRecord: win.archiveRecord
					},
					success: function(result_form, action) {
						win.loadCount = win.loadCount - 1;

						// В зависимости от accessType переопределяем win.action
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							win.action = 'view';
						}

						win.EvnXmlPanel.setReadOnly('view' == win.action);
						win.EvnXmlPanel.setBaseParams({
							userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
							UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
							Server_id: base_form.findField('Server_id').getValue(),
							Evn_id: base_form.findField('EvnUslugaStom_id').getValue()
						});
						win.EvnXmlPanel.doLoadData();

						if ( win.action == 'edit' ) {
							win.setTitle(WND_POL_EUSTOMEDIT);
							win.enableEdit(true);

							// Для Перми делаем параметр "Услуги по МЭС" недоступным
							if ( isPerm == true ) {
								is_mes_combo.disable();
							}
						}
						else {
							win.setTitle(WND_POL_EUSTOMVIEW);
							win.enableEdit(false);
							win.findById('EUStomEF_EvnAggGrid').getTopToolbar().items.items[0].disable();
						}

						if (!Ext.isEmpty(base_form.findField('LpuDispContract_id').getValue())) {
							// надо загрузить
							base_form.findField('LpuDispContract_id').getStore().load({
								params: {
									LpuDispContract_id: base_form.findField('LpuDispContract_id').getValue()
								},
								callback: function() {
									base_form.findField('LpuDispContract_id').setValue(base_form.findField('LpuDispContract_id').getValue());
								}
							});
						}

						if ( win.formMode == 'morbus' ) {
							win.loadCount++;

							base_form.findField('EvnDiagPLStom_id').getStore().load({
								callback: function() {
									win.loadCount = win.loadCount - 1;

									if ( win.loadCount == 0 ) {
										loadMask.hide();
									}

									if ( !Ext.isEmpty(base_form.findField('EvnDiagPLStom_id').getValue()) ) {
										usluga_complex_combo.getStore().baseParams.EvnDiagPLStom_id = base_form.findField('EvnDiagPLStom_id').getValue();
										base_form.findField('EvnDiagPLStom_id').setValue(base_form.findField('EvnDiagPLStom_id').getValue());
										if (!Ext.isEmpty(win.EvnDiagPLStom_Title)) {
											base_form.findField('EvnDiagPLStom_id').setRawValue(win.EvnDiagPLStom_Title);
										}
									}
									else {
										usluga_complex_combo.getStore().baseParams.EvnDiagPLStom_id = null;
									}

									base_form.findField('EvnDiagPLStom_id').fireEvent('change', base_form.findField('EvnDiagPLStom_id'), base_form.findField('EvnDiagPLStom_id').getValue());
								},
								params: {
									EvnDiagPLStom_rid: base_form.findField('EvnUslugaStom_rid').getValue()
								}
							});
						}
						
						base_form.findField('LpuSectionProfile_id').onChangeDateField(base_form.findField('EvnUslugaStom_setDate'), base_form.findField('EvnUslugaStom_setDate').getValue());
						base_form.findField('LpuSectionProfile_id').onChangeLpuSectionId(base_form.findField('LpuSection_uid'), base_form.findField('LpuSection_uid').getValue());
						base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());

						win.loadCount++;

						base_form.findField('EvnVizitPLStom_id').getStore().load({
							callback: function() {
								win.loadCount = win.loadCount - 1;

								base_form.findField('EvnVizitPLStom_id').setValue(base_form.findField('EvnUslugaStom_pid').getValue());
								base_form.findField('EvnVizitPLStom_id').disable();

								base_form.findField('UslugaComplex_id').getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();

								var setDate = base_form.findField('EvnUslugaStom_setDate').getValue();
								var setTime = base_form.findField('EvnUslugaStom_setTime').getValue();
								//var disDate = base_form.findField('EvnUslugaStom_disDate').getValue();
								//var disTime = base_form.findField('EvnUslugaStom_disTime').getValue();

								/*if ((!Ext.isEmpty(disDate) || !Ext.isEmpty(disTime)) && (disDate-setDate != 0 || setTime != disTime)) {
									win.toggleVisibleDisDTPanel('show');
								}*/
								
								usluga_complex_combo.getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_uid').getValue();
								if (base_form.findField('EvnUslugaStom_pid').getValue()) {
									usluga_complex_combo.getStore().baseParams.EvnUsluga_pid = base_form.findField('EvnUslugaStom_pid').getValue();
									usluga_complex_combo.getStore().baseParams.LpuSection_pid = null;
								} else {
									usluga_complex_combo.getStore().baseParams.EvnUsluga_pid = null;
									usluga_complex_combo.getStore().baseParams.LpuSection_pid = null;
								}

								var evn_usluga_stom_set_date = base_form.findField('EvnUslugaStom_setDate').getValue();
								var EvnUslugaStom_UED = base_form.findField('EvnUslugaStom_UED').getValue();
								var index;
								var lpu_section_uid = lpu_section_combo.getValue();
								var usluga_complex_id = usluga_complex_combo.getValue();
								var UslugaComplexTariff_id = base_form.findField('UslugaComplexTariff_id').getValue();
								var EvnUslugaStom_Summa = base_form.findField('EvnUslugaStom_Summa').getValue();

								var
									MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue(),
									MedStaffFact_sid = base_form.findField('MedStaffFact_sid').getValue(),
									ucat_cmb = base_form.findField('UslugaCategory_id');

								if ( win.action == 'edit' && ucat_cmb.getStore().getCount() == 1 ) {
									ucat_cmb.disable();
									ucat_cmb.setValue( ucat_cmb.getStore().getAt(0).get('UslugaCategory_id'));
								}

								lpu_section_combo.clearValue();
								var lpuSectionFilters = {
									allowLowLevel: 'yes',
									isStom: true,
									regionCode: getGlobalOptions().region.number,
									onDate: Ext.util.Format.date(evn_usluga_stom_set_date, 'd.m.Y')
								};
								setLpuSectionGlobalStoreFilter(lpuSectionFilters);
								lpu_section_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
								index = lpu_section_combo.getStore().findBy(function (rec, id) {
									return (rec.get('LpuSection_id') == lpu_section_uid);
								});
								if ( index >= 0 ) {
									lpu_section_combo.setValue(lpu_section_uid);
									win.loadMedStaffFactCombo('MedStaffFact_id', { onDate: (lpuSectionFilters.onDate||null), exactIdList: (!Ext.isEmpty(MedStaffFact_id) ? [ MedStaffFact_id ] : null) }, function() {
										win.loadMedStaffFactCombo('MedStaffFact_sid', { onDate: (lpuSectionFilters.onDate||null), exactIdList: (!Ext.isEmpty(MedStaffFact_sid) ? [ MedStaffFact_sid ] : null) }, function() {});
									});

									win.onLoadLpuSection();
								}
								else {
									Ext.Ajax.request({
										failure: function(response, options) {
											//
										},
										params: {
											LpuSection_id: lpu_section_uid
										},
										success: function(response, options) {
											lpu_section_combo.getStore().loadData(Ext.util.JSON.decode(response.responseText), true);

											index = lpu_section_combo.getStore().findBy(function (rec, id) {
												return (rec.get('LpuSection_id') == lpu_section_uid);
											});

											if ( index >= 0 ) {
												lpu_section_combo.setValue(lpu_section_uid);
											}
											win.loadMedStaffFactCombo('MedStaffFact_id', { onDate: (lpuSectionFilters.onDate||null), exactIdList: (!Ext.isEmpty(MedStaffFact_id) ? [ MedStaffFact_id ] : null) }, function() {
												win.loadMedStaffFactCombo('MedStaffFact_sid', { onDate: (lpuSectionFilters.onDate||null), exactIdList: (!Ext.isEmpty(MedStaffFact_sid) ? [ MedStaffFact_sid ] : null) }, function() {});
											});

											win.onLoadLpuSection();
										},
										url: C_LPUSECTION_LIST
									});
								}

								ucat_cmb.disable();
								usluga_complex_combo.disable();

								if ( !Ext.isEmpty(lpu_section_uid) ) {
									usluga_complex_combo.getStore().baseParams.LpuSection_id = lpu_section_uid;
								}

								usluga_complex_combo.getStore().load({
									callback: function() {
										if ( usluga_complex_combo.getStore().getCount() > 0 ) {
											usluga_complex_combo.setValue(usluga_complex_id);

											var usluga_category_id = usluga_complex_combo.getStore().getAt(0).get('UslugaCategory_id');

											index = base_form.findField('UslugaCategory_id').getStore().findBy(function(rec) {
												return (rec.get('UslugaCategory_id') == usluga_category_id);
											});

											if ( index >= 0 ) {
												base_form.findField('UslugaCategory_id').setValue(usluga_category_id);
											}

											win.toogleParodontogram(usluga_complex_combo, usluga_complex_id);
											
											var UslugaComplexAttributeList = usluga_complex_combo.getStore().getAt(0).get('UslugaComplex_AttributeList');
											if (!Ext.isEmpty(UslugaComplexAttributeList)) {
												var AttributeArray = UslugaComplexAttributeList.split(',');
												if (AttributeArray.in_array('operstomatusl')) {
													this.EvnXmlPanel.options.XmlType_id = sw.Promed.EvnXml.OPERATION_PROTOCOL_TYPE_ID; // протокол операции
												}
												else {
													this.EvnXmlPanel.options.XmlType_id = sw.Promed.EvnXml.EVN_USLUGA_PROTOCOL_TYPE_ID; // протокол оказания услуги
												}
											}

											if ( getRegionNick() == 'penza' && usluga_complex_combo.getStore().getAt(0).get('UslugaTypeAttributeValue') == '02' ) {
												base_form.findField('MedStaffFact_id').disable();
											}
										}
										else {
											usluga_complex_combo.clearValue();
										}

										// Для Перми получаем значение параметра "Услуги по МЭС"
										/*if ( isPerm == true && !Ext.isEmpty(win.Mes_id) && !Ext.isEmpty(usluga_complex_combo.getValue()) ) {
											Ext.Ajax.request({
												failure: function(response, options) {
													is_mes_combo.setValue(1);
												},
												params: {
													UslugaComplex_id: usluga_complex_combo.getValue(),
													Mes_id: win.Mes_id
												},
												success: function(response, options) {
													var response_obj = Ext.util.JSON.decode(response.responseText);
													var EvnUslugaStom_IsMes = 1;

													if ( !Ext.isEmpty(response_obj.Error_Msg) ) {
														sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
													}
													else {
														EvnUslugaStom_IsMes = (response_obj.UslugaComplex_IsMes == 2 ? 2 : 1);
													}

													is_mes_combo.setValue(EvnUslugaStom_IsMes);
												}.createDelegate(win),
												url: '/?c=Usluga&m=checkUslugaComplexIsMes'
											});
										}
										else {
											is_mes_combo.setValue(1);
										}*/
									}.createDelegate(win),
									params: {
										UslugaComplex_id: usluga_complex_id,
										EvnDiagPLStom_id: base_form.findField('EvnDiagPLStom_id').getValue()
									}
								});
								base_form.findField('UslugaComplexTariff_id').setParams({
									 LpuSection_id: lpu_section_uid
									,PayType_id: base_form.findField('PayType_id').getValue()
									,Person_id: base_form.findField('Person_id').getValue()
									,UslugaComplex_id: usluga_complex_id
									,UslugaComplexTariff_Date: base_form.findField('EvnUslugaStom_setDate').getValue()
								});

								if ( !Ext.isEmpty(UslugaComplexTariff_id) ) {
									base_form.findField('UslugaComplexTariff_id').getStore().load({
										callback: function() {
											if ( base_form.findField('UslugaComplexTariff_id').getStore().getCount() > 0 ) {
												base_form.findField('UslugaComplexTariff_id').setValue(UslugaComplexTariff_id);
												//base_form.findField('UslugaComplexTariff_id').fireEvent('change', base_form.findField('UslugaComplexTariff_id'), UslugaComplexTariff_id);

												if ( !Ext.isEmpty(EvnUslugaStom_Summa) ) {
													base_form.findField('EvnUslugaStom_Summa').setValue(EvnUslugaStom_Summa);
												}
												else {
													base_form.findField('EvnUslugaStom_UED').fireEvent('change', base_form.findField('EvnUslugaStom_UED'), EvnUslugaStom_UED);
												}
											}
											else {
												base_form.findField('UslugaComplexTariff_id').clearValue();
											}
										},
										params: {
											UslugaComplexTariff_id: UslugaComplexTariff_id
										}
									});
								}

								if ( win.loadCount == 0 ) {
									loadMask.hide();
								}

								base_form.clearInvalid();

								if ( win.action == 'edit' ) {
									lpu_section_combo.focus(true, 250);
								}
								else {
									win.buttons[win.buttons.length - 1].focus();
								}

								if ( getRegionNick() != 'perm' ) {
									lpu_section_combo.disable();
								}
								
								if (getRegionNick() == 'kz') {
									pay_type_combo.disable();
								}
							},
							params: {
								rid: base_form.findField('EvnUslugaStom_rid').getValue()
							}
						});
					},
					url: '/?c=EvnUsluga&m=loadEvnUslugaEditForm'
				});
			break;

			default:
				loadMask.hide();
				win.hide();
			break;
		}
	},
	/*toggleVisibleDisDTPanel: function(action) {
		var base_form = this.findById('EvnUslugaStomEditForm').getForm();

		if (action == 'show') {
			this.isVisibleDisDTPanel = false;
		} else if (action == 'hide') {
			this.isVisibleDisDTPanel = true;
		}

		if (this.isVisibleDisDTPanel) {
			this.findById('EUStomEF_EvnUslugaDisDTPanel').hide();
			this.findById('EUStomEF_ToggleVisibleDisDTBtn').setText('Уточнить период выполнения');
			base_form.findField('EvnUslugaStom_disDate').setAllowBlank(true);
			base_form.findField('EvnUslugaStom_disTime').setAllowBlank(true);
			base_form.findField('EvnUslugaStom_disDate').setValue(null);
			base_form.findField('EvnUslugaStom_disTime').setValue(null);
			this.isVisibleDisDTPanel = false;
		} else {
			this.findById('EUStomEF_EvnUslugaDisDTPanel').show();
			this.findById('EUStomEF_ToggleVisibleDisDTBtn').setText('Скрыть поля');
			base_form.findField('EvnUslugaStom_disDate').setAllowBlank(false);
			base_form.findField('EvnUslugaStom_disTime').setAllowBlank(false);
			this.isVisibleDisDTPanel = true;
		}
	},*/
	width: 900
});
