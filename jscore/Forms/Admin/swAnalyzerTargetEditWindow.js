/**
 * swAnalyzerTargetEditWindow - окно редактирования "Исследование анализатора"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package	  Common
 * @access	   public
 * @copyright	Copyright (c) 2009 Swan Ltd.
 * @author	   Alexander Chebukin
 * @version	  06.2012
 * @comment
 */
sw.Promed.swAnalyzerTargetEditWindow = Ext.extend(sw.Promed.BaseForm,	{
	autoHeight: true,
	objectName: 'swAnalyzerTargetEditWindow',
	objectSrc: '/jscore/Forms/Admin/swAnalyzerTargetEditWindow.js',
	title: lang['issledovanie_analizatora'],
	layout: 'form',
	id: 'AnalyzerTargetEditWindow',
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
	doSave:  function(callback) {
		var win = this;
		if ( !this.form.isValid() )
		{
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						win.findById('AnalyzerTargetEditForm').getFirstInvalidEl().focus(true);
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
	submit: function(callback)
	{
		var win = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = new Object();
		params.action = win.action;
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
					win.form.findField('AnalyzerTest_id').setValue(action.result.AnalyzerTest_id);
					loadMask.hide();
					if (undefined == callback) {
						win.callback(win.owner, action.result.AnalyzerTest_id);
						win.hide();
					} else {
						callback(action.result.AnalyzerTest_id);
					}

				}
			});
	},
	show: function()
	{
		var win = this;
		sw.Promed.swAnalyzerTargetEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.AnalyzerTest_id = null;
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { win.hide(); });
			return false;
		}
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		} else {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_obyazatelnyiy_parametr_-_action'], function() { win.hide(); });
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
		if ( arguments[0].AnalyzerTest_id ) {
			this.AnalyzerTest_id = arguments[0].AnalyzerTest_id;
		}
		
		this.AnalyzerModel_id = arguments[0].AnalyzerModel_id || null;
		this.Analyzer_id = arguments[0].Analyzer_id || null;
		this.ReagentModel_id = arguments[0].ReagentModel_id || null;

		if (Ext.isEmpty(this.AnalyzerModel_id) && Ext.isEmpty(this.Analyzer_id)) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_obyazatelnyie_parametryi_analizator_ili_model_analizatora'], function() { win.hide(); });
		}
		
		if (Ext.isEmpty(this.Analyzer_id)) {
			win.findById('ATAEW_RefValuesSet').hide();
			win.form.findField('UslugaCategory_id').hideContainer();
			win.form.findField('UslugaCategory_id').setAllowBlank(true);
			win.form.findField('AnalyzerTest_begDT').hideContainer();
			win.form.findField('AnalyzerTest_begDT').setAllowBlank(true);
			win.form.findField('AnalyzerTest_endDT').hideContainer();
		} else {
			win.findById('ATAEW_RefValuesSet').show();
			win.form.findField('UslugaCategory_id').showContainer();
			win.form.findField('UslugaCategory_id').setAllowBlank(false);
			win.form.findField('AnalyzerTest_begDT').showContainer();
			win.form.findField('AnalyzerTest_begDT').setAllowBlank(false);
			win.form.findField('AnalyzerTest_endDT').showContainer();
		}
		
		win.syncShadow();
		
		if ( arguments[0].AnalyzerTest_pid ) {
			this.AnalyzerTest_pid = arguments[0].AnalyzerTest_pid;
		} else {
			this.AnalyzerTest_pid = null;
		}
		this.form.reset();
		win.form.findField('AnalyzerModel_id').setValue(win.AnalyzerModel_id);
		win.form.findField('Analyzer_id').setValue(win.Analyzer_id);
		win.form.findField('AnalyzerTest_pid').setValue(win.AnalyzerTest_pid);
		win.form.findField('ReagentModel_id').setValue(win.ReagentModel_id);
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});		loadMask.show();
		
		var uslugacomplex_combo = win.form.findField('UslugaComplex_id');
		if (Ext.isEmpty(this.Analyzer_id)) {
			if ( getRegionNick() == 'kz' ) {
				uslugacomplex_combo.setUslugaCategoryList(['classmedus']);
			}
			else {
				uslugacomplex_combo.setUslugaCategoryList(['gost2011']);
			}
			uslugacomplex_combo.setAllowedUslugaComplexAttributeList(['lab']);
			uslugacomplex_combo.getStore().baseParams.hasLinkWithGost2011 = null;
		} else {
			uslugacomplex_combo.setUslugaCategoryList();
			uslugacomplex_combo.setAllowedUslugaComplexAttributeList(['lab']);
			uslugacomplex_combo.getStore().baseParams.hasLinkWithGost2011 = 1;
			//console.log( frms.swAnalyzerPanel.getGrid().getSelectionModel().getSelected().get('Analyzer_IsUseAutoReg') );
		}
		uslugacomplex_combo.getStore().baseParams.Analyzer_id = this.Analyzer_id;
		switch (arguments[0].action) {
			case 'add':
				win.setTitle(lang['issledovanie_analizatora_dobavlenie']);
				win.enableEdit(true);
				loadMask.hide();
				win.form.findField('UslugaComplex_id').focus(true);
				break;
			case 'edit':
			case 'view':
				if (win.action == 'edit') {
					win.setTitle(lang['issledovanie_analizatora_redaktirovanie']);
					win.enableEdit(true);
				} else {
					win.setTitle(lang['issledovanie_analizatora_prosmotr']);
					win.enableEdit(false);
				}
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						win.hide();
					},
					params:{
						AnalyzerTest_id: win.AnalyzerTest_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) { return false}
						win.form.setValues(result[0]);
						
						var uslugacomplex_id = uslugacomplex_combo.getValue();
						if (!Ext.isEmpty(uslugacomplex_id)) {
							uslugacomplex_combo.getStore().load({
								callback: function() {
									uslugacomplex_combo.getStore().each(function(record) {
										if (record.data.UslugaComplex_id == uslugacomplex_id)
										{
											uslugacomplex_combo.setValue(uslugacomplex_id);
											uslugacomplex_combo.fireEvent('select', uslugacomplex_combo, record, 0);
											uslugacomplex_combo.collapse();
											uslugacomplex_combo.focus(true);
										}
									});
								},
								params: { "UslugaComplex_id": uslugacomplex_id }
							});
						}
						var AnalyzerTest_id = win.form.findField('AnalyzerTest_id').getValue();
						loadMask.hide();
						win.form.findField('UslugaComplex_id').focus(true);
					},
					url:'/?c=AnalyzerTest&m=load'
				});
				break;
		}

		if (getRegionNick() == 'adygeya') {
			Ext.Ajax.request({
				url: '/?c=Analyzer&m=checkIfFromExternalMedService',
				method: 'GET',
				params: {
					'Analyzer_id': win.Analyzer_id
				},
				failure: function () {
				},
				success: function (result) {
					var obj = Ext.util.JSON.decode(result.responseText);
					if (obj[0] == true) {
						uslugacomplex_combo.setUslugaCategoryList(['gost2011']);
						win.form.findField('UslugaCategory_id').setValue(4);
						win.form.findField('UslugaCategory_id').hideContainer();
					}
				}
			});
		}
	},
	openRefValuesSetEditWindow: function(action) {
		var win = this;
		var AnalyzerTest_id = win.form.findField('AnalyzerTest_id').getValue();
							
		var addition = function (AnalyzerTest_id) {
			var p = {
				AnalyzerTest_id: AnalyzerTest_id,
				action: action,
				AnalyzerTest_IsTest: 1,
				onLoadSave: function(RefValuesSet_Name) {
					win.form.findField('RefValuesSet_Name').setValue(RefValuesSet_Name);
				}
			};
			
			getWnd('swRefValuesSetEditWindow').show(p);
		}
		if (AnalyzerTest_id > 0) {
			addition(AnalyzerTest_id);
		} else {
			win.doSave(addition);
		}
	},
	initComponent: function()
	{
		var win = this;
		
		var form = new Ext.Panel({
			autoScroll: true,
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			labelAlign: 'right',
			items: [
				{
				xtype: 'form',
				frame: true,
				labelAlign: 'right',
				autoHeight: true,
				id: 'AnalyzerTargetEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 200,
				collapsible: true,
				url:'/?c=AnalyzerTest&m=save',
				items: [
					{
						name: 'AnalyzerTest_id',
						xtype: 'hidden'
					},
					{
						name: 'UslugaComplexMedService_id',
						xtype: 'hidden'
					},
					{
						name: 'AnalyzerTest_pid',
						xtype: 'hidden'
					},
					{
						name: 'AnalyzerModel_id',
						xtype: 'hidden'
					},
					{
						name: 'ReagentModel_id',
						xtype: 'hidden'
					},
					{
						name: 'Analyzer_id',
						xtype: 'hidden'
					},
					{
						name: 'AnalyzerTest_isTest',
						xtype: 'hidden',
						value: 1
					}, {
						allowBlank: false,
						fieldLabel: lang['kategoriya_uslugi'],
						loadParams: {params: {where: "where UslugaCategory_SysNick in (" + (getRegionNick() == "kz" ? "'classmedus'" : "'gost2011','tfoms','lpu'") + ")"}},
						hiddenName: 'UslugaCategory_id',
						listeners: {
							//#PROMEDWEB-10693
							'render': function(){
								var combo = this;
								if(getRegionNick() == 'vologda') {
									combo.setValue('4'); //ГОСТ
								}
							},
							'select': function (combo, record) {
								win.form.findField('UslugaComplex_id').clearValue();
								win.form.findField('UslugaComplex_id').getStore().removeAll();

								if ( !record ) {
									win.form.findField('UslugaComplex_id').setUslugaCategoryList();
									return false;
								}

								win.form.findField('UslugaComplex_id').setUslugaCategoryList([ record.get('UslugaCategory_SysNick') ]);

								return true;
							}
						},
						listWidth: 400,
						tabIndex: TABINDEX_ATAEW + 0,
						width: 250,
						xtype: 'swuslugacategorycombo'
					}, {
						fieldLabel: lang['usluga'],
						name: 'UslugaComplex_id',
						listeners: {
							
						},
						tabindex: TABINDEX_ATAEW + 0,
						allowBlank:false,
						xtype: 'swuslugacomplexnewcombo',
						to: 'EvnUslugaPar',
						listeners: {
							'change': function() {
								var uslugaName = this.getRawValue();
								var idx = uslugaName.indexOf(' ');
								if ( idx > -1 ) {
									if ( uslugaName.slice( 0, idx ).split('.').length > 2 )
										uslugaName = uslugaName.substr( idx + 1 );
								}
								win.form.findField('AnalyzerTest_Name').setValue( uslugaName );
							}
						},
						showUslugaComplexLpuSection: false,
						listWidth: 450,
						width: 400
					}, {
						fieldLabel: lang['naimenovanie'],
						name: 'AnalyzerTest_Name',
						id: 'ATAEW_AnalyzerTest_Name',
						tabindex: TABINDEX_ATAEW + 0,
						width: 400,
						xtype: 'textfield',
						allowBlank: !getRegionNick().inlist(['ufa'])
					}, {
						fieldLabel: lang['data_nachala'],
						name: 'AnalyzerTest_begDT',
						xtype: 'swdatefield'
					}, {
						fieldLabel: lang['data_okonchaniya'],
						name: 'AnalyzerTest_endDT',
						xtype: 'swdatefield'
					}, {
						layout: 'column',
						id: 'ATAEW_RefValuesSet',
						items:[{
							layout: 'form',
							labelWidth: 200,
							items:[{
								fieldLabel: lang['nabor_referensnyih_znacheniy'],
								name: 'RefValuesSet_Name',
								readOnly: true,
								width: 150,
								xtype: 'textfield'
							}]
						}, {
							layout: 'form',
							bodyStyle: 'margin: 0px 5px;',
							items:[{
								xtype: 'button',
								text: lang['zagruzit_nabor'],
								handler: function ()
								{
									win.openRefValuesSetEditWindow('load');
								}
							}]
						}, {
							layout: 'form',
							items:[{
								xtype: 'button',
								text: lang['sohranit_nabor'],
								handler: function ()
								{
									win.openRefValuesSetEditWindow('save');
								}
							}]
						}]
					}]
				}
			],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'AnalyzerTest_id'},
				{name: 'AnalyzerTest_Name'},
				{name: 'AnalyzerTest_pid'},
				{name: 'AnalyzerTest_isTest'},
				{name: 'AnalyzerModel_id'},
				{name: 'ReagentModel_id'},
				{name: 'Analyzer_id'},
				{name: 'UslugaCategory_id'},
				{name: 'UslugaComplex_id'},
				{name: 'UslugaComplexMedService_id'},
				{name: 'RefValuesSet_Name'},
				{name: 'AnalyzerTest_begDT'},
				{name: 'AnalyzerTest_endDT'}
			]),
			url: '/?c=AnalyzerTest&m=save'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function()
				{
					this.ownerCt.doSave();
				},
				id: 'ATAEW_SaveButton',
				iconCls: 'save16',
				tabindex: TABINDEX_ATAEW + 20,
				text: BTN_FRMSAVE
			},
			{
				text: '-'
			},
			HelpButton(this, TABINDEX_ATAEW + 21),
			{
				handler: function()
				{
					var AnalyzerTest_id = win.form.findField('AnalyzerTest_id').getValue();
					if (('add' == win.action) && (AnalyzerTest_id > 0)) {
						var loadMask = new Ext.LoadMask(win.form.getEl(), {msg:lang['udalenie_testa_modeli_analizatora']});
						loadMask.show();
						Ext.Ajax.request({
							failure:function () {
								sw.swMsg.alert(lang['oshibka_pri_udalenii_testa_modeli_analizatora'], lang['ne_udalos_poluchit_dannyie_s_servera']);
								loadMask.hide();
								win.hide();
							},
							params:{
								AnalyzerTest_id:AnalyzerTest_id
							},
							success:function (response) {
								var result = Ext.util.JSON.decode(response.responseText);
								if (!result || !result.success) {
									sw.swMsg.alert(lang['oshibka_pri_udalenii_testa_modeli_analizatora'], result.Error_Code + ': ' + result.Error_Msg);
								}
								loadMask.hide();
								win.hide();
							},
							url:'/?c=AnalyzerTest&m=delete'
						});
					} else {
						this.ownerCt.hide();
					}
				},
				iconCls: 'cancel16',
				onTabAction: function() {
					win.form.findField('UslugaComplex_id').focus(true);
				},
				tabindex: TABINDEX_ATAEW + 22,
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		
		sw.Promed.swAnalyzerTargetEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('AnalyzerTargetEditForm').getForm();
	}
});