/**
 * ufa_GetReportInterval - диалог установки дат начала и конца периода для чего-нибудь 
 *
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Muskat Boris (bob@npk-progress.com)
 * @version			31.10.2017
 * C:\Zend\Promed\jscore\Forms\Common\ufa\ufa_GetReportInterval.js
 */


sw.Promed.ufa_GetReportInterval = Ext.extend(sw.Promed.BaseForm, {
	id: 'ufa_GetReportInterval',
	width: 450,
	autoHeight: true,
	modal: true,

	action: 'view',
	callback: Ext.emptyFn,

	show: function() {
   		sw.Promed.ufa_GetReportInterval.superclass.show.apply(this, arguments);

	//	var win = this;
	//	var form = this.FormPanel.getForm();
                
        this.setTitle('Интервал'); //     (lang['obslujivaemoe_otdelenie_redaktirovanie']);

        if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		
		this.findById('GRI_BeginDate').setValue(getGlobalOptions().date);
		this.findById('GRI_EndDate').setValue(getGlobalOptions().date);
	},
        
        doSave: function() {
            
            var pdata = { 
				BeginDate: this.findById('GRI_BeginDate').getValue(),
				EndDate: this.findById('GRI_EndDate').getValue()
            };
        //    console.log('BOB_Object_pdata=',pdata);  //BOB - 17.03.2017 
            this.callback(pdata);
            

        },
        
//        doHelp: function() {
//        
//        		$.ajax({
//			mode: "abort",
//			type: "post",
//			async: false,
//			url: '/?c=ReanimatRegister&m=doHelp',
////			data: { 
//////				PersonRegister_id: selected_record.data.PersonRegister_id,   //BOB - 23.01.2017
////				ReanimatRegister_id: selected_record.data.ReanimatRegister_id,
////				ReanimatRegister_disDate: pdata.ReanimatRegister_disDate.dateFormat('Y-m-d'),
////				PersonRegisterOutCause_id: pdata.PersonRegisterOutCause_id,
////				MedPersonal_did: win.userMedStaffFact.MedPersonal_id,
////				Lpu_did: win.userMedStaffFact.Lpu_id
////			},
//			success: function(response) {
////				var params = Ext.util.JSON.decode(response);
////				
////				console.log('BOB_params1=',params); 
//				alert('Ok');
//			}, 
//			error: function() {
//				alert("При обработке запроса на сервере произошла ошибка!");
//			} 
//		});	
//
//        
//        
//        },

	initComponent: function() {
            
    //        	var win = this;
  
  
		//                
            
            
		this.FormPanel = new Ext.form.FormPanel({
		//	bodyBorder: false,
		//	border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'GRI_ufa_GetReportIntervalForm',
		//	url: '/?c=Attribute&m=saveAttribute',
			//bodyStyle: 'padding: 10px 20px;',
			labelAlign: 'right',
			labelWidth: 100,			
			items: [
				
				
				{
					xtype: 'panel',
					layout:'column',
					border: false,
					items:[
						{
							layout:'form',
							border: false,
							items:[
								{
									allowBlank: false,
									fieldLabel: lang['data_nachala'],
									id: 'GRI_BeginDate',
									name: 'GRI_BeginDate',
									xtype: 'swdatefield',
									plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
									maxValue: getGlobalOptions().date
							   }
							]
						},
						{
							layout:'form',
							border: false,
							items:[
								{
									allowBlank: false,
									fieldLabel: lang['data_kontsa'],
									id: 'GRI_EndDate',
									name: 'GRI_EndDate',
									xtype: 'swdatefield',
									plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
									maxValue: getGlobalOptions().date
								} 
							]
						}
					]
				}
            ]
		});
           
            
		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [
				{
					text: lang['vyibrat'],
					id: 'TRFFPSW_ButtonSave',
					tooltip: lang['vyibrat'],
					iconCls: 'save16',
					handler: function()
					{
                                            this.doSave();
					}.createDelegate(this)
				}, 
//                                {
//					text: 'DO',
//					id: 'TRFFPSW_ButtonDO',
//					tooltip: langs('DO'),
//					iconCls: 'save16',
//					handler: function()
//					{
//                                            this.doHelp();  //BOB - 17.03.2018
//					}.createDelegate(this)
//				},
                                {
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {                                            
                                            this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'TRFFPSW_CancelButton',
					text: lang['otmenit']
				}]
		});
            
        sw.Promed.ufa_GetReportInterval.superclass.initComponent.apply(this, arguments);

	}



});

