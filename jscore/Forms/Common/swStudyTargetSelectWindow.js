/**
 * swStudyTargetSelectWindow - Форма выбора Цели назначения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2017 Swan Ltd.
 */

sw.Promed.swStudyTargetSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swStudyTargetSelectWindow',
	objectSrc: '/jscore/Forms/Common/swStudyTargetSelectWindow.js',
	collapsible: false,
	draggable: true,
	id: 'StudyTargetSelectWindow',
    buttonAlign: 'left',
    closeAction: 'hide',
	maximized: false,
	modal: true,
	autoHeight: true,
	width: 600,
    title: 'Параметры исследования',
    callback: Ext.emptyFn,
    onHide: Ext.emptyFn,
    hasSelect: false,
	params: {
		UslugaComplex_id: null,
		UslugaComplex_List: null,
		Person_id: null,
		Sex_id: null,
		RaceType_id: null,
		PersonHeight_Height: null,
		PersonHeight_setDT: null,
		PersonWeight_WeightText: null,
		PersonWeight_setDT: null
	},
	listeners: {
		hide: function (wnd) {
			for (var i in wnd.params) {
				wnd.params[i] = null;
			}
		}
	},
	UslugaComplex_AttributeList: [],
	hiddenFields: [
		'RaceType_id',
		'PersonHeight_Height',
		'PersonHeight_setDT',
		'PersonWeight_WeightText',
		'PersonWeight_setDT'
	],
	show: function() {

        sw.Promed.swStudyTargetSelectWindow.superclass.show.apply(this, arguments);

		var wnd = this;
		var baseForm = wnd.formPanel.getForm();

		wnd.hasSelect = false;
		wnd.callback = Ext.emptyFn;
		wnd.onHide = Ext.emptyFn;

        if (typeof arguments[0].callback == 'function') { wnd.callback = arguments[0].callback; }
        if (typeof arguments[0].onHide == 'function') { wnd.onHide = arguments[0].onHide; }



		var params = (arguments[0].params) ? arguments[0].params : null;

		var duplicatedFieldsPanel = wnd.findById(wnd.id + "_" + 'ToothNumFieldsPanel');
		duplicatedFieldsPanel.clearPanel();

		// show() отрабатывало некорректно, пришлось хайдить
		if (!params.parentEvnClass_SysNick || (params.parentEvnClass_SysNick && params.parentEvnClass_SysNick != "EvnVizitPLStom")
		|| !params.PrescriptionType_Code || (params.PrescriptionType_Code && params.PrescriptionType_Code != 12)) {

			duplicatedFieldsPanel.hide();
		}
		wnd.selectStudyTargetForm.getForm().reset();

		baseForm.findField('HormonalPhaseType_id').disable();
		baseForm.findField('HormonalPhaseType_id').hideContainer();
		baseForm.findField('CovidContingentType_id').disable();
		baseForm.findField('CovidContingentType_id').hideContainer();
		baseForm.findField('HIVContingentTypeFRMIS_id').disable();
		baseForm.findField('HIVContingentTypeFRMIS_id').hideContainer();

		if ( getRegionNick() == 'ufa' ) {
			wnd.showLoadMask(langs('pojaluysta_podojdite_idet_zagruzka_dannyih_formyi'));

			if (params.UslugaComplex_id) wnd.params.UslugaComplex_id = params.UslugaComplex_id;
			if (params.UslugaComplex_List) wnd.params.UslugaComplex_List = params.UslugaComplex_List;
			if (params.Person_id) wnd.params.Person_id = params.Person_id;
			if (params.UslugaComplexMedService_pid) wnd.params.UslugaComplexMedService_pid = params.UslugaComplexMedService_pid;
			if (params.Lpu_id) wnd.params.Lpu_id = params.Lpu_id;

			wnd.loadEvnDirectionPersonDetails()
			.then(personDetails => {
				for (var i in personDetails)
					if (wnd.params.hasOwnProperty(i)) wnd.params[i] = personDetails[i];
				wnd.setValueToHidden();
			})
			.then(() => {
				if (!wnd.params.UslugaComplex_List) {
					wnd.params.UslugaComplex_List = [];
					return wnd.loadUslugaComplexList()
					.then((result) => {
						for (var i = 0; i < result.length; i++) {
							wnd.params.UslugaComplex_List.push(result[i].UslugaComplex_id);
						}
						wnd.params.UslugaComplex_List.push(wnd.params.UslugaComplex_id);
					})
				}
				return true;
			})
			.then(() => {
				return wnd.loadUslugaComplexDetails().then(attributeList => {
					wnd.UslugaComplex_AttributeList = attributeList;
				})
			})
			.then(() => {
				wnd._processFieldsVisible();
				wnd.hideLoadMask();
			})
			.catch((err) => { Ext.Msg.alert(lang['oshibka'], err.message); wnd.hideLoadMask(); });
		} else {
			Ext.getCmp(wnd.id + '_RaceType_FS').hide();
			Ext.getCmp(wnd.id + '_PersonWeight_FS').hide();
			Ext.getCmp(wnd.id + '_PersonHeight_FS').hide();
		}
		wnd.syncShadow();
		return true;
	},
    doSave: function(){

		var wnd = this,
			form = wnd.findById('SelectStudyTargetForm').getForm();

		if (!form.isValid()) {
			Ext.Msg.alert(lang['oshibka_zapolneniya_formyi'],lang['proverte_pravilnost_zapolneniya_poley_formyi']);
			return;
		}

		var data = form.getValues();
		var duplicatedFieldsPanel = wnd.findById(wnd.id + "_" + 'ToothNumFieldsPanel');

        this.callback({
			StudyTarget_id: data.StudyTarget_id,
			studyTargetPayloadData: {
				toothData: duplicatedFieldsPanel.getData()
			},
	        HIVContingentTypeFRMIS_id: data.HIVContingentTypeFRMIS_id || null,
			CovidContingentType_id: data.CovidContingentType_id || null,
	        HormonalPhaseType_id: data.HormonalPhaseType_id || null
		});

        this.hide();
        return true;
    },
	initComponent: function() {
		
		var wnd = this;

		wnd.selectStudyTargetForm = new Ext.form.FormPanel({
			id: 'SelectStudyTargetForm',
			layout: 'form',
			labelAlign: 'right',
			border: false,
			frame: true,
			labelWidth: 150,
			items: [
				{
					fieldLabel: 'Цель исследования',
					xtype: 'swcommonsprcombo',
					allowBlank: false,
					hiddenName: 'StudyTarget_id',
					value: 2,
					comboSubject: 'StudyTarget',
					anchor: '95%',
				},
				{
					xtype: 'swcommonsprcombo',
					fieldLabel: langs('Код контингента ВИЧ'),
					comboSubject: 'HIVContingentTypeFRMIS',
					hiddenName: 'HIVContingentTypeFRMIS_id',
					allowBlank: false,
					editable: true,
					ctxSerach: true,
					loadParams: { params: { where: ' where HIVContingentTypeFRMIS_Code != 100' } },
					anchor: '95%'
				}, {
					xtype: 'swcommonsprcombo',
					fieldLabel: langs('Код контингента COVID'),
					comboSubject: 'CovidContingentType',
					hiddenName: 'CovidContingentType_id',
					allowBlank: false,
					editable: true,
					ctxSerach: true,
					anchor: '95%'
				},
				{
					xtype: 'swcommonsprcombo',
					hiddenName: 'HormonalPhaseType_id',
					comboSubject: 'HormonalPhaseType',
					fieldLabel: langs('Фаза цикла'),
					anchor: '95%'
				}, {
					id: wnd.id + '_RaceType_FS',
					xtype: 'fieldset',
					layout: 'column',
					border: false,
					autoHeight: true,
					//labelWidth: 130,
					style: 'margin: 2px 0 0 0; padding: 0;',
					items: [
						{
							xtype: 'panel',
							html: 'Раса: ',
							layuot: 'anchor',
							width: 150,
							style: 'margin-right: 5px;',
							bodyStyle: 'text-align: right; border: 0px; font: normal 12px tahoma, arial, helvetica, sans-serif;'
						}, {
							xtype: 'swcommonsprcombo',
							fieldLabel: langs('Раса'),
							comboSubject: 'RaceType',
							hiddenName: 'RaceType_id',
							anchor: '95%',
							disabled: true
						}, {
							xtype: 'button',
							id: wnd.id + 'RaceTypeAddBtn',
							style: 'margin-left: 5px;',
							text: 'Добавить',
							handler: function () {
								getWnd('swPersonRaceEditWindow').show({
									formParams: {
										PersonRace_id: 0,
										Person_id: wnd.params.Person_id
									},
									action: 'add',
									onHide: Ext.emptyFn,
									callback: function(data) {
										if (!data || !data.personRaceData)
											return false;
										wnd.formPanel.getForm()
											.findField('RaceType_id')
											.setValue(data.personRaceData.RaceType_id);
										Ext.getCmp(wnd.id + 'RaceTypeAddBtn').setDisabled(true);
									}
								});
							}
						}
					]
				}, {
					id: wnd.id + '_PersonHeight_FS',
					xtype: 'fieldset',
					layout: 'column',
					border: false,
					autoHeight: true,
					labelWidth: 130,
					style: 'margin: 2px 0 0 0; padding: 0;',
					items: [
						{
							xtype: 'panel',
							html: 'Рост (см): ',
							name: 'PersonHeight_Height_label',
							layuot: 'anchor',
							width: 150,
							style: 'margin-right: 5px;',
							bodyStyle: 'text-align: right; border: 0px; font: normal 12px tahoma,arial,helvetica,sans-serif;'
						}, {
							xtype: 'textfield',
							name: 'PersonHeight_Height',
							disabled: true
						}, {
							xtype: 'panel',
							html: ' на дату: ',
							layuot: 'anchor',
							bodyStyle: 'padding: 1px 5px 0 5px; border: 0px; font: normal 12px tahoma,arial,helvetica,sans-serif;'
						}, {
							fieldLabel : lang['okonchanie'],
							name: 'PersonHeight_setDT',
							xtype: 'swdatefield',
							disabled: true,
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
						}, {
							xtype: 'button',
							id: wnd.id + 'PersonHeightAddBtn',
							text: 'Добавить',
							style: 'margin-left: 5px;',
							handler: function () {
								getWnd('swPersonHeightEditWindow').show({
									measureTypeExceptions:[1,2],
									formParams: {
										PersonHeight_id: 0,
										Person_id: wnd.params.Person_id
									},
									action: 'add',
									onHide: Ext.emptyFn,
									callback: function(data) {
										if (!data || !data.personHeightData)
											return false;
										wnd.formPanel.getForm()
											.findField('PersonHeight_Height')
											.setValue(data.personHeightData.PersonHeight_Height);
										var date = Ext.util.Format.date(new Date(data.personHeightData.PersonHeight_setDate), 'd.m.Y');
										wnd.formPanel.getForm()
											.findField('PersonHeight_setDT')
											.setValue(date);
									}
								});
							}
						}
					]
				}, {
					id: wnd.id + '_PersonWeight_FS',
					xtype: 'fieldset',
					layout: 'column',
					border: false,
					autoHeight: true,
					labelWidth: 130,
					style: 'margin: 2px 0 0 0; padding: 0;',
					items: [
						{
							xtype: 'panel',
							html: 'Масса: ',
							layuot: 'anchor',
							width: 150,
							style: 'margin-right: 5px;',
							bodyStyle: 'text-align: right; border: 0px; font: normal 12px tahoma, arial, helvetica, sans-serif;'
						}, {
							xtype: 'textfield',
							name: 'PersonWeight_WeightText',
							disabled: true
						}, {
							xtype: 'panel',
							html: ' на дату: ',
							layuot: 'anchor',
							bodyStyle: 'padding: 1px 5px 0 5px; border: 0px; font: normal 12px tahoma, arial, helvetica, sans-serif;'
						}, {
							fieldLabel : lang['okonchanie'],
							name: 'PersonWeight_setDT',
							xtype: 'swdatefield',
							disabled: true,
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
						}, {
							xtype: 'button',
							id: wnd.id + 'PersonWeightAddBtn',
							text: 'Добавить',
							style: 'margin-left: 5px;',
							handler: function () {
								getWnd('swPersonWeightEditWindow').show({
									measureTypeExceptions:[1,2],
									Okei_InterNationSymbol:"kg",
									formParams: {
										PersonWeight_id: 0,
										Person_id: wnd.params.Person_id
									},
									action: 'add',
									onHide: Ext.emptyFn,
									callback: function(data) {
										if (!data || !data.personWeightData)
											return false;
										wnd.formPanel.getForm()
											.findField('PersonWeight_WeightText')
											.setValue(data.personWeightData.PersonWeight_text);
										var date = Ext.util.Format.date(new Date(data.personWeightData.PersonWeight_setDate), 'd.m.Y');
										wnd.formPanel.getForm()
											.findField('PersonWeight_setDT')
											.setValue(date);
									}
								});
							}
						}
					]
				}, {
					ownerWindow: wnd,
					xtype: 'swduplicatedfieldpanel',
					fieldLbl: 'Номер зуба',
					fieldName: 'ToothNumEvnUsluga_ToothNum',
					id: wnd.id + '_' + 'ToothNumFieldsPanel',
					hidden: false,
					anchor: '95%',
				}]
		});
		wnd.formPanel = wnd.selectStudyTargetForm;

    	Ext.apply(this, {
			buttonAlign: "right",
			buttons: [
				{
					handler: function() { wnd.doSave() },
					iconCls: 'ok16',
					text: langs('Сохранить')
				},
				{text: '-'},
				HelpButton(this),
				{
					handler: function() { wnd.hide() },
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
            border: false,
			items: [wnd.selectStudyTargetForm]
		});
		sw.Promed.swStudyTargetSelectWindow.superclass.initComponent.apply(this, arguments);
	},
	_processFieldsVisible: function () {
		var baseForm = this.formPanel.getForm();
		var hiddenCount = 0;

		var isUfa = getGlobalOptions().region.nick === 'ufa';
		var isLab = checkUslugaAttribute(8, this.UslugaComplex_AttributeList);
		var isContingentReq = checkUslugaAttribute(224, this.UslugaComplex_AttributeList);
		var isContingentCovid = isUfa && isLab && checkUslugaAttribute(227, this.UslugaComplex_AttributeList);

		var RaceType_FS = Ext.getCmp(this.id + '_RaceType_FS');
		if (!isUfa || !isLab) {
			RaceType_FS.hide();
			hiddenCount++;
		} else {
			RaceType_FS.show();
			Ext.getCmp(this.id + 'RaceTypeAddBtn').setDisabled(!Ext.isEmpty(baseForm.findField('RaceType_id').getValue()));
		}
		var HIVContingentTypeFRMIS_id = baseForm.findField('HIVContingentTypeFRMIS_id');
		if (!isUfa || !isLab || !isContingentReq) {
			HIVContingentTypeFRMIS_id.setDisabled(true);
			HIVContingentTypeFRMIS_id.hideContainer();
			hiddenCount++;
		} else {
			HIVContingentTypeFRMIS_id.setDisabled(false);
			HIVContingentTypeFRMIS_id.showContainer();
			
		}

		var CovidContingentTypeField = baseForm.findField('CovidContingentType_id');
		CovidContingentTypeField.setDisabled(!isContingentCovid);
		CovidContingentTypeField.setContainerVisible(isContingentCovid);

		var HormonalPhaseType_id = baseForm.findField('HormonalPhaseType_id');
		if (!isUfa || !isLab || !(this.params.Sex_id == 2)) {
			HormonalPhaseType_id.hideContainer();
			HormonalPhaseType_id.disable();
			hiddenCount++;
		} else {
			HormonalPhaseType_id.showContainer();
			HormonalPhaseType_id.enable();
		}
		Ext.getCmp(this.id + '_PersonHeight_FS').setVisible(isUfa && isLab);
		Ext.getCmp(this.id + '_PersonWeight_FS').setVisible(isUfa && isLab);
		this.syncShadow();
	},
	loadEvnDirectionPersonDetails: function () {
		var scope = this;
		return new Promise(function (resolve, reject) {
			var requestParams = {
				callback: function (options, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						resolve(response_obj);
					} else {
						throw new Error('Ошибка при загрузке сигнальной информации');
					}
				},
				params: {
					Person_id: scope.params.Person_id
				},
				url: '/?c=PersonDetailEvnDirection&m=getOne'
			};
			Ext.Ajax.request(requestParams);
		});
	},
	loadUslugaComplexDetails: function () {
		var scope = this;
		return new Promise(function (resolve, reject) {
			var requestParams = {
				callback: function (options, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						resolve(response_obj);
					} else {
						throw new Error('Ошибка при загрузке информации об атрибутах комплексной услиги');
					}
				},
				params: {
					uslugaComplexList: Ext.util.JSON.encode(scope.params.UslugaComplex_List),
					UslugaComplex_id: scope.params.UslugaComplex_id
				},
				url: '/?c=UslugaComplex&m=loadUslugaComplexAttributeGrid'
			};
			Ext.Ajax.request(requestParams);
		});
	},
	loadUslugaComplexList: function () {
		var scope = this;
		return new Promise(function (resolve, reject) {
			Ext.Ajax.request({
				params: {
					UslugaComplexMedService_pid: scope.params.UslugaComplexMedService_pid,
					UslugaComplex_pid: scope.params.UslugaComplex_id,
					Lpu_id: scope.params.Lpu_id
				},
				callback: function (options, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						resolve(response_obj);
					} else {
						throw new Error('Ошибка при загрузке состава комплексной услиги');
					}
				},
				url: '/?c=MedService&m=loadCompositionMenu'
			});
		});
	}
});
