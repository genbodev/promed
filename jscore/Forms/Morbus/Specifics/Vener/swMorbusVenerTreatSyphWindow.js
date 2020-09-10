/**
* swMorbusVenerTreatSyphWindow - окно редактирования "Лечение больного сифилисом"
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

sw.Promed.swMorbusVenerTreatSyphWindow = Ext.extend(sw.Promed.BaseForm, {
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
	titleWin: lang['lechenie_bolnogo_sifilisom'],
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
					that.findById('swMorbusVenerTreatSyphEditForm').getFirstInvalidEl().focus(true);
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
				that.callback(that.owner, action.result.MorbusVenerTreatSyph_id);
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
		sw.Promed.swMorbusVenerTreatSyphWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.MorbusVenerTreatSyph_id = null;
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
		if ( arguments[0].MorbusVenerTreatSyph_id ) {
			this.MorbusVenerTreatSyph_id = arguments[0].MorbusVenerTreatSyph_id;
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
				that.form.findField('MorbusVenerTreatSyph_NumCourse').focus(true,200);

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
						MorbusVenerTreatSyph_id: that.MorbusVenerTreatSyph_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						that.getLoadMask().hide();
						if (!result[0]) { return false; }
						that.form.setValues(result[0]);
						that.InformationPanel.load({
							Person_id: person_id
						});
						var drug_id = that.form.findField('Drug_id').getValue();
						var drug = that.form.findField('Drug_id');
						if (drug_id>0) {
							var DrugPrepFasCombo = that.form.findField('DrugPrepFas_id');
							DrugPrepFasCombo.getStore().load({
								params: {Drug_id: drug_id},
								callback: function() {
									if (DrugPrepFasCombo.getStore().getCount()>0) {
										DrugPrepFasCombo.setValue(DrugPrepFasCombo.getStore().getAt(0).get('DrugPrepFas_id'));
										DrugPrepFasCombo.fireEvent('change', DrugPrepFasCombo, DrugPrepFasCombo.getStore().getAt(0).get('DrugPrepFas_id'), 0);
									}
								}
							});
						}
						
						that.form.findField('MorbusVenerTreatSyph_NumCourse').focus(true,200);
					},
					url:'/?c=MorbusVener&m=loadMorbusVenerTreatSyph'
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
			id: 'swMorbusVenerTreatSyphEditForm',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			frame: true,
			border: false,
			labelWidth: 200,
			labelAlign: 'right',
			region: 'center',
			items: [
				{name: 'MorbusVenerTreatSyph_id', xtype: 'hidden', value: null},
				{name: 'MorbusVener_id', xtype: 'hidden', value: null},
				{name: 'Evn_id', xtype: 'hidden', value: null},
				{
					fieldLabel: lang['№_kursa'],
					name: 'MorbusVenerTreatSyph_NumCourse',
					xtype: 'numberfield',
					maxValue: 150,
					minValue: 1,
					autoCreate: {tag: "input", size:14, maxLength: "6", autocomplete: "off"},
					allowBlank: false
				}, {
					fieldLabel: lang['data_nachala_lecheniya'],
					name: 'MorbusVenerTreatSyph_begDT',
					allowBlank:false,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['data_okonchaniya_lecheniya'],
					name: 'MorbusVenerTreatSyph_endDT',
					allowBlank:true,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, { // Первый комбобокс (медикамент)
					hiddenName: 'DrugPrepFas_id',
					fieldLabel: lang['naimenovanie_preparata'],
					anchor: '100%',
					xtype: 'swdrugprepcombo'
				},
				{ // второй комбобокс (упаковка)
					hiddenName: 'Drug_id',
					fieldLabel: lang['preparat'],
					anchor: '100%',
					xtype: 'swdrugpackcombo'
				}, {
					fieldLabel: lang['summarnaya_doza_preparata'],
					name: 'MorbusVenerTreatSyph_SumDose',
					xtype: 'numberfield',
					//maxValue: 150,
					minValue: 0,
					autoCreate: {tag: "input", size:14, maxLength: "6", autocomplete: "off"},
					allowDecimals: true,
					allowNegative: false,
					allowBlank:true
				}, {
					fieldLabel: lang['rezultatyi_serologicheskogo_issl_do_nachala_kursa'],
					anchor:'100%',
					name: 'MorbusVenerTreatSyph_RSSBegCourse',
					xtype: 'textfield',
					allowBlank:true
				}, {
					fieldLabel: lang['rezultatyi_serologicheskogo_issl_po_okonchanii_kursa'],
					anchor:'100%',
					name: 'MorbusVenerTreatSyph_RSSEndCourse',
					xtype: 'textfield',
					allowBlank:true
				}, {
					fieldLabel: lang['primechanie'],
					anchor:'100%',
					name: 'MorbusVenerTreatSyph_Comment',
					xtype: 'textfield',
					allowBlank:true
				}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MorbusVenerTreatSyph_id'},
				{name: 'MorbusVener_id'},
				{name: 'MorbusVenerTreatSyph_NumCourse'},
				{name: 'MorbusVenerTreatSyph_begDT'},
				{name: 'MorbusVenerTreatSyph_endDT'},
				{name: 'Drug_id'},
				{name: 'MorbusVenerTreatSyph_SumDose'},
				{name: 'MorbusVenerTreatSyph_RSSBegCourse'},
				{name: 'MorbusVenerTreatSyph_RSSEndCourse'},
				{name: 'MorbusVenerTreatSyph_Comment'}
			]),
			url: '/?c=MorbusVener&m=saveMorbusVenerTreatSyph'
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
		sw.Promed.swMorbusVenerTreatSyphWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('swMorbusVenerTreatSyphEditForm').getForm();
	}	
});