/**
 * swQualitativeTestAnswerReferValueEditWindow - окно редактирования "Соответствия конкретных ответов конкретному референсному значению качественного теста"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @comment
 */
sw.Promed.swQualitativeTestAnswerReferValueEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	objectName: 'swQualitativeTestAnswerReferValueEditWindow',
	objectSrc: '/jscore/Forms/Admin/swQualitativeTestAnswerReferValueEditWindow.js',
	title: lang['variant_normalnogo_znacheniya'],
	layout: 'form',
	id: 'QualitativeTestAnswerReferValueEditWindow',
	modal: true,
	shim: false,
	width:600,
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
						that.findById('QualitativeTestAnswerReferValueEditForm').getFirstInvalidEl().focus(true);
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
		var form = this.form;
		var that = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = new Object();
		params.action = that.action;
		form.submit({
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
				that.callback(that.owner, action.result.QualitativeTestAnswerReferValue_id);
				that.hide();
			}
		});
	},
	show: function() {
		var that = this;
		sw.Promed.swQualitativeTestAnswerReferValueEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.QualitativeTestAnswerReferValue_id = null;
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { that.hide(); });
			return false;
		}
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].QualitativeTestAnswerReferValue_id ) {
			this.QualitativeTestAnswerReferValue_id = arguments[0].QualitativeTestAnswerReferValue_id;
		}
		if ( undefined != arguments[0].AnalyzerTestRefValues_id ) {
			this.AnalyzerTestRefValues_id = arguments[0].AnalyzerTestRefValues_id;
		} else {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_obyazatelnyiy_parametr_-_analyzertestrefvalues_id'], function() { that.hide(); });
		}
		this.form.reset();
		that.form.findField('AnalyzerTestRefValues_id').setValue(that.AnalyzerTestRefValues_id);
		that.form.findField('QualitativeTestAnswerAnalyzerTest_id').getStore().load({
			params: {
				AnalyzerTestRefValues_id: that.AnalyzerTestRefValues_id
			},
			callback: function() {
				that.form.findField('QualitativeTestAnswerAnalyzerTest_id').collapse();
				that.form.findField('QualitativeTestAnswerAnalyzerTest_id').focus(true);
			}
		});
		
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		switch (arguments[0].action) {
			case 'add':
				loadMask.hide();
				that.form.findField('QualitativeTestAnswerAnalyzerTest_id').focus(true);
				break;
			case 'edit':
			case 'view':
				that.form.load({
					failure:function () {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						that.hide();
					},
					params:{
						QualitativeTestAnswerReferValue_id: that.QualitativeTestAnswerReferValue_id
					},
					success: function (response) {
						loadMask.hide();
						that.form.findField('QualitativeTestAnswerAnalyzerTest_id').focus(true);
					},
					url:'/?c=QualitativeTestAnswerReferValue&m=load'
				});
				break;
		}
	},
	initComponent: function() {
		var win = this;
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'QualitativeTestAnswerReferValueEditForm',
				labelAlign: 'right',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;',
				border: true,
				labelWidth: 130,
				collapsible: true,
				region: 'north',
				url:'/?c=QualitativeTestAnswerReferValue&m=save',
				items: [{
					name: 'QualitativeTestAnswerReferValue_id',
					xtype: 'hidden',
					value: 0
				},
				{
					fieldLabel: lang['znachenie'],
					hiddenName: 'QualitativeTestAnswerAnalyzerTest_id',
					xtype: 'swqualitativetestansweranalyzertestcombo',
					allowBlank:false,
					width: 400
				},
				{
					name: 'AnalyzerTestRefValues_id',
					xtype: 'hidden',
					value: 0
				}],
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{name: 'QualitativeTestAnswerReferValue_id'},
					{name: 'QualitativeTestAnswerAnalyzerTest_id'},
					{name: 'AnalyzerTestRefValues_id'}
				])
			}]
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
						onTabAction: function() {
							win.form.findField('QualitativeTestAnswerAnalyzerTest_id').focus();
						},
						iconCls: 'cancel16',
						text: BTN_FRMCANCEL
					}],
			items:[form]
		});
		sw.Promed.swQualitativeTestAnswerReferValueEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('QualitativeTestAnswerReferValueEditForm').getForm();
	}
});