/**
* swMorbusCrazyDiagWindow - окно редактирования "Диагнозы"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       A. Markoff
* @version      2012/11
* @comment      
*/

sw.Promed.swMorbusCrazyDiagWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'form',
	modal: true,
	width: 670,
	titleWin: lang['diagnoz'],
	autoHeight: true,
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
					that.findById('swMorbusCrazyDiagEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		this.setFieldsDisabled(false);
        this.submit();
		return true;		
	},
	submit: function() {
		var that = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = new Object();
		params.action = that.action;
		this.form.submit({
			params: params,
			failure: function(result_form, action) 
			{
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
				that.callback(that.owner, action.result.MorbusCrazyDiag_id);
				that.hide();
			}
		});
	},
	setFieldsDisabled: function(d,main)
	{
		var form = this;
		this.form.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		if(main&&this.action=='edit'){
			this.form.findField('Diag_sid').setDisabled();
		}else{
			form.buttons[0].setDisabled(d);
		}
		
	},
	show: function() {
        var that = this, 
        	win = this;
		sw.Promed.swMorbusCrazyDiagWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.type='crazy';
		this.callback = Ext.emptyFn;
		this.MorbusCrazyDiag_id = null;
        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { that.hide(); });
            return false;
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].type ) {
			this.type = arguments[0].type;
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
		if ( arguments[0].MorbusCrazyDiag_id ) {
			this.MorbusCrazyDiag_id = arguments[0].MorbusCrazyDiag_id;
		}

		this.form.reset();


		this.form.findField('CrazyDiag_id').getStore().removeAll();
		this.form.findField('CrazyDiag_id').getStore().baseParams.date = null;
		
		this.form.findField('MorbusCrazyDiagDepend_id').hideContainer();

		switch (arguments[0].action) {
			case 'add':
				this.setTitle(this.titleWin+lang['_dobavlenie']);
				this.setFieldsDisabled(false);
				break;
			case 'edit':
				this.setTitle(this.titleWin+lang['_redaktirovanie']);
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(this.titleWin+lang['_prosmotr']);
				this.setFieldsDisabled(true);
				break;
		}
		
        this.getLoadMask(lang['idet_zagruzka_dannyih_formyi_podojdite']).show();
		setCurrentDateTime({
			dateField:that.form.findField('MorbusCrazyDiag_setDT'),
			loadMask:false,
			setDate:false,
			setDateMaxValue:true,
			setDateMinValue:false,
			windowId:this.id
		});
		switch (arguments[0].action) {
			case 'add':
				that.form.setValues(arguments[0].formParams);
				that.InformationPanel.load({
					Person_id: arguments[0].formParams.Person_id
				});
				that.form.findField('MorbusCrazyDiag_setDT').focus(true,200);
				that.form.findField('CrazyDiag_id').getStore().baseParams.type=that.type;
				that.getLoadMask().hide();
			break;
			case 'edit':
			case 'view':
				var person_id = arguments[0].formParams.Person_id;
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						that.getLoadMask().hide();
					},
					params:{
						MorbusCrazyDiag_id: that.MorbusCrazyDiag_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) { that.getLoadMask().hide(); return false; }
						that.form.setValues(result[0]);
						that.InformationPanel.load({
							Person_id: person_id
						});
						

						if(result[0].isMain&&result[0].isMain==1){
							//#52579 Реализовать возможность редактирования первой строки списка "Диагноз" (поля "Дата установления (пересмотра)", "Диагноз")
							//that.setFieldsDisabled(true,true);
						}
						var crazydiag_id = that.form.findField('CrazyDiag_id').getValue();
						var diag_id = that.form.findField('Diag_sid').getValue();
						that.form.findField('CrazyDiag_id').getStore().baseParams.query=null;
						that.form.findField('CrazyDiag_id').getStore().baseParams.type=that.type;

						if ( typeof that.form.findField('MorbusCrazyDiag_setDT').getValue() == 'object' ) {
							that.form.findField('CrazyDiag_id').getStore().baseParams.date = that.form.findField('MorbusCrazyDiag_setDT').getValue().format('d.m.Y');
						}

						//Т.к. загрузка может идти долго то надо скрывать лодмаску только когда всё уже загружено дабы не смущать пользователей

        				that.getLoadMask().show();
						if ( crazydiag_id != null && crazydiag_id.toString().length > 0 && diag_id != null && diag_id.toString().length > 0) {
							that.form.findField('CrazyDiag_id').getStore().load({
								callback: function() {
									that.form.findField('CrazyDiag_id').setValue(crazydiag_id);

									var diagCombo = win.form.findField('CrazyDiag_id');
									diagCombo.fireEvent('change', diagCombo, result[0].CrazyDiag_id);

									that.form.findField('Diag_sid').getStore().load({
										callback: function() {
											that.form.findField('Diag_sid').getStore().each(function(record) {
												if ( record.get('Diag_id') == diag_id ) {
													that.form.findField('Diag_sid').fireEvent('select', that.form.findField('Diag_sid'), record, 0);
												}
											});
											that.getLoadMask().hide();
										},
										params: { CrazyDiag_id: diag_id }
									});
								},
								params: { CrazyDiag_id: crazydiag_id }
							});
						} else if ( crazydiag_id != null && crazydiag_id.toString().length > 0 ) {
							that.form.findField('CrazyDiag_id').getStore().load({
								callback: function() {
									that.form.findField('CrazyDiag_id').setValue(crazydiag_id);
									that.getLoadMask().hide();

									var diagCombo = win.form.findField('CrazyDiag_id');
									diagCombo.fireEvent('change', diagCombo, result[0].CrazyDiag_id);
								},
								params: { CrazyDiag_id: crazydiag_id }
							});
						} else if ( diag_id != null && diag_id.toString().length > 0 ) {
							that.form.findField('Diag_sid').getStore().load({
								callback: function() {
									that.form.findField('Diag_sid').getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_id ) {
											that.form.findField('Diag_sid').fireEvent('select', that.form.findField('Diag_sid'), record, 0);
										}
									});
									that.getLoadMask().hide();
								},
								params: { CrazyDiag_id: diag_id }
							});
						} else {
							that.getLoadMask().hide();
						}
						that.form.findField('MorbusCrazyDiag_setDT').focus(true,200);
					},
					url:'/?c=MorbusCrazy&m=loadMorbusCrazyDiag'
				});				
			break;	
		}
	},
	initComponent: function() {
		var win = this;

		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});
		var form = new Ext.form.FormPanel({
			autoHeight: true,
			id: 'swMorbusCrazyDiagEditForm',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			frame: true,
			border: false,
			labelWidth: 200,
			labelAlign: 'right',
			region: 'center',
			items: [
				{name: 'MorbusCrazyDiag_id', xtype: 'hidden', value: null},
				{name: 'MorbusCrazy_id', xtype: 'hidden', value: null},
				{name: 'Evn_id', xtype: 'hidden', value: null},
				{
					allowBlank:false,
					fieldLabel: lang['data_ustanovleniya_peresmotra'],
					listeners: {
						'change': function(field, newValue, oldValue) {
							var
								base_form = this.form,
								CrazyDiag_id = base_form.findField('CrazyDiag_id').getValue();

							base_form.findField('CrazyDiag_id').getStore().baseParams.date = (typeof newValue == 'object' ? newValue.format('d.m.Y') : null);
							base_form.findField('CrazyDiag_id').getStore().load({
								callback: function() {
									if ( !Ext.isEmpty(CrazyDiag_id) ) {
										var index = base_form.findField('CrazyDiag_id').getStore().findBy(function(rec) {
											return (rec.get('CrazyDiag_id') == CrazyDiag_id);
										});

										if ( index == -1 ) {
											base_form.findField('CrazyDiag_id').clearValue();
										}
									}
								},
								params: {
									CrazyDiag_id: CrazyDiag_id
								}
							})
						}.createDelegate(this)
					},
					name: 'MorbusCrazyDiag_setDT',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					xtype: 'swdatefield'
				}, {
					fieldLabel: lang['diagnoz'],
					anchor:'100%',
					listWidth: 600,
					hiddenName: 'CrazyDiag_id',
					listeners: {
						'change': function(combo, newValue) {
							
							if(!combo.getRawValue()) {
								win.form.findField('MorbusCrazyDiagDepend_id').hideContainer();
								return false;
							}
							var diagCode = combo.getDiagCode(combo.getRawValue());
							if(diagCode.search(/F1[35689]\./i) != -1) { // F13.%, F15.% F16.%, F18.%, F19.%
								win.form.findField('MorbusCrazyDiagDepend_id').showContainer();
							} else {
								win.form.findField('MorbusCrazyDiagDepend_id').hideContainer();
							}
						}

					},
					xtype: 'swcrazydiagcombo',
					allowBlank:false
				}, {
					fieldLabel: langs('Код зависимости'),
					comboSubject: 'MorbusCrazyDiagDepend',
					hiddenName: 'MorbusCrazyDiagDepend_id',
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel: langs('Диагноз основного заболевания'),
					anchor:'100%',
					hiddenName: 'Diag_sid',
					xtype: 'swdiagcombo',
					allowBlank:true
				}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MorbusCrazyDiag_id'},
				{name: 'MorbusCrazy_id'},
				{name: 'MorbusCrazyDiag_setDT'},
				{name: 'CrazyDiag_id'},
				{name: 'Diag_sid'}
			]),
			url: '/?c=MorbusCrazy&m=saveMorbusCrazyDiag'
		});
		Ext.apply(this, {
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
			items:[this.InformationPanel,form]
		});
		sw.Promed.swMorbusCrazyDiagWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('swMorbusCrazyDiagEditForm').getForm();
	}	
});