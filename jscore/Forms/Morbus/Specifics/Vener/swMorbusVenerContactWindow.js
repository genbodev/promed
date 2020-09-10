/**
* swMorbusVenerContactWindow - окно редактирования "Люди, контактировавшие с больным"
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

sw.Promed.swMorbusVenerContactWindow = Ext.extend(sw.Promed.BaseForm, {
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
	titleWin: lang['lyudi_kontaktirovavshie_s_bolnyim'],
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
					that.findById('swMorbusVenerContactEditForm').getFirstInvalidEl().focus(true);
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
				that.callback(that.owner, action.result.MorbusVenerContact_id);
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
		sw.Promed.swMorbusVenerContactWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.MorbusVenerContact_id = null;
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
		if ( arguments[0].MorbusVenerContact_id ) {
			this.MorbusVenerContact_id = arguments[0].MorbusVenerContact_id;
		}

		this.form.reset();
		
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
		
        this.getLoadMask().show();
		switch (arguments[0].action) {
			case 'add':
				that.form.setValues(arguments[0].formParams);
				that.InformationPanel.load({
					Person_id: arguments[0].formParams.Person_id
				});
				that.form.findField('Person_cid').focus(true,200);

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
						MorbusVenerContact_id: that.MorbusVenerContact_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						that.getLoadMask().hide();
						if (!result[0]) { return false; }
						that.form.setValues(result[0]);
						that.InformationPanel.load({
							Person_id: person_id
						});
						that.form.findField('Person_cid').focus(true,200);
					},
					url:'/?c=MorbusVener&m=loadMorbusVenerContact'
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
			id: 'swMorbusVenerContactEditForm',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			frame: true,
			border: false,
			labelWidth: 200,
			labelAlign: 'right',
			region: 'center',
			items: [
				{name: 'MorbusVenerContact_id', xtype: 'hidden', value: null},
				{name: 'MorbusVener_id', xtype: 'hidden', value: null},
				{name: 'Evn_id', xtype: 'hidden', value: null},
				{
					xtype: 'swpersoncombo',
					hiddenName: 'Person_cid',
					readOnly: true,
					anchor:'100%',
					enableKeyEvents: true,
					listeners : {
						'keydown' : function(inp, e) {
							if ( e.getKey() == e.DELETE) {
								inp.onTrigger2Click();
								e.stopEvent();
								return true;
							}
							if (e.getKey() == e.F4) {
								inp.onTrigger1Click();
								e.stopEvent();
								return true;
							}
						}
					},
					fieldLabel: lang['kontaktirovavshiy_chelovek'],
					onTrigger1Click: function() {
						var combo = this.form.findField('Person_cid');
						if (combo.disabled) return false;
						
						getWnd('swPersonSearchWindow').show({
							onHide: function() {
								combo.focus(false);
							},
							onSelect: function(personData) {
								if ( personData.Person_id > 0 ) {
									combo.getStore().loadData([{
										Person_id: personData.Person_id,
										Person_Fio: personData.PersonSurName_SurName + ' ' + personData.PersonFirName_FirName + ' ' + personData.PersonSecName_SecName
									}]);
									combo.setValue(personData.Person_id);
									combo.collapse();
									combo.focus(true, 500);
									combo.fireEvent('change', combo);
								}
								getWnd('swPersonSearchWindow').hide();
							}.createDelegate(this)
						});
					}.createDelegate(this),
					onTrigger2Click: function() {
						var combo = this.form.findField('Person_cid');
						combo.setValue(null);
					}.createDelegate(this)
				}, {
					fieldLabel: lang['otnoshenie_k_bolnomu'],
					anchor:'100%',
					name: 'MorbusVenerContact_RelationSick',
					xtype: 'textfield',
					allowBlank:true
				}, {
					fieldLabel: lang['istochnik_zarajeniya'],
					width: 70,
					hiddenName: 'MorbusVenerContact_IsSourceInfect',
					xtype: 'swyesnocombo',
					allowBlank: true
				}, {
					fieldLabel: lang['kontakt_podlejaschiy_obsl-niyu'],
					width: 70,
					hiddenName: 'MorbusVenerContact_IsFamSubjServey',
					xtype: 'swyesnocombo',
					allowBlank: true
				}, {
					fieldLabel: lang['data_vyizova'],
					name: 'MorbusVenerContact_CallDT',
					allowBlank:false,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['data_yavki'],
					name: 'MorbusVenerContact_PresDT',
					allowBlank:true,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['data_pervichnogo_osmotra'],
					name: 'MorbusVenerContact_FirstDT',
					allowBlank:true,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['data_zaklyuchitelnogo_osmotra'],
					name: 'MorbusVenerContact_FinalDT',
					allowBlank:true,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['diagnoz'],
					anchor:'100%',
					hiddenName: 'Diag_id',
					xtype: 'swdiagcombo',
					allowBlank:true
				}, {
					fieldLabel: lang['primechanie'],
					anchor:'100%',
					name: 'MorbusVenerContact_Comment',
					xtype: 'textfield',
					allowBlank:true
				}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MorbusVenerContact_id'},
				{name: 'MorbusVener_id'},
				{name: 'Person_cid'},
				{name: 'MorbusVenerContact_RelationSick'},
				{name: 'MorbusVenerContact_IsSourceInfect'},
				{name: 'MorbusVenerContact_IsFamSubjServey'},
				{name: 'MorbusVenerContact_CallDT'},
				{name: 'MorbusVenerContact_PresDT'},
				{name: 'MorbusVenerContact_FirstDT'},
				{name: 'MorbusVenerContact_FinalDT'},
				{name: 'Diag_id'},
				{name: 'MorbusVenerContact_Comment'}
			]),
			url: '/?c=MorbusVener&m=saveMorbusVenerContact'
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
		sw.Promed.swMorbusVenerContactWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('swMorbusVenerContactEditForm').getForm();
	}	
});