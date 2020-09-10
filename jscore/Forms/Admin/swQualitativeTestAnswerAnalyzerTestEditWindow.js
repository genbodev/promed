/**
 * swQualitativeTestAnswerAnalyzerTestEditWindow - окно редактирования "Соответствия конкретных ответов конкретному качественному тесту"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Alexander Chebukin
 * @version      06.2012
 * @comment
 */
sw.Promed.swQualitativeTestAnswerAnalyzerTestEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	objectName: 'swQualitativeTestAnswerAnalyzerTestEditWindow',
	objectSrc: '/jscore/Forms/Admin/swQualitativeTestAnswerAnalyzerTestEditWindow.js',
	title: lang['variant_otveta'],
	layout: 'form',
	id: 'QualitativeTestAnswerAnalyzerTestEditWindow',
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
						that.findById('QualitativeTestAnswerAnalyzerTestEditForm').getFirstInvalidEl().focus(true);
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
				that.callback(that.owner, action.result.QualitativeTestAnswerAnalyzerTest_id);
				that.hide();
			}
		});
	},
	show: function() {
		var that = this;
		sw.Promed.swQualitativeTestAnswerAnalyzerTestEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.QualitativeTestAnswerAnalyzerTest_id = null;
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
		if ( arguments[0].QualitativeTestAnswerAnalyzerTest_id ) {
			this.QualitativeTestAnswerAnalyzerTest_id = arguments[0].QualitativeTestAnswerAnalyzerTest_id;
		}
		if ( undefined != arguments[0].AnalyzerTest_id ) {
			this.AnalyzerTest_id = arguments[0].AnalyzerTest_id;
		} else {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_obyazatelnyiy_parametr_-_analyzertest_id'], function() { that.hide(); });
		}
		this.form.reset();
		that.form.findField('AnalyzerTest_id').setValue(that.AnalyzerTest_id);
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		switch (arguments[0].action) {
			case 'add':
				loadMask.hide();
				that.form.findField('QualitativeTestAnswerAnalyzerTest_Answer').focus();
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
						QualitativeTestAnswerAnalyzerTest_id: that.QualitativeTestAnswerAnalyzerTest_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) { return false}
						that.form.setValues(result[0]);
						that.AnalyzerTest_id = result[0].AnalyzerTest_id;
						loadMask.hide();
						that.form.findField('QualitativeTestAnswerAnalyzerTest_Answer').focus();
					},
					url:'/?c=QualitativeTestAnswerAnalyzerTest&m=load'
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
				id: 'QualitativeTestAnswerAnalyzerTestEditForm',
				labelAlign: 'right',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;',
				border: true,
				labelWidth: 130,
				collapsible: true,
				region: 'north',
				url:'/?c=QualitativeTestAnswerAnalyzerTest&m=save',
				items: [{
					name: 'QualitativeTestAnswerAnalyzerTest_id',
					xtype: 'hidden',
					value: 0
				},
				{
					fieldLabel: lang['variant_otveta'],
					name: 'QualitativeTestAnswerAnalyzerTest_Answer',
					xtype: 'textfield',
					allowBlank:false,
					width: 400
				},
				{
					fieldLabel: lang['prioritet'],
					name: 'QualitativeTestAnswerAnalyzerTest_SortCode',
					xtype: 'textfield',
					width: 400
				},
				{
					name: 'AnalyzerTest_id',
					xtype: 'hidden',
					value: 0
				}]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'QualitativeTestAnswerAnalyzerTest_id'},
				{name: 'QualitativeTestAnswerAnalyzerTest_Answer'},
				{name: 'AnalyzerTest_id'}
			]),
			url: '/?c=QualitativeTestAnswerAnalyzerTest&m=save'
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
							win.form.findField('QualitativeTestAnswerAnalyzerTest_Answer').focus();
						},
						iconCls: 'cancel16',
						text: BTN_FRMCANCEL
					}],
			items:[form]
		});
		sw.Promed.swQualitativeTestAnswerAnalyzerTestEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('QualitativeTestAnswerAnalyzerTestEditForm').getForm();
	}
});