/**
 * swMedServiceLinkEditWindow - окно редактирования "Связь между службами"
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
sw.Promed.swMedServiceLinkEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['svyaz_mejdu_slujbami'],
	layout: 'border',
	id: 'MedServiceLinkEditWindow',
	modal: true,
	shim: false,
	width: 550,
	height: 150,
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
						that.findById('MedServiceLinkEditForm').getFirstInvalidEl().focus(true);
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
		var params = new Object();
		params.action = that.action;
		this.form.findField('MedService_id').enable();
		this.form.findField('MedService_lid').enable();
		this.form.submit({
			params: params,
			failure: function(result_form, action)
			{
				that.fixCombo();
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
				that.callback(that.owner, action.result.MedServiceLink_id);
				that.hide();
			}
		});
	},
	show: function() {
		var that = this;
		sw.Promed.swMedServiceLinkEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.MedServiceLink_id = null;
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
		if ( arguments[0].MedServiceLink_id ) {
			this.MedServiceLink_id = arguments[0].MedServiceLink_id;
		}
		
		/*arguments[0].MedService_lid = arguments[0].MedService_id;
		delete(arguments[0].MedService_id);*/
		
		this.MedServiceType_SysNick = arguments[0].MedServiceType_SysNick || 'lab';
		
		if (this.MedServiceType_SysNick == 'reglab' && arguments[0].MedService_lid) {
			that.form.findField('MedService_lid').params.MedService_lid = arguments[0].MedService_lid;
			delete(arguments[0].MedService_lid);
		} else {
			that.form.findField('MedService_lid').params.MedService_lid = null;
		}
		
		if (arguments[0].MedService_id) {
			var MedService_id = arguments[0].MedService_id;
			that.fixCombo = function () {
				that.form.findField('MedService_id').setValue(MedService_id);
				that.form.findField('MedService_id').disable();
				that.form.findField('MedService_lid').enable();
			}
		} else if (arguments[0].MedService_lid) {
			var MedService_lid = arguments[0].MedService_lid;
			that.fixCombo = function () {
				that.form.findField('MedService_lid').setValue(MedService_lid);
				that.form.findField('MedService_lid').disable();
				that.form.findField('MedService_id').enable();
			}
		} else if (this.MedServiceType_SysNick != 'reglab') {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() {
				that.hide();
			});
			return false;
		} else {
			that.fixCombo = function () {
				that.form.findField('MedService_lid').enable();
				that.form.findField('MedService_id').enable();
			}
		}
		
		this.MedServiceLinkType_id = arguments[0].MedServiceLinkType_id || 1;
		
		this.form.reset();
		this.form.findField('MedServiceLinkType_id').setValue(this.MedServiceLinkType_id);
		switch(this.MedServiceLinkType_id) {
			case 2:
				that.form.findField('MedService_id').setFieldLabel(lang['registratsionnaya_slujba']);
				that.form.findField('MedService_id').params.MedServiceType_SysNick = 'reglab';
				that.form.findField('MedService_lid').params.MedServiceType_SysNick = 'lab';

				break;
			case 14:
				that.form.findField('MedService_id').setFieldLabel('Операционный блок:');
				that.form.findField('MedService_id').params.MedServiceType_SysNick = 'oper_block';
				that.form.findField('MedService_lid').setFieldLabel('Анестезиология:');
				that.form.findField('MedService_lid').params.MedServiceType_SysNick = 'anesthes';

				break;
			case 1: 
			default:
				that.form.findField('MedService_id').setFieldLabel(lang['punkt_zabora_biomateriala']);
				that.form.findField('MedService_id').params.MedServiceType_SysNick = 'pzm';
				that.form.findField('MedService_lid').params.MedServiceType_SysNick = 'lab';
				break;
		}	
		
		
		that.form.findField('MedService_lid').params.NotLinkedMedService_id = null;
		that.form.findField('MedService_id').params.NotLinkedMedService_id = null;
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		switch (arguments[0].action) {
			case 'add':
				that.setTitle(lang['svyaz_mejdu_slujbami'] + ': ' + lang['dobavlenie']);
				if (Ext.isEmpty(MedService_id)) {
					that.form.findField('MedService_id').params.NotLinkedMedService_id = MedService_lid;
				} else {
					that.form.findField('MedService_lid').params.NotLinkedMedService_id = MedService_id;					
				}				
				that.form.findField('MedService_id').getStore().load(
					{
						callback: function (){
							that.fixCombo();
						}
					}
				);
				that.form.findField('MedService_lid').getStore().load(
					{
						callback: function (){
							that.fixCombo();
						}
					}
				);
				loadMask.hide();
				break;
			case 'edit':
			case 'view':
				if (that.action == 'edit') {
					that.setTitle(lang['svyaz_mejdu_slujbami'] + ': ' + lang['redaktirovanie']);
				} else {
					that.setTitle(lang['svyaz_mejdu_slujbami'] + ': ' + lang['prosmotr']);
				}
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						that.hide();
					},
					params:{
						MedServiceLink_id: that.MedServiceLink_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) { return false}
						that.form.setValues(result[0]);
						that.form.findField('MedService_id').getStore().load(
							{
								callback: function (){
									that.form.findField('MedService_id').setValue(result[0].MedService_id);
									that.fixCombo();
								}
							}
						);
						that.form.findField('MedService_lid').getStore().load(
							{
								callback: function (){
									that.form.findField('MedService_lid').setValue(result[0].MedService_lid);
									that.fixCombo();
								}
							}
						);
						loadMask.hide();
					},
					url:'/?c=MedServiceLink&m=load'
				});
				break;
		}
	},
	initComponent: function() {
		var win = this;
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'MedServiceLinkEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: false,
				labelWidth: 180,
				collapsible: true,
				region: 'north',
				url:'/?c=MedServiceLink&m=save',
				items: [{
					name: 'MedServiceLink_id',
					xtype: 'hidden',
					value: 0
				},
					{
						name: 'MedServiceLinkType_id',
						xtype: 'hidden',
						value: 1
					},
					{
						fieldLabel: lang['punkt_zabora_biomateriala'],
						hiddenName: 'MedService_id',
						xtype: 'swmedservicecombo',
						params:{
							MedServiceType_SysNick: 'pzm',
							order: 'lpu',
							isClose: 1,
							ARMType: win.ARMType
						}
					},
					{
						fieldLabel: lang['laboratoriya'],
						hiddenName: 'MedService_lid',
						xtype: 'swmedservicecombo',
						params:{
							MedServiceType_SysNick: 'lab',
							order: 'lpu'
						}
					}
				]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MedServiceLink_id'},
				{name: 'MedServiceLinkType_id'},
				{name: 'MedService_id'},
				{name: 'MedService_lid'}
			]),
			url: '/?c=MedServiceLink&m=save'
		});
		Ext.apply(this, {
			layout: 'border',
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
		sw.Promed.swMedServiceLinkEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('MedServiceLinkEditForm').getForm();
	}
});