/**
 * swAnalyzerWorksheetTypeEditWindow - окно редактирования "Тип рабочих списков"
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
sw.Promed.swAnalyzerWorksheetTypeEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['tip_rabochih_spiskov'],
	layout: 'border',
	id: 'AnalyzerWorksheetTypeEditWindow',
	modal: true,
	shim: false,
	width: 500,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners: {
		hide: function () {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	gridAddClick: function (gridId) {
		var that = this;
		var AnalyzerWorksheetType_id = that.form.findField('AnalyzerWorksheetType_id').getValue();
		var addition = function (AnalyzerWorksheetType_id) {
			var grid = that.findById(gridId);
			var AnalyzerModel_id = that.form.findField('AnalyzerModel_id').getValue();
			var p = {
				AnalyzerWorksheetType_id:AnalyzerWorksheetType_id,
				action:'add',
				callback: function (){
					var AnalyzerWorksheetType_id = that.form.findField('AnalyzerWorksheetType_id').getValue();
					grid.loadData({globalFilters:{AnalyzerWorksheetType_id:AnalyzerWorksheetType_id}});
				}
			};
			getWnd(grid.editformclassname).show(p);
		};
		if (AnalyzerWorksheetType_id > 0) {
			addition(AnalyzerWorksheetType_id);
		} else {
			this.doSave(addition);
		}
	},
	deleteGridRecord: function (objectName) {
		var that = this;
		var grid = this.findById(objectName + 'Grid').getGrid();
		if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(objectName+'_id')) {
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId/*, text, obj*/) {
				if (buttonId == 'yes') {
					var loadMask = new Ext.LoadMask(that.getEl(), {msg:lang['udalenie']});
					loadMask.show();
					var p = {};
					p[objectName + '_id'] = record.get(objectName+'_id');
					Ext.Ajax.request({
						callback:function (options, success, response) {
							loadMask.hide();
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.success == false) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['pri_udalenii_proizoshla_oshibka']);
								}
								else {
									grid.getStore().remove(record);
								}
								if (grid.getStore().getCount() > 0) {
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_kartyi_vyizova_voznikli_oshibki']);
							}
						},
						params: p,
						url:'/?c=' + objectName + '&m=delete'
					});
				}
			},
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		});
		return true;
	},
	doSave: function (callback) {
		var that = this;
		if (!this.form.isValid()) {
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function () {
						that.findById('AnalyzerWorksheetTypeEditForm').getFirstInvalidEl().focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}
		this.submit(callback);
		return true;
	},
	submit: function (callback) {
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
				that.form.findField('AnalyzerWorksheetType_id').setValue(action.result.AnalyzerWorksheetType_id);
				loadMask.hide();
				if (undefined == callback) {
					that.hide();
					that.callback(that.owner, action.result.AnalyzerWorksheetType_id);
				} else {
					callback(action.result.AnalyzerWorksheetType_id);
				}
			}
		});
	},
	show: function () {
		var that = this;
		sw.Promed.swAnalyzerWorksheetTypeEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.AnalyzerWorksheetType_id = null;
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
		if (arguments[0].AnalyzerWorksheetType_id) {
			this.AnalyzerWorksheetType_id = arguments[0].AnalyzerWorksheetType_id;
		}
		this.form.reset();
		var loadMask = new Ext.LoadMask(this.body, {msg: lang['zagruzka']});
		loadMask.show();
		switch (arguments[0].action) {
			case 'add':
				if (arguments[0].AnalyzerModel_id){
					this.form.findField('AnalyzerModel_id').setValue(arguments[0].AnalyzerModel_id);
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function () {
						loadMask.hide();
						that.hide();
					});
					return false;
				}
				that.grid.removeAll();
				that.grid.addEmptyRecord(that.grid.getGrid().getStore());
				loadMask.hide();
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
						AnalyzerWorksheetType_id: that.AnalyzerWorksheetType_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) {
							return false;
						}
						that.form.setValues(result[0]);
						that.grid.loadData({globalFilters:{AnalyzerWorksheetType_id:result[0].AnalyzerWorksheetType_id}});
						loadMask.hide();
						return true;
					},
					url: '/?c=AnalyzerWorksheetType&m=load'
				});
				break;
		}
		return true;
	},
	initComponent: function () {
		var that = this;
		this.grid = new sw.Promed.ViewFrame({
			actions: [
				{
					name:'action_add',
					handler: function (){
						that.gridAddClick('AnalyzerTestWorksheetTypeGrid');
					}
				},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', hidden: true},
				{
					name: 'action_delete',
					handler: function (){
						that.deleteGridRecord('AnalyzerTestWorksheetType');
					}
				},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=AnalyzerTestWorksheetType&m=loadList',
			region: 'center',
			object: 'AnalyzerTestWorksheetType',
			editformclassname: 'swAnalyzerTestWorksheetTypeEditWindow',
			id: 'AnalyzerTestWorksheetTypeGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'AnalyzerTestWorksheetType_id', type: 'int', header: 'ID', key: true},
				{name: 'AnalyzerTest_id_Code', type: 'string', header: lang['kod_testa'], width: 50},
				{name: 'AnalyzerTest_id_Name', type: 'string', header: lang['nazvanie_testa'], width: 120, id: 'autoexpand'},
				{name: 'AnalyzerTest_id', type: 'int', hidden: true},
				{name: 'AnalyzerWorksheetType_id_Name', type: 'string', header: lang['tip_rabochego_spiska'], width: 120, hidden: true},
				{name: 'AnalyzerWorksheetType_id', type: 'int', hidden: true}
			],
			title: lang['testyi_v_rabochem_spiske'],
			toolbar: true
		});
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			region: 'north',
			labelAlign: 'right',
			height: 75,
			items: [
				{
					xtype: 'form',
					autoHeight: true,
					id: 'AnalyzerWorksheetTypeEditForm',
					bodyStyle: 'background:#DFE8F6;',
					border: true,
					labelWidth: 100,
					collapsible: true,
					region: 'north',
					url: '/?c=AnalyzerWorksheetType&m=save',
					items: [
						{
							name: 'AnalyzerWorksheetType_id',
							xtype: 'hidden',
							value: 0
						},
						{
							fieldLabel: lang['kod'],
							name: 'AnalyzerWorksheetType_Code',
							allowBlank: false,
							xtype: 'textfield',
							width: 50
						},
						{
							fieldLabel: lang['naimenovanie'],
							name: 'AnalyzerWorksheetType_Name',
							allowBlank: false,
							xtype: 'textfield',
							width: 250
						},
						{
							name: 'AnalyzerModel_id',
							xtype: 'hidden'
						}
					]
				}
			],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'AnalyzerWorksheetType_id'},
				{name: 'AnalyzerWorksheetType_Code'},
				{name: 'AnalyzerWorksheetType_Name'},
				{name: 'AnalyzerModel_id'}
			]),
			url: '/?c=AnalyzerWorksheetType&m=save'
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
				HelpButton(this, 0),
				//todo проставить табиндексы
				{
					handler: function () {
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}
			],
			items: [
				form,
				this.grid
			]
		});
		sw.Promed.swAnalyzerWorksheetTypeEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('AnalyzerWorksheetTypeEditForm').getForm();
	}
});