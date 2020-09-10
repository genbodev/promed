/**
 * Форма первичного онко скрининга
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * EvnPLDispDop13Form
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 *
 */

Ext6.define('common.EMK.EvnPLDispScreenOnkoForm', {
	requires: [
		'common.EMK.EvnDirectionPanel',
		'common.EMK.EvnPLDispScreenOnko.EvnPLDispScreenPrescrPanel',
		'common.EMK.EvnPLDispScreenOnko.PersonProfileEditPanel'
	],
	alias: 'widget.EvnPLDispScreenOnkoForm',
	itemId: 'ScreenOnkoForm',
	cls: 'screen-onko-form dispdop13form emk-forms-window arm-window-new',
	extend: 'Ext6.Panel',
	layout: 'border',
	region: 'center',
	border: false,
	evnParams: {},
	params: {},
	status: 0, //статус формы (Не сохранено / Сохранено)
	changeStatus: function(status) {// 0 / 1
		var me = this;

		//~ if(me.status==status) return;
		me.status = status;
		me.queryById('status').setText(status==1 ? 'Сохранено' : 'Не сохранено');
		me.queryById('status').setTooltip(status==1 ? '' : 'Для сохранения нажмите "Пройти осмотр"');

		//~ me.ConfirmPanel.queryById('panelstatus').setVisible(status==1);
		me.ConfirmPanel.queryById('panelstatus').setText(status==1 ? 'Перечень услуг определен' : '');
		me.ConfirmPanel.queryById('panelstatus').setIconCls(status==1 ? 'icon-ok' : '');

		me.ProtokolForm.expand();
		me.ProtokolForm.removeAll();
		me.ProtokolPanel.collapse();

		me.AnketaIsAgree = false;
		me.AnketaIsEarlier = false;

		me.confirmGrid.getStore().each(function(rec) {
			if(rec.get('SurveyType_id') == 2) {
				me.AnketaIsAgree = rec.get('DopDispInfoConsent_IsAgree');
				me.AnketaIsEarlier = rec.get('DopDispInfoConsent_IsEarlier');
			}
		});

		var AnketaIsVisible = ((status == 1 && me.AnketaIsAgree) || me.AnketaIsEarlier);

		me.AnketaPanel.setVisible(AnketaIsVisible);
		if (AnketaIsVisible) {
			me.AnketaPanel.expand();
		}

		if(status==1) {//загружаем и разворачиваем доп.разделы
			//me.loadAnketa();
			me.ProtokolPanel.expand();
			me.createProtokolForms();
			me.PrescribePanel.expand();
		} else me.ConfirmPanel.expand();
	},
    getParams: function() { return this.params; },
	setParams: function(params) {//метод для родителя
		var me = this;

		//~ var piPanel = me.ownerWin.PersonInfoPanel;
		me.params = params;
	},
	scrollToAnketa: function() {
		this.AnketaPanel.expand();
		document.getElementById(this.AnketaPanel.id).scrollIntoView();
	},
	scrollToProtokol: function(SurveyType_id, EvnUsluga_id) {
		var me = this;
		var prot = this.queryById(this.id + 'ProtokolForm' + SurveyType_id);
		if (prot && prot.EvnUslugaDispDop_setDT) {
			this.ProtokolPanel.expand();
			prot.expand();
			document.getElementById(prot.id).scrollIntoView();
		} else {
			getWnd('EvnUslugaCommonEditWindow').show({
				action: 'view',
				Person_id: me.params.Person_id,
				onHide: Ext6.emptyFn,
				parentClass: 'EvnVizit',
				formParams: {
					EvnUslugaCommon_id: EvnUsluga_id
				}
			});
		}
	},
	loadFormPanel: function(options) {//
		var me = this;
		//~ me.mask(LOADING_MSG);
		if (options && typeof options.callback == 'function') {
			options.callback();
		}
		me.HiddenForm.reset();
		me.changeStatus(0);
	},
	loadData: function(options) {//метод эмк
		var me = this;
		me.isLoading = true;
		me.titlePanel.hide();//?заголовок не нужен

		me.ConfirmPanel.collapse();
		me.AnketaPanel.collapse();
		me.queryById('EvnPLDispScreenOnko_setDate').enable();
		me.queryById('anketastatus').setText('');

		var d = new Date();
		d = d.format('d.m.Y');

		me.mask(langs('Загрузка формы'));
		me.getWRisk();
		me.queryById('PathologyType_WRisk').disable();
		Ext6.Ajax.request({
			url: '/?c=EvnPLDispScreenOnko&m=loadEvnPLDispScreenOnko',
			params: {
				EvnPLDispScreenOnko_id: me.params.EvnPLDispScreenOnko_id
			},
			failure: function(response, options){
				me.unmask();
			},
			success: function(response, action) {
				me.unmask();

				var data = Ext6.util.JSON.decode(response.responseText);
				if (data.length) {
					me.queryById('Diag_spid').setValue(data[0].Diag_spid);
					me.queryById('EvnPLDispScreenOnko_IsSuspectZNO').setValue(data[0].EvnPLDispScreenOnko_IsSuspectZNO == 2);
					//if (me.status==1) {
						d = data[0].EvnPLDispScreenOnko_setDate;
						me.queryById('EvnPLDispScreenOnko_setDate').disable();
					//}
				}

				me.queryById('EvnPLDispScreenOnko_IsSuspectZNO').fireEvent('change', me.queryById('EvnPLDispScreenOnko_IsSuspectZNO'), me.queryById('EvnPLDispScreenOnko_IsSuspectZNO').getValue());

				var components = me.query('combobox');
				// загружаем справочники
				loadDataLists(me, components, function() {
					me.loadFormPanel(options);

					me.queryById('EvnPLDispScreenOnko_setDate').setValue(d);

					me.mask(langs('Загрузка формы'));
					me.confirmGrid.getStore().load({
						params:{EvnPLDispScreenOnko_setDate: d, EvnPLDispScreenOnko_id: me.params.EvnPLDispScreenOnko_id, Person_id: me.params.Person_id, DispClass_id: 27},
						callback: function() {

						}
					});
				});

				me.isLoading = false;
			}
		});
	},
	saveResult: function(options) {

		var me = this,
			params = {
				EvnPLDispScreenOnko_id: me.params.EvnPLDispScreenOnko_id,
				EvnPLDispScreenOnko_IsSuspectZNO: me.queryById('EvnPLDispScreenOnko_IsSuspectZNO').getValue() ? 2 : 1,
				Diag_spid: me.queryById('Diag_spid').getValue()
			};

		if (me.isLoading) return false;

		Ext6.Ajax.request({
			url: '/?c=EvnPLDispScreenOnko&m=saveResult',
			params: params,
			failure: function(result_form, action) {
				sw4.showInfoMsg({
					panel: me,
					type: 'error',
					text: 'Ошибка сохранения данных'
				});
			},
			success: function(response, action) {
				sw4.showInfoMsg({
					panel: me,
					type: 'success',
					text: 'Данные сохранены'
				});
			}
		});
	},
	saveProtokolForm: function(options) {

		var me = this,
			SurveyType_id = options.SurveyType_id,
			UslugaComplex_id = options.UslugaComplex_id,
			form = me.ProtokolForm.query('[iDt=FIP_' + SurveyType_id+']')[0],
			formValid = true,
			fdata = [],
			params = {
				EvnPLDispScreenOnko_id: me.params.EvnPLDispScreenOnko_id,
				SurveyType_id: SurveyType_id,
				UslugaComplex_id: UslugaComplex_id,
				Lpu_id: getGlobalOptions().lpu_id,
				Server_id: me.ownerWin.PersonInfoPanel.serverId,
				PersonEvn_id: me.ownerWin.PersonInfoPanel.PersonEvn_id,
				MedPersonal_id: me.ownerWin.userMedStaffFact.MedPersonal_id,
				MedStaffFact_id: me.ownerWin.userMedStaffFact.MedStaffFact_id,
			};

		form.items.items.forEach(function(item) {
			var f_el = {
				FormalizedInspectionParams_id: item.FormalizedInspectionParams_id,
				FormalizedInspection_Result: null,
				FormalizedInspection_DirectoryAnswer_id: null,
				FormalizedInspection_NResult: null,

			};

			if (item.xtype == 'radiogroup') {
				f_el.FormalizedInspection_Result = item.getValue()[item.name] || null;
			} else if (item.xtype == 'checkbox') {
				f_el.FormalizedInspection_DirectoryAnswer_id = item.getValue() ? 2 : 1;
			} else if (item.xtype == 'numberfield') {
				if (!item.isValid()) formValid = false;
				f_el.FormalizedInspection_NResult = item.getValue();
			} else {
				f_el.FormalizedInspection_DirectoryAnswer_id = item.getValue();
			}

			fdata.push(f_el);
		});

		if (!formValid) return false;

		params.data = Ext6.encode(fdata);

		/*sw4.showInfoMsg({
			panel: me,
			type: 'loading',
			text: 'Сохранение...'
		});*/

		Ext6.Ajax.request({
			url: '/?c=EvnPLDispScreenOnko&m=saveFormalizedInspection',
			params: params,
			failure: function(response, action) {
				sw4.showInfoMsg({
					panel: me,
					type: 'error',
					text: 'Ошибка сохранения данных'
				});
			},
			success: function(response, action) {
				me.getWRisk();
					sw4.showInfoMsg({
						panel: me,
						type: 'success',
						text: 'Данные сохранены'
					});
			}
		});

	},

	getWRisk: function() {
		var me = this;
		Ext6.Ajax.request({
			url: '/?c=EvnPLDispScreenOnko&m=getProtokolFieldList',
			params: {
				EvnPLDispScreenOnko_id: me.params.EvnPLDispScreenOnko_id,
				checkRisk: true
			},
			success: function(response, action) {
				var data = Ext6.util.JSON.decode(response.responseText);
				log('Количество баллов риска',data[0]);
				if (typeof data[0] != "undefined" && data[0] == 0) {
					me.queryById('PathologyType_WRisk').setValue('Уровень риска: низкий');
				} else {
					me.queryById('PathologyType_WRisk').setValue('Уровень риска: высокий');
				}


			}})
	},

	createProtokolForms: function() {//создать все подразделы в протоколе (их не так много, лучше один раз создать все)
		var me = this;
		var panel = me.ProtokolForm;

		panel.removeAll();

		Ext6.Ajax.request(
		{
			url: '/?c=EvnPLDispScreenOnko&m=getProtokolFieldList',
			params: {
				EvnPLDispScreenOnko_id: me.params.EvnPLDispScreenOnko_id
			},
			failure: function(response, options)
			{
				me.unmask();
			},
			success: function(response, action)
			{
				me.unmask();

				var data = Ext6.util.JSON.decode(response.responseText);
				Ext6.suspendLayouts();
				data.forEach(function(item, index) {

					var items = [];
					var options = {
						UslugaComplex_id: item.UslugaComplex_id,
						SurveyType_id: item.SurveyType_id
					};
					var PathologyTypeBlock = null;
					var SurveyTypeBlock = null;
					var blockDisabled = false;

					item.data.forEach(function(itemd, index) {

						var defDisabled = (SurveyTypeBlock == item.SurveyType_id && blockDisabled); // (SurveyTypeBlock == item.SurveyType_id && blockDisabled) || !!item.EvnUslugaDispDop_setDT;

						if (itemd.FormalizedInspectionParams_Directory == 'PathologyType') {

							var subitems = [];
							var PathologyType_DefaultValue = null;

							itemd.PathologyType.forEach(function(itemp, index) {
								subitems.push({
									boxLabel: itemp.PathologyType_Name,
									inputValue: itemp.PathologyType_id,
									checked: itemd.FormalizedInspection_Result == itemp.PathologyType_id
								});
								if (itemp.PathologyType_IsDefault == 1) PathologyType_DefaultValue = itemp.PathologyType_id;
							});

							items.push({
								fieldLabel: itemd.FormalizedInspectionParams_Name,
								xtype: 'radiogroup',
								labelAlign: 'top',
								SurveyType_id: item.SurveyType_id,
								PathologyType_DefaultValue: PathologyType_DefaultValue,
								disabled: false, // !!item.EvnUslugaDispDop_setDT,
								columns: 1,
								FormalizedInspectionParams_id: itemd.FormalizedInspectionParams_id,
								name: 'FormalizedInspectionParams_id' + itemd.FormalizedInspectionParams_id,
								items: subitems,
								listeners: {
									'change': function (group, nv, ov) {
										var fs = Ext6.ComponentQuery.query('field[iDt='+item.SurveyType_id+'_'+itemd.FormalizedInspectionParams_id+']');
										var isDisabled = (nv['FormalizedInspectionParams_id' + itemd.FormalizedInspectionParams_id] == PathologyType_DefaultValue);
										fs.forEach(function(el) {
											el.setDisabled(isDisabled);
											if (isDisabled) {
												el.setValue('');
											}
										});
										me.saveProtokolForm(options);
									}
								}
							});

							blockDisabled = !itemd.FormalizedInspection_Result || itemd.FormalizedInspection_Result == PathologyType_DefaultValue;
							PathologyTypeBlock = itemd.FormalizedInspectionParams_id;
							SurveyTypeBlock = item.SurveyType_id;
						}

						else if (itemd.FormalizedInspectionParams_Directory == 'TopographyType') {

							items.push({
								width: 480,
								fieldLabel: itemd.FormalizedInspectionParams_Name,
								FormalizedInspectionParams_id: itemd.FormalizedInspectionParams_id,
								name: 'FormalizedInspectionParams_id' + itemd.FormalizedInspectionParams_id,
								valueField: 'TopographyType_id',
								displayField: 'TopographyType_Name',
								iDt: item.SurveyType_id + '_' + PathologyTypeBlock,
								disabled: defDisabled,
								queryMode: 'local',
								value: itemd.FormalizedInspection_DirectoryAnswer_id,
								store: new Ext6.data.SimpleStore({
									autoLoad: false,
									fields: [
										{ name: 'TopographyType_id', type: 'int' },
										{ name: 'TopographyType_Name', type: 'string' }
									],
									data: itemd.TopographyType
								}),
								xtype: 'baseCombobox',
								listeners: {
									'change': function () {
										me.saveProtokolForm(options);
									}
								}
							});
						}

						else if (itemd.FormalizedInspectionParams_Directory == 'YesNo') {
							items.push({
								xtype: 'checkbox',
								iDt: item.SurveyType_id + '_' + PathologyTypeBlock,
								disabled: defDisabled,
								FormalizedInspectionParams_id: itemd.FormalizedInspectionParams_id,
								name: 'FormalizedInspectionParams_id' + itemd.FormalizedInspectionParams_id,
								boxLabel: itemd.FormalizedInspectionParams_Name,
								labelSeparator: '',
								checked: (itemd.FormalizedInspection_DirectoryAnswer_id == 2),
								listeners: {
									'change': function () {
										me.saveProtokolForm(options);
									}
								}
							});
						}

						else {
							items.push({
								xtype: 'numberfield',
								iDt: item.SurveyType_id + '_' + PathologyTypeBlock,
								disabled: defDisabled,
								fieldLabel: itemd.FormalizedInspectionParams_Name + ' (см)',
								FormalizedInspectionParams_id: itemd.FormalizedInspectionParams_id,
								name: 'FormalizedInspectionParams_id' + itemd.FormalizedInspectionParams_id,
								hideTrigger: true,
								minValue: 0,
								maxLength: 7,
								value: itemd.FormalizedInspection_NResult,
								listeners: {
									'blur': function () {
										me.saveProtokolForm(options);
									}
								}
							});
						}
					});

					var grid = Ext6.create('common.EMK.EvnDirectionPanel', {
						ownerPanel: me,
						ownerWin: me.ownerWin,
						allTimeExpandable: true,
						btnAddClickEnable: false,
						isScreenOnko: true,
						userCls: 'accordion-panel-window accordion-panel-with-dropdown-menu',
					});

					grid.setParams({
						Evn_id: me.params.EvnPLDispScreenOnko_id,
						DopDispInfoConsent_id: item.DopDispInfoConsent_id,
						userMedStaffFact: me.ownerWin.userMedStaffFact,
						Person_id: me.params.Person_id,
						Server_id: me.params.Server_id,
						PersonEvn_id: me.ownerWin.PersonInfoPanel.PersonEvn_id,
						Person_Birthday: me.ownerWin.PersonInfoPanel.getFieldValue('Person_Birthday'),
						Person_Surname: me.ownerWin.PersonInfoPanel.getFieldValue('Person_Surname'),
						Person_Firname: me.ownerWin.PersonInfoPanel.getFieldValue('Person_Firname'),
						Person_Secname: me.ownerWin.PersonInfoPanel.getFieldValue('Person_Secname')
					});

					grid.addTool({
						type: 'plusmenu',
						tooltip: 'Создать направление',
						minWidth: 23,
						callback: function(panel, tool, event) {
							getWnd('swDirectionMasterWindow').show({
								directionData: {
									EvnDirection_pid: me.params.EvnPLDispScreenOnko_id
									,DopDispInfoConsent_id: item.DopDispInfoConsent_id
									,Diag_id: null
									,DirType_id: 16
									,MedService_id: me.ownerWin.userMedStaffFact.MedService_id
									,MedStaffFact_id: me.ownerWin.userMedStaffFact.MedStaffFact_id
									,MedPersonal_id: me.ownerWin.userMedStaffFact.MedPersonal_id
									,LpuSection_id: me.ownerWin.userMedStaffFact.LpuSection_id
									,ARMType_id: me.ownerWin.userMedStaffFact.ARMType_id
									,Lpu_sid: getGlobalOptions().lpu_id
									,withDirection: true
									,Person_id: me.params.Person_id
									,Server_id: me.params.Server_id
									,PersonEvn_id: me.ownerWin.PersonInfoPanel.PersonEvn_id
								},
								userMedStaffFact: me.ownerWin.userMedStaffFact,
								dirTypeData: {
									DirType_id: 16,
									DirType_Code: 12,
									DirType_Name: 'На поликлинический прием'
								},
								dirTypeCodeIncList: ['12'],
								personData: {
									Person_id: me.params.Person_id,
									Server_id: me.params.Server_id,
									PersonEvn_id: me.ownerWin.PersonInfoPanel.PersonEvn_id,
									Person_Birthday: me.ownerWin.PersonInfoPanel.getFieldValue('Person_Birthday'),
									Person_Surname: me.ownerWin.PersonInfoPanel.getFieldValue('Person_Surname'),
									Person_Firname: me.ownerWin.PersonInfoPanel.getFieldValue('Person_Firname'),
									Person_Secname: me.ownerWin.PersonInfoPanel.getFieldValue('Person_Secname')
								},
								onDirection: function () {
									grid.load();
								}
							});
						}
					});

					var cfg = Ext6.create('Ext6.form.Panel', {
						cls: 'accordion-panel-emk',
						userCls: 'panel-with-tree-dots accordion-panel-window',
						title: item.SurveyType_Name,
						itemId: me.id + 'ProtokolForm' + item.SurveyType_id,
						EvnUslugaDispDop_setDT: item.EvnUslugaDispDop_setDT,
						tools: [{
							xtype: 'button',
							userCls: 'button-without-frame',
							style: {'text-transform': 'none'},
							width: 200,
							text: item.EvnUslugaDispDop_setDT + ' &nbsp; ' + item.MedPersonal_Fin
						}],
						collapsed: true,
						layout: {
							type: 'accordion',
							titleCollapse: true,
							animate: true,
							multi: true
						},
						items: [{
							bodyPadding: '10 10 10 10',
							layout: 'column',
							header: {
								hidden: true
							},
							items: [{
								width: 200,
								height: 185,
								border: false,
								html: '<img src="img/OnkoScreen/'+item.SurveyType_id+'.png"  style="width: 150px; margin-left: 10px;">',
							}, {
								defaults: {
									labelWidth: 150,
								},
								iDt: 'FIP_' + item.SurveyType_id,
								cls: 'EvnCourseTreatEditPanel',
								border: false,
								items: items
							}]
						},grid]
					});

					panel.add(cfg);
				});
				Ext6.resumeLayouts(true);
			}
		});
		return;
	},
	saveDopDispInfoConsent: function(options) {
		var me = this,
			params = {},
			base_form = me.ConfirmForm.getForm(),
			grid = me.confirmGrid;
		me.mask(LOAD_WAIT_SAVE);
		var btn = me.queryById('DopDispInfoConsentSaveBtn');
		if ( btn.disabled || me.action == 'view' ) {
			return false;
		}

		options = options || {};

		btn.disable();
		params.EvnPLDispScreenOnko_setDate = me.queryById('EvnPLDispScreenOnko_setDate').getValue().format('d.m.Y');
		params.Person_id = me.params.Person_id;
		params.PersonEvn_id = me.ownerWin.PersonInfoPanel.PersonEvn_id;
		params.Server_id = me.params.Server_id;
		params.EvnPLDispScreenOnko_id = me.params.EvnPLDispScreenOnko_id;
		params.DispClass_id = 27;
		params.MedPersonal_id = me.ownerWin.userMedStaffFact.MedPersonal_id;
		params.MedStaffFact_id = me.ownerWin.userMedStaffFact.MedStaffFact_id;
		params.Lpu_id = getGlobalOptions().lpu_id;

		params.DopDispInfoConsentData = Ext6.util.JSON.encode(sw4.getStoreRecords( grid.getStore(), {
			exceptionFields: [
				'SurveyType_Name'
			]
		}));

		Ext6.Ajax.request(
		{
			url: '/?c=EvnPLDispScreenOnko&m=saveDopDispInfoConsent',
			params: params,
			failure: function(response)
			{
				btn.enable();
				me.unmask;
			},
			success: function(response, action)
			{
				btn.enable();
				me.unmask();
				me.changeStatus(1);
				if (options.doHide && me.callback) {
					me.callback();
				}
				else if (response.responseText)
				{

					var answer = Ext6.util.JSON.decode(response.responseText);
					if (answer.success && answer.EvnPLDispScreenOnko_id > 0)
					{
						grid.getStore().load({
							params:{
								EvnPLDispScreenOnko_setDate: me.queryById('EvnPLDispScreenOnko_setDate').getValue().format('d.m.Y'),
								EvnPLDispScreenOnko_id: answer.EvnPLDispScreenOnko_id,
								Person_id: me.params.Person_id,
								DispClass_id: 27
							},
							callback: function() {
							}
						});
					}
				}
			}
		});
	},

	checkConfirmSaved: function() {//проверяем все ли согласия сохранены
		var me = this,
			statusform = 1;

		me.confirmGrid.getStore().each(function(rec) {
			rec.set('test',false);
			if(
				(!Ext6.isEmpty(rec.get('DopDispInfoConsent_IsEarlier')) || !Ext6.isEmpty('DopDispInfoConsent_IsAgree'))//есть согласие/выполнено ранее
				&& (Ext6.isEmpty(rec.get('DopDispInfoConsent_id')) || rec.get('DopDispInfoConsent_id')<0) //и нет id согласия

			)
			{
				statusform = 0;
			}
			/*if(!Ext6.isEmpty(rec.get('EvnUsluga_Date'))) {
				rec.set('DopDispInfoConsent_IsAgree',false);
				rec.set('DopDispInfoConsent_IsEarlier', true);
			}*/
			if(rec.get('SurveyType')==2 && !Ext6.isEmpty(rec.get('onkoAnketaDate'))) {
				rec.set('DopDispInfoConsent_IsAgree',false);
				rec.set('DopDispInfoConsent_IsEarlier', true);
			}
		});
		me.changeStatus(statusform);
	},
	showAnketa: function(){
		var me = this;
		var action = 'view';
		if(!me.PersonOnkoProfile_id){
			action = 'add';
		}
		var params = {
			action: action,
			Person_id: me.params.Person_id,
			userMedStaffFact: me.userMedStaffFact,
			ReportType: 'onko',
			inPLDispScreen: true
		};
		if (action === 'add') {
			Ext6.apply(params, {
				PersonProfile_id: null
			});
		} else {
			// var PersonProfile_id = me.PersonOnkoProfile_id;
			//
			// var index = grid.store.find('PersonProfile_id', PersonProfile_id);
			// var record = grid.store.getAt(index);
			//
			// if (!record || Ext6.isEmpty(record.get('PersonProfile_id'))) {
			// 	return;
			// }

			Ext6.apply(params, {
				PersonProfile_id: me.PersonOnkoProfile_id
			});
		}
		me.PersonOnkoProfilePanel.show(params);
	},
	initComponent: function() {
		var me = this;
		me.titleLabel = Ext6.create('Ext6.form.Label', {
			xtype: 'label',
			cls: 'no-wrap-ellipsis',
			style: 'font-size: 16px; padding: 3px 10px;',
			html: 'Диспансеризация взрослого населения'
		});

		me.titlePanel = Ext6.create('Ext6.Panel', {
			region: 'north',
			style: {
				'box-shadow': '0px 1px 6px 2px #ccc',
				zIndex: 2
			},
			layout: 'border',
			border: false,
			height: 40,
			bodyStyle: 'background-color: #EEEEEE;',
			items: [{
				region: 'center',
				border: false,
				bodyStyle: 'background-color: #EEEEEE;',
				height: 40,
				bodyPadding: 10,
				items: [
					this.titleLabel
				]
			}
			],
			xtype: 'panel'
		});

		me.tabToolPanel = Ext6.create('Ext6.Toolbar', {
			region: 'east',
			width: '100%',
			height: 40,
			border: false,
			margin: "0 10px 0 0",
			items: [
			{
				text: 'Первичный онкологический скрининг',
				cls: 'dispdop-title1'
			},
			'->',
			{
				itemId: 'status',
				text: 'Не сохранено',
				cls: 'dispdop-title2',
				tooltip: 'Для сохранения нажмите "Пройти осмотр"'
			}]
		});

		me.TabContainer = Ext6.create('Ext6.Panel', {
			region: 'north',
			layout: 'border',
			border: false,
			height: 50,
			cls: 'topRadius leftPadding emk-top-panel',
			items: [{
				region: 'center',
				border: false,
				height: 40,
				items: [

				]
			}, this.tabToolPanel, {
				region: 'south',
				bodyStyle: 'background-color: #EEEEEE; border-width: 0px 1px 0px 1px; -webkit-box-shadow: inset 0px 7px 7px -5px rgba(0,0,0,0.2); -moz-box-shadow: inset 0px 7px 7px -5px rgba(0,0,0,0.2); box-shadow: inset 0px 7px 7px -5px rgba(0,0,0,0.2);',
				height: 10,
				html: ''
			}],
			xtype: 'panel'
		});

		me.confirmGrid = Ext6.create('Ext6.grid.Panel', {
			//xtype: 'cell-editing',
			cls: 'grid-common',
			xtype: 'grid',
			region: 'center',
			border: false,
			viewConfig:{
				markDirty:false,
				getRowClass: function (record, rowIndex) {
					var c = record.get('DopDispInfoConsent_IsImpossible');
					if (c == 2) {
						return 'x-item-disabled';
					} else return '';
				}
			},
			store: {
				fields: [
					{ name: 'DopDispInfoConsent_id', type: 'int'},
					{ name: 'SurveyType_id', type: 'int'},
					{ name: 'SurveyTypeLink_id', type: 'int'},
					{ name: 'SurveyTypeLink_IsNeedUsluga', type: 'int' },
					{ name: 'SurveyType_Code', type: 'int' },
					{ name: 'SurveyType_isVizit ', type: 'int' },
					{ name: 'sortOrder', type: 'int' },
					{ name: 'SurveyTypeLink_IsDel', type: 'int' },
					{ name: 'SurveyTypeLink_IsUslPack', type: 'int' },
					{ name: 'DopDispInfoConsent_IsAgeCorrect', type: 'int' },
					{ name: 'SurveyType_Name', type: 'string' },
					{ name: 'DopDispInfoConsent_IsEarlier', type: 'bool' },
					{ name: 'DopDispInfoConsent_IsAgree', type: 'bool' },
					{ name: 'DopDispInfoConsent_IsImpossible', type: 'bool' },
					{ name: 'EvnPLDisp_id', type: 'int'},
					{ name: 'Lpu_Nick', type: 'int'},
					{ name: 'EvnUsluga_id', type: 'int'},
					{ name: 'EvnUslugaPar_id', type: 'int'},
					{ name: 'UslugaComplex_id', type: 'int'},//id услуги из SurveyTypeLink
					{ name: 'CompletedUslugaComplex_id', type: 'int'},//id выполненной услуги
					{ name: 'EvnUsluga_Date', type: 'date', format: 'd.m.Y', dateFormat: 'd.m.Y'},//дата выполненной услуги
					{ name: 'Lpu_Nick', type: 'string' },//ЛПУ где выполнена услуга
					{ name: 'test', type: 'int' }
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnPLDispScreenOnko&m=loadDopDispInfoConsent',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				sorters: [
					'sortOrder',
					'SurveyType_Code'
				],
				listeners: {
					load: function(store, records, success) {
						me.unmask();
						if(me.params.EvnPLDispScreenOnko_id){
							me.PrescribePanel.Person_id = me.params.Person_id;
							me.PrescribePanel.PersonEvn_id = me.ownerWin.PersonInfoPanel.PersonEvn_id;
							me.PrescribePanel.Server_id = me.params.Server_id;
							me.PrescribePanel.EvnPLDispScreenOnko_id = me.params.EvnPLDispScreenOnko_id;
							me.PrescribePanel.loadPrescribes(records);
						}
						me.PersonOnkoProfile_id = null;
						if(records){
							records.forEach(function(rec){
								if(rec.get('SurveyType_Code') == 2 && rec.get('PersonOnkoProfile_id'))
									me.PersonOnkoProfile_id = rec.get('PersonOnkoProfile_id')
							});
						}
						me.showAnketa();
						me.checkConfirmSaved();
					}
				}
			},
			columns: [
				{ dataIndex: 'DopDispInfoConsent_id', hidden: true },
				{ dataIndex: 'SurveyType_id', hidden: true },
				{ dataIndex: 'SurveyTypeLink_id', hidden: true },
				{ dataIndex: 'SurveyTypeLink_IsNeedUsluga', hidden: true },
				{ dataIndex: 'SurveyType_Code', hidden: true },
				{ dataIndex: 'SurveyType_isVizit ', hidden: true },
				{ dataIndex: 'sortOrder', hidden: true },
				{ dataIndex: 'SurveyTypeLink_IsDel', hidden: true },
				{ dataIndex: 'SurveyTypeLink_IsUslPack', hidden: true },
				{ dataIndex: 'DopDispInfoConsent_IsAgeCorrect', hidden: true },
				{ dataIndex: 'EvnUsluga_id', hidden: true },
				{ dataIndex: 'EvnUslugaPar_id', hidden: true },
				{ dataIndex: 'UslugaComplex_id', hidden: true },
				{ dataIndex: 'CompletedUslugaComplex_id', hidden: true },
				{ dataIndex: 'test', hidden: true },

				{ dataIndex: 'SurveyType_Name', type: 'string', text: 'Осмотр,<br>исследование', flex: 1, sortable: false },
				{ dataIndex: 'DopDispInfoConsent_IsEarlier', sortable: false, xtype: 'checkcolumn', text: 'Пройдено<br>ранее', width: 70,
					listeners: {
						beforecheckchange: function(column, rowIndex, newValue, record, eOpts ) {
							if(!record.get('EvnUsluga_id')) {
								return false;
							}
						},
						checkchange: function(column, rowIndex, newValue, record) {
							if(newValue) {
								//выбрали - убираем другие отметки в строке
								record.set('DopDispInfoConsent_IsAgree', false);
								record.set('DopDispInfoConsent_IsImpossible', false);
							} else {
								//сняли - возвращаем отметку на согласие
								record.set('DopDispInfoConsent_IsAgree', true);
							}
						}
					},
					renderer : function(value, meta) {
						var cssPrefix = Ext6.baseCSSPrefix,
							cls = [cssPrefix + 'grid-checkcolumn'],
							label = '',
							grid = meta.column.ownerCt.grid,
							store = meta.column.ownerCt.grid.store;

						if(meta.column.ownerCt.grid.disable2 || meta.record.get('SurveyType_id')==2) {
							meta.tdCls += ' ' + this.disabledCls;
						}
						if (value) {
							cls.push(cssPrefix + 'grid-checkcolumn-checked');
						}

						if(!Ext6.isEmpty(meta.record.get('EvnUsluga_Date')) || !Ext6.isEmpty(meta.record.get('onkoAnketaDate'))) {
							return '<span class="checkcolumnlabelbox ' + cls.join(' ') + '"></span>'+ label;
						}
						else return '';
					}
				},
				{ dataIndex: 'DopDispInfoConsent_IsAgree', xtype: 'checkcolumn', header: 'Выбор осмотра,<br>исследования', width: 100, sortable: false,
					listeners: {
						beforecheckchange: function(column, rowIndex, newValue, record, eOpts ) {

						},
						checkchange: function(column, rowIndex, newValue, record, eOpts ) {
							if(record.get('SurveyType_id')==2) {
								column.ownerCt.grid.disable2 = !newValue;
								column.ownerCt.grid.store.each(function(rec){
									rec.set('test',!rec.get('test'));//чтобы точно строка отрендерилась
									if(rec.get('SurveyType_id')!=2) {
										if(!newValue) {
											rec.set('DopDispInfoConsent_IsEarlier', false);
											rec.set('DopDispInfoConsent_IsAgree', false);
											rec.set('DopDispInfoConsent_IsImpossible', false);
										}
										else if(!rec.get('DopDispInfoConsent_IsEarlier') && !rec.get('DopDispInfoConsent_IsImpossible'))
											rec.set('DopDispInfoConsent_IsAgree', true);
									}
								});
							}
							if(newValue) {
								//согласие на новое исследование
								record.set('DopDispInfoConsent_IsEarlier', false);
								record.set('DopDispInfoConsent_IsImpossible', false);
							} else {
								//отказ от исследования
							}
						}
					},
					renderer : function(value, meta) {
						var cssPrefix = Ext6.baseCSSPrefix,
							cls = [cssPrefix + 'grid-checkcolumn'],
							grid = meta.column.ownerCt.grid,
							store = meta.column.ownerCt.grid.store;

						if( (meta.column.ownerCt.grid.disable2 &&
							(meta.record.get('SurveyType_id')!=2 )) || !Ext6.isEmpty(meta.record.get('onkoAnketaDate'))
						) {//сам опрос не дизейблим
							meta.tdCls += ' ' + this.disabledCls;
						}
						if (value) {
							cls.push(cssPrefix + 'grid-checkcolumn-checked');
						}

						return '<span class="checkcolumnlabelbox ' + cls.join(' ') + '"></span>';
					}
				},
				{ dataIndex: 'DopDispInfoConsent_IsImpossible', sortable: false, xtype: 'checkcolumn', text: 'Невозможно по<br>показаниям', width: 100,
					listeners: {
						beforecheckchange: function(column, rowIndex, newValue, record, eOpts ) {
							if(record.get('SurveyType_IsVizit')!=2) {
								return false;
							}
						},
						checkchange: function(column, rowIndex, newValue, record) {
							if(newValue) {
								//выбрали - убираем другие отметки в строке
								record.set('DopDispInfoConsent_IsEarlier', false);
								record.set('DopDispInfoConsent_IsAgree', false);
							} else {
								//сняли - возвращаем отметку на согласие
								record.set('DopDispInfoConsent_IsAgree', true);
							}
						}
					},
					renderer : function(value, meta) {
						var cssPrefix = Ext6.baseCSSPrefix,
							cls = [cssPrefix + 'grid-checkcolumn'],
							label = '';

						if (this.disabled || meta.column.ownerCt.grid.disable2) {
							meta.tdCls += ' ' + this.disabledCls;
						}
						if (value) {
							cls.push(cssPrefix + 'grid-checkcolumn-checked');
						}

						if(meta.record.get('SurveyType_IsVizit')!=2) return '';
						else return '<span class="checkcolumnlabelbox ' + cls.join(' ') + '"></span>';
					}
				},
				/*{ dataIndex: 'EvnUsluga_Date', sortable: false, type: 'date', text: 'Дата прохождения', width: 100,
					formatter: 'date("d.m.Y")' //вопрос делать ли отдельной колонкой или совмещенной с фио врачом
				},*/
				{ dataIndex: 'MedPersonalFIO', sortable: false, type: 'string', text: 'Дата прохождения, Ф.И.О. врача ', width: 300,
					renderer: function(value, meta) {
						var link = '';
						if (meta.record.get('SurveyType_IsVizit') == 1 && meta.record.get('SurveyType_id') != 2) { // исследования
							link = "getWnd('uslugaResultWindow').show({Evn_id: "+meta.record.get('EvnUslugaPar_id')+", object: 'EvnUslugaPar', object_id: 'EvnUslugaPar_id'});";
						} else if (meta.record.get('SurveyType_id') == 2) { // анкета
							link = "Ext6.getCmp('"+me.id+"').scrollToAnketa();";
							//link = "getWnd('swPersonProfileEditWindow').show({PersonProfile_id: "+meta.record.get('PersonOnkoProfile_id')+", ReportType: 'onko', action: 'view', Person_id: "+me.params.Person_id+"});";
						} else if (meta.record.get('SurveyType_IsVizit') == 2) { // осмотр
							link = "Ext6.getCmp('"+me.id+"').scrollToProtokol("+meta.record.get('SurveyType_id')+", "+meta.record.get('EvnUsluga_id')+");";
						}

						link += ' return false;';

						var s = '';
						if(meta.record.get('EvnUsluga_Date')) s+= '<a href="#" onclick="'+link+'">' + meta.record.get('EvnUsluga_Date').dateFormat('d.m.Y') + '</a>';
						if(meta.record.get('MedPersonalFIO')) s+= ' • '+meta.record.get('MedPersonalFIO');
						return s;
					}
				}
			]
		});

		me.HiddenForm = Ext6.create('Ext6.form.Panel', {
			hidden: true,
			items: [
				{
					name: 'EvnPLDispScreenOnko_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'accessType',
					xtype: 'hidden'
				}, {
					name: 'DispClass_id',
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
					name: 'EvnPLDispScreenOnko_disDate',
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispScreenOnko_setDate',
					xtype: 'datefield',
					hidden: true,
					value: new Date()
				}, {
					name: 'Server_id',
					value: 0,
					xtype: 'hidden'
				}
			]
		});

		var tools = [];
		var defaultTools = [
			{
				xtype: 'tbspacer',
				flex: 1
			}, {
				xtype: 'button',
				itemId: 'panelstatus',
				userCls: 'iconlabel button-without-frame',
				//~ iconCls: 'icon-ok',
				width: 200,
				text: ''
			}
			];

		defaultTools.forEach(function(tool){
			if(tool.xtype && tool.xtype == 'tbspacer')
				tools.push(tool);
			else
				tools.push(Ext6.Object.merge({
					//~ userCls: 'sw-tool-label',
				},tool));
		});

		me.ConfirmForm = Ext6.create('Ext6.form.Panel', {
			accessType: 'view',
			padding: "18 0 30 27",
			layout: 'anchor',
			bodyPadding: 10,
			border: false,
			defaults: {
				anchor: '100%'
			},
			items: [{
				layout: 'column',
				border: false,
				items: [{
					allowBlank: false,
					xtype: 'swDateField',
					fieldLabel: 'Дата проведения осмотра',
					width: 200+5+140,
					labelWidth: 200,
					name: 'EvnPLDispScreenOnko_setDate',
					itemId: 'EvnPLDispScreenOnko_setDate'
				}]
			},
			me.confirmGrid,
			{
				layout: {
					type: 'hbox',
					align: 'stretch'
				},
				style: 'padding-top: 33px;',
				border: false,
				items: [{
					border: false,
					flex: 1,
					xtype: 'label',
					html: ''
				}, {
					xtype: 'button',
					text: 'Пройти осмотр',
					itemId: 'DopDispInfoConsentSaveBtn',
					cls: 'button-primary',
					style: 'margin-left: 23px;',
					width: 150,
					handler: function() {
						me.saveDopDispInfoConsent();
					}
				}]
			}]
		});

		me.ConfirmPanel = Ext6.create('swPanel', {
			userCls: 'panel-with-tree-dots accordion-panel-window',
			title: 'Перечень услуг кабинета раннего выявления заболеваний',
			//~ collapseOnOnlyTitle: true,
			tools: tools,
			items: [
				me.ConfirmForm
			]
		});

		this.PersonOnkoProfilePanel = Ext6.create('common.EMK.EvnPLDispScreenOnko.PersonProfileEditPanel', {
			action: 'add',
			Person_id: me.Person_id,
			userMedStaffFact:  me.ownerWin.userMedStaffFact,
			ReportType: 'onko',
			ownerPanel: me,
			confirmGrid: me.confirmGrid,
			ownerWin: me.ownerWin,
			PersonInfoPanel: me.ownerWin.PersonInfoPanel,
			userCls: 'panel-with-tree-dots accordion-panel-window accordion-panel-prescr'
			});

		me.AnketaPanel = Ext6.create('swPanel', {
			userCls: 'panel-with-tree-dots accordion-panel-window questionnaire',
			title: 'Анкетирование по онкологии',
			tools: [{
				xtype: 'tbspacer',
				flex: 1
			}, {
				xtype: 'button',
				userCls: 'button-without-frame',
				style: {'text-transform': 'none'},
				itemId: 'anketastatus',
				width: 100
			}],
			//~ collapseOnOnlyTitle: true,
			listeners: {
				expand: function(p, eOpts) {
					var ank = me.PersonOnkoProfilePanel;
					switch(ank.action){
						case 'view':
							/*Ext6.Msg.alert(
								langs('Внимание'),
								langs(
									'У пациента уже есть актуальная Анкета по онкоконтролю. ' +
									'<br />Следующее заполнение Анкеты на пациента - в новом календарном году. ' +
									'<br />Актуальная Анкета будет открыта в режиме "Просмотр"'
								)
							);*/
							break;
						case 'not':
							var msg = 'Пациенту ' + ank.Diag_setDate + ' был поставлен диагноз <br />' + ank.Diag_Code + ' - "' + ank.Diag_Name + '".<br />Заполнение Анкеты не требуется.';
							Ext6.Msg.alert(langs('Внимание'), msg);
							me.AnketaPanel.hide();
							break;
				}

				}
			},
			items: [
				me.PersonOnkoProfilePanel
			]
		});

		{ // Протокол осмотра

			me.ProtokolForm = Ext6.create('Ext6.form.Panel', {
				cls: 'accordion-panel-emk',
				border: true,
				collapsed: true,
				bodyPadding: '10 10 10 10',
				layout: {
					type: 'accordion',
					titleCollapse: true,
					animate: true,
					multi: true
				},
				defaults: {
					bodyStyle: 'border-width: 0px 1px 1px 1px;',
					header: {
						cls: 'arrow-expander-panel',
						titlePosition: 1
					},
				}
			});
		}

		me.ProtokolPanel = Ext6.create('swPanel', {
			userCls: 'panel-with-tree-dots accordion-panel-window',
			title: 'Протокол осмотра',
			//~ collapseOnOnlyTitle: true,
			listeners: {
				expand: function(p, eOpts) {

				}
			},
			items: [
				me.ProtokolForm
			],
		});

		this.PrescribePanel = Ext6.create('common.EMK.EvnPLDispScreenOnko.EvnPLDispScreenPrescrPanel', {
			ownerPanel: me,
			autoHeight: true,
			confirmGrid: me.confirmGrid,
			userMedStaffFact: me.ownerWin.userMedStaffFact,
			ownerWin: me.ownerWin,
			PersonInfoPanel: me.ownerWin.PersonInfoPanel,
			userCls: 'panel-with-tree-dots accordion-panel-window accordion-panel-prescr'
			//~ collapseOnOnlyTitle: true,
			/*listeners: {
			expand: function(p, eOpts) {

			}
		},*/
		});


		me.ResultPanel = Ext6.create('swPanel', {
			userCls: 'panel-with-tree-dots accordion-panel-window',
			title: 'Результат',
			bodyPadding: 20,
			//~ collapseOnOnlyTitle: true,
			listeners: {
				expand: function(p, eOpts) {

				}
			},
			items: [{
					xtype: 'checkbox',
					//~ boxLabel: 'Подозрение на ЗНО',
					fieldLabel: 'Подозрение на ЗНО',
					itemId: 'EvnPLDispScreenOnko_IsSuspectZNO',
					width: 300,
					labelWidth: 180,
					listeners:{
						'change': function(checkbox, value) {
							if (!value) me.queryById('Diag_spid').setValue('');
							me.queryById('Diag_spid').setVisible(value);
							me.saveResult();
						}
					}
				}, {
					xtype: 'swDiagCombo',
					fieldLabel: 'Подозрение на диагноз',
					itemId: 'Diag_spid',
					width: 500,
					labelWidth: 180,
					additQueryFilter: "(Diag_Code like 'C%' or Diag_Code like 'D0%')",
					baseFilterFn: function(rec){
						if(typeof rec.get == 'function') {
							return (rec.get('Diag_Code').substr(0,1) == 'C' || rec.get('Diag_Code').substr(0,2) == 'D0');
						} else if (rec.attributes && rec.attributes.Diag_Code) {
							return (rec.attributes.Diag_Code.substr(0,1) == 'C' || rec.attributes.Diag_Code.substr(0,2) == 'D0');
						} else {
							return true;
						}
					},
					listeners: {
						'change': function () {
							me.saveResult();
						}
					}
				},
				{
					xtype: 'textfield',
					fieldLabel: 'Общий риск',
					itemId: 'PathologyType_WRisk',
					width: 500,
					labelWidth: 180
				}]
		});

		me.AccordionPanel = Ext6.create('Ext6.Panel', {
			cls: 'accordion-panel-emk',
			border: true,
			bodyStyle: 'border-width: 0px 1px 1px 1px;',
			defaults: {
				margin: "0px 0px 2px 0px"
			},
			layout: {
				type: 'accordion',
				titleCollapse: true,
				animate: true,
				multi: true,
				//~ activeOnTop: false
			},
			listeners: {
				'resize': function() {
					me.updateLayout();
				}
			},
			dockedItems: [
				me.TabContainer
			],
			items: [
				me.ConfirmPanel,
				me.AnketaPanel,
				me.ProtokolPanel,
				me.PrescribePanel,
				me.ResultPanel
			]
		});

		/*me.bottomPanel = Ext6.create('common.EMK.PersonBottomPanel', {
			ownerPanel: me,
			ownerWin: me.ownerWin
		});*/

		me.EvnPLDispFormPanel = Ext6.create('Ext6.form.FormPanel', {
			border: false,
			items: [
				me.HiddenForm,
				me.AccordionPanel,
				{
					xtype: 'button',
					text: 'Сохранить',
					itemId: 'saveBtn',
					cls: 'button-primary',
					margin: '10 0 10 0',
					//style: 'margin-left: 23px;',
					width: 150,
					handler: function() {
						me.saveDopDispInfoConsent({doHide: true});
					}
				}
			]
		});

		Ext6.apply(me, {
			border: false,
			items: [me.titlePanel, {
				region: 'center',
				flex: 400,
				bodyPadding: 10,
				scrollable: true,
				bodyStyle: "border-width: 1px 0;",
				border: false,
				items: [
					me.EvnPLDispFormPanel
				]
			}/*, me.bottomPanel*/]
		});

		me.callParent(arguments);
	}
});
