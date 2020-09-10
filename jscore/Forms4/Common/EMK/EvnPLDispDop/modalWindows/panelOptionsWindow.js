Ext6.define('common.EMK.EvnPLDispDop.model.EvnPLDispDop13UslugaOptionsModel', {
	extend: 'Ext6.app.ViewModel',
	alias: 'viewmodel.swEvnPLDispDopUslugaOptionsModel',
	data: {
		addDateTime: false,
		person_height: null,
		person_weight: null,
		EvnUsluga_Diag_Code: '',
		UslugaComplex_Date: '',
		EvnUsluga_UslugaComplex_Code: ''
	},
	formulas: {
		body_mass_index: function (get) {
			var h = get('person_height');
			var w = get('person_weight');
			return (h > 0) ? w / (h/100 * h/100) : '';
		},
		body_mass_index_over_max: function(get) {
			return Number(get('body_mass_index')) > 25;
		},
		body_mass_index_over_min: function(get) {
			return Number(get('body_mass_index')) <18;
		},
		isVisibleDeseaseStage: function(get) {
			var Diag_Code = get('EvnUsluga_Diag_Code'),
				UslugaComplex_Code = get('EvnUsluga_UslugaComplex_Code'),
				UslugaComplex_Date = get('UslugaComplex_Date'),
				ShowDeseaseStageCombo = getRegionNick().inlist(['perm','buryatiya','kareliya']),//для остальных всегда ShowDeseaseStageCombo == fasle
				visible = false;
			if(typeof UslugaComplex_Date != 'object') UslugaComplex_Date = Date.parseDate(UslugaComplex_Date, 'd.m.Y');
			
			if (UslugaComplex_Date) {
				if(ShowDeseaseStageCombo) {
					if(UslugaComplex_Date<Date.parseDate('01.04.2015', 'd.m.Y'))
					{
						ShowDeseaseStageCombo = false;
						//this.AllowEditDeseaseStageByDate = true;
					}
					// #161204, #179362 Поле "Стадия" на Перми и Карелии (Из ТЗ: Если дата подписания согласия больше или равна 01.01.2019) скрыто,
					if(UslugaComplex_Date>=Date.parseDate('01.01.2019', 'd.m.Y') && getRegionNick().inlist([ 'perm', 'kareliya']))
					{
						ShowDeseaseStageCombo = false;
						//this.AllowEditDeseaseStageByDate = false;
					}
				}
			}
			return ShowDeseaseStageCombo;
		},
	}
});

Ext6.define('common.EMK.EvnPLDispDop.modalWindows.panelOptionsWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swEvnPLDispDop13UslugaOptionsWindow',
	requires: [],
	renderTo: main_center_panel.body.dom,
	autoShow: false,
	cls: 'arm-window-new save-template-window arm-window-new-without-padding dispdop13form',
	title: 'Выполнение услуги',
	width: 845,
	//~ height: 686,
	layout: 'vbox',
	modal: true,
	blocktype: '',
	topPanelDisabled: true,
	viewModel: 'swEvnPLDispDopUslugaOptionsModel',
	setMode: function(mode){
		var me = this,
			saveBtn = me.queryById('buttonAccept');
		switch(mode){
			case 'TherOverview':
			
				break;
			case 'IndiProf':
				
				break;
			case 'Antropo':
				
				break;
			default:
				mode = 'default';
		}
		//var base_form = me.getForm();
		//base_form.findField('searchDrugNameCombo').focus();
		me.mode = mode;
		saveBtn.disable();
		me.reset();
	},
	onSprLoad: function(args) {
		var me = this,
			base_form = me.MainForm.getForm(),
			diagCombo = base_form.findField('Diag_id'),
			vm = me.getViewModel();
		me.MainForm.reset();
		me.callback = Ext6.emptyFn;
		
		var medcombo = base_form.findField('MedStaffFact_id');
		medcombo.getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));
		
		base_form.findField('ExaminationPlace_id').getStore().filterBy(function(rec) {
			return rec.get('ExaminationPlace_Code')!=2;
		});
		
		if(args[0] && args[0].params) {
			vm.set('UslugaComplex_Date', args[0].params.UslugaComplex_Date);
			me.setTitle(!Ext6.isEmpty(args[0].params.title) ? args[0].params.title : 'Выполнение услуги');
			
			if(args[0].params.ownerWin)
				me.ownerWin = args[0].params.ownerWin;
			
			//let data = args[0].params.DataValues;
			//base_form.setValues(data);
			
			base_form.reset();
			if(!Ext6.isEmpty(args[0].params.EvnUslugaDispDop_id)) {
				me.mask('Загрузка');
				Ext6.Ajax.request({
					url: '/?c=EvnUslugaDispDop&m=loadEvnUslugaDispDop',
					params: {
						EvnUslugaDispDop_id: args[0].params.EvnUslugaDispDop_id,
						ExtVersion: 6
					},
					callback: function(request, success, response){
						me.unmask();
						if (success && response && response.responseText) {
							//var data = Ext6.JSON.decode(response.responseText);
							var dec_data = Ext6.JSON.decode(response.responseText);
							if(dec_data) data = dec_data[0];
								else return;
							if (data && data.Error_Msg) {
								return;
							}
							me.values = Ext6.Object.merge(Ext6.isEmpty(me.values) ? {} : me.values, data);
							base_form.setValues(me.values);
							//me.setMode(me.blockType);
						}
					}
				});
				/*base_form.load({
					url: '/?c=EvnUslugaDispDop&m=loadEvnUslugaDispDop',
					failure: function() {
						
						me.unmask();
					}.createDelegate(this),
					params: {
						EvnUslugaDispDop_id: args[0].params.EvnUslugaDispDop_id,
						ExtVersion: 6
					},
					success: function(result_form, action) {
						me.unmask();
						
					},
					callback: function(success, response){
						me.unmask();
						
					}
				});*/
			}
			
			if (args[0].callback) {
				me.callback = args[0].callback;
			}
			
			diagCombo.clearBaseFilter();
			if(args[0].params.SurveyType_isVizit && args[0].params.SurveyType_isVizit == 2) {
				diagCombo.setBaseFilter(function(rec) {return rec.get('Diag_Code')<'D' || rec.get('Diag_Code').slice(0,3)>'D09'});
			}
		}
	},
	doSave: function(callback, nothide, options) {
		var me = this,
			vm = me.getViewModel(),
			base_form = me.MainForm.getForm(),
			params = {};
		params.ExtVersion = 6;
		
		if ( typeof options != 'object' ) {
			options = new Object();
		}
		
		if ( !base_form.isValid() )
		{
			sw.swMsg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					me.MainForm.getFirstInvalidEl().focus(false);
				},
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		if ( Ext6.isEmpty(base_form.findField('EvnUslugaDispDop_setDate').getValue()) && Ext6.isEmpty(base_form.findField('EvnUslugaDispDop_didDate').getValue()) ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Должна быть заполнена хотя бы одна дата'), function() {
				base_form.findField('EvnUslugaDispDop_setDate').focus(true);
			}.createDelegate(this));
			return false;
		}
		
		if (
			options.ignoreEmptyDidDate != true
			&& Ext6.isEmpty(base_form.findField('EvnUslugaDispDop_didDate').getValue())
			&& !Ext6.isEmpty(base_form.findField('ExaminationPlace_id').getValue())
		) {
			sw.swMsg.show({
				buttons: Ext6.Msg.YESNO,
				fn: function ( buttonId ) {
					if ( buttonId == 'yes' ) {
						options.ignoreEmptyDidDate = true;
						me.doSave(callback, nothide, options);
					}
					else {
						base_form.findField('EvnUslugaDispDop_didDate').focus(true);
					}
				},
				msg: langs('Дата выполнения осмотра/исследования не заполнена, продолжить сохранение?'),
				title: langs('Подтверждение сохранения')
			});
			return false;
		}
		
		let Diag_Code = base_form.findField('Diag_id').getFieldValue('Diag_Code');
		if (
			options.ignoreCheckDiag != true
			&& (
				(getRegionNick()=='krym' && Diag_Code !='Z00.0')
				|| ((getRegionNick()=='kareliya' || getRegionNick()=='penza') && Diag_Code !='Z01.8')
				|| (getRegionNick()!='krym' && getRegionNick()!='kareliya' && getRegionNick()!='penza' && Diag_Code !='Z10.8')
			)
		) {
			me.mask("Подождите, идет проверка диагноза...");
			var GroupDiag_Code = Diag_Code.slice(0,3);
			Ext6.Ajax.request({
				url: '/?c=EvnPLDispDop13&m=CheckDiag',
				params: {
					EvnPLDispDop13_id: base_form.findField('EvnVizitDispDop_pid').getValue(),
					Diag_id: base_form.findField('Diag_id').getValue(),
					EvnUslugaDispDop_id: base_form.findField('EvnUslugaDispDop_id').getValue()
				},
				failure: function(result_form, action) {
					me.unmask();
					
					sw.swMsg.show({
						buttons: Ext6.Msg.YESNO,
						fn: function ( buttonId ) {
							if ( buttonId == 'yes' ) {
								options.ignoreCheckDiag = true;
								me.doSave(callback, nothide, options);
							}
						},
						msg: langs('Ошибка при проверке на дублирование диагноза. Продолжить сохранение?'),
						title: langs('Подтверждение сохранения')
					});
				},
				success: function(response, action) {
					me.unmask();
					
					if (response.responseText != '') {
						
						var data = Ext6.util.JSON.decode(response.responseText);
						if (data) {
							if(data == base_form.findField('Diag_id').getValue()) {
								sw.swMsg.alert(langs('Ошибка'), langs('У пациента уже указан диагноз')+' <b>'+Diag_Code+'</b><br>'
									+langs('Проверьте правильность введенных данных.'),
									function() {
										base_form.findField('Diag_id').focus(true);
									}.createDelegate(this)
								);
							} else {
								sw.swMsg.show({
									buttons: {yes: langs('Продолжить'), no: langs('Отмена')},
									fn: function ( buttonId ) {
										if ( buttonId == 'yes' ) {
											options.ignoreCheckDiag = true;
											me.doSave(callback, nothide, options);
										} else {
											base_form.findField('Diag_id').focus(true);
										}
									},
									msg: langs('У пациента уже указан диагноз группы')+' <b>'+GroupDiag_Code+'</b>',
									title: langs('Подтверждение сохранения'),
									width: 300
								});
							}
						} else {//проверка успешна, пересечений диагнозов нет
							options.ignoreCheckDiag = true;
							me.doSave(callback, nothide, options);
						}
					}
				}
			});
			return false;
		}
		
		if(options.ignoreCheckMorbusOnko)
			params.ignoreCheckMorbusOnko = 1;
		params.isOnkoDiag = (Diag_Code && Diag_Code.search(new RegExp("^(C|D0)", "i")) !== -1)?1:0;
		
		base_form.submit({
			url: '/?c=EvnUslugaDispDop&m=saveEvnUslugaDispDop',
			failure: function(result_form, action) {
				me.unmask();
				
				if ( action.result ) {
					if ( action.result.Error_Msg || action.result.Alert_Msg ) {
						if(action.result.Alert_Msg){
							sw.swMsg.show({
								buttons: Ext6.Msg.OKCANCEL,
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'ok' ) {
										if(me.ownerWin && action.result.openSpecificAfterSave && base_form.findField('EvnVizitDispDop_id').getValue()){
											// открываем специфику, раз попросили
											me.ownerWin.ownerPanel.getController().openSpecificsWindow();
										}
										if (action.result.Error_Code == 289) {
											options.ignoreCheckMorbusOnko = 1;
											me.doSave(callback, nothide, options);
										}

									}
								}.createDelegate(this),
								icon: Ext6.MessageBox.QUESTION,
								msg: action.result.Alert_Msg + '<br>Продолжить сохранение?',
								title: ''
							});
						} else {
							sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
						}
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
					}
				}
			},
			params: params,
			success: function(result_form, action) {
				me.unmask();
				
				if ( action.result ) {
					me.hide();
					params.Diag_Code = base_form.findField('Diag_id').getFieldValue('Diag_Code');
					if(me.ownerWin && !callback && action.result.openSpecificAfterSave && base_form.findField('EvnVizitDispDop_id').getValue())
					{
						if(action.result.Alert_Msg){
							sw.swMsg.show({
								buttons: Ext6.Msg.OKCANCEL,
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'ok' ) {
										me.ownerWin.ownerPanel.getController().openSpecificsWindow();
									}
								}.createDelegate(this),
								icon: Ext6.MessageBox.QUESTION,
								msg: action.result.Alert_Msg,
								title: 'Продолжить сохранение?'
							});
						} else {
							// открываем специфику (тем самым создаем ее)
							me.ownerWin.ownerPanel.getController().openSpecificsWindow();
						}
						nothide = true;
					}

					me.callback(params);
					if (typeof callback == 'function') {
						callback();
					}
					if (!nothide) {
						me.hide();
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки'));
				}
			}
		});
	},
	initComponent: function () {
		let me = this,
			vm = me.getViewModel();

		me.MainForm = Ext6.create('Ext6.form.Panel', {
			border: false,
			bodyPadding: '20 20 0 20',
			//~ trackResetOnLoad: false,
			items: [
			{
				name: 'EvnUslugaDispDop_id',
				xtype: 'hidden'
			}, {
				name: 'EvnVizitDispDop_id',
				xtype: 'hidden'
			}, {
				name: 'EvnVizitDispDop_pid',
				xtype: 'hidden'
			}, {
				name: 'DopDispInfoConsent_id',
				xtype: 'hidden'
			}, {
				name: 'EvnDirection_id',
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				xtype: 'hidden'
			}, {
				name: 'CytoMedPersonal_id',
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				xtype: 'hidden'
			}, {
				name: 'XmlTemplate_id',
				xtype: 'hidden'
			}, {
				name: 'EvnDirection_Type',
				xtype: 'hidden'
				//fieldLabel: 'Тип'
			}, {
				name: 'EvnDirection_insDate',
				xtype: 'hidden',
				//fieldLabel: 'Дата создания'
			}, {
				name: 'EvnDirection_Num',
				xtype: 'hidden',
				//fieldLabel: 'Номер направления'
			}, {
				name: 'EvnDirection_RecTo',
				xtype: 'hidden',
				//fieldLabel: 'Место оказания'
			}, {
				name: 'EvnDirection_RecDate',
				xtype: 'hidden',
				//fieldLabel: 'Запись'
			}, {
				name: 'EvnUslugaDispDop_setDate',
				xtype: 'hidden'
			}, {
				name: 'EvnUslugaDispDop_setTime',
				xtype: 'hidden'
			//}, {
				//name: 'EvnUslugaDispDop_didDate',
				//xtype: 'hidden'
			//}, {
				//name: 'EvnUslugaDispDop_setTime',
				//xtype: 'hidden'
			//}, {
				//name: 'EvnUslugaDispDop_didTime',
				//xtype: 'hidden'
			//}, {
				//name: 'EvnUslugaDispDop_disDate',
				//xtype: 'hidden'
			//}, {
				//name: 'EvnUslugaDispDop_disTime',
				//xtype: 'hidden'
			},  {
				name: 'MedPersonal_id',
				xtype: 'hidden'
			},
			{
				xtype: 'fieldset',
				flex: 1,
				title: 'Направление/назначение',
				padding: '10 10 10 10',
				checkboxToggle: false,
				layout: 'anchor',
				defaults: {
					width: '100%',
					maxWidth: 635,
					labelWidth: 150,
					hideEmptyLabel: false
				},
				items: [{
					fieldLabel: 'Тип',
					xtype: 'textfield',
					disabled: true, //me.topPanelDisabled,
					width: '100%'
					//typeCode: 'int',
				}, {
					fieldLabel: 'Дата создания',
					xtype: 'textfield',
					disabled: true,
					width: 300,
					
					/*border: false,
					xtype: 'container',
					layout: 'column',
					items: [{
						padding: '0 0 5 0',
						fieldLabel: 'Дата создания',
						disabled: me.topPanelDisabled,
						xtype: 'datefield',
						width: 260,
						labelWidth: 150,
						startDay: 1,
						format: 'd.m.Y',
						labelAlign: 'left',
						invalidText: 'Неправильный формат даты',
						value: new Date()
					}]*/
				}, {
					fieldLabel: '№ направления',
					xtype: 'textfield',
					disabled: true
				}, {
					fieldLabel: 'Место оказания',
					xtype: 'textfield',
					disabled: true,
					width: '100%'
				}, {
					fieldLabel: 'Запись',
					xtype: 'textfield',
					disabled: true
				}]
			}, {
				xtype: 'fieldset',
				flex: 1,
				title: '',
				padding: 10,
				checkboxToggle: false,
				layout: 'anchor',
				defaults: {
					hideEmptyLabel: false,
					width: 635,
					labelWidth: 150
				},
				items: [{
					xtype: 'UslugaComplexCombo',
					name: 'UslugaComplex_id',
					fieldLabel: langs('Услуга'),
					queryMode: 'remote',
					bind: {
						value: '{UslugaComplex_id}'
					},
					allowBlank: false,
					listeners: {
						change: function (obj) {
							vm.set('EvnUsluga_UslugaComplex_Code', obj.getFieldValue('UslugaComplex_Code'));
						}
					}
				}, {
					border: false,
					xtype: 'container',
					itemId: 'containerDateTimeStartId',
					layout: 'column',
					style: 'margin-bottom: 5px;',
					items: [{
						fieldLabel: 'Начало выполнения',
						xtype: 'datefield',
						name: 'EvnUslugaDispDop_didDate',
						width: 270,
						labelWidth: 150,
						startDay: 1,
						format: 'd.m.Y',
						labelAlign: 'left',
						invalidText: 'Неправильный формат даты'
					}, {
						xtype: 'swTimeField',
						allowBlank: false,
						width: 100,
						userCls:'vizit-time',
						hideLabel: true,
						name: 'EvnUslugaDispDop_didTime',
						padding: '0 0 0 5'
					}, {
						xtype: 'button',
						itemId: 'containerDateTimeStartButtonId',
						padding: '5 0 0 10',
						cls: 'button-without-frame evnpldispdop-button-link',
						bind: {
							text: '{!addDateTime ? "Добавить время окончания":"Скрыть время окончания"}'
						},
						handler: function () {
							vm.set('addDateTime', !vm.get('addDateTime'));
						}
					}]
				}, {
					border: false,
					xtype: 'container',
					itemId: 'containerDateTimeEndId',
					layout: 'column',
					style: 'margin-bottom: 5px;',
					bind: {
						hidden: '{!addDateTime}'
					},
					items: [{
						fieldLabel: 'Дата окончания',
						xtype: 'swDateField',
						name: 'EvnUslugaDispDop_disDate',
						width: 270,
						labelWidth: 150,
						startDay: 1,
						format: 'd.m.Y',
						labelAlign: 'left',
						invalidText: 'Неправильный формат даты',
						//value: new Date()
					}, {
						xtype: 'swTimeField',
						allowBlank: false,
						width: 100,
						userCls:'vizit-time',
						hideLabel: true,
						name: 'EvnUslugaDispDop_disTime',
						padding: '0 0 0 5'
					},{
						xtype: 'hidden',
						name: 'useEndDateTime',
						itemId: 'useEndDateTime',
						value: false
					}]
				}, {
					xtype: 'swExaminationPlaceCombo',
					fieldLabel: 'Место выполнения',
					name: 'ExaminationPlace_id',
					//~ displayCode: true,
					bind: {
						value: '{ExaminationPlace_id}' //если "Другое МО" - показать доп.поля МО, Профиль, Специальность
					}
				}, {
					fieldLabel: 'МО',
					xtype: 'swLpuCombo',
					name: 'Lpu_uid',
					bind: {
						value: '{Lpu_uid}'
					}
				}, {
					fieldLabel: 'Профиль',
					xtype: 'swLpuSectionProfileCombo',
					name: 'LpuSectionProfile_id',
					bind: {
						value: '{LpuSectionProfile_id}',
					}
				}, {
					fieldLabel: 'Отделение',
					xtype: 'SwLpuSectionGlobalCombo',
					name: 'LpuSection_id',
					bind: {
						value: '{LpuSection_id}'
					}
				}, {
				    xtype: 'swMedStaffFactCombo',
					fieldLabel: langs('Врач'),
					name: 'MedStaffFact_id',
					bind: {
						value: '{MedStaffFact_id}'
					},
					listWidth: 750,
				}, {
					xtype: 'swDiagCombo',
					fieldLabel: 'Диагноз',
					name: 'Diag_id',
					userCls: 'diagnoz trigger-outside',
					listeners: {
						change: function(field, newVal, oldVal) {
							let diag_code = field.getFieldValue('Diag_Code');
							vm.set('EvnUsluga_Diag_Code', diag_code);
						}
					}
				}, {
					xtype: 'swDopDispDiagTypeCombo',
					fieldLabel: 'Характер заболевания',
					itemId: 'DopDispDiagType_id',
					name: 'DopDispDiagType_id',
					bind: {
						allowBlank: '{EvnUsluga_Diag_Code>"Z"}',
						disabled: '{EvnUsluga_Diag_Code>"Z"}'
						
					}
				}, {
					xtype: 'swDeseaseStageCombo',
					fieldLabel: 'Стадия',
					name: 'DeseaseStage',
					bind: {
						allowBlank: '{!isVisibleDeseaseStage}',
						visible: '{isVisibleDeseaseStage}'
					}
				}]
			}]
		});
		/*
		me.tabPanel1 = Ext6.create('Ext6.Panel', {
			border: false,
			hidden: true,
			title: 'Выполнение',
			padding: 10,
			flex: 1,
			//~ region: 'center',
			items: [
			]
		});
		me.tabPanel2 = Ext6.create('Ext6.Panel', {
			border: false,
			title: 'Направление',
			padding: 10,
			flex: 1,
			items: [
				me.MainForm
			],
		});

		me.tabPanel = Ext6.create('Ext6.TabPanel', {
			border: false,
			bodyBorder: false,
			flex: 1,
			//~ region: 'center',
			cls: 'light-tab-panel',
			items: [
				me.tabPanel2,
				me.tabPanel1 
			],
			listeners: {
				tabchange: function(panel, tab) {

				}
			}
		});*/

		me.mainPanel = Ext6.create('Ext6.Panel', {
			border: false,
			//~ region: 'center',
			flex: 1,
			bodyPadding: '20 20 0 20',
			defaults: {
				anchor: '100%',
				labelWidth: 130,
				matchFieldWidth: false
			},
			items: [me.tabPanel]
		});
		Ext6.apply(me, {
			defaults: {
				width: '100%'
			},
			items: [
				//me.mainPanel
				me.MainForm
			],
			buttons: [
				'->',
				{
					text: 'Отмена',
					userCls: 'buttonCancel',
					margin: 0,
					handler: function () {
						me.hide();
					}
				}, {
					itemId: 'savebutton',
					cls: 'buttonAccept',
					text: 'Сохранить',
					margin: '0 19 0 0',
					handler: function () {
						me.doSave();
					}
				}
			]
		});
		me.callParent(arguments);
	}
});