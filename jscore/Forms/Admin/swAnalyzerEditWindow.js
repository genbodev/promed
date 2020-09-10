/**
 * swAnalyzerEditWindow - окно редактирования "Анализатор"
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
sw.Promed.swAnalyzerEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	title: lang['analizator'],
	layout: 'form',
	id: 'AnalyzerEditWindow',
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
						that.findById('AnalyzerEditForm').getFirstInvalidEl().focus(true);
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
						Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action)
			{
				loadMask.hide();
				that.callback(that.owner, action.result.Analyzer_id);
				that.hide();
			}
		});
	},
	show: function() {
		var that = this;
		sw.Promed.swAnalyzerEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.Analyzer_id = null;
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { that.hide(); });
			return false;
		}
		var MedService_id;
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
		if ( arguments[0].Analyzer_id ) {
			this.Analyzer_id = arguments[0].Analyzer_id;
		}
		that.form.findField('AnalyzerModel_id').getStore().load();
		this.form.reset();
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		switch (arguments[0].action) {
			case 'add':
				if ( arguments[0].MedService_id ) {
					this.form.findField('MedService_id').setValue(arguments[0].MedService_id);
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { loadMask.hide(); that.hide(); });
					return false;
				}
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
						Analyzer_id: that.Analyzer_id
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
					url:'/?c=Analyzer&m=load'
				});
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
			autoHeight: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'AnalyzerEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 100,
				collapsible: true,
				region: 'north',
				url:'/?c=Analyzer&m=save',
				items: [{
					name: 'Analyzer_id',
					xtype: 'hidden',
					value: 0
				},
					{
						fieldLabel: lang['naimenovanie_analizatora'],
						name: 'Analyzer_Name',
						allowBlank:false,
						xtype: 'textfield',
						width: 350
					},
					{
						fieldLabel: lang['kod'],
						name: 'Analyzer_Code',
						allowBlank:false,
						xtype: 'textfield',
						width: 350
					},
					{
						fieldLabel: lang['model_analizatora'],
						hiddenName: 'AnalyzerModel_id',
						xtype: 'swanalyzermodelcombo',
						allowBlank:true,
						width: 350
					},
					{
						name: 'MedService_id',
						xtype: 'hidden'
					},
					{
						fieldLabel: lang['data_otkryitiya'],
						name: 'Analyzer_begDT',
						allowBlank:false,
						xtype: 'swdatefield'
					},
					{
						fieldLabel: lang['data_zakryitiya'],
						name: 'Analyzer_endDT',
						allowBlank:true,
						xtype: 'swdatefield'
					}]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'Analyzer_id'},
				{name: 'Analyzer_Name'},
				{name: 'Analyzer_Code'},
				{name: 'AnalyzerModel_id'},
				{name: 'MedService_id'},
				{name: 'Analyzer_begDT'},
				{name: 'Analyzer_endDT'}
			]),
			url: '/?c=Analyzer&m=save'
		});
		Ext.apply(this, {
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
		sw.Promed.swAnalyzerEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('AnalyzerEditForm').getForm();
	}
});