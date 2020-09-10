

/**
* swEvnUslugaFuncRequestDicomViewerEditWindow - окно редактирования выполнения услуги с встроенным DICOM просмотровщиком.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @version      сентябрь.2013
*
*/
/*NO PARSE JSON*/

sw.Promed.swEvnUslugaFuncRequestDicomViewerEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnUslugaFuncRequestDicomViewerEditWindow',
	objectSrc: '/jscore/Forms/FuncDiag/swEvnUslugaFuncRequestDicomViewerEditWindow.js',
	action: 'edit',
	autoScroll: true,
	autoHeight: false,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	id: 'EvnUslugaFuncRequestDicomViewerEditWindow',
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnUslugaFuncRequestDicomViewerEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		}.createDelegate(this),
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'form',
	listeners: {
		'hide': function(win) {
			var EvnXml_id = win.EvnXmlPanel.getEvnXmlId();
			if (!Ext.isEmpty(EvnXml_id)) {
				checkNeedSignature({
					EMDRegistry_ObjectName: 'EvnXml',
					EMDRegistry_ObjectID: EvnXml_id
				});
			}
			win.DicomPanel.removeAll({clearAll:true, addEmptyRecord:false});
			win.AssociatedResearches.removeAll({clearAll:true, addEmptyRecord:false});
			this._setResearchFormat('digital');
			win.onHide();
			this.ResearchRegion.collapse();
		},
	},
	maximizable: true,
//	height: 550,
	width: '100%',
//	minHeight: 550,
	minWidth: '100%',
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	masksInitiated: false,
	doSave: function (options) {

		if (typeof options != 'object') options = new Object();

		// options @Object
		// options.copyMode @String Режим создания копии выполняемой параклинической услуги

		if ( this.formStatus == 'save' || this.action == 'view' ) {return false;}

		// Режим цифровой?
		if (!options.ignoreDicomResearches && this._getResearchFormat() == 'digital' && !this.hasDicomAssociatedResearches()) {
			// Картинки заполнены?
			sw.swMsg.show({
				buttons: {yes: 'Продолжить', no: 'Отмена'},
				fn: function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						options.ignoreDicomResearches = true;
						this.doSave(options);
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: 'Не прикреплено ни одно исследование к результату выполнения услуги. Продолжить сохранение?',
				title: 'Продолжить сохранение?'
			});

			return false;
		}

		var silent = (options && options.silent) ? true : null;
		var wnd = this;

		this.formStatus = 'save';
		var base_form = this.findById('EvnUslugaFuncRequestEditForm').getForm(),
			templ_panel = Ext.getCmp('EUFREF_TemplPanel'),
			loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT_SAVE});

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					this.formStatus = 'edit';
					//log(base_form);
					//log(this.findById('EvnUslugaFuncRequestEditForm'));
					//log(this.findById('EvnUslugaFuncRequestEditForm').getFirstInvalidEl());
					this.EvnDirectionPanel.expand();
					this.findById('EvnUslugaFuncRequestEditForm').getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		var evn_usluga_set_time = base_form.findField('EvnUslugaPar_setTime').getValue();

		var params = new Object();

		if (base_form.findField('Org_uid').disabled) {
			params.Org_uid = base_form.findField('Org_uid').getValue();
		}

		if (base_form.findField('LpuSection_uid').disabled) {
			params.LpuSection_uid = base_form.findField('LpuSection_uid').getValue();
		}

		if (base_form.findField('MedStaffFact_id').disabled) {
			params.MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
		}

		if (base_form.findField('UslugaComplex_id').disabled) {
			params.UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
		}

		if (base_form.findField('EvnUsluga_Kolvo').disabled) {
			params.EvnUsluga_Kolvo = base_form.findField('EvnUsluga_Kolvo').getValue();
		}

		base_form.findField('MedPersonal_sid').setValue(base_form.findField('MedStaffFact_sid').getFieldValue('MedPersonal_id'));
		base_form.findField('MedPersonal_uid').setValue(base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id'));

		//params.AnamnezData = Ext.util.JSON.encode(templ_panel.getSavingData());
		//params.XmlTemplate_id = templ_panel.getXmlTemplate_id();

		var ARStore = this.AssociatedResearches.getGrid().getStore();
		var arr = [];
		for (var i = 0; ((i < ARStore.getCount()) && (this._getResearchFormat() == 'digital')); i++) {
			var rec = ARStore.getAt(i);
			arr.push({
				'study_uid': rec.get('study_uid'),
				'study_date': rec.get('study_date'),
				'study_time': rec.get('study_time'),
				'patient_name': rec.get('patient_name'),
				'LpuEquipmentPacs_id': rec.get('LpuEquipmentPacs_id')
			});
		}
		params.AssociatedResearches = JSON.stringify(arr);

		var grid = this.EvnUslugaAttributeValueGrid.getGrid();
		var isAttributeValuePanelHidden = wnd.checkAttributeValuePanelHidden();
		if (isAttributeValuePanelHidden) {
			grid.getStore().each(function (rec) {
				rec.set('RecordStatus_Code', 3);
				rec.commit();
			});
		}

		var isAttributeValuePanelHidden = wnd.checkAttributeValuePanelHidden();
		if (isAttributeValuePanelHidden) {
			grid.getStore().each(function (rec) {
				rec.set('RecordStatus_Code', 3);
				rec.commit();
			});
		}

		if (getRegionNick() == 'perm' && !isAttributeValuePanelHidden) {
			var UslugaComplex_Code = base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Code');
			var requiredAttributeSign_Codes = [];
			switch (UslugaComplex_Code) {
				case 'A04.20.001':
					requiredAttributeSign_Codes = ['12'];
					break;
				case 'A04.20.001.001':
					requiredAttributeSign_Codes = ['12'];
					break;
			}

			if (requiredAttributeSign_Codes && requiredAttributeSign_Codes.length > 0) {
				// проверяем обязательность заведённых атрибутов
				var AttributeSign_Codes = [];
				grid.getStore().each(function (rec) {
					if (rec.get('AttributeSign_Code')) {
						AttributeSign_Codes.push(rec.get('AttributeSign_Code').toString());
					}
				});

				var emptyAttributes = "";
				for (var k in requiredAttributeSign_Codes) {
					if (typeof requiredAttributeSign_Codes[k] != 'function' && !requiredAttributeSign_Codes[k].inlist(AttributeSign_Codes)) {
						emptyAttributes += "<br>";
						switch(requiredAttributeSign_Codes[k]) {
							case '12':
								emptyAttributes += "Результат ЭКО";
								break;
						}
					}
				}

				if (emptyAttributes.length > 0) {
					loadMask.hide();
					wnd.formStatus = 'edit';
					sw.swMsg.alert(langs('Ошибка'), 'Для сохранения необходимо добавить обязательные признаки атрибутов  в раздел «Атрибуты»:' + emptyAttributes);
					return false;
				}
			}

			if (UslugaComplex_Code && UslugaComplex_Code.inlist(['A04.20.001', 'A04.20.001.001'])) {
				var ecoResultRec = null;
				grid.getStore().each(function (rec) {
					if (rec.get('AttributeSign_Code') == 12) { // Результат ЭКО
						ecoResultRec = rec;
					}
				});
				// если для УЗИ нет значения атрибута, то
				if (ecoResultRec && (!ecoResultRec.get('AttributeValueLoadParams') || ecoResultRec.get('AttributeValueLoadParams').length == 0 || ecoResultRec.get('AttributeValueLoadParams') == '[]')) {
					if (!options.ignoreUziResult) {
						if (!this.questionWin) {
							this.questionWin = new sw.Promed.BaseForm({
								width: 400,
								modal: true,
								title: langs('Отсутствует результат выполнения УЗИ'),
								resizable: false,
								closable: true,
								layout: 'form',
								autoHeight: true,
								initComponent: function () {
									var win = this;
									this.QuestionForm = new Ext.FormPanel({
										layout: 'form',
										autoHeight: true,
										items: [{
											xtype: 'label',
											style: '{' +
											'display: inline-block;' +
											' margin-left: 33px;' +
											' font-size: 12px;' +
											' margin-top: 10px;' +
											' margin-bottom: 10px;' +
											' font-weight: bold;' +
											'}',
											text: 'Внести данные о наступлении беременности?'
										}, {
											xtype: 'radiogroup',
											hideLabel: true,
											name: 'PregnancyResult',
											columns: 1,
											style: '{' +
											' margin-left: 33px;' +
											' margin-bottom: 10px;' +
											'}',
											items: [
												{
													boxLabel: 'Беременность подтверждена',
													name: 'ch',
													checked: true,
													value: 1
												},
												{boxLabel: 'Беременность не подтверждена', name: 'ch', value: 2}
											]
										}]
									});

									Ext.apply(this, {
										items: [
											this.QuestionForm
										],
										buttons: [{
											handler: function () {
												win.callback(1);
												win.hide();
											},
											text: 'Не указывать результат'
										}, '-', {
											handler: function () {
												if (win.QuestionForm.getForm().findField('PregnancyResult').items.items[0].checked) {
													win.callback(2);
												} else {
													win.callback(3);
												}
												win.hide();
											},
											text: 'Да'
										}]
									});

									sw.Promed.BaseForm.superclass.initComponent.apply(this, arguments);
								},
								show: function () {
									sw.Promed.BaseForm.superclass.show.apply(this, arguments);

									this.callback = arguments[0].callback;
									this.QuestionForm.getForm().reset();
								}
							});
						}

						loadMask.hide();
						wnd.formStatus = 'edit';
						this.questionWin.show({
							callback: function(ignoreUziResult) {
								options.ignoreUziResult = ignoreUziResult;
								switch(ignoreUziResult) {
									case 2:
										ecoResultRec.set('AttributeValueLoadParams', '[{"Attribute_id":"133","Attribute_SysNick":"EKOConfPregn","AttributeValue_Value":2,"AttributeValueType_SysNick":"ident","AttributeValue_TableName":"dbo.EvnUslugaCommon","AttributeVision_id":"646","RecordStatus_Code":0}]');
										ecoResultRec.set('AttributeValueSaveParams', '[{"Attribute_id":"133","Attribute_SysNick":"EKOConfPregn","AttributeValue_Value":2,"AttributeValueType_SysNick":"ident","AttributeValue_TableName":"dbo.EvnUslugaCommon","AttributeVision_id":"646","RecordStatus_Code":0}]');
										ecoResultRec.set('RecordStatus_Code', 2);
										break;
									case 3:
										ecoResultRec.set('AttributeValueLoadParams', '[{"Attribute_id":"133","Attribute_SysNick":"EKOConfPregn","AttributeValue_Value":1,"AttributeValueType_SysNick":"ident","AttributeValue_TableName":"dbo.EvnUslugaCommon","AttributeVision_id":"646","RecordStatus_Code":0}]');
										ecoResultRec.set('AttributeValueSaveParams', '[{"Attribute_id":"133","Attribute_SysNick":"EKOConfPregn","AttributeValue_Value":1,"AttributeValueType_SysNick":"ident","AttributeValue_TableName":"dbo.EvnUslugaCommon","AttributeVision_id":"646","RecordStatus_Code":0}]');
										ecoResultRec.set('RecordStatus_Code', 2);
										break;
								}
								ecoResultRec.commit();
								wnd.doSave();
							}
						});
						return false;
					}
				}
			}
		}

		grid.getStore().clearFilter();
		grid.getStore().filterBy(function(rec) {
			return rec.get('RecordStatus_Code') !== null;
		});

		if ( grid.getStore().getCount() > 0 ) {
			var AttributeSignValueData = getStoreRecords(grid.getStore(), {
				convertDateFields: true,
				exceptionFields: [
					'AttributeSign_Code'
					,'AttributeSign_Name'
					,'AttributeValueLoadParams'
				]
			});

			params.AttributeSignValueData = Ext.util.JSON.encode(AttributeSignValueData);
		}
		grid.getStore().filterBy(function(rec) {
			return !(Number(rec.get('RecordStatus_Code')) == 3);
		});

		loadMask.show();

		base_form.submit({
			failure: function (result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if (action.result) {
					if (action.result.Error_Msg && action.result.Error_Code == 301) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function () {
								this.formStatus = 'edit';
								this.EvnDirectionPanel.expand();
								base_form.findField('EvnUslugaPar_UslugaNum').focus(true);
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: action.result.Error_Msg,
							title: ERR_INVFIELDS_TIT
						});
					} else if (action.result.Error_Msg) {
						sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
					} else {
						sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
					}
				}
			}.createDelegate(this),
			params: params,
			success: function (result_form, action) {
				loadMask.hide();
				if (action.result && action.result.EvnUslugaPar_id > 0) {
					this.FileUploadPanel.listParams = {Evn_id: action.result.EvnUslugaPar_id};
					this.FileUploadPanel.saveChanges();
					params.EvnUslugaPar_id = action.result.EvnUslugaPar_id;
					this.EvnXmlPanel.onEvnSave();
					this.EvnXmlPanel.EvnUslugaPar_id = action.result.EvnUslugaPar_id;
					this.formStatus = 'edit';

					if (!silent) {
						var doHide = true;

						if (!Ext.isEmpty(options) && options.doPrint == true) {
							this.doPrint();
							doHide = false;
						}
						
						if (!Ext.isEmpty(options) && options.openChildWindow) {
							options.openChildWindow();
							doHide = false;
						}
						
						if (doHide) wnd.hide();

						if (options.callback && typeof options.callback === 'function') {
							params.callback = options.callback;
						}

						wnd.callback(params);
					}

				} else {
					sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
				}
			}.createDelegate(this)
		});
	},
	
	refreshFieldsEnable: function() {
		var win = this,
			tarm = win.isTelemedARM;
		var base_form = win.findById('EvnUslugaFuncRequestEditForm').getForm();
		base_form.items.each(function(field){
			field.setDisabled(tarm);
		});
	},

	refreshFieldsVisibility: function(fieldNames) {
		var win = this;
		var base_form = win.findById('EvnUslugaFuncRequestEditForm').getForm();
		if (typeof fieldNames == 'string') fieldNames = [fieldNames];

		var action = win.action;
		var Region_Nick = getRegionNick();
		win.refreshFieldsEnable();

		base_form.items.each(function(field){			
			if (!Ext.isEmpty(fieldNames) && !field.getName().inlist(fieldNames)) return;

			var value = field.getValue();
			var allowBlank = null;
			var visible = null;
			var enable = null;
			var filter = null;			
			var UslugaComplex_AttributeList_str = base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_AttributeList');
			var UslugaComplex_AttributeList = [];
			if (!Ext.isEmpty(UslugaComplex_AttributeList_str)) {
				UslugaComplex_AttributeList = UslugaComplex_AttributeList_str.split(',');
			}

			switch(field.getName()) {
				case 'EvnUslugaPar_UslugaNum':
					visible = false;
					if (getRegionNick() != 'kz') {
						var panel = Ext.getCmp('EUFREF_TemplPanel');
						if ('mrt'.inlist(UslugaComplex_AttributeList) || 'kt'.inlist(UslugaComplex_AttributeList)) {
							panel.setRECIST(true);
						} else {
							panel.setRECIST(false);
						}
					}
					if (Region_Nick == 'penza') {
						var hasKtOrMrtorPortalNum = UslugaComplex_AttributeList.some(function(attr) {
							return attr.inlist(['kt','mrt','NumberPortal']);
						});
						visible = hasKtOrMrtorPortalNum;
						allowBlank = !hasKtOrMrtorPortalNum;
					}

			if (visible === false && win.formLoaded) {
				value = null;
			}
			if (value != field.getValue()) {
				field.setValue(value);
				field.fireEvent('change', field, value);
			}
			if (allowBlank !== null) {
				field.setAllowBlank(allowBlank);
			}
			if (visible !== null) {
				field.setContainerVisible(visible);
			}
			if (enable !== null) {
				field.setDisabled(!enable || action == 'view');
			}
			if (typeof filter == 'function' && field.store) {
				field.lastQuery = '';
				if (typeof field.setBaseFilter == 'function') {
					field.setBaseFilter(filter);
				} else {
					field.store.filterBy(filter);
				}
			}

					break;
				case 'EUP_MedicalCareFormType_id':
					var hasKtOrMrt = false;
					if(Region_Nick == 'penza') {
						hasKtOrMrt = UslugaComplex_AttributeList.some(function(attr) {
							return attr.inlist(['kt', 'mrt']);
		});
					}
					field.setContainerVisible(hasKtOrMrt);
					if (!base_form.findField('EvnUslugaPar_UslugaNum').getValue() && hasKtOrMrt) {
						field.setValue(3);
						field.setRawValue('Плановая');
					}
					break;
				
				case 'EvnUsluga_Kolvo':
					var isMultipleUsl = UslugaComplex_AttributeList.some(function(attr) {
						return attr == 'MultipleUsl';
					});
					field.setDisabled(!isMultipleUsl);
					break;
			}
		});
	},
	
	// Открпление DICOM объекта
	disassociateStudyFromEvnUslugaPar: function(study_uid) {
		var ASStore = this.AssociatedResearches.getGrid().getStore(),
			record = ASStore.getAt(ASStore.findBy(function(rec) { return rec.get('study_uid') == study_uid; }));
		if(this.isTelemedARM) return false;
		this.DicomPanel.getGrid().getStore().add(record);
		ASStore.remove(record);
		this.checkEvnXmlPanelAvailibility();
		
		if ( !this.hasDicomAssociatedResearches() ) {
			sw.swMsg.confirm(lang['vnimanie'],lang['prikreplennyih_izobrajeniy_bolshe_ne_ostalos_pereyti_v_rejim_rabotyi_bez_tsifrovyih_izobrajeniy'],function(btn){
				if ( btn == 'yes' ) {
					this._setResearchFormat('analog');
					return true;
				}
				
				return false;
			},this);
		}
	},
	
	// Прикрепление DICOM объекта
	associateStudyFromEvnUslugaPar: function(){
		var record = this.DicomPanel.getGrid().getSelectionModel().getSelected();
		if(this.isTelemedARM) return false;
		var DicomSelectionModel = this.DicomPanel.getGrid().getSelectionModel();
		if (!DicomSelectionModel.hasSelection()) {
			return false
		}
		var DicomSelection = DicomSelectionModel.getSelections();

		for (var i=0; i<DicomSelection.length; i++)  {
			
			this.DicomPanel.getGrid().getStore().remove(DicomSelection[i]);
			
			DicomSelection[i].set('deleteLink','<img src="img/icons/unchain16.png" onclick="Ext.getCmp(\''+this.id+'\').disassociateStudyFromEvnUslugaPar(\''+DicomSelection[i].get('study_uid')+'\');" class=\'additionalGridRowHoverIcon\'>');
			this.AssociatedResearches.getGrid().getStore().add(DicomSelection[i]);
		}
		this.checkEvnXmlPanelAvailibility();
		
//		if ( !record ) {
//			sw.swMsg.alert('Ошибка', 'Вы должны отметить хотя бы одно исследование.');
//			return false;
//		}
		
//		var loadMask = new Ext.LoadMask(this.getEl(), {msg: 'Сохраняем идентификаторы выбранных исследований.'});
//		loadMask.show();
//		
//		var params = {
//			EvnUslugaPar_id: this.findById('EvnUslugaFuncRequestEditForm').getForm().findField('EvnUslugaPar_id').getValue(),
//			study_uid: record.data.study_uid
//		}
//		
//		var obj = this;
//
//		Ext.Ajax.request({
//			callback: function(options, success, response) {
//				if ( success ) {
//					var response_obj = Ext.util.JSON.decode(response.responseText);
//					loadMask.hide();
//					if ( response_obj.success == false ) {
//						sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : 'Во время добавления исследования произошла ошибка. Обратитесь к администратору.');
//					} else {
//						sw.swMsg.alert('Сообщение', 'Исследование успешно добавлено');
//						obj.searchStudies();
//					}
//				} else {
//					loadMask.hide();
//					sw.swMsg.alert('Ошибка', 'При добавлении исследования возникли ошибки');
//				}
//			},
//			params: params,
//			url: '/?c=Dicom&m=associateStudyFromEvnUslugaPar'
//		});
	},
	
	
	searchStudies: function(){
		var params = {};
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		params.Lpu_id = getGlobalOptions().lpu_id;//this.findById('EvnUslugaFuncRequestEditForm').getForm().findField('Lpu_id').getValue(),
		params.MedService_id = this.MedService_id; //#146135
		params.EvnUslugaPar_id = this.findById('EvnUslugaFuncRequestEditForm').getForm().findField('EvnUslugaPar_id').getValue();
		this.DicomPanel.removeAll({clearAll:true, addEmptyRecord:false});
		this.DicomPanel.loadData({globalFilters: params,callback: function(r,opts,success){				
				if ((r.length == 1)&&(typeof r[0]['json'] != undefined)&&(typeof r[0]['json']['Error_Msg'] != undefined)&&( r[0]['json']['Error_Msg']!=null )) {
					sw.swMsg.alert(lang['oshibka'], r[0]['json']['Error_Msg']);
				}
		}});
	},
	initMasks: function() {
		if (!this.masksInitiated) {
			maskCfg = {
				msgCls:'hiddenMessageForLoadMask'
			};
			this.DicomPanel.loadMask  = new Ext.LoadMask(Ext.get(this.DicomPanel.id),maskCfg);
			this.AssociatedResearches.loadMask  = new Ext.LoadMask(Ext.get(this.AssociatedResearches.id),maskCfg);
		}
	},
	
	checkEvnXmlPanelAvailibility: function() {
		return true; // заполнение протокола всегда доступно
	},
	
	openEvnFuncRequestEditWindowViewMode: function() {
		var form = this;
		var action = 'view';
		sw.Applets.uec.stopUecReader();
		sw.Applets.BarcodeScaner.stopBarcodeScaner();
		
		var params = new Object();
		
		params.MedService_id = this.MedService_id;
		
		params.action = action;
		params.callback = function(data) {};
        params.swWorkPlaceFuncDiagWindow = form;





		params.EvnFuncRequest_id = this.EvnFuncRequest_id;
		params.EvnDirection_id = this.EvnDirection_id;
		params.MedService_id = this.MedService_id;
		
		params.onHide = function() {
			sw.Applets.uec.startUecReader();
			sw.Applets.BarcodeScaner.startBarcodeScaner();
		}
		
		//Жирный костыль. Описание в ревизии к задаче #28832
		if (!window['swEvnFuncRequestEditWindow']) {
			loadJsCode({objectClass:'swEvnFuncRequestEditWindow',objectName:'swEvnFuncRequestEditWindow'},function(success){
				if (success) {
					getWnd('swEvnFuncRequestEditNonModalWindow').show(params);
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_zagruzki_fayla_obratites_k_administratoru'], Ext.emptyFn );
				}
			});
		} else {
			getWnd('swEvnFuncRequestEditNonModalWindow').show(params);
		}	
		
		
	},
	
	
	loadResearchByUid: function(data) {
		
		if (!data['study_uid']) {
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuet_identifikator_issledovaniya'], Ext.emptyFn );
			return false;
		}
		if (!data['LpuEquipmentPacs_id']) {
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuet_identifikator_ustroystva_pacs'], Ext.emptyFn );
			return false;
		}
		
		/*
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();
		
		Ext.Ajax.request({
			url: '/?c=Dicom&m=getStudyView',
			params:{
				'study_uid':data['study_uid'],
				'LpuEquipmentPacs_id':data['LpuEquipmentPacs_id']
			},
			success: function(response, opts) {
				var resp = JSON.parse(response.responseText);
				console.log(resp);
				this.DicomViewerPanel.getEl().update(resp.html,false,function(){
					$('#EvnUslugaFuncRequestDicomViewerEditWindow .EvnUslugaparFunctRequest_wraper .sidebar1 .active img').click()
				});
				this.DicomViewerPanel.doLayout();
				loadMask.hide();
				this.ResearchRegion.expand();
			}.createDelegate(this),
			failure: function(response, opts) {
				loadMask.hide();
			}.createDelegate(this)
		});
		
		this.DicomViewerPanel.getEl().up('div').addClass('EvnUslugaparFunctRequest_position');
		*/
		//this.DicomViewerPanel.loadImages(data);
	},
	
	showSeriesInStudy: function(data){
		var me = this,
			dicomPanel = this.DicomViewerPanel,
			seriesInStudy = new Ext.Window({
			width:700,
			title:lang['issledovaniya'],
			modal: false,
			draggable:false,
			resizable:false,
			closable : true,
			seriesData: [],
			/*getInstances: function(instData, r){
				var win = this;
				Ext.Ajax.request({
					url: '/?c=Dicom&m=getInstancesForDicomViewer',
					params:{
						'study_uid':instData['study_uid'],
						'seriesUID':instData['seriesUID'],
						'LpuEquipmentPacs_id':instData['LpuEquipmentPacs_id']
					},
					success: function(response, opts) {
						var resp = JSON.parse(response.responseText);
						//win.loadMask.hide();
						if(resp.data)
						{
							win.seriesData[r].instances = resp.data;
							dicomPanel.setStudies(win.seriesData, r);
							seriesInStudy.close();
							me.ResearchRegion.expand();
							//win.seriesData = resp.data;
							//win.findById('dicomSeriesGrid').store.loadData(resp);
						}
					}.createDelegate(win),
					failure: function(response, opts) {
						win.loadMask.hide();
					}.createDelegate(win)
				});
			},*/
			initComponent: function()
			{
				var win = this;
				
				win.on('afterlayout', function(){
					win.loadMask = new Ext.LoadMask(win.getEl(), {msg:lang['podojdite_idet_zagruzka']});
				win.loadMask.show();
				})
				
				Ext.Ajax.request({
					url: '/?c=Dicom&m=getSeriesForDicomViewer',
					params:{
						'study_uid':data['study_uid'],
						'LpuEquipmentPacs_id':data['LpuEquipmentPacs_id']
					},
					success: function(response, opts) {
						var resp = JSON.parse(response.responseText);
						win.loadMask.hide();
						if(resp.data)
						{
							win.seriesData = resp.data;
							win.findById('dicomSeriesGrid').store.loadData(resp);
						}
					}.createDelegate(win),
					failure: function(response, opts) {
						win.loadMask.hide();
					}.createDelegate(win)
				});
				
				win.bbar = [
					'->',
					{
						text:lang['vyibrat'],
						name:'btnSeriesSelect',
						iconCls: 'ok16',
						xtype: 'button',
						handler: function(){
							var grid = seriesInStudy.find('xtype','grid')[0],
								rec = grid.getSelectionModel().getSelected(),
								r = grid.store.indexOf(rec);
							
							if(rec){							
								dicomPanel.setStudies(win.seriesData, r);
								seriesInStudy.close();
								me.ResearchRegion.expand();
							}
							
						}
				}];
				
				Ext.apply(win, {
					items:[{
						xtype: 'grid',
						id: 'dicomSeriesGrid',
						loadMask: true,
						columns: [
							//{dataIndex: 'CmpCallCard_prmDate', type: 'date', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i'), header: 'Дата время', width: 110},
							{dataIndex: 'seriesNumber', header: lang['nomer'], width: 100},
							{dataIndex: 'seriesDescription', header: lang['opisanie'], width: 250},
							{dataIndex: 'numberOfInstances', header: lang['kol-vo_snimkov'], width: 130},
							{dataIndex: 'modality', header: lang['modalnost'], width: 120}
						],
						store: new Ext.data.JsonStore({
							autoLoad: false,
							root: 'data',
							fields: [
								{name: 'seriesNumber', type: 'string'},
								{name: 'seriesDescription', type: 'string'},							
								{name: 'numberOfInstances', type: 'int'},
								{name: 'modality', type: 'string'},
								{name: 'seriesUID', type: 'string'}						
							],
							baseParams:{
								'study_uid':data['study_uid'],
								'LpuEquipmentPacs_id':data['LpuEquipmentPacs_id']
							},
							url: '/?c=Dicom&m=getSeriesForDicomViewer'
						}),
						height: 250,
						view: new Ext.grid.GridView({
							forceFit: false
						}),
						listeners: {
							'celldblclick': function(g,r){
								var rec = g.getStore().getAt(r);						
								if(rec){
									var instData = data;
									instData.seriesUID = rec.get('seriesUID');
									//win.getInstances(instData, r);
									dicomPanel.setStudies(win.seriesData, r);
									me.ResearchRegion.expand();
									seriesInStudy.close();
								}								
							}.createDelegate(win)
						}
					}]
				});
				
				sw.Promed.DicomViewerPanel.superclass.initComponent.apply(win, arguments);
			}
		});
		seriesInStudy.show();
	},
	
	calculateAssociatedResearchesPanelHeight: function(store) {
		var rowCount = store.getCount(),
			minRowCount = 2,
			maxRowCount = 4,
			setRowCount = (rowCount<minRowCount)?minRowCount:((rowCount>maxRowCount)?maxRowCount:rowCount)
		this.checkEnableTelemedButtons();
		this.AssociatedResearches.setHeight(50+setRowCount*21);
		this.AssociatedResearches.getGrid().setHeight(50+setRowCount*21);
	},
	
	calculateDicomPanelHeight: function(store) {
		var rowCount = store.getCount(),
			minRowCount = 2,
			maxRowCount = 6,
			setRowCount = (rowCount<minRowCount)?minRowCount:((rowCount>maxRowCount)?maxRowCount:rowCount)
		
		this.DicomPanel.setHeight(50+setRowCount*21);
		this.DicomPanel.getGrid().setHeight(50+setRowCount*21);
	},
	
	moveToConsult : function() {
		var win = this,
			base_form = win.findById('EvnUslugaFuncRequestEditForm').getForm(),
			infopanel = win.ExtendedPersonInformationPanelShort,
			personData = {};
		if(Ext.isEmpty(win.userMedStaffFact)) {
			return false;
		}
		
        personData.Person_id = infopanel.getFieldValue('Person_id');
        personData.Server_id = infopanel.getFieldValue('Server_id');
        personData.PersonEvn_id = infopanel.getFieldValue('PersonEvn_id');
        personData.Person_IsDead = !Ext.isEmpty(infopanel.getFieldValue('Person_deadDT'));
        personData.Person_Firname = infopanel.getFieldValue('Person_Firname');
        personData.Person_Secname = infopanel.getFieldValue('Person_Secname');
        personData.Person_Surname = infopanel.getFieldValue('Person_Surname');
        personData.Person_Birthday = infopanel.getFieldValue('Person_Birthday');
        personData.userMedStaffFact = win.userMedStaffFact;

        var directionData = {
            EvnDirection_pid: base_form.findField('EvnUslugaPar_id').getValue()
			,DopDispInfoConsent_id: null //?
            ,Diag_id: base_form.findField('Diag_id').getValue() || null //?
            ,DirType_id: 17 //на удаленную консультацию
            ,MedService_id: win.MedService_id
            ,MedStaffFact_id: win.userMedStaffFact ? win.userMedStaffFact.MedStaffFact_id : null
            ,MedPersonal_id: win.userMedStaffFact ? win.MedPersonal_id : null
            ,LpuSection_id: win.userMedStaffFact ? win.userMedStaffFact.LpuSection_id : null
			,ARMType_id: win.userMedStaffFact ? win.userMedStaffFact.ARMType_id : win.ARMType_id
			,ARMType_Code: win.userMedStaffFact ? win.userMedStaffFact.ARMType_Code : win.ARMType_Code
			,Lpu_sid: getGlobalOptions().lpu_id
			,withDirection: true
        };
        directionData.Person_id = personData.Person_id;
        directionData.PersonEvn_id = personData.PersonEvn_id;
        directionData.Server_id = personData.Server_id;
		var onDirection = function () {
			win.hide();
		}
			
		getWnd('swUslugaComplexMedServiceListWindow').show({
			userMedStaffFact: win.userMedStaffFact,
			personData: personData,
			dirTypeData: {DirType_id: 17, DirType_Code: 13},
			directionData: directionData,
			onDirection: onDirection
		});
	},
    doPrint: function(){
        var params = {},
            _this = this;

            params.object =	'EvnUslugaPar';
            params.object_id = 'EvnUslugaPar_id';
            params.object_value	= this.findById('EvnUslugaFuncRequestEditForm').getForm().findField('EvnUslugaPar_id').getValue();
            params.view_section = 'main';

        Ext.Ajax.request({
            failure: function(response, options) {
                //loadMask.hide();
                sw.swMsg.alert(lang['oshibka'], lang['pri_pechati_uslugi_proizoshla_oshibka']);
            },
            params: params,
            success: function(response, options) {

                _this.formStatus = 'edit';

                if ( response.responseText ) {
                    var result  = Ext.util.JSON.decode(response.responseText);
                    if (result.html)
                    {
                        var id_salt = Math.random();
						openNewWindow('<html><head><title>Печатная форма</title><link href="/css/emk.css?'+ id_salt +'" rel="stylesheet" type="text/css" /><style type="text/css">.noprint { display: none; }</style></head><body id="rightEmkPanelPrint">'+ result.html +'</body></html>');

                    } else {
                        sw.swMsg.show({
                            buttons: Ext.Msg.OK,
                            fn: function() {
                                _this.formStatus = 'edit';
                            }.createDelegate(this),
                            icon: Ext.Msg.WARNING,
                            msg: lang['ne_udalos_poluchit_soderjanie_uslugi'],
                            title: ERR_INVFIELDS_TIT
                        });
                        return false;
                    }
                } else {
                    sw.swMsg.show({
                        buttons: Ext.Msg.OK,
                        fn: function() {
                            _this.formStatus = 'edit';
                        }.createDelegate(this),
                        icon: Ext.Msg.WARNING,
                        msg: lang['oshibka_pri_pechati_uslugi'],
                        title: ERR_INVFIELDS_TIT
                    });
                    return false;
                }

            }.createDelegate(this),
            url: '/?c=Template&m=getEvnForm'
        });
    },
	_setResearchFormat: function(value) {
		var base_form = this.findById('EvnUslugaFuncRequestEditForm').getForm();

		switch (value) {
			case 'digital':
				this.DicomPanel.getGrid().getSelectionModel().unlock();
				this.DicomPanel.loadMask.hide();
				this.AssociatedResearches.loadMask.hide();				
				this.checkEvnXmlPanelAvailibility();
				base_form.findField('EvnUslugaPar_Regime').setValue(1);
				
				// Проверяем, наличие прикрепленных DICOM объектов
				var ar_store = this.AssociatedResearches.getGrid().getStore();
				var dicom_attached = ar_store.data.length ? true : false;
			break;
			
			case 'analog':
				this.DicomPanel.getGrid().getSelectionModel().lock();
				this.DicomPanel.loadMask.show();
				this.AssociatedResearches.loadMask.show();
				base_form.findField('EvnUslugaPar_Regime').setValue(2);
			break;
			
			default:
				sw.swMsg.alert(lang['oshibka'], lang['nevernyiy_tip']);
			break;
		}
	},
	
	_getResearchFormat: function() {
		var base_form = this.findById('EvnUslugaFuncRequestEditForm').getForm();
		if (base_form.findField('MedProductCard_id').getFieldValue('PrincipleWorkType_id') == 2) {
			return 'digital';
		} else {
			return 'analog';
		}
	},
	
	// Есть прикрепленные DICOM объекты?
	hasDicomAssociatedResearches: function(){
		return this.AssociatedResearches.getGrid().getStore().getCount() ? true : false;
	},
	
	// Выбран ли какой-то шаблоа протокола?
	hasEvnXmlPanelTemplate: function(){
		return this.EvnXmlPanel._XmlTemplate_id ? true : false;
	},
	
	setSaveButtonDisabled: function(disable){
		this.buttons[0].setDisabled(disable);
	},
		
	initComponent: function() {
		var win = this;
		var _this = this,
			wnd = this;
		
		this.FileUploadPanel = new sw.Promed.FileUploadPanel({
			id: this.id+'_FileUploadPanel',
			win: this,
			buttonAlign: 'left',
			maxHeight: 150,
			buttonLeftMargin: 100,
			labelWidth: 100,
			commentTextfieldWidth: 250,
			folder: 'evnmedia/',
			style: 'background: transparent',
			dataUrl: '/?c=EvnMediaFiles&m=loadEvnMediaFilesListGrid',
			saveUrl: '/?c=EvnMediaFiles&m=uploadFile',
			saveChangesUrl: '/?c=EvnMediaFiles&m=saveChanges',
			deleteUrl: '/?c=EvnMediaFiles&m=deleteFile'
		});
		
		// Тулбар для даты
		this.dateMenu = new Ext.form.DateRangeField({
			width: 150,
			fieldLabel: lang['period'],
			plugins: [
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			]
		});
		this.dateMenu.addListener('keydown',function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.searchStudies();
			}
		}.createDelegate(this));
		this.dateMenu.addListener('select',function(){
			this.searchStudies();
		}.createDelegate(this));
		
		
		this.WindowToolbar = new Ext.Toolbar({
			cls: 'ToolbarWithToggleSlider',
			items: [
				this.dateMenu,
				'-',
				{
					xtype: 'tbfill'
				}
			]
		});

		
		// Дикомовские запросики
		
		this.DicomPanel = new sw.Promed.ViewFrame({
			id: this.id + '_DicomPanel',
			focusOnFirstLoad:false,
			toolbar: true,
			autoExpandColumn: 'autoexpand',
			selectionModel: 'multiselect',
			useEmptyRecord: false,
			autoWidth: true,
			autoLoadData: false,
			paging: false,
			border: false,
			stripeRows: true,
			height: 91,
			cls: 'additionalGridRowHoverClass',
			stringfields: [
				{ header: 'Study UID', name: 'study_uid',  hidden: true, key: true, hideable: false },
				//{ header: 'Идентификатор', name: 'study_id', width: 200, hideable: false },
				{ header: lang['data'], name: 'study_date', hideable: false,width: 70 },
				//{ header: 'Время', name: 'study_time', hideable: false, width: 60 },
				{ header: lang['imya_patsienta'], name: 'patient_name', width: 200, hideable: false },
				{ header: lang['opisanie'], name: 'study_description', hideable: false },
				{ header: lang['modalnost'], name: 'modality', hideable: false, width: 100 },
				
				{ header: lang['identifikator_patsienta'], name: 'patient_id', width: 150, hideable: false },
				//{ header: 'Количество срезов', width:120, name: 'number_of_study_related_instances', hideable: false},
				{ header: '', name: 'link_to_oviyam', hideable: false, width:120},
				{ name: 'LpuEquipmentPacs_id', hidden: true, hideable: false }
			],
			dataUrl: '/?c=Dicom&m=remoteStudy',
			totalProperty: 'totalCount',
			actions: [
				{ name: 'action_add', text: lang['prikrepit'], tooltip: lang['prikrepit_ins'], icon: 'img/icons/chain16.png', disabled: true, handler: this.associateStudyFromEvnUslugaPar.createDelegate(this) },
				{ name: 'action_refresh', text: lang['pokazat_obnovit'], handler: this.searchStudies.createDelegate(this) },
				{ name: 'action_view', hidden: true, disabled: true },
				{ name: 'action_edit', hidden: true, disabled: true },
				{ name: 'action_delete', hidden: true, disabled: true },
				{ name: 'action_print', hidden: true, disabled: true }
			]
		});
		
		this.DicomPanel.getGrid().getStore().on('update',this.calculateDicomPanelHeight.createDelegate(this));
		this.DicomPanel.getGrid().getStore().on('add',this.calculateDicomPanelHeight.createDelegate(this));
		this.DicomPanel.getGrid().getStore().on('remove',this.calculateDicomPanelHeight.createDelegate(this));
		this.DicomPanel.getGrid().getStore().on('load',this.calculateDicomPanelHeight.createDelegate(this));
		
		this.DicomPanel.ViewGridPanel.getStore().on('load',function(store,records,opts){
			store.filterBy(function(record,ind){
				var base_form = this.findById('EvnUslugaFuncRequestEditForm').getForm();
				var UslugaComplex_Code = base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Code');
				var numberObjects = this.DicomPanel.getGrid().getStore().getCount();
				if (getRegionNick() == 'ufa' && UslugaComplex_Code && UslugaComplex_Code.inlist(['A05.10.006', 'A05.10.002', 'A05.10.004']) && numberObjects == 1 && this.AssociatedResearches.getGrid().getStore().getCount() == 0) {
					record.set('deleteLink','<img src="img/icons/unchain16.png" onclick="Ext.getCmp(\''+this.id+'\').disassociateStudyFromEvnUslugaPar(\''+record.get('study_uid')+'\');" class=\'additionalGridRowHoverIcon\'>');
					this.AssociatedResearches.getGrid().getStore().add(record);
					this.checkEvnXmlPanelAvailibility();
				}
				if(records[0].json.Error_Message) win.findById('EUFREF_DicomObj').setDisabled(true); //если есть сообщение об ошибке (отсутствует PACS в МО), то поле не доступно
				return ((this.AssociatedResearches.getGrid().getStore().findBy(function(rec) { return rec.get('study_uid') == record.get('study_uid'); }))===-1);
			}.createDelegate(this));
		}.createDelegate(this));
		
		this.DicomPanel.getGrid().getSelectionModel().on('rowselect',function(sm,rowIndex,rec){
			this.DicomPanel.getAction('action_add').setDisabled( ( rec.get('study_uid') == null ? true : false ) );
		}.createDelegate(this));
		
		
		this.DicomPanel.getGrid().on('rowdblclick',function(grid,rowIndex,evt){
			var rec = grid.getStore().getAt(rowIndex);
			this.showSeriesInStudy({
				'study_uid': rec.get('study_uid'),
				'LpuEquipmentPacs_id': rec.get('LpuEquipmentPacs_id'),
				'patient_id': rec.get('patient_id')				
			});
		}.createDelegate(this));
		
		this.DicomPanel.ViewGridModel.on('selectionchange', function(obj) {
			if (obj.getCount()==0) { 
				this.DicomPanel.ViewActions.action_add.setDisabled(true);
			} else {
				this.DicomPanel.ViewActions.action_add.setDisabled(false);
			}
		}.createDelegate(this));
		
			
			
		
		this.AssociatedResearches = new sw.Promed.ViewFrame({
			title: lang['prikreplennyie_izobrajeniya'],
			id: this.id + '_AssociatedResearchesGrid',
			focusOnFirstLoad:false,
			autoExpandColumn: 'autoexpand',
			useEmptyRecord: false,
			height:91,
			toolbar: false,
			autoLoadData: false,
			paging: false,
			border: false,
			stripeRows: true,
			dataUrl: '/?c=Dicom&m=getAssociatedResearches',
			cls: 'additionalGridRowHoverClass',
			stringfields: [
				{ header: 'Study UID', key: true, name: 'study_uid',  hidden: true, hideable: false },
				{ header: lang['data'], width: 100, name: 'study_date', hideable: false},
				{ header: lang['vremya'], width: 100,  name: 'study_time', hideable: false},
				{ header: lang['imya_patsienta'], name: 'patient_name', width: 300, hideable: false },
				{ header: '', name: 'deleteLink', id:'deleteLink', hideable: false},
				{ header: '', name: 'link_to_oviyam', hideable: false, width:120},
				{ header: '', name: 'link_to_digipacs', id: 'link_to_digipacs', hideable: false, width: 120},
				{ name: 'LpuEquipmentPacs_id', hidden: true, hideable: false },
				{ name: 'digiPacs_ip', hidden: true, hideable: false }
			],
			totalProperty: 'totalCount'
		});
		
		this.AssociatedResearches.ViewGridStore.on('update',this.calculateAssociatedResearchesPanelHeight.createDelegate(this));
		this.AssociatedResearches.ViewGridStore.on('add',this.calculateAssociatedResearchesPanelHeight.createDelegate(this));
		this.AssociatedResearches.ViewGridStore.on('remove',this.calculateAssociatedResearchesPanelHeight.createDelegate(this));
		this.AssociatedResearches.ViewGridStore.on('load',this.calculateAssociatedResearchesPanelHeight.createDelegate(this));
		
		this.AssociatedResearches.getGrid().on('rowdblclick',function(grid,rowIndex,evt){
			var rec = grid.getStore().getAt(rowIndex);
			this.showSeriesInStudy({
				'study_uid': rec.get('study_uid'),
				'LpuEquipmentPacs_id': rec.get('LpuEquipmentPacs_id'),
				'patient_id': rec.get('patient_id')				
			});
		}.createDelegate(this));

		this.AssociatedResearches.ViewGridPanel.getStore().on('load',function(store,records,opts){
			for (var i=0; i<records.length;i++) {
				records[i].set('deleteLink','<img src="img/icons/unchain16.png" onclick="Ext.getCmp(\''+this.id+'\').disassociateStudyFromEvnUslugaPar(\''+records[i].get('study_uid')+'\');" class=\'additionalGridRowHoverIcon\'>');
				records[i].commit();

				if(!Ext.isEmpty(records[i].get('digiPacs_ip'))) {
					var linkDigiPacs = 'http://' + records[i].get('digiPacs_ip') + '/#/viewer/token-auth?token=user&n=%2Fviewer%2Fredirect-to-image-view%3FStudy%3D' + records[i].get('study_uid') + '%26serverName%3DPACS';

					records[i].set('link_to_digipacs', '<a href="' + linkDigiPacs + '" target="_blank">Cнимок в DigiPacs</a>');
					records[i].commit();
				}
			}
		}.createDelegate(this));

		/*@todo потом перенести в панели*/
		this.DicomViewerPanel = new sw.Promed.DicomViewerPanel();

        this.EvnXmlPanel = new sw.Promed.EvnXmlPanel({
            autoHeight: true,
            border: true,
            collapsible: true,
			biRadsEnabled: true,
			consultFunction: function() {
				win.moveToConsult();
			},
			loadMask: {},
            id: 'EUFREF_TemplPanel',
            layout: 'form',
            title: lang['protokol'],
            ownerWin: this,
            options: {
                XmlType_id: sw.Promed.EvnXml.EVN_USLUGA_PROTOCOL_TYPE_ID, // только протоколы услуг
                EvnClass_id: 47 // документы и шаблоны только категории параклинические услуги
            },
			signEnabled: true,
            onAfterLoadData: function(panel){
                var bf = this.findById('EvnUslugaFuncRequestEditForm').getForm();
                bf.findField('XmlTemplate_id').setValue(panel.getXmlTemplateId());
                panel.expand();
				if (getRegionNick() != 'kz') {
					var UslugaComplex_AttributeList = [];
					var UslugaComplex_AttributeList_str = bf.findField('UslugaComplex_id').getFieldValue('UslugaComplex_AttributeList');
					if (!Ext.isEmpty(UslugaComplex_AttributeList_str)) {
						UslugaComplex_AttributeList = UslugaComplex_AttributeList_str.split(',');
					}
					if ('mrt'.inlist(UslugaComplex_AttributeList) || 'kt'.inlist(UslugaComplex_AttributeList)) {
						panel.setRECIST(true);
					} else {
						panel.setRECIST(false);
					}
				}
                if (bf.findField('UslugaComplex_id').getValue() == 201010) {
					panel.setBiRads(true);
				}
            }.createDelegate(this),
            onAfterClearViewForm: function(panel){
                var bf = this.findById('EvnUslugaFuncRequestEditForm').getForm();
                bf.findField('XmlTemplate_id').setValue(null);
				panel.setBiRads(false);
            }.createDelegate(this),
            // определяем метод, который должен создать посещение перед созданием документа с помощью указанного метода
            onBeforeCreate: function (panel, method, params) {
                if (!panel || !method || typeof panel[method] != 'function') {
                    return false;
                }
                var base_form = this.findById('EvnUslugaFuncRequestEditForm').getForm();
                var evn_id_field = base_form.findField('EvnUslugaPar_id');
                var evn_id = evn_id_field.getValue();
				var UslugaComplex_Code = base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Code');
				//для экг всегда выводим "Сохранение результатов"
				var isECG = getRegionNick() == 'ufa' && UslugaComplex_Code && UslugaComplex_Code.inlist(['A05.10.006', 'A05.10.002', 'A05.10.004']);
				var me = this;
				if( !isECG && ((me.electronicQueueData && this.electronicQueueData.electronicTalonStatus_id!=4)
					|| ( Ext.isEmpty(base_form.findField('MedStaffFact_id').getValue()) && Ext.isEmpty(base_form.findField('MedPersonal_sid').getValue())
					))
				) {
					panel[method](params);
				} else {
					sw.swMsg.show({
						buttons: {yes: 'Сохранить', no: 'Отмена'},
						icon: Ext.MessageBox.WARNING,
						fn: function ( buttonId ) {
							if ( buttonId == 'yes' ) {
								me.doSave({
									openChildWindow: function() {
										panel.setBaseParams({
											userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
											UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
											Server_id: base_form.findField('Server_id').getValue(),
											Evn_id: evn_id_field.getValue()
										});
										panel[method](params);
									}.createDelegate(me)
								});
							} else {
								if (evn_id && evn_id > 0) {
									panel[method](params);
								}
							}
						},
						msg: "Для правильной работы с шаблонами будет выполнено автоматическое сохранение результатов",
						title: 'Сохранение результатов'
					});
				}
				return true;
			}.createDelegate(this)
		});
		
		this.EvnDirectionPanel = new sw.Promed.Panel({
			autoHeight: true,
			bodyStyle: 'padding-top: 0.5em;',
			border: true,
			title: lang['osnovnyie_dannyie'],
			collapsed: false,
			collapsible: true,
			// hidden: true,
			id: 'EUFREF_EvnDirectionPanel',
			layout: 'form',
			items: [{
				name: 'PrehospDirect_id',
				value: 0,
				xtype: 'hidden'
			}, {
				allowBlank: true,
				value: null,
				fieldLabel: lang['kompleksnaya_usluga'],
				name: 'UslugaComplex_id',
				listeners: {
					'change': function (combo, newValue, oldValue) {
                        var Diag_AllowBlank = true;

						if (getRegionNick() == 'ekb') {
							var base_form = win.findById('EvnUslugaFuncRequestEditForm').getForm();
							var Diag_Code = null;
							var UslugaComplex_Code = base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Code');
							switch (UslugaComplex_Code) {
								case 'A06.09.006':
								case 'A06.09.006.888':
									Diag_Code = 'Z11.1';
                                    Diag_AllowBlank = false;
									break;
								case 'A06.20.004':
								case 'A06.20.004.888':
									Diag_Code = 'Z01.8';
                                    Diag_AllowBlank = false;
									break;
								case 'A06.30.003.001':
								case 'A06.30.003.002':
									Diag_Code = 'Z03.8';
									break;
							}

                            base_form.findField('Diag_id').setAllowBlank(Diag_AllowBlank);

							if (Ext.isEmpty(base_form.findField('Diag_id').getValue())) {
                                if (!Ext.isEmpty(base_form.findField('DirectionDiag_id').getValue())) {
                                    var diag_id = base_form.findField('DirectionDiag_id').getValue();
                                    base_form.findField('Diag_id').getStore().load({
                                        params: {where: "where Diag_id = " + diag_id},
                                        callback: function () {
                                            if (base_form.findField('Diag_id').getStore().getCount() > 0) {
                                                base_form.findField('Diag_id').setValue(diag_id);
                                                base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), base_form.findField('Diag_id').getStore().getAt(0), 0);
                                                base_form.findField('Diag_id').onChange();
                                            }
                                        }
                                    });
                                } else if (Diag_Code) {
									base_form.findField('Diag_id').getStore().load({
										params: {where: "where Diag_Code = '" + Diag_Code + "'"},
										callback: function () {
											if (base_form.findField('Diag_id').getStore().getCount() > 0) {
												var diag_id = base_form.findField('Diag_id').getStore().getAt(0).get('Diag_id');
												base_form.findField('Diag_id').setValue(diag_id);
												base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), base_form.findField('Diag_id').getStore().getAt(0), 0);
												base_form.findField('Diag_id').onChange();
											}
										}
									});
								}
							}

							base_form.findField('Mes_id').lastQuery = 'This query sample that is not will never appear';
							base_form.findField('Mes_id').getStore().removeAll();
							base_form.findField('Mes_id').getStore().baseParams.UslugaComplex_id = newValue;
							base_form.findField('Mes_id').getStore().baseParams.query = '';
						}

						win.refreshFieldsVisibility(['EvnUslugaPar_UslugaNum']);
						win.refreshFieldsVisibility(['EUP_MedicalCareFormType_id']);
						win.refreshFieldsVisibility(['EvnUsluga_Kolvo']);

						win.findById('EvnUslugaFuncRequestEditForm').getForm().findField('FSIDI_id').checkVisibilityAndGost(combo.value);
						return true;
					}
				},
				to: 'EvnUslugaPar',
				listWidth: 600,
				tabIndex: 12,
				width: 500,
				xtype: 'swuslugacomplexnewcombo'
			}, {
				xtype: 'swfsidicombo',
				hiddenName: 'FSIDI_id',
				width: 480,
				listWidth: 500,
				labelWidth: 250,
				hideOnInit: true
			}, {
				comboSubject: 'UslugaMedType',
				enableKeyEvents: true,
				hidden: getRegionNick() !== 'kz',
				fieldLabel: langs('Вид услуги'),
				hiddenName: 'UslugaMedType_id',
				allowBlank: getRegionNick() !== 'kz',
				lastQuery: '',
				tabIndex: 13,
				typeCode: 'int',
				width: 450,
				xtype: 'swcommonsprcombo'
			},{
				ownerWindow: wnd,
				xtype: 'swduplicatedfieldpanel',
				border: false,
				fieldLbl: 'Номер зуба',
				fieldName: 'ToothNumEvnUsluga_ToothNum',
				id: wnd.id + '_' + 'ToothNumFieldsPanel',
				labelWidth: 160,
				viewMode: true,
				fullScreenWnd: true
			}, {
				allowBlank: true,
				editable: true,
				codeField: 'AccountingData_InventNumber',
				displayField: 'MedProductClass_Name',
				fieldLabel: lang['meditsinskoe_izdelie'],
				hiddenName: 'MedProductCard_id',
				id: 'MedProductCard_combo',
				store: new Ext.data.Store({
					autoLoad: false,
					reader: new Ext.data.JsonReader({
						id: 'MedProductCard_id'
					}, [
						{ name: 'MedProductCard_id', mapping: 'MedProductCard_id', type: 'int' },
						{ name: 'LpuSection_id', mapping: 'LpuSection_id', type: 'int' },
						{ name: 'Resource_id', mapping: 'Resource_id', type: 'int' },
						{ name: 'AccountingData_InventNumber', mapping: 'AccountingData_InventNumber', type: 'string' },
						{ name: 'MedProductClass_Name', mapping: 'MedProductClass_Name', type: 'string' },
						{ name: 'MedProductClass_Model', mapping: 'MedProductClass_Model', type: 'string' },
						{ name: 'PrincipleWorkType_id', mapping: 'PrincipleWorkType_id', type: 'int' }
					]),
					url: '/?c=LpuPassport&m=loadMedProductCard'
				}),
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<table style="border: 0;"><td style="width: 70px"><font color="red">{AccountingData_InventNumber}</font></td><td><h3>{MedProductClass_Name}</h3>{MedProductClass_Model}</td></tr></table>',
					'</div></tpl>'
				),
				triggerAction: 'all',
				valueField: 'MedProductCard_id',
				lastQuery: '',
				width: 500,
				listWidth: 600,
				xtype: 'swbaselocalcombo',
				listeners: {
					'change': function(field, newValue, oldValue) {
						if(field.getFieldValue('PrincipleWorkType_id') == 2) {
							this._setResearchFormat('digital');
						} else {
							this._setResearchFormat('analog');
						}
					}.createDelegate(this)
				}
			}, {
				fieldLabel: 'Повторная подача',
				listeners: {
					'check': function(checkbox, value) {
						if ( getRegionNick() != 'perm' ) {
							return false;
						}

						var base_form = this.findById('EvnUslugaFuncRequestEditForm').getForm();

						var
							EvnUslugaPar_IndexRep = parseInt(base_form.findField('EvnUslugaPar_IndexRep').getValue()),
							EvnUslugaPar_IndexRepInReg = parseInt(base_form.findField('EvnUslugaPar_IndexRepInReg').getValue()),
							EvnUslugaPar_IsPaid = parseInt(base_form.findField('EvnUslugaPar_IsPaid').getValue());

						var diff = EvnUslugaPar_IndexRepInReg - EvnUslugaPar_IndexRep;

						if ( EvnUslugaPar_IsPaid != 2 || EvnUslugaPar_IndexRepInReg == 0 ) {
							return false;
						}

						if ( value == true ) {
							if ( diff == 1 || diff == 2 ) {
								EvnUslugaPar_IndexRep = EvnUslugaPar_IndexRep + 2;
							}
							else if ( diff == 3 ) {
								EvnUslugaPar_IndexRep = EvnUslugaPar_IndexRep + 4;
							}
						}
						else if ( value == false ) {
							if ( diff <= 0 ) {
								EvnUslugaPar_IndexRep = EvnUslugaPar_IndexRep - 2;
							}
						}

						base_form.findField('EvnUslugaPar_IndexRep').setValue(EvnUslugaPar_IndexRep);

					}.createDelegate(this)
				},
				tabIndex: TABINDEX_EUPAREF + 57,
				name: 'EvnUslugaPar_RepFlag',
				xtype: 'checkbox'
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel: lang['data_issledovaniya'],
						format: 'd.m.Y',
						name: 'EvnUslugaPar_setDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: 8,
						width: 100,
						xtype: 'swdatefield',
						listeners: {
							'change': function(field, newValue, oldValue) {
								var base_form = this.findById('EvnUslugaFuncRequestEditForm').getForm();

								var
									index,
									LpuSection_id = base_form.findField('LpuSection_uid').getValue(),
									MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue(),
									MedStaffFact_sid = base_form.findField('MedStaffFact_sid').getValue();

								base_form.findField('LpuSection_uid').clearValue();
								base_form.findField('MedStaffFact_id').clearValue();
								base_form.findField('MedStaffFact_sid').clearValue();

								base_form.findField('LpuSection_uid').getStore().clearFilter();
								base_form.findField('MedStaffFact_id').getStore().clearFilter();
								base_form.findField('MedStaffFact_sid').getStore().clearFilter();

								if ( !Ext.isEmpty(newValue) ) {
									base_form.findField('LpuSection_uid').lastQuery = "This query sample that is not will never appear";
									base_form.findField('LpuSection_uid').setBaseFilterAdvanced(function(rec) {
										var
											ls_dis_date = Date.parseDate(rec.get('LpuSection_disDate'), 'd.m.Y'),
											ls_set_date = Date.parseDate(rec.get('LpuSection_setDate'), 'd.m.Y');

										return (
											(Ext.isEmpty(ls_set_date) || ls_set_date <= newValue)
											&& (Ext.isEmpty(ls_dis_date) || ls_dis_date >= newValue)
										);
									});

									base_form.findField('MedStaffFact_id').lastQuery = "This query sample that is not will never appear";
									base_form.findField('MedStaffFact_id').setBaseFilterAdvanced(function(rec) {
										var
											ls_dis_date = Date.parseDate(rec.get('LpuSection_disDate'), 'd.m.Y'),
											ls_set_date = Date.parseDate(rec.get('LpuSection_setDate'), 'd.m.Y'),
											mp_beg_date = Date.parseDate(rec.get('WorkData_begDate'), 'd.m.Y'),
											mp_end_date = Date.parseDate(rec.get('WorkData_endDate'), 'd.m.Y');

										return (
											(Ext.isEmpty(ls_set_date) || ls_set_date <= newValue)
											&& (Ext.isEmpty(ls_dis_date) || ls_dis_date >= newValue)
											&& (Ext.isEmpty(mp_beg_date) || mp_beg_date <= newValue)
											&& (Ext.isEmpty(mp_end_date) || mp_end_date >= newValue)
										);
									});

									base_form.findField('MedStaffFact_sid').lastQuery = "This query sample that is not will never appear";
									base_form.findField('MedStaffFact_sid').setBaseFilterAdvanced(function(rec) {
										var
											ls_dis_date = Date.parseDate(rec.get('LpuSection_disDate'), 'd.m.Y'),
											ls_set_date = Date.parseDate(rec.get('LpuSection_setDate'), 'd.m.Y'),
											mp_beg_date = Date.parseDate(rec.get('WorkData_begDate'), 'd.m.Y'),
											mp_end_date = Date.parseDate(rec.get('WorkData_endDate'), 'd.m.Y');

										return (
											(Ext.isEmpty(ls_set_date) || ls_set_date <= newValue)
											&& (Ext.isEmpty(ls_dis_date) || ls_dis_date >= newValue)
											&& (Ext.isEmpty(mp_beg_date) || mp_beg_date <= newValue)
											&& (Ext.isEmpty(mp_end_date) || mp_end_date >= newValue)
										);
									});
								}

								if ( !Ext.isEmpty(LpuSection_id) ) {
									index = base_form.findField('LpuSection_uid').getStore().findBy(function(rec) {
										return (rec.get('LpuSection_id') == LpuSection_id);
									});
									if ( index >= 0 ) {
										base_form.findField('LpuSection_uid').setValue(LpuSection_id);
										base_form.findField('LpuSection_uid').disable();
										base_form.findField('LpuSection_uid').fireEvent('change', base_form.findField('LpuSection_uid'), base_form.findField('LpuSection_uid').getValue());
									}
								}

								if ( !Ext.isEmpty(MedStaffFact_id) ) {
									index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
										return (rec.get('MedStaffFact_id') == MedStaffFact_id);
									});
									if ( index >= 0 ) {
										base_form.findField('MedStaffFact_id').setValue(MedStaffFact_id);
										// только если не является ср. мед. персоналом
										if ( !sw.Promed.MedStaffFactByUser.last || sw.Promed.MedStaffFactByUser.last.PostKind_id != 6 ) {
											base_form.findField('MedStaffFact_id').disable();
										}
									}
								}

								if ( !Ext.isEmpty(MedStaffFact_sid) ) {
									index = base_form.findField('MedStaffFact_sid').getStore().findBy(function(rec) {
										return (rec.get('MedStaffFact_id') == MedStaffFact_sid);
									});
									if ( index >= 0 ) {
										base_form.findField('MedStaffFact_sid').setValue(MedStaffFact_sid);
									}
								}

								win.setTumorStageVisibility();
							}.createDelegate(this)
						}
					}]
				}, {
					border: false,
					layout: 'form',
					items: [{
						fieldLabel: lang['vremya'],
						listeners: {
							'keydown': function (inp, e) {
								if ( e.getKey() == Ext.EventObject.F4 ) {
									e.stopEvent();
									inp.onTriggerClick();
								}
							}
						},
						name: 'EvnUslugaPar_setTime',
						onTriggerClick: function() {
							var base_form = this.findById('EvnUslugaFuncRequestEditForm').getForm();
							var time_field = base_form.findField('EvnUslugaPar_setTime');

							if ( time_field.disabled ) {
								return false;
							}

							setCurrentDateTime({
								callback: function() {
									base_form.findField('EvnUslugaPar_setDate').fireEvent('change', base_form.findField('EvnUslugaPar_setDate'), base_form.findField('EvnUslugaPar_setDate').getValue());
								},
								dateField: base_form.findField('EvnUslugaPar_setDate'),
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
						tabIndex: 9,
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}]
				}]
			}, {
				allowDicimals: false,
				allowNegative: false,
				xtype: 'numberfield',
				name: 'EvnUslugaPar_UslugaNum',
				fieldLabel: '№ услуги из журнала выполненных услуг',
				width: 100
			}, {
				allowBlank: true,
				editable: true,
				hiddenName: 'EUP_MedicalCareFormType_id',
				width: 100,
				listWidth: 100,
				xtype: 'swmedicalcareformtypecombo'
			}, {
				displayField: 'Org_Name',
				editable: false,
				enableKeyEvents: true,
				fieldLabel: lang['organizatsiya'],
				hiddenName: 'Org_uid',
				listeners: {
					'keydown': function( inp, e ) {
						if ( inp.disabled ) {
							return;
						}

						if ( e.F4 == e.getKey() ) {
							if ( e.browserEvent.stopPropagation )
								e.browserEvent.stopPropagation();
							else
								e.browserEvent.cancelBubble = true;

							if ( e.browserEvent.preventDefault )
								e.browserEvent.preventDefault();
							else
								e.browserEvent.returnValue = false;

							e.returnValue = false;

							if ( Ext.isIE ) {
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}

							inp.onTrigger1Click();

							return true;
						}
					},
					'keyup': function(inp, e) {
						if ( e.F4 == e.getKey() ) {
							if ( e.browserEvent.stopPropagation )
								e.browserEvent.stopPropagation();
							else
								e.browserEvent.cancelBubble = true;

							if ( e.browserEvent.preventDefault )
								e.browserEvent.preventDefault();
							else
								e.browserEvent.returnValue = false;

							e.returnValue = false;

							if ( Ext.isIE ) {
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}

							return true;
						}
					}
				},
				mode: 'local',
				onTrigger1Click: function() {
					var base_form = this.findById('EvnUslugaFuncRequestEditForm').getForm();
					var combo = base_form.findField('Org_uid');

					if ( combo.disabled ) {
						return false;
					}

					getWnd('swOrgSearchWindow').show({
						object: 'lpu',
						onClose: function() {
							combo.focus(true, 200)
						},
						onSelect: function(org_data) {
							if ( org_data.Org_id > 0 ) {
								combo.getStore().loadData([{
									Org_id: org_data.Org_id,
									Lpu_id: org_data.Lpu_id,
									Org_Name: org_data.Org_Name
								}]);
								combo.setValue(org_data.Org_id);
								getWnd('swOrgSearchWindow').hide();
								combo.collapse();
							}

							this.onOrgSelect();
						}.createDelegate(this)
					});
				}.createDelegate(this),
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{name: 'Org_id', type: 'int'},
						{name: 'Lpu_id', type: 'int'},
						{name: 'Org_Name', type: 'string'}
					],
					key: 'Org_id',
					sortInfo: {
						field: 'Org_Name'
					},
					url: C_ORG_LIST
				}),
				tabIndex: 10,
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'{Org_Name}',
					'</div></tpl>'
				),
				trigger1Class: 'x-form-search-trigger',
				triggerAction: 'none',
				valueField: 'Org_id',
				width: 500,
				xtype: 'swbaseremotecombo'
			}, {
				allowBlank: false,
				hiddenName: 'LpuSection_uid',
				id: 'EUFREF_LpuSectionCombo',
				lastQuery: '',
				tabIndex: 11,
				width: 500,
				xtype: 'swlpusectionglobalcombo',
				linkedElements: [
					'EUFREF_MedStaffFactCombo',
					'EUFREF_MidMedStaffFactCombo'
				]
			}, {
				hiddenName: 'MedStaffFact_id',
				id: 'EUFREF_MedStaffFactCombo',
				lastQuery: '',
				listWidth: 750,
				parentElementId: 'EUFREF_LpuSectionCombo',
				tabIndex: 12,
				width: 500,
				xtype: 'swmedstafffactglobalcombo'
			}, {
				name: 'MedPersonal_uid',
				value: 0,
				xtype: 'hidden'
			}, {
				hiddenName: 'Diag_id',
				tabIndex: 13,
				fieldLabel: 'Диагноз',
				width: 450,
				xtype: 'swdiagcombo',
				onChange: function() {
					win.setTumorStageVisibility();
				}
			}, 
			{
				xtype: 'swcommonsprcombo',
				comboSubject: 'TumorStage',
				hiddenName: 'TumorStage_id',
				fieldLabel: 'Стадия выявленного ЗНО',
				tabIndex: 13,
				width: 450
			},
			{
				fieldLabel: 'МЭС',
				hiddenName: 'Mes_id',
				tabIndex: 13,
				width: 450,
				forceSelection: true,
				xtype: 'swmesekbcombo'
			}, {
				fieldLabel: lang['sredniy_med_personal'],
				hiddenName: 'MedStaffFact_sid',
				id: 'EUFREF_MidMedStaffFactCombo',
				lastQuery: '',
				listWidth: 750,
				parentElementId: 'EUFREF_LpuSectionCombo',
				tabIndex: 13,
				width: 500,
				valueField: 'MedStaffFact_id',
				xtype: 'swmedstafffactglobalcombo'
			}, {
				name: 'MedPersonal_sid',
				value: 0,
				xtype: 'hidden'
			}, {
				fieldLabel: 'Количество снимков',
				xtype: 'numberfield',
				allowDecimals: false,
				allowNegative: false,
				autoCreate: {tag: "input", size: 10, maxLength: 2, autocomplete: "off"},
				name: 'EvnUslugaPar_NumUsluga',
				minValue: 1,
				tabIndex: 14
			}, {
				fieldLabel: 'Количество оказанных услуг',
				xtype: 'numberfield',
				allowDecimals: false,
				allowNegative: false,
				name: 'EvnUsluga_Kolvo',
				minValue: 1,
				maxValue: 99,
				tabIndex: 14,
				value: 1
			}, {
				fieldLabel: 'Результат',
				xtype: 'swcommonsprcombo',
				//allowBlank: (getRegionNick() == 'kz'),
				hiddenName: 'StudyResult_id',
				comboSubject: 'StudyResult',
				tabIndex: 14,
				width: 500
			}, {
				fieldLabel: lang['kommentariy'],
				xtype: 'textarea',
				name: 'EvnUslugaPar_Comment',
				tabIndex: 14,
				width: 500
			}]
		})


		this.ExtendedPersonInformationPanelShort = new sw.Promed.PersonInformationPanelShortWithDirection({
			id: 'EUFREF_PersonInformationFrame',
			showINN: true
		});

		this.EvnUslugaAttributeValueGrid = new sw.Promed.AttributeSignValueGridPanel({
			tableName: 'dbo.EvnUslugaCommon',
			formMode: 'local',
			hideDates: true,
			denyDoubles: true,
			stringfields: [
				{name: 'AttributeSignValue_id', type: 'int', header: 'ID', key: true},
				{name: 'AttributeSignValue_TablePKey', type: 'int', hidden: true},
				{name: 'AttributeSign_id', type: 'int', hidden: true},
				{name: 'RecordStatus_Code', type: 'int', hidden: true},
				{name: 'AttributeValueLoadParams', type: 'string', hidden: true},	//Входящие параметры для редактирования значений атрибутов
				{name: 'AttributeValueSaveParams', type: 'string', hidden: true},	//Исходящие параметры для сохранения значений атрибутов
				{name: 'AttributeSign_Code', type: 'int', header: 'Код признака', width: 100},
				{name: 'AttributeSign_Name', type: 'string', header: 'Наименование признака', width: 150},
				{name: 'AttributeValue_ValueText', header: 'Значение признака', type: 'string', id: 'autoexpand'}
			]
		});

		this.AttributeValuePanel = new sw.Promed.Panel({
			border: true,
			collapsible: true,
			style: "margin-bottom: 0.5em;",
			height: 200,
			id: 'EUFREF_AttributeValuePanel',
			isLoaded: false,
			layout: 'border',
			listeners: {
				'expand': function(panel) {
					if ( panel.isLoaded === false ) {
						panel.isLoaded = true;
						win.EvnUslugaAttributeValueGrid.doLoad({tablePKey: win.findById('EvnUslugaFuncRequestEditForm').getForm().findField('EvnUslugaPar_id').getValue()});
					}
					panel.doLayout();
				}.createDelegate(this)
			},
			// style: 'margin-bottom: 0.5em;',
			title: 'Атрибуты',
			items: [
				win.EvnUslugaAttributeValueGrid
			]
		});

		this.FilePanel = new Ext.Panel({
			title: lang['faylyi'],
			id: 'EUFREF_FileTab',
			border: false,
			collapsible: true,
			autoHeight: true,
			items: [this.FileUploadPanel],
			listeners: {
				'expand':function(panel){
					//Приходится делать такую ерунду, чтобы cодержимое адекватно перерисовывалось
					//console.log(panel);
					//this.FileUploadPanel.setWidth(adjWidth);
					this.FileUploadPanel.doLayout();

				}.createDelegate(this)
			}
		});

		this.ResearchRegion = new Ext.form.FormPanel({
				id:'EUFREF_ResearchRegion',
				region:'east',
				title: lang['issledovanie'],
				collapsible: true,
				collapsed: true,
				split:true,
				width: '50%',
				heigth: '100%',
				margins:'0 5 0 0',
				layout: 'fit',
				items:
				[
					this.DicomViewerPanel
				]
			});
		this.ResearchRegion.on('beforeexpand',function(pan,anim) {
			this.ResearchRegion.getForm().getEl().setHeight(this.getEl().getHeight());
			this.DicomViewerPanel.slider.setWidth(this.DicomViewerPanel.lastSize.width-50);
			Ext.getCmp('EvnUslugaFuncRequestDicomViewerEditWindow').doLayout();
		}.createDelegate(this));

		this.ResearchRegion.on('collapse',function(pan,anim) {
			if(	this.DicomViewerPanel.vMode == 'videoPlay'){
				this.DicomViewerPanel.videoPlay(false);
			};
			Ext.getCmp('EvnUslugaFuncRequestDicomViewerEditWindow').doLayout();
		}.createDelegate(this));

		this.ElectronicQueuePanel = new sw.Promed.ElectronicQueuePanel({
			ownerWindow: win,
			panelType: 2,
			region: 'south',
			// функция выполняющаяся при нажатии на кнопку завершить прием
			completeServiceActionFn: function(params){ win.doSave(params) }
															});

        Ext.apply(this, {
			buttons: [{
				id: 'frdvSaveBtn',
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: 17,
				text: BTN_FRMSAVE,
			},{
				id: 'frdvPrintBtn',
				handler: function() {
					win.doSave({doPrint:true})
					//win.doPrint();
				},
				iconCls: 'print16',
				tabIndex: 21,
				text: BTN_FRMPRINT
			}, {
				id: 'frdvmMoveToTelemed',
				text: langs('Направить на удаленную консультацию'),
				disabled: true,
				handler: function() {
					win.doSave({
						openChildWindow: function() {
							win.moveToConsult();
						}.createDelegate(win)
					});
				}
			}, {
				id: 'frdvmTelemedDirLabel',
				cls: 'button-as-text',
				style: 'text-decoration:none; color: #000;',
				text: langs('Направление в ЦУК: '),
				hidden: true				
			}, {
				id: 'frdvmTelemedDir',
				cls: 'button-as-link',
				text: '',
				hidden: true,
				handler: function() {
					var base_form = win.findById('EvnUslugaFuncRequestEditForm').getForm();

					getWnd('swEvnDirectionEditWindow').show({
						action: 'view',
						formParams: {},
						EvnDirection_id: base_form.findField('link_EvnDirection_id').getValue(),
						PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
						Person_id: base_form.findField('Person_id').getValue(),
						UserMedStaffFact_id: win.userMedStaffFact.MedStaffFact_id,
						UserLpuSection_id: win.userMedStaffFact.LpuSection_id,
						userMedStaffFact: win.userMedStaffFact,
						from: win.userMedStaffFact.ARMForm,
						ARMType: win.userMedStaffFact.ARMType,
						//~ onHide: function(){}
					});
				}
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.buttons[2].focus();
					}
					else {
						this.buttons[1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					if ( this.action != 'view' ) {
						this.findById('EvnUslugaFuncRequestEditForm').getForm().findField('PrehospDirect_id').focus(true);
					}
					else {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				tabIndex: 25,
				text: BTN_FRMCANCEL
			}],
			layout: 'border',
			autoScroll: true,
			items:[
			{
					listeners: {
						'resize':function(panel, adjWidth, adjHeight, rawWidth, rawHeight){
							//Приходится делать такую ерунду, чтобы cодержимое адекватно перерисовывалось
							this.DicomPanel.getGrid().setWidth(adjWidth);
							this.DicomPanel.getGrid().doLayout();
							this.DicomPanel.doLayout();
							this.AssociatedResearches.getGrid().setWidth(adjWidth);
							this.AssociatedResearches.getGrid().doLayout();
							this.FileUploadPanel.setWidth(adjWidth);
							this.FileUploadPanel.doLayout();

						}.createDelegate(this)
					},
					region: 'center',
					layout: 'form',
					border: false,
					autoScroll: true,
					items:[
						this.ExtendedPersonInformationPanelShort,
						new Ext.form.FormPanel({
							bodyBorder: false,
							border: false,
							frame: false,
							id: 'EvnUslugaFuncRequestEditForm',
							labelAlign: 'right',
							labelWidth: 160,
							layout: 'form',
							reader: new Ext.data.JsonReader({
								success: Ext.emptyFn
							}, [
								{name: 'accessType'},
								{name: 'needAttributesPanel'},
								{name: 'EvnDirection_id'},
								{name: 'EvnRequest_id'},
								{name: 'EvnDirection_Num'},
								{name: 'EvnDirection_setDate'},
								{name: 'UslugaComplex_id'}, // комплексная услуга
								{name: 'FSIDI_id'},
								{name: 'UslugaMedType_id'},
								{name: 'DirectionDiag_id'},
								{name: 'Diag_id'},
								{name: 'TumorStage_id'},
								{name: 'Mes_id'},
								{name: 'EvnUslugaPar_NumUsluga'},
								{name: 'EvnUsluga_Kolvo'},
								{name: 'EvnUslugaPar_UslugaNum'},
								{name: 'EUP_MedicalCareFormType_id'},
								{name: 'StudyResult_id'},
								{name: 'XmlTemplate_id'},
								{name: 'EvnUslugaPar_id'},
								{name: 'TimetablePar_id'},
								{name: 'MedProductCard_id'},
								{name: 'EvnUslugaPar_setDate'},
								{name: 'EvnUslugaPar_setTime'},
								{name: 'LpuSection_did'},
								{name: 'LpuSection_uid'},
								{name: 'MedPersonal_sid'},
								{name: 'MedPersonal_uid'},
								{name: 'MedStaffFact_id'},
								{name: 'EvnUslugaPar_Comment'},
								{name: 'Lpu_id'},
								{name: 'Org_uid'},
								{name: 'PayType_id'},
								{name: 'Person_id'},
								{name: 'PersonEvn_id'},
								{name: 'PrehospDirect_id'},
								{name: 'Server_id'},
								{name: 'Usluga_id'},
								{name: 'EvnUslugaPar_Regime'},
								{name: 'EvnUslugaPar_IsPaid'},
								{name: 'EvnUslugaPar_IndexRep'},
								{name: 'EvnUslugaPar_IndexRepInReg'},
								{name: 'link_EvnDirection_id'},
								{name: 'link_EvnDirection_Num'},
								{name: 'link_EvnDirection_setDate'},
								{name: 'link_EvnStatus_id'},
								{name: 'link_EvnStatus_Name'},
								{name: 'ToothNums'}
							]),
							region: 'center',
							url: '/?c=EvnFuncRequest&m=saveEvnUslugaEditForm',
							items: [{
								name: 'accessType',
								value: '',
								xtype: 'hidden'
							},{
								name: 'needAttributesPanel',
								xtype: 'hidden'
							}, {
								name: 'EvnUslugaPar_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name:'DirectionDiag_id',
								xtype:'hidden'
							}, {
								name: 'XmlTemplate_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'EvnUslugaPar_isCito',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'TimetablePar_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'EvnDirection_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'EvnRequest_id',
								xtype: 'hidden'
							}, {
								name: 'Lpu_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'PayType_id',
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
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'EvnUslugaPar_Regime',
								value: 1,
								xtype: 'hidden'
							}, {
								name: 'EvnUslugaPar_IsPaid',
								xtype: 'hidden'
							}, {
								name: 'EvnUslugaPar_IndexRep',
								xtype: 'hidden'
							}, {
								name: 'EvnUslugaPar_IndexRepInReg',
								xtype: 'hidden'
							}, {
								name: 'link_EvnDirection_id',
								xtype: 'hidden'
							}, {
								name: 'link_EvnDirection_Num',
								xtype: 'hidden'
							}, {
								name: 'link_EvnDirection_setDate',
								xtype: 'hidden'
							}, {
								name: 'link_EvnStatus_id',
								xtype: 'hidden'
							}, {
								name: 'link_EvnStatus_Name',
								xtype: 'hidden'
							}, {
								name: 'ToothNums',
								xtype: 'hidden'
							},
							this.EvnDirectionPanel
							]
						}),
						{
							title:			lang['dobavit_dicom_obyektyi'],
							id:				'EUFREF_DicomObj',
							border:			false,
	//						collapsed :		true,
							collapsible:	false,
							autoHeight:		true,
							items: [this.WindowToolbar, this.DicomPanel ]
						},
						this.AssociatedResearches,
						this.EvnXmlPanel,
						this.AttributeValuePanel,
						this.FilePanel,
					]
				},
					this.ResearchRegion,
					this.ElectronicQueuePanel
				]
//			}]
		});
		sw.Promed.swEvnUslugaFuncRequestDicomViewerEditWindow.superclass.initComponent.apply(this, arguments);
	},

	checkIsInWorkListQueue: function(combo, Evn_id) {
		Ext.Ajax.request({
			url: '/?c=WorkList&m=checkIsInWorkListQueue',
			params: {EvnUslugaPar_id: Evn_id},
			callback: function(options, success, response) {
				var result = Ext.util.JSON.decode(response.responseText)[0];
				
				if(success) {
					combo.setDisabled(!!result.inQueue);
				}
			}
		});
	},

	onOrgSelect: function(defaultValues) {
		if ( typeof defaultValues != 'object' ) {
			defaultValues = new Object();
		}

		var
			base_form = this.findById('EvnUslugaFuncRequestEditForm').getForm();

		var
			index,
			Lpu_id = base_form.findField('Org_uid').getFieldValue('Lpu_id'),
			LpuSection_id = base_form.findField('LpuSection_uid').getValue() || defaultValues.LpuSection_id,
			MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue(),
			MedPersonal_id = defaultValues.MedPersonal_id || null;
			MedPersonal_sid = defaultValues.MedPersonal_sid || null;
			MedStaffFact_sid = base_form.findField('MedStaffFact_sid').getValue();

		base_form.findField('LpuSection_uid').clearValue();
		base_form.findField('MedStaffFact_id').clearValue();
		base_form.findField('MedStaffFact_sid').clearValue();

		if ( Lpu_id == getGlobalOptions().lpu_id ) {
			// Загрузка из локальных хранилищ
			setLpuSectionGlobalStoreFilter();
			base_form.findField('LpuSection_uid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

			setMedStaffFactGlobalStoreFilter({
				isDoctor: 1
			});
			base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

			setMedStaffFactGlobalStoreFilter({
				isMidMedPersonalOnly: true
			});
			base_form.findField('MedStaffFact_sid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

			base_form.findField('EvnUslugaPar_setDate').fireEvent('change', base_form.findField('EvnUslugaPar_setDate'), base_form.findField('EvnUslugaPar_setDate').getValue());

			if ( !Ext.isEmpty(LpuSection_id) ) {
				index = base_form.findField('LpuSection_uid').getStore().findBy(function(rec) {
					return (rec.get('LpuSection_id') == LpuSection_id);
				});
				if ( index >= 0 ) {
					base_form.findField('LpuSection_uid').setValue(LpuSection_id);
					base_form.findField('LpuSection_uid').disable();
					base_form.findField('LpuSection_uid').fireEvent('change', base_form.findField('LpuSection_uid'), base_form.findField('LpuSection_uid').getValue());
				}
			}

			if ( !Ext.isEmpty(MedStaffFact_id) ) {
				index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
					return (rec.get('MedStaffFact_id') == MedStaffFact_id);
				});
				if ( index >= 0 ) {
					base_form.findField('MedStaffFact_id').setValue(MedStaffFact_id);
					// только если не является ср. мед. персоналом
					if ( !sw.Promed.MedStaffFactByUser.last || sw.Promed.MedStaffFactByUser.last.PostKind_id != 6 ) {
						base_form.findField('MedStaffFact_id').disable();
					}
				}
			}
			else if ( !Ext.isEmpty(LpuSection_id) && !Ext.isEmpty(MedPersonal_id) ) {
				index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
					return (rec.get('LpuSection_id') == LpuSection_id && rec.get('MedPersonal_id') == MedPersonal_id);
				});
				if ( index >= 0 ) {
					base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
					// только если не является ср. мед. персоналом
					if ( !sw.Promed.MedStaffFactByUser.last || sw.Promed.MedStaffFactByUser.last.PostKind_id != 6 ) {
						base_form.findField('MedStaffFact_id').disable();
					}
				}
			}
			else if ( !Ext.isEmpty(MedPersonal_id) ) {
				index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
					return (rec.get('MedPersonal_id') == MedPersonal_id);
				});
				if ( index >= 0 ) {
					base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
					// только если не является ср. мед. персоналом
					if ( !sw.Promed.MedStaffFactByUser.last || sw.Promed.MedStaffFactByUser.last.PostKind_id != 6 ) {
						base_form.findField('MedStaffFact_id').disable();
					}
				}
			}

			if ( !Ext.isEmpty(MedStaffFact_sid) ) {
				index = base_form.findField('MedStaffFact_sid').getStore().findBy(function(rec) {
					return (rec.get('MedStaffFact_id') == MedStaffFact_sid);
				});
				if ( index >= 0 ) {
					base_form.findField('MedStaffFact_sid').setValue(MedStaffFact_sid);
				}
			}
			else if ( !Ext.isEmpty(LpuSection_id) && !Ext.isEmpty(MedPersonal_sid) ) {
				index = base_form.findField('MedStaffFact_sid').getStore().findBy(function(rec) {
					return (rec.get('LpuSection_id') == LpuSection_id && rec.get('MedPersonal_id') == MedPersonal_sid);
				});
				if ( index >= 0 ) {
					base_form.findField('MedStaffFact_sid').setValue(base_form.findField('MedStaffFact_sid').getStore().getAt(index).get('MedStaffFact_id'));
				}
			}
			else if ( !Ext.isEmpty(MedPersonal_sid) ) {
				index = base_form.findField('MedStaffFact_sid').getStore().findBy(function(rec) {
					return (rec.get('MedPersonal_id') == MedPersonal_sid);
				});
				if ( index >= 0 ) {
					base_form.findField('MedStaffFact_sid').setValue(base_form.findField('MedStaffFact_sid').getStore().getAt(index).get('MedStaffFact_id'));
				}
			}
		}
		else {
			// Загрузка с сервера
			base_form.findField('LpuSection_uid').getStore().load({
				callback: function() {
					base_form.findField('EvnUslugaPar_setDate').fireEvent('change', base_form.findField('EvnUslugaPar_setDate'), base_form.findField('EvnUslugaPar_setDate').getValue());

					if ( !Ext.isEmpty(LpuSection_id) ) {
						index = base_form.findField('LpuSection_uid').getStore().findBy(function(rec) {
							return (rec.get('LpuSection_id') == LpuSection_id);
						});
						if ( index >= 0 ) {
							base_form.findField('LpuSection_uid').setValue(LpuSection_id);
							base_form.findField('LpuSection_uid').disable();
							base_form.findField('LpuSection_uid').fireEvent('change', base_form.findField('LpuSection_uid'), base_form.findField('LpuSection_uid').getValue());
						}
					}
				},
				params: {
					mode: 'combo',
					Lpu_id: Lpu_id
				}
			});

			base_form.findField('MedStaffFact_id').getStore().load({
				callback: function() {
					base_form.findField('EvnUslugaPar_setDate').fireEvent('change', base_form.findField('EvnUslugaPar_setDate'), base_form.findField('EvnUslugaPar_setDate').getValue());

					if ( !Ext.isEmpty(MedStaffFact_id) ) {
						index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
							return (rec.get('MedStaffFact_id') == MedStaffFact_id);
						});
						if ( index >= 0 ) {
							base_form.findField('MedStaffFact_id').setValue(MedStaffFact_id);
							// только если не является ср. мед. персоналом
							if ( !sw.Promed.MedStaffFactByUser.last || sw.Promed.MedStaffFactByUser.last.PostKind_id != 6 ) {
								base_form.findField('MedStaffFact_id').disable();
							}
						}
					}
					else if ( !Ext.isEmpty(LpuSection_id) && !Ext.isEmpty(MedPersonal_id) ) {
						index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
							return (rec.get('LpuSection_id') == LpuSection_id && rec.get('MedPersonal_id') == MedPersonal_id);
						});
						if ( index >= 0 ) {
							base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
							// только если не является ср. мед. персоналом
							if ( !sw.Promed.MedStaffFactByUser.last || sw.Promed.MedStaffFactByUser.last.PostKind_id != 6 ) {
								base_form.findField('MedStaffFact_id').disable();
							}
						}
					}
					else if ( !Ext.isEmpty(MedPersonal_id) ) {
						index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
							return (rec.get('MedPersonal_id') == MedPersonal_id);
						});
						if ( index >= 0 ) {
							base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
							// только если не является ср. мед. персоналом
							if ( !sw.Promed.MedStaffFactByUser.last || sw.Promed.MedStaffFactByUser.last.PostKind_id != 6 ) {
								base_form.findField('MedStaffFact_id').disable();
							}
						}
					}
				},
				params: {
					mode: 'combo',
					Lpu_id: Lpu_id,
					isDoctor: 1
				}
			});

			base_form.findField('MedStaffFact_sid').getStore().load({
				callback: function() {
					base_form.findField('EvnUslugaPar_setDate').fireEvent('change', base_form.findField('EvnUslugaPar_setDate'), base_form.findField('EvnUslugaPar_setDate').getValue());

					if ( !Ext.isEmpty(MedStaffFact_sid) ) {
						index = base_form.findField('MedStaffFact_sid').getStore().findBy(function(rec) {
							return (rec.get('MedStaffFact_id') == MedStaffFact_sid);
						});
						if ( index >= 0 ) {
							base_form.findField('MedStaffFact_sid').setValue(MedStaffFact_sid);
						}
					}
					else if ( !Ext.isEmpty(LpuSection_id) && !Ext.isEmpty(MedPersonal_sid) ) {
						index = base_form.findField('MedStaffFact_sid').getStore().findBy(function(rec) {
							return (rec.get('LpuSection_id') == LpuSection_id && rec.get('MedPersonal_id') == MedPersonal_sid);
						});
						if ( index >= 0 ) {
							base_form.findField('MedStaffFact_sid').setValue(base_form.findField('MedStaffFact_sid').getStore().getAt(index).get('MedStaffFact_id'));
						}
					}
					else if ( !Ext.isEmpty(MedPersonal_sid) ) {
						index = base_form.findField('MedStaffFact_sid').getStore().findBy(function(rec) {
							return (rec.get('MedPersonal_id') == MedPersonal_sid);
						});
						if ( index >= 0 ) {
							base_form.findField('MedStaffFact_sid').setValue(base_form.findField('MedStaffFact_sid').getStore().getAt(index).get('MedStaffFact_id'));
						}
					}
				},
				params: {
					mode: 'combo',
					Lpu_id: Lpu_id,
					isMidMedPersonal: 1
				}
			});								
		}
	},
	checkAttributeValuePanelHidden: function() {
		var win = this;
		var base_form = this.findById('EvnUslugaFuncRequestEditForm').getForm();
		var needAttributesPanel = base_form.findField('needAttributesPanel').getValue();
		var UslugaComplex_Code = base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Code');

		var isHidden = true;
		win.EvnUslugaAttributeValueGrid.UslugaComplex_Code = UslugaComplex_Code;
		switch(getRegionNick()) {
			case 'perm':
				if (needAttributesPanel && parseInt(needAttributesPanel) == 1) {
					isHidden = false;
				}
				break;
		}

		if (isHidden) {
			win.AttributeValuePanel.hide();
		} else {
			win.AttributeValuePanel.show();
		}

		return isHidden;
	},
	setTumorStageVisibility: function() {
		var base_form = this.findById('EvnUslugaFuncRequestEditForm').getForm();

		var
			dateX20180601 = new Date(2018, 5, 1),
			Diag_Code = base_form.findField('Diag_id').getFieldValue('Diag_Code'),
			EvnUslugaPar_setDate = base_form.findField('EvnUslugaPar_setDate').getValue();

		if (
			getRegionNick() == 'ekb'
			&& !Ext.isEmpty(Diag_Code) && ((Diag_Code.slice(0, 3) >= 'C00' && Diag_Code.slice(0, 5) <= 'C80.9') || Diag_Code.slice(0,3) == 'C97')
			&& typeof EvnUslugaPar_setDate == 'object' && EvnUslugaPar_setDate < dateX20180601
		) {
			base_form.findField('TumorStage_id').setContainerVisible(true);
			base_form.findField('TumorStage_id').setAllowBlank(false);
		}
		else {
			base_form.findField('TumorStage_id').setContainerVisible(false);
			base_form.findField('TumorStage_id').setAllowBlank(true);
			base_form.findField('TumorStage_id').clearValue();
		}
	},
	checkEnableTelemedButtons: function() {
		var _this = this,
			form_panel = _this.findById('EvnUslugaFuncRequestEditForm'),
			base_form = form_panel.getForm(),
			hasLinkDirection = !Ext.isEmpty(base_form.findField('link_EvnDirection_Num').getValue()),
			hasLinkDirection10_15 = (base_form.findField('link_EvnStatus_id').getValue() == 10 || base_form.findField('link_EvnStatus_id').getValue() == 15);//есть связанное направление в ЦУК со статусом обслужено или поставлено в очередь
		
		Ext.getCmp('frdvmMoveToTelemed').setVisible(!hasLinkDirection10_15);
		Ext.getCmp('frdvmMoveToTelemed').setDisabled(!_this.hasDicomAssociatedResearches() && _this.FileUploadPanel.FileStore.getCount()==0);
		Ext.getCmp('frdvmTelemedDirLabel').setVisible(hasLinkDirection);
		Ext.getCmp('frdvmTelemedDir').setText(
			'№ '+base_form.findField('link_EvnDirection_Num').getValue()+
			' дата выписки направления: '+base_form.findField('link_EvnDirection_setDate').getValue()+
			', Статус направления: "'+base_form.findField('link_EvnStatus_Name').getValue()+'"');
		Ext.getCmp('frdvmTelemedDir').setVisible(hasLinkDirection);
		
	},
	show: function() {
		sw.Promed.swEvnUslugaFuncRequestDicomViewerEditWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();
		if (arguments[0].userMedStaffFact)
		{
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}
		this.MedService_id = arguments[0].MedService_id || null;
		this.dateMenu.setValue(getGlobalOptions().date+' - '+getGlobalOptions().date);
		var wnd = this;
		if(getRegionNick()=='ufa') {
			Ext.Ajax.request({
				url: '/?c=MedService&m=checkMedServiceUsluga',
				params: {
					MedService_id: wnd.MedService_id
				},
				callback: function(opt, success, response) {
					if (success && response.responseText != '') {
						var result  = Ext.util.JSON.decode(response.responseText);
						if (result.checkMedServiceUsluga) {
							wnd.dateMenu.setValue(getValidDT(getGlobalOptions().date, '').add(Date.DAY, -3).format('d.m.Y')+' - '+getGlobalOptions().date);
						} 
					}
				}
			});
		}
		if ( !arguments[0] )
		{
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}
		this.formStatus = 'edit'; // или 'save'
		this.show_complete = false;
		this.action = arguments[0].action || 'edit';
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onSaveUsluga = arguments[0].onSaveUsluga || Ext.emptyFn;
		this.onSaveProtocol = arguments[0].onSaveProtocol || Ext.emptyFn;
		this.addProtocolAfterSaveUsluga = arguments[0].addProtocolAfterSaveUsluga || false;
		this.editProtocolAfterSaveUsluga = arguments[0].editProtocolAfterSaveUsluga || false;
		// определяем параметры, влияющие на внешний вид.
		this.ARMType = arguments[0].ARMType || '';
		this.face = ( arguments[0].face ) ? arguments[0].face : '';
		this.is_UslugaComplex = false; // обычная или комплексная услуга. Можно определить только после загрузки формы
		this.is_doctorpar = false; // Врач параклиники или др. пользователь
		this.is_operator = false; // Оператор или работающий врач (есть список мест работы)
		// параметры, влияющие на свободный выбор врача и отделения, а также внешний вид
		this.UserMedStaffFact_id = arguments[0].UserMedStaffFact_id || null;
		this.UserLpuSection_id = arguments[0].UserLpuSection_id || null;
		this.LpuSection_did = arguments[0].LpuSection_did || null;

		this.Lpu_id = arguments[0].Lpu_id || getGlobalOptions().lpu_id;
		this.LpuSection_id = arguments[0].LpuSection_id || null;
		this.MedPersonal_id = arguments[0].MedPersonal_id || null;
		this.Resource_id = arguments[0].Resource_id || null;
		this.EvnFuncRequest_id = arguments[0].EvnFuncRequest_id || null;
		this.EvnDirection_id = arguments[0].EvnDirection_id || null;
		if (sw.Promed.MedStaffFactByUser.last) {
			this.Lpu_id = sw.Promed.MedStaffFactByUser.last.Lpu_id || this.Lpu_id;
			this.LpuSection_id = sw.Promed.MedStaffFactByUser.last.LpuSection_id || this.LpuSection_id;
			this.MedPersonal_id = sw.Promed.MedStaffFactByUser.last.MedPersonal_id || this.MedPersonal_id;
		}

		this.UserMedStaffFacts = null;
		this.UserLpuSections = null;
		this.DicomPanel.getAction('action_add').setDisabled(true);
		this.electronicQueueData = arguments[0].electronicQueueData || false;
		//this.EvnDirectionPanel.collapse();
		
		if ( !arguments[0].EvnUslugaPar_id && this.action != 'add')
		{
			sw.swMsg.alert(lang['soobschenie'], lang['otsutstvuet_identifikator_paraklinicheskoy_uslugi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}
		
		this.isTelemedARM = this.ARMType=='remoteconsultcenter' || (!Ext.isEmpty(this.userMedStaffFact) && this.userMedStaffFact.ARMType == 'remoteconsultcenter');
		
        if (!arguments[0].EvnDirection_id && !arguments[0].EvnUslugaPar_id) {
            this.buttons[1].disable();
        } else {
            this.buttons[1].enable();
        }
		/*if ( this.ARMType == 'par' && !(this.UserMedStaffFact_id > 0 && this.UserLpuSection_id > 0) )
		{
			sw.swMsg.alert(lang['soobschenie'], lang['otsutstvuyut_parametryi_polzovatelya_arma_parakliniki'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}*/
		if ( this.ARMType == 'par' && (this.UserMedStaffFact_id > 0 && this.UserLpuSection_id > 0) )
		{
			this.is_doctorpar = true;
		}
		
		if ( this.is_doctorpar && this.action == 'add')
		{
			// добавление обычной паракл.услуги врачом из АРМа парки
			this.is_UslugaComplex = false;
		}

		// если в настройках есть medstafffact, то имеем список мест работы
		if ( Ext.globalOptions.globals['medstafffact'] && Ext.globalOptions.globals['medstafffact'].length > 0 )
		{
			this.UserMedStaffFacts = Ext.globalOptions.globals['medstafffact'];
		}
		// если в настройках есть lpusection, то имеем список мест работы
		if ( Ext.globalOptions.globals['lpusection'] && Ext.globalOptions.globals['lpusection'].length > 0 )
		{
			this.UserLpuSections = Ext.globalOptions.globals['lpusection'];
		}

		this.ElectronicQueuePanel.initElectronicQueue();
		if (this.electronicQueueData
			&& this.electronicQueueData.electronicTalonStatus_id
			&& this.electronicQueueData.electronicTalonStatus_id < 4
		) {
			this.ElectronicQueuePanel.show(); this.doLayout(); this.syncSize();
				// скрываем кнопки сохранить и печать
				Ext.getCmp('frdvSaveBtn').hide();
				Ext.getCmp('frdvPrintBtn').hide();
		} else {
			this.ElectronicQueuePanel.hide(); this.doLayout(); this.syncSize();
			Ext.getCmp('frdvSaveBtn').show();
			Ext.getCmp('frdvPrintBtn').show();
			}

		this.is_operator = (!this.UserMedStaffFacts || !this.UserLpuSections);
		
		var form_panel = this.findById('EvnUslugaFuncRequestEditForm');
		var base_form = form_panel.getForm();

		var usluga_complex_combo = base_form.findField('UslugaComplex_id');
		var usluga_setdate = base_form.findField('EvnUslugaPar_setDate');
		//var templ_panel = this.findById('EUFREF_TemplPanel');

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();
		base_form.reset();
        this.EvnXmlPanel.doReset();
        this.EvnXmlPanel.collapse();
        this.EvnXmlPanel.LpuSectionField = base_form.findField('LpuSection_uid');
        this.EvnXmlPanel.MedStaffFactField = base_form.findField('MedStaffFact_id');
		this.EvnXmlPanel.Person_id = arguments[0].Person_id || null;
		this.EvnXmlPanel.EvnUslugaPar_id = arguments[0].EvnUslugaPar_id || null;

		this.EvnUslugaAttributeValueGrid.getGrid().getStore().removeAll();
		this.EvnUslugaAttributeValueGrid.setReadOnly(false);
		this.AttributeValuePanel.hide();
		this.AttributeValuePanel.collapse();
		this.AttributeValuePanel.isLoaded = false;

		base_form.findField('Diag_id').setContainerVisible(getRegionNick() == 'ekb');
		base_form.findField('Mes_id').setContainerVisible(getRegionNick() == 'ekb');
        base_form.findField('Diag_id').setAllowBlank(true);

		base_form.findField('EvnUslugaPar_RepFlag').hideContainer();
		
		base_form.setValues(arguments[0]);
		base_form.findField('Lpu_id').setValue(this.Lpu_id); // устанавливаем текущую МО, не зависимо от того в какой МО была создана заявка.
		base_form.findField('Org_uid').enable();
		base_form.findField('LpuSection_uid').enable();
		base_form.findField('MedStaffFact_id').enable();

		base_form.clearInvalid();
		this.ExtendedPersonInformationPanelShort.load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			EvnDirection_id: (arguments[0].EvnDirection_id ? arguments[0].EvnDirection_id : ''),
			callback: function() {
				clearDateAfterPersonDeath('personpanelid', this.ExtendedPersonInformationPanelShort.id, usluga_setdate);
			}.createDelegate(this)
		});

		var evn_usluga_par_id = this.EvnUslugaPar_id = arguments[0].EvnUslugaPar_id;
		
		//загружаем файлы
		this.FileUploadPanel.reset();
		this.FileUploadPanel.listParams = {
			Evn_id: evn_usluga_par_id
		};	
		this.FileUploadPanel.loadData({
			Evn_id: evn_usluga_par_id
		});
		
		var params = {};
		params.EvnUslugaPar_id = evn_usluga_par_id;
		this.AssociatedResearches.removeAll({clearAll:true, addEmptyRecord:false});
		this.AssociatedResearches.loadData({globalFilters: params,callback: function(r,opts,success){				
				if ((r.length == 1)&&(typeof r[0]['json'] != undefined)&&(typeof r[0]['json']['Error_Msg'] != undefined)&&( r[0]['json']['Error_Msg']!=null )) {
					sw.swMsg.alert(lang['oshibka'], r[0]['json']['Error_Msg']);
				}
				this.checkEvnXmlPanelAvailibility();
		}.createDelegate(this)});
		
		/*
		templ_panel.getToolbarItem('btnTemplatePrint').setVisible(true);
		templ_panel.Evn_id = evn_usluga_par_id;
		templ_panel.loadTemplate({
			Evn_id: evn_usluga_par_id,
			onNotFound: function(){
			}
		});
        */


		this.initMasks();
		this.refreshFieldsVisibility();
		
		base_form.findField('TumorStage_id').setContainerVisible(false);
		base_form.findField('TumorStage_id').setAllowBlank(true);

		Ext.Ajax.request({
			failure: function (response, options) {
				sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function () {
					this.hide();
				}.createDelegate(this));
			},
			params: {
				Evn_id: evn_usluga_par_id,
				MedStaffFact_id: this.UserMedStaffFacts ? this.UserMedStaffFacts.MedStaffFact_id : null,
				ArmType: this.UserMedStaffFacts ? this.UserMedStaffFacts.ARMType : null
			},
			success: function (response, options) {
				if (!Ext.isEmpty(response.responseText)) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if (response_obj.success == false) {
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_zagruzke_dannyih_formyi']);
						this.action = 'view';
					}
				}

				//вынес продолжение show в отдельную функцию, т.к. иногда callback приходит после выполнения логики
				this.onShow();
			}.createDelegate(this),
			url: '/?c=Evn&m=CommonChecksForEdit'
		});

		var toothsPanel = form_panel.findById(wnd.id + "_" + 'ToothNumFieldsPanel');
		toothsPanel.clearPanel();

		// скрываем зубы
		if (!arguments[0].parentEvnClass_SysNick
			|| arguments[0].parentEvnClass_SysNick && arguments[0].parentEvnClass_SysNick != "EvnVizitPLStom"
		) {
			toothsPanel.hide();
		}

		base_form.findField('EUP_MedicalCareFormType_id').getStore().load();
	},
	onShow: function() {
		var form_panel = this.findById('EvnUslugaFuncRequestEditForm');
		var base_form = form_panel.getForm();
		var _this = this;

		var evn_usluga_par_id = this.EvnUslugaPar_id;

		var usluga_complex_combo = base_form.findField('UslugaComplex_id');
		var usluga_setdate = base_form.findField('EvnUslugaPar_setDate');

		//из арм цук особые права на доступ к кнопкам панели управления #139304
		Ext.getCmp('frdvSaveBtn').setDisabled(_this.isTelemedARM);
		Ext.getCmp('frdvPrintBtn').setDisabled(_this.isTelemedARM);
		Ext.getCmp('frdvmMoveToTelemed').setDisabled(_this.isTelemedARM);
				// Ext.getCmp('frdvmTelemedDirLabel').setDisabled(_this.isTelemedARM); //надо ли ограничивать доступ к гиперссылке?
				// Ext.getCmp('frdvmTelemedDir').setDisabled(_this.isTelemedARM);
		_this.EvnXmlPanel.setReadOnly(_this.isTelemedARM);
		_this.FileUploadPanel.setDisabled(_this.isTelemedARM);
		_this.WindowToolbar.setVisible(!_this.isTelemedARM);
		_this.DicomPanel.setVisible(!_this.isTelemedARM);
		_this.AssociatedResearches.setReadOnly(_this.isTelemedARM);
		
		var loadMask = new Ext.LoadMask(this.getEl());

		base_form.load({
			failure: function() {
				loadMask.hide();
				sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {this.hide();}.createDelegate(this) );
			}.createDelegate(this),
			params: {
				'EvnUslugaPar_id': evn_usluga_par_id
			},
			success: function(loadedForm, response) {
				_this.checkEnableTelemedButtons();

				var modeAdd = false,
					UslugaMedType_id = base_form.findField('UslugaMedType_id');

				if (Ext.isEmpty(base_form.findField('EvnUslugaPar_setDate').getValue())) {
					modeAdd = true;
				}

				_this.checkAttributeValuePanelHidden();

				UslugaMedType_id.setContainerVisible(getRegionNick() === 'kz');
				
				var evnUslugaPar_NumUsluga = base_form.findField('EvnUslugaPar_NumUsluga');
				var EvnUsluga_Kolvo = base_form.findField('EvnUsluga_Kolvo');
				
				if( Ext.isEmpty(evnUslugaPar_NumUsluga.getValue()) ) evnUslugaPar_NumUsluga.setValue(1);
				if( Ext.isEmpty(EvnUsluga_Kolvo.getValue()) ) EvnUsluga_Kolvo.setValue(1);
				
				if( getRegionNick() === 'kz' ){
					
					var studyResult_id = base_form.findField('StudyResult_id');
					
					if( Ext.isEmpty(studyResult_id.getValue()) ){
						studyResult_id.setValue(1);
					}

					if (Ext.isEmpty(UslugaMedType_id.getValue())) {
						UslugaMedType_id.setFieldValue('UslugaMedType_Code', '1400');
				}
				}

				base_form.findField('MedProductCard_id').getStore().load({
					params: {Lpu_id: _this.Lpu_id, MedService_id: _this.MedService_id},
					callback: function(records, options, success) {
						_this.checkIsInWorkListQueue(base_form.findField('MedProductCard_id'), evn_usluga_par_id);
						
						if (!Ext.isEmpty(base_form.findField('MedProductCard_id').getValue())) {
							base_form.findField('MedProductCard_id').setValue(base_form.findField('MedProductCard_id').getValue());
						} else if (modeAdd && !Ext.isEmpty(_this.Resource_id)) {
							// проставить по умолчанию изделие с ресурса
							var index = base_form.findField('MedProductCard_id').getStore().findBy( function(rec) {
								if ( rec.get('Resource_id') == _this.Resource_id ) {
									return true;
								}
							});
							if (index > -1) {
								var mpc_id = base_form.findField('MedProductCard_id').getStore().getAt(index).get('MedProductCard_id');
								base_form.findField('MedProductCard_id').setValue(mpc_id);
								base_form.findField('MedProductCard_id').fireEvent('change', base_form.findField('MedProductCard_id'), mpc_id);
							}
						} else if (modeAdd && base_form.findField('MedProductCard_id').getStore().getCount()) {
							var mpc_id = base_form.findField('MedProductCard_id').getStore().getAt(0).get('MedProductCard_id');
							base_form.findField('MedProductCard_id').setValue(mpc_id);
							base_form.findField('MedProductCard_id').fireEvent('change', base_form.findField('MedProductCard_id'), mpc_id, 0);
						}
					}
				});

				base_form.findField('Lpu_id').setValue(this.Lpu_id); // устанавливаем текущую МО, не зависимо от того в какой МО была создана заявка.

				var
					MedPersonal_sid = base_form.findField('MedPersonal_sid').getValue(),
					Org_uid = base_form.findField('Org_uid').getValue();

				if (Ext.isEmpty(MedPersonal_sid)) {
					MedPersonal_sid = this.MedPersonal_id; // по умолчанию
				}
				
				var params = {
					OrgType: 'lpu'
				};
				
				if ( Ext.isEmpty(Org_uid) ) {
					params.Lpu_oid = this.Lpu_id;
				}
				else {
					params.Org_id = Org_uid;
				}

				base_form.findField('Org_uid').getStore().load({
					callback: function(records, options, success) {
						if ( success ) {
							if ( base_form.findField('Org_uid').getStore().getCount() > 0 ) {
								base_form.findField('Org_uid').setValue(base_form.findField('Org_uid').getStore().getAt(0).get('Org_id'));
								base_form.findField('Org_uid').disable();
							}
						}

						if ( Ext.isEmpty(usluga_setdate.getValue()) ) {
							setCurrentDateTime({
								callback: function() {
									this.onOrgSelect({
										LpuSection_id: this.LpuSection_id,
										MedPersonal_id: this.MedPersonal_id,
										MedPersonal_sid: MedPersonal_sid
									});
								}.createDelegate(this),
								dateField: usluga_setdate,
								loadMask: false,
								setDate: true,
								setDateMaxValue: true,
								setDateMinValue: false,
								setTime: true,
								timeField: base_form.findField('EvnUslugaPar_setTime'),
								windowId: this.id
							});
						}
						else {
							this.onOrgSelect({
								LpuSection_id: this.LpuSection_id,
								MedPersonal_id: this.MedPersonal_id,
								MedPersonal_sid: MedPersonal_sid
							});
						}
					}.createDelegate(this),
					params: params
				});

				/*usluga_complex_combo.getStore().removeAll();
				usluga_complex_combo.getStore().load({
                    params: {UslugaComplex_id: usluga_complex_combo.getValue()},
					callback: function() {
						this.setValue(this.getValue());
						this.fireEvent('change', this, this.getValue());
					}.createDelegate(usluga_complex_combo)
				});
				usluga_complex_combo.disable();*/
				
				document.getElementById('EUFREF_ResearchRegion-xcollapsed').style.width = '20px';
				loadMask.hide();
				if (!Ext.isEmpty(base_form.findField('Diag_id').getValue())) {
					var diag_id = base_form.findField('Diag_id').getValue();
					base_form.findField('Diag_id').clearValue();
					base_form.findField('Diag_id').getStore().load({
						params: {where: "where Diag_id = " + diag_id},
						callback: function () {
							if (base_form.findField('Diag_id').getStore().getCount() > 0) {
								base_form.findField('Diag_id').setValue(diag_id);
								base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), base_form.findField('Diag_id').getStore().getAt(0), 0);
								base_form.findField('Diag_id').onChange();
								usluga_complex_combo.getStore().removeAll();
								usluga_complex_combo.getStore().load({
				                    params: {UslugaComplex_id: usluga_complex_combo.getValue()},
									callback: function() {
										this.setValue(this.getValue());
										if (!_this.checkAttributeValuePanelHidden()) {
											_this.AttributeValuePanel.expand();
										}
										this.fireEvent('change', this, this.getValue());
									}.createDelegate(usluga_complex_combo)
								});
								usluga_complex_combo.disable();
							}
						}
					});
				}

				else {
					usluga_complex_combo.getStore().removeAll();
					usluga_complex_combo.getStore().load({
	                    params: {UslugaComplex_id: usluga_complex_combo.getValue()},
						callback: function() {
							this.setValue(this.getValue());
							if (!_this.checkAttributeValuePanelHidden()) {
								_this.AttributeValuePanel.expand();
							}
							this.fireEvent('change', this, this.getValue());
						}.createDelegate(usluga_complex_combo)
					});
					usluga_complex_combo.disable();
				}

				if (!Ext.isEmpty(base_form.findField('Mes_id').getValue())) {
					var mes_id = base_form.findField('Mes_id').getValue();
					base_form.findField('Mes_id').clearValue();
					base_form.findField('Mes_id').getStore().load({
						params: {
							Mes_id: mes_id
						},
						callback: function () {
							if (base_form.findField('Mes_id').getStore().getCount() > 0) {
								base_form.findField('Mes_id').setValue(mes_id);
								base_form.findField('Mes_id').fireEvent('change', base_form.findField('Mes_id'), base_form.findField('Mes_id').getValue());
							}
						}
					});
				}
				
				this.EvnXmlPanel.setReadOnly('view' == base_form.findField('accessType').getValue() || this.isTelemedARM);
                this.EvnXmlPanel.setBaseParams({
                    userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
                    UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
                    Server_id: base_form.findField('Server_id').getValue(),
                    Evn_id: base_form.findField('EvnUslugaPar_id').getValue()
                });
			
				if (!Ext.isEmpty(base_form.findField('EvnUslugaPar_Regime').getValue())) {
					if (base_form.findField('EvnUslugaPar_Regime').getValue() != 2) {
						_this._setResearchFormat('digital');
						_this.searchStudies();
						this._setResearchFormat('digital');
					} else {
						_this._setResearchFormat('analog');
						this._setResearchFormat('analog');
					}
				} else {
					_this._setResearchFormat('analog');
					this._setResearchFormat('analog');
					this.searchStudies();
				}

				if ( getRegionNick() == 'perm' && base_form.findField('EvnUslugaPar_IsPaid').getValue() == 2) {
					base_form.findField('EvnUslugaPar_RepFlag').showContainer();

					var indexRep = Ext.isEmpty(base_form.findField('EvnUslugaPar_IndexRep').getValue()) ?
						0 : base_form.findField('EvnUslugaPar_IndexRep').getValue(),
						indexRepInReg = Ext.isEmpty(base_form.findField('EvnUslugaPar_IndexRepInReg').getValue()) ?
							0 : base_form.findField('EvnUslugaPar_IndexRepInReg').getValue();

					if (indexRep >= indexRepInReg) {
						base_form.findField('EvnUslugaPar_RepFlag').setValue(true);
					}
					else {
						base_form.findField('EvnUslugaPar_RepFlag').setValue(false);
					}
				}

				// если есть зубы
				var ToothNums = base_form.findField('ToothNums').getValue();

				if (ToothNums) {

					var toothsPanel = form_panel.findById(_this.id + "_" + 'ToothNumFieldsPanel');

					toothsPanel.fillPanelByData({
						panelValues: ToothNums,
						fillInLine: true
					});
				}

				if (!Ext.isEmpty(base_form.findField('FSIDI_id').getValue())) {
					base_form.findField('FSIDI_id').showContainer();
				} else {
					base_form.findField('FSIDI_id').hideContainer();
				}

				this.refreshFieldsVisibility();
                this.EvnXmlPanel.doLoadData();
			}.createDelegate(this),
			url: '/?c=EvnFuncRequest&m=loadEvnUslugaEditForm'
		});
	},
	title: lang['rezultat_vyipolneniya_uslugi']
	
});
