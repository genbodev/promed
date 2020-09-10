Ext6.define('common.EMK.EvnPLDispScreenOnko.PersonProfileEditPanel', {
	extend: 'base.BaseFormPanel',
	alias: 'widget.swPersonProfileEditPanel',
	cls: 'arm-window-new emk-forms-window save-template-window',
	autoShow: false,
	constrain: true,
	autoScroll: true,
	header: false,
	save: function() {
		var me = this,
			baseForm = me.formPanel.getForm();

		var params = me.getParams([
			me.mainFieldsPanel,
			me.palliatOtherFieldSet,
			me.geriatricsOtherFieldSet
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
				var result = Ext6.util.JSON.decode(response.responseText);
				if(result.success){
					if(result.rows && result.rows[0] && result.rows[0].PersonOnkoProfile_id){
						me.PersonProfile_id = result.rows[0].PersonOnkoProfile_id;
						baseForm.findField('PersonOnkoProfile_id').setValue(me.PersonProfile_id);
						sw4.showInfoMsg({
							panel: me.ownerPanel,
							type: 'success',
							text: 'Анкета сохранена.'
						});
					}
					me.enableEdit(false);
				} else {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						icon: Ext.Msg.WARNING,
						msg: 'Не выбран обязательный ответ. Вернитесь в анкету.',
						title: ERR_INVFIELDS_TIT
					});
				}
				me.unmask();
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
			return fields.concat(panel.query('field'));
		}, []);

		var params = {};
		fields.forEach(function(field) {
			switch(field.xtype) {
				case 'datefield':
					params[field.name] = Ext6.util.Format.date(field.value, 'd.m.Y');
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

	createSeparator: function(item, index) {
		var me = this;
		var panel = me.questionsFieldSet;

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
		var panel = me.questionsFieldSet;

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
			labelWidth: 710
		};

		var field = panel.add(cfg);
		field.setValue(val == 2);
	},

	createTextField: function(item, index) {
		var me = this;
		var panel = me.questionsFieldSet;

		var id = Number(item.OnkoQuestions_id);
		var name = item.OnkoQuestions_Name;
		var num = item.Questions_Num ? item.Questions_Num : (index+1);
		var val = item.val;

		var cfg = {
			xtype: 'textfield',
			oId: id,
			cls: 'text-custome-quest',
			name: 'text'+id,
			allowBlank: (item.IsRequired != 2),
			fieldLabel: num + ') '+name,
			labelSeparator: '',
			labelWidth: 550
		};

		var field = panel.add(cfg);
		field.setValue(val);
	},

	createSupCombobox: function(item, index) {
		
		this.createCombobox(item, index);
		
		var me = this;
		var panel = me.questionsFieldSet;

		var id = Number(item.OnkoQuestions_id);
		var name = item.OnkoQuestions_Name;
		var num = item.Questions_Num ? item.Questions_Num : (index+1);
		var val = item.FreeForm;

		var cfg = {
			xtype: 'textfield',
			oIdTx: id,
			cls: 'combo-custome-quest',
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
		var panel = me.questionsFieldSet;

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
					comboSubject: answerClass,
					cls: 'combo-custome-quest'
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
				Ext6.suspendLayouts();
				list.forEach(function(item, index) {
					var answerType = item.AnswerType_Code;
					var answerClass = item.AnswerClass_SysNick;

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
				});

				if (me.action == 'view') {
					me.questionsFieldSet.query('field').forEach(function(field) {
						field.disable();
					});
				}
				Ext6.resumeLayouts(true);
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
				if (me.reportType == 'onko' && me.action == 'add') {
					me.queryById(me.id+'-save-btn').setVisible(true);
					if (!Ext6.isEmpty(baseForm.findField('PersonOnkoProfile_id').getValue())) {
						me.action = 'view';
						me.enableEdit(false);
						/*Ext6.Msg.alert(
							langs('Внимание'),
							langs(
								'У пациента уже есть актуальная Анкета по онкоконтролю. ' +
								'<br />Следующее заполнение Анкеты на пациента - в новом календарном году. ' +
								'<br />Актуальная Анкета будет открыта в режиме "Просмотр"'
							)
						);*/
					} else if (!Ext6.isEmpty(baseForm.findField('Diag_id').getValue())) {
						me.Diag_setDate = baseForm.findField('Diag_setDate').getValue();
						me.Diag_Code = baseForm.findField('Diag_Code').getValue();
						me.Diag_Name = baseForm.findField('Diag_Name').getValue();
						me.action = 'not';
						me.queryById(me.id+'-save-btn').setVisible(false);
						/*var msg = 'Пациенту ' + Diag_setDate + ' был поставлен диагноз <br />' + Diag_Code + ' - "' + Diag_Name + '".<br />Заполнение Анкеты не требуется.';
						Ext6.Msg.alert(langs('Внимание'), msg);*/
						if(me.ownerPanel && me.ownerPanel.AnketaPanel)
							me.ownerPanel.AnketaPanel.hide();
						return;
					}
				}
				
				if (me.ownerPanel && me.ownerPanel.queryById('anketastatus') && responseObj.data[0].PersonOnkoProfile_DtBeg) {
					me.ownerPanel.queryById('anketastatus').setText(responseObj.data[0].PersonOnkoProfile_DtBeg);
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

	show: function(data) {
		var me = this;
		var baseForm = me.formPanel.getForm();

		me.loading = true;
		me.isValid = true;

		me.questionsFieldSet.removeAll();
		baseForm.reset();

		me.callback = data.callback || Ext.emptyFn;
		me.action = data.action;
		me.userMedStaffFact = data.userMedStaffFact;
		me.Person_id = data.Person_id;
		me.PersonProfile_id = data.PersonProfile_id;
		me.reportType = data.ReportType;
		me.Diag_setDate = null;
		me.Diag_Code = null;
		me.Diag_Name = null;
		me.controllerName = sw.Promed.PersonOnkoProfile.getControllerName(me.reportType);

		me.palliatOtherFieldSet.hide();
		me.geriatricsOtherFieldSet.hide();

		if (me.reportType == 'palliat') {
			me.palliatOtherFieldSet.show();
		}
		if (me.reportType == 'geriatrics') {
			me.geriatricsOtherFieldSet.show();
		}

		me.inPLDispScreen = (data && data.inPLDispScreen);
		me.mainFieldsPanel.setVisible(!(data && data.inPLDispScreen));

		me.enableEdit(me.action != 'view');

		me.callParent(arguments);
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
					labelWidth: 130,
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
				name: 'GeriatricsQuestion_CountYes',
				fieldLabel: 'Результат'
			}]
		});

		me.questionsFieldSet = Ext6.create('Ext6.form.FieldSet', {
			style: {
				paddingTop: '15px',
				paddingLeft: '10px',
				borderWidth: '0px !important'
			},
			defaults: {
				width: '100%',
				labelWidth: 150
			},
			items: []
		});

		me.formPanel = Ext6.create('Ext6.form.Panel', {
			border: false,
			bodyPadding: '10 10 0 10',
			trackResetOnLoad: false,
			items: [
				me.mainFieldsPanel,
				me.palliatOtherFieldSet,
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
				me.formPanel,
				{
					xtype: 'button',
					id: me.getId()+'-save-btn',
					width: 120,
					//cls: 'buttonAccept',
					userCls: 'button-secondary',
					margin: '0 19 20 20',
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