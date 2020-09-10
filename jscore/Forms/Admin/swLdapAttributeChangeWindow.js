/**
* swLdapAttributeChangeWindow - форма для функционала, способного сломать пользователей в LDAP.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Common
* @access		public
* @copyright	Copyright (c) 2013 Swan Ltd.
* @author		Dmitry Vlasenko
* @version		25.02.2013
*/
/*NO PARSE JSON*/
sw.Promed.swLdapAttributeChangeWindow = Ext.extend(sw.Promed.BaseForm, {
	width : 500,
	resizable: false,
	autoHeight: true,
	border : false,
	plain : true,
	id: 'swLdapAttributeChangeWindow',
	show: function() {
		sw.Promed.swLdapAttributeChangeWindow.superclass.show.apply(this, arguments);
		this.center();
		this.formPanel.getForm().reset();
	},
	title: lang['zamena_atributa_v_ldap'],
	initComponent: function() {
		var win = this;
		
		this.formPanel = new Ext.form.FormPanel({
			bodyStyle: 'padding: 5px',
			url: '/?c=User&m=ldapAttributeChange',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 150,
			items:
			[{
				name: 'attribute',
				fieldLabel: lang['atribut'],
				allowBlank: false,
				xtype: 'textfield'
			}, {
				name: 'oldValue',
				fieldLabel: lang['staroe_znachenie'],
				allowBlank: false,
				xtype: 'textfield'
			}, {
				name: 'newValue',
				fieldLabel: lang['novoe_znachenie'],
				allowBlank: false,
				xtype: 'textfield'
			}]
		});
		Ext.apply(this, {
			buttonAlign : "left",
			buttons : 
				  [{
					text : "Заменить атрибут",
					handler : function()
					{
						win.doSave();
					}
				  },
				  {
					text: "-"
				  },
				  HelpButton(this, -1),
				  {
					text : lang['zakryit'],
					iconCls: 'close16',
					handler : function(button, event) {
						win.hide();
					}
				  }],					
			items:
			[
				this.formPanel
			]
		});
		sw.Promed.swLdapAttributeChangeWindow.superclass.initComponent.apply(this, arguments);
	},
	doSave: function()
	{
		var win = this;
		
		this.formStatus = 'save';

		var base_form = this.formPanel.getForm();
		var form = this.formPanel;


		if ( !base_form.isValid() ) {
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
		
		var attribute = base_form.findField('attribute').getValue();
		var oldValue = base_form.findField('oldValue').getValue();
		var newValue = base_form.findField('newValue').getValue();
		
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' )
				{
					win.getLoadMask(lang['vyipolnyaetsya_zamena_atributa']).show();
					base_form.submit(
					{
						failure: function()
						{
							win.getLoadMask().hide();
						},
						success: function()
						{
							win.getLoadMask().hide();
							sw.swMsg.alert(lang['vnimanie'],lang['zamena_atributa_proizvedena_uspeshno']);
						}.createDelegate(this)
					});
				}
			},
			msg: lang['vyi_uverenyi_chto_namerenyi_proizvesti_zamenu_atributa']+attribute+lang['so_znacheniem'] + oldValue + lang['na_znachenie'] + newValue + lang['po_vsem_polzovatelyam_v_ldap_vnimanie_vashe_soglasie_prived�t_k_neobratimyim_izmeneniyam_v_ldap'],
			title: lang['podtverjdenie']
		});
	}
});