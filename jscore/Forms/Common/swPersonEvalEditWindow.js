/**
* swPersonEvalEditWindow - редактирование параметра человека
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dyomin Dmitry
* @version      01.10.2012
*/

sw.Promed.swPersonEvalEditWindow = Ext.extend(sw.Promed.BaseForm, {
	
	modal: true,
	id:'PersonEvalEditWindow',
	width: 700,
	HeightStat:null,
	WeightStat:null,
	autoHeight: true,
	type:null,
	evalId:null,
	onCancel: Ext.emptyFn,
	action:'edit',
	callback: Ext.emptyFn,

	show: function() {
		sw.Promed.swPersonEvalEditWindow.superclass.show.apply(this, arguments);
		var base_form = this.FormPanel.getForm();
		this.formStatus = 'edit';
		var EType = base_form.findField('EvalType_id');
		var win = this;
		this.type = null;
		this.evalId = null;
		this.HeightStat = this.findById('HeightStat');
		this.WeightStat = this.findById('WeightStat');
		base_form.reset();
		if ( arguments[0] )
		{
			if(arguments[0].PersonEval_id){
				if(arguments[0].PersonEval_id.substr(0,12)=='PersonHeight'){
					win.type = 'Height'
				}else if(arguments[0].PersonEval_id.substr(0,12)=='PersonWeight'){
					win.type = 'Weight'
				}
				this.evalId = Number(arguments[0].PersonEval_id.substr(12));
			}
			if(arguments[0].action){
				win.action = arguments[0].action
			}
			if(arguments[0].Person_id){
				base_form.findField('Person_id').setValue(arguments[0].Person_id);
			}
		}
		
		if(this.action == 'add'){
			this.setEvalState(1);
			this.setAbnormFieldState(1);
			EType.setDisabled(false);
			
		}
		if ( this.action != 'add' ) {
			var params = {};
			win.getLoadMask(lang['pojaluysta_podojdite_idet_zagruzka_dannyih_formyi']).show();
			base_form.reset();
			EType.setValue((win.type=='Height')?0:1);
			this.setEvalState(win.type);
			
			EType.setDisabled(true);

			if(win.type!=null){
				params.PersonEval_id = this.evalId;
				params.EvalType = win.type;
				base_form.load({
					failure: function() {
						win.getLoadMask().hide();
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera']);
					}.createDelegate(this),
					url:'/?c=Person&m=getPersonEvalEditWindow',
					params:params,
					success: function(fm,rec,d) {
						if(rec&&rec.response&&rec.response.responseText){
							var response_obj = Ext.util.JSON.decode(rec.response.responseText);
							win.setAbnormFieldState(response_obj[0]['Person'+win.type+'_IsAbnorm']||1)
							base_form.findField(win.type+'Okei_id').setValue(response_obj[0].Okei_id);
							base_form.findField(win.type+'Okei_id').fireEvent('change',base_form.findField(win.type+'Okei_id'),response_obj[0].Okei_id)
							
						}
					}
				})
			}
			win.getLoadMask().hide();
		}
	},
	setAbnormFieldState:function(val){
		var base_form = this.FormPanel.getForm();
		base_form.findField('WeightAbnormType_id').setDisabled(!(val==2));
		base_form.findField('HeightAbnormType_id').setDisabled(!(val==2));
	},
	setEvalState:function(id){
		if(id==null){
			return false;
		}
		var base_form = this.FormPanel.getForm();
		switch(id){
			case 0:
			case 'Height':
				if(this.action=='add'){
					this.setTitle(lang['rost_dobavlenie']);
				}else if(this.action=='edit'){
					this.setTitle(lang['rost_redaktirovanie']);
				}else{
					this.setTitle(lang['rost_prosmotr']);
				}
				this.HeightStat.setVisible(true);
				this.WeightStat.setVisible(false);
				this.HeightStat.setDisabled(false);
				this.WeightStat.setDisabled(true);
				base_form.findField('PersonHeight_Height').setAllowBlank(false);
				base_form.findField('HeightMeasureType_id').setAllowBlank(false);
				base_form.findField('PersonWeight_Weight').setAllowBlank(true);
				base_form.findField('PersonWeight_Weight').setValue('')
				base_form.findField('WeightMeasureType_id').setAllowBlank(true);
				base_form.findField('WeightOkei_id').disable()
				this.type='Height';
				break;
			case 1:
			case 'Weight':
				if(this.action=='add'){
					this.setTitle(lang['ves_dobavlenie']);
				}else if(this.action=='edit'){
					this.setTitle(lang['ves_redaktirovanie']);
				}else{
					this.setTitle(lang['ves_prosmotr']);
				}
				this.HeightStat.setVisible(false);
				this.WeightStat.setVisible(true);
				this.HeightStat.setDisabled(true);
				this.WeightStat.setDisabled(false);
				base_form.findField('PersonHeight_Height').setAllowBlank(true);
				base_form.findField('PersonHeight_Height').setValue('');
				base_form.findField('HeightMeasureType_id').setAllowBlank(true);
				base_form.findField('PersonWeight_Weight').setAllowBlank(false);
				base_form.findField('WeightMeasureType_id').setAllowBlank(false);
				base_form.findField('WeightOkei_id').enable();
				this.type='Weight';
				break;
		}
	},
	doSave: function() {
		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';
		var base_form = this.FormPanel;
		if (!base_form.getForm().isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					//this.formStatus = 'edit';
					base_form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
            this.formStatus = 'edit';
			return false;
		}

        if(base_form.getForm().findField('EvalType_id').getValue() == '1'){ //Если добавляем вес
            var _this = this;
            if(base_form.getForm().findField('PersonWeight_Weight').getValue() == 0){
                sw.swMsg.show({
                    buttons: Ext.Msg.OK,
                    fn: function() {
                        _this.formStatus = 'edit';
                    },
                    icon: Ext.Msg.WARNING,
                    msg: lang['znachenie_pokazatelya_doljno_byit_otlichno_ot_nulya'],
                    title: lang['oshibka_sohraneniya']
                });
                return false;
            }
            switch(base_form.getForm().findField('WeightOkei_id').getValue()){
                case 36: //вес в граммах
                    if(base_form.getForm().findField('PersonWeight_Weight').getValue() > 999999){
                        sw.swMsg.show({
                            buttons: Ext.Msg.OK,
                            fn: function() {
                                _this.formStatus = 'edit';
                            },
                            icon: Ext.Msg.WARNING,
                            msg: lang['vyi_vvodite_znachenie_vesa_v_grammah_znachenie_ne_doljno_byit_bolee_999999'],
                            title: lang['oshibka_sohraneniya']
                        });
                        return false;
                    }
                    break;
                case 37: //вес в килограммах
                    if(base_form.getForm().findField('PersonWeight_Weight').getValue() > 999.999){
                        sw.swMsg.show({
                            buttons: Ext.Msg.OK,
                            fn: function() {
                                _this.formStatus = 'edit';
                            },
                            icon: Ext.Msg.WARNING,
                            msg: lang['vyi_vvodite_znachenie_vesa_v_kilogrammah_znachenie_ne_doljno_byit_bolee_999_999'],
                            title: lang['oshibka_sohraneniya']
                        });
                        return false;
                    }
                    break;

            }
        }
		this.submit();
	},
	submit: function(mode,onlySave) {
		var form = this.FormPanel;
		var win = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		form.getForm().submit({
			params: {
				EvalType:win.type,
				EvalMeasureType_id:form.getForm().findField(win.type+'MeasureType_id').getValue(),
				PersonEval_Value:form.getForm().findField('Person'+win.type+'_'+win.type).getValue(),
				Okei_id:form.getForm().findField(win.type+'Okei_id').getValue(),
				PersonEval_IsAbnorm:form.getForm().findField('Person'+win.type+'_IsAbnorm').getValue()||null,
				EvalAbnormType_id:form.getForm().findField(win.type+'AbnormType_id').getValue()||null
				
			},
			url:'/?c=Person&m=savePersonEvalEditWindow',
			failure: function(result_form, action) {
				loadMask.hide();
				this.formStatus = 'edit';
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				loadMask.hide();
				form.getForm().reset();
				win.hide();
				var grid = Ext.getCmp('PersonEditWindow').PersonEval;
				if (grid)
					grid.refreshRecords(null,0);
			}
		});
	},
	initComponent: function() {
    	
		var win = this;
		
		this.Height ={
			layout:'form',
			id:'HeightStat',
			items:[{
						
				layout: 'form',
				items: [
				{
					allowBlank: false,
					hiddenName:'HeightMeasureType_id',
					name:'HeightMeasureType_id',
					value:3,
					width: 350,
					xtype:'swheightmeasuretypecombo'
				},{
					layout:'column',
					items:[{
						layout:'form',
						items:[{
							xtype: 'numberfield',
							allowNegative: false,
							allowDecimals: true,
							allowBlank: false,
							regex:new RegExp('(^[0-9]{0,3}\.[0-9]{0,2})$'),
							maxValue:299.99,
							width: 165,
							fieldLabel: lang['znachenie_pokazatelya'],
							hiddenName:'PersonHeight_Height',
							name: 'PersonHeight_Height',
							tabIndex: TABINDEX_PEF + 48
										
						}]
					},{
						border: false,
						layout: 'form',
						items: [{
							hideLabel: true,
							disabled:true,
							width: 60,
							value: 2,
							tabIndex: TABINDEX_PEF + 49,
							loadParams: {
								params: {
									where: ' where Okei_id = 2'
								}
							},
							hiddenName: 'HeightOkei_id',
							xtype: 'swokeicombo'
						}]
					}]
				},{
					fieldLabel:lang['otklonenie'],
					name:'PersonHeight_IsAbnorm',
					xtype:'swyesnocombo',
					value:1,
					listeners: {
							'select': function(v,r,id) {
								win.setAbnormFieldState(id)
							}
					}
				},{
					width: 225,
					fieldLabel:lang['tip_otkloneniya'],
					xtype:'swheightabnormtypecombo'
				}]
			} ]
		};
		
		this.Weight = {
			layout:'form',
			id:'WeightStat',
			items:[{
						
				layout: 'form',
				items: [
				{
					allowBlank: false,
					hiddenName:'WeightMeasureType_id',
					name:'WeightMeasureType_id',
					value:3,
					width: 350,
					xtype:'swweightmeasuretypecombo'
				},{
					layout:'column',
					items:[{
						layout:'form',
						items:[{
							xtype: 'numberfield',
							allowNegative: false,
							allowDecimals: true,
                            decimalPrecision: 3,
							allowBlank: false,
							regex:new RegExp('(^[0-9]{0,3}\.[0-9]{0,3})$'),
							maxValue:999.999,
							minValue:0.1,
							width: 165,
							fieldLabel: lang['znachenie_pokazatelya'],
							name: 'PersonWeight_Weight',
							hiddenName: 'PersonWeight_Weight',
                            maxLength: 7,
							tabIndex: TABINDEX_PEF + 48,
                            maxLengthText: lang['maksimalnaya_dlina_etogo_polya_6_simvolov_bez_ucheta_znaka_razdelitelya_v_drobnyih_chislah']
                            }]
					},{
						border: false,
						layout: 'form',
						items: [{
							hideLabel: true,
							disabled:true,
							width: 60,
							value: 37,
							tabIndex: TABINDEX_PEF + 49,
							loadParams: {
								params: {
									where: ' where Okei_id in (36,37)'
								}
							},
                            listeners: {
								select: function(){
									var base_form = this.FormPanel.getForm();
									base_form.findField('PersonWeight_Weight').setValue('');
								}.createDelegate(this),
                                'change': function(c,n,o) {
                                    var base_form = this.FormPanel.getForm();
                                    if(n == 37){
										base_form.findField('PersonWeight_Weight').regex=new RegExp('(^[0-9]{0,3}\.[0-9]{0,3})$');
										base_form.findField('PersonWeight_Weight').maxValue=999.999;
										base_form.findField('PersonWeight_Weight').minValue=0.1;
                                        base_form.findField('PersonWeight_Weight').maxLength = 7;
                                        base_form.findField('PersonWeight_Weight').maxLengthText = lang['maksimalnaya_dlina_etogo_polya_6_simvolov_bez_ucheta_znaka_razdelitelya_v_drobnyih_chislah'];
                                        base_form.findField('PersonWeight_Weight').decimalPrecision = 3;
                                    }
                                    else{
										base_form.findField('PersonWeight_Weight').minValue=1;
                                        base_form.findField('PersonWeight_Weight').regex=new RegExp('[0-9]{0,6}');
										base_form.findField('PersonWeight_Weight').maxValue=999999;
                                        base_form.findField('PersonWeight_Weight').maxLength = 6;
                                        base_form.findField('PersonWeight_Weight').maxLengthText = lang['maksimalnaya_dlina_etogo_polya_6_simvolov'];
                                        base_form.findField('PersonWeight_Weight').decimalPrecision = 0;
									}
                                }.createDelegate(this)
                            },
							hiddenName: 'WeightOkei_id',
							xtype: 'swokeicombo'
						}]
					}]
				},{
					fieldLabel:lang['otklonenie'],
					name:'PersonWeight_IsAbnorm',
					xtype:'swyesnocombo',
					value:1,
					listeners: {
							'select': function(v,r,id) {
								win.setAbnormFieldState(id)
							}
					}
				},{
					fieldLabel:lang['tip_otkloneniya'],
					width: 225,
					xtype:'swweightabnormtypecombo'
				}]
			} ]
		};
		
		
		this.FormPanel = new Ext.form.FormPanel({
			height:150,
			bodyStyle: 'padding: 5px',
			buttonAlign: 'left',
			frame: true,
			id: 'PersEvalEditForm',
			labelAlign: 'right',
			labelWidth: 180,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
			{
				name: 'Person_id'
			},
			{
				name: 'Evn_id'
			},

			{
				name: 'PersonEval_id'
			},

			{
				name: 'HeightAbnormType_id'
			},

			{
				name: 'PersonHeight_Height'
			},

			{
				name: 'PersonWeight_Weight'
			},

			{
				name: 'WeightOkei_id'
			},
			{
				name: 'HeightOkei_id'
			},

			{
				name: 'PersonHeight_IsAbnorm'
			},

			{
				name: 'PersonWeight_IsAbnorm'
			},

			{
				name: 'WeightAbnormType_id'
			},

			{
				name: 'WeightMeasureType_id'
			},

			{
				name: 'HeightMeasureType_id'
			},
			{
				name: 'PersonEval_setDT'
			}

			]),
			items: [
			{
				name: 'Person_id',
				xtype: 'hidden'
			},

			{
				name: 'PersonEval_id',
				xtype: 'hidden'
			},

			{
				name: 'Evn_id',
				xtype: 'hidden'
			},

			{
				layout:'column'
				,
				items:[
				{
					
					layout:'form',
					items:[{
							allowBlank: false,
						xtype: 'swevaltypecombo',
						name: 'EvalType_id',
						value: 1,
						listeners: {
							'select': function(v,r,id) {
								win.setEvalState(id)
							}
						}
					}]
				},
				{
					layout:'form',
					items:[{
							allowBlank: false,
							fieldLabel: lang['data_izmereniya'],
							format: 'd.m.Y',
							maxValue: getGlobalOptions().date,
							minValue: getMinBirthDate(),
							setDate:true,
							value:getGlobalOptions().date,
							name: 'PersonEval_setDT',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							tabIndex: TABINDEX_PEF + 3,
							width: 95,
							xtype: 'swdatefield'
						}]
				}
				]
			},this.Weight,this.Height
			
				
			],
			enableKeyEvents: true,
			keys: [{
				alt: true,
				fn: function(inp, e) {
					Ext.getCmp('PersonEvalEditWindow').hide();
				},
				key: [ Ext.EventObject.J ],
				stopEvent: true
			}, {
				alt: true,
				fn: function(inp, e) {
					Ext.getCmp('PersonEvalEditWindow').doSave();
				},
				key: [ Ext.EventObject.C ],
				stopEvent: true
			}]
		});
		
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'ok16',
				tabIndex: TABINDEX_ADDREF + 19,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.FormPanel.getForm().findField('KLAreaStat_idEdit').focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_ADDREF + 20,
				text: BTN_FRMCANCEL
			}],
			items: [
			this.FormPanel
			]
		});
		
		sw.Promed.swPersonEvalEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
