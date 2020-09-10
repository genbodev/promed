/**
 * swLpuOrgServedEditForm - окно просмотра и редактирования обслуживающих организаций
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright © 2009 Swan Ltd.
 * @author       Khorev Sergey (sergey.khorev@yandex.ru)
 * @version      30.05.2012
 */

sw.Promed.swLpuOrgServedEditForm = Ext.extend(sw.Promed.BaseForm,{
	title:lang['obslujivaemaya_oraganizatsiya'],
	id: 'LpuOrgServedEditForm',
	objectSrc: '/jscore/Forms/Admin/LpuOrgServedEditForm',
	layout: 'border',
	//layout: 'form',
	maximizable: false,
	shim: false,
	modal: true,
	resizable: false,
	height: 265,
	//autoHeight: true,
	show: function(){
		sw.Promed.swLpuOrgServedEditForm.superclass.show.apply(this, arguments);
		var form = this;
		var bf = this.FormPanel.getForm();
		
		if (arguments[0].action)
			this.action = arguments[0].action;
		if (arguments[0].LpuOrgServed_id)
			this.LpuOrgServed_id = arguments[0].LpuOrgServed_id;
		if (arguments[0].Lpu_id)
			this.Lpu_id = arguments[0].Lpu_id;
		this.callback = arguments[0].callback || Ext.emptyFn();
		if (arguments[0].owner)
			this.owner = arguments[0].owner;
		bf.findField('Org_id').clearValue();
		bf.findField('LpuOrgServed_begDate').setValue('');
		bf.findField('LpuOrgServed_endDate').setValue('');
		
		form.buttons[0].show();
		
		switch (this.action)
		{
			case 'add':
				form.setTitle(lang['obslujivaemaya_organizatsiya_dobavlenie']);
				bf.findField('Org_id').enable();
				bf.findField('LpuOrgServed_begDate').enable();
				bf.findField('LpuOrgServed_endDate').enable();
				bf.findField('LpuOrgServiceType_id').enable();
				break;
			case 'edit':
				form.setTitle(lang['obslujivaemaya_organizatsiya_redaktirovanie']);
				bf.findField('Org_id').disable();
				bf.findField('LpuOrgServed_begDate').disable();
				bf.findField('LpuOrgServed_endDate').enable();
				bf.findField('LpuOrgServiceType_id').enable();
				break;
			case 'view':
				form.setTitle(lang['obslujivaemaya_organizatsiya_prosmotr']);
				bf.findField('Org_id').disable();
				bf.findField('LpuOrgServed_begDate').disable();
				bf.findField('LpuOrgServed_endDate').disable();
				bf.findField('LpuOrgServiceType_id').disable();
				form.buttons[0].hide();
				break;
		}
		if (this.action!='add')
		{
			var param = new Object();
			param['LpuOrgServed_id'] = this.LpuOrgServed_id;
			Ext.Ajax.request({
				url: C_GET_CLOS,
				params: param,
				callback: function(options, success, response) {
					if (success) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!Ext.isEmpty(result[0])) { 
							result = result[0]; 
							
							var combo = bf.findField('Org_id');
							combo.getStore().load({
								params: {
									Object:'Org',
									Org_id: result.Org_id,
									Org_Name:''
								},
								callback: function() {
									combo.setValue(result.Org_id);
									combo.focus(true, 500);
									combo.fireEvent('change', combo);
								}
							});
							bf.findField('LpuOrgServed_begDate').setValue(result.LpuOrgServed_begDate);
							bf.findField('LpuOrgServed_endDate').setValue(result.LpuOrgServed_endDate);
							bf.findField('LpuOrgServiceType_id').setValue(result.LpuOrgServiceType_id);
						}
					}
				}.createDelegate(this)
			});
		}
	},
	doSave: function(){
		var bf = this.FormPanel.getForm();
		var form = this.FormPanel;
		
		if (this.action!='view')
		{
			if ( !bf.isValid() ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						form.getFirstInvalidEl().focus(true);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
			else
			{
				if((bf.findField('LpuOrgServed_endDate').getValue())&&(bf.findField('LpuOrgServed_endDate').getValue() < bf.findField('LpuOrgServed_begDate').getValue()))
				{
					alert(lang['data_zakryitiya_ne_doljna_byit_bolshe_datyi_nachala']);
				}
				else
				{
					var params = new Object();
					params['Lpu_id'] = this.Lpu_id;
					params['Org_id'] = bf.findField('Org_id').getValue();
					if (this.action!='add'){params['LpuOrgServed_id'] = this.LpuOrgServed_id;}
					params['LpuOrgServiceType_id'] = bf.findField('LpuOrgServiceType_id').getValue();
					params['LpuOrgServed_begDate'] = bf.findField('LpuOrgServed_begDate').getValue();
					params['LpuOrgServed_endDate'] = bf.findField('LpuOrgServed_endDate').getValue();
					params['pmUser_id'] = getGlobalOptions().pmuser_id;

					Ext.Ajax.request({
						url: C_SAVE_LOS,
						params: params,
						callback: function(options,success,response){
							this.callback(this.owner,1);
							this.hide();
						}.createDelegate(this)
					});
				}
			}
		}
	},
	initComponent: function(){
	
		this.FormPanel = new Ext.form.FormPanel({
				autoHeight: true,
				buttonAlign: 'left',
				frame: true,
				//id: 'OrgSearchForm',
				labelAlign: 'top',
				region: 'center',
				items:
				[
					{
						xtype: 'sworgcombo',
						allowBlank: false,
						editable: false,
						fieldLabel: lang['organizatsiya'],
						triggerAction: 'none',
						anchor: '100%',
						hiddenName: 'Org_id',
						onTrigger1Click: function() {
							if(!this.disabled){
								var ownerWindow = this.ownerCt.ownerCt.ownerCt.ownerCt;
								var combo = this;
								getWnd('swOrgSearchWindow').show({
									object: 'Org_Served',
									onSelect: function(orgData) {
										if ( orgData.Org_id > 0 ) {
											combo.getStore().load({
												params: {
													Object:'Org',
													Org_id: orgData.Org_id,
													Org_Name:''
												},
												callback: function() {
													combo.setValue(orgData.Org_id);
													combo.focus(true, 500);
													combo.fireEvent('change', combo);
												}
											});
										}

										getWnd('swOrgSearchWindow').hide();
									},
									onClose: function() {combo.focus(true, 200)}
								});
						}}
					},
					{
						hiddenName: 'LpuOrgServiceType_id',
						comboSubject: 'LpuOrgServiceType',
						fieldLabel: lang['tip_obslujivaniya'],
						xtype: 'swcommonsprcombo',
						allowBlank:false
					},
					{
						xtype: 'swdatefield',
						allowBlank: false,
						hiddenName: 'LpuOrgServed_begDate',
						fieldLabel: lang['data_nachala'],
						format: 'd.m.Y',
						name: 'LpuOrgServed_begDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
					},
					{
						xtype: 'swdatefield',
						allowBlank: true,
						hiddenName: 'LpuOrgServed_endDate',
						fieldLabel: lang['data_okonchaniya'],
						format: 'd.m.Y',
						name: 'LpuOrgServed_endDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
					}
				]

			});
			
		Ext.apply(this,{
			buttonAlign : "left",
			buttons :
				[
					{
						text : lang['sohranit'],
						id: 'close',
						handler : function()
						{
							this.doSave();
						}.createDelegate(this)
					},
					{
						text: "-"
					},
					HelpButton(this, -1),
					{
						text : lang['otmena'],
						iconCls: 'close16',
						handler : function(button, event) {
							button.ownerCt.hide();
						}
					}
				],
			items: [ this.FormPanel ]



		});
		sw.Promed.swLpuOrgServedEditForm.superclass.initComponent.apply(this, arguments);
	}
});