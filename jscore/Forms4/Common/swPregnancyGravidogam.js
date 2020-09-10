/**
 * swPregnancyGravidogamExt6 - Гравидограмма
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Swan
 * @version      21.05.2020
 */

Ext6.define('common.swPregnancyGravidogam', {
	alias: 'widget.swPregnancyGravidogamExt6',
	width: 1330,
	height: 720,
	title: langs('Гравидограмма'),
	cls: 'arm-window-new ',
	noTaskBarButton: true,
	closeToolText: 'Закрыть',
	closable: true,
	closeAction: 'hide',
	extend: 'base.BaseForm',
	renderTo: Ext6.getBody(),
	layout: 'border',
	constrain: true,
	modal: true,
	clearValues: [],
	storeBP: Ext6.create('Ext6.data.Store', {  //хранилище для диаграммы давления
		model: Ext6.create('Ext6.data.Model', {
			fields: [{
				name: 'SystolicBP',
				type: 'int'
			},{
				name: 'DiastolicBP',
				type: 'int'
			},{
				name: 'name',
				type: 'string'
			}]
		}),
		data: []
	}),
	storeVDM: Ext6.create('Ext6.data.Store', {  //хранилище для стояния дна матки
		model: Ext6.create('Ext6.data.Model', {
			fields: [{
				name: 'vdm',
				type: 'int'
			},{
				name: 'name',
				type: 'string'
			}]
		}),
		data: []
	}),
	setFieldValue: function(field, value) {
		var win = this;
		var base_form = win.MainPanel.getForm();
		var field_com = base_form.findField(field);
		field_com.setValue(!Ext6.isEmpty(value) ? value:null);
	},
	weekAct: function(col, weekStr, oneScrin) {
		var win = this;
		if (!Ext6.isEmpty(oneScrin.SystolicBP) && !Ext6.isEmpty(oneScrin.DiastolicBP)) {
			win.storeBP.add({ 'name': weekStr, 'SystolicBP':oneScrin.SystolicBP, 'DiastolicBP':oneScrin.DiastolicBP});
		}
		if (!Ext6.isEmpty(oneScrin.vdm)) {
			win.storeVDM.add({ 'name': weekStr, 'vdm':oneScrin.vdm});
		}
		return col;
	},
	loadAllData: function(PersonRegister_id) {
		var win = this;
		var base_form = win.MainPanel.getForm();
		var loadMask = new Ext6.LoadMask(this, {msg: 'Загрузка гравидограммы'});
		loadMask.show();
		Ext6.Ajax.request({
			url: '/?c=PersonPregnancy&m=loadPersonPregnancyGravidogramData',
			params: {
				PersonRegister_id: PersonRegister_id
			},
			callback: function (req, success, response) {
				loadMask.hide();
				if (success) {
					var responseData = Ext6.JSON.decode(response.responseText);
					win.storeBP.removeAll();
					win.storeVDM.removeAll();

					responseData.forEach(function (oneScrin) {
						week = oneScrin.PersonPregnancy_Week;
						if (week<5) return;

						switch(true) {
							case week>=5 && week<7:
								col_num = win.weekAct(1, '5-6', oneScrin);
								break;
							case week>=7 && week<9:
								col_num = win.weekAct(2, '7-8', oneScrin);
								break;
							case week>=9 && week<11:
								col_num = win.weekAct(3, '9-10', oneScrin);
								break;
							case week>=11 && week<13:
								col_num = win.weekAct(4, '11-12', oneScrin);
								break;
							case week>=13 && week<15:
								col_num = win.weekAct(5, '13-14', oneScrin);
								break;
							case week>=15 && week<17:
								col_num = win.weekAct(6, '15-16', oneScrin);
								break;
							case week>=17 && week<19:
								col_num = win.weekAct(7, '17-18', oneScrin);
								break;
							case week>=19 && week<21:
								col_num = win.weekAct(8, '19-20', oneScrin);
								break;
							case week>=21 && week<23:
								col_num = win.weekAct(9, '21-22', oneScrin);
								break;
							case week>=23 && week<25:
								col_num = win.weekAct(10, '23-24', oneScrin);
								break;
							case week>=25 && week<27:
								col_num = win.weekAct(11, '25-26', oneScrin);
								break;
							case week>=27 && week<29:
								col_num = win.weekAct(12, '27-28', oneScrin);
								break;
							case week>=29 && week<31:
								col_num = win.weekAct(13, '29-30', oneScrin);
								break;
							case week>=31 && week<33:
								col_num = win.weekAct(14, '31-32', oneScrin);
								break;
							case week>=33 && week<35:
								col_num = win.weekAct(15, '33-34', oneScrin);
								break;
							case week>=35 && week<37:
								col_num = win.weekAct(16, '35-36', oneScrin);
								break;
							case week>=37 && week<39:
								col_num = win.weekAct(17, '37-38', oneScrin);
								break;
							case week>=39 && week<41:
								col_num = win.weekAct(18, '39-40', oneScrin);
								break;
							case week>=41 && week<43:
								col_num = win.weekAct(19, '41-42', oneScrin);
								break;
							case week>=43 && week<45:
								col_num = win.weekAct(20, '43-44', oneScrin);
								break;
						}

						win.setFieldValue('date'+col_num, oneScrin.PregnancyScreen_setDate);
						win.setFieldValue('FetusHeartRate'+col_num, oneScrin.FetusHeartRate);
						win.setFieldValue('abdominometry'+col_num, oneScrin.abdominometry);
						win.setFieldValue('HemoglobinLevel'+col_num, oneScrin.HemoglobinLevel);
						win.setFieldValue('BloodSugarLevel'+col_num, oneScrin.BloodSugarLevel);
						win.setFieldValue('Proteinuria'+col_num, oneScrin.Proteinuria);

						base_form.findField('oedema'+col_num).setValue(oneScrin.oedema == 1 ? "Нет":"Да");
						base_form.findField('avo'+col_num).setValue(oneScrin.avo == 1 ? "Нет":"Да");
						switch (oneScrin.childpositiontype) {
							case "1":
								base_form.findField('childpositiontype'+col_num).setValue("головное");
								break;
							case "2":
								base_form.findField('childpositiontype'+col_num).setValue("ягодичное");
								break;
							case "3":
								base_form.findField('childpositiontype'+col_num).setValue("ножное");
								break;
							default:
								base_form.findField('childpositiontype'+col_num).setValue("");
								break;
						}
						switch (oneScrin.RhSensitization) {
							case "5":
								base_form.findField('TitrRhClarification'+col_num).setValue("1:2");
								break;
							case "6":
								base_form.findField('TitrRhClarification'+col_num).setValue("1:4");
								break;
							case "2":
								base_form.findField('TitrRhClarification'+col_num).setValue("1:8");
								break;
							case "3":
								base_form.findField('TitrRhClarification'+col_num).setValue("1:16");
								break;
							case "4":
								switch (oneScrin.TitrRhClarification) {
									case "1":
										base_form.findField('TitrRhClarification'+col_num).setValue("1:32");
										break;
									case "2":
										base_form.findField('TitrRhClarification'+col_num).setValue("1:64");
										break;
									case "3":
										base_form.findField('TitrRhClarification'+col_num).setValue("1:128");
										break;
									case "4":
										base_form.findField('TitrRhClarification'+col_num).setValue(">1:128");
										break;
									default:
										base_form.findField('TitrRhClarification'+col_num).setValue("");
										break;
								}
								break;
							default:
								base_form.findField('TitrRhClarification'+col_num).setValue("");
								break;
						}
						switch (oneScrin.TitrABOClarification) {
							case "1":
								base_form.findField('TitrABOClarification'+col_num).setValue("1:2");
								break;
							case "2":
								base_form.findField('TitrABOClarification'+col_num).setValue("1:4");
								break;
							case "3":
								base_form.findField('TitrABOClarification'+col_num).setValue("1:8");
								break;
							case "4":
								base_form.findField('TitrABOClarification'+col_num).setValue("1:16");
								break;
							case "5":
								base_form.findField('TitrABOClarification'+col_num).setValue("1:32");
								break;
							case "6":
								base_form.findField('TitrABOClarification'+col_num).setValue("1:64");
								break;
							case "7":
								base_form.findField('TitrABOClarification'+col_num).setValue("1:128");
								break;
							case "8":
								base_form.findField('TitrABOClarification'+col_num).setValue(">1:128");
								break;
							default:
								base_form.findField('TitrABOClarification'+col_num).setValue("");
								break;
						}
					});
				}
				else {
					callbackFn();
					Ext6.Msg.alert(langs('Ошибка'), langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'));
				}
			}.createDelegate(this)
		});
		return true;
	},
	show: function(data) {
		var win = this;
		var base_form = win.MainPanel.getForm();
		base_form.setValues(win.clearValues);

		win.loadAllData(data.PersonRegister_id);
		win.callParent(arguments);
	},
	initComponent: function() {
		var win = this;

		win.MainPanel = new Ext6.form.FormPanel({
			width: 1330,
			height: 650,
			autoScroll: true,
			defaults: {
				bodyStyle: 'padding:10px; white-space:ncwrap;',
				border: true,
				width: 120
			},
			layout: {
				type: 'table',
				columns: 21
			},
			items: [{
				html: 'Дата'
			},{
				xtype: 'textfield',
				readOnly:true,
				name: 'date1'
			},{
				xtype: 'textfield',
				readOnly:true,
				name: 'date2'
			},{
				xtype: 'textfield',
				readOnly:true,
				name: 'date3'
			},{
				xtype: 'textfield',
				readOnly:true,
				name: 'date4'
			},{
				xtype: 'textfield',
				readOnly:true,
				name: 'date5'
			},{
				xtype: 'textfield',
				readOnly:true,
				name: 'date6'
			},{
				xtype: 'textfield',
				readOnly:true,
				name: 'date7'
			},{
				xtype: 'textfield',
				readOnly:true,
				name: 'date8'
			},{
				xtype: 'textfield',
				readOnly:true,
				name: 'date9'
			},{
				xtype: 'textfield',
				readOnly:true,
				name: 'date10'
			},{
				xtype: 'textfield',
				readOnly:true,
				name: 'date11'
			},{
				xtype: 'textfield',
				readOnly:true,
				name: 'date12'
			},{
				xtype: 'textfield',
				readOnly:true,
				name: 'date13'
			},{
				xtype: 'textfield',
				readOnly:true,
				name: 'date14'
			},{
				xtype: 'textfield',
				readOnly:true,
				name: 'date15'
			},{
				xtype: 'textfield',
				readOnly:true,
				name: 'date16'
			},{
				xtype: 'textfield',
				readOnly:true,
				name: 'date17'
			},{
				xtype: 'textfield',
				readOnly:true,
				name: 'date18'
			},{
				xtype: 'textfield',
				readOnly:true,
				name: 'date19'
			},{
				xtype: 'textfield',
				readOnly:true,
				name: 'date20'
			},{
				html: 'Недели'
			},{html: '5-6'},{html: '7-8'},{html: '9-10'},{html: '11-12'},{html: '13-14'},{html: '15-16'},{html: '17-18'},{html: '19-20'},
				{html: '21-22'},{html: '23-24'},{html: '25-26'},{html: '27-28'},{html: '29-30'},{html: '31-32'},{html: '33-34'},{html: '35-36'},
				{html: '37-38'},{html: '39-40'},{html: '41-42'},{html: '43-44'},{
					html: 'Гемоглобин'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'HemoglobinLevel1'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'HemoglobinLevel2'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'HemoglobinLevel3'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'HemoglobinLevel4'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'HemoglobinLevel5'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'HemoglobinLevel6'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'HemoglobinLevel7'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'HemoglobinLevel8'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'HemoglobinLevel9'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'HemoglobinLevel10'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'HemoglobinLevel11'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'HemoglobinLevel12'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'HemoglobinLevel13'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'HemoglobinLevel14'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'HemoglobinLevel15'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'HemoglobinLevel16'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'HemoglobinLevel17'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'HemoglobinLevel18'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'HemoglobinLevel19'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'HemoglobinLevel20'
				},{
					html: 'Белок мочи'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'Proteinuria1'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'Proteinuria2'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'Proteinuria3'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'Proteinuria4'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'Proteinuria5'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'Proteinuria6'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'Proteinuria7'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'Proteinuria8'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'Proteinuria9'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'Proteinuria10'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'Proteinuria11'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'Proteinuria12'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'Proteinuria13'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'Proteinuria14'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'Proteinuria15'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'Proteinuria16'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'Proteinuria17'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'Proteinuria18'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'Proteinuria19'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'Proteinuria20'
				},{
					html: 'Сахар крови'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'BloodSugarLevel1'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'BloodSugarLevel2'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'BloodSugarLevel3'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'BloodSugarLevel4'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'BloodSugarLevel5'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'BloodSugarLevel6'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'BloodSugarLevel7'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'BloodSugarLevel8'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'BloodSugarLevel9'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'BloodSugarLevel10'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'BloodSugarLevel11'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'BloodSugarLevel12'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'BloodSugarLevel13'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'BloodSugarLevel14'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'BloodSugarLevel15'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'BloodSugarLevel16'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'BloodSugarLevel17'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'BloodSugarLevel18'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'BloodSugarLevel19'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'BloodSugarLevel20'
				},{
					html: 'Титр Rh'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrRhClarification1'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrRhClarification2'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrRhClarification3'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrRhClarification4'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrRhClarification5'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrRhClarification6'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrRhClarification7'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrRhClarification8'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrRhClarification9'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrRhClarification10'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrRhClarification11'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrRhClarification12'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrRhClarification13'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrRhClarification14'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrRhClarification15'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrRhClarification16'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrRhClarification17'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrRhClarification18'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrRhClarification19'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrRhClarification20'
				},{
					html: 'Титр АВО'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrABOClarification1'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrABOClarification2'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrABOClarification3'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrABOClarification4'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrABOClarification5'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrABOClarification6'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrABOClarification7'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrABOClarification8'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrABOClarification9'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrABOClarification10'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrABOClarification11'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrABOClarification12'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrABOClarification13'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrABOClarification14'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrABOClarification15'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrABOClarification16'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrABOClarification17'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrABOClarification18'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrABOClarification19'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'TitrABOClarification20'
				},{
					html: 'ABO-сенсибилизация'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'avo1'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'avo2'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'avo3'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'avo4'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'avo5'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'avo6'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'avo7'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'avo8'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'avo9'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'avo10'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'avo11'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'avo12'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'avo13'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'avo14'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'avo15'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'avo16'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'avo17'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'avo18'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'avo19'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'avo20'
				},{
					html: 'Артериальное давление'
				},{
					xtype: 'chart',
					colspan: 20,
					store: win.storeBP,
					height: 300,
					width: 1100,
					legend: {
						position: 'right'
					},
					axes: [{
						type: 'numeric',
						position: 'left',
						fields: ['SystolicBP', 'DiastolicBP']
					},{
						title: 'Недели',
						highlight: {
							size: 7,
							radius: 7
						},
						type: 'category',
						position: 'bottom',
						fields: ['name']
					}],
					series: [{
						type: 'line',
						title: 'Систолическое',
						xField: 'name',
						yField: 'SystolicBP'
					},{
						type: 'line',
						title: 'Диастолическое',
						xField: 'name',
						yField: 'DiastolicBP'
					}]
				},{
					html: 'Окружность живота'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'abdominometry1'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'abdominometry2'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'abdominometry3'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'abdominometry4'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'abdominometry5'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'abdominometry6'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'abdominometry7'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'abdominometry8'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'abdominometry9'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'abdominometry10'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'abdominometry11'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'abdominometry12'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'abdominometry13'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'abdominometry14'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'abdominometry15'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'abdominometry16'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'abdominometry17'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'abdominometry18'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'abdominometry19'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'abdominometry20'
				},{
					html: 'Высота стояния дна матки'
				},{
					xtype: 'chart',
					colspan: 20,
					store: win.storeVDM,
					height: 300,
					width: 1100,
					legend: {
						position: 'right'
					},
					axes: [{
						type: 'numeric',
						position: 'left',
						fields: ['vdm']
					},{
						title: 'Недели',
						highlight: {
							size: 7,
							radius: 7
						},
						type: 'category',
						position: 'bottom',
						fields: ['name']
					}],
					series: [{
						type: 'line',
						title: 'Высота',
						xField: 'name',
						yField: 'vdm'
					}]
				},{
					html: 'Предлежание плода'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'childpositiontype1'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'childpositiontype2'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'childpositiontype3'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'childpositiontype4'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'childpositiontype5'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'childpositiontype6'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'childpositiontype7'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'childpositiontype8'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'childpositiontype9'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'childpositiontype10'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'childpositiontype11'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'childpositiontype12'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'childpositiontype13'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'childpositiontype14'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'childpositiontype15'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'childpositiontype16'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'childpositiontype17'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'childpositiontype18'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'childpositiontype19'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'childpositiontype20'
				},{
					html: 'Сердцебиение'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'FetusHeartRate1'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'FetusHeartRate2'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'FetusHeartRate3'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'FetusHeartRate4'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'FetusHeartRate5'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'FetusHeartRate6'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'FetusHeartRate7'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'FetusHeartRate8'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'FetusHeartRate9'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'FetusHeartRate10'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'FetusHeartRate11'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'FetusHeartRate12'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'FetusHeartRate13'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'FetusHeartRate14'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'FetusHeartRate15'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'FetusHeartRate16'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'FetusHeartRate17'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'FetusHeartRate18'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'FetusHeartRate19'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'FetusHeartRate20'
				},{
					html: 'Отеки'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'oedema1'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'oedema2'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'oedema3'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'oedema4'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'oedema5'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'oedema6'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'oedema7'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'oedema8'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'oedema9'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'oedema10'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'oedema11'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'oedema12'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'oedema13'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'oedema14'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'oedema15'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'oedema16'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'oedema17'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'oedema18'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'oedema19'
				},{
					xtype: 'textfield',
					readOnly:true,
					name: 'oedema20'
				}]
		});
		win.clearValues = win.MainPanel.getValues();

		Ext6.apply(win, {
			items: [
				win.MainPanel
			],
			buttons: ['->',
				{
					text: langs('Закрыть'),
					itemId: 'button_cancel',
					userCls:'buttonPoupup buttonCancel',
					handler:function () {
						win.hide();
					}
				}
			]
		});

		this.callParent(arguments);
	}
});