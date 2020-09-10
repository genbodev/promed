/**
* swMorbusStudyResultWindow - окно редактирования "Направление на проведение микроскопических исследований на туберкулез"
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

sw.Promed.swMorbusStudyResultWindow = Ext.extend(sw.Promed.BaseForm, {
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
	titleWin: lang['napravlenie_na_provedenie_mikro_issl_na_tuberkulez'],
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
					that.findById('swMorbusStudyResultEditForm').getFirstInvalidEl().focus(true);
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
				that.callback(that.owner, action.result.MorbusStudyResult_id);
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
		sw.Promed.swMorbusStudyResultWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.MorbusStudyResult_id = null;
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
		if ( arguments[0].MorbusStudyResult_id ) {
			this.MorbusStudyResult_id = arguments[0].MorbusStudyResult_id;
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
				that.form.findField('TubStageChemType_id').focus(true,200);
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
						MorbusStudyResult_id: that.MorbusStudyResult_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						that.getLoadMask().hide();
						if (!result[0]) { return false; }
						that.form.setValues(result[0]);
						that.InformationPanel.load({
							Person_id: person_id
						});
						that.form.findField('TubStageChemType_id').focus(true,200);
					},
					url:'/?c=MorbusTub&m=loadMorbusStudyResult'
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
			id: 'swMorbusStudyResultEditForm',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			frame: true,
			border: false,
			labelWidth: 200,
			labelAlign: 'right',
			region: 'center',
			items: [
				{name: 'MorbusStudyResult_id', xtype: 'hidden', value: null},
				{name: 'MorbusTub_id', xtype: 'hidden', value: null},
				{name: 'Evn_id', xtype: 'hidden', value: null},
				{name: 'Person_id', xtype: 'hidden', value: null},
				{name: 'PersonWeight_id', xtype: 'hidden', value: null},
				{
					fieldLabel: lang['faza_himioterapii'],
					anchor:'100%',
					hiddenName: 'TubStageChemType_id',
					xtype: 'swcommonsprcombo',
					typeCode: 'int',
					allowBlank: true,
					sortField:'TubStageChemType_Code',
					comboSubject: 'TubStageChemType'
				}, {
					fieldLabel: lang['data_registratsii_rezultata'],
					name: 'MorbusStudyResult_setDT',
					allowBlank:false,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['tip_issledovaniya'],
					anchor:'100%',
					hiddenName: 'TubStudyType_id',
					xtype: 'swcommonsprcombo',
					typeCode: 'int',
					allowBlank: false,
					sortField:'TubStudyType_Code',
					comboSubject: 'TubStudyType'
				}, {
					fieldLabel: lang['rezultat_issledovaniya'],
					anchor:'100%',
					name: 'MorbusStudyResult_Result',
					xtype: 'textfield',
					allowBlank:true
				}, {
					allowBlank: false,
					allowNegative: false,
					decimalPrecision: 3,
					fieldLabel: lang['ves_cheloveka_v_kg'],
					name: 'PersonWeight_Weight',
					width: 100,
					xtype: 'numberfield'
				}, {
					xtype: 'fieldset',
					autoHeight: true,
					title: lang['nalichie_lekarstvennoy_ustoychivosti'],
					labelWidth: 150,
					items: [{
						fieldLabel: lang['k_izoniazidu'],
						width: 70,
						hiddenName: 'MorbusStudyResult_IsResultH',
						xtype: 'swyesnocombo',
						allowBlank: true
					}, {
						fieldLabel: lang['k_rifampitsinu'],
						width: 70,
						hiddenName: 'MorbusStudyResult_IsResultR',
						xtype: 'swyesnocombo',
						allowBlank: true
					}, {
						fieldLabel: lang['k_streptomitsinu'],
						width: 70,
						hiddenName: 'MorbusStudyResult_IsResultS',
						xtype: 'swyesnocombo',
						allowBlank: true
					}, {
						fieldLabel: lang['k_etambutolu'],
						width: 70,
						hiddenName: 'MorbusStudyResult_IsResultE',
						xtype: 'swyesnocombo',
						allowBlank: true
					}, {
						fieldLabel: lang['k_drugomu_preparatu'],
						width: 70,
						hiddenName: 'MorbusStudyResult_IsResultOther',
						xtype: 'swyesnocombo',
						allowBlank: true
					}, {
						fieldLabel: lang['drugoy_preparat'],
						anchor:'100%',
						hiddenName: 'TubDrug_id',
                        xtype: 'swtubcommonsprcombo',
                        isMDR: false,
						typeCode: 'int',
						allowBlank: true,
						sortField:'TubDrug_Code',
						comboSubject: 'TubDrug'
					}]
				}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MorbusStudyResult_id'},
				{name: 'MorbusTub_id'},
				{name: 'Person_id'},
				{name: 'TubStageChemType_id'},
				{name: 'MorbusStudyResult_setDT'},
				{name: 'TubStudyType_id'},
				{name: 'MorbusStudyResult_Result'},
				{name: 'PersonWeight_id'},
				{name: 'PersonWeight_Weight'},
				{name: 'MorbusStudyResult_IsResultH'},
				{name: 'MorbusStudyResult_IsResultR'},
				{name: 'MorbusStudyResult_IsResultS'},
				{name: 'MorbusStudyResult_IsResultE'},
				{name: 'TubDrug_id'},
				{name: 'MorbusStudyResult_IsResultOther'}
			]),
			url: '/?c=MorbusTub&m=saveMorbusStudyResult'
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
		sw.Promed.swMorbusStudyResultWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('swMorbusStudyResultEditForm').getForm();
	}	
});