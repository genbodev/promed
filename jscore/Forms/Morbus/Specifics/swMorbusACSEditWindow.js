sw.Promed.swMorbusACSEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	width : 700,
	height : 500,
	modal: true,
	closeAction :'hide',
	onCancel: Ext.emptyFn,
	action:'edit',
	id:"swMorbusACSEditWindow",
	doSave: function() 
	{
		if ( this.formStatus == 'save' ) {
			return false;
		}
		
		var win = this;
		this.formStatus = 'save';
		var form = this.FormPanel;
		var base_form = form.getForm();
		var params = new Object();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		
		base_form.submit({
		params:params,
		
		failure: function(result_form, action) 
		{
			win.formStatus = 'edit';
			
			loadMask.hide();
			if (action.result) 
			{
				if (action.result[0].Error_Code)
				{
					Ext.Msg.alert(lang['oshibka_#']+action.result[0].Error_Code, action.result[0].Error_Msg);
				}
			}
		},
		success: function(result_form, action) 
		{
			win.formStatus = 'edit';
			loadMask.hide();
			if (!action.result) 
			{
				return false;
			}
			Ext.getCmp('swMorbusACSWindow').HospGrid.getGrid().getStore().reload();
			win.hide();
		}
		});
	},
	setDisDateLimit: function() {
		var base_form = this.FormPanel.getForm();
		var minValue = base_form.findField('Morbus_setDT').getValue();
		
		base_form.findField('Morbus_disDT').setMinValue(minValue);
		
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		this.FormPanel.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	show: function(){
		sw.Promed.swMorbusACSEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0]) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
			return false;
		}
		var curWin = this;
		this.action = 'edit';
		this.formStatus = 'edit';
		this.callback = Ext.emptyFn;
		if(arguments[0].action){
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
			
		}
		var base_form = this.FormPanel.getForm();
		base_form.reset();
		base_form.setValues(arguments[0]);
		var params = {};
		switch(this.action){
			case "add":
				curWin.setTitle(lang['gospitalizatsiya_dobavlenie']);
				this.setFieldsDisabled(false);
				break;
			case "edit":
			case "view":
				var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
				loadMask.show();
				params.MorbusACS_id = base_form.findField('MorbusACS_id').getValue();
				if(this.action=="edit"){
					curWin.setTitle(lang['gospitalizatsiya_redaktirovanie']);
					this.setFieldsDisabled(false);
				}else{
					curWin.setTitle(lang['gospitalizatsiya_prosmotr']);
					this.setFieldsDisabled(true);
				}
				var diag_id =  base_form.findField('Diag_id')
				var diag_did =  base_form.findField('Diag_did')
				base_form.load({
					url: "/?c=MorbusACS&m=loadMorbusACSEditWindow",
					params: params,
					success: function (form, action)
					{
						loadMask.hide();
						var result = Ext.util.JSON.decode(action.response.responseText);
						if ( result[0].success != undefined && !result[0].success ){
							Ext.Msg.alert("Ошибка", result[0].Error_Msg);
						}
						diag_id.getStore().load({
						callback: function() {
							diag_id.getStore().each(function(record) {
								if ( record.get('Diag_id') == result[0].Diag_id ) {
									diag_id.fireEvent('select', diag_id, record, 0);
								}
							});
						},
						params: { where: "where Diag_id = " + result[0].Diag_id }
					});
					diag_did.getStore().load({
						callback: function() {
							diag_did.getStore().each(function(record) {
								if ( record.get('Diag_id') == result[0].Diag_did ) {
									diag_did.fireEvent('select', diag_did, record, 0);
								}
							});
						},
						params: { where: "where Diag_id = " + result[0].Diag_did }
					});

						curWin.setDisDateLimit();
					},
					failure: function (form, action)
					{
						loadMask.hide();
						Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
					}
				})
				loadMask.hide();
				break;
		}
		
;

			
		
		
	},
	initComponent: function() 
	{
		var win = this;
		this.FormPanel = new Ext.form.FormPanel(
		{	
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'MorbusForm',
			labelAlign: 'right',
			labelWidth: 180,
			layout: 'form',
			region: 'center',
			url: "/?c=MorbusACS&m=saveMorbusACSEditWindow",
			items: [
				 {
				   name: 'Morbus_id',
                   value: null,
                   xtype: 'hidden'
                },
                {
				   name: 'MorbusACS_id',
                   value: null,
                   xtype: 'hidden'
                },
				{
				   name: 'Person_id',
                   value: null,
                   xtype: 'hidden'
                },
				{
					allowBlank:false,
					fieldLabel: lang['data_postupleniya'],
					name: 'Morbus_setDT',
					hiddenName: 'Morbus_setDT',
					plugins: [
						new Ext.ux.InputTextMask('99.99.9999', false)
					],
					listeners: {
						'change': function(combo, newValue, oldValue) {
							win.setDisDateLimit();
						}
					},
					xtype: 'swdatefield'
					
				},
				{
					fieldLabel: lang['data_vyipiski'],
					name: 'Morbus_disDT',
					hiddenName: 'Morbus_disDT',
					plugins: [
						new Ext.ux.InputTextMask('99.99.9999', false)
					],
					xtype: 'swdatefield'
					
				},{
						xtype: 'textfield',
						anchor: '100%',
						hiddenName: 'MorbusACS_TimeDesease',
						name: 'MorbusACS_TimeDesease',
						fieldLabel: lang['vremya_ot_nachala_zabolevaniya']
						
				},{
						xtype: 'swyesnocombo',
						hiddenName: 'MorbusACS_isST',
						name: 'MorbusACS_isST',
						fieldLabel: lang['podyem_segmenta_st']
						
				},{
						fieldLabel: lang['kem_dostavlen'],
						hiddenName: 'PrehospArrive_id',
						name: 'PrehospArrive_id',
						xtype: 'swprehosparrivecombo'
				},{
						xtype: 'swyesnocombo',
						hiddenName: 'MorbusACS_isTrombPrehosp',
						name: 'MorbusACS_isTrombPrehosp',
						fieldLabel: lang['dogospitalnyiy_trombolizis']
						
				},{
						xtype: 'swyesnocombo',
						hiddenName: 'MorbusACS_isTrombStac',
						name: 'MorbusACS_isTrombStac',
						fieldLabel: lang['trombolizis_v_statsionare']
						
				},{
						xtype: 'swdiagcombo',
						name: 'Diag_id',
						hiddenName: 'Diag_id',
						fieldLabel: lang['diagnoz_napravitelnyiy']
						
				},{
						xtype: 'swdiagcombo',
						name: 'Diag_did',
						hiddenName: 'Diag_did',
						fieldLabel: lang['klinicheskiy_diagnoz']
						
				},{
						xtype: 'swyesnocombo',
						hiddenName: 'MorbusACS_isCoronary',
						name: 'MorbusACS_isCoronary',
						fieldLabel: lang['koronaroangiografiya']
						
				},{
						xtype: 'swyesnocombo',
						hiddenName: 'MorbusACS_isTransderm',
						name: 'MorbusACS_isTransderm',
						fieldLabel: lang['chrezkojnoe_koronarnoe_vmeshatelstvo']
						
				},{
						xtype: 'textfield',
						anchor: '100%',
						hiddenName: 'MorbusACS_Result',
						name: 'MorbusACS_Result',
						fieldLabel: lang['ishod_zabolevaniya']
						
				},{
						xtype: 'swyesnocombo',
						hiddenName: 'MorbusACS_isPso',
						name: 'MorbusACS_isPso',
						fieldLabel: lang['perevod_iz_pso']
						
				},{
						xtype: 'swyesnocombo',
						name: 'MorbusACS_isLpu',
						hiddenName: 'MorbusACS_isLpu',
						fieldLabel: lang['perevod_iz_drugogo_lpu']
						
				},{
						xtype: 'swyesnocombo',
						name: 'MorbusACS_isTinaki',
						hiddenName: 'MorbusACS_isTinaki',
						fieldLabel: lang['napravlenie_v_tinaki']
						
				},{
						xtype: 'swyesnocombo',
						name: 'MorbusACS_isFCSSH',
						hiddenName: 'MorbusACS_isFCSSH',
						fieldLabel: lang['napravlenie_v_ftsssh']
						
				},{
						xtype: 'textfield',
						anchor: '100%',
						name: 'MorbusACS_Comment',
						hiddenName: 'MorbusACS_Comment',
						fieldLabel: lang['primechanie']
						
				}
				
				
			],
			reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{ name: 'Morbus_id' },
					{ name: 'Morbus_setDT' },
					{ name: 'Morbus_disDT' },
					{ name: 'MorbusACS_TimeDesease' },
					{ name: 'MorbusACS_isST' },
					{ name: 'PrehospArrive_id' },
					{ name: 'MorbusACS_isTrombPrehosp' },
					{ name: 'MorbusACS_isTrombStac' },
					{ name: 'Diag_id' },
					{ name: 'Diag_did' },
					{ name: 'MorbusACS_isCoronary' },
					{ name: 'MorbusACS_isTransderm' },
					{ name: 'MorbusACS_Result' },
					{ name: 'MorbusACS_isPso' },
					{ name: 'MorbusACS_isLpu' },
					{ name: 'MorbusACS_isTinaki' },
					{ name: 'MorbusACS_isFCSSH' },
					{ name: 'MorbusACS_Comment' },
				])
				
		});
		
	Ext.apply(this, 
		{
			buttons: 
			[{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
			items: [this.FormPanel]
		});
	sw.Promed.swMorbusACSEditWindow.superclass.initComponent.apply(this, arguments);
	}
});