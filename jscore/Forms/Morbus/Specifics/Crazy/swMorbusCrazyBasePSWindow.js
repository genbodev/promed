/**
* swMorbusCrazyBasePSWindow - окно редактирования "Сведения о госпитализации"
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

sw.Promed.swMorbusCrazyBasePSWindow = Ext.extend(sw.Promed.BaseForm, {
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
	titleWin: lang['svedeniya_o_gospitalizatsii'],
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
					that.findById('swMorbusCrazyBasePSEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
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
				that.callback(that.owner, action.result.MorbusCrazyBasePS_id);
				that.hide();
			}
		});
	},
	setFieldsDisabled: function(d)
	{
		var form = this;
		this.form.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	show: function() {
        var that = this;
		sw.Promed.swMorbusCrazyBasePSWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.MorbusCrazyBasePS_id = null;
		this.type = null;
        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { that.hide(); });
            return false;
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
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
		if ( arguments[0].MorbusCrazyBasePS_id ) {
			this.MorbusCrazyBasePS_id = arguments[0].MorbusCrazyBasePS_id;
		}
		if ( arguments[0].evnsysnick ) {
			this.evnsysnick = arguments[0].evnsysnick;
		}
		if ( arguments[0].type ) {
			this.type = arguments[0].type;
		}
		

		this.form.reset();
		this.form.findField('CrazyDiag_id').getStore().removeAll();
		this.form.findField('CrazyDiag_id').getStore().baseParams.date = null;

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

		if (!Ext.isEmpty(this.type)) {
			that.form.findField('CrazyDiag_id').getStore().baseParams.type = this.type;
		}
		
        this.getLoadMask().show();
		switch (arguments[0].action) {
			case 'add':
				that.form.setValues(arguments[0].formParams);
				that.InformationPanel.load({
					Person_id: arguments[0].formParams.Person_id
				});
				if (that.evnsysnick == 'EvnSection') {
					that.form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
					that.form.findField('Lpu_id').setDisabled(true);
					that.form.findField('CrazyPurposeHospType_id').focus(true,200);
				} else {
					that.form.findField('Lpu_id').focus(true,200);
				}
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
						MorbusCrazyBasePS_id: that.MorbusCrazyBasePS_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) { that.getLoadMask().hide(); return false; }
						that.form.setValues(result[0]);
						that.InformationPanel.load({
							Person_id: person_id
						});
						var crazydiag_id = that.form.findField('CrazyDiag_id').getValue();
						that.form.findField('CrazyDiag_id').getStore().baseParams.query=null;
						if (that.evnsysnick == 'EvnSection') {
							that.form.findField('Lpu_id').setDisabled(true);
							that.form.findField('CrazyPurposeHospType_id').focus(true,200);
						} else {
							that.form.findField('Lpu_id').focus(true,200);
						}

						if ( crazydiag_id != null && crazydiag_id.toString().length > 0 ) {
							that.form.findField('CrazyDiag_id').getStore().load({
								callback: function() {
									that.form.findField('CrazyDiag_id').setValue(crazydiag_id);
									that.getLoadMask().hide();
								},
								params: { CrazyDiag_id: crazydiag_id }
							});
						} else {
							that.getLoadMask().hide();
						}

						var diag_id = that.form.findField('Diag_id').getValue();
						if ( diag_id != null && diag_id.toString().length > 0 ) {
							that.form.findField('Diag_id').getStore().load({
								callback: function() {
									that.form.findField('Diag_id').getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_id ) {
											that.form.findField('Diag_id').fireEvent('select', that.form.findField('Diag_id'), record, 0);
										}
									});
								},
								params: { CrazyDiag_id: diag_id }
							});
						}
					},
					url:'/?c=MorbusCrazy&m=loadMorbusCrazyBasePS'
				});
			break;
		}
	},
	initComponent: function() {
		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});
		var form = new Ext.form.FormPanel({
			autoHeight: true,
			id: 'swMorbusCrazyBasePSEditForm',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			frame: true,
			border: false,
			labelWidth: 200,
			labelAlign: 'right',
			region: 'center',
			items: [
				{name: 'MorbusCrazyBasePS_id', xtype: 'hidden', value: null},
				{name: 'MorbusCrazyBase_id', xtype: 'hidden', value: null},
				{name: 'Evn_id', xtype: 'hidden', value: null},
				{
					fieldLabel: lang['mo'],
					anchor:'100%',
					hiddenName: 'Lpu_id',
					xtype: 'swlpulocalcombo',
					allowBlank: false
				}, {
					fieldLabel: lang['tsel_gospitalizatsii'],
					anchor:'100%',
					hiddenName: 'CrazyPurposeHospType_id',
					xtype: 'swcommonsprcombo',
					allowBlank: false,
					sortField:'CrazyPurposeHospType_Code',
					comboSubject: 'CrazyPurposeHospType'
				}, {
					fieldLabel: lang['data_postupleniya'],
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
					name: 'MorbusCrazyBasePS_setDT',
					allowBlank:false,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['data_vyibyitiya'],
					name: 'MorbusCrazyBasePS_disDT',
					allowBlank:true,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['diagnoz_v_sootvetstvii_s_v_klassom_mkb-10'],
					anchor:'100%',
					listWidth: 600,
					hiddenName: 'CrazyDiag_id',
					xtype: 'swcrazydiagcombo',
					allowBlank: false
				}, {
					fieldLabel: lang['diagnoz_osnovnogo_zabolevaniya'],
					anchor:'100%',
					hiddenName: 'Diag_id',
					xtype: 'swdiagcombo',
					allowBlank: true
				}, {
					xtype: 'fieldset',
					autoHeight: true,
					title: lang['dopolnitelnaya_informatsiya_o_lechenii'],
					labelWidth: 160,
					items: [{
						fieldLabel: lang['gospitalizirovan'],
						anchor:'100%',
						hiddenName: 'CrazyHospType_id',
						xtype: 'swcommonsprcombo',
						allowBlank: true,
						sortField:'CrazyHospType_Code',
						comboSubject: 'CrazyHospType'
					}, {
						fieldLabel: lang['postuplenie'],
						anchor:'100%',
						hiddenName: 'CrazySupplyType_id',
						xtype: 'swcommonsprcombo',
						allowBlank: true,
						sortField:'CrazySupplyType_Code',
						comboSubject: 'CrazySupplyType'
					}, {
						fieldLabel: lang['kem_napravlen'],
						anchor:'100%',
						hiddenName: 'CrazyDirectType_id',
						xtype: 'swcommonsprcombo',
						allowBlank: true,
						sortField:'CrazyDirectType_Code',
						comboSubject: 'CrazyDirectType'
					}, {
						fieldLabel: lang['poryadok_postupleniya'],
						anchor:'100%',
						hiddenName: 'CrazySupplyOrderType_id',
						xtype: 'swcommonsprcombo',
						allowBlank: true,
						sortField:'CrazySupplyOrderType_Code',
						comboSubject: 'CrazySupplyOrderType'
					}, {
						fieldLabel: lang['otkuda_postupil'],
						anchor:'100%',
						hiddenName: 'CrazyDirectFromType_id',
						xtype: 'swcommonsprcombo',
						allowBlank: true,
						sortField:'CrazyDirectFromType_Code',
						comboSubject: 'CrazyDirectFromType'
					}, {
						fieldLabel: lang['reshenie_sudi_po_st_35'],
						anchor:'100%',
						hiddenName: 'CrazyJudgeDecisionArt35Type_id',
						xtype: 'swcommonsprcombo',
						allowBlank: true,
						sortField:'CrazyJudgeDecisionArt35Type_Code',
						comboSubject: 'CrazyJudgeDecisionArt35Type'
					}, {
						fieldLabel: lang['tsel_napravleniya'],
						anchor:'100%',
						hiddenName: 'CrazyPurposeDirectType_id',
						xtype: 'swcommonsprcombo',
						allowBlank: true,
						sortField:'CrazyPurposeDirectType_Code',
						comboSubject: 'CrazyPurposeDirectType'
					}, {
						fieldLabel: lang['vyibyil'],
						anchor:'100%',
						hiddenName: 'CrazyLeaveType_id',
						xtype: 'swcommonsprcombo',
						allowBlank: true,
						sortField:'CrazyLeaveType_Code',
						comboSubject: 'CrazyLeaveType'
					}]
				}
			],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MorbusCrazyBasePS_id'},
				{name: 'MorbusCrazyBase_id'},
				{name: 'CrazyPurposeHospType_id'},
				{name: 'CrazyPurposeDirectType_id'},
				{name: 'MorbusCrazyBasePS_setDT'},
				{name: 'MorbusCrazyBasePS_disDT'},
				{name: 'CrazyDiag_id'},
				{name: 'Diag_id'},
				{name: 'Lpu_id'},
				{name: 'CrazyHospType_id'},
				{name: 'CrazySupplyType_id'},
				{name: 'CrazyDirectType_id'},
				{name: 'CrazySupplyOrderType_id'},
				{name: 'CrazyDirectFromType_id'},
				{name: 'CrazyJudgeDecisionArt35Type_id'},
				{name: 'CrazyLeaveType_id'}, 
				{name: 'Evn_id'}
			]),
			url: '/?c=MorbusCrazy&m=saveMorbusCrazyBasePS'
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
		sw.Promed.swMorbusCrazyBasePSWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('swMorbusCrazyBasePSEditForm').getForm();
	}	
});