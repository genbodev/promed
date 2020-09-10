/**
 * swMesUslugaEditWindow - окно редактирования "Услуги по МЕСам"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       gabdushev
 * @version      06.2012
 * @comment
 */
sw.Promed.swMesUslugaEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	title: lang['uslugi_po_mesu'],
	layout: 'form',
	id: 'MesUslugaEditWindow',
	modal: true,
	shim: false,
	width: 520,
	resizable: false,
	maximizable: false,
	maximized: false,
	curARMType: null,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSave:  function() {
		var that = this;
		if ( !this.form.isValid() )
		{
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						that.findById('MesUslugaEditForm').getFirstInvalidEl().focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}
		if (that.useUslugaComplexIsteadUsluga) {
			var UslugaComplex = that.form.findField('UslugaComplex_id');
			//2. Реализовать также вывод сообщения "Для введенной услуги из федерального справочника не существует услуги из регионального справочника. ОК" в случае, если для введенной услуги из федерального справочника (ГОСТ 2011) нет связи с региональной услуги (см. в UslugaComplex).
			if (null == (UslugaComplex.getStore().getById(UslugaComplex.getValue()).data.Fedswuslugacomplexnewcombo)) {
				sw.swMsg.show(
					{
						buttons: Ext.Msg.OK,
						fn: function()
						{
							that.submit();
						},
						icon: Ext.Msg.WARNING,
						msg: lang['dlya_vvedennoy_uslugi_iz_federalnogo_spravochnika_ne_suschestvuet_uslugi_iz_regionalnogo_spravochnika_usluga_budet_sohranena_no_dlya_korrektnoy_rabotyi_neobhodimo_ukazat_dlya_dannoy_federalnoy_uslugi_svyaz_s_regionalnoy'],
						title: lang['preduprejdenie_-_otsutstvuet_svyazannaya_regionalnaya_usluga']
					});
			} else {
				that.submit();
			}
		} else {
			that.submit();
		}
		return true;
	},
	submit: function() {
		var that = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = {};
		params.curARMType = that.curARMType;
		
		if(this.type=='nosave'){
			params.MesUsluga_UslugaCount=that.form.findField('MesUsluga_UslugaCount').getValue();
			params.UslugaComplex_id=that.form.findField('UslugaComplex_id').getValue();
			params.Usluga_id=that.form.findField('Usluga_id').getValue();
			params.MesUsluga_id = that.form.findField('MesUsluga_id').getValue();
			if(this.useUslugaComplexIsteadUsluga){
				params.Usluga_id_Name=that.form.findField('UslugaComplex_id').getRawValue();
			}else{
				params.Usluga_id_Name=that.form.findField('Usluga_id').getRawValue();
			}
			
			loadMask.hide();
			that.callback(params);
			that.hide();
		}else{
			params.action = that.action;
			this.form.submit({
				params: params,
				failure: function(result_form, action)
				{
					loadMask.hide();
					if (action.result)
					{
						if (action.result.Error_Code)
						{
							Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
						}
					}
				},
				success: function(result_form, action)
				{
					loadMask.hide();
					that.callback(that.owner, action.result.MesUsluga_id);
					that.hide();
				}
			});
		}
	},
	show: function() {
		var that = this;
		sw.Promed.swMesUslugaEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.type='';
		this.callback = Ext.emptyFn;
		this.MesUsluga_id = null;
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { that.hide(); });
			return false;
		}
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].ARMType ) {
			this.ARMType = arguments[0].ARMType;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].type ) {
			this.type = arguments[0].type;
		}
		if ( arguments[0].useUslugaComplexIsteadUsluga ) {
			this.useUslugaComplexIsteadUsluga = arguments[0].useUslugaComplexIsteadUsluga;
		} else {
			this.useUslugaComplexIsteadUsluga = false;
		}
		this.curARMType = (arguments[0].curARMType) ? arguments[0].curARMType : getGlobalOptions().curARMType;
		if (this.useUslugaComplexIsteadUsluga) {
			that.form.findField('Usluga_id').hideContainer();
			that.form.findField('Usluga_id').allowBlank = true;
			that.form.findField('UslugaComplex_id').showContainer();
			that.form.findField('UslugaComplex_id').allowBlank = false;
		} else {
			that.form.findField('UslugaComplex_id').hideContainer();
			that.form.findField('UslugaComplex_id').allowBlank = true;
			that.form.findField('Usluga_id').showContainer();
			that.form.findField('Usluga_id').allowBlank = false;
		}

		this.syncSize();
		this.syncShadow();

		if ( arguments[0].MesUsluga_id ) {
			this.MesUsluga_id = arguments[0].MesUsluga_id;
		}
		this.form.reset();
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		var UslugaComplexCombo = that.form.findField('UslugaComplex_id');
		UslugaComplexCombo.getStore().baseParams.UslugaCategory_id = 4;
		switch (arguments[0].action) {
			case 'add':
				if ( arguments[0].Mes_id ) {
					this.form.findField('Mes_id').setValue(arguments[0].Mes_id);
				} else {
					if(this.type=='nosave'){
						
					}else{
						sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { that.hide(); });
						return false;
					}
				}
				loadMask.hide();
				break;
			case 'edit':
			case 'view':
				if(this.type=='nosave'){
					that.form.setValues(arguments[0].data);
							if (that.useUslugaComplexIsteadUsluga) {
								var setUslugaComplexComboValue = function(rec) {
									if (rec.get('UslugaComplex_id') == UslugaComplexCombo.getValue()) {
										UslugaComplexCombo.setValue(rec.get('UslugaComplex_id'));
										UslugaComplexCombo.fireEvent('select', UslugaComplexCombo, rec, 0);
									}
								}
								UslugaComplexCombo.getStore().load({
										callback: function() {
											if (UslugaComplexCombo .getStore().getCount() > 0) {
												UslugaComplexCombo.getStore().each(setUslugaComplexComboValue);
											}
											loadMask.hide();
										},
										params: {
											UslugaComplex_id: UslugaComplexCombo.getValue(),
											UslugaCategory_id: 4
										}
									});
							} else {
								that.form.findField('Usluga_id').getStore().load({
									callback: function(r, o, s) {
										that.form.findField('Usluga_id').getStore().each(function(record) {
											if ( record.get('Usluga_id') == that.form.findField('Usluga_id').getValue() ) {
												that.form.findField('Usluga_id').fireEvent('select', that.form.findField('Usluga_id'), record, 0);
											}
										});
										loadMask.hide();
										that.form.findField('Usluga_id').focus(true, 100);
									},
									params: {
										where: "where UslugaType_id = 2 and Usluga_id = " + that.form.findField('Usluga_id').getValue()
									}
								});
							}
				}else{
					Ext.Ajax.request({
						failure:function () {
							sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
							loadMask.hide();
							that.hide();
						},
						params:{
							MesUsluga_id: that.MesUsluga_id
						},
						success: function (response) {
							var result = Ext.util.JSON.decode(response.responseText);
							if (!result[0]) { return false}
							that.form.setValues(result[0]);
							if (that.useUslugaComplexIsteadUsluga) {
								var setUslugaComplexComboValue = function(rec) {
									if (rec.get('UslugaComplex_id') == UslugaComplexCombo.getValue()) {
										UslugaComplexCombo.setValue(rec.get('UslugaComplex_id'));
										UslugaComplexCombo.fireEvent('select', UslugaComplexCombo, rec, 0);
									}
								}
								UslugaComplexCombo.getStore().load({
										callback: function() {
											if (UslugaComplexCombo .getStore().getCount() > 0) {
												UslugaComplexCombo.getStore().each(setUslugaComplexComboValue);
											}
											loadMask.hide();
										},
										params: {
											UslugaComplex_id: UslugaComplexCombo.getValue(),
											UslugaCategory_id: 4
										}
									});
							} else {
								that.form.findField('Usluga_id').getStore().load({
									callback: function(r, o, s) {
										that.form.findField('Usluga_id').getStore().each(function(record) {
											if ( record.get('Usluga_id') == that.form.findField('Usluga_id').getValue() ) {
												that.form.findField('Usluga_id').fireEvent('select', that.form.findField('Usluga_id'), record, 0);
											}
										});
										loadMask.hide();
										that.form.findField('Usluga_id').focus(true, 100);
									},
									params: {
										where: "where UslugaType_id = 2 and Usluga_id = " + that.form.findField('Usluga_id').getValue()
									}
								});
							}
							return true;
						},
						url:'/?c=MesUsluga&m=load'
					});
				}
				break;
		}
		return true;
	},
	initComponent: function() {
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'MesUslugaEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 140,
				collapsible: true,
				region: 'north',
				url:'/?c=MesUsluga&m=save',
				items: [{
					name: 'MesUsluga_id',
					xtype: 'hidden',
					value: 0
				},
					{
						xtype: 'swuslugacombo',
						hiddenName: 'Usluga_id',
						allowBlank:false,
						width: 300
					},
					{
						allowBlank: true,
						value: null,
						fieldLabel: lang['kompleksnaya_usluga'],
						name: 'UslugaComplex_id',
						listWidth: 600,
						width: 300,
						xtype: 'swuslugacomplexnewcombo'
					},
					{
						name: 'Mes_id',
						xtype: 'hidden'
					},
					{
						allowNegative:false,
						allowBlank:false,
						fieldLabel: lang['kolichestvo'],
						name: 'MesUsluga_UslugaCount',
						width:100,
						xtype:'numberfield'
					},
					{
						allowBlank: true,
						fieldLabel: lang['data_nachala'],
						format: 'd.m.Y',
						name: 'MesUsluga_begDT',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						xtype: 'swdatefield'
					},
					{
						allowBlank: true,
						fieldLabel: lang['data_okonchaniya'],
						format: 'd.m.Y',
						name: 'MesUsluga_endDT',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						xtype: 'swdatefield'
					}]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MesUsluga_id'},
				{name: 'Usluga_id'},
				{name: 'Mes_id'},
				{name: 'MesUsluga_UslugaCount'},
				{name: 'MesUsluga_begDT'},
				{name: 'MesUsluga_endDT'}
			]),
			url: '/?c=MesUsluga&m=save'
		});
		Ext.apply(this, {
			buttons: [{
					handler: function()
					{
						this.ownerCt.doSave();
					},
					iconCls: 'save16',
					text: BTN_FRMSAVE
				},
					{
						text: '-'
					},
					HelpButton(this, 0),//todo проставить табиндексы
					{
						handler: function()
						{
							this.ownerCt.hide();
						},
						iconCls: 'cancel16',
						text: BTN_FRMCANCEL
					}],
			items:[form]
		});
		sw.Promed.swMesUslugaEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('MesUslugaEditForm').getForm();
	}
});