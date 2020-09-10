/**
 * swUnitLinkEditWindow - окно редактирования связи единиц измерения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      31.01.2014
 * @comment
 */
sw.Promed.swUnitLinkEditWindow = Ext.extend(sw.Promed.BaseForm,	{
	autoHeight: true,
	objectName: 'swUnitLinkEditWindow',
	objectSrc: '/jscore/Forms/Admin/swUnitLinkEditWindow.js',
	title: lang['svyazannyie_znacheniya'],
	layout: 'form',
	id: 'UnitLinkEditWindow',
	modal: true,
	shim: false,
	width: 500,
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
						win.FormPanel.getFirstInvalidEl().focus(true);
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
					win.form.findField('UnitLink_id').setValue(action.result.UnitLink_id);
					loadMask.hide();
					if (undefined == callback) {
						win.callback(win.owner, action.result.UnitLink_id);
						win.hide();
					} else {
						callback(action.result.UnitLink_id);
					}

				}
			});
	},
	filterOkeiCombo: function() {
		var win = this;
		this.form.findField('Okei_id').lastQuery = '';
		this.form.findField('Okei_id').getStore().filterBy(function(rec) {
			if (rec.get('Okei_id').inlist(win.usedOkei_ids) ) {
				return false;
			} else {
				return true;
			}
		});
	},
	filterUnitCombo: function() {
		var win = this;
		this.form.findField('Unit_id').lastQuery = '';
		this.form.findField('Unit_id').getStore().filterBy(function(rec) {
			if (rec.get('Unit_id').inlist(win.usedUnit_ids) ) {
				return false;
			} else {
				return true;
			}
		});
	},
	show: function()
	{
		var win = this;
		sw.Promed.swUnitLinkEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
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
		
		this.obrLink = false;
		if ( arguments[0].obrLink ) {
			this.obrLink = arguments[0].obrLink;
		}
		
		this.usedOkei_ids = new Array();
		this.usedUnit_ids = new Array();
		
		if ( arguments[0].usedOkei_ids ) {
			this.usedOkei_ids = arguments[0].usedOkei_ids;
		}
		if ( arguments[0].usedUnit_ids ) {
			this.usedUnit_ids = arguments[0].usedUnit_ids;
		}
		
		this.form.reset();
		this.form.setValues(arguments[0]);
		
		win.filterOkeiCombo();
		win.filterUnitCombo();
		
		win.form.findField('UnitType_sid').fireEvent('change', win.form.findField('UnitType_sid'), win.form.findField('UnitType_sid').getValue());
		
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		switch (arguments[0].action) {
			case 'add':
				win.setTitle(lang['svyazannyie_znacheniya_dobavlenie']);
				win.enableEdit(true);
				loadMask.hide();
				win.form.findField('UnitType_sid').focus(true);
				break;
			case 'edit':
			case 'view':
				if (win.action == 'edit') {
					win.setTitle(lang['svyazannyie_znacheniya_redaktirovanie']);
					win.enableEdit(true);
				} else {
					win.setTitle(lang['svyazannyie_znacheniya_prosmotr']);
					win.enableEdit(false);
				}
				
				var url = '/?c=UnitSpr&m=loadUnitLink';
				if (this.obrLink) {
					url = '/?c=UnitSpr&m=loadUnitLinkObr';
				}
				
				this.form.load({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						win.hide();
					},
					params:{
						UnitLink_id: win.form.findField('UnitLink_id').getValue()
					},
					success: function (response) {
						loadMask.hide();
						win.form.findField('UnitType_sid').fireEvent('change', win.form.findField('UnitType_sid'), win.form.findField('UnitType_sid').getValue());
						win.form.findField('UnitType_sid').focus(true);
					},
					url: url
				});
				break;
		}
	},
	openRecordEditWindow: function(action) {
		if (!action.inlist(['add','edit','view'])) {
			return;
		}
		var wnd = this;

		var grid = wnd.GridPanel.getGrid();

		var params = new Object();

		var record = grid.getSelectionModel().getSelected();
		if (action.inlist(['edit','view'])) {
			params['UnitLink_id'] = record.get('UnitLink_id');
		}

		params.action = action;

		params.callback = function(data) {
			wnd.GridPanel.ViewActions.action_refresh.execute();
		}.createDelegate(this);

		getWnd(wnd.GridPanel.editformclassname).show(params);
	},
	initComponent: function()
	{
		var win = this;
		
		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			labelAlign: 'right',
			labelWidth: 160,
			items: [
			{
				name: 'UnitLink_id',
				xtype: 'hidden'
			},
			{
				name: 'UnitLink_Fir',
				xtype: 'hidden'
			},
			{
				name: 'UnitType_fid',
				xtype: 'hidden'
			},
			{
				fieldLabel: lang['tip_spravochnika'],
				value: 1,
				hiddenName: 'UnitType_sid',
				listeners: {
					'change': function(combo, newValue) {
						if (newValue == 2) {
							win.form.findField('Okei_id').setAllowBlank(true);
							win.form.findField('Okei_id').clearValue();
							win.form.findField('Okei_id').hideContainer();
							win.form.findField('Unit_id').setAllowBlank(false);
							win.form.findField('Unit_id').showContainer();
						} else {
							win.form.findField('Okei_id').setAllowBlank(false);
							win.form.findField('Okei_id').showContainer();
							win.form.findField('Unit_id').setAllowBlank(true);
							win.form.findField('Unit_id').clearValue();
							win.form.findField('Unit_id').hideContainer();
						}
						
						win.syncShadow();
					}
				},
				allowBlank: false,
				comboSubject: 'UnitType',
				tabindex: TABINDEX_ULEW + 1,
				width: 200,
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: lang['svyazannoe_znachenie'],
				hiddenName: 'Okei_id',
				allowBlank: false,
				comboSubject: 'Okei',
				tabindex: TABINDEX_ULEW + 2,
				width: 200,
				onLoadStore: function() {
					win.filterOkeiCombo();
				},
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: lang['svyazannoe_znachenie'],
				hiddenName: 'Unit_id',
				allowBlank: false,
				prefix:'lis_',
				comboSubject: 'Unit',
				tabindex: TABINDEX_ULEW + 3,
				width: 200,
				onLoadStore: function() {
					win.filterUnitCombo();
				},
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: lang['koeffitsient_perescheta'],
				name: 'UnitLink_UnitConv',
				allowBlank: false,
				width: 100,
				autoCreate: {
					tag: "input",
					type: "text",
					maxLength: "12",
					autocomplete: "off"
				},
				decimalPrecision: 6,
				minValue: 0.000001,
				maxValue: 1000000,
				xtype: 'numberfield'
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'UnitLink_id'},
				{name: 'UnitLink_Fir'},
				{name: 'UnitType_fid'},
				{name: 'Okei_id'},
				{name: 'Unit_id'},
				{name: 'UnitType_sid'},
				{name: 'UnitLink_UnitConv'}
			]),
			url: '/?c=UnitSpr&m=saveUnitLink'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function()
				{
					this.ownerCt.doSave();
				},
				id: 'ULEW_SaveButton',
				iconCls: 'save16',
				tabindex: TABINDEX_ULEW + 20,
				text: BTN_FRMSAVE
			},
			{
				text: '-'
			},
			HelpButton(this, TABINDEX_ULEW + 21),
			{
				handler: function()
				{
					win.hide();
				},
				iconCls: 'cancel16',
				onTabAction: function() {
					win.form.findField('UnitType_sid').focus(true);
				},
				tabindex: TABINDEX_ULEW + 22,
				text: BTN_FRMCANCEL
			}],
			items:[ win.FormPanel ]
		});
		
		sw.Promed.swUnitLinkEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.FormPanel.getForm();
	}
});