/**
 * swMesOldEditWindow - окно редактирования/добавления МЭС.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
 * @author       Markoff A.A. <markov@swan.perm.ru>
 * @version      08.08.2011
 * @comment      Префикс для id компонентов MEW (MesOldEditWindow)
 */
sw.Promed.swMesOldEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 600,
	layout: 'form',
	id: 'MesOldEditWindow',
	listeners: {
		hide: function () {
			this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	curARMType: null,
	doSave: function (callback) {
		var form = this.findById('MesOldEditForm');
		if (!form.getForm().isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		if ((form.getForm().findField('Mes_endDT').getValue() != '') && (form.getForm().findField('Mes_endDT').getValue() < form.getForm().findField('Mes_begDT').getValue())) {
			sw.swMsg.alert(lang['oshibka'], lang['data_okonchaniya_deystviya'] + getMESAlias() + lang['ne_doljna_byit_ranshe_datyi_nachala_deystviya'], function () {
				form.getForm().findField('Mes_endDT').focus();
			});
			return false;
		}
		this.submit(callback);
		return true;
	},
	submit: function (callback) {
		var form = this.findById('MesOldEditForm');
		var current_window = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		var params={};
		if(this.action=='copy'){
			var UslugaArr = [];
			this.MesUslugaGrid.getGrid().getStore().each(function(s,d,f){
				UslugaArr.push(s.data);
			});
			params.UslugaArr = Ext.util.JSON.encode(UslugaArr);
		}
		
		params.action = current_window.action;
		params.Mes_Code=form.getForm().findField('Mes_Code').getValue();
		params.curARMType = this.curARMType;
		loadMask.show();
		form.getForm().submit({
			params: params,
			failure: function (result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#'] + action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function (result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.Mes_id) {
						if (callback){
							form.getForm().findField('Mes_id').setValue(action.result.Mes_id);
							form.getForm().findField('Mes_Code').setValue(action.result.Mes_Code);
							callback(action.result.Mes_id);
						} else {
							current_window.hide();
							current_window.callback({
								Mes_id: action.result.Mes_id,
								Mes_Code: form.getForm().findField('Mes_Code').getValue(),
								MesProf_CodeName: form.getForm().findField('MesProf_id').getRawValue(),
								MesAgeGroup_CodeName: form.getForm().findField('MesAgeGroup_id').getRawValue(),
								MesLevel_CodeName: form.getForm().findField('MesLevel_id').getRawValue(),
								Mes_KoikoDniMin: form.getForm().findField('Mes_KoikoDniMin').getValue(),
								Mes_KoikoDni: form.getForm().findField('Mes_KoikoDni').getValue(),
								Mes_VizitNumber: form.getForm().findField('Mes_VizitNumber').getValue(),
								Diag_CodeName: form.getForm().findField('Diag_id').getRawValue(),
								Mes_begDT: form.getForm().findField('Mes_begDT').getValue(),
								Mes_endDT: form.getForm().findField('Mes_endDT').getValue()
							});
						}
					} else {
						sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: function () {
									form.hide();
								},
								icon: Ext.Msg.ERROR,
								msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
								title: lang['oshibka']
							});
					}
				}
			}
		});
	},
	enableEdit: function (enable) {
		var form = this.findById('MesOldEditForm');
		if (enable) {
			form.getForm().findField('MesProf_id').enable();
			form.getForm().findField('MesAgeGroup_id').enable();
			form.getForm().findField('MesLevel_id').enable();
			form.getForm().findField('Diag_id').enable();
			form.getForm().findField('Mes_KoikoDniMin').enable();
			form.getForm().findField('Mes_VizitNumber').enable();
			form.getForm().findField('Mes_KoikoDni').enable();
			form.getForm().findField('Mes_begDT').enable();
			form.getForm().findField('Mes_endDT').enable();
			this.buttons[0].enable();
		} else {
			form.getForm().findField('MesProf_id').disable();
			form.getForm().findField('MesAgeGroup_id').disable();
			form.getForm().findField('MesLevel_id').disable();
			form.getForm().findField('Diag_id').disable();
			form.getForm().findField('Mes_KoikoDniMin').disable();
			form.getForm().findField('Mes_VizitNumber').disable();
			form.getForm().findField('Mes_KoikoDni').disable();
			form.getForm().findField('Mes_begDT').disable();
			form.getForm().findField('Mes_endDT').disable();
			this.buttons[0].disable();
		}
	},
	stomatMesProf_Codes: [63,64,65,66,67,68],
	show: function () {
		sw.Promed.swMesOldEditWindow.superclass.show.apply(this, arguments);
		var that = this;
		if (!arguments[0]) {
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.ERROR,
					msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
					title: lang['oshibka'],
					fn: function () {
						this.hide();
					}
				});
		}
		this.focus();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		if (arguments[0].Mes_id) {
			this.Mes_id = arguments[0].Mes_id;
		} else {
			this.Mes_id = null;
		}
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].owner) {
			this.owner = arguments[0].owner;
		}
		if (arguments[0].onHide) {
			this.onHide = arguments[0].onHide;
		}
		if (arguments[0].action) {
			this.action = arguments[0].action;
		} else {
			if (( this.Mes_id ) && ( this.Mes_id > 0 ))
				this.action = "edit";
			else
				this.action = "add";
		}
		this.curARMType = (arguments.length>0 && arguments[0].curARMType) ? arguments[0].curARMType : getGlobalOptions().curARMType;

		var form = this.findById('MesOldEditForm');
		var MesProfCombo = form.getForm().findField('MesProf_id');
		if (arguments[0].stomat) {
			form.getForm().findField('Mes_KoikoDni').setFieldLabel(lang['maksimalnoe_kol-vo_uet']);
			form.getForm().findField('Mes_KoikoDniMin').setFieldLabel(lang['minimalnoe_kol-vo_uet']);
			form.getForm().findField('Mes_VizitNumber').showContainer();
			MesProfCombo.stomat = true;
		} else {
			form.getForm().findField('Mes_KoikoDni').setFieldLabel(lang['maks_normativnyiy_srok']);
			form.getForm().findField('Mes_KoikoDniMin').setFieldLabel(lang['min_normativnyiy_srok']);
			form.getForm().findField('Mes_VizitNumber').hideContainer();
			MesProfCombo.stomat = false;
		}
		if (MesProfCombo.stomat) {
			that.form.findField('MesProf_id').getStore().filterBy(function (el){ return that.stomatMesProf_Codes.in_array(el.data.MesProf_Code)});
			if (!MesProfCombo.getStore().getById(MesProfCombo.getValue())) {
				MesProfCombo.clearValue();
			}
		} else {
			that.form.findField('MesProf_id').getStore().clearFilter();
		}

		this.syncSize();
		this.syncShadow();

		form.getForm().setValues(arguments[0]);
		this.MesUslugaGrid.removeAll();
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action) {
			case 'add':
				this.form.findField('Mes_id').setValue('0');
				this.form.findField('MedicalCareKind_id').setValue('');
				this.form.findField('Mes_Code').setValue('');
				this.form.findField('Mes_Code').hideContainer();
				this.findById('MesOldEditFormFieldPanel').setHeight(Ext.isIE ? 290 : 275);
				this.form.findField('Diag_id').setValue('');
				this.form.findField('Mes_KoikoDni').setValue('');
				this.form.findField('Mes_KoikoDniMin').setValue('');
				this.form.findField('Mes_VizitNumber').setValue('');
				this.form.findField('Mes_begDT').setValue('');
				this.form.findField('Mes_endDT').setValue('');
				this.setTitle(getMESAlias() + lang['_dobavlenie']);
				this.form.findField('MedicalCareKind_id').setValue(arguments[0].MedicalCareKind_id);
				this.enableEdit(true);
				loadMask.hide();
				form.getForm().clearInvalid();
				break;
			case 'copy':
				this.form.findField('Mes_Code').hideContainer();
				this.form.findField('MedicalCareKind_id').setValue('');
				this.findById('MesOldEditForm').getForm().reset();
				this.setTitle(getMESAlias() + lang['_dobavlenie']);
				this.form.findField('MedicalCareKind_id').setValue(arguments[0].MedicalCareKind_id);
				this.enableEdit(true);
				break;
			case 'edit':
				this.form.findField('Mes_Code').showContainer();
				this.findById('MesOldEditFormFieldPanel').setHeight(Ext.isIE ? 310 : 295);
				this.findById('MesOldEditForm').getForm().reset();
				this.setTitle(getMESAlias() + lang['_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.findById('MesOldEditForm').getForm().reset();
				this.setTitle(getMESAlias() + lang['_prosmotr']);
				this.enableEdit(false);
				break;
		}
		if (this.action != 'add') {
			form.getForm().load(
				{
					params: {
						Mes_id: that.Mes_id
					},
					failure: function () {
						loadMask.hide();
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function () {
								that.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
							title: lang['oshibka']
						});
					},
					success: function () {
						loadMask.hide();
						if(that.action =='copy'){
							if (form.getForm().findField('Mes_endDT').getValue() != '') {
								var date = form.getForm().findField('Mes_endDT').getValue();
								form.getForm().findField('Mes_endDT').setValue('')
								form.getForm().findField('Mes_begDT').setValue(date.add(Date.DAY,1));
							}else{
								form.getForm().findField('Mes_begDT').setValue('');
							}
						}else{
							if (form.getForm().findField('Mes_endDT').getValue() != '') {
								form.getForm().findField('Mes_endDT').disable();
							}
						}
						var diag_combo = form.getForm().findField('Diag_id');
						diag_combo.getStore().load({
							params: { where: "where Diag_id = " + diag_combo.getValue() },
							callback: function () {
								diag_combo.setValue(diag_combo.getValue());
								diag_combo.getStore().each(function (record) {
									if (record.data.Diag_id == diag_combo.getValue()) {
										diag_combo.fireEvent('select', diag_combo, record, 0);
									}
								});
							}
						});
						that.loadUslugaGrid();
						if(that.action =='copy'){
							that.form.findField('Mes_id').setValue('0');
							that.form.findField('Mes_Code').setValue('');
							
						}
						if (that.action == 'edit') {
							form.getForm().findField('Mes_KoikoDni').focus(true, 300);
						} else {
							that.buttons[3].focus();
						}
					},
					url: '/?c=MesOld&m=loadMesOld'
				});
		}
		var grid = this.findById('MesUslugaGrid');
		if (this.action != 'view') {
			grid.focusPrev = this.form.findField('Mes_endDT');
			grid.focusPrev.type = 'field';
			grid.focusPrev.name = grid.focusPrev.id;
			grid.focusOn = this.buttons[0];
			grid.focusOn.type = 'field';
			grid.focusOn.name = grid.focusOn.id;
			grid.setReadOnly(false);
			form.getForm().findField('MesProf_id').focus(true, 100);
		} else {
			grid.focusPrev = this.buttons[3];
			grid.focusPrev.type = 'field';
			grid.focusPrev.name = grid.focusPrev.id;
			grid.focusOn = grid.focusPrev;
			this.buttons[3].focus();
			grid.setReadOnly(true);
		}
	},
	loadUslugaGrid: function () {
		var that = this;
		var Mes_id = this.form.findField('Mes_id').getValue();
		this.MesUslugaGrid.loadData({globalFilters:{Mes_id: Mes_id}, callback: function (){
			that.MesUslugaGrid.getGrid().getSelectionModel().selectFirstRow();
			
		}});
	},
	editNoSaveUsluga:function(data,action){
		var store = this.findById('MesUslugaGrid').getGrid().getStore();
		if(action=='add'){
			data.MesUsluga_id = -swGenTempId(store);
			store.loadData([data],true);
		}else{
			var index= store.findBy(function (rec){return rec.get('MesUsluga_id') == data.MesUsluga_id});
			var record = store.getAt(index);
			record.set('MesUsluga_UslugaCount',data.MesUsluga_UslugaCount);
			record.set('UslugaComplex_id',data.UslugaComplex_id);
			record.set('Usluga_id',data.Usluga_id);
			record.set('Usluga_id_Name',data.Usluga_id_Name);
			record.commit();
		}
	},
	initComponent: function () {
		var that = this;
		this.MesUslugaGrid = new sw.Promed.ViewFrame({
			actions: [
				{
					name: 'action_add',
					handler: function () {
						if(that.action=='copy'){
							getWnd('swMesUslugaEditWindow', {}).show({
								curARMType: that.curARMType,
								action: 'add',
								type:'nosave',
								useUslugaComplexIsteadUsluga: that.form.findField('MesProf_id').stomat,
								callback: function (data) {
									that.editNoSaveUsluga(data,'add');
								}
							});
						}else{
							var openMesUslugaAddingWindow = function (Mes_id){
								getWnd('swMesUslugaEditWindow', {}).show({
									curARMType: that.curARMType,
									action: 'add',
									Mes_id: Mes_id,
									useUslugaComplexIsteadUsluga: that.form.findField('MesProf_id').stomat,
									callback: function () {
										that.loadUslugaGrid();
									}
								});
							};
							var Mes_id = that.form.findField('Mes_id').getValue();
							if (parseInt(Mes_id)>0) {
								openMesUslugaAddingWindow(Mes_id);
							} else {
								that.doSave(openMesUslugaAddingWindow);
							}
						}
					}
				},
				{
					name: 'action_edit',
					handler: function (){
						if(that.action=='copy'){
							var record = that.MesUslugaGrid.getGrid().getSelectionModel().getSelected();
							
							getWnd('swMesUslugaEditWindow', {}).show({
								curARMType: that.curARMType,
								action: 'edit',
								type:'nosave',
								data:record.data,
								useUslugaComplexIsteadUsluga: that.form.findField('MesProf_id').stomat,
								callback: function (data) {
									that.editNoSaveUsluga(data,'edit');
								}
							});
						}else{
							var MesUsluga_id = that.MesUslugaGrid.getGrid().getSelectionModel().getSelected().id
							if (MesUsluga_id > 0) {
								getWnd('swMesUslugaEditWindow', {}).show({
									curARMType: that.curARMType,
									action: 'edit',
									MesUsluga_id: MesUsluga_id,
									useUslugaComplexIsteadUsluga: that.form.findField('MesProf_id').stomat,
									callback: function () {
										that.loadUslugaGrid();
									}
								});
							}
						}
					}
				},
				{name: 'action_view', hidden: true},
				{name: 'action_refresh', disabled: (that.action=='copy')},
				{name: 'action_delete', handler: function (){
						if(that.action=='copy'){
							var store = that.MesUslugaGrid.getGrid().getStore();
							var record = that.MesUslugaGrid.getGrid().getSelectionModel().getSelected();
							var index= store.findBy(function (rec){return rec.get('MesUsluga_id') == record.get('MesUsluga_id')});
							store.removeAt(index);
							that.MesUslugaGrid.getGrid().getSelectionModel().selectFirstRow();
						}else{
							that.MesUslugaGrid.deleteRecord();
						}
				}},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MesUsluga&m=loadList',
			height: 160,
			region: 'center',
			object: 'MesUsluga',
			editformclassname: 'swMesUslugaEditWindow',
			id: 'MesUslugaGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'MesUsluga_id', type: 'int', header: 'ID', key: true},
				{name: 'Usluga_id_Name', type: 'string', header: lang['usluga'], id: 'autoexpand'},
				{name: 'Usluga_id', type: 'int', hidden: true},
				{name: 'UslugaComplex_id', type: 'int', hidden: true},
				{name: 'Mes_id', type: 'int', hidden: true},
				{name: 'MesUsluga_UslugaCount', type: 'float', header: lang['kolichestvo'], width: 120},
				{name: 'MesUsluga_begDT', type: 'date', format: 'd.m.Y', header: lang['data_nachala'], width: 100},
				{name: 'MesUsluga_endDT', type: 'date', format: 'd.m.Y', header: lang['data_okonchaniya'], width: 100}
			],
			title: lang['2_uslugi'],
			toolbar: true,
			focusOnFirstLoad: false
		});
		this.MesOldEditForm = new Ext.form.FormPanel({
			autoHeight: true,
			border: false,
			buttonAlign: 'left',
			frame: false,
			region: 'north',
			id: 'MesOldEditForm',
			labelAlign: 'right',
			labelWidth: 200,
			items: [
				{
					id: 'MEW_Mes_id',
					name: 'Mes_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'MedicalCareKind_id',
					value: 0,
					xtype: 'hidden'
				},
				new Ext.Panel({
					//height: Ext.isIE ? 310 : 295,
					id: 'MesOldEditFormFieldPanel',
					bodyStyle: 'padding-top: 0.2em;',
					border: true,
					frame: true,
					style: 'margin-bottom: 0.1em;',
					items: [
						{
							allowBlank: false,
							anchor: '99%',
							disabled: true,//
							xtype: 'textfield',
							fieldLabel: lang['kod'] + getMESAlias(),
							name: 'Mes_Code',
							width: 250,
							id: 'MEW_MES_Code_Edit_Field'
						},
						{
							allowBlank: false,
							anchor: '99%',
							enableKeyEvents: true,
							listeners: {
								'keydown': function (inp, e) {
									if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
										e.stopEvent();
										var form = that.findById('MesOldEditForm');
										form.getForm().findField('MesAgeGroup_id').focus(true);
									}
								},
								'expand':function () {
									if (this.stomat) {
										that.form.findField('MesProf_id').getStore().filterBy(function (el){ return that.stomatMesProf_Codes.in_array(el.data.MesProf_Code)});
									} else {
										that.form.findField('MesProf_id').getStore().clearFilter();
									}
								}
							},
							hiddenName: 'MesProf_id',
							width: 250,
							tabIndex: TABINDEX_MEW + 1,
							xtype: 'swmesprofcombo'
						},
						{
							allowBlank: false,
							anchor: '99%',
							enableKeyEvents: true,
							listeners: {
								'keydown': function (inp, e) {
									if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB) {
										e.stopEvent();
										var form = that.findById('MesOldEditForm');
										form.getForm().findField('MesProf_id').focus(true);
									}
								}
							},
							hiddenName: 'MesAgeGroup_id',
							width: 250,
							tabIndex: TABINDEX_MEW + 2,
							xtype: 'swmesagegroupcombo'
						},
						{
							allowBlank: false,
							anchor: '99%',
							fieldLabel: lang['kategoriya_slojnosti'],
							hiddenName: 'MesLevel_id',
							width: 250,
							tabIndex: TABINDEX_MEW + 3,
							xtype: 'swmeslevelcombo'
						},
						{
							allowBlank: false,
							anchor: '99%',
							fieldLabel: lang['diagnoz'],
							hiddenName: 'Diag_id',
							width: 250,
							tabIndex: TABINDEX_MEW + 4,
							xtype: 'swdiagcombo'
						},
						{
							xtype: 'numberfield',
							allowNegative: false,
							decimalPrecision: 2,
							minValue: 0,
							maxValue: 999.99,
							anchor: '99%',
							width: 250,
							fieldLabel: lang['min_normativnyiy_srok'],
							tabIndex: TABINDEX_MEW + 5,
							name: 'Mes_KoikoDniMin'
						},
						{
							xtype: 'numberfield',
							allowNegative: false,
							decimalPrecision: 2,
							minValue: 0,
							maxValue: 999.99,
							anchor: '99%',
							width: 250,
							fieldLabel: lang['maks_normativnyiy_srok'],
							tabIndex: TABINDEX_MEW + 6,
							name: 'Mes_KoikoDni'
						},
						{
							xtype: 'numberfield',
							allowNegative: false,
							allowDecimals: false,
							decimalPrecision: 0,
							minValue: 0,
							maxValue: 999,
							anchor: '99%',
							width: 250,
							fieldLabel: lang['poryadkovyiy_nomer_posescheniya'],
							tabIndex: TABINDEX_MEW + 7,
							name: 'Mes_VizitNumber'
						},
						{
							allowBlank: false,
							fieldLabel: lang['data_nachala_deystviya'] + getMESAlias(),
							xtype: 'swdatefield',
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							tabIndex: TABINDEX_MEW + 8,
							name: 'Mes_begDT'
						},
						{
							fieldLabel: lang['data_okonchaniya_deystviya'] + getMESAlias(),
							xtype: 'swdatefield',
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							tabIndex: TABINDEX_MEW + 9,
							name: 'Mes_endDT',
							enableKeyEvents: true,
							listeners:{
								'keydown':function (inp, e) {
									if (e.getKey() == Ext.EventObject.TAB) {
										if (!e.shiftKey) {
											e.stopEvent();
											that.focusOnGrid();
										}
									}
								}
							}
						}
					],
					layout: 'form',
					title: lang['1_mes']
				})
			],
			keys: [
				{
					alt: true,
					fn: function (inp, e) {
						switch (e.getKey()) {
							case Ext.EventObject.C:
								if (this.action != 'view') {
									this.doSave(false);
								}
								break;
							case Ext.EventObject.J:
								this.hide();
								break;
						}
					},
					key: [ Ext.EventObject.C, Ext.EventObject.J ],
					scope: this,
					stopEvent: true
				}
			],
			reader: new Ext.data.JsonReader({
				success: function () {
					//
				}
			}, [
				{ name: 'Mes_id' },
				{ name: 'MedicalCareKind_id' },
				{ name: 'Mes_Code' },
				{ name: 'MesProf_id' },
				{ name: 'MesAgeGroup_id' },
				{ name: 'MesLevel_id' },
				{ name: 'Diag_id' },
				{ name: 'Mes_KoikoDniMin' },
				{ name: 'Mes_VizitNumber' },
				{ name: 'Mes_KoikoDni' },
				{ name: 'Mes_begDT' },
				{ name: 'Mes_endDT' }
			]),
			url: '/?c=MesOld&m=save'
		});
		Ext.apply(this, {
			buttons: [
				{
					handler: function () {
						this.ownerCt.doSave();
					},
					iconCls: 'save16',
					tabIndex: TABINDEX_MEW + 10,
					text: BTN_FRMSAVE,
					onShiftTabAction: function () {
						that.focusOnGrid();
					},
					onTabAction: function () {
						that.buttons[3].focus();
					}
				},
				{
					text: '-'
				},
				HelpButton(this, TABINDEX_MEW + 11),
				{
					handler: function () {
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					tabIndex: TABINDEX_MEW + 12,
					text: BTN_FRMCANCEL,
					onTabAction: function () {
						if (that.action == 'view') {
							that.focusOnGrid();
						} else {
							that.form.findField('MesProf_id').focus();
						}
					},
					onShiftTabAction: function () {
						if (that.action == 'view') {
							that.focusOnGrid();
						} else {
							that.buttons[0].focus();
						}
					}
				}
			],
			items: [this.MesOldEditForm,
				this.MesUslugaGrid
			]
		});
		sw.Promed.swMesOldEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById("MesOldEditForm").getForm();
		this.focusOnGrid = function () {
			var grid = that.findById('MesUslugaGrid').getGrid();
			if (grid.getStore().getCount() > 0) {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}
		}

	}
});