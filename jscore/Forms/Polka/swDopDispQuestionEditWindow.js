/**
* swDopDispQuestionEditWindow - окно редактирования анкетирования по ДД
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Polka
* @access			public
* @copyright		Copyright (c) 2013 Swan Ltd.
* @author			Dmitry Vlasenko
* @version			20.05.2013
* @comment			Префикс для id компонентов DDQEW (swDopDispQuestionEditWindow)
*
*
* Использует: окно редактирования талона по доп. диспансеризации (swEvnPLDispDop13EditWindow)
*/

sw.Promed.swDopDispQuestionEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	buttons: [{
		handler: function() {
			this.ownerCt.doSave({calculation: true});
		},
		iconCls: 'save16',
		tabIndex: TABINDEX_DDQEW+89,
		disabled: true,
		text: 'Расчёт'
	}, {
		handler: function() {
			this.ownerCt.doSave({calculation:false});
		},
		iconCls: 'save16',
		tabIndex: TABINDEX_DDQEW+90,
		text: BTN_FRMSAVE
	}, {
		handler: function() {
			var win = this.ownerCt;
			if ( getRegionNick() == 'kareliya' ) {
				var paramEvnPLDispID = win.EvnPLDisp_id;
				if(paramEvnPLDispID){
					printBirt({
						'Report_FileName': 'questionnaire.rptdesign',
						'Report_Params': '&paramEvnPLDispID=' + paramEvnPLDispID,
						'Report_Format': 'pdf'
					});
				}
			} else if (win.object == 'EvnPLDispDop13') {
				var paramEvnPLDispID = win.EvnPLDisp_id;
				if (paramEvnPLDispID) {
					win.getLoadMask('Получение шаблона для печати').show();
					Ext.Ajax.request({
						url: '/?c=DopDispQuestion&m=getTemplateForPrint',
						params: {
							EvnPLDisp_id: paramEvnPLDispID
						},
						callback: function(options, success, response) {
							win.getLoadMask().hide();
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.template) {
									printBirt({
										'Report_FileName': response_obj.template,
										'Report_Params': '&EvnPLDispDop13_id=' + paramEvnPLDispID,
										'Report_Format': 'pdf'
									});
								}
							}
						}
					});
				}
			} else {
				// TO-DO печатная форма не ясна
				win.dopDispQuestionGrid.printRecords();
			}
		},
		iconCls: 'print16',
		tabIndex: TABINDEX_DDQEW+89,
		disabled: false,
		text: lang['pechat_blanka']
	}, '-', 
	HelpButton(this, TABINDEX_DDQEW+91),
	{
		handler: function() {
			this.ownerCt.hide();
		},
		iconCls: 'cancel16',
		id: 'DDQEW_CancelButton',
		tabIndex: TABINDEX_DDQEW+92,
		text: BTN_FRMCANCEL
	}],
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
	formStatus: 'edit',
	doSave: function(options) { // options.calculation - необходимость произвести расчёт
		// сохраняем все ответы в БД, берём весь грид и кидаем на сервер
		if ( typeof options != 'object' ) {
			options = new Object();
		}
		
		var win = this,
			base_form = win.dopDispQuestionPanel.getForm();
		
		if ( win.formStatus == 'save' || win.action == 'view' ) {
			return false;
		}
		win.formStatus = 'save';
		
		var form = this.dopDispQuestionPanel;
		if ( !form.getForm().isValid() )
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		// #181668 пока убрали контроль кроме Вологды
		// NGS: AN ADDITIONAL CHECK IS NOT NEEDED ANYMORE FOR VOLOGDA - #194032
		if(!(getRegionNick().inlist([/*'vologda'*/]) && win.object && win.object.inlist(['EvnPLDispDop13','EvnPLDispProf']))){
			options.ignoreCheckDiag = true;
		}
		//Проверка диагноза на наличие в EvnDiagDopDisp
		var Diag_Code = base_form.findField('Diag_id').getFieldValue('Diag_Code');
		var GroupDiag_Code = Diag_Code.slice(0,3);
		if (
			options.ignoreCheckDiag != true
			&& (
				(getRegionNick()=='krym' && Diag_Code !='Z00.0')
				|| ((getRegionNick()=='kareliya' || getRegionNick()=='penza') && Diag_Code !='Z01.8')
				|| (getRegionNick()!='krym' && getRegionNick()!='kareliya' && getRegionNick()!='penza' && Diag_Code !='Z10.8')
			)
		) {
			win.getLoadMask(langs("Подождите, идет проверка диагноза...")).show();
			
			Ext.Ajax.request({
				url: '/?c=EvnPLDispDop13&m=CheckDiag',
				params: {
					EvnPLDispDop13_id: win.EvnPLDisp_id,
					Diag_id: base_form.findField('Diag_id').getValue(),
					EvnUslugaDispDop_id: win.EvnUslugaDispDop_id
				},
				failure: function(result_form, action) {
					win.getLoadMask().hide();
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function ( buttonId ) {
							if ( buttonId == 'yes' ) {
								options.ignoreCheckDiag = true;
								win.doSave(options);
							}
							else {
								win.formStatus = 'edit';
							}
						},
						msg: langs('Ошибка при проверке на дублирование диагноза. Продолжить сохранение?'),
						title: langs('Подтверждение сохранения')
					});
				},
				success: function(response, action) {
					win.getLoadMask().hide();

					if (response.responseText != '') {
						var data = Ext.util.JSON.decode(response.responseText);
						if (data) {
							var msg = '';
							
							if(data == -1) {//совпадение с диагнозами в случаях лечения и картах дисп.учета
								sw.swMsg.alert(langs('Ошибка'), langs('У пациента уже установлен диагноз')+' <b>'+Diag_Code+'</b><br>'
									+langs('Проверьте правильность введенных данных.'),
									function() {
										win.formStatus = 'edit';
										base_form.findField('Diag_id').focus(true);
									}.createDelegate(this)
								);
							} else if(data == base_form.findField('Diag_id').getValue()) {
								sw.swMsg.alert(langs('Ошибка'), langs('У пациента уже указан диагноз')+' <b>'+Diag_Code+'</b><br>'
									+langs('Проверьте правильность введенных данных.'),
									function() {
										win.formStatus = 'edit';
										base_form.findField('Diag_id').focus(true);
									}.createDelegate(this)
								);
							} else {
								sw.swMsg.show({
									buttons: {yes: langs('Продолжить'), no: langs('Отмена')},
									fn: function ( buttonId ) {
										if ( buttonId == 'yes' ) {
											options.ignoreCheckDiag = true;
											win.doSave(options);
										} else {
											win.formStatus = 'edit';
											base_form.findField('Diag_id').focus(true);
										}
									},
									msg: langs('У пациента уже указан диагноз группы')+' <b>'+GroupDiag_Code+'</b>',
									title: langs('Подтверждение сохранения'),
									width: 300
								});
							}
						} else {
							options.ignoreCheckDiag = true;
							win.doSave(options);
						}
					}
				}
			});

			win.formStatus = 'edit';
			return false;
		}
		
		win.getLoadMask(langs('Сохранение анкетирования')).show();
		var grid = win.dopDispQuestionGrid.getGrid();
		var params = {};
		
		if (options.calculation) {
			params.NeedCalculation = 1;
		} else {
			params.NeedCalculation = 0;
		}

		params.EvnPLDisp_id = win.EvnPLDisp_id;
		var qdata = getStoreRecords( grid.getStore(), {
			exceptionFields: [
				'DopDispQuestion_Response',
				'QuestionType_Name',
				'QuestionType_RowNum'
			]
		});

		params.PayType_id = win.PayType_id;
		params.DopDispQuestionData = Ext.util.JSON.encode(qdata);
		params.MedPersonal_id = base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id');
		
		if (base_form.findField('UslugaComplex_id').disabled) {
			params.UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
		}

		base_form.submit({
			url: '/?c='+win.object+'&m=saveDopDispQuestionGrid',
			failure: function(result_form, action) {
				win.formStatus = 'edit';
				win.getLoadMask().hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 1]');
					}
				}
			},
			params: params,
			success: function(result_form, action) {
				win.formStatus = 'edit';
				win.getLoadMask().hide();
				if ( action.result ) {
					if ( action.result.success ) {
						if (options.calculation) {
							win.callback(qdata);
						} else {
							win.callback();
						}
						// скрываем окно
						win.hide();
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert('Ошибка', action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 3]');
						}
					}
				}
				else {
					sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 2]');
				}
			}
		});
    },
	draggable: true,
    height: 490,
	id: 'DopDispQuestionEditWindow',
	onFieldKeyPress: function(field, e) {
		var win = this;
		
		if ( e.getKey() == e.TAB ) {
			field.fireEvent('blur', field);
			win.editNextRecord();
		}
	},
	onFieldKeyDown: function(field, e) {
		var win = this;
		
		if ( e.altKey && e.getKey() == Ext.EventObject.J ) {
			field.fireEvent('blur', field);
			win.hide();
		}
		else if ( e.altKey && e.getKey() == Ext.EventObject.C ) {
			if ( 'view' != win.action ) {
				field.fireEvent('blur', field);
				win.doSave();
			}
		}
	},
	getTextFieldEditor: function() {
		var win = this;
		return new Ext.form.TextField({
			allowBlank: false,
			enableKeyEvents: true,
			fireAfterEditOnEmpty: true,
			listeners: {
				'keypress': function(field, e) {
					win.onFieldKeyPress(field, e);
				},
				'keydown': function(field, e) {
					win.onFieldKeyDown(field, e);
				}
			}
		});
	},
	getYesNoEditor: function() {
		var win = this;
		return new sw.Promed.SwCommonSprCombo({
			allowBlank: false,
			comboSubject: 'YesNo',
			codeField: 'YesNo_Code',
			editable: true,
			enableKeyEvents: true,
			fireAfterEditOnEmpty: true,
			listeners: {
				'keypress': function(field, e) {
					win.onFieldKeyPress(field, e);
				},
				'keydown': function(field, e) {
					win.onFieldKeyDown(field, e);
				},
				'render': function() {
					// как появился нужно и прогрузиться
					this.getStore().load();
				}
			}
		});
	},
	getAnswerYesNoTypeEditor: function() {
		var win = this;
		return new sw.Promed.SwCommonSprCombo({
			allowBlank: false,
			comboSubject: 'AnswerYesNoType',
			codeField: 'AnswerYesNoType_Code',
			editable: true,
			enableKeyEvents: true,
			fireAfterEditOnEmpty: true,
			listeners: {
				'keypress': function(field, e) {
					win.onFieldKeyPress(field, e);
				},
				'keydown': function(field, e) {
					win.onFieldKeyDown(field, e);
				},
				'render': function() {
					// как появился нужно и прогрузиться
					this.getStore().load();
				}
			}
		});
	},
	getAnswerOnkoTypeEditor: function() {
		var win = this;
		return new sw.Promed.SwCommonSprCombo({
			allowBlank: false,
			comboSubject: 'AnswerOnkoType',
			codeField: 'AnswerOnkoType_Code',
			editable: true,
			enableKeyEvents: true,
			fireAfterEditOnEmpty: true,
			listeners: {
				'keypress': function(field, e) {
					win.onFieldKeyPress(field, e);
				},
				'keydown': function(field, e) {
					win.onFieldKeyDown(field, e);
				},
				'render': function() {
					// как появился нужно и прогрузиться
					this.getStore().load();
				}
			}
		});
	},
	getAnswerSmokeTypeEditor: function() {
		var win = this;
		return new sw.Promed.SwCommonSprCombo({
			allowBlank: false,
			comboSubject: 'AnswerSmokeType',
			codeField: 'AnswerSmokeType_Code',
			editable: true,
			enableKeyEvents: true,
			fireAfterEditOnEmpty: true,
			listeners: {
				'keypress': function(field, e) {
					win.onFieldKeyPress(field, e);
				},
				'keydown': function(field, e) {
					win.onFieldKeyDown(field, e);
				},
				'render': function() {
					// как появился нужно и прогрузиться
					this.getStore().load();
				}
			}
		});
	},
	getAnswerWalkTypeEditor: function() {
		var win = this;
		return new sw.Promed.SwCommonSprCombo({
			allowBlank: false,
			comboSubject: 'AnswerWalkType',
			codeField: 'AnswerWalkType_Code',
			editable: true,
			enableKeyEvents: true,
			fireAfterEditOnEmpty: true,
			listeners: {
				'keypress': function(field, e) {
					win.onFieldKeyPress(field, e);
				},
				'keydown': function(field, e) {
					win.onFieldKeyDown(field, e);
				},
				'render': function() {
					// как появился нужно и прогрузиться
					this.getStore().load();
				}
			}
		});
	},
	getAnswerPissTypeEditor: function() {
		var win = this;
		return new sw.Promed.SwCommonSprCombo({
			allowBlank: false,
			comboSubject: 'AnswerPissType',
			codeField: 'AnswerPissType_Code',
			editable: true,
			enableKeyEvents: true,
			fireAfterEditOnEmpty: true,
			listeners: {
				'keypress': function(field, e) {
					win.onFieldKeyPress(field, e);
				},
				'keydown': function(field, e) {
					win.onFieldKeyDown(field, e);
				},
				'render': function() {
					// как появился нужно и прогрузиться
					this.getStore().load();
				}
			}
		});
	},
	getAlcoholIngestTypeEditor: function() {
		var win = this;
		return new sw.Promed.SwCommonSprCombo({
			allowBlank: false,
			comboSubject: 'AlcoholIngestType',
			codeField: 'AlcoholIngestType_Code',
			editable: true,
			enableKeyEvents: true,
			fireAfterEditOnEmpty: true,
			listeners: {
				'keypress': function(field, e) {
					win.onFieldKeyPress(field, e);
				},
				'keydown': function(field, e) {
					win.onFieldKeyDown(field, e);
				},
				'render': function() {
					// как появился нужно и прогрузиться
					this.getStore().load();
				}
			}
		});
	},
	blockStartingEditing: false,
	startEditData: function() {
		if (this.blockStartingEditing) {
			return false;
		}
		var win = this;
		var grid = this.dopDispQuestionGrid.getGrid();

		if (win.action == 'view') {
			return false;
		}
		
		// если ещё редактируется
		var editor = grid.getColumnModel().getCellEditor(4);
		if (editor && !editor.hidden) {
			return false;
		}

		this.blockStartingEditing = true;
		
		var cell = grid.getSelectionModel().getSelectedCell();
		var record = grid.getSelectionModel().getSelected();
		if ( !record || !record.get('QuestionType_id') ) {
			return false;
		}
		
		grid.getSelectionModel().select(cell[0], 4);
		grid.getView().focusCell(cell[0], 4);
		
		if (record.get('AnswerType_id') == 1) {
			// да/нет
			grid.getColumnModel().setEditor(4, new Ext.grid.GridEditor(win.getYesNoEditor()));
		} else if (record.get('AnswerType_id') == 2) {
			// текстовый
			grid.getColumnModel().setEditor(4, new Ext.grid.GridEditor(win.getTextFieldEditor()));
		} else {
			// тип - справочник или смешанный
			switch(record.get('AnswerClass_id')) {
				case 6:
					grid.getColumnModel().setEditor(4, new Ext.grid.GridEditor(win.getYesNoEditor()));
				break;
				case 2:
					grid.getColumnModel().setEditor(4, new Ext.grid.GridEditor(win.getAnswerOnkoTypeEditor()));
				break;				
				case 3:
					grid.getColumnModel().setEditor(4, new Ext.grid.GridEditor(win.getAnswerSmokeTypeEditor()));
				break;
				case 4:
					grid.getColumnModel().setEditor(4, new Ext.grid.GridEditor(win.getAnswerWalkTypeEditor()));
				break;
				case 5:
					grid.getColumnModel().setEditor(4, new Ext.grid.GridEditor(win.getAnswerPissTypeEditor()));
				break;
				case 7:
					grid.getColumnModel().setEditor(4, new Ext.grid.GridEditor(win.getAlcoholIngestTypeEditor()));
				break;
				case 1:
				default:
					grid.getColumnModel().setEditor(4, new Ext.grid.GridEditor(win.getAnswerYesNoTypeEditor()));
				break;
			}
		}
		grid.getColumnModel().setEditable(4, true);
		grid.startEditing(cell[0], 4);
		this.blockStartingEditing = false;
	},
	editNextRecord: function(first) {
		var win = this;
		var grid = win.dopDispQuestionGrid.getGrid();
		if (!grid) {
			return false;
		}
		
		var recId = 0;
		var cell = grid.getSelectionModel().getSelectedCell();
		if (!first && cell) {
			recId = cell[0] + 1
		}
		
		if (recId < grid.getStore().getCount()) {
			grid.getSelectionModel().select(recId, 4);
			grid.getView().focusCell(recId, 4);
		} else {
			win.buttons[0].focus();
		}
	},
	loadUslugaComplexCombo: function() {
		var win = this;
		var base_form = win.dopDispQuestionPanel.getForm();
		var usluga_combo = base_form.findField('UslugaComplex_id');
		
		if (usluga_combo.hidden) {
			return;
		}
		
		var onDate = Ext.util.Format.date(base_form.findField('DopDispQuestion_setDate').getValue(), 'd.m.Y');
		
		usluga_combo.getStore().baseParams.SurveyType_id = 2;
		usluga_combo.getStore().baseParams.UslugaComplex_Date = onDate;
		
		usluga_combo.enable();
		
		usluga_combo.getStore().load({
			callback: function() {
				var id = usluga_combo.getValue();
				var count = usluga_combo.getStore().getCount();
				
				if (count == 0) {
					id = null;
				} else {
					if (!id) {
						var record = usluga_combo.getStore().getAt(0);
						if (record && record.get('UslugaComplex_id')) {
							id = record.get('UslugaComplex_id');
						}
					}
					if (id && count == 1) {
						usluga_combo.disable();
					}
				}

				usluga_combo.setValue(id);
			}
		});
	},
    initComponent: function() {
		var win = this;

		this.UslugaInfoPanel = new sw.Promed.Panel({
			layout: 'form',
			autoHeight: true,
			items: [{
				hidden: true,
				xtype: 'swuslugacomplexnewcombo',
				hiddenName: 'UslugaComplex_id',
				fieldLabel: langs('Услуга'),
				nonDispOnly: false,
				width: 450
			}, {
				hiddenName: 'LpuSection_uid',
				id: 'DDQEW_LpuSectionCombo',
				lastQuery: '',
				listWidth: 650,
				linkedElements: [
					'DDQEW_MedPersonalCombo'
				],
				width: 450,
				xtype: 'swlpusectionglobalcombo'
			}, {
				hiddenName: 'MedStaffFact_id',
				id: 'DDQEW_MedPersonalCombo',
				lastQuery: '',
				listWidth: 650,
				parentElementId: 'DDQEW_LpuSectionCombo',
				width: 450,
				xtype: 'swmedstafffactglobalcombo'
			}, {
				fieldLabel: 'Диагноз',
				hiddenName: 'Diag_id',
				baseFilterFn: function(rec) {
					var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
					if (Ext.isEmpty(Diag_Code)) return false;
					return (
						(Diag_Code.substr(0,1) < 'V' || Diag_Code.substr(0,1) > 'Y')
					);
				},
				onChange: function() {
					var diag_code = this.getFieldValue('Diag_Code');

					var base_form = win.dopDispQuestionPanel.getForm();
					if ( !Ext.isEmpty(diag_code) && diag_code.substr(0, 1).toUpperCase() == 'Z') {
						base_form.findField('DopDispDiagType_id').clearValue();
						base_form.findField('DopDispDiagType_id').disable();
						base_form.findField('DopDispDiagType_id').setAllowBlank(true);
					} else {
						base_form.findField('DopDispDiagType_id').enable();
						base_form.findField('DopDispDiagType_id').setAllowBlank(false);
					}

					if (!base_form.findField('DeseaseStage').hidden) {
						var enabled = (!Ext.isEmpty(diag_code) && ((diag_code.substr(0, 1).toUpperCase() == 'C')||(diag_code.substr(0, 1).toUpperCase() == 'D' && diag_code.substr(1, 2)<=48)));
						base_form.findField('DeseaseStage').setAllowBlank(!enabled);
						base_form.findField('DeseaseStage').setDisabled(!enabled);
						if (!enabled) {
							base_form.findField('DeseaseStage').clearValue();
						}
					}
				},
				listWidth: 600,
				width: 450,
				xtype: 'swdiagcombo'
			}, {				
				comboSubject: 'DopDispDiagType',
				fieldLabel: 'Характер заболевания',
				hiddenName: 'DopDispDiagType_id',
				value: 1,
				lastQuery: '',
				width: 450,
				xtype: 'swcommonsprcombo'
			},
			{
				allowBlank: true,
				codeField: 'DeseaseStage',
				displayField: 'DeseaseStage',
				editable: false,
				fieldLabel: 'Стадия',
				hiddenName: 'DeseaseStage',
				hideEmptyRow: true,
				ignoreIsEmpty: true,
				store: new Ext.data.SimpleStore({
					autoLoad: true,
					data: [
						[ 1],
						[ 2],
						[ 3],
						[ 4]
					],
					fields: [
						{ name: 'DeseaseStage', type: 'int'}
					],
					key: 'DeseaseStage',
					sortInfo: { field: 'DeseaseStage' }
				}),
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<font color="blue">{DeseaseStage}</font>',
					'</div></tpl>'
				),
				valueField: 'DeseaseStage',
				width: 450,
				xtype: 'swbaselocalcombo'
			}]
		});

		this.dopDispQuestionPanel = new Ext.form.FormPanel({
			border: false,
			labelWidth: 150,
			reader: new Ext.data.JsonReader({
				success: function() { }
			}, [
				{ name: 'Diag_id' },
				{ name: 'UslugaComplex_id' },
				{ name: 'LpuSection_uid' },
				{ name: 'MedPersonal_id' },
				{ name: 'MedStaffFact_id' },
				{ name: 'DeseaseStage' },
				{ name: 'DopDispDiagType_id' }
			]),
			labelAlign: 'right',
			layout: 'form',
			autoHeight: true,
			region: 'north',
			frame: true,
			bodyStyle: 'padding: 5px;',
			items: [{
				allowBlank: false,
				fieldLabel: lang['data_anketirovaniya'],
				format: 'd.m.Y',
				listeners: {
					'keypress': function(field, e) {
						var base_form = win.dopDispQuestionPanel.getForm();
						if ( base_form.findField('LpuSection_uid').hidden && e.getKey() == e.TAB ) {
							win.editNextRecord(true);
						}
					},
					change: function(combo, newValue) {
						var base_form = win.dopDispQuestionPanel.getForm();

						var params = {
							allowLowLevel: 'yes'
							,allowDuplacateMSF: true
						};

						if (!Ext.isEmpty(base_form.findField('DopDispQuestion_setDate').getValue())) {
							params.onDate = Ext.util.Format.date(base_form.findField('DopDispQuestion_setDate').getValue(), 'd.m.Y');
						}

						setLpuSectionGlobalStoreFilter(params);
						setMedStaffFactGlobalStoreFilter(params);

						base_form.findField('LpuSection_uid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
						base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
						
						if (!base_form.findField('UslugaComplex_id').hidden) {
							win.loadUslugaComplexCombo();							
						}
					}
				},
				name: 'DopDispQuestion_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				width: 100,
				xtype: 'swdatefield'
			}, win.UslugaInfoPanel]
		});
		
		this.dopDispQuestionGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			id: 'DDQEW_dopDispQuestionGrid',
			dataUrl: '/?c=EvnPLDispDop13&m=loadDopDispQuestionGrid',
			region: 'center',
			height: 200,
			title: '',
			toolbar: false,
			saveAtOnce: false, 
			saveAllParams: false, 
			selectionModel: 'cell',
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', disabled: true, hidden: true },
				{ name: 'action_view', disabled: true, hidden: true },
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh' },
				{ name: 'action_print', disabled: true, hidden: true },
				{ name: 'action_save', disabled: true, hidden: true }
			],
			stringfields: [
				{ name: 'DopDispQuestion_id', type: 'int', header: 'ID', key: true },
				{ name: 'QuestionType_id', type: 'int', editor: new Ext.form.TextField(), hidden: true },
				{ name: 'QuestionType_RowNum', type: 'string', header: lang['№'], width: 40 },
				{ name: 'QuestionType_Name', renderer: function(value, metaData) {
					metaData.attr = 'style="white-space: normal;"';
					return value;
				}, header: lang['vopros'], id: 'autoexpand' },
				{ name: 'DopDispQuestion_Response', type: 'string', header: lang['otvet'], width: 100 },
				{ name: 'DopDispQuestion_IsTrue', type: 'int', hidden: true },
				{ name: 'DopDispQuestion_Answer', type: 'string', hidden: true },
				{ name: 'DopDispQuestion_ValuesStr', type: 'int', hidden: true },
				{ name: 'AnswerType_id', type: 'int', hidden: true },
				{ name: 'AnswerClass_id', type: 'int', hidden: true }
			],
			onLoadData: function() {
				this.checkAllFieldsNotEmpty();
			},
			onCellSelect: function(sm,rowIdx,colIdx) {
				win.startEditData();
			},
			checkAllFieldsNotEmpty: function() {
				var empty = false;
				
				win.dopDispQuestionGrid.getGrid().getStore().each(function(rec){
					if (Ext.isEmpty(rec.get('DopDispQuestion_Response'))) {
						empty = true;
					}
				});
				
				if (empty) {
					win.buttons[0].disable();
				} else {
					win.buttons[0].enable();
				}
			},
			onSelectionChange: function() {
				win.dopDispQuestionGrid.getGrid().getColumnModel().setEditable(4, false);
			}.createDelegate(this),
			onAfterEdit: function(o) {
				o.grid.stopEditing(true);
				o.grid.getColumnModel().setEditable(4, false);
				if (o && o.field) {
					if (o.field == 'DopDispQuestion_Response') {
						if (o.record.get('AnswerType_id') == 1) {
							o.record.set('DopDispQuestion_IsTrue', o.value);
							o.record.set('DopDispQuestion_Response', o.rawvalue);
						} else if (o.record.get('AnswerType_id') == 2) {
							o.record.set('DopDispQuestion_Answer', o.value);
							o.record.set('DopDispQuestion_Response', o.value);
						} else {
							switch(o.record.get('AnswerClass_id')) {
								case 6:
									o.record.set('DopDispQuestion_ValuesStr', null);
									o.record.set('DopDispQuestion_IsTrue', o.value);
									o.record.set('DopDispQuestion_Response', o.rawvalue);
									
									if (o.value == 2) {
										// показываем окно выбора диагноза
										win.setVisible(false); // чтобы не перекрывало окно поиска диагноза, не нашёл другого способа
										getWnd('swDiagSearchWindow').show({
											withGroups: false,
											formMode: 'DopDispQuestion',
											onSelect: function(diagData) {
												if (!Ext.isEmpty(diagData.Diag_id)) {
													o.record.set('DopDispQuestion_ValuesStr', diagData.Diag_id);
													o.record.set('DopDispQuestion_Response', o.rawvalue + ', ' + diagData.Diag_Name);
												}
												getWnd('swDiagSearchWindow').hide();
											},
											onHide: function() {
												win.setVisible(true);
											}
										});
									}
								break;
								default:
									o.record.set('DopDispQuestion_ValuesStr', o.value);
									o.record.set('DopDispQuestion_Response', o.rawvalue);
								break;
							}
						}
					}
				}
				
				this.checkAllFieldsNotEmpty();
			}
		});
		
        Ext.apply(this, {
            items: [ win.dopDispQuestionPanel, win.dopDispQuestionGrid ]
        });
    	sw.Promed.swDopDispQuestionEditWindow.superclass.initComponent.apply(this, arguments);
    },
	keys: [{
		alt: true,
		fn: function(inp, e) {
			e.stopEvent();

			if ( e.browserEvent.stopPropagation ) {
				e.browserEvent.stopPropagation();
			}
			else {
				e.browserEvent.cancelBubble = true;
			}

			if ( e.browserEvent.preventDefault ) {
				e.browserEvent.preventDefault();
			}
			else {
				e.browserEvent.returnValue = false;
			}

			e.returnValue = false;

			if ( Ext.isIE ) {
				e.browserEvent.keyCode = 0;
				e.browserEvent.which = 0;
			}

			var current_window = Ext.getCmp('DopDispQuestionEditWindow');

			if ( e.getKey() == Ext.EventObject.J ) {
				current_window.hide();
			}
			else if ( e.getKey() == Ext.EventObject.C ) {
				if ( 'view' != current_window.action ) {
					current_window.doSave();
				}
			}
		},
		key: [ Ext.EventObject.C, Ext.EventObject.J ],
		scope: this,
		stopEvent: false
	}],
    layout: 'border',
    listeners: {
    	'hide': function() {
    		this.onHide();
    	}
    },
    maximizable: true,
    minHeight: 370,
    minWidth: 750,
    modal: true,
    onHide: Ext.emptyFn,
	plain: true,
    resizable: true,
	title: lang['anketirovanie_redaktirovanie'],
    show: function() {
		sw.Promed.swDopDispQuestionEditWindow.superclass.show.apply(this, arguments);

		this.formStatus = 'edit';
		var win = this;
		var base_form = this.dopDispQuestionPanel.getForm();

		if (!arguments || arguments.length == 0) {
			return false;
		}
		
        if (!arguments[0] && !arguments[0].EvnPLDisp_id)
        {
        	Ext.Msg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { win.hide(); } );
        	return false;
        }

		win.EvnPLDisp_id = arguments[0].EvnPLDisp_id;

		win.object = 'EvnPLDispDop13';

		win.EvnUslugaDispDop_id = null;
		win.DispClass_id = null;
		
        if (arguments[0].EvnUslugaDispDop_id)
        {
        	win.EvnUslugaDispDop_id = arguments[0].EvnUslugaDispDop_id;
        }
		
        if (arguments[0].DispClass_id)
        {
        	win.DispClass_id = arguments[0].DispClass_id;
        }

	    if (arguments[0].PayType_id)
	    {
		    win.PayType_id = arguments[0].PayType_id;
	    }

		if (arguments[0].object)
        {
        	win.object = arguments[0].object;
        }

		win.action = 'edit';
        if (arguments[0].action)
        {
        	win.action = arguments[0].action;
        }
		
        if (arguments[0].callback)
        {
            win.callback = arguments[0].callback;
        }

        if (arguments[0].onHide)
        {
        	win.onHide = arguments[0].onHide;
        }

		base_form.findField('DopDispQuestion_setDate').setMaxValue(undefined);
		base_form.findField('DopDispQuestion_setDate').setMinValue(undefined);

		if (arguments[0].minDate)
		{
			base_form.findField('DopDispQuestion_setDate').setMinValue(arguments[0].minDate);
		}

		if (arguments[0].maxDate)
		{
			base_form.findField('DopDispQuestion_setDate').setMaxValue(arguments[0].maxDate);
		}
		
		base_form.reset();
		
		if (arguments[0].DopDispQuestion_setDate) {
			base_form.findField('DopDispQuestion_setDate').setValue(arguments[0].DopDispQuestion_setDate);
		} else {
			var set_date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
			base_form.findField('DopDispQuestion_setDate').setValue(set_date);
		}

		if (win.action == 'view') {
			win.enableEdit(false);
			win.setTitle('Анкетирование: Просмотр');
		} else {
			win.enableEdit(true);
			win.setTitle('Анкетирование: Редактирование');
		}

		// Скрываем поля о услуге
		base_form.findField('UslugaComplex_id').setContainerVisible(false);
		base_form.findField('LpuSection_uid').setContainerVisible(false);
		base_form.findField('MedStaffFact_id').setContainerVisible(false);
		base_form.findField('Diag_id').setContainerVisible(false);
		base_form.findField('DopDispDiagType_id').setContainerVisible(false);
		base_form.findField('DeseaseStage').setContainerVisible(false);

		base_form.findField('Diag_id').setAllowBlank(true);
		base_form.findField('DeseaseStage').setAllowBlank(true);
		if (arguments[0].EvnPLDisp_consDate) {
			var xdate = new Date(2016,0,1);
			if (Date.parseDate(arguments[0].EvnPLDisp_consDate, 'd.m.Y') >= xdate) {
				// Если дата согласия 1.01.2016 и позже показываем поля о услуге
				base_form.findField('LpuSection_uid').setContainerVisible(true);
				base_form.findField('MedStaffFact_id').setContainerVisible(true);

				base_form.findField('Diag_id').setContainerVisible(true);
				base_form.findField('DopDispDiagType_id').setContainerVisible(true);

				// Обязательные поля:
				base_form.findField('Diag_id').setAllowBlank(false);

				if (getRegionNick().inlist(['perm', 'kareliya', 'buryatiya'])) {
					base_form.findField('DeseaseStage').setContainerVisible(true);
				}
				
				if (getRegionNick() == 'adygeya' && Number(win.DispClass_id).inlist([1,5])) {
					base_form.findField('UslugaComplex_id').setContainerVisible(true);
					base_form.findField('UslugaComplex_id').setAllowBlank(false);
				}
			}
		}

		win.doLayout();
		
		base_form.findField('DopDispQuestion_setDate').fireEvent('change', base_form.findField('DopDispQuestion_setDate'), base_form.findField('DopDispQuestion_setDate').getValue());

		win.dopDispQuestionGrid.setDataUrl('/?c=DopDispQuestion&m=loadDopDispQuestionGrid');
		win.dopDispQuestionGrid.getGrid().getStore().load({
			callback: function() {
				win.dopDispQuestionPanel.getForm().findField('DopDispQuestion_setDate').focus(250, true);
			},
			params: {
				EvnPLDisp_id: win.EvnPLDisp_id
			}
		});

		if (Ext.isEmpty(win.EvnUslugaDispDop_id)) {
			var Diag_Code = 'Z10.8';

			switch (getRegionNick()) {
				case 'kareliya':
				case 'penza':
					Diag_Code = 'Z01.8';
					break;

				case 'krym':
					Diag_Code = 'Z00.0';
					break;
			}

			base_form.findField('Diag_id').getStore().load({
				params: { where: "where Diag_Code = '" + Diag_Code + "'" },
				callback: function() {
					if (base_form.findField('Diag_id').getStore().getCount() > 0) {
						var diag_id = base_form.findField('Diag_id').getStore().getAt(0).get('Diag_id');
						base_form.findField('Diag_id').setValue(diag_id);
						base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), base_form.findField('Diag_id').getStore().getAt(0), 0);
						base_form.findField('Diag_id').onChange();
					}
				}
			});
		} else {
			base_form.load({
				url: '/?c=DopDispQuestion&m=loadDopDispQuestionEditWindow',
				params: {
					EvnUslugaDispDop_id: win.EvnUslugaDispDop_id
				},
				success: function() {
					if (!Ext.isEmpty(base_form.findField('Diag_id').getValue())) {
						base_form.findField('Diag_id').getStore().load({
							params: {where: "where Diag_id = "+base_form.findField('Diag_id').getValue()},
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
			});
		}

		if ( getRegionNick() == 'kareliya' ) {
			var karMed = base_form.findField('MedStaffFact_id');
			karMed.setAllowBlank(false);
			if (sw.Promed.MedStaffFactByUser.last.MedStaffFact_id){
				karMed.setValue(sw.Promed.MedStaffFactByUser.last.MedStaffFact_id);
			}
		}
    },
    width: 750
});