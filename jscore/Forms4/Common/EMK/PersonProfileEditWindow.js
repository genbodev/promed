Ext6.define('common.EMK.PersonProfileEditWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swPersonProfileEditWindow',
	cls: 'arm-window-new emk-forms-window save-template-window',
	title: 'Анкетирование',
	autoShow: false,
	constrain: true,
	modal: true,
	width: 800,
	height: 550,
	autoScroll: true,

	save: function() {
		var me = this;

		var params = me.getParams([
			me.mainFieldsPanel,
			me.palliatOtherFieldSet,
			me.geriatricsOtherFieldSet,
			me.biradsOtherFieldSet
		]);

		params.Profile_Date = params.PersonOnkoProfile_DtBeg;
		params.QuestionAnswer = Ext6.encode(me.getAnswers());
		
		if (!me.isValid) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: 'Не выбран обязательный ответ. Вернитесь в анкету.',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		me.mask('Сохранение...');

		Ext6.Ajax.request({
			url: '/?c='+me.controllerName+'&m=savePersonOnkoProfile',
			params: params,
			success: function(response) {
				me.unmask();
				me.callback();
				me.hide();
			},
			failure: function() {
				me.unmask();
			}
		});
	},

	getParams: function(panels) {
		var me = this;

		if (!Ext6.isArray(panels)) {
			panels = [panels];
		}

		var fields = panels.reduce(function(fields, panel) {
			return fields.concat(panel.isVisible()?panel.query('field'):[]);
		}, []);

		var params = {};
		fields.forEach(function(field) {
			switch(field.xtype) {
				case 'datefield':
					params[field.name] = typeof field.value == 'object' ? Ext6.util.Format.date(field.value, 'd.m.Y') : field.value;
					break;
				default:
					params[field.name] = field.value;
					break;
			}
		});

		return params;
	},

	getAnswers: function() {
		var me = this;
		var answers = [];
		me.isValid = true;
		me.questionsFieldSet.query('field').forEach(function(field) {
			if (!Ext6.isEmpty(field.oId))  {
				var freeField = me.query('[oIdTx="'+field.oId+'"]');
				var textAns = freeField.length ? freeField[0].value : null;
				if (field.allowBlank === false && Ext6.isEmpty(field.value) && Ext6.isEmpty(textAns)) {
					me.isValid = false;
				}
				answers.push([field.oId, field.value, textAns]);
			}
		});
		return answers;
	},

	createFieldset: function(item) {  
		var me = this;
		var panel = me.questionsFieldSet;
		var id = item.OnkoQuestions_id;
		var name = item.OnkoQuestions_Name;
		var num = item.Questions_Num ? item.Questions_Num + ') ' : '';
		var fieldset = {
			title: '<b style="color: #333">' + num + name + '</b>',
			id: 'fieldsetForm' + id,
			xtype: 'fieldset',
			style: {
				paddingTop: '0',
				//marginTop: '-10px',
				paddingLeft: '20px'
			},
			border: true,
			defaults: {
				width: '100%',
				labelWidth: 150
			},
			items: []
		};
		panel.add(fieldset);
		panel.add({height: 5, border: false});
    },

	createSeparator: function(item, index) {
		var me = this;
		var pid = item.OnkoQuestions_pid;
		
		if (pid) {
			var panel = Ext6.getCmp('fieldsetForm'+pid);
		} else {
			var panel = me.questionsFieldSet;
		}

		var name = item.OnkoQuestions_Name;
		var num = item.Questions_Num ? item.Questions_Num : (index+1);

		var cfg = {
			html: '<b>' + num + ') '+name + '</b>',
			border: false,
			padding: '2 0 7 0'
		};

		var field = panel.add(cfg);
	},

	createCheckbox: function(item, index) {
		var me = this;
		var pid = item.OnkoQuestions_pid;
		var labelWidth = 710;
		
		if (pid) {
			var panel = Ext6.getCmp('fieldsetForm'+pid);
			labelWidth = 650;
		} else {
			var panel = me.questionsFieldSet;
		}

		var id = Number(item.OnkoQuestions_id);
		var name = item.OnkoQuestions_Name;
		var num = item.Questions_Num ? item.Questions_Num : (index+1);
		var val = item.val;

		var cfg = {
			xtype: 'checkbox',
			oId: id,
			name: 'check'+id,
			fieldLabel: num + ') '+name,
			labelSeparator: '',
			labelWidth: labelWidth
		};
		
		if (me.reportType == 'previzit' && !pid) {
			cfg.style = 'padding-left: 20px;';
			cfg.labelWidth -= 60;
		}

		var field = panel.add(cfg);
		field.setValue(val == 2);
	},

	createTextField: function(item, index) {
		var me = this;
		var pid = item.OnkoQuestions_pid;
		
		if (pid) {
			var panel = Ext6.getCmp('fieldsetForm'+pid);
		} else {
			var panel = me.questionsFieldSet;
		}

		var id = Number(item.OnkoQuestions_id);
		var name = item.OnkoQuestions_Name;
		var num = item.Questions_Num ? item.Questions_Num : (index+1);
		var val = item.val;

		var cfg = {
			xtype: 'textfield',
			oId: id,
			name: 'text'+id,
			allowBlank: (item.IsRequired != 2),
			fieldLabel: num + ') '+name,
			labelSeparator: '',
			labelWidth: 550
		};
		
		if (me.reportType == 'previzit' && !pid) {
			cfg.style = 'padding-left: 20px;';
			cfg.labelWidth -= 60;
		}

		var field = panel.add(cfg);
		field.setValue(val);
	},

	createSupCombobox: function(item, index) {
		
		this.createCombobox(item, index);
		
		var me = this;
		var pid = item.OnkoQuestions_pid;
		
		if (pid) {
			var panel = Ext6.getCmp('fieldsetForm'+pid);
		} else {
			var panel = me.questionsFieldSet;
		}

		var id = Number(item.OnkoQuestions_id);
		var name = item.OnkoQuestions_Name;
		var num = item.Questions_Num ? item.Questions_Num : (index+1);
		var val = item.FreeForm;

		var cfg = {
			xtype: 'textfield',
			oIdTx: id,
			name: 'text'+id,
			fieldLabel: 'Свой вариант',
			labelSeparator: '',
			labelWidth: 550
		};

		var field = panel.add(cfg);
		field.setValue(val);
	},

	createCombobox: function(item, index) {
		var me = this;
		var pid = item.OnkoQuestions_pid;
		
		if (pid) {
			var panel = Ext6.getCmp('fieldsetForm'+pid);
		} else {
			var panel = me.questionsFieldSet;
		}

		var id = Number(item.OnkoQuestions_id);
		var name = item.OnkoQuestions_Name;
		var num = item.Questions_Num ? item.Questions_Num : (index+1);
		var answerClass = item.AnswerClass_SysNick;
		var val = item.val;

		var cfg = {
			oId: id,
			name: 'combo'+id,
			allowBlank: (item.IsRequired != 2),
			fieldLabel: num + ') '+name,
			labelSeparator: '',
			labelWidth: 550
		};

		switch(answerClass) {
			case 'Diag':
				Ext6.apply(cfg, {
					xtype: 'swDiagCombo',
					userCls: 'diagnoz',
					labelWidth: 350
				});
				break;
			case 'PalliatPPSScale':
				Ext6.apply(cfg, {
					xtype: 'swPalliatPPSScaleCombo',
					labelWidth: 150
				});
				break;
			case 'PalliatPainScale':
				Ext6.apply(cfg, {
					xtype: 'swPalliatPainScaleCombo',
					labelWidth: 150
				});
				break;
			default:
				Ext6.apply(cfg, {
					xtype: 'commonSprCombo',
					comboSubject: answerClass
				});
				break;
		}

		var field = panel.add(cfg);
		field.setValue(val);

		if (field.store.mode == 'local') {
			field.store.load({
				callback: function() {
					field.setValue(val);
				}
			});
		}
		if (answerClass == 'Diag' && !Ext6.isEmpty(val)) {
			field.store.load({
				params: { where: "where DiagLevel_id = 4 and Diag_id = " + val },
				callback: function() {
					field.setValue(val);
				}
			});
		}
	},

	loadQuestions: function() {
		var me = this;
		var baseForm = me.formPanel.getForm();

		me.questionsFieldSet.removeAll();

		var date = baseForm.findField('PersonOnkoProfile_DtBeg').getValue();

		if (Ext6.isEmpty(date)) {
			return;
		}

		var params = {
			OnkoCtrl_Date: Ext6.util.Format.date(date, 'd.m.Y'),
			PersonOnkoProfile_id: me.PersonProfile_id,
			Person_id: me.Person_id
		};

		me.mask('Загрузка...');

		Ext6.Ajax.request({
			url: '/?c='+me.controllerName+'&m=getOnkoQuestions',
			params: params,
			success: function(response) {
				me.unmask();
				var list = Ext6.decode(response.responseText);

				list.forEach(function(item, index) {
					var answerType = item.AnswerType_Code;
					var answerClass = item.AnswerClass_SysNick;
					var QuestionKind = item.QuestionKind_id;
						
					if (QuestionKind && QuestionKind == 1) {
						me.createFieldset(item);
					} else {
						switch(true) {
							case (answerType == 1 || (answerType == 3 && answerClass == 'AnswerYesNoType')):
								me.createCheckbox(item, index);
								break;
							case (answerType == 2):
								me.createTextField(item, index);
								break;
							case (answerType == 3):
								me.createCombobox(item, index);
								break;
							case (answerType == 4):
								me.createSupCombobox(item, index);
								break;
							case (answerType == null):
								me.createSeparator(item, index);
								break;
						}
					}
				});

				if (me.action == 'view') {
					me.questionsFieldSet.query('field').forEach(function(field) {
						field.disable();
					});
				}
			},
			failure: function() {
				me.unmask();
			}
		});
	},

	enableEdit: function(enable) {
		var me = this;

		me.query('field').forEach(function(field) {
			if (field.initialConfig.disabled) {
				field.setDisabled(true);
			} else {
				field.setDisabled(!enable);
			}
		});

		me.queryById(me.id+'-save-btn').setVisible(enable);
	},

	onSprLoad: function() {
		var me = this;
		var baseForm = me.formPanel.getForm();

		var personField = baseForm.findField('Person_id');
		var dateField = baseForm.findField('PersonOnkoProfile_DtBeg');
		var lpuCombo = baseForm.findField('Lpu_id');
		var lpuBuildingCombo = baseForm.findField('LpuBuilding_id');
		var lpuSectionCombo = baseForm.findField('LpuSection_id');
		var medStaffFactCombo = baseForm.findField('MedStaffFact_id');

		var params = {
			PersonOnkoProfile_id: me.PersonProfile_id,
			Person_id: me.Person_id
		};

		me.mask('Загрузка...');
		Ext6.Ajax.request({
			url: '/?c='+me.controllerName+'&m=loadOnkoContrProfileFormInfo',
			params: params,
			success: function(response) {
				me.unmask();
				var responseObj = Ext6.decode(response.responseText);

				if (Ext6.isArray(responseObj) && responseObj.length > 0) {
					baseForm.setValues(responseObj[0]);
				} else if (responseObj && responseObj.data) {
					baseForm.setValues(responseObj.data[0]);
				}
				
				if (me.reportType == 'previzit' && me.action == 'add' && !Ext6.isEmpty(baseForm.findField('PersonOnkoProfile_id').getValue())) {
					me.action = 'edit';
					me.PersonProfile_id = baseForm.findField('PersonOnkoProfile_id').getValue();
				}

				if (me.reportType == 'onko' && me.action == 'add') {
					if (!Ext6.isEmpty(baseForm.findField('PersonOnkoProfile_id').getValue())) {
						me.action = 'view';
						me.enableEdit(false);
						Ext6.Msg.alert(
							langs('Внимание'),
							langs(
								'У пациента уже есть актуальная Анкета по онкоконтролю. ' +
								'<br />Следующее заполнение Анкеты на пациента - в новом календарном году. ' +
								'<br />Актуальная Анкета будет открыта в режиме "Просмотр"'
							)
						);
					} else if (!Ext6.isEmpty(baseForm.findField('Diag_id').getValue())) {
						var Diag_setDate = baseForm.findField('Diag_setDate').getValue();
						var Diag_Code = baseForm.findField('Diag_Code').getValue();
						var Diag_Name = baseForm.findField('Diag_Name').getValue();
						var msg = 'Пациенту ' + Diag_setDate + ' был поставлен диагноз <br />' + Diag_Code + ' - "' + Diag_Name + '".<br />Заполнение Анкеты не требуется.';
						Ext6.Msg.alert(langs('Внимание'), msg);
						me.hide();
						return;
					}
				}

				if (me.action == 'add') {
					personField.setValue(me.Person_id);
					dateField.setValue(new Date());

					if (me.userMedStaffFact) {
						lpuCombo.setValue(me.userMedStaffFact.Lpu_id);
						lpuBuildingCombo.setValue(me.userMedStaffFact.LpuBuilding_id);
						lpuSectionCombo.setValue(me.userMedStaffFact.LpuSection_id);
						medStaffFactCombo.setValue(me.userMedStaffFact.MedStaffFact_id);
					}
				}

				me.loading = false;

				lpuBuildingCombo.store.load({
					params: {
						Lpu_id: lpuCombo.getValue()
					},
					callback: function () {
						lpuBuildingCombo.setValue(lpuBuildingCombo.getValue());
					}
				});

				lpuSectionCombo.store.load({
					params: {
						Lpu_id: lpuCombo.getValue(),
						LpuBuilding_id: lpuBuildingCombo.getValue()
					},
					callback: function () {
						lpuSectionCombo.setValue(lpuSectionCombo.getValue());
					}
				});

				medStaffFactCombo.store.load({
					params: {
						Lpu_id: lpuCombo.getValue(),
						LpuBuilding_id: lpuBuildingCombo.getValue(),
						LpuSection_id: lpuSectionCombo.getValue()
					},
					callback: function () {
						medStaffFactCombo.setValue(medStaffFactCombo.getValue());
					}
				});

				me.loadQuestions();
			},
			failure: function () {
				me.unmask();
				me.loading = false;
			}
		});
	},

	show: function() {
		var me = this;
		var baseForm = me.formPanel.getForm();

		me.loading = true;
		me.isValid = true;

		me.questionsFieldSet.removeAll();
		baseForm.reset();

		me.callback = arguments[0].callback || Ext.emptyFn;
		me.action = arguments[0].action;
		me.userMedStaffFact = arguments[0].userMedStaffFact;
		me.Person_id = arguments[0].Person_id;
		me.PersonProfile_id = arguments[0].PersonProfile_id;
		me.reportType = arguments[0].ReportType;
		me.Sex_id = arguments[0].Sex_id

		me.controllerName = sw.Promed.PersonOnkoProfile.getControllerName(me.reportType);

		me.palliatOtherFieldSet.hide();
		me.geriatricsOtherFieldSet.hide();
		me.biradsOtherFieldSet.hide();
		
		Ext6.getCmp(me.getId() + '_OnkoCtrl_FieldsetLpu').hide();

		if (me.reportType == 'palliat') {
			me.palliatOtherFieldSet.show();
		}
		if (me.reportType == 'geriatrics') {
			me.geriatricsOtherFieldSet.show();
		}
		if (me.reportType == 'birads') {
			me.biradsOtherFieldSet.show();
		}
		if (me.reportType != 'previzit') {
			Ext6.getCmp(me.getId() + '_OnkoCtrl_FieldsetLpu').show();
		}

		me.enableEdit(me.action != 'view');

		me.callParent(arguments);
	},
	printPersonProfile: function() {
		var me = this;
		
		if (me.Sex_id) {
			paramSex = me.Sex_id;
		} else {
			paramSex = '';
		}

		switch(me.reportType) {
			case 'onko':
			case 'previzit':
				printBirt({
					Report_FileName: 'onkoPersonProfile.rptdesign',
					Report_Params: '&paramPerson=' + me.Person_id + '&paramOnkoProfile=' + me.PersonProfile_id + '&paramSex=' + paramSex,
					Report_Format: 'pdf'
				});
				break;
			case 'palliat':
				printBirt({
					Report_FileName: 'PalliatPersonProfile.rptdesign',
					Report_Params: '&paramPerson=' + me.Person_id + '&paramPalliatQuestion=' + me.PersonProfile_id,
					Report_Format: 'pdf'
				});
				break;
			case 'geriatrics':
				printBirt({
					Report_FileName: 'GeriatricsAnketaprint.rptdesign',
					Report_Params: '&paramPerson=' + me.Person_id + '&paramGeriatricsQuestion=' + me.PersonProfile_id,
					Report_Format: 'pdf'
				});
				break;
			case 'birads':
				printBirt({
					Report_FileName: 'Print_BIRADSQuestion.rptdesign',
					Report_Params: '&paramLpu=' + getGlobalOptions().lpu_id + '&paramBIRADSQuestion=' + me.PersonProfile_id,
					Report_Format: 'pdf'
				});
				break;
		}
	},
	initComponent: function() {
		var me = this;

		me.mainFieldsPanel = Ext6.create('Ext6.Panel', {
			layout: 'hbox',
			border: false,
			items: [{
				xtype: 'hidden',
				name: 'PersonOnkoProfile_id'
			}, {
				xtype: 'hidden',
				name: 'Person_id'
			}, {
				xtype: 'hidden',
				name: 'Diag_id'
			}, {
				xtype: 'hidden',
				name: 'Diag_setDate'
			}, {
				xtype: 'hidden',
				name: 'Diag_Code'
			}, {
				xtype: 'hidden',
				name: 'Diag_Name'
			}, {
				layout: 'column',
				border: false,
				items: [{
					allowBlank: false,
					xtype: 'datefield',
					plugins: [new Ext6.ux.InputTextMask('99.99.9999', true)],
					name: 'PersonOnkoProfile_DtBeg',
					fieldLabel: 'Дата анкетирования',
					width: 255,
					maxValue: new Date(),
					labelWidth: 130,
					style: {
						marginBottom: '10px',
					},
					listeners: {
						change: function(field, newValue) {
							if (!me.loading) {
								me.loadQuestions();
							}
						}
					}
				}]
			}, {
				layout: 'column',
				border: false,
				flex: 1,
				style: 'margin-left: 40px;',
				items: [{
					xtype: 'fieldset',
					id: me.getId() + '_OnkoCtrl_FieldsetLpu',
					title: 'Анкетирование провел',
					width: '100%',
					style: {
						paddingTop: '0',
						marginTop: '-10px',
						paddingLeft: '10px'
					},
					defaults: {
						width: '100%'
					},
					items: [{
						disabled: true,
						xtype: 'swLpuCombo',
						name: 'Lpu_id',
						fieldLabel: 'МО'
					}, {
						disabled: true,
						xtype: 'swLpuBuildingCombo',
						name: 'LpuBuilding_id',
						fieldLabel: 'Подразделение'
					}, {
						disabled: true,
						xtype: 'SwLpuSectionGlobalCombo',
						name: 'LpuSection_id',
						fieldLabel: 'Отделение'
					}, {
						disabled: true,
						xtype: 'swMedStaffFactCombo',
						name: 'MedStaffFact_id',
						fieldLabel: 'Врач',
						minWidth: null
					}]
				}]
			}]
		});

		me.palliatOtherFieldSet = Ext6.create('Ext6.form.FieldSet', {
			id: me.getId()+'_PalliatQuestion_OtherFieldset',
			style: {
				paddingTop: '15px',
				paddingLeft: '10px'
			},
			defaults: {
				width: '100%',
				labelWidth: 150
			},
			items: [{
				disabled: true,
				hideTrigger: true,
				xtype: 'numberfield',
				name: 'PalliatQuestion_CountYes',
				fieldLabel: 'Количество баллов'
			}]
		});

		me.geriatricsOtherFieldSet = Ext6.create('Ext6.form.FieldSet', {
			id: me.getId()+'_GeriatricsQuestion_OtherFieldset',
			style: {
				paddingTop: '15px',
				paddingLeft: '10px'
			},
			defaults: {
				width: '100%',
				labelWidth: 150
			},
			items: [/*{
				xtype: 'textfield',
				name: 'GeriatricsQuestion_Other',
				fieldLabel: 'Иные признаки'
			}, */{
				disabled: true,
				hideTrigger: true,
				xtype: 'numberfield',
				name: 'GeriatricsQuestion_CountYes',
				fieldLabel: 'Количество баллов'
			}, {
				disabled: true,
				xtype: 'commonSprCombo',
				comboSubject: 'AgeNotHindrance',
				name: 'AgeNotHindrance_id',
				fieldLabel: 'Результат'
			}]
		});

		me.questionsFieldSet = Ext6.create('Ext6.form.FieldSet', {
			style: {
				paddingTop: '15px',
				paddingLeft: '10px'
			},
			defaults: {
				width: '100%',
				labelWidth: 150
			},
			items: []
		});

		me.biradsOtherFieldSet = Ext6.create('Ext6.form.FieldSet', {
			style: {
				paddingTop: '15px',
				paddingLeft: '10px'
			},
			defaults: {
				width: '100%',
				labelWidth: 150
			},
			items: [{
				comboSubject: 'CategoryBIRADS',
				disabled: true,
				fieldLabel: langs('Категория BI-RADS'),
				width: 300,
				name: 'CategoryBIRADS_id',
				xtype: 'commonSprCombo'
			}]
		});

		me.formPanel = Ext6.create('Ext6.form.Panel', {
			border: false,
			bodyPadding: '10 10 0 10',
			trackResetOnLoad: false,
			items: [
				me.mainFieldsPanel,
				me.palliatOtherFieldSet,
				me.biradsOtherFieldSet,
				me.geriatricsOtherFieldSet,
				me.questionsFieldSet
			]
		});

		Ext6.apply(me, {
			layout: 'vbox',
			defaults: {
				width: '100%'
			},
			items: [
				me.formPanel
			],
			buttons: [
				{
					id: me.getId()+'-print-btn',
					text: 'Печать',
					userCls: 'action_print',
					margin: 0,
					handler: function () {
						me.printPersonProfile();
					}
				},
				'->',
				{
					text: 'Отмена',
					userCls: 'buttonCancel',
					margin: 0,
					handler: function() {
						me.hide();
					}
				}, {
					id: me.getId()+'-save-btn',
					cls: 'buttonAccept',
					margin: '0 19 0 0',
					text: 'Сохранить',
					handler: function() {
						me.save();
					}
				}
			]
		});

		me.callParent(arguments);
	}
});