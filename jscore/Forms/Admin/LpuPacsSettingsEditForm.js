/**
 * swLpuPacsSettingsEditForm - окно просмотра и редактирования настроек ПАКС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright © 2013 Swan Ltd.
 * @author       Tokarev Sergey 
 * @version      22.02.2012
 */

      

sw.Promed.swLpuPacsSettingsEditForm = Ext.extend(sw.Promed.BaseForm,{
	title:lang['nastroyki_pacs'],
	id: 'LpuPacsSettingsEditForm',
	objectSrc: '/jscore/Forms/Admin/LpuPacsSettingsEditForm',
	layout: 'border',
	//layout: 'form',
	maximizable: false,
	shim: false,
	modal: true,
	resizable: false,
	height: 265,
	callback: Ext.emptyFn,
	//autoHeight: true,
	show: function(){
		//sw.Promed.swLpuPacsSettingsEditForm
		sw.Promed.swLpuPacsSettingsEditForm.superclass.show.apply(this, arguments);
		var form = this;
		var bf = this.FormPanel.getForm();
		bf.reset();
		
		if (arguments[0].action){
			this.action = arguments[0].action;
		}

		if (arguments[0].callback && (typeof arguments[0].callback == 'function')) 
			{
				this.callback = arguments[0].callback;
			}
		
		switch (this.action)
		{
			case 'add' :				
				form.setTitle(lang['nastroyki_pacs_dobavlenie']);	
				bf.findField('LpuSection_id').setValue(arguments[0].LpuSection_id);
				break;
				
			case 'edit': 				
				form.setTitle(lang['nastroyki_pacs_redaktirovanie']);
				bf.setValues(arguments[0]);
				var tempip = arguments[0].LpuPacs_ip.split('.');
				for(var i=0; i<tempip.length; i++) 
					{
						var num =  Math.ceil(tempip[i] / 10);
						//alert(num);
						if (num <2)
						{
							tempip[i] +='__';
							continue;
						}
						if (num < 9) 
						{
							tempip[i] +='_';
							continue;
						}
						
					};
				tempip = tempip.join('.');
				bf.findField('pacs-settings-ip').setValue(tempip);       
				break;		
		}	
	},
	doSave: function(){
		var bf = this.FormPanel.getForm();
		var form = this.FormPanel;

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
				var params = new Object();

			    var aetitle = bf.findField('pacs-settings-ae').getValue();

				if (aetitle.length == 0)
					{
						sw.swMsg.alert(lang['soobschenie'], lang['vvedite_znachenie_ae'] );
						return false;
					}					
					
				var desc = bf.findField('pacs-settings-title').getValue();				
				
				var port = bf.findField('pacs-settings-port').getValue().split('_').join('');
				if (port.length == 0)
					{
						sw.swMsg.alert(lang['soobschenie'], lang['vvedite_parametryi_porta'] );
						return false;
					}				
				
				var wadoport = bf.findField('LpuPacs_wadoPort').getValue().split('_').join('');;
				
				var pacsip = bf.findField('pacs-settings-ip').getValue().split('_').join('');
				
				var chpacsip = pacsip.split('.');
				
				if (chpacsip.length == 4){
				for(var i=0; i<chpacsip.length; i++) 
				{					
					if (chpacsip[i]>256)
					{
						sw.swMsg.alert(lang['soobschenie'], lang['znachenie_ip_adresa_doljno_byit_menshe_255'] );
						return false;
					}
					if (chpacsip[i]=='')
					{
						sw.swMsg.alert(lang['soobschenie'], lang['znachenie_segmenta_ip_doljno_byit_ot_0_do_255'] );
						return false;
					}
				}
				}
				else
					{
						sw.swMsg.alert(lang['soobschenie'], lang['vvedite_korektnyiy_ip'] );
						return false;
					}
				
				params['LpuPacs_aetitle'] = bf.findField('pacs-settings-ae').getValue();
				params['LpuPacs_desc'] = bf.findField('pacs-settings-title').getValue();				
				params['LpuPacs_port'] = port;
				params['LpuPacs_ip'] = pacsip;
				params['LpuSection_id'] = bf.findField('LpuSection_id').getValue();
				params['LpuPacs_wadoPort'] = wadoport;
				
				if (this.action == 'edit')
				{	
					params['LpuPacs_id'] = bf.findField('LpuPacs_id').getValue();
				}

				var parentObj = this;
				
				Ext.Ajax.request({
						url: C_SAVE_PACSSET,
						params: params,
						callback: function(options,success,response){
							parentObj.callback(this.owner,1);
							parentObj.hide();
						}.createDelegate(this)
				});
				
			}
	},
	initComponent: function(){	
           this.FormPanel = new sw.Promed.FormPanel(
                {
                        id: 'pacs-settings-panel',
                        hidden: false,
                        region: 'center',
                        items:
                        [
						{
							name: 'LpuSection_id',
							value: '',
							xtype: 'hidden'
						},
						{
							name: 'LpuPacs_id',
							value: '',
							xtype: 'hidden'
						},
                        {
                            fieldLabel: 'AE',
                            id: 'pacs-settings-ae',
							name: 'LpuPacs_aetitle',
                            allowBlank: true,
                            disabled: false,
                            xtype: 'textfield',
                            tabIndex: 1100,
                            width: 550
                        },
						{
                            fieldLabel: lang['opisanie'],
                            id: 'pacs-settings-title',
							name: 'LpuPacs_desc',
                            allowBlank: true,
                            disabled: false,
                            xtype: 'textfield',
                            tabIndex: 1100,
                            width: 550
                        },
                        {
                            fieldLabel: 'IP',
                            id: 'pacs-settings-ip',
							name: 'LpuPacs_ip',
                            allowBlank: true,
                            disabled: false,
                            xtype: 'textfield',
                            plugins: [ new Ext.ux.InputTextMask('999.999.999.999', false) ],
                            tabIndex: 1100,
                            width: 550
							
							
							/*
							,
							listeners: {
								'render': function(c) {
								  c.getEl().on('keyup', function() {
									alert('you changed the text of this input field');
									console.log(c);
								  });
								}
							  }
							  */
                        }, 
                        {
                            fieldLabel : lang['port'],
                            xtype: 'descfield',
                            id: 'pacs-settings-port',
							name: 'LpuPacs_port',
                            allowBlank: true,
                            disabled: false,
                            xtype: 'textfield',
                            plugins: [ new Ext.ux.InputTextMask('99999', false) ],
                            tabIndex: 1100,
                            width: 550
                        },
						{
                            fieldLabel : lang['port_wado'],
                            xtype: 'descfield',
                            id: 'pacs-settings-wadoPort',
							name: 'LpuPacs_wadoPort',
                            allowBlank: true,
                            disabled: false,
                            xtype: 'textfield',
                            plugins: [ new Ext.ux.InputTextMask('99999', false) ],
                            tabIndex: 1100,
                            width: 550
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
						iconCls: 'save16',
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
                
		sw.Promed.swLpuPacsSettingsEditForm.superclass.initComponent.apply(this, arguments);
	}
        
        
                                   
});