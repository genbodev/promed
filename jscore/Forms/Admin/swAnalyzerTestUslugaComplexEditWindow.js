/**
 * swAnalyzerTestUslugaComplexEditWindow - окно редактирования "Связь тестов с услугами"
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
sw.Promed.swAnalyzerTestUslugaComplexEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight:false,
	objectName:'swAnalyzerTestUslugaComplexEditWindow',
	objectSrc:'/jscore/Forms/Admin/swAnalyzerTestUslugaComplexEditWindow.js',
	title:lang['svyaz_testov_s_uslugami'],
	layout:'border',
	id:'AnalyzerTestUslugaComplexEditWindow',
	modal:true,
	shim:false,
	width:600,
	height:130,
	resizable:false,
	maximizable:false,
	maximized:false,
	listeners:{
		hide:function () {
			this.onHide();
		}
	},
	onHide:Ext.emptyFn,
	doSave:function () {
		var that = this;
		if (!this.form.isValid()) {
			sw.swMsg.show(
				{
					buttons:Ext.Msg.OK,
					fn:function () {
						that.findById('AnalyzerTestUslugaComplexEditForm').getFirstInvalidEl().focus(true);
					},
					icon:Ext.Msg.WARNING,
					msg:ERR_INVFIELDS_MSG,
					title:ERR_INVFIELDS_TIT
				});
			return false;
		}
		this.submit();
		return true;
	},
	submit:function () {
		var that = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg:"Подождите, идет сохранение..."});
		loadMask.show();
		var params = new Object();
		params.action = that.action;
		this.form.submit({
			params:params,
			failure:function (result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#'] + action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success:function (result_form, action) {
				loadMask.hide();
				that.callback(that.owner, action.result.AnalyzerTestUslugaComplex_id);
				that.hide();
			}
		});
	},
	show:function () {
		var that = this;
		sw.Promed.swAnalyzerTestUslugaComplexEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.AnalyzerTestUslugaComplex_id = null;
		if (!arguments[0]) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function () {
				that.hide();
			});
			return false;
		}
		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}
		if (arguments[0].callback && typeof arguments[0].callback == 'function') {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].owner) {
			this.owner = arguments[0].owner;
		}
		if (arguments[0].AnalyzerTestUslugaComplex_id) {
			this.AnalyzerTestUslugaComplex_id = arguments[0].AnalyzerTestUslugaComplex_id;
		}
		if ( undefined != arguments[0].AnalyzerTest_id ) {
			this.AnalyzerTest_id = arguments[0].AnalyzerTest_id;
		} else {
			if ('add' == this.action) {
				sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_obyazatelnyiy_parametr_-_analyzertest_id'], function() { that.hide(); });
			}
		}
		this.form.reset();
		that.form.findField('AnalyzerTest_id').setValue(that.AnalyzerTest_id);
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		switch (arguments[0].action) {
			case 'add':
				//получаю список доступных при добавлении категорий с помощью метода контроллера loadAlowedUslugaCategory_SysNicks
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						that.hide();
					},
					params:{
						AnalyzerTest_id:that.AnalyzerTest_id
					},
					success:function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						var t = [];
						result.forEach(function (el){t.push(el.UslugaCategory_SysNick)});
						that.form.findField('UslugaComplex_id').setUslugaCategoryList(t);
						loadMask.hide();
					},
					url:'/?c=AnalyzerTestUslugaComplex&m=loadAlowedUslugaCategory_SysNicks'
				});
				that.form.findField('UslugaComplex_id').getStore().load({});
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
						AnalyzerTestUslugaComplex_id:that.AnalyzerTestUslugaComplex_id
					},
					success:function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) {
							return false;
						}
						that.form.findField('UslugaComplex_id').setUslugaCategoryList([result[0]['UslugaCategory_SysNick']]);
						that.form.setValues(result[0]);
						that.form.findField('UslugaComplex_id').getStore().load({
							callback: function() {
								if ( that.form.findField('UslugaComplex_id').getStore().getCount() > 0 ) {
									that.form.findField('UslugaComplex_id').setValue(result[0].UslugaComplex_id);
								}
								else {
									that.form.findField('UslugaComplex_id').clearValue();
								}
							}.createDelegate(this),
							params: {
								UslugaComplex_id: result[0].UslugaComplex_id
							}
						});
						that.AnalyzerTest_id = result[0].AnalyzerTest_id;
						loadMask.hide();
					},
					url:'/?c=AnalyzerTestUslugaComplex&m=load'
				});
				break;
		}
	},
	initComponent:function () {
		var form = new Ext.Panel({
			autoScroll:true,
			bodyBorder:false,
			bodyStyle:'padding: 5px 5px 0',
			border:false,
			frame:true,
			region:'center',
			labelAlign:'right',
			items:[
				{
					xtype:'form',
					autoHeight:true,
					id:'AnalyzerTestUslugaComplexEditForm',
					style:'margin-bottom: 0.5em;',
					bodyStyle:'background:#DFE8F6;padding:5px;',
					border:true,
					labelWidth:50,
					collapsible:true,
					region:'north',
					url:'/?c=AnalyzerTestUslugaComplex&m=save',
					items:[
						{
							name:'AnalyzerTestUslugaComplex_id',
							xtype:'hidden',
							value:0
						},
						{
							name:'AnalyzerTest_id',
							xtype:'hidden',
							value:null
						},
						{
							fieldLabel:lang['usluga'],
							hiddenName:'UslugaComplex_id',
							to: 'EvnUslugaPar',
							listWidth:600,
							width:500,
							xtype:'swuslugacomplexnewcombo'
						}
					]
				}
			],
			reader:new Ext.data.JsonReader({
				success:Ext.emptyFn
			}, [
				{name:'AnalyzerTestUslugaComplex_id'},
				{name:'AnalyzerTest_id'},
				{name:'UslugaComplex_id'},
				{name:'AnalyzerTestUslugaComplex_Deleted'}
			]),
			url:'/?c=AnalyzerTestUslugaComplex&m=save'
		});
		Ext.apply(this, {
			layout:'border',
			buttons:[
				{
					handler:function () {
						this.ownerCt.doSave();
					},
					iconCls:'save16',
					text:BTN_FRMSAVE
				},
				{
					text:'-'
				},
				HelpButton(this, 0),
				//todo проставить табиндексы
				{
					handler:function () {
						this.ownerCt.hide();
					},
					iconCls:'cancel16',
					text:BTN_FRMCANCEL
				}
			],
			items:[form]
		});
		sw.Promed.swAnalyzerTestUslugaComplexEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('AnalyzerTestUslugaComplexEditForm').getForm();
	}
});