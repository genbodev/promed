/**
* swPersonBirthTraumaEditWindow - Добавление уточненного диагноза
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Swan
* @version      01.06.2014
*/

sw.Promed.swPersonBirthTraumaEditWindow = Ext.extend(sw.Promed.BaseForm, {
	
	modal: true,
	id:'PersonBirthTraumaEditWindow',
	width: 500,
	autoHeight: true,
	onCancel: Ext.emptyFn,
	action:'edit',
	callback: Ext.emptyFn,

	show: function() {
		sw.Promed.swPersonBirthTraumaEditWindow.superclass.show.apply(this, arguments);
		var base_form = this.FormPanel.getForm();
		this.formStatus = 'edit';
		var win = this;
		base_form.reset();
		if ( arguments[0] )
		{
			if (arguments[0] && arguments[0].action) {
				this.action = arguments[0].action;
			}
			if (arguments[0].onHide) {
				this.onHide = arguments[0].onHide;
			}
			if (arguments[0] && arguments[0].callback) {
				this.callback = arguments[0].callback;
			}
			if (arguments[0] && arguments[0].formParams) {
				base_form.setValues(arguments[0].formParams);
			}
			if(arguments[0].PersonBirthTrauma_id && arguments[0].PersonBirthTrauma_id!=null){
				this.PersonBirthTrauma_id = arguments[0].PersonBirthTrauma_id
			}
			if(arguments[0].BirthTraumaType_id && arguments[0].BirthTraumaType_id!=null){
				this.BirthTraumaType_id = arguments[0].BirthTraumaType_id
			}
			if(arguments[0].PersonNewBorn_id && arguments[0].PersonNewBorn_id!=null){
				this.PersonNewBorn_id = arguments[0].PersonNewBorn_id
			}
			if(arguments[0].Person_BirthDay && arguments[0].Person_BirthDay!=null){
				this.Person_BirthDay = arguments[0].Person_BirthDay
			}
			
			//base_form.setValues(arguments[0]);
		}else{
			return false;
		}
		switch(this.action){
			case 'add': this.setTitle(lang['spisok_utochnennyih_diagnozov_dobavlenie']); break;
			case 'edit': this.setTitle(lang['spisok_utochnennyih_diagnozov_redaktirovanie']); break;
			case 'view': this.setTitle(lang['spisok_utochnennyih_diagnozov_prosmotr']); break;
			default: this.setTitle(lang['spisok_utochnennyih_diagnozov']); break;
			
		}
		base_form.findField('PersonBirthTrauma_setDate').setMinValue(this.Person_BirthDay)
		if(this.action != 'add'){
			var diag_combo = base_form.findField('Diag_id');
			var diag_id = base_form.findField('Diag_id').getValue();
			diag_combo.getStore().removeAll();
			diag_combo.clearValue();
			diag_combo.getStore().load({
				callback:function () {
					diag_combo.setValue(diag_id);
				}.createDelegate(this),
				params:{where:"where DiagLevel_id = 4 and Diag_id = " + diag_id}
			});
	
			
		}/*
		if ( this.action != 'add' ) {
			var params = {};
			win.getLoadMask(lang['pojaluysta_podojdite_idet_zagruzka_dannyih_formyi']).show();
			base_form.reset();
			
			params.PersonBirthTrauma_id = this.PersonBirthTrauma_id;
			base_form.load({
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera']);
				}.createDelegate(this),
				url:'/?c=PersonNewBorn&m=getPersonBirthTraumaEditWindow',
				params:params,
				success: function(fm,rec,d) {
						var response_obj = Ext.util.JSON.decode(rec.response.responseText);
						
						var diag_id = base_form.findField('Diag_id').getValue();
						base_form.findField('Diag_id').getStore().load({
							callback: function() {
								base_form.findField('Diag_id').getStore().each(function(record) {
									if ( record.get('Diag_id') == diag_id ) {
										base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
										base_form.findField('Diag_id').fireEvent('change', base_form.findField('Diag_id'), diag_id);
									}
								});
							},
							params: {where: "where Diag_id = " + diag_id}
						});
				}
			});
			
			win.getLoadMask().hide();

		}*/
		this.setFieldDisable();
	},
	doSave: function() {
		if ( this.formStatus == 'save' ) {
			return false;
		}

		//this.formStatus = 'save';
		var win = this;
		var base_form = this.FormPanel.getForm();
		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					//this.formStatus = 'edit';
					win.FormPanel.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
            this.formStatus = 'edit';
			return false;
		}
		this.getLoadMask("Подождите, идет сохранение...").show();

		var data = new Object();

		data = getAllFormFieldValues(this.FormPanel);
		//data.CmpEmergencyTeamData.CmpProfile_Name = base_form.findField('CmpProfile_id').getFieldValue('CmpProfile_Name');
		var diag_combo = base_form.findField('Diag_id');
		diag_combo.getStore().each(function(rec){
			if(rec.get("Diag_id")==diag_combo.getValue()){
				data.Diag_Code = rec.get("Diag_Code");
				data.Diag_Name = rec.get("Diag_Name");
			}
		})
		this.callback(data);
		this.getLoadMask().hide();

		this.hide();
		//this.submit();
	},
	setFieldDisable:function(){
		var form = this.FormPanel;
		var win = this;
		var base_form = this.FormPanel.getForm();
		base_form.items.each(function(item,s,f){
			item.setDisabled(win.action=='view');
		});
		base_form.findField('Diag_id').setAllowBlank(win.action=='view');
		if(win.action=='view'){
			this.buttons[0].disable();
		}else{
			this.buttons[0].enable();
		}
	
	},
	submit: function(mode,onlySave) {
		var form = this.FormPanel;
		var base_form = this.FormPanel.getForm();
		var win = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		form.getForm().submit({
			url:'/?c=PersonNewBorn&m=savePersonBirthTraumaEditWindow',
			failure: function(result_form, action) {
				loadMask.hide();
				win.formStatus = 'edit';
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				loadMask.hide();
				var data={};
				win.callback(data);
				form.getForm().reset();
				win.hide();
				
			}
		});
	},
	initComponent: function() {
    	
		var win = this;

		this.FormPanel = new Ext.form.FormPanel({
			height:150,
			bodyStyle: 'padding: 5px',
			buttonAlign: 'left',
			frame: true,
			id: 'PersEvalEditForm',
			labelAlign: 'right',
			labelWidth: 80,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
			{
				name: 'PersonNewBorn_id'
			},

			{
				name: 'BirthTraumaType_id'
			},

			{
				name: 'PersonBirthTrauma_id'
			},
			{
				name: 'Diag_id'
			},
			{
				name: 'PersonBirthTrauma_setDate'
			},
			{
				name: 'Server_id'
			},
			{
				name: 'PersonBirthTrauma_Comment'
			}
			]),
			items: [
			{
				name: 'PersonBirthTrauma_id',
				xtype: 'hidden'
			},
			{
				name: 'Server_id',
				xtype: 'hidden'
			},
			{
				name: 'PersonNewBorn_id',
				xtype: 'hidden'
			},
			{
				name: 'BirthTraumaType_id',
				xtype: 'hidden'
			},
			{
				layout: 'form',
				items: [
					{
						allowBlank: false,
						fieldLabel: lang['data_vvoda'],
						format: 'd.m.Y',
						maxValue: getGlobalOptions().date,
						minValue: getMinBirthDate(),
						setDate:true,
						value:getGlobalOptions().date,
						hiddenName: 'PersonBirthTrauma_setDate',
						name: 'PersonBirthTrauma_setDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: TABINDEX_DSEW + 5,
						width: 95,
						xtype: 'swdatefield'
						
					},{
						allowBlank: false,
						hiddenName: 'Diag_id',
						listWidth: 580,
						tabIndex: TABINDEX_DSEW + 15,
						width: 350,
						xtype: 'swdiagcombo'
					},{
						xtype: 'textarea',
						fieldLabel: lang['rasshifrovka'],
						name:'PersonBirthTrauma_Comment',
						tabIndex: TABINDEX_DSEW + 35,
						width: 350
					}
				]
			}],
			enableKeyEvents: true
		});
		
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'ok16',
				tabIndex: TABINDEX_DSEW + 40,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this,TABINDEX_DSEW + 45),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function() {
				}.createDelegate(this),
				tabIndex: TABINDEX_DSEW + 50,
				text: BTN_FRMCANCEL
			}],
			items: [
			this.FormPanel
			]
		});
		
		sw.Promed.swPersonBirthTraumaEditWindow.superclass.initComponent.apply(this, arguments);
	}
});