/**
 * swUnitSprEditWindow - окно редактирования единицы измерения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      29.01.2014
 * @comment
 */
sw.Promed.swUnitSprEditWindow = Ext.extend(sw.Promed.BaseForm,	{
	autoHeight: true,
	objectName: 'swUnitSprEditWindow',
	objectSrc: '/jscore/Forms/Admin/swUnitSprEditWindow.js',
	title: lang['edinitsa_izmereniya'],
	layout: 'form',
	id: 'UnitSprEditWindow',
	modal: true,
	shim: false,
	width: 700,
	resizable: false,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSave:  function(options) {
		var win = this;
		if ( !this.form.isValid() )
		{
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						win.FormPanel.getFirstInvalidEl().focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}
		var params = {};
		params.Unit_id = win.form.findField('Unit_id').getValue();
		params.UnitSpr_endDate = win.form.findField('UnitSpr_endDate').getValue();
		
		Ext.Ajax.request({
			failure: function(response, options) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
					sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
				}
			},
			params: params,
			success: function(response, options) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if(!Ext.isEmpty(response_obj[0])){
					sw.swMsg.alert(lang['oshibka'], lang['zakryitie_zapisi_datoy_ranshe']+response_obj[0].begDT+lang['nevozmojno_t_k_suschestvuyut_svyazannyie_zapisi'], 
						function(){win.FormPanel.getForm().findField('UnitSpr_endDate').focus(true);}
					);
					log(response_obj);
				} else {
					win.submit(options);
				}
			},
			url: '/?c=UnitSpr&m=checkUnitSprEndDate'
		});
		//this.submit(options);
		return true;
	},
	submit: function(options)
	{
		var win = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = new Object();
		params.UnitSpr_Code = this.form.findField('UnitSpr_Code').getValue();
		params.UnitType_id = this.form.findField('UnitType_id').getValue();
		params.Unit_begDate = this.form.findField('UnitSpr_begDate').getValue();
		params.Unit_endDate = this.form.findField('UnitSpr_endDate').getValue();
		
		this.form.submit(
			{
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
					win.form.findField('UnitSpr_id').setValue(action.result.UnitSpr_id);
					win.form.findField('Okei_id').setValue(action.result.Okei_id);
					win.form.findField('Unit_id').setValue(action.result.Unit_id);
					loadMask.hide();
					
					win.callback(win.owner, action.result.UnitSpr_id);
					
					if (options && options.callback) {
						win.UnitLinkGrid.loadData({
							params: {
								Okei_id: win.form.findField('Okei_id').getValue(),
								Unit_id: win.form.findField('Unit_id').getValue()
							},
							globalFilters: {
								Okei_id: win.form.findField('Okei_id').getValue(),
								Unit_id: win.form.findField('Unit_id').getValue()
							}
						});
						options.callback(action.result.UnitSpr_id);
					} else {
						win.hide();
					}

				}
			});
	},
	show: function()
	{
		var win = this;
		sw.Promed.swUnitSprEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.UnitSpr_id = null;
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { win.hide(); });
			return false;
		}
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		} else {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_obyazatelnyiy_parametr_-_action'], function() { win.hide(); });
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		
		this.form.reset();
		this.form.setValues(arguments[0]);
		
		this.UnitLinkGrid.removeAll();
		this.UnitLinkGrid.setReadOnly(win.action == 'view');
		
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		switch (arguments[0].action) {
			case 'add':
				win.setTitle(lang['edinitsa_izmereniya_dobavlenie']);
				win.enableEdit(true);
				loadMask.hide();
				win.form.findField('UnitSpr_Code').focus(true);
				break;
			case 'edit':
			case 'view':
				if (win.action == 'edit') {
					win.setTitle(lang['edinitsa_izmereniya_redaktirovanie']);
					win.enableEdit(true);
				} else {
					win.setTitle(lang['edinitsa_izmereniya_prosmotr']);
					win.enableEdit(false);
				}

				this.form.findField('UnitSpr_Code').disable();
				this.form.findField('UnitType_id').disable();
				
				this.UnitLinkGrid.loadData({
					params: {
						Okei_id: win.form.findField('Okei_id').getValue(),
						Unit_id: win.form.findField('Unit_id').getValue()
					},
					globalFilters: {
						Okei_id: win.form.findField('Okei_id').getValue(),
						Unit_id: win.form.findField('Unit_id').getValue()
					}
				});
				
				this.form.load({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						win.hide();
					},
					params:{
						UnitSpr_id: win.form.findField('UnitSpr_id').getValue(),
						Okei_id: win.form.findField('Okei_id').getValue(),
						Unit_id: win.form.findField('Unit_id').getValue()
					},
					success: function (response) {
						loadMask.hide();
						win.form.findField('UnitSpr_Code').focus(true);
						win.form.findField('UnitSpr_begDate').disable();
					},
					url:'/?c=UnitSpr&m=load'
				});
				break;
		}
	},
	openRecordEditWindow: function(action) {
		if (!action.inlist(['add','edit','view'])) {
			return;
		}
		var win = this;

		var grid = win.UnitLinkGrid.getGrid();

		var params = new Object();

		var record = grid.getSelectionModel().getSelected();
		if (action.inlist(['edit','view'])) {
			params['UnitLink_id'] = record.get('UnitLink_id');
		}

		var usedOkei_ids = new Array();
		var usedUnit_ids = new Array();
		
		if (!Ext.isEmpty(win.form.findField('Unit_id').getValue())) {
			params['UnitLink_Fir'] = win.form.findField('Unit_id').getValue();
			usedUnit_ids.push(win.form.findField('Unit_id').getValue());
			params['UnitType_fid'] = 2;
		} else if (!Ext.isEmpty(win.form.findField('Okei_id').getValue())) {
			params['UnitLink_Fir'] = win.form.findField('Okei_id').getValue();
			usedOkei_ids.push(win.form.findField('Okei_id').getValue());
			params['UnitType_fid'] = 1;
		} else {
			win.doSave({ callback: function() { win.openRecordEditWindow(action); } });
			return false;
		}
		
		grid.getStore().each(function(rec) {
			if (action == 'add' || rec.get('UnitLink_id') != params['UnitLink_id']) {
				if (!Ext.isEmpty(rec.get('Okei_id'))) {
					usedOkei_ids.push(rec.get('Okei_id'));
				}
				
				if (!Ext.isEmpty(rec.get('Unit_id'))) {
					usedUnit_ids.push(rec.get('Unit_id'));
				}
			}
		});
		
		params['usedOkei_ids'] = usedOkei_ids;
		params['usedUnit_ids'] = usedUnit_ids;
		
		params.action = action;

		params.callback = function(data) {
			win.UnitLinkGrid.ViewActions.action_refresh.execute();
		}.createDelegate(this);
		
		if (action.inlist(['edit','view']) && record.get('UnitLinkType_id') == 2) {
			params.obrLink = true;
		}
		
		getWnd('swUnitLinkEditWindow').show(params);
	},
	deleteRecord: function() {
		var win = this;
		var question = lang['udalit_svyazannoe_znachenie'];
		var grid = win.UnitLinkGrid.getGrid();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var params = new Object();
					var deleteUrl = '/?c=UnitSpr&m=deleteUnitLink';
					var record = grid.getSelectionModel().getSelected();
					if (record && !Ext.isEmpty(record.get('UnitLink_id'))) {
						params['UnitLink_id'] = record.get('UnitLink_id');
					} else {
						return false;
					}

					win.getLoadMask("Удаление записи...").show();

					Ext.Ajax.request({
						callback:function (options, success, response) {
							win.getLoadMask().hide();
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.success == true) {
									grid.getStore().remove(record);
								}
								if (grid.getStore().getCount() > 0) {
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
							}
						},
						params: params,
						url: deleteUrl
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: lang['vopros']
		});
	},
	initComponent: function()
	{
		var win = this;
		
		this.UnitLinkGrid = new sw.Promed.ViewFrame({
			id: 'USEW_UnitLinkGrid',
			region: 'center',
			height: 250,
			title: lang['svyazannyie_znacheniya'],
			dataUrl: '/?c=UnitSpr&m=loadUnitLinkGrid',
			editformclassname: 'swUnitLinkEditWindow',
			autoLoadData: false,
			stringfields:
				[
					{name: 'UnitLink_id', header: 'ID', key: true},
					{name: 'UnitLinkType_id', header: lang['tip_svyazi_pryamaya_ili_obratnaya'], type: 'int', hidden: true},
					{name: 'UnitType_Name', header: lang['tip_spravochnika'], type: 'string', width: 150},
					{name: 'Okei_id', header: lang['svyazannoe_znachenie'], type: 'int', hidden: true},
					{name: 'Unit_id', header: lang['svyazannoe_znachenie'], type: 'int', hidden: true},
					{name: 'UnitSpr_Name', header: lang['svyazannoe_znachenie'], type: 'string', id: 'autoexpand'},
					{name: 'UnitLink_UnitConv', header: lang['koeffitsient_perescheta'], type: 'float', width: 150}
				],
			actions:
				[
					{name:'action_add', handler: function(){win.openRecordEditWindow('add');}},
					{name:'action_edit', handler: function(){win.openRecordEditWindow('edit');}},
					{name:'action_view', handler: function(){win.openRecordEditWindow('view');}},
					{name:'action_delete', handler: function (){
						win.deleteRecord();
					}},
					{name:'action_refresh'},
					{name:'action_print'}
				]
		});
		
		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			labelAlign: 'right',
			labelWidth: 130,
			items: [
			{
				name: 'UnitSpr_id',
				xtype: 'hidden'
			},
			{
				name: 'Unit_id',
				xtype: 'hidden'
			},
			{
				name: 'Okei_id',
				xtype: 'hidden'
			},
			{
				fieldLabel: lang['kod'],
				name: 'UnitSpr_Code',
				allowBlank: false,
				minValue: 1,
				maxValue: 999,
				autoCreate: {
					tag: "input",
					type: "text",
					maxLength: "3",
					autocomplete: "off"
				},
				tabindex: TABINDEX_USEW + 0,
				width: 200,
				xtype: 'numberfield'
			}, {
				fieldLabel: lang['tip_spravochnika'],
				hiddenName: 'UnitType_id',
				allowBlank: false,
				comboSubject: 'UnitType',
				tabindex: TABINDEX_USEW + 1,
				width: 200,
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: lang['naimenovanie'],
				name: 'UnitSpr_Name',
				allowBlank: false,
				tabindex: TABINDEX_USEW + 2,
				width: 400,
				xtype: 'textfield'
			}, {
				xtype: 'datefield',
				fieldLabel : lang['data_nachala'],
				name: 'UnitSpr_begDate',
				allowBlank: false,
				format : 'd.m.Y',
				tabindex: TABINDEX_USEW + 3,
				width: 120,
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ] 
			}, {
				xtype: 'datefield',
				fieldLabel : lang['data_okonchaniya'],
				name: 'UnitSpr_endDate',
				allowBlank: true,
				format : 'd.m.Y',
				tabindex: TABINDEX_USEW + 4,
				width: 120,
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', true) ] 
			},
				win.UnitLinkGrid
			],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'UnitSpr_id'},
				{name: 'Unit_id'},
				{name: 'Okei_id'},
				{name: 'UnitSpr_Code'},
				{name: 'UnitType_id'},
				{name: 'UnitSpr_Name'},
				{name: 'UnitSpr_begDate'},
				{name: 'UnitSpr_endDate'}
			]),
			url: '/?c=UnitSpr&m=save'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function()
				{
					this.ownerCt.doSave();
				},
				id: 'USEW_SaveButton',
				iconCls: 'save16',
				tabindex: TABINDEX_USEW + 20,
				text: BTN_FRMSAVE
			},
			{
				text: '-'
			},
			HelpButton(this, TABINDEX_USEW + 21),
			{
				handler: function()
				{
					win.hide();
				},
				iconCls: 'cancel16',
				onTabAction: function() {
					win.form.findField('UnitSpr_Code').focus(true);
				},
				tabindex: TABINDEX_USEW + 22,
				text: BTN_FRMCANCEL
			}],
			items:[ win.FormPanel ]
		});
		
		sw.Promed.swUnitSprEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.FormPanel.getForm();
	}
});