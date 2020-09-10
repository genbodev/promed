/**
 * amm_OnkoCtr_ProfileEditWindow - окно ввода анкеты по онкоконтролю
 *
 * @copyright    Copyright (c) 2014 
 * @author       Нигматуллин Тагир, Уфа
 * @version      23.09.2014
 * @comment      
 */


sw.Promed.amm_OnkoCtr_ProfileEditWindow = Ext.extend(sw.Promed.BaseForm, {
    title: langs('Анкетирование'),
    id: 'ammOnkoProfileEditWindow',
    border: false,
    width: 900,
    height: 700,
    frame: true,
    action: '',
    maximizable: true,
    closeAction: 'hide',
    layout: 'border',
    codeRefresh: true,
    autoScroll: true,
    modal: true,
    objectName: 'amm_OnkoCtr_ProfileEditWindow',
    objectSrc: '/jscore/Forms/OnkoCtrl/amm_OnkoCtr_ProfileEditWindow.js',
    onHide: Ext.emptyFn,
    actionEdit: function(action) {
		var enable = action.inlist(['edit','add']);
		if (enable)
		{
			Ext.getCmp('Oncoctrl_ButtonSave').show();
		} else {
			Ext.getCmp('Oncoctrl_ButtonSave').hide();
		}
		Ext.getCmp('fieldsetForm').findBy(function(el) {
			if (el.xtype == 'checkbox') {
				el.disabled = !enable;
			}
		});
		 if ((enable) & ((this.formParams.parent_id == undefined) || (this.formParams.Evn_setDT != undefined) || this.ReportType == 'onko'))
			 //  Если создано от посещения
			 enable = false
			
		Ext.getCmp('OnkoCtrl_Date').setDisabled(!enable && (this.ReportType != 'previzit' || action == 'view'));
		Ext.getCmp('OnkoCtrl_MedPersonalCombo').setDisabled(!enable);
		Ext.getCmp('OnkoCtrl_LpuSectionCombo').setDisabled(!enable);
		Ext.getCmp('OnkoCtrl_LpuBuildingCombo').setDisabled(!enable);
    },
	fieldsetFormIsDirty: function(){
		var flag = false;
		Ext.getCmp('fieldsetForm').findBy(function(el) {
			if (el.xtype == 'checkbox') {
				var val = (el.getValue()) ? 1 : 0;
				if(el.originalValue != val){
					flag = true;
				}
			}
		}.createDelegate(this));
		return flag;
	},
	fieldsetFormSetOriginalValue: function(){
		Ext.getCmp('fieldsetForm').findBy(function(el) {
			if (el.xtype == 'checkbox') {
				var val = (el.getValue()) ? 1 : 0;
				el.originalValue = val;
			}
		});
	},
    saveRecord: function ($action) {
		var params = {};
		var $val;
		var $paramOnkoProfile;

		params.Person_id = this.formParams.Person_id;
		params.EvnUslugaPar_id = this.formParams.EvnUslugaPar_id;
		params.Profile_Date = Ext.getCmp('OnkoCtrl_Date').value;
		params.PersonOnkoProfile_id = this.formParams.PersonOnkoProfile_id;
		params.MedStaffFact_id = Ext.getCmp('OnkoCtrl_MedPersonalCombo').getValue() || getGlobalOptions().CurMedStaffFact_id;

		if ( this.ReportType == 'geriatrics' ) {
			//params.GeriatricsQuestion_Other = Ext.getCmp('Anket_GeriatricsQuestion_Other').getValue();
			params.AgeNotHindrance_id = Ext.getCmp('Anket_AgeNotHindrance_id').getValue();
			params.MorbusGeriatrics_id = Ext.getCmp('Anket_MorbusGeriatrics_id').getValue();
		}
		
		if ( this.ReportType == 'birads' || this.ReportType == 'recist') {
			params.LpuSection_id = this.LpuSection_id;
			params.MedPersonal_id = this.MedPersonal_id;
		}

		params.Lpu_id = this.formParams.Lpu_id;
		params.Questions = '';

		var answers = [];
		var $ProfileResult = [];

		var isValid = true;
		Ext.getCmp('fieldsetForm').findBy(function(el) {
			if ((el.isFormField || el.xtype == 'radiogroup') && el.oId) {
				var value = el.getValue();
				var freeField = Ext.getCmp('OnkoCtrl_Text' + el.oId);
				var textAns = freeField ? freeField.getValue() : null;
				if (el.allowBlank === false && Ext.isEmpty(value) && Ext.isEmpty(textAns)) {
					isValid = false;
				}
				answers.push([el.oId, value, textAns]);
				if (!Ext.isEmpty(value) && value) {
					$ProfileResult.push(el.oId);
				}
			}
		});
		params.QuestionAnswer = Ext.util.JSON.encode(answers);
		
		if (!isValid) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: 'Не выбран обязательный ответ. Вернитесь в анкету.',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		sw.Promed.vac.utils.consoleLog('params');
		sw.Promed.vac.utils.consoleLog(params);

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		Ext.Ajax.request({
			url: '/?c='+this.controller+'&m=savePersonOnkoProfile',
			method: 'POST',
			params: params,
			failure: function() {
				loadMask.hide();
			},
			success: function(response, opts) {
				loadMask.hide();

				sw.Promed.vac.utils.consoleLog('response');

				log(response.responseText);
				//sw.Promed.vac.utils.consoleLog(response.responseText.rows[0].Error_Code);
				//alert(response.responetext)
				sw.Promed.vac.utils.consoleLog(sw.Promed.vac.utils.msgBoxErrorBd(response));

				if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
					//alert(111);
				   // flag_Save = 1;
					Ext.getCmp('ammOnkoProfileEditWindow').fieldsetFormSetOriginalValue();
				   var additionalData = new Object();
					
					var obj = Ext.util.JSON.decode(response.responseText);
					sw.Promed.vac.utils.consoleLog('Error_Code');
					sw.Promed.vac.utils.consoleLog(obj.rows[0].Error_Code);
					$paramOnkoProfile = obj.rows[0].PersonOnkoProfile_id;
					if($paramOnkoProfile && Ext.getCmp('ammOnkoProfileEditWindow').formParams.action == 'add'){
						Ext.getCmp('ammOnkoProfileEditWindow').formParams.action = 'edit';
					}
					 this.formParams.PersonOnkoProfile_id = $paramOnkoProfile;
					 sw.Promed.vac.utils.consoleLog('$paramOnkoProfile');
					sw.Promed.vac.utils.consoleLog($paramOnkoProfile);

					if ( this.ReportType == 'geriatrics' ) {
						Ext.getCmp('Anket_AgeNotHindrance_id').setValue(obj.rows[0].AgeNotHindrance_id);
						additionalData.AgeNotHindrance_id = obj.rows[0].AgeNotHindrance_id;

						if ( !Ext.isEmpty(obj.rows[0].Alert_Msg) ) {
							sw.swMsg.alert('Внимание', obj.rows[0].Alert_Msg);
						}
					}

					if ( this.ReportType == 'birads' ) {
						additionalData.CategoryBIRADS_id = obj.rows[0].CategoryBIRADS_id;
					}
					if ( this.ReportType == 'recist' ) {
						additionalData.ResultRECIST_id = obj.rows[0].ResultRECIST_id;
					}

					this.isPersonOnkoProfileSaved = true;
					if (typeof this.callback == 'function') {
						this.callback(this, $paramOnkoProfile, additionalData);
					}
				   
					 
					if (this.formParams.parent_id != undefined) {
						sw.Promed.vac.utils.consoleLog(this.formParams.parent_id);
						if (this.formParams.parent_id == 'amm_OnkoCtrl_ProfileJurnal') {
						sw.Promed.vac.utils.consoleLog('parent_id');
						sw.Promed.vac.utils.consoleLog(this.formParams.parent_id);
						var $params4parent = new Object();
						
						$params4parent.action = this.formParams.action;

						// Формирование данных для обновления строки в журнале
						$params4parent.action = this.formParams.action;
						$params4parent.Person_id = this.formParams.Person_id;
						$params4parent.New_PersonOnkoProfile_id = $paramOnkoProfile;
						$params4parent.PersonOnkoProfile_DtBeg = Ext.getCmp('OnkoCtrl_Date').getValue();
						$params4parent.MedStaffFact_id = params.MedStaffFact_id;
						$params4parent.MedPersonal_Fio =  Ext.getCmp('OnkoCtrl_MedPersonalCombo').getFieldValue ("MedPersonal_Fio")
						$params4parent.ProfileResult = '';
						if ($ProfileResult.length > 0) {
							$params4parent.ProfileResult = langs('Вопросы:')+' '+$ProfileResult.join(', ');
						}

						if (this.formParams.parent_id == 'amm_OnkoCtrl_ProfileJurnal')
							Ext.getCmp(this.formParams.parent_id).fireEvent('success', 'ammOnkoProfileEditWindow', $params4parent);
						}
					}   
					 if ($action == 'print') {
						var $Person = this.formParams.Person_id;
						var paramSex = this.PersonInfoPanel.getFieldValue('Sex_id');
						if (this.ReportType == 'palliat') {
							printBirt({
								'Report_FileName': 'PalliatPersonProfile.rptdesign',
								'Report_Params': '&paramPerson=' + $Person + '&paramPalliatQuestion=' + $paramOnkoProfile,
								'Report_Format': 'pdf'
							});
						} else if(this.ReportType == 'geriatrics'){
							printBirt({
								'Report_FileName': 'GeriatricsAnketaprint.rptdesign',
								'Report_Params': '&paramPerson=' + $Person + '&paramGeriatricsQuestion=' + $paramOnkoProfile,
								'Report_Format': 'pdf'
							});
						} else if (this.ReportType == 'birads'){
							printBirt({
								'Report_FileName': 'Print_BIRADSQuestion.rptdesign',
								'Report_Params': '&paramLpu=' + getGlobalOptions().lpu_id + '&paramBIRADSQuestion=' + $paramOnkoProfile,
								'Report_Format': 'pdf'
							});
						} else {
							printBirt({
								'Report_FileName': 'onkoPersonProfile.rptdesign',
							    'Report_Params': '&paramPerson=' + $Person + '&paramOnkoProfile=' + $paramOnkoProfile + '&paramSex=' + paramSex,
							    'Report_Format': 'pdf'
						    });
						 }
					} else //  Если не печатаем, то закрываем окно
						Ext.getCmp('ammOnkoProfileEditWindow').hide();
				}
			   
			}.createDelegate(this)
		});
		//return $PersonOnkoProfile_id;           
     },
	createFieldset: function(item) {  
		var panel = Ext.getCmp('fieldsetForm');
		var id = item.OnkoQuestions_id;
		var name = item.OnkoQuestions_Name;
		var num = item.Questions_Num ? item.Questions_Num + ') ' : '';
		var fieldset = {
			title: num + name,
			id: 'fieldsetForm' + id,
			xtype: 'fieldset',
			layout: 'form',
			autoHeight: true,
			border: true,
			items: []
		};
		panel.add(fieldset);
		panel.add({height: 5, border: false});
		panel.doLayout();
    },
	createCheckbox: function(item, index) {
		var id = item.OnkoQuestions_id;
		var pid = item.OnkoQuestions_pid;
		var name = item.OnkoQuestions_Name;
		var num = item.Questions_Num ? item.Questions_Num : (index+1);
		var val = item.val;

		if (pid) {
			var panel = Ext.getCmp('fieldsetForm'+pid);
		} else {
			var panel = Ext.getCmp('fieldsetForm');
		}

		var element = {
			xtype: 'swcheckbox',
			height: 24,
			oId: id,
			border: false,
			tabIndex: TABINDEX_ONKOCTRL + 2,
			id: 'OnkoCtrl_Check' + id,
			checked: false,
			labelSeparator: ''
		};
		var wrapper = {
			bodyStyle: (this.ReportType == 'previzit' && !pid) ? 'padding-left: 11px;' : '',
			layout: 'form',
			labelWidth: 770,
			items: [element]
		};
		panel.add(wrapper);
		panel.add({height: 5, border: false});
		panel.doLayout();
		Ext.getCmp('OnkoCtrl_Check' + id).setFieldLabel(num + ') ' + name);
		Ext.getCmp('OnkoCtrl_Check' + id).setValue(val);
	},
	createTextField: function(item, index) {
		var id = item.OnkoQuestions_id;
		var pid = item.OnkoQuestions_pid;
		var name = item.OnkoQuestions_Name;
		var num = item.Questions_Num ? item.Questions_Num : (index+1);
		var val = item.val;

		var panel = Ext.getCmp('fieldsetForm');

		var element = {
			xtype: 'textfield',
			width: 200,
			height: 24,
			oId: id,
			allowBlank: (item.IsRequired != 2),
			tabIndex: TABINDEX_ONKOCTRL + 2,
			id: 'OnkoCtrl_Text' + id,
			labelSeparator: ''
		};
		var wrapper = {
			bodyStyle: (this.ReportType == 'previzit' && !pid) ? 'padding-left: 11px;' : '',
			layout: 'form',
			labelWidth: 600,
			items: [element]
		};
		panel.add(wrapper);
		panel.add({height: 5, border: false});
		panel.doLayout();
		Ext.getCmp('OnkoCtrl_Text' + id).setFieldLabel(num + ') ' + name);
		Ext.getCmp('OnkoCtrl_Text' + id).setValue(val);
	},
	createRbGroup: function(item, index, form) {
		var id = item.OnkoQuestions_id;
		var pid = item.OnkoQuestions_pid;
		var name = item.OnkoQuestions_Name;
		var num = item.Questions_Num ? item.Questions_Num : (index+1);
		var answerClass = item.AnswerClass_SysNick;
		var val = item.val;

		if (pid) {
			var panel = Ext.getCmp('fieldsetForm'+pid);
		} else {
			var panel = Ext.getCmp('fieldsetForm');
		}
		
		var idField = answerClass + '_id';
		var codeField = answerClass + '_Code';
		var displayField = answerClass + '_Name';
		var items = [];

		if (form.ReportType == 'recist') {
			var rbstore = new Ext.data.Store({
				autoLoad: false,
				reader: new Ext.data.JsonReader({
					id: 'MedicalFormAnswers_id'
				}, [
					{ name: 'MedicalFormAnswers_id', mapping: 'MedicalFormAnswers_id', type: 'int' },
					{ name: 'MedicalFormQuestion_id', mapping: 'MedicalFormQuestion_id', type: 'int' },
					{ name: 'MedicalFormAnswers_Name', mapping: 'MedicalFormAnswers_Name', type: 'string' }
				]),
				baseParams:{
					'OnkoQuestions_id' : item.OnkoQuestions_id
				},
				sortInfo: {field: 'MedicalFormQuestion_id'},
				url: '/?c=RECISTQuestion&m=loadMedicalFormAnswers'
			});
		} else {		
			var rbstore = new Ext.db.AdapterStore({
				autoLoad: true,
				dbFile: 'Promed.db',
				fields: [
					{name: idField, mapping: idField},
					{name: codeField, mapping: codeField},
					{name: displayField, mapping: displayField}
				],
				key: idField,
				sortInfo: {field: codeField},
				tableName: answerClass
			});
		}
		
		rbstore.load({
			callback: function() {
				rbstore.findBy(function(rec) {
					items.push({boxLabel: rec.get(answerClass + '_Name'), name: 'OnkoCtrl_Rg' + id, value: rec.get(answerClass + '_id'), height: 15, checked: val == rec.get(answerClass + '_id')});
				});
				var element = {
					vertical: true,	
					columns: 1,
					xtype: 'radiogroup',
					id: 'OnkoCtrl_Rg' + id,
					name: 'OnkoCtrl_Rg' + id,
					oId: id,
					tabIndex: TABINDEX_ONKOCTRL + 2,
					items: items,
					getValue: function() {
						var val = null;
						this.items.each(function(item){
							if(item.checked){
								val = item.value;
							}
						});
						return val;
					}
				};
				var wrapper = {
					layout: 'form',
					labelAlign: 'top',
					labelWidth: 550,
					items: [element]
				};
				panel.add(wrapper);
				panel.add({height: 5, border: false});
				panel.doLayout();
				Ext.getCmp('OnkoCtrl_Rg' + id).setFieldLabel(num + ') ' + name);
			}
		});

	},
	createSupCombobox: function(item, index) {
		this.createCombobox(item, index);

		var id = item.OnkoQuestions_id;
		var name = item.OnkoQuestions_Name;
		var num = item.Questions_Num ? item.Questions_Num : (index+1);
		var val = item.FreeForm;

		var panel = Ext.getCmp('fieldsetForm');

		var element = {
			xtype: 'textfield',
			width: 200,
			height: 24,
			oIdTx: id,
			allowBlank: (item.IsRequired != 2),
			tabIndex: TABINDEX_ONKOCTRL + 2,
			fieldLabel: 'Свой вариант',
			id: 'OnkoCtrl_Text' + id
		};
		var wrapper = {
			layout: 'form',
			labelWidth: 625,
			items: [element]
		};
		panel.add(wrapper);
		panel.add({height: 5, border: false});
		panel.doLayout();
		Ext.getCmp('OnkoCtrl_Text' + id).setValue(val);
	},
	createCombobox: function(item, index) {
		var id = item.OnkoQuestions_id;
		var pid = item.OnkoQuestions_pid;
		var name = item.OnkoQuestions_Name;
		var num = item.Questions_Num ? item.Questions_Num : (index+1);
		var answerClass = item.AnswerClass_SysNick;
		var val = item.val;
		var labelWidth = 625;

		if (pid) {
			var panel = Ext.getCmp('fieldsetForm'+pid);
		} else {
			var panel = Ext.getCmp('fieldsetForm');
		}

		var element = {
			width: 200,
			height: 24,
			oId: id,
			allowBlank: (item.IsRequired != 2),
			tabIndex: TABINDEX_ONKOCTRL + 2,
			id: 'OnkoCtrl_Combo' + id,
			labelSeparator: ''
		};

		switch(answerClass) {
			case 'Diag':
				element = Ext.apply(element, {
					xtype: 'swdiagcombo'
				});
				break;
			case 'PalliatPPSScale':
				labelWidth = 225;
				element = Ext.apply(element, {
					xtype: 'swbaselocalcombo',
					fieldLabel: 'Шкала PPS',
					codeField: 'PalliatPPSScale_Percent',
					initTrigger: this.inT,
					triggerConfig: {
						tag:'span', cls:'x-form-twin-triggers', cn:[
						{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-select-trigger"},
						{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-search-trigger"}
					]},
					onTrigger1Click: function() {
						this.focus(true);
						if(this.getStore().getCount()>0) {
							( this.isExpanded() ) ? this.collapse() : this.expand();
						}
					},
					onTrigger2Click: function() {
						getWnd('swPalliatPPSScaleSelectWindow').show({
							callback: function(PalliatPPSScale_id) {
								if (PalliatPPSScale_id) Ext.getCmp('Anket_PalliatPPSScale_id').setValue(PalliatPPSScale_id);
							}
						});
					}.createDelegate(this),
					triggerAction: 'all',
					editable: false,
					width: 600,
					readOnly: true,
					listWidth: 600,
					mode: 'local',
					valueField: 'PalliatPPSScale_id',
					displayField: 'PalliatPPSScale_MoveAbility',
					enableKeyEvents: true,
					store: new Ext.data.Store({
						mode: 'local',
						autoLoad: false,
						reader: new Ext.data.JsonReader({
							id: 'PalliatPPSScale_id'
						}, [
							{ mapping: 'PalliatPPSScale_id', name: 'PalliatPPSScale_id', type: 'int' },
							{ mapping: 'PalliatPPSScale_Percent', name: 'PalliatPPSScale_Percent', type: 'string' },
							{ mapping: 'PalliatPPSScale_MoveAbility', name: 'PalliatPPSScale_MoveAbility', type: 'string' },
							{ mapping: 'PalliatPPSScale_ActivityType', name: 'PalliatPPSScale_ActivityType', type: 'string' },
							{ mapping: 'PalliatPPSScale_SelfCare', name: 'PalliatPPSScale_SelfCare', type: 'string' },
							{ mapping: 'PalliatPPSScale_Diet', name: 'PalliatPPSScale_Diet', type: 'string' },
							{ mapping: 'PalliatPPSScale_ConsiousLevel', name: 'PalliatPPSScale_ConsiousLevel', type: 'string' }
						]),
						url: '/?c=PalliatQuestion&m=loadPalliatPPSScale'
					}),
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<font color="red">{PalliatPPSScale_Percent}%</font>&nbsp;{PalliatPPSScale_MoveAbility}.',
						'&nbsp;{PalliatPPSScale_ActivityType}.',
						'&nbsp;{PalliatPPSScale_SelfCare}.',
						'&nbsp;{PalliatPPSScale_Diet}.',
						'&nbsp;{PalliatPPSScale_ConsiousLevel}.',
						'</div></tpl>'
					)
				});
				break;
			case 'PalliatPainScale':
				labelWidth = 225;
				element = Ext.apply(element, {
					xtype: 'swbaselocalcombo',
					fieldLabel: 'Болевой синдром по шкале боли',
					codeField: 'PalliatPainScale_PointCount',
					editable: false,
					width: 600,
					listWidth: 600,
					mode: 'local',
					valueField: 'PalliatPainScale_id',
					displayField: 'PalliatPainScale_Characteristic',
					enableKeyEvents: true,
					store: new Ext.data.Store({
						mode: 'local',
						autoLoad: false,
						reader: new Ext.data.JsonReader({
							id: 'PalliatPainScale_id'
						}, [
							{ mapping: 'PalliatPainScale_id', name: 'PalliatPainScale_id', type: 'int' },
							{ mapping: 'PalliatPainScale_Characteristic', name: 'PalliatPainScale_Characteristic', type: 'string' },
							{ mapping: 'PalliatPainScale_PointCount', name: 'PalliatPainScale_PointCount', type: 'string' }
						]),
						url: '/?c=PalliatQuestion&m=loadPalliatPainScale'
					}),
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<font color="red">{PalliatPainScale_PointCount}</font>&nbsp;{PalliatPainScale_Characteristic}',
						'</div></tpl>'
					)
				});
				break;
			default:
				element = Ext.apply(element, {
					xtype: 'swcommonsprcombo',
					comboSubject: answerClass
				});
				break;
		}

		var wrapper = {
			layout: 'form',
			labelWidth: labelWidth,
			items: [element]
		};

		panel.add(wrapper);
		panel.add({height: 5, border: false});
		panel.doLayout();
		Ext.getCmp('OnkoCtrl_Combo' + id).setFieldLabel(num + ') ' + name);
		Ext.getCmp('OnkoCtrl_Combo' + id).setValue(val);

		if (Ext.getCmp('OnkoCtrl_Combo' + id).getStore().mode == 'local') {
			Ext.getCmp('OnkoCtrl_Combo' + id).getStore().load({
				callback: function() {
					Ext.getCmp('OnkoCtrl_Combo' + id).setValue(val);
				}
			});
		}
		if (answerClass == 'Diag' && !Ext.isEmpty(val)) {
			Ext.getCmp('OnkoCtrl_Combo' + id).getStore().load({
				params: { where: "where DiagLevel_id = 4 and Diag_id = " + val },
				callback: function() {
					Ext.getCmp('OnkoCtrl_Combo' + id).setValue(val);
				}
			});
		}
	},
	createSeparator: function(item, index) {
		var pid = item.OnkoQuestions_pid;
		var name = item.OnkoQuestions_Name;
		var num = item.Questions_Num ? item.Questions_Num : (index+1);

		if (pid) {
			var panel = Ext.getCmp('fieldsetForm'+pid);
		} else {
			var panel = Ext.getCmp('fieldsetForm');
		}

		var cfg = {
			html: '<b>' + num + ') '+name + '</b>',
			border: false,
			style: 'padding: 0 0 10px; font-size: 12px;'
		};

		var field = panel.add(cfg);
	},
	loadQuestions: function() {
		var form = this;
		var base_form = form.FormPanelOnkoQuestions.getForm();
		var panel = Ext.getCmp('fieldsetForm');
		panel.removeAll();
		panel.getEl().dom.querySelector("div").querySelector("div").innerHTML = '';

		var date = Ext.getCmp('OnkoCtrl_Date').getValue();

		if (Ext.isEmpty(date)) {
			return;
		}

		var params = {};
		var formStoreRecord = form.formStore.getAt(0);
		params.PersonOnkoProfile_id = formStoreRecord ? formStoreRecord.get('PersonOnkoProfile_id') : null;
		params.OnkoCtrl_Date = Ext.util.Format.date(date, 'd.m.Y');
		params.Person_id = form.formParams.Person_id;

		Ext.Ajax.request({
			url: '/?c='+form.controller+'&m=getOnkoQuestions',
			params: params,
			callback: function(options, success, response) {
				if (success) {
					var result = Ext.util.JSON.decode(response.responseText);

					var i = 0;
					result.forEach(function(item, index) {
						var answerType = item.AnswerType_Code;
						var answerClass = item.AnswerClass_SysNick;
						var QuestionKind = item.QuestionKind_id;

						if (QuestionKind && QuestionKind == 1) {
							form.createFieldset(item);
						} else {
							switch(true) {
								case (answerType == 1 || (answerType == 3 && answerClass == 'AnswerYesNoType')):
									form.createCheckbox(item, i);
									break;
								case (answerType == 2):
									form.createTextField(item, i);
									break;
								case (answerType == 3 && answerClass.inlist(['AnswerMalign', 'MedicalFormAnswers'])):
									form.createRbGroup(item, i, form);
									break;
								case (answerType == 3):
									form.createCombobox(item, i);
									break;
								case (answerType == 4):
									form.createSupCombobox(item, i);
									break;
								case (answerType == null):
									form.createSeparator(item, i);
									break;
							}

							i++;
						}
					});

					Ext.getCmp('ammOnkoProfileEditWindow').fieldsetFormSetOriginalValue();
					Ext.getCmp('fieldsetForm').doLayout();
				}
			}
		});
	},
    initComponent: function() {
        var params = {};
        var form = this;
		
		this.inT = function(){
			var ts = this.trigger.select('.x-form-trigger', true);
			this.wrap.setStyle('overflow', 'hidden');
			var triggerField = this;
			ts.each(function(t, all, index){
				t.hide = function(){
					var w = triggerField.wrap.getWidth();
					this.dom.style.display = 'none';
					triggerField.el.setWidth(w-triggerField.trigger.getWidth());
				};
				t.show = function(){
					var w = triggerField.wrap.getWidth();
					this.dom.style.display = '';
					triggerField.el.setWidth(w-triggerField.trigger.getWidth());
				};
				var triggerIndex = 'Trigger'+(index+1);
				if(this['hide'+triggerIndex]){
					t.dom.style.display = 'none';
				}
				t.on("click", this['on'+triggerIndex+'Click'], this, {preventDefault:true});
				t.addClassOnOver('x-form-trigger-over');
				t.addClassOnClick('x-form-trigger-click');
			}, this);
			this.triggers = ts.elements;
		}


        /**
         * хранилище для доп сведений
         */

        this.formStore = new Ext.data.JsonStore({
            fields: ['PersonOnkoProfile_id', 'Person_id', 'PersonOnkoProfile_DtBeg', 'Lpu_id', 'Lpu_Nick', 'MedStaffFact_id', 'MorbusGeriatrics_id', 'Diag_setDate', 'Diag_Code', 'Diag_Name', 'Diag_id', 'Evn_id', 'Evn_setDT', /*'GeriatricsQuestion_Other',*/ 'GeriatricsQuestion_CountYes', 'AgeNotHindrance_id', 'PalliatQuestion_Other', 'PalliatQuestion_CountYes', 'PalliatPPSScale_id', 'PalliatPainScale_id', 'CategoryBIRADS_id', 'ResultRECIST_id', 'EvnDirection_id'],
            url: '/?c=OnkoCtrl&m=loadOnkoContrProfileFormInfo',
            key: 'PersonOnkoProfile_id',
            root: 'data'
        });

        this.PersonInfoPanel = new sw.Promed.PersonInfoPanel({
            titleCollapse: true,
            floatable: false,
            collapsible: true,
            bodyStyle: 'padding: 0px',
            collapsed: true,
            plugins: [Ext.ux.PanelCollapsedTitle],
            region: 'north'
        });

        this.FormPanelOnkoQuestions = new Ext.form.FormPanel({
            autoScroll: true,
            bodyBorder: false,
            bodyStyle: 'padding: 5px',
            border: false,
            frame: true,
            id: 'FormPanelOnkoQuestions',
            region: 'center',
            labelAlign: 'left',
            autohight: true,
            labelWidth: 130,
            layout: 'form',

            items: [{
				id: 'Anket_MorbusGeriatrics_id',
				name: 'MorbusGeriatrics_id',
				xtype: 'hidden'
			}, {
				height: 5,
				border: false
			}, {
				border: false,
				layout: 'column',
				labelWidth: 130,
				items: [{
					width: 450,
					border: false,
					layout: 'form',
					items: [{
						fieldLabel: langs('Дата анкетирования'),
						style: 'padding: 0px 5px;',
						tabIndex: TABINDEX_ONKOCTRL + 1,
						allowBlank: false,
						labelWidth: 130,
						width: 200,
						xtype: 'swdatefield',
						format: 'd.m.Y',
						maxValue: new Date(),
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
						listeners: {
							'change': function(cmp, valNew, valOld) {
								if(Ext.isEmpty(valOld)) return;
								form.loadQuestions();
							}
						},
						id: 'OnkoCtrl_Date'
					}]
				}, {
					autoHeight: true,
					id: 'OnkoCtrl_FieldsetLpu',
					style: 'margin: 5px; padding: 5px;',
					title: langs('Анкетирование провел'),
					xtype: 'fieldset',
					layout: 'form',
					border: true,
					labelWidth: 100,
					items: [{
						id: 'OnkoCtrl_LpuNick',
						fieldLabel: langs('МО'),
						width: 260,
						height: 40,
						xtype: 'textarea',
						disabled: true
					}, {
						id: 'OnkoCtrl_LpuBuildingCombo',
						listWidth: 500,
						linkedElements: [
							'OnkoCtrl_LpuSectionCombo'
						],
						tabIndex: TABINDEX_ONKOCTRL + 17,
						width: 260,
						xtype: 'swlpubuildingglobalcombo'
					}, {
						id: 'OnkoCtrl_LpuSectionCombo',
						linkedElements: [
							'OnkoCtrl_MedPersonalCombo'
						],
						listWidth: 500,
						parentElementId: 'OnkoCtrl_LpuBuildingCombo',
						tabIndex: TABINDEX_ONKOCTRL + 18,
						width: 260,
						xtype: 'swlpusectionglobalcombo'
					}, {
						allowBlank: false,
						fieldLabel: langs('Врач'),
						hiddenName: 'MedStaffOnkoCtrl_id',
						id: 'OnkoCtrl_MedPersonalCombo', 
						lastQuery: '',
						tableName: 'MedStaffFact',
						parentElementId: 'OnkoCtrl_LpuSectionCombo',
						listWidth: 500,
						tabIndex: TABINDEX_ONKOCTRL + 19,
						onTabAction: function() {
							Ext.getCmp('OnkoCtrl_Check1').focus();
						}.createDelegate(this),
						width: 260,
						readOnly: true,
						value: null,
						emptyText: VAC_EMPTY_TEXT,
						xtype: 'swmedstafffactglobalcombo'
					}]
				}]
			}, {
				autoHeight: true,
				id: 'PalliatQuestion_OtherFieldset',
				xtype: 'fieldset',
				border: true,
				labelWidth: 120,
				items: [{
					id: 'Anket_PalliatQuestion_CountYes',
					fieldLabel: 'Количество баллов',
					width: 100,
					xtype: 'textfield',
					disabled: true
				}]
			}, {
				autoHeight: true,
				id: 'BIRADSQuestion_OtherFieldset',
				xtype: 'fieldset',
				border: true,
				labelWidth: 120,
				items: [{
					comboSubject: 'CategoryBIRADS',
					disabled: true,
					fieldLabel: langs('Категория BI-RADS'),
					id: 'Anket_CategoryBIRADS_id',
					width: 200,
					xtype: 'swcommonsprcombo'
				}]
			}, {
				autoHeight: true,
				id: 'RECISTQuestion_OtherFieldset',
				xtype: 'fieldset',
				border: true,
				labelWidth: 120,
				items: [{
					comboSubject: 'MedicalFormDecision',
					disabled: true,
					fieldLabel: langs('Общий ответ'),
					id: 'Anket_ResultRECIST_id',
					width: 250,
					xtype: 'swcommonsprcombo'
				}]
			}, {
				autoHeight: true,
				id: 'GeriatricsQuestion_OtherFieldset',
				xtype: 'fieldset',
				border: true,
				labelWidth: 120,
				items: [/*{
					disabled: false,
					fieldLabel: langs('Иные признаки'),
					id: 'GeriatricsQuestion_Other',
					width: 690,
					xtype: 'textfield'
				},*/ {
					disabled: true,
					fieldLabel: langs('Количество баллов'),
					id: 'Anket_GeriatricsQuestion_CountYes',
					width: 100,
					xtype: 'textfield'
				}, {
					comboSubject: 'AgeNotHindrance',
					disabled: true,
					fieldLabel: langs('Результат'),
					id: 'Anket_AgeNotHindrance_id',
					width: 200,
					xtype: 'swcommonsprcombo'
				}]
			}, {
				autoHeight: true,
				id: 'fieldsetForm',
				title: langs('Вопросы анкеты'),
				xtype: 'fieldset',
				border: true,
				labelWidth: 750,
				items: []
			}]
        });

        Ext.apply(this, {
            buttons: [{
				text: langs('Сохранить'),
				iconCls: 'save16',
				id: 'Oncoctrl_ButtonSave',
				tabIndex: TABINDEX_ONKOCTRL + 12,
				handler: function(b) { 
					if ((!Ext.getCmp('OnkoCtrl_MedPersonalCombo').isValid()) || (!Ext.getCmp('OnkoCtrl_Date').isValid()))  {
						sw.Promed.vac.utils.msgBoxNoValidForm();
						return false;
					}
				   this.saveRecord();
				}.createDelegate(this)
			}, {
				id: 'Oncoctrl_Print',
				iconCls: 'print16',
				tabIndex: TABINDEX_ONKOCTRL + 13,
				text: langs('Печать'),
				handler: function() {
					var $Person = Ext.getCmp('ammOnkoProfileEditWindow').formParams.Person_id;
					var $paramOnkoProfile = Ext.getCmp('ammOnkoProfileEditWindow').formParams.PersonOnkoProfile_id;
					var paramSex = Ext.getCmp('ammOnkoProfileEditWindow').PersonInfoPanel.getFieldValue('Sex_id');

					if (Ext.isEmpty($paramOnkoProfile) || (Ext.getCmp('ammOnkoProfileEditWindow').formParams.action == 'edit' && Ext.getCmp('ammOnkoProfileEditWindow').fieldsetFormIsDirty())) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if (buttonId === 'yes') {
									$paramOnkoProfile= Ext.getCmp('ammOnkoProfileEditWindow').saveRecord('print');
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: langs('Чтобы распечатать, анкету необходимо сохранить! Продолжить?'),
							title: langs('Печать анкеты')
						});
					}
					else {
						if (form.ReportType == 'palliat') {
							printBirt({
								'Report_FileName': 'PalliatPersonProfile.rptdesign',
								'Report_Params': '&paramPerson=' + $Person + '&paramPalliatQuestion=' + $paramOnkoProfile,
								'Report_Format': 'pdf'
							});
						} else if(form.ReportType == 'geriatrics'){
							printBirt({
								'Report_FileName': 'GeriatricsAnketaprint.rptdesign',
								'Report_Params': '&paramPerson=' + $Person + '&paramGeriatricsQuestion=' + $paramOnkoProfile,
								'Report_Format': 'pdf'
							});
						} else if (form.ReportType == 'birads'){
							printBirt({
								'Report_FileName': 'Print_BIRADSQuestion.rptdesign',
								'Report_Params': '&paramLpu=' + getGlobalOptions().lpu_id + '&paramBIRADSQuestion=' + $paramOnkoProfile,
								'Report_Format': 'pdf'
							});
						} else {
							printBirt({
								'Report_FileName': 'onkoPersonProfile.rptdesign',
								'Report_Params': '&paramPerson=' + $Person + '&paramOnkoProfile=' + $paramOnkoProfile + '&paramSex=' + paramSex,
								'Report_Format': 'pdf'
							});
						}
					}
				}
			}, {
				text: '-'
			},
			{text: BTN_FRMHELP,
				iconCls: 'help16',
				tabIndex: TABINDEX_ONKOCTRL + 14,
				handler: function(button, event)
				{
					ShowHelp(langs('Анкетирование'));
				}
			}, {
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				tabIndex: TABINDEX_ONKOCTRL + 15,
				onTabAction: function() {
					Ext.getCmp('OnkoCtrl_Date').focus();
				}.createDelegate(this),
				text: langs('Закрыть')
			}],
            items: [
                this.PersonInfoPanel,
                this.FormPanelOnkoQuestions
            ]
        });

        sw.Promed.amm_OnkoCtr_ProfileEditWindow.superclass.initComponent.apply(this, arguments);
    },
    show: function(record) {
        sw.Promed.amm_OnkoCtr_ProfileEditWindow.superclass.show.apply(this, arguments);
        this.isPersonOnkoProfileSaved = false;
        if (record.callback) {
            this.callback = record.callback;
            delete record.callback;
        } else {
            this.callback = Ext.emptyFn;
        }
        if (record.onHide) {
            this.onHide = record.onHide;
            delete record.onHide;
        } else {
            this.onHide = Ext.emptyFn;
        }
        this.formParams = record;
        var form = this,
            base_form = this.FormPanelOnkoQuestions.getForm();
        base_form.reset();
       
		this.ReportType = record.ReportType || 'onko';
		
		Ext.getCmp('PalliatQuestion_OtherFieldset').setVisible(this.ReportType == 'palliat');
		Ext.getCmp('GeriatricsQuestion_OtherFieldset').setVisible(this.ReportType == 'geriatrics');
		Ext.getCmp('BIRADSQuestion_OtherFieldset').setVisible(this.ReportType == 'birads');
		Ext.getCmp('RECISTQuestion_OtherFieldset').setVisible(this.ReportType == 'recist');
		Ext.getCmp('OnkoCtrl_FieldsetLpu').setVisible(this.ReportType != 'previzit');

		if ( this.ReportType == 'geriatrics' ) {
			//this.buttons[1].hide();

			if ( !Ext.isEmpty(record.MorbusGeriatrics_id) ) {
				Ext.getCmp('Anket_MorbusGeriatrics_id').setValue(record.MorbusGeriatrics_id);
			}
		}
		else {
			this.buttons[1].show();
		}
		
		Ext.getCmp('Oncoctrl_Print').setVisible(!this.ReportType.inlist(['previzit', 'recist']));
       
        var CurMedStaffFact_id = getGlobalOptions().CurMedStaffFact_id;

        this.PersonInfoPanel.load({
            callback: function() {
                form.PersonInfoPanel.setPersonTitle();
                form.PersonInfoPanel.setPersonTitle();
            },
            loadFromDB: true,
            Person_id: form.formParams.Person_id //record.person_id
                    //,Server_id: this.formParams.Server_id
        });
       
        var Params = {};
        Params.Person_id = this.formParams.Person_id;
		Params.PersonOnkoProfile_id = this.formParams.PersonOnkoProfile_id;

		if (this.ReportType.inlist(['birads', 'recist']) && arguments[0]) {
			Params.EvnUslugaPar_id = arguments[0].EvnUslugaPar_id;
			this.MedPersonal_id = arguments[0].MedPersonal_id;
			this.LpuSection_id = arguments[0].LpuSection_id;
		}

		// чтобы не перефигачивать всё, будем тупо подменять контроллер
		this.controller = sw.Promed.PersonOnkoProfile.getControllerName(this.ReportType);
		this.formStore.proxy.conn.url = '/?c=' + this.controller + '&m=loadOnkoContrProfileFormInfo';

        this.formStore.load({
            params: Params,
            callback: function() {
                var formStoreCount = form.formStore.getCount() > 0;
                var params = {};
               
                if (formStoreCount) {
                    var formStoreRecord = form.formStore.getAt(0);
                    params.PersonOnkoProfile_id = formStoreRecord.get('PersonOnkoProfile_id');
				
					if (form.ReportType == 'birads' && form.formParams.action == 'add') {
						if (!Ext.isEmpty(formStoreRecord.get('EvnDirection_id'))) {
							form.formParams.action = 'view';
						} else {
							form.formParams.action = 'edit';
						}
					}
				
					if (form.ReportType == 'previzit' && form.formParams.action == 'add' && params.PersonOnkoProfile_id > 0) {
						form.formParams.action = 'edit';
					}
					
					if (form.ReportType == 'recist' && form.formParams.action == 'add' && params.PersonOnkoProfile_id > 0) {
						form.formParams.action = 'view';
					}

					if (form.ReportType == 'onko') {
						if ((form.formParams.action == 'add') && (formStoreRecord.get('PersonOnkoProfile_id'))) {
							form.formParams.action = 'view'
							Ext.MessageBox.show({
								id: 'MessageBox1',
								title: langs('Внимание'),
								msg: langs('У пациента уже есть актуальная Анкета по онкоконтролю. <br />Следующее заполнение Анкеты на пациента - в новом календарном году. <br />Актуальная Анкета будет открыта в режиме "Просмотр"'),
								width: 500,
								buttons: Ext.Msg.OK
							});
						}
						else  if ((form.formParams.action == 'add') && (formStoreRecord.get('Diag_id'))) {
							form.hide();
							var $str = "Пациенту " + formStoreRecord.get('Diag_setDate') + " был поставлен диагноз <br />" + formStoreRecord.get('Diag_Code') + ' - "' + formStoreRecord.get('Diag_Name') + '".<br />'
							Ext.MessageBox.show({
								title: langs('Внимание'),
								msg: $str + "Заполнение Анкеты не требуется.",
								width: 500,
								buttons: Ext.Msg.OK
							});
							return false;
						}  
					}
					if (form.formParams.action == 'add') {
                        form.formParams.PersonOnkoProfile_id = null;
                        params.PersonOnkoProfile_id = null;
                        form.formParams.Lpu_id = getGlobalOptions().lpu_id;
                        form.formParams.Lpu_Nick = getGlobalOptions().lpu_nick;
                        form.formParams.MedStaffFact_id = CurMedStaffFact_id;
                    } else {
                        form.formParams.PersonOnkoProfile_id = formStoreRecord.get('PersonOnkoProfile_id');
                        form.formParams.MedStaffFact_id = formStoreRecord.get('MedStaffFact_id');
                        form.formParams.PersonOnkoProfile_DtBeg = formStoreRecord.get('PersonOnkoProfile_DtBeg');
                        form.formParams.Lpu_Nick = formStoreRecord.get('Lpu_Nick');
                        form.formParams.Lpu_id = formStoreRecord.get('Lpu_id');
                        
                        form.formParams.Evn_id = formStoreRecord.get('Evn_id'); 
                        form.formParams.Evn_setDT = formStoreRecord.get('Evn_setDT');
                        Ext.getCmp('OnkoCtrl_Date').setValue(formStoreRecord.get('PersonOnkoProfile_DtBeg'));

						if ( form.ReportType == 'palliat' ) {
							form.formParams.PalliatQuestion_CountYes = formStoreRecord.get('PalliatQuestion_CountYes');
							Ext.getCmp('Anket_PalliatQuestion_CountYes').setValue(formStoreRecord.get('PalliatQuestion_CountYes'));
						}
						else if ( form.ReportType == 'geriatrics' ) {
							//form.formParams.GeriatricsQuestion_Other = formStoreRecord.get('GeriatricsQuestion_Other');
							form.formParams.GeriatricsQuestion_CountYes = formStoreRecord.get('GeriatricsQuestion_CountYes');
							form.formParams.AgeNotHindrance_id = formStoreRecord.get('AgeNotHindrance_id');

							//Ext.getCmp('Anket_GeriatricsQuestion_Other').setValue(formStoreRecord.get('GeriatricsQuestion_Other'));
							Ext.getCmp('Anket_GeriatricsQuestion_CountYes').setValue(formStoreRecord.get('GeriatricsQuestion_CountYes'));
							Ext.getCmp('Anket_AgeNotHindrance_id').setValue(formStoreRecord.get('AgeNotHindrance_id'));
							Ext.getCmp('Anket_MorbusGeriatrics_id').setValue(formStoreRecord.get('MorbusGeriatrics_id'));
						}
						else if ( form.ReportType == 'birads' ) {
							form.formParams.CategoryBIRADS_id = formStoreRecord.get('CategoryBIRADS_id');
							
							Ext.getCmp('Anket_CategoryBIRADS_id').setValue(formStoreRecord.get('CategoryBIRADS_id'));
						}
						else if ( form.ReportType == 'recist' ) {
							form.formParams.ResultRECIST_id = formStoreRecord.get('ResultRECIST_id');

							Ext.getCmp('Anket_ResultRECIST_id').setValue(formStoreRecord.get('ResultRECIST_id'));
						}

						var $d = new Date;
                        var $days = 24 * 60 * 60 * 1000;
                        $d.setTime ($d.getTime() - $days);
                        if (form.formParams.action == 'edit') {
                            //if ((getGlobalOptions().medpersonal_id != form.formParams.MedPersonal_id) 
                            /* if ((CurMedStaffFact_id != form.formParams.MedStaffFact_id) 
                                    && (!isAdmin) && (!isLpuAdmin())) {
                                form.formParams.action = 'view';
                                
                                log (CurMedStaffFact_id + ' = ' + form.formParams.MedStaffFact_id);
                            }
                            else if ((isLpuAdmin()) && (form.formParams.Lpu_id != getGlobalOptions().lpu_id)) {
                                form.formParams.action = 'view';
                            }
                             else if ($d >=  Ext.getCmp('OnkoCtrl_Date').getValue()) {
                                 //  Анкету можно редактировать день в день
                                form.formParams.action = 'view'; 
                            }

                            if ( form.formParams.action == 'view')
                                Ext.MessageBox.show({
								title: langs('Внимание'),
								msg: "У Вас нет прав на редактирование! <br />Анкета откроется в режиме просмотра!",
								buttons: Ext.Msg.OK
							  });*/
                        }
                        
                    }
                }
				else if (form.formParams.action == 'add') {
                     
                    params.PersonOnkoProfile_id = null;
                    form.formParams.PersonOnkoProfile_id = null;
                    params.PersonOnkoProfile_id = null;
                    form.formParams.Lpu_id = getGlobalOptions().lpu_id;
                    form.formParams.Lpu_Nick = getGlobalOptions().lpu_nick;
                    form.formParams.MedStaffFact_id = CurMedStaffFact_id;
                }
				else {
                    return false
                }
                
               
                if (form.formParams.action == 'add') {
                    if (form.formParams.PersonOnkoProfile_DtBeg != undefined)
                        Ext.getCmp('OnkoCtrl_Date').setValue(form.formParams.PersonOnkoProfile_DtBeg);
                    else
                        Ext.getCmp('OnkoCtrl_Date').setValue(new Date);
                };   
                
                //  Определение минимальной и максимальной даты анкетирования
                 var $d =  Ext.getCmp('OnkoCtrl_Date').getValue ();
                 var $days = 30 * 24 * 60 * 60 * 1000;
                 var $d_min = new Date();     
                 var $d_max = new Date();     
                 $d_min.setTime($d.getTime() - $days);
                 $d_max.setTime($d.getTime() + $days);         
                    if ($d_max >= new Date())
                        $d_max =  new Date();

                Ext.getCmp('OnkoCtrl_Date').setMinValue ($d_min);
                Ext.getCmp('OnkoCtrl_Date').setMaxValue ($d_max);

                //var days_in_mohth = 32 - new Date($d.getFullYear(), $d.getMonth(), 32).getDay();
                //var $d_max = new Date($d.getFullYear(), $d.getMonth() , days_in_mohth);
                var $d_max = new Date($d.getFullYear(), $d.getMonth() + 1 , 0);


                var $title = langs('Анкетирование');

                //alert(this.formParams.action);

                if (form.formParams.action == 'edit') {
                    $title += langs(': Редактирование');
                    //alert(form.formParams.Evn_id );
                    if ( !Ext.isEmpty(form.formParams.Evn_setDT) ) {
                        $title += langs(' (посещение от ') + form.formParams.Evn_setDT + ')';
					}

                } else if (form.formParams.action == 'add')
                    $title += langs(': Добавление');
                else if (form.formParams.action == 'view')
                    $title += langs(': Просмотр');
                form.setTitle($title);
                Ext.getCmp('OnkoCtrl_LpuNick').setValue(form.formParams.Lpu_Nick);


				sw.Promed.vac.utils.consoleLog(form.formParams);
				Ext.getCmp('OnkoCtrl_MedPersonalCombo').setValue(form.formParams.MedStaffFact_id);
				Ext.getCmp('ammOnkoProfileEditWindow').actionEdit(form.formParams.action);
                
                var $lpu_id = form.formParams.Lpu_id;
                base_form.findField('LpuBuilding_id').getStore().load({
                    params : {Lpu_id: $lpu_id}
                    });

                base_form.findField('LpuSection_id').getStore().load({
                    params : {Lpu_id: $lpu_id}
                });
                
                
                base_form.findField('MedStaffOnkoCtrl_id').getStore().load({
                    params : {Lpu_id: $lpu_id},
                    callback: function() {
                        if (form.formParams.MedStaffFact_id != undefined) {
                            base_form.findField('MedStaffOnkoCtrl_id').setValue(form.formParams.MedStaffFact_id);
						}
                        else {
                            form.formParams.MedStaffFact_id = CurMedStaffFact_id;
                            base_form.findField('MedStaffOnkoCtrl_id').setValue(CurMedStaffFact_id);
                        }
                    }
                });

				form.loadQuestions();
            }
        });

		Ext.getCmp('OnkoCtrl_Date').focus(true, 50);
    },
    listeners: {
        'hide': function(win) {
            win.onHide(win);
        }
    }
});

