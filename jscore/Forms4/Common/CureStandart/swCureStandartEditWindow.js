/**
 * swCureStandartEditWindow - Клиническая рекомендация
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 */

Ext6.define('common.CureStandart.swCureStandartEditWindow', {
	/* свойства */
	alias: 'widget.swCureStandartEditWindow',
	addCodeRefresh: Ext.emptyFn,
	autoScroll: true,
    autoShow: false,
	closable: true,
	closeToolText: 'Закрыть',
	cls: 'arm-window-new',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
	maximized: true,
	refId: 'swcurestandart',
	renderTo: main_center_panel.body.dom,
	layout: 'vbox',
	resizable: true,
	title: 'Клиническая рекомендация',
	header: getGlobalOptions().client == 'ext2', //если ext6 - заголовок не нужен
	width: 1000,
	conditions: [],
	doSave: function() {
		var win = this;
		var base_form = win.InfoPanel.getForm();

		if ( !base_form.isValid() ) {
			Ext6.MessageBox.show({
				title: 'Проверка данных формы',
				msg: 'Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.',
				buttons: Ext6.Msg.OK,
				icon: Ext6.Msg.WARNING
			});
			return;
		}

		var Name = base_form.findField('Name').getValue();
		var Phase_id = base_form.findField('Phase_id').getValue();
		var Stage_id = base_form.findField('Stage_id').getValue();
		var Complication_id = base_form.findField('Complication_id').getValue();
		var DurationT = base_form.findField('Duration').getValue();
		var Description = base_form.findField('Description').getValue();

		var adult = base_form.findField('AgeAdult').getValue();
		var child = base_form.findField('AgeChild').getValue();
		var age = 0;
		if(adult && child) age = 3;
		else if(adult) age=1;
		else if(child) age=2;

		var diags = base_form.findField('diagtags').getValue();

		var conditions_list = base_form.findField('Conditions').getValue().cond;
		if(! conditions_list) {
			return Ext6.MessageBox.alert('Проверка данных формы', 'Нужно выбрать хотя бы одно условие оказания.');
			if(!conditions_list.length) conditions_list=[conditions_list];
		}
		var diagnosis =  win.DiagGrid.getStore().data.items;
		win.Diagnosis = [];
		for(i=0; i<diagnosis.length; i++) {
			win.Diagnosis.push(diagnosis[i].data);
		}
		var Treatment =  win.CureGrid.getStore().data.items;
		win.Treatment = [];
		for(i=0; i<Treatment.length; i++) {
			win.Treatment.push(Treatment[i].data);
		}
		var TreatmentDrug =  win.TreatmentDrugGrid.getStore().data.items;
		win.TreatmentDrug = [];
		for(i=0; i<TreatmentDrug.length; i++) {
			win.TreatmentDrug.push(TreatmentDrug[i].data);
		}
		var NutrMixture =  win.NutrMixtureGrid.getStore().data.items;
		win.NutrMixture = [];
		for(i=0; i<NutrMixture.length; i++) {
			win.NutrMixture.push(NutrMixture[i].data);
		}
		var Implant =  win.ImplantGrid.getStore().data.items;
		win.Implant = [];
		for(i=0; i<Implant.length; i++) {
			win.Implant.push(Implant[i].data);
		}
		var PresBlood =  win.PresBloodGrid.getStore().data.items;
		win.PresBlood = [];
		for(i=0; i<PresBlood.length; i++) {
			win.PresBlood.push(PresBlood[i].data);
		}

		win.saveMask.show();
		Ext.Ajax.request({
			method: 'POST',
			url: '/?c=CureStandart&m=save',
			params: {
				action: win.action,
				id: win.CureStandart_id,
				Name: Name,
				Diags: Ext.util.JSON.encode(diags),
				Phase_id: Phase_id,
				Stage_id: Stage_id,
				Complication_id: Complication_id,
				Duration: DurationT,
				Age_id: age,
				Description: Description,
				Conditions: Ext.util.JSON.encode(conditions_list),
				Diagnostika: Ext.util.JSON.encode(win.Diagnosis),
				Treatment: Ext.util.JSON.encode(win.Treatment),
				TreatmentDrug: Ext.util.JSON.encode(win.TreatmentDrug),
				NutrMixture: Ext.util.JSON.encode(win.NutrMixture),
				Implant: Ext.util.JSON.encode(win.Implant),
				PresBlood: Ext.util.JSON.encode(win.PresBlood)
			},
			success: function(response, opts) {
				var res = JSON.parse(response.responseText);
				var data = new Object();
				data.diagcodes='';
				if (res.Error_Msg) {
					Ext6.MessageBox.alert('Ошибка', res.Error_Msg);
					win.saveMask.hide();
					return;
				} else {
					//Ext6.MessageBox.alert('Сохранено', 'Клиническая рекомендация сохранена.');
					if(res.diagcodes) {
						data.diagcodes = res.diagcodes;
						data.name = Name;
					}
				}
				win.saveMask.hide();
				win.hide();
				if(data.diagcodes) {
					win.callback(data);
				}
			},
			failure: function() {
				Ext6.MessageBox.alert('Ошибка', 'При сохранении данных произошла ошибка. Обратитесь к администратору');
				win.saveMask.hide();
			}
		});
		return true;
	},
	loadCureStandart: function() {
		var win = this;
		var base_form = win.InfoPanel.getForm();
		Ext.Ajax.request({
			params: {
				id: win.CureStandart_id
			},
			callback: function(options, success, response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj.success) {
					win.response = response_obj;
					var info = response_obj.data.Info[0];
					if(info) {
						base_form.findField('Name').setValue(info.CureStandart_Name);
						base_form.findField('Phase_id').setValue(info.Phase_id);
						base_form.findField('Stage_id').setValue(info.Stage_id);
						base_form.findField('Complication_id').setValue(info.Complication_id);
						base_form.findField('Duration').setValue(info.DurationT);
						switch(info.Age_id) {
							case '3':
								base_form.findField('AgeAdult').setValue(true);
								base_form.findField('AgeChild').setValue(true);
								break;
							case '1':
								base_form.findField('AgeAdult').setValue(true);
								base_form.findField('AgeChild').setValue(false);
								break;
							case '2':
								base_form.findField('AgeAdult').setValue(false);
								base_form.findField('AgeChild').setValue(true);
								break;
						}
						base_form.findField('Description').setValue(info.Description);
						//заполняем условия оказания
						win.conditions = response_obj.data.Conditions;
						cond=[];
						for(i=0;i<win.conditions.length;i++) {
							cond.push(win.conditions[i].Cond_id);
						}
						var checklist = win.InfoPanel.getForm().findField('Conditions');
						checklist.setValue({'cond':cond});
						//заполняем диагнозы
						var diags = response_obj.data.Diags;
						win.diags = diags;
						diags_id = [];
						diags_store = [];
						for(i=0;i<diags.length;i++) {
							diags_id.push(diags[i].id);
							diags_store.push({Diag_id: diags[i].id , Diag_Code: diags[i].code, Diag_Name: '' });
						}
						win.queryById('codemkb').getStore().add(diags_store);
						base_form.findField('diagtags').setValue(diags_id);
						//заполняем диагностику
						win.Diagnosis = response_obj.data.Diagnosis;
						if(win.Diagnosis) {
							var diagnostika = {};
							var diagnostik_id = [];
							for (i = 0; i < win.Diagnosis.length; i++) {
								if(!diagnostika[win.Diagnosis[i].id]) {
									diagnostika[win.Diagnosis[i].id] = {data: win.Diagnosis[i], attr: [win.Diagnosis[i].typecode]};
									diagnostik_id.push(win.Diagnosis[i].id);
								} else {
									diagnostika[win.Diagnosis[i].id].attr.push(win.Diagnosis[i].typecode);
								}
							}
							for (i = 0; i < diagnostik_id.length; i++) {
								win.DiagGrid.getStore().add({
									name: diagnostika[diagnostik_id[i]].data.code + '  ' + diagnostika[diagnostik_id[i]].data.name,
									freq: diagnostika[diagnostik_id[i]].data.freq,
									avenum: diagnostika[diagnostik_id[i]].data.avenum,
									servicetype: win.getUslugaTypeForDiag(diagnostika[diagnostik_id[i]].attr, diagnostika[diagnostik_id[i]].data.code),
									Usluga_Name: diagnostika[diagnostik_id[i]].name,
									Usluga_Code: diagnostika[diagnostik_id[i]].data.code ? diagnostika[diagnostik_id[i]].data.code : '',
									id: diagnostika[diagnostik_id[i]].data.id
								});
							}
							if(diagnostik_id.length>0)
								win.DiagPanel.expand();
						}
						//заполняем лечение
						win.Treatment = response_obj.data.Treatment;
						if(win.Treatment) {
							var treatm = {};
							var treatm_id = [];

							for (i = 0; i < win.Treatment.length; i++) {
								if(!treatm[win.Treatment[i].id]) {
									treatm[win.Treatment[i].id] = {data: win.Treatment[i], attr: [win.Treatment[i].typecode]};
									treatm_id.push(win.Treatment[i].id);
								} else {
									treatm[win.Treatment[i].id].attr.push(win.Treatment[i].typecode);
								}
							}
							for (j = 0; j < treatm_id.length; j++) {
								i = treatm_id[j];
								win.CureGrid.getStore().add({
									id: treatm[i].data.id,
									name: treatm[i].data.code + '  ' + treatm[i].data.name,
									freq: treatm[i].data.freq,
									avenum: treatm[i].data.avenum,
									servicetype: win.getUslugaTypeForCure(treatm[i].attr, treatm[i].data.code),
									Usluga_Name: treatm[i].data.name,
									Usluga_Code: treatm[i].data.code ? treatm[i].data.code : ''
								});
							}
							if(treatm_id.length>0)
								win.TreatmentPanel.expand();
						}
						//заполняем лекарственное лечение
						win.TreatmentDrug = response_obj.data.TreatmentDrug;
						if(win.TreatmentDrug) {
							for(i=0;i<win.TreatmentDrug.length;i++) {
								win.TreatmentDrugGrid.getStore().add({
									id: win.TreatmentDrug[i].id,
									name: win.TreatmentDrug[i].name,
									freq: win.TreatmentDrug[i].freq,
									ODD: win.TreatmentDrug[i].ODD,
									ODD_ed: win.TreatmentDrug[i].ODD_ed,
									EKD: win.TreatmentDrug[i].EKD,
									EKD_ed: win.TreatmentDrug[i].EKD_ed
								});
							}
							if(win.TreatmentDrug.length>0)
								win.TreatmentDrugPanel.expand();
						}

						//заполняем питательные смеси
						win.NutrMixture = response_obj.data.NutrMixture;
						if(win.NutrMixture) {
							for(i=0;i<win.NutrMixture.length;i++) {
								win.NutrMixtureGrid.getStore().add({
									id: win.NutrMixture[i].id,
									name: win.NutrMixture[i].name,
									freq: win.NutrMixture[i].freq,
									ODD: win.NutrMixture[i].ODD,
									ODD_ed: win.NutrMixture[i].ODD_ed,
									avenum: win.NutrMixture[i].avenum,
									EKD: win.NutrMixture[i].EKD,
									EKD_ed: win.NutrMixture[i].EKD_ed,
									avenum_ed: win.NutrMixture[i].avenum_ed
								});
							}
							if(win.NutrMixture.length)
								win.NutrMixturePanel.expand();
						}

						//заполняем импланты
						win.Implant = response_obj.data.Implant;
						if(win.Implant) {
							for(i=0;i<win.Implant.length;i++) {
								win.ImplantGrid.getStore().add({
									id: win.Implant[i].id,
									name: win.Implant[i].name,
									freq: win.Implant[i].freq,
									avenum: win.Implant[i].avenum
								});
								if(win.Implant.length>0)
									win.ImplantPanel.expand();
							}
						}

						//заполняем консервированную кровь
						win.PresBlood = response_obj.data.PresBlood;
						if(win.PresBlood) {
							for(i=0;i<win.PresBlood.length;i++) {
								win.PresBloodGrid.getStore().add({
									id: win.PresBlood[i].id,
									name: win.PresBlood[i].name,
									freq: win.PresBlood[i].freq,
									avenum: win.PresBlood[i].avenum,
									ODD: win.PresBlood[i].ODD,
									EKD: win.PresBlood[i].EKD,
									ed: win.PresBlood[i].ed
								});
							}
							if(win.PresBlood.length>0)
								win.PresBloodPanel.expand();
						}

						win.loadMask.hide();
					}
				}
			},
			url: '/?c=CureStandart&m=load'
		});
	},
	loadConditions: function() {
		var win = this;

		var checklist = win.InfoPanel.getForm().findField('Conditions');
		//~ checklist.removeAll();
		win.checklistStore.load({
			callback: function(records, operation, success) {

				//~ win.checklistStore.each(function(record) {
					//~ checklist.add({
						//~ boxLabel: record.get('CureStandartConditionsType_Name'),
						//~ inputValue: record.get('CureStandartConditionsType_Code'),
						//~ name: 'cond'
					//~ });
				//~ });
				if (win.action.inlist(['view', 'edit', 'copy'])) {
					win.loadCureStandart();
				} else win.loadMask.hide();
			},
			scope: this
		});
	},
	getUslugaTypeForDiag: function(attr, code) {
		var type = 0; //прочее

		if( attr.in_array(9) ) {
			type = 3; //функц.диагностика
		} else if( attr.in_array(8) ) {
			type = 2; //лаб.диагностика
		} else if( code.slice(0,1)=='B' && !attr.in_array(16) ) {
			type = 1; //осмотры
		}
		return type;
	},
	getUslugaTypeForCure: function(attr, code) {
		var type = 0;

		if(
				(
					(attr.in_array(9) && attr.in_array(8)) ||
					(attr.in_array(9) && attr.in_array(16))
				) && !attr.in_array(4)
			) {
			return 3; //функциональная диагностика
		}
		if( attr.in_array(8) ) {
			type = 2; //лаб.диагностика
		}
		if( code.slice(0,1)=='B' && !attr.in_array(16) && !attr.in_array(9) ) {
			type = 1; //осмотры
		}
		if( attr.in_array(4) ) {
			return 4; //хирургические методы
		}
		if( (attr.in_array(5) || (code.slice(0,3)>="A17" && code.slice(0,3)<="A25"))
			&& !attr.in_array(4) && !attr.in_array(9)
		)
			type = 5; //немедикаментозные методы
		if( attr.in_array(16) )
			type = 6; //процедуры и манипуляции
		return type;
	},
	show: function() {
		var win = this;
		win.callParent(arguments);
		if(arguments[0].action)
			win.action = arguments[0].action;
		else {
			win.hide();
			return false;
		}
		if(arguments[0].callback)
			win.callback = arguments[0].callback;

		if(arguments[0].id)
			win.CureStandart_id = arguments[0].id;

		win.DiagPanel.collapse();
		win.TreatmentPanel.collapse();
		win.TreatmentDrugPanel.collapse();
		win.NutrMixturePanel.collapse();
		win.ImplantPanel.collapse();
		win.PresBloodPanel.collapse();

		win.InfoPanel.reset();
		win.DiagGrid.getStore().removeAll();
		win.CureGrid.getStore().removeAll();
		win.TreatmentDrugGrid.getStore().removeAll();
		win.NutrMixtureGrid.getStore().removeAll();
		win.ImplantGrid.getStore().removeAll();
		win.PresBloodGrid.getStore().removeAll();

		switch(win.action) {
			case 'add':
				this.setTitle('Стандарты лечения: Добавление');
				this.queryById('nameStandart').st = 1;
				break;
			case 'view':
				this.setTitle('Стандарты лечения: Просмотр');
				this.queryById('nameStandart').st = 0;
				break;
			case 'edit':
			case 'copy':
				this.setTitle('Стандарты лечения: Редактирование');
				this.queryById('nameStandart').st = 0;
				break;
		}

		if (win.action == 'view') {
			win.DiagGridToolbar.items.items[0].disable();
			win.CureGridToolbar.items.items[0].disable();
			win.TreatmentDrugGridToolbar.items.items[0].disable();
			win.NutrMixtureGridToolbar.items.items[0].disable();
			win.ImplantGridToolbar.items.items[0].disable();
			win.PresBloodGridToolbar.items.items[0].disable();
			win.saveButton.disable();
			win.InfoPanel.enableEdit(false);
		} else {
			win.DiagGridToolbar.items.items[0].enable();
			win.CureGridToolbar.items.items[0].enable();
			win.TreatmentDrugGridToolbar.items.items[0].enable();
			win.NutrMixtureGridToolbar.items.items[0].enable();
			win.ImplantGridToolbar.items.items[0].enable();
			win.PresBloodGridToolbar.items.items[0].enable();
			win.saveButton.enable();
			win.InfoPanel.enableEdit(true);
		}

		win.queryById('codemkb').getStore().getProxy().setExtraParam('diags', '' );
		win.queryById('codemkb').loaded = false;
		win.queryById('codemkb').getStore().load();
		win.loadMask.show();

		/*win.loadConditions(); //изначально подгружался справочник условий оказания
		if(win.action=='edit' || win.action=='copy') {
			win.loadCureStandart();
		} else win.loadMask.hide();*/
	},
	initComponent: function() {
		var win = this;

		win.loadMask = new Ext6.LoadMask(win, {msg: LOAD_WAIT});
		win.saveMask = new Ext6.LoadMask(win, {msg: LOAD_WAIT_SAVE});

		win.InfoPanel = new Ext6.form.FormPanel({
			autoScroll: true,
			collapsible: false,
			title: 'Модель пациента',
			border: false,
			layout: 'vbox',
			style: 'margin: 5px 5px 5px;',
			width: 980,
			viewModel: {},
			bodyPadding: 10,
			userCls: 'infopanel',
			items: [{
					name: 'Name',
					fieldLabel: 'Наименование',
					itemId: 'nameStandart',
					xtype: 'textfield',
					width: 970,
					allowBlank: false,
					def: false,
					defaultName: 'СТАНДАРТ МЕДИЦИНСКОЙ ПОМОЩИ БОЛЬНЫМ ',
					listeners: {
						'change': function( namefield, newValue, oldValue, eOpts ) {
							if(this.st==1) {
								this.st=2;
							} else if(this.st==2) {
								this.st=3;
								if(newValue==this.defaultName.slice(0,-1) || newValue==this.defaultName.slice(1)) this.setValue('');
								this.setUserCls('');
							}
						},
						'focus': function() {
							if(this.st==1) {
								this.setUserCls('csNameDefault');
								this.setValue(this.defaultName);
								this.def = true;
								var input_el = document.getElementById(this.getInputId());
								if(input_el) {
									var n = this.getValue().length;
									setTimeout(function() {
										input_el.selectionStart = n;
										input_el.selectionEnd = n;
									}, 100);
								}
								this.st=2;
							}
						}
					}
				}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'vbox',
						items: [{
							xtype: 'tagfield',
							filterPickList: true,
							minChars: 0,
							name: 'diagtags',
							fieldLabel: 'Код по МКБ-10',
							itemId: 'codemkb',
							allowBlank: false,
							loaded: false,
							store: new Ext6.create('Ext6.data.Store', {
								listeners: {
									'load': function() {
										var _this = win.queryById('codemkb');
										if(!_this.loaded) {
											if (win.action.inlist(['view', 'edit', 'copy'])) {
												win.loadCureStandart();
											} else
												win.loadMask.hide();
											_this.loaded = true;
										}
									}
								},
								fields: [
									{ name: 'Diag_id', mapping: 'Diag_id' },
									{ name: 'Diag_Code', mapping: 'Diag_Code', type: 'string'},
									{ name: 'Diag_Name', mapping: 'Diag_Name', type: 'string'}
								],
								autoLoad: false,
								sorters: {
									property: 'Diag_Code',
									direction: 'ASC'
								},
								proxy: {
									type: 'ajax',
									actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
									url : '/?c=CureStandart&m=loadDiagList',
									reader: {
										type: 'json',
										rootProperty: 'data'
									}
								},
								extraParams: {
									diags: ''
								},
								mode: 'remote'
							}),
							queryMode: 'remote',
							countSymbolsCode: (getRegionNick().inlist([ 'msk', 'pskov', 'ufa', 'vologda' ]) ? 6 : 5),
							tpl: new Ext6.XTemplate(
								'<tpl for="."><div class="x6-boundlist-item">',
								'<table style="border: 0;">',
								'<tr><td style="width: 45px;"><font color="red">{Diag_Code}&nbsp;</font></td>',
								'<td style="width: 200px;">{Diag_Name}&nbsp;</td>',
								'</tr></table>',
								'</div></tpl>'
							),
							getDiagCode: function(code) {
								// получаем количество возможных символов
								q = code.slice(0, this.countSymbolsCode);
								// если в этом полученном количестве есть пробел, то обрезаем по пробел
								q = (q)?q.split(' ')[0]:'';
								// если там есть русские символы, то делаем их нерусскимми (код же в английской транскрипции)
								q = LetterChange(q.charAt(0)) + q.slice(1, q.length);
								// если нет точки в коде, и код больше трех символов, то добавляем точку
								if (q.charAt(3) != '.' && q.length > 3)	{
									q = q.slice(0, 3) + '.' + q.slice(3, this.countSymbolsCode-1);
								}
								// все пробелы заменяем на пусто // upd: после строки q = (q)?q.split(' ')[0]:''; уже не имеет актуальности
								// q = q.replace(' ', '');
								return q;
							},
							value: [],
							displayField: 'Diag_Code',
							valueField: 'Diag_id',
							width: 500,
							fieldCls: 'tags_mkb10',
							triggers: {
								openMkb10: {
									cls: Ext6.baseCSSPrefix + 'form-search-trigger',
									weight: -1,
									handler: function() {
										getWnd('DiagSearchTreeWindow').show({
											onSelect: function(diagData) {
												var diags = win.InfoPanel.getForm().findField('diagtags').getValue();

												win.queryById('codemkb').getStore().add([ {Diag_id: diagData.Diag_id , Diag_Code: diagData.Diag_Code, Diag_Name: diagData.Diag_Name } ]);

												diags.push(""+diagData.Diag_id);
												win.InfoPanel.getForm().findField('diagtags').setValue(diags);
												getWnd('DiagSearchTreeWindow').hide();
												return true;
											}
										});
									}
								}
							},
							listeners: {
								'beforeQuery': function( queryPlan ) {
									var q = this.getDiagCode( queryPlan.query );
									document.getElementById(this.id+'-inputEl').value=q;
									queryPlan.query = q;
									win.queryById('codemkb').getStore().getProxy().setExtraParam('diags', Ext.util.JSON.encode(this.getValue()) );
								},
								'keyup': function(field, e) {
									if ( e.ENTER == e.getKey() && this.getStore().getCount()==0 )
										this.getStore().add({Diag_id:0, Diag_Code:'',Diag_Name:''}); //иначе на следующем запросе пропадает автовыделение первого элемента выпадающего списка
								}
							}
						}, {
							fieldLabel: 'Фаза',
							name: 'Phase_id',
							width: 500,
							xtype: 'swCureStandartSpr',
							comboSubject: 'Phase',
							allowBlank: false
						}, {
							fieldLabel: 'Стадия',
							name: 'Stage_id',
							width: 500,
							xtype: 'swCureStandartSpr',
							comboSubject: 'Stage',
							allowBlank: false
						}, {
							fieldLabel: 'Осложнения',
							name: 'Complication_id',
							width: 500,
							xtype: 'swCureStandartSpr',
							comboSubject: 'Complication',
							allowBlank: false
						}]
					}, {
						border:false,
						layout: 'vbox',
						//columnWidth: 0.5,
						style: 'margin-left: 20px;',
						items: [{
							xtype: 'fieldcontainer',
							fieldLabel: 'Возрастная категория',
							labelWidth: 150,
							defaultType: 'checkboxfield',
							layout: 'column',
							items: [
								{
									boxLabel: 'Взрослые',
									name: 'AgeAdult',
									inputValue: '1',
									width: 100,
									checked: true
								}, {
									boxLabel: 'Дети',
									name: 'AgeChild',
									inputValue: '2',
									width: 100,
									checked: true
								}]
						}, {
							xtype: 'textareafield',
							name: 'Description',
							fieldLabel: 'Описание',
							labelWidth: 150,
							value: '',
							width:450,
							height:105
						}]

					}]
				}, {
					xtype: 'checkboxgroup',
					listConfig: {
						minWidth: 400,
						resizable: true
					},
					name: 'Conditions',
					columns: 2,
					vertical: true,
					fieldLabel: 'Условия оказания',
					items: [
						{
							boxLabel: 'амбулаторно-поликлиническая помощь',
							inputValue: 1,
							name: 'cond'
						}, {
							boxLabel: 'стационарная помощь',
							inputValue: 2,
							name: 'cond'
						}, {
							boxLabel: 'скорая медицинская помощь',
							inputValue: 4,
							name: 'cond'
						}, {
							boxLabel: 'дневной стационар',
							inputValue: 5,
							name: 'cond'
						}
					]
				}, {
					name : 'Duration',
					xtype: 'numberfield',
					fieldLabel: 'Продолжительность лечения',
					minValue: 0,
					width: 300,
					value: 10,
					allowBlank: false,
					labelWidth: 200
				}
				]
		});

		win.checklistStore = Ext6.create('Ext.data.Store', {
			fields: [
				{name: 'CureStandartConditionsType_id', type: 'int'},
				{name: 'CureStandartConditionsType_Code', type: 'int'},
				{name: 'CureStandartConditionsType_Name', type: 'string'}
			],
			proxy: {
				type: 'ajax',
				url: '/?c=CureStandart&m=loadConditions',
				reader: {
					type: 'json'
				}
			},
			autoLoad: false
		});

		// _________ Toolbars для панелей:

		win.DiagGridToolbar = new Ext6.Toolbar({
			xtype: 'toolbar',
			border: false,
			dock: 'top',
			items: [{
				xtype: 'button',
				text: langs('Добавить'),
				iconCls: 'icon-add',
				handler: function() {
					getWnd('swCureStandartServiceWindow').show({
						ARMType: this.ARMType,
						action: 'add',
						callback: function(data) {
							Ext.Ajax.request({
								method: 'POST',
								url: '/?c=CureStandart&m=loadUslugaComplex',
								params: {
									id: data.subject.id
								},
								success: function(response, opts) {
									var attr = [];
									if(response.responseText!='') {
										var res = JSON.parse(response.responseText);
										for(i=0;i<res.length;i++) {
											attr.push(res[i].Attr_id);
										}
									}

									win.DiagGrid.getStore().add({
										id: data.subject.id,
										name: data.subject.Code +'  '+ data.subject.Name,
										freq: data.freq,
										avenum: data.avenum,
										Usluga_Name: data.subject.Name,
										Usluga_Code: data.subject.Code,
										servicetype: win.getUslugaTypeForDiag(attr, data.subject.Code)
									});
								},
								failure: function() {
									Ext6.MessageBox.alert('Ошибка', 'При загрузке данных услуги произошла ошибка. Обратитесь к администратору');
								}
							});
						}
					});
				}
			}, {
				xtype: 'button',
				text: langs('Изменить'),
				disabled: true,
				iconCls: 'icon-edit', //'panicon-edit-pers-info',
				handler: function() {
					if(win.DiagGrid.selection)
						getWnd('swCureStandartServiceWindow').show({
							ARMType: this.ARMType,
							action: win.action != 'view' ? 'edit' : 'view',
							data: win.DiagGrid.selection.data,
							callback: function(data) {
								Ext.Ajax.request({
									method: 'POST',
									url: '/?c=CureStandart&m=loadUslugaComplex',
									params: {
										id: data.subject.id
									},
									success: function(response, opts) {
										var attr = [];
										if(response.responseText!='') {
											var res = JSON.parse(response.responseText);
											for(i=0;i<res.length;i++) {
												attr.push(res[i].Attr_id);
											}
										}

										var record = win.DiagGrid.getSelectionModel().getSelected().items[0];
										record.set('id', data.subject.id);
										record.set('name', data.subject.Code +'  '+ data.subject.Name);
										record.set('freq', data.freq);
										record.set('avenum', data.avenum);

										record.set('Usluga_Name', data.subject.Name);
										record.set('Usluga_Code', data.subject.Code);

										record.set('servicetype', win.getUslugaTypeForDiag(attr, data.subject.Code));

										record.commit();
									},
									failure: function() {
										Ext6.MessageBox.alert('Ошибка', 'При загрузке данных услуги произошла ошибка. Обратитесь к администратору');
									}
								});
							}
						});
				}
			}, {
				xtype: 'button',
				text: langs('Удалить'),
				disabled: true,
				iconCls: 'icon-delete', //'action_cancel',
				handler: function() {
					var selection = win.DiagGrid.getSelectionModel().getSelection();
					var record = selection[0];
					win.DiagGrid.getStore().remove(record);
				}
			}]
		});
		//Тулбар для "Лечение"
		win.CureGridToolbar = new Ext6.Toolbar({
			xtype: 'toolbar',
			dock: 'top',
			border: false,
			items: [{
				xtype: 'button',
				text: langs('Добавить'),
				name: 'btnadd',
				iconCls: 'icon-add',
				handler: function() {
					getWnd('swCureStandartServiceWindow').show({
						ARMType: this.ARMType,
						action: 'add',
						callback: function(data) {
							Ext.Ajax.request({
									method: 'POST',
									url: '/?c=CureStandart&m=loadUslugaComplex',
									params: {
										id: data.subject.id
									},
									success: function(response, opts) {
										var attr = [];
										if(response.responseText!='') {
											var res = JSON.parse(response.responseText);
											for(i=0;i<res.length;i++) {
												attr.push(res[i].Attr_id);
											}
										}

										win.CureGrid.getStore().add({
											id: data.subject.id,
											name: data.subject.Code +'  '+ data.subject.Name,
											freq: data.freq,
											avenum: data.avenum,
											Usluga_Name: data.subject.Name,
											Usluga_Code: data.subject.Code,
											servicetype: win.getUslugaTypeForCure(attr, data.subject.Code)
										});
									},
									failure: function() {
										Ext6.MessageBox.alert('Ошибка', 'При загрузке данных услуги произошла ошибка. Обратитесь к администратору');
									}
							});
						}
					});
				}
			}, {
				xtype: 'button',
				text: langs('Изменить'),
				disabled: true,
				iconCls: 'icon-edit', //'panicon-edit-pers-info',
				handler: function() {
					if(win.CureGrid.selection)
						getWnd('swCureStandartServiceWindow').show({
							ARMType: this.ARMType,
							action: win.action != 'view' ? 'edit' : 'view',
							data:  win.CureGrid.selection.data,
							callback: function(data) {
								Ext.Ajax.request({
									method: 'POST',
									url: '/?c=CureStandart&m=loadUslugaComplex',
									params: {
										id: data.subject.id
									},
									success: function(response, opts) {
										var attr = [];
										if(response.responseText!='') {
											var res = JSON.parse(response.responseText);
											for(i=0;i<res.length;i++) {
												attr.push(res[i].Attr_id);
											}
										}

										var record = win.CureGrid.getSelectionModel().getSelected().items[0];
										record.set('id', data.subject.id);
										record.set('name', data.subject.Code +'  '+ data.subject.Name);
										record.set('freq', data.freq);
										record.set('avenum', data.avenum);

										record.set('Usluga_Name', data.subject.Name);
										record.set('Usluga_Code', data.subject.Code);

										record.set('servicetype', win.getUslugaTypeForCure(attr, data.subject.Code));

										record.commit();
									},
									failure: function() {
										Ext6.MessageBox.alert('Ошибка', 'При загрузке данных услуги произошла ошибка. Обратитесь к администратору');
									}
								});


							}
						});
				}
			}, {
				xtype: 'button',
				text: langs('Удалить'),
				disabled: true,
				iconCls: 'icon-delete', //'action_cancel',
				handler: function() {
					var selection = win.CureGrid.getSelectionModel().getSelection();
					var record = selection[0];
					win.CureGrid.getStore().remove(record);
				}
			}]
		});
		//Тулбар для "Лекарственное лечение"
		win.TreatmentDrugGridToolbar = new Ext6.Toolbar({
			xtype: 'toolbar',
			dock: 'top',
			border: false,
			items: [{
				xtype: 'button',
				text: langs('Добавить'),
				iconCls: 'icon-add',
				handler: function() {
					getWnd('swCureStandartTreatmentDrugWindow').show({
						ARMType: this.ARMType,
						action: 'add',
						callback: function(data) {
							win.TreatmentDrugGrid.getStore().add({
								id: data.subject.id,
								name: data.subject.Name,
								freq: data.freq,
								ODD: data.ODD,
								ODD_ed: data.ODD_ed,
								EKD: data.EKD,
								EKD_ed: data.EKD_ed
							});
						}
					});
				}
			}, {
				xtype: 'button',
				text: langs('Изменить'),
				disabled: true,
				iconCls: 'icon-edit', //'panicon-edit-pers-info',
				handler: function() {
					if(win.TreatmentDrugGrid.selection)
						getWnd('swCureStandartTreatmentDrugWindow').show({
							ARMType: this.ARMType,
							action: win.action != 'view' ? 'edit' : 'view',
							data:  win.TreatmentDrugGrid.selection.data,
							callback: function(data) {
								var record = win.TreatmentDrugGrid.getSelectionModel().getSelected().items[0];
								record.set('id', data.subject.id);
								record.set('name', data.subject.Name);
								record.set('freq', data.freq);
								record.set('ODD', data.ODD);
								record.set('ODD_ed', data.ODD_ed);
								record.set('EKD', data.EKD);
								record.set('EKD_ed', data.EKD_ed);

								record.commit();
							}
						});
				}
			}, {
				xtype: 'button',
				text: langs('Удалить'),
				disabled: true,
				iconCls: 'icon-delete', //'action_cancel',
				handler: function() {
					var selection = win.TreatmentDrugGrid.getSelectionModel().getSelection();
					var record = selection[0];
					win.TreatmentDrugGrid.getStore().remove(record);
				}
			}]
		});
		//Тулбар для "Питательные смеси"
		win.NutrMixtureGridToolbar = new Ext6.Toolbar({
			xtype: 'toolbar',
			dock: 'top',
			border: false,
			items: [{
				xtype: 'button',
				text: langs('Добавить'),
				iconCls: 'icon-add',
				handler: function() {
					getWnd('swCureStandartNutrMixtureWindow').show({
						ARMType: this.ARMType,
						action: 'add',
						callback: function(data) {
								win.NutrMixtureGrid.getStore().add({
									id: data.subject.id,
									name: data.subject.Name,
									freq: data.freq,
									avenum: data.avenum,
									ODD: data.ODD,
									EKD: data.EKD,
									avenum_ed: data.avenum_ed,
									ODD_ed: data.ODD_ed,
									EKD_ed: data.EKD_ed,
								});
						}
					});
				}
			}, {
				xtype: 'button',
				text: langs('Изменить'),
				disabled: true,
				iconCls: 'icon-edit', //'panicon-edit-pers-info',
				handler: function() {
					if(win.NutrMixtureGrid.selection)
						getWnd('swCureStandartNutrMixtureWindow').show({
							ARMType: this.ARMType,
							action: win.action != 'view' ? 'edit' : 'view',
							data:  win.NutrMixtureGrid.selection.data,
							callback: function(data) {
								var record = win.NutrMixtureGrid.getSelectionModel().getSelected().items[0];
								record.set('id', data.subject.id);
								record.set('name', data.subject.Name);
								record.set('freq', data.freq);
								record.set('ODD', data.ODD);
								record.set('EKD', data.EKD);
								record.set('avenum', data.avenum);
								record.set('ODD_ed', data.ODD_ed);
								record.set('EKD_ed', data.EKD_ed);
								record.set('avenum_ed', data.avenum_ed);
								record.commit();
							}
						});
				}
			}, {
				xtype: 'button',
				text: langs('Удалить'),
				disabled: true,
				iconCls: 'icon-delete', //'action_cancel',
				handler: function() {
					var selection = win.MixtureGrid.getSelectionModel().getSelection();
					var record = selection[0];
					win.MixtureGrid.getStore().remove(record);
				}
			}]
		});
		//Тулбар для "Импланты"
		win.ImplantGridToolbar = new Ext6.Toolbar({
			xtype: 'toolbar',
			dock: 'top',
			border: false,
			items: [{
				xtype: 'button',
				text: langs('Добавить'),
				iconCls: 'icon-add',
				handler: function() {
					getWnd('swCureStandartImplantWindow').show({
						ARMType: this.ARMType,
						action: 'add',
						callback: function(data) {
								win.ImplantGrid.getStore().add({
									name: data.subject.Name,
									id: data.subject.id,
									freq: data.freq,
									avenum: data.avenum
								});
						}
					});
				}
			}, {
				xtype: 'button',
				text: langs('Изменить'),
				disabled: true,
				iconCls: 'icon-edit', //'panicon-edit-pers-info',
				handler: function() {
					getWnd('swCureStandartImplantWindow').show({
						ARMType: this.ARMType,
						action: win.action != 'view' ? 'edit' : 'view',
						callback: function(data) {
							var record = win.ImplantGrid.getSelectionModel().getSelected().items[0];
							record.set('id', data.subject.id);
							record.set('name', data.subject.Name);
							record.set('freq', data.freq);
							record.set('avenum', data.avenum);
							record.commit();
						}
					});
				}
			}, {
				xtype: 'button',
				text: langs('Удалить'),
				disabled: true,
				iconCls: 'icon-delete', //'action_cancel',
				handler: function() {
					var selection = win.ImplantGrid.getSelectionModel().getSelection();
					var record = selection[0];
					win.ImplantGrid.getStore().remove(record);
				}
			}]
		});
		win.PresBloodGridToolbar = new Ext6.Toolbar({
			xtype: 'toolbar',
			dock: 'top',
			border: false,
			items: [{
				xtype: 'button',
				text: langs('Добавить'),
				iconCls: 'icon-add',
				handler: function() {
					getWnd('swCureStandartPresBloodWindow').show({
						ARMType: this.ARMType,
						action: 'add',
						callback: function(data) {
								win.PresBloodGrid.getStore().add({
									name: data.subject.Name,
									id: data.subject.id,
									freq: data.freq,
									avenum: data.avenum,
									ODD: data.ODD,
									EKD: data.EKD,
									ed: data.ed
								});
						}
					});
				}
			}, {
				xtype: 'button',
				text: langs('Изменить'),
				disabled: true,
				iconCls: 'icon-edit', //'panicon-edit-pers-info',
				handler: function() {
					getWnd('swCureStandartPresBloodWindow').show({
						ARMType: this.ARMType,
						action: win.action != 'view' ? 'edit' : 'view',
						callback: function(data) {
							var record = win.PresBloodGrid.getSelectionModel().getSelected().items[0];
							record.set('id', data.subject.id);
							record.set('name', data.subject.Name);
							record.set('freq', data.freq);
							record.set('avenum', data.avenum);
							record.set('ODD', data.ODD);
							record.set('EKD', data.EKD);
							record.set('ed', data.ed);
							record.commit();
						}
					});
				}
			}, {
				xtype: 'button',
				text: langs('Удалить'),
				disabled: true,
				iconCls: 'icon-delete', //'action_cancel',
				handler: function() {
					var selection = win.PresBloodGrid.getSelectionModel().getSelection();
					var record = selection[0];
					win.PresBloodGrid.getStore().remove(record);
				}
			}]
		});
		//_________ Панели:
		var grid_padding_left = '24px';
		//панель диагностика
		win.DiagGrid = new Ext6.grid.Panel({
			xtype: 'grid',
			cls: 'EmkGrid',
			width: 1000,
			style: 'padding-left: '+grid_padding_left,
			border: false,
			dockedItems: win.DiagGridToolbar,
			store: {
				groupField: 'servicetype',
				fields: [
					{ name: 'name', type: 'string' },
					{ name: 'freq', type: 'float' },
					{ name: 'avenum', type: 'float' },
					{ name: 'servicetype', type: 'int' },
					{ name: 'Usluga_Name', type: 'string' },
					{ name: 'Usluga_Code', type: 'string' },
					{ name: 'id', type: 'int' }
				],
				data : []
			},
			columns: [
				{text: langs('Услуга'), flex: 1, minWidth: 200, dataIndex: 'name'},
				{text: langs('Частота'), minWidth: 200, dataIndex: 'freq'},
				{text: langs('Среднее количество'), minWidth: 200, dataIndex: 'avenum'},
				{text: langs('Тип услуги'), dataIndex: 'servicetype', hidden: true},
				{text: langs('Услуга'), dataIndex: 'Usluga_Name', hidden: true},
				{text: langs('Код услуги'), dataIndex: 'Usluga_Code', hidden: true},
				{text: langs('id'), dataIndex: 'id', hidden: true}
			],
			features: [{
				ftype: 'grouping',
				startCollapsed: false,
				groupHeaderTpl:  Ext6.create('Ext.XTemplate',
					'<div>{name:this.formatName}</div>',
					{
						formatName: function(name) {
							if(name==3) {
								return langs('Функциональная диагностика');
							} else if(name==2) {
								return langs('Лабораторная диагностика');
							} else if(name==1) {
								return langs('Осмотры');
							} else if(name==0)
								return langs('Прочее');
						}
					}
				)
			}],
			listeners: {
				'select': function () {
					win.DiagGridToolbar.items.items[1].enable();
					if (win.action != 'view') {
						win.DiagGridToolbar.items.items[2].enable();
					} else {
						win.DiagGridToolbar.items.items[2].disable();
					}
				},
				'deselect': function () {
					win.DiagGridToolbar.items.items[1].disable();
					win.DiagGridToolbar.items.items[2].disable();
				}
			}
		});

		//панель Лечение и контроль терапии
		win.CureGrid = new Ext6.grid.Panel({
			xtype: 'grid',
			cls: 'EmkGrid',
			width: 1000,
			style: 'padding-left: '+grid_padding_left,
			border: false,
			dockedItems: win.CureGridToolbar,
			store: {
				groupField: 'servicetype',
				fields: [
					{ name: 'id', type: 'int' },
					{ name: 'name', type: 'string' },
					{ name: 'freq', type: 'float' },
					{ name: 'avenum', type: 'float' },
					{ name: 'servicetype', type: 'int' },
					{ name: 'Usluga_Name', type: 'string' },
					{ name: 'Usluga_Code', type: 'string' }
				],
				data : []
			},
			columns: [
				{text: langs('Услуга'), flex: 1, minWidth: 200, dataIndex: 'name'},
				{text: langs('Частота'), minWidth: 200, dataIndex: 'freq'},
				{text: langs('Среднее количество'), minWidth: 200, dataIndex: 'avenum'},
				{text: langs('Тип услуги'), dataIndex: 'servicetype', hidden: true},
				{text: langs('Услуга'), dataIndex: 'Usluga_Name', hidden: true},
				{text: langs('Код услуги'), dataIndex: 'Usluga_Code', hidden: true},
				{text: langs('Индекс'), dataIndex: 'id', hidden: true}
			],
			features: [{
				ftype: 'grouping',
				startCollapsed: false,
				groupHeaderTpl:  Ext6.create('Ext.XTemplate',
					'<div>{name:this.formatName}</div>',
					{
						formatName: function(name) {
							if(name==4) {
								return langs('Хирургические методы');
							} else if(name==6) {
								return langs('Процедуры и манипуляции');
							} else if(name==2) {
								return langs('Лабораторная диагностика');
							} else if(name==5) {
								return langs('Немедикаментозные методы');
							} else if(name==1) {
								return langs('Осмотры');
							} else if(name==0)
								return langs('Прочее');
						}
					}
				)
			}],
			listeners: {
				'select': function () {
					win.CureGridToolbar.items.items[1].enable();
					if (win.action != 'view') {
						win.CureGridToolbar.items.items[2].enable();
					} else {
						win.CureGridToolbar.items.items[2].disable();
					}
				},
				'deselect': function () {
					win.CureGridToolbar.items.items[1].disable();
					win.CureGridToolbar.items.items[2].disable();
				}
			}
		});

		//панель Лекарственное лечение
		win.TreatmentDrugGrid = new Ext6.grid.Panel({
			xtype: 'grid',
			cls: 'EmkGrid',
			width: 1000,
			style: 'padding-left: '+grid_padding_left,
			border: false,
			dockedItems: win.TreatmentDrugGridToolbar,
			store: {
				fields: [
					{ name: 'name', type: 'string' },
					{ name: 'id', type: 'int' },
					{ name: 'freq', type: 'float' },
					{ name: 'ODD', type: 'int' },
					{ name: 'EKD', type: 'int' }
				],
				data : []
			},
			columns: [
				{text: langs('Наименование МНН'), flex: 1, minWidth: 200, dataIndex: 'name'},
				{text: langs('Индекс'), dataIndex: 'freq', hidden: true},
				{text: langs('Частота'), minWidth: 200, dataIndex: 'freq'},
				{text: langs('ОДД'), minWidth: 200, dataIndex: 'ODD'},
				{text: langs('ЭКД'), minWidth: 200, dataIndex: 'EKD'}
			],
			listeners: {
				'select': function () {
					win.TreatmentDrugGridToolbar.items.items[1].enable();
					if (win.action != 'view') {
						win.TreatmentDrugGridToolbar.items.items[2].enable();
					} else {
						win.TreatmentDrugGridToolbar.items.items[2].disable();
					}
				},
				'deselect': function () {
					win.TreatmentDrugGridToolbar.items.items[1].disable();
					win.TreatmentDrugGridToolbar.items.items[2].disable();
				}
			}
		});

		//панель Питательные смеси
		win.NutrMixtureGrid = new Ext6.grid.Panel({
			xtype: 'grid',
			cls: 'EmkGrid',
			width: 1000,
			style: 'padding-left: '+grid_padding_left,
			border: false,
			dockedItems: win.NutrMixtureGridToolbar,
			store: {
				fields: [
					{ name: 'id', type: 'int' },
					{ name: 'name', type: 'string' },
					{ name: 'freq', type: 'float' },

					{ name: 'ODD', type: 'int' },
					{ name: 'EKD', type: 'int' },
					{ name: 'avenum', type: 'int' },

					{ name: 'ODD_ed', type: 'int' },
					{ name: 'EKD_ed', type: 'int' },
					{ name: 'avenum_ed', type: 'int' },
				],
				data : []
			},
			columns: [
				{text: langs('Вид питательной смеси'), flex: 1, minWidth: 200, dataIndex: 'name'},
				{text: langs('Дневная доза'), minWidth: 150, dataIndex: 'ODD'},
				{text: langs('Курсовая доза'), minWidth: 150, dataIndex: 'EKD'},
				{text: langs('Среднее кол-во'), minWidth: 150, dataIndex: 'avenum'},
				{text: langs('Частота предоставления'), minWidth: 200, dataIndex: 'freq'},

				{text: 'id', dataIndex: 'id', hidden: true},
				{text: langs('Единицы измерения ОДД'), dataIndex: 'ODD_ed', hidden: true},
				{text: langs('Единицы измерения ЭКД'), dataIndex: 'EKD_ed', hidden: true},
				{text: langs('Среднее количество'), dataIndex: 'avenum_ed', hidden: true},

			],
			listeners: {
				'select': function () {
					win.NutrMixtureGridToolbar.items.items[1].enable();
					if (win.action != 'view') {
						win.NutrMixtureGridToolbar.items.items[2].enable();
					} else {
						win.NutrMixtureGridToolbar.items.items[2].disable();
					}
				},
				'deselect': function () {
					win.NutrMixtureGridToolbar.items.items[1].disable();
					win.NutrMixtureGridToolbar.items.items[2].disable();
				}
			}
		});

		//панель Импланты
		win.ImplantGrid = new Ext6.grid.Panel({
			xtype: 'grid',
			cls: 'EmkGrid',
			width: 1000,
			style: 'padding-left: '+grid_padding_left,
			border: false,
			dockedItems: win.ImplantGridToolbar,
			store: {
				fields: [
					{ name: 'name', type: 'string' },
					{ name: 'id', type: 'int' },
					{ name: 'freq', type: 'float' },
					{ name: 'avenum', type: 'float' }
				],
				data : []
			},
			columns: [
				{text: langs('Имплант'), flex: 1, minWidth: 200, dataIndex: 'name'},
				{text: 'id', dataIndex: 'id', hidden: true},
				{text: langs('Частота предоставления'), minWidth: 200, dataIndex: 'freq'},
				{text: langs('Среднее количество'), minWidth: 200, dataIndex: 'avenum'}
			],
			listeners: {
				'select': function () {
					win.ImplantGridToolbar.items.items[1].enable();
					if (win.action != 'view') {
						win.ImplantGridToolbar.items.items[2].enable();
					} else {
						win.ImplantGridToolbar.items.items[2].disable();
					}
				},
				'deselect': function () {
					win.ImplantGridToolbar.items.items[1].disable();
					win.ImplantGridToolbar.items.items[2].disable();
				}
			}
		});

		//панель Компоненты и препараты крови
		win.PresBloodGrid = new Ext6.grid.Panel({
			xtype: 'gridpanel',
			cls: 'EmkGrid',
			width: 1000,
			style: 'padding-left: '+grid_padding_left,
			border: false,
			dockedItems: win.PresBloodGridToolbar,
			store: {
				fields: [
					{ name: 'id', type: 'int' },
					{ name: 'name', type: 'string' },
					{ name: 'ODD', type: 'int' },
					{ name: 'EKD', type: 'int' },
					{ name: 'freq', type: 'float' },
					{ name: 'avenum', type: 'float' },
					{ name: 'ed', type: 'int' }
				],
				data : []
			},
			columns: [
				{text: langs('Вид'), flex: 1, minWidth: 200, dataIndex: 'name'},
				{text: langs('Дневная доза'), minWidth: 150, dataIndex: 'ODD'},
				{text: langs('Курсовая доза'), minWidth: 150, dataIndex: 'EKD'},
				{text: langs('Частота предоставления'), minWidth: 200, dataIndex: 'freq'},
				{text: langs('Среднее количество'), minWidth: 200, dataIndex: 'avenum'},
				{text: langs('Единицы измерения'), dataIndex: 'ed', hidden:true}
			],
			listeners: {
				'select': function () {
					win.PresBloodGridToolbar.items.items[1].enable();
					if (win.action != 'view') {
						win.PresBloodGridToolbar.items.items[2].enable();
					} else {
						win.PresBloodGridToolbar.items.items[2].disable();
					}
				},
				'deselect': function () {
					win.PresBloodGridToolbar.items.items[1].disable();
					win.PresBloodGridToolbar.items.items[2].disable();
				}
			}
		});

		Ext6.apply(win, {
			layout: 'vbox',
			border: false,
			items: [{
					layout: {
						type: 'accordion',
						titleCollapse: false,
						animate: true,
						multi: true,
						activeOnTop: false,
						border: true
					},
					border: false,
					cls: 'cs6',
					width: '100%',
					items: [
						win.InfoPanel,
						win.TreatmentDrugPanel = new Ext6.create('swPanel', {
							cls: 'header-no-background',
							title: 'Медикаментозное лечение',
							collapseToolText: 'Свернуть',
							expandToolText: 'Развернуть',
							collapsible: true,
							titleCollapse: true,
							collapsed: true,
							layout: 'vbox',
							style: 'margin: 5px 5px 5px;',
							width: '100%',
							border: false,
							viewModel: {},
							items: win.TreatmentDrugGrid
						}),
						win.DiagPanel = new Ext6.create('swPanel', {
							cls: 'header-no-background',
							title: 'Диагностика',
							collapseToolText: 'Свернуть',
							expandToolText: 'Развернуть',
							collapsible: true,
							titleCollapse: true,
							collapsed: true,
							layout: 'vbox',
							style: 'margin: 5px 5px 5px;',
							width: '100%',
							border: false,
							viewModel: {},
							items: win.DiagGrid
						}),
						win.TreatmentPanel = new Ext6.create('swPanel', {
							cls: 'header-no-background',
							title: langs('Лечение и контроль терапии'),
							collapseToolText: langs('Свернуть'),
							expandToolText: langs('Развернуть'),
							collapsible: true,
							titleCollapse: true,
							collapsed: true,
							layout: 'vbox',
							style: 'margin: 5px 5px 5px;',
							width: '100%',
							border: false,
							viewModel: {},
							items: win.CureGrid
						}),
						win.NutrMixturePanel = new Ext6.create('swPanel', {
							cls: 'header-no-background',
							title: 'Питательные смеси',
							collapseToolText: 'Свернуть',
							expandToolText: 'Развернуть',
							collapsible: true,
							hidden: true,
							titleCollapse: true,
							collapsed: true,
							layout: 'vbox',
							style: 'margin: 5px 5px 5px;',
							width: '100%',
							border: false,
							viewModel: {},
							items: win.NutrMixtureGrid
						}),
						win.ImplantPanel = new Ext6.create('swPanel', {
							cls: 'header-no-background',
							title: 'Импланты',
							collapseToolText: 'Свернуть',
							expandToolText: 'Развернуть',
							collapsible: true,
							hidden: true,
							titleCollapse: true,
							collapsed: true,
							layout: 'vbox',
							style: 'margin: 5px 5px 5px;',
							width: '100%',
							border: false,
							viewModel: {},
							items: win.ImplantGrid
						}),
						win.PresBloodPanel = new Ext6.create('swPanel', {
							cls: 'header-no-background',
							title: 'Компоненты и препараты крови',
							collapseToolText: 'Свернуть',
							expandToolText: 'Развернуть',
							collapsible: true,
							hidden: true,
							titleCollapse: true,
							collapsed: true,
							layout: 'vbox',
							style: 'margin: 5px 5px 5px;',
							width: '100%',
							border: false,
							viewModel: {},
							items: win.PresBloodGrid
						})
					]
				}, {
					layout: 'column',
					border: false,
					margin: '10 10 10 30',
					items: [
						win.saveButton = Ext6.create('Ext6.button.Button', {
							text: 'Сохранить',
							xtype: 'button',
							cls: 'button-primary',
							margin: 5,
							handler: function() {
								win.doSave();
							}
						}), {
							text: 'Отмена',
							xtype: 'button',
							cls: 'button-secondary',
							margin: 5,
							handler: function() {
								win.hide();
							}
						}
					]
				}
			]
		});

		this.callParent(arguments);
	}
});