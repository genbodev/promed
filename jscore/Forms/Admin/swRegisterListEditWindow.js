/**!!!!!!!!!!!!!!!!!!!НЕ ИСПОЛЬЗУЕТСЯ - МУСОР!!!!!!!!!!!!!!!!!!!!
 * swRegisterListEditWindow - окно редактирования "Таблица регистров/справочников доступных для загрузки"
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
sw.Promed.swRegisterListEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['tablitsa_registrov_spravochnikov_dostupnyih_dlya_zagruzki'],
	layout: 'border',
	id: 'RegisterListEditWindow',
	modal: true,
	shim: false,
	width: 500,
	resizable: false,
	maximizable: false,
	maximized: false,
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
						that.findById('RegisterListEditForm').getFirstInvalidEl().focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}
		this.submit();
		return true;
	},
	submit: function() {
		var that = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = {};
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
				that.callback(that.owner, action.result.RegisterList_id);
				that.hide();
			}
		});
	},
	show: function() {
		var that = this;
		sw.Promed.swRegisterListEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.RegisterList_id = null;
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
		if ( arguments[0].RegisterList_id ) {
			this.RegisterList_id = arguments[0].RegisterList_id;
		}
		this.form.reset();
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		switch (arguments[0].action) {
			case 'add':
				loadMask.hide();
				break;
			case 'edit':
			case 'view':
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						that.hide();
					},
					params:{
						RegisterList_id: that.RegisterList_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) {
							return false;
						}
						that.form.setValues(result[0]);
						loadMask.hide();
						return true;
					},
					url:'/?c=RegisterList&m=load'
				});
				break;
		}
		return true;
	},
	initComponent: function() {
		var form = new Ext.form.FormPanel({
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
				id: 'RegisterListEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 100,
				collapsible: true,
				region: 'north',
				url:'/?c=RegisterList&m=save',
				items: [{
					name: 'RegisterList_id',
					xtype: 'hidden',
					value: 0
				},
					{
						fieldLabel: lang['nazvanie_osnovnoy_tablitsyi_v_bd'],
						name: 'RegisterList_Name',
						allowBlank:true,
						xtype: 'textfield',
						width: 350
					},
					{
						fieldLabel: lang['shema_bd'],
						name: 'RegisterList_Schema',
						allowBlank:true,
						xtype: 'textfield',
						width: 350
					},
					{
						fieldLabel: lang['opisanie_spravochnika'],
						name: 'RegisterList_Descr',
						allowBlank:true,
						xtype: 'textfield',
						width: 350
					},
					{
						fieldLabel: lang['identifikator_regiona_spravochnika_territoriy'],
						hiddenName: 'Region_id',
						xtype: 'swcommonsprcombo',

						allowBlank:true,
						sortField:'KLArea_Code',
						comboSubject: 'KLArea',
						width: 350
					}]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'RegisterList_id'},
				{name: 'RegisterList_Name'},
				{name: 'RegisterList_Schema'},
				{name: 'RegisterList_Descr'},
				{name: 'Region_id'}
			]),
			url: '/?c=RegisterList&m=save'
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
				[{
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
		sw.Promed.swRegisterListEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('RegisterListEditForm').getForm();
	}
});