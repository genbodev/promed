/**
 * swAnalyzerTestWorksheetTypeEditWindow - окно редактирования "Связь тестов с типами рабочего списка"
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
sw.Promed.swAnalyzerTestWorksheetTypeEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['dobavlenie_testa_v_tip_rabochego_spiska'],
	layout: 'border',
	id: 'AnalyzerTestWorksheetTypeEditWindow',
	modal: true,
	shim: false,
	width: 300,
	height: 130,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners: {
		hide: function () {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSave: function () {
		var that = this;
		if (!this.form.isValid()) {
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function () {
						that.findById('AnalyzerTestWorksheetTypeEditForm').getFirstInvalidEl().focus(true);
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
	submit: function () {
		var that = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = {};
		params.action = that.action;
		this.form.submit({
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
				that.callback(that.owner, action.result.AnalyzerTestWorksheetType_id);
				that.hide();
			}
		});
	},
	show: function () {
		var that = this;
		sw.Promed.swAnalyzerTestWorksheetTypeEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.AnalyzerTestWorksheetType_id = null;
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
		if (arguments[0].AnalyzerTestWorksheetType_id) {
			this.AnalyzerTestWorksheetType_id = arguments[0].AnalyzerTestWorksheetType_id;
		}
		this.form.reset();
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg: lang['zagruzka']});
		loadMask.show();
		switch (arguments[0].action) {
			case 'add':
				if (arguments[0].AnalyzerWorksheetType_id) {
					this.form.findField('AnalyzerWorksheetType_id').setValue(arguments[0].AnalyzerWorksheetType_id);
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function () {
						that.hide();
						loadMask.hide();
					});
					return false;
				}
				that.form.findField('AnalyzerTest_id').getStore().load({
					params: {
						AnalyzerWorksheetType_id: arguments[0].AnalyzerWorksheetType_id
					},
					callback: function (){
						loadMask.hide();
					}
				});
				break;
			case 'edit':
			case 'view':
				Ext.Ajax.request({
					failure: function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						that.hide();
					},
					params: {
						AnalyzerTestWorksheetType_id: that.AnalyzerTestWorksheetType_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) {
							return false
						}
						that.form.setValues(result[0]);
						loadMask.hide();
						return true;
					},
					url: '/?c=AnalyzerTestWorksheetType&m=load'
				});
				break;
		}
		return true;
	},
	initComponent: function () {
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [
				{
					xtype: 'form',
					autoHeight: true,
					id: 'AnalyzerTestWorksheetTypeEditForm',
					style: 'margin-bottom: 0.5em;',
					bodyStyle: 'background:#DFE8F6;padding:5px;',
					border: true,
					labelWidth: 50,
					collapsible: true,
					region: 'north',
					url: '/?c=AnalyzerTestWorksheetType&m=save',
					items: [
						{
							name: 'AnalyzerTestWorksheetType_id',
							xtype: 'hidden',
							value: 0
						},
						{
							fieldLabel: lang['test'],
							hiddenName: 'AnalyzerTest_id',
							xtype: 'swanalyzertestcombo',
							allowBlank: false,
							width: 250
						},
						{
							name: 'AnalyzerWorksheetType_id',
							xtype: 'hidden'
						}
					]
				}
			],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'AnalyzerTestWorksheetType_id'},
				{name: 'AnalyzerTest_id'},
				{name: 'WorksheetType_id'}
			]),
			url: '/?c=AnalyzerTestWorksheetType&m=save'
		});
		Ext.apply(this, {
			layout: 'border',
			buttons: [
				{
					handler: function () {
						this.ownerCt.doSave();
					},
					iconCls: 'save16',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				{
					handler: function () {
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}
			],
			items: [form]
		});
		sw.Promed.swAnalyzerTestWorksheetTypeEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('AnalyzerTestWorksheetTypeEditForm').getForm();
	}
});