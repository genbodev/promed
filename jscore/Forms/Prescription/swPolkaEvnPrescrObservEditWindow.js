/**
* swPolkaEvnPrescrObservEditWindow - окно добавления/редактирования назначения c типом Наблюдение.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      0.001-14.04.2012
* @comment      Префикс для id компонентов EPROBEF (PolkaEvnPrescrObservEditForm)
*/
/*NO PARSE JSON*/

sw.Promed.swPolkaEvnPrescrObservEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swPolkaEvnPrescrObservEditWindow',
	objectSrc: '/jscore/Forms/Prescription/swPolkaEvnPrescrObservEditWindow.js',

	PrescriptionType_id: 10,
	action: null,
	onHide: Ext.emptyFn,
	callback: Ext.emptyFn,
	autoHeight: true,
	width: 900,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	formStatus: 'edit',
	id: 'PolkaEvnPrescrObservEditWindow',
	layout: 'form',
	listeners: {
		'beforehide': function(win) {
			//
		},
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	maximized: false,
	modal: true,
	plain: true,
	resizable: false,
	keys: [{
		alt: true,
		fn: function(inp, e) {
			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					this.doSave();
				break;

				case Ext.EventObject.J:
					this.hide();
				break;
			}
		}.createDelegate(this),
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		scope: this,
		stopEvent: false
	}],
	doSave: function(options) {
		if ( this.formStatus == 'save' ) {
			return false;
		}

		if ( typeof options != 'object' ) {
			options = new Object();
		}

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = new Object();
		params.parentEvnClass_SysNick = this.parentEvnClass_SysNick;
		params.PrescriptionType_id = this.PrescriptionType_id;
		params.signature = (options.signature)?1:0;

		var observParamTypeList = new Array();
		var observTimeTypeList = new Array();
		
		base_form.findField('ParametrValues1').items.each(
			function(rec){
				if(rec.checked){
					if(rec.inputValue==1){
						observParamTypeList.push(1);
						observParamTypeList.push(2);
					}else{
						observParamTypeList.push(rec.inputValue)
					}
				}
			}
		);
		base_form.findField('ParametrValues2').items.each(function(rec){if(rec.checked&&rec.inputValue>0){observParamTypeList.push(rec.inputValue)}});
		base_form.findField('ObservTimeType').items.each(
			function(rec){log(rec)
				if(rec.checked){
					if(rec.inputValue==1){
						observTimeTypeList.push(1);
						observTimeTypeList.push(3);
					}else{
						observTimeTypeList.push(2);
					}
				}
			}
		);

		if(base_form.findField('EvnPrescr_dayNum').getValue()<=0){base_form.findField('EvnPrescr_dayNum').setValue(1)}
		if ( observParamTypeList.length == 0 ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_parametr_nablyudeniya'], function() {
				this.formStatus = 'edit';
			}.createDelegate(this));
			return false;
		}

		if ( observTimeTypeList.length == 0 ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrano_vremya_nablyudeniy'], function() {
				this.formStatus = 'edit';
			}.createDelegate(this));
			return false;
		}
		params.observParamTypeList = Ext.util.JSON.encode(observParamTypeList);
		params.observTimeTypeList = Ext.util.JSON.encode(observTimeTypeList);
		
		this.formStatus = 'save';
		this.getLoadMask(LOAD_WAIT_SAVE).show();
		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				this.getLoadMask().hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				this.getLoadMask().hide();

				if ( action.result ) {
					var data = new Object();
					data.evnPrescrData = base_form.getValues();
					data.evnPrescrData.EvnPrescr_id = action.result.EvnPrescr_id;
					this.callback(data);
					this.hide();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},

	setFieldsDisabled: function(d,obj)
    {
		var win = this;
		var base_form = this.FormPanel.getForm();
		obj=obj||this.FormPanel
		if(obj.items){
			obj.items.each(function(f)
			{
				if (f && (f.xtype!='hidden') &&  (f.xtype!='fieldset')  && (f.changeDisabled!==false))
				{
					if((typeof f.getLayout=='function')){
						win.setFieldsDisabled(d,f);
					}else{
						f.setDisabled(d);
					}
				}
			});
		}
        this.buttons[0].setDisabled(d);
		if(this.action=='view'){
			base_form.findField('ObservTimeType').disable();
		}else{
			base_form.findField('ObservTimeType').enable();
		}
 
	},
	uncheckAll:function(){
		var base_form = this.FormPanel.getForm();
		base_form.findField('ParametrValues2').items.each(
			function(rec,s,d){
				rec.setValue(false);
			}
		);
		base_form.findField('ParametrValues1').items.each(
			function(rec,s,d){
				rec.setValue(false);
			}
		);	
	},
	initComponent: function() {

       
	   var win = this;
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'PolkaEvnPrescrObservEditForm',
			labelAlign: 'right',
			labelWidth: 160,
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{name: 'accessType'},
				{name: 'EvnPrescr_id'},
				{name: 'EvnPrescr_pid'},
				{name: 'EvnPrescr_setDate'},
				{name: 'EvnPrescr_dayNum'},
				{name: 'EvnPrescr_Descr'},
				{name: 'PersonEvn_id'},
				{name: 'Server_id'}
			]),
			region: 'center',
			url: '/?c=EvnPrescr&m=saveEvnPrescrObserv',

			items: [{
				name: 'accessType', // Режим доступа
				value: null,
				xtype: 'hidden'
			}, {
				name: 'EvnPrescr_id', // Идентификатор назначения
				value: null,
				xtype: 'hidden'
			}, {
				name: 'EvnPrescr_pid', // Идентификатор события
				value: null,
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id', // Идентификатор состояния человека
				value: null,
				xtype: 'hidden'
			},{
				name: 'Person_id', // Идентификатор состояния человека
				value: null,
				xtype: 'hidden'
			},{
				name: 'Server_id', // Идентификатор сервера
				value: null,
				xtype: 'hidden'
			}, {
				layout:'column',
				items:[
					{
						layout:'form',
						items:[{
							allowBlank: false,
							fieldLabel: lang['nachat'],
							name: 'EvnPrescr_setDate',
							plugins: [
								new Ext.ux.InputTextMask('99.99.9999', false)
							],
							xtype: 'swdatefield'
						}]
					},
					{
						layout:'form',
						style:"margin-left:220px",
						items:[{
							fieldLabel: lang['prodoljat'],
							
							name: 'EvnPrescr_dayNum',
							minValue: 1,
							width:40,
							xtype: 'numberfield'
						}]
					},
					{
						layout:'form',
						items:[{
							xtype:'label',
							text: lang['dney'],
							name: 'labDays',
							style: 'margin-left:7px;font-size:13px;'
						 }]
					}
				]
			}, {
				allowBlank: false,
				xtype: 'checkboxgroup',
				fieldLabel: lang['parametryi'],
				name:'ParametrValues1',
				columns: 3,
				vertical: true,
				setValue:function(values){
					if(typeof values =='object'){
						for(var val in values){
							this.items.each(
								function(rec,id){
									if(values[val]==rec.inputValue){
										rec.setValue(true);
									}	
								}
							)
						}
					}
				},
				items: [
					{boxLabel: lang['art_davlenie'],width:300,boxWidth:300, columnWidth:300,inputValue: 1,checked:true},
					{boxLabel: lang['puls'],width:300,boxWidth:300, columnWidth:300, inputValue: 3,checked:true},
					{boxLabel: lang['temperatura'], inputValue: 4,checked:true},
					
				]
			},{
				xtype: 'radiogroup',
				fieldLabel: lang['kol-vo'],
				name:'ObservTimeType',
				allowBlank: false,
				columns: 2,
				vertical: true,
				setValue:function(val){
					this.items.each(
						function(rec,id){
							if(val==rec.inputValue){
								rec.setValue(true);
							}	
						}
					)
				},
				items: [
					{boxLabel: lang['2_raza_v_den'],width:400,boxWidth:400, columnWidth:400,name:'sdf', inputValue: 1},
					{boxLabel: lang['1_raz_v_den'],name:'sdf', inputValue: 2},
					
				]
			},{
				xtype: 'checkboxgroup',
				fieldLabel: lang['parametryi'],
				name:"ParametrValues2",
				columns: 2,
				vertical: true,
				setValue:function(values){
					if(typeof values =='object'){
						for(var val in values){
							this.items.each(
								function(rec,id){
									if(values[val]==rec.inputValue){
										rec.setValue(true);
									}	
								}
							)
						}
					}
				},
				items: [
					{boxLabel: lang['chastota_dyihaniya'],width:400,boxWidth:400, columnWidth:400, inputValue: 5,checked:true},
					{boxLabel: lang['ves'], inputValue: 6,checked:true},
					{boxLabel: lang['vyipito_jidkosti_ml'], inputValue: 7,checked:true},
					{boxLabel: lang['sutochnoe_kolichestvo_mochi_ml'], inputValue: 8,checked:true},
					{boxLabel: lang['stul'], inputValue: 9,checked:true},
					{boxLabel: lang['vanna'], inputValue: 10 ,checked:true},
					{boxLabel: lang['smena_belya'], inputValue: 11,checked:true},
					{ 
						boxLabel: lang['vyibrat_snyat_vse'],
						inputValue:'-1',
						checked:true,
						listeners:{
							check:function(s,check){
								var base_form = win.FormPanel.getForm();
								base_form.findField('ParametrValues2').items.each(
									function(rec,s,d){
										if(rec.inputValue!='-1'){
											rec.setValue(check);
										}
									}
								);	
							}
						}	
					}
					
				]
				
			},/*{
				autoHeight: true,
				labelWidth: 1,
				style: 'margin-left: 165px; padding: 0px;',
				title: lang['vremya_provedeniya_nablyudeniy'],
				width: 500,
				xtype: 'fieldset',

				items: [{
					boxLabel: lang['utro'],
					checked: true,
					fieldLabel: '',
					labelSeparator: '',
					// tabIndex: TABINDEX_EPSSW + 69,
					name: 'ObservTimeType_Morning',
					xtype: 'checkbox'
				}, {
					boxLabel: lang['den'],
					checked: true,
					fieldLabel: '',
					labelSeparator: '',
					// tabIndex: TABINDEX_EPSSW + 69,
					name: 'ObservTimeType_Day',
					xtype: 'checkbox'
				}, {
					boxLabel: lang['vecher'],
					checked: true,
					fieldLabel: '',
					labelSeparator: '',
					// tabIndex: TABINDEX_EPSSW + 69,
					name: 'ObservTimeType_Evening',
					xtype: 'checkbox'
				}]
			}, {
				border: false,
				height: 250,
				id: 'EPROBEF_ObservParamTypeGridPanel',
				layout: 'border',
				style: 'margin-left: 165px; margin-right: 0.5em; padding-bottom: 4px;',
				width: 500,
				items: [ this.viewFrame ]
			},*/ {
				fieldLabel: lang['kommentariy'],
				height: 70,
				name: 'EvnPrescr_Descr',
				width: 700,
				xtype: 'textarea'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			/*}, {
				handler: function() {
					this.doSave({signature: true});
				}.createDelegate(this),
				iconCls: 'signature16',
				text: BTN_FRMSIGN*/
			}, {
				text: '-'
			},
			//HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onTabAction: function () {
					this.FormPanel.getForm().findField('EvnPrescr_setDate').focus(true, 250);
				}.createDelegate(this),
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swPolkaEvnPrescrObservEditWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swPolkaEvnPrescrObservEditWindow.superclass.show.apply(this, arguments);
		var win = this;
		this.center();

		var base_form = this.FormPanel.getForm();
		

		this.parentEvnClass_SysNick = null;
		this.action = 'add';
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}

				base_form.reset();
				base_form.setValues(arguments[0].formParams);

		//base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action && typeof arguments[0].action == 'string' ) {
			this.action = arguments[0].action;
			///alert(arguments[0].action);
		}
		
		if ( arguments[0].parentEvnClass_SysNick && typeof arguments[0].parentEvnClass_SysNick == 'string' ) {
			this.parentEvnClass_SysNick = arguments[0].parentEvnClass_SysNick;
		}

		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}
		
		this.getLoadMask(LOAD_WAIT).show();

		switch ( this.action ) {
			case 'add':
				if(arguments[0].formParams && arguments[0].formParams.newEvnPrescr_id ){
					if(arguments[0].formParams.newEvnPrescr_id>0){
					Ext.Ajax.request({
						failure: function(response, options) {
							this.getLoadMask().hide();
							sw.swMsg.alert(lang['oshibka'], (response.status ? response.status.toString() + ' ' + response.statusText : lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']));
						}.createDelegate(this),
						params: {
						'EvnPrescr_id': arguments[0].formParams.newEvnPrescr_id
						,'parentEvnClass_SysNick': this.parentEvnClass_SysNick
					},
						success: function(act, acts) {
							this.uncheckAll();
							var response_obj = Ext.util.JSON.decode(act.responseText);
						base_form.clearInvalid();
						base_form.findField('ParametrValues1').setValue(response_obj[0].ObservParamType_id);
						base_form.findField('ParametrValues2').setValue(response_obj[0].ObservParamType_id);
						base_form.findField('ObservTimeType').setValue((response_obj[0].ObservTimeType_id[0]==3)?1:response_obj[0].ObservTimeType_id[0]);
						}.createDelegate(this),
						url: '/?c=EvnPrescr&m=loadEvnPrescrObservEditForm'
					});
					}else{
				Ext.Ajax.request({
						failure: function(response, options) {
							this.getLoadMask().hide();
							sw.swMsg.alert(lang['oshibka'], (response.status ? response.status.toString() + ' ' + response.statusText : lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']));
						}.createDelegate(this),
						params: {
							EvnPrescr_pid: base_form.findField('EvnPrescr_pid').getValue()
						},
						success: function(response, options) {
						

							var response_obj = Ext.util.JSON.decode(response.responseText);
							base_form.findField('EvnPrescr_setDate').setValue(response_obj[0].FreeDate);
						}.createDelegate(this),
						url: '/?c=EvnPrescr&m=getFreeDay'
					});
				
					}	
				}
			
				this.getLoadMask().hide();
				this.setTitle(lang['naznachenie_nablyudeniya_dobavlenie']);
				this.setFieldsDisabled(false);	
				//base_form.findField('EvnPrescr_setDate').focus(true, 250);
			break;

			case 'edit':
			case 'view':
				this.getLoadMask().hide();
				//this.hide();
				//break;
				this.uncheckAll();
				base_form.load({
					failure: function() {
						this.getLoadMask().hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {this.hide();}.createDelegate(this) );
					}.createDelegate(this),
					params: {
						'EvnPrescr_id': base_form.findField('EvnPrescr_id').getValue()
						,'parentEvnClass_SysNick': this.parentEvnClass_SysNick
					},
					success: function(frm, act) {
						var response_obj = Ext.util.JSON.decode(act.response.responseText);
						log(response_obj);
						//this.action = response_obj[0].accessType;
						this.getLoadMask().hide();
						base_form.clearInvalid();
						base_form.findField('ParametrValues1').setValue(response_obj[0].ObservParamType_id);
						base_form.findField('ParametrValues2').setValue(response_obj[0].ObservParamType_id);
						base_form.findField('ObservTimeType').setValue((response_obj[0].ObservTimeType_id[0]==3)?1:response_obj[0].ObservTimeType_id[0]);
						if ( this.action == 'edit' ) {
							
							this.setTitle(lang['naznachenie_nablyudeniya_redaktirovanie']);
							this.setFieldsDisabled(false);
						}
						else {
							this.setTitle(lang['naznachenie_nablyudeniya_prosmotr']);
							this.setFieldsDisabled(true);
						}
						base_form.findField('EvnPrescr_setDate').focus(true, 250);
					}.createDelegate(this),
					url: '/?c=EvnPrescr&m=loadEvnPrescrObservEditForm'
				});
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
	}
});