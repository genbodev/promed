/**
 * swAnalyzerModelEditWindow - окно редактирования "Модели анализаторов"
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
sw.Promed.swAnalyzerModelEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight:false,
	height:465,
	objectName:'swAnalyzerModelEditWindow',
	objectSrc:'/jscore/Forms/Admin/swAnalyzerModelEditWindow.js', //todo: проверить путь к файлу
	title:lang['model_analizatora'],
	layout:'border',
	id:'AnalyzerModelEditWindow',
	modal:true,
	shim:false,
	width:600,
	resizable:false,
	maximizable:false,
	maximized:false,
	listeners:{
		hide:function () {
			this.onHide();
		}
	},
	onHide:Ext.emptyFn,
	doSave:function (callback) {
		var that = this;
		if (!this.form.isValid()) {
			sw.swMsg.show(
				{
					buttons:Ext.Msg.OK,
					fn:function () {
						that.findById('AnalyzerModelEditForm').getFirstInvalidEl().focus(true);
					},
					icon:Ext.Msg.WARNING,
					msg:ERR_INVFIELDS_MSG,
					title:ERR_INVFIELDS_TIT
				});
			return false;
		}
		this.submit(callback);
		return true;
	},
	submit:function (callback) {
		var that = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg:"Подождите, идет сохранение..."});
		loadMask.show();
		var params = {};
		params.action = that.action;
		that.form.submit({
			params:params,
			failure:function (result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert('Ошибка #' + action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success:function (result_form, action) {
				that.form.findField('AnalyzerModel_id').setValue(action.result.AnalyzerModel_id);
				loadMask.hide();
				if (undefined == callback) {
					that.callback(that.owner, action.result.AnalyzerModel_id);
					that.hide();
				} else {
					callback(action.result.AnalyzerModel_id);
				}
			}
		});
	},
	show:function () {
		var that = this;
		sw.Promed.swAnalyzerModelEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.AnalyzerModel_id = null;
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
		if (arguments[0].AnalyzerModel_id) {
			this.AnalyzerModel_id = arguments[0].AnalyzerModel_id;
		}
		this.form.reset();
		var loadMask = new Ext.LoadMask(this.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		switch (arguments[0].action) {
			case 'add':
				var AnalyzerModel_id = that.form.findField('AnalyzerModel_id').getValue();
				that.findById('AnalyzerRackGrid').loadData({globalFilters:{AnalyzerModel_id:AnalyzerModel_id}});
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
						AnalyzerModel_id:that.AnalyzerModel_id
					},
					success:function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) {
							return false
						}
						that.form.setValues(result[0]);
						var AnalyzerModel_id = that.form.findField('AnalyzerModel_id').getValue();
						that.findById('AnalyzerRackGrid').loadData({globalFilters:{AnalyzerModel_id:AnalyzerModel_id}});
						loadMask.hide();
						return true;
					},
					url:'/?c=AnalyzerModel&m=load'
				});
				break;
		}
		return true;
	},
	deleteAnalyzerRack: function (){
		this.deleteGridRecord('AnalyzerRack');
	},
	deleteGridRecord:function (objectName) {
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
	gridAddClick: function (gridId) {
		var that = this;
		var AnalyzerModel_id = that.form.findField('AnalyzerModel_id').getValue();
		var addition = function (AnalyzerModel_id) {
			var grid = that.findById(gridId);
			var p = {
				AnalyzerModel_id:AnalyzerModel_id,
				action:'add',
				callback: function (){
					var AnalyzerModel_id = that.form.findField('AnalyzerModel_id').getValue();
					grid.loadData({globalFilters:{AnalyzerModel_id:AnalyzerModel_id}});
				}
			};
			getWnd(grid.editformclassname).show(p);
		};
		if (AnalyzerModel_id > 0) {
			addition(AnalyzerModel_id);
		} else {
			this.doSave(addition);
		}
	},
	initComponent:function () {
		var that = this;
		var form = new Ext.Panel({
			autoScroll:true,
			bodyBorder:false,
			bodyStyle:'padding: 5px 5px 0',
			border:false,
			frame:false,
			region:'center',
			labelAlign:'left',
			items:[
				{
					xtype:'form',
					autoHeight:true,
					id:'AnalyzerModelEditForm',
					style:'margin-bottom: 0.5em;',
					bodyStyle:'background:#DFE8F6;padding:5px;',
					border:true,
					labelWidth:150,
					collapsible:true,
					region:'north',
					url:'/?c=AnalyzerModel&m=save',
					items:[
						{
							name:'AnalyzerModel_id',
							xtype:'hidden',
							value:0
						},
						{
							fieldLabel:lang['naimenovanie_modeli'],
							name:'AnalyzerModel_Name',
							allowBlank:false,
							xtype:'textfield',
							width:300
						},
						{
							fieldLabel:lang['korotkoe_naimenovanie'],
							name:'AnalyzerModel_SysNick',
							allowBlank:false,
							xtype:'textfield',
							width:300
						},
						{
							fieldLabel: lang['tip_oborudovaniya'],
							hiddenName: 'FRMOEquipment_id',
							xtype: 'swcommonsprcombo',
							prefix: 'passport_',
							allowBlank: false,
							comboSubject: 'FRMOEquipment',
							width: 300
						},
						{
							fieldLabel:lang['klass_analizatora'],
							hiddenName:'AnalyzerClass_id',
							xtype:'swcommonsprcombo',
							prefix:'lis_',
							allowBlank:true,
							sortField:'AnalyzerClass_Code',
							comboSubject:'AnalyzerClass',
							width:300
						},
						{
							fieldLabel:lang['tip_vzaimodeystviya'],
							hiddenName:'AnalyzerInteractionType_id',
							xtype:'swcommonsprcombo',
							prefix:'lis_',
							allowBlank:false,
							sortField:'AnalyzerInteractionType_Code',
							comboSubject:'AnalyzerInteractionType',
							width:300
						},
						{
							fieldLabel:lang['nalichie_skanera'],
							hiddenName:'AnalyzerModel_IsScaner',
							allowBlank:false,
							xtype:'swyesnocombo',
							width:300
						},
						{
							fieldLabel:lang['tip_vzaimodeystviya_c_rabochimi_spiskami'],
							hiddenName:'AnalyzerWorksheetInteractionType_id',
							xtype:'swcommonsprcombo',
							prefix:'lis_',
							allowBlank:false,
							sortField:'AnalyzerWorksheetInteractionType_Code',
							comboSubject:'AnalyzerWorksheetInteractionType',
							width:300
						}
					]
				},
				new sw.Promed.ViewFrame({
					actions:[
						{
							name:'action_add',
							handler: function (){
								that.gridAddClick('AnalyzerRackGrid');
							}
						},
						{name:'action_edit'},
						{name:'action_view', hidden:true},
						{
							name:'action_delete',
							handler: function (){
								that.deleteAnalyzerRack();
							}
						},
						{name:'action_print', hidden:true}
					],
					autoExpandColumn:'autoexpand',
					autoExpandMin:150,
					autoLoadData:false,
					border:true,
					dataUrl:'/?c=AnalyzerRack&m=loadList',
					height:180,
					region:'north',
					object:'AnalyzerRack',
					editformclassname:'swAnalyzerRackEditWindow',
					id:'AnalyzerRackGrid',
					paging:false,
					style:'margin-bottom: 10px',
					stringfields:[
						{name:'AnalyzerRack_id', type:'int', header:'ID', key:true},
						//{name:'AnalyzerModel_id_Name', type:'string', header:'Модель анализатора', width:120},
						{name:'AnalyzerModel_id', type:'int', hidden:true, isparams:true},
						{name:'AnalyzerRack_DimensionX', type:'float', header:lang['razmernost_po_h'], width:120},
						{name:'AnalyzerRack_DimensionY', type:'float', header:lang['razmernost_po_y'], width:120},
						{name:'AnalyzerRack_IsDefault_Name', type:'string', header:lang['po_umolchaniyu'], width:120},
						{name:'AnalyzerRack_IsDefault', type:'int', hidden:true},
						{name:'AnalyzerRack_Deleted', type:'int', hidden:true}
					],
					title:lang['shtativyi'],
					toolbar:true
				})
			],
			reader:new Ext.data.JsonReader({
				success:Ext.emptyFn
			}, [
				{name:'AnalyzerModel_id'},
				{name:'AnalyzerModel_Name'},
				{name:'AnalyzerModel_SysNick'},
				{name:'FRMOEquipment_id'},
				{name:'AnalyzerClass_id'},
				{name:'AnalyzerInteractionType_id'},
				{name:'AnalyzerModel_IsScaner'},
				{name:'AnalyzerWorksheetInteractionType_id'}
			]),
			url:'/?c=AnalyzerModel&m=save'
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
						var AnalyzerModel_id = that.form.findField('AnalyzerModel_id').getValue();
						if (('add' == that.action) && (AnalyzerModel_id > 0)) {
							var loadMask = new Ext.LoadMask(that.form.getEl(), {msg:lang['udalenie_modeli_analizatora']});
							loadMask.show();
							Ext.Ajax.request({
								failure:function () {
									sw.swMsg.alert(lang['oshibka_pri_udalenii_modeli_analizatora'], lang['ne_udalos_poluchit_dannyie_s_servera']);
									loadMask.hide();
									that.hide();
								},
								params:{
									AnalyzerModel_id:AnalyzerModel_id
								},
								success:function (response) {
									var result = Ext.util.JSON.decode(response.responseText);
									if (!result || !result.success) {
										sw.swMsg.alert(lang['oshibka_pri_udalenii_modeli_analizatora'], result.Error_Code + ': ' + result.Error_Msg);
									}
									loadMask.hide();
									that.hide();
								},
								url:'/?c=AnalyzerModel&m=delete'
							});
						} else {
							this.ownerCt.hide();
						}
					},
					iconCls:'cancel16',
					text:BTN_FRMCANCEL
				}
			],
			items:[form]
		});
		sw.Promed.swAnalyzerModelEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('AnalyzerModelEditForm').getForm();
	}
});