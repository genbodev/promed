/**
 * ufa_RegisterOutCaseSelectWindow - окно выбора причины исключения из регистра 
 *
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Muskat Boris (bob@npk-progress.com)
 * @version			23.10.2017
 * C:\Zend\Promed\jscore\Forms\Common\ufa\ufa_RegisterOutCaseSelectWindow.js
 */

/*NO PARSE JSON*/



sw.Promed.ufa_RegisterOutCaseSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'ufa_RegisterOutCaseSelectWindow',
	width: 580,
	autoHeight: true,
	modal: true,

	action: 'view',
	callback: Ext.emptyFn,

	show: function() {
   		sw.Promed.ufa_RegisterOutCaseSelectWindow.superclass.show.apply(this, arguments);

		var win = this;
		var form = win.FormPanel.getForm();
                
        win.setTitle('Причина исключения из регистра'); //     (lang['obslujivaemoe_otdelenie_redaktirovanie']);

        if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		//		this.onPersonSelect = arguments[0].onSelect;
 
        //        this.Server_id = arguments[0].Server_id;
        //        this.PersonEvn_id = arguments[0].PersonEvn_id;
        this.Person_id = arguments[0].Person_id;
//		this.PersonRegister_id = arguments[0].PersonRegister_id;      //BOB - 23.01.2018
		this.RegisterType_SysNick = arguments[0].RegisterType_SysNick;    //BOB - 23.01.2018   "Person"  отрезал
		
		this.findById('ROCSW_ReanimatRegister_disDate').setValue(getGlobalOptions().date);  //BOB - 23.01.2018
		

		$.ajax({
			mode: "abort",
			type: "post",
			async: false,
			url: '/?c=ReanimatRegister&m=getRegisterOutCaseType',
			success: function(response) {
				var ROCSW_NSI = Ext.util.JSON.decode(response);
				
				//загрузка справочника исходов реанимационного периода
				var RegisterType_SysNick_List = [];
				if (win.RegisterType_SysNick === 'reanimat')   //BOB - 23.01.2018
					RegisterType_SysNick_List = ['Death','Out'];  //,'Recovery'
				var Datas =  [];
				var FirstValue = 0;
				for (var i in ROCSW_NSI) {
					if ((ROCSW_NSI[i].PersonRegisterOutCause_SysNick) && (ROCSW_NSI[i].PersonRegisterOutCause_SysNick.inlist(RegisterType_SysNick_List))){
						Datas[i]= [ ROCSW_NSI[i].PersonRegisterOutCause_Name,  ROCSW_NSI[i].PersonRegisterOutCause_SysNick, ROCSW_NSI[i].PersonRegisterOutCause_id];
						if (FirstValue === 0) FirstValue = ROCSW_NSI[i].PersonRegisterOutCause_id;
					}
				}
				win.findById('ROCSW_RegisterOutCaseSelect').getStore().loadData(Datas);
				win.findById('ROCSW_RegisterOutCaseSelect').setValue(FirstValue);
				
			}, 
			error: function() {
				alert("При обработке запроса на сервере произошла ошибка!");
			} 
		});	
				


             
	},
        
    doSave: function() {
            
            var pdata = { 
				PersonRegisterOutCause_id: this.findById('ROCSW_RegisterOutCaseSelect').getValue(),
				ReanimatRegister_disDate: this.findById('ROCSW_ReanimatRegister_disDate').getValue()   //BOB - 23.01.2018
            };
        //    console.log('BOB_Object_pdata=',pdata);  //BOB - 17.03.2017 
            this.callback(pdata);
            

    },

	initComponent: function() {
            
            	var win = this;
  
  
		//                
            
            
		this.FormPanel = new Ext.form.FormPanel({
		//	bodyBorder: false,
		//	border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'ROCSW_ufa_RegisterOutCaseSelectWindowForm',
		//	url: '/?c=Attribute&m=saveAttribute',
			//bodyStyle: 'padding: 10px 20px;',
			labelAlign: 'right',
			labelWidth: 240,
			//border:true,

			items: [
				 {
					allowBlank: false,
					fieldLabel: lang['data_isklyucheniya_iz_registra'],
					id: 'ROCSW_ReanimatRegister_disDate',   //BOB - 23.01.2018
					name: 'ReanimatRegister_disDate',		//BOB - 23.01.2018
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					maxValue: getGlobalOptions().date
				},
				{
					xtype: 'combo',
					allowBlank: false,
					disabled: false,
					id: 'ROCSW_RegisterOutCaseSelect',
					mode:'local',
					listWidth: 240,
					width: 240,
					triggerAction : 'all',
					editable: false,
					displayField:'PersonRegisterOutCause_Name',
					valueField:'PersonRegisterOutCause_id',
					fieldLabel:'Причина исключения',
					labelSeparator: '',
					tpl: '<tpl for="."><div class="x-combo-list-item">'+
						'{PersonRegisterOutCause_Name} '+ '&nbsp;' +
						'</div></tpl>' ,
					store:new Ext.data.SimpleStore(  {           
						fields: [{name:'PersonRegisterOutCause_Name', type:'string'},
								 {name:'PersonRegisterOutCause_SysNick', type:'string'},
								 { name:'PersonRegisterOutCause_id',type:'int'} ]//,
					})
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
				}, {
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
            
                sw.Promed.ufa_RegisterOutCaseSelectWindow.superclass.initComponent.apply(this, arguments);

	}



});

