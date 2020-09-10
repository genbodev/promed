/**
* swMorbusTubMDRStudyDrugResultWindow - окно редактирования "Тест на лекарственную чувствительность" специфики туберкулеза с МЛУ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      TubRegistry
* @access       public
* @copyright    Copyright (c) 2009-1014 Swan Ltd.
* @author       A. Permyakov
* @version      12/2014    
*/

sw.Promed.swMorbusTubMDRStudyDrugResultWindow = Ext.extend(sw.Promed.BaseForm, {
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
	titleWin: lang['test_na_lekarstvennuyu_chuvstvitelnost'],
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
					that.findById('swMorbusTubMDRStudyDrugResultEditForm').getFirstInvalidEl().focus(true);
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
		this.form.submit({
			params: {},
			failure: function(result_form, action)
			{
				loadMask.hide();
				if (action.result)  {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action)
			{
				loadMask.hide();
				if (action.result) {
					var id = action.result.MorbusTubMDRStudyDrugResult_id;
					if (id) {
						that.callback(that.owner, id);
						that.hide();
					}
				}
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
		sw.Promed.swMorbusTubMDRStudyDrugResultWindow.superclass.show.apply(this, arguments);
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { that.hide(); });
			return false;
		}
		this.callback = Ext.emptyFn;
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		this.action = arguments[0].action || '';
		this.ARMType = arguments[0].ARMType || null;
		this.owner = arguments[0].owner || null;
		this.MorbusTubMDRStudyDrugResult_id = arguments[0].MorbusTubMDRStudyDrugResult_id || null;
		that.form.reset();
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
				that.form.setValues({
					MorbusTubMDRStudyResult_id: arguments[0].MorbusTubMDRStudyResult_id || null
				});
				that.form.findField('MorbusTubMDRStudyDrugResult_setDT').focus(true,200);
				that.getLoadMask().hide();
			break;
			case 'edit':
			case 'view':
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						that.getLoadMask().hide();
					},
					params:{
						MorbusTubMDRStudyDrugResult_id: that.MorbusTubMDRStudyDrugResult_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						that.getLoadMask().hide();
						if (!result[0]) { return false; }
						that.form.setValues(result[0]);
						that.form.findField('MorbusTubMDRStudyDrugResult_setDT').focus(true,200);
						return true;
					},
					url:'/?c=MorbusTub&m=loadMorbusTubMDRStudyDrugResult'
				});				
			break;	
		}
		return true;
	},
	initComponent: function() {
		var me = this;
		
		var form = new Ext.form.FormPanel({
			autoHeight: true,
			id: 'swMorbusTubMDRStudyDrugResultEditForm',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			frame: true,
			border: false,
			labelWidth: 150,
			labelAlign: 'right',
			region: 'center',
			items: [
				{name: 'MorbusTubMDRStudyDrugResult_id', xtype: 'hidden', value: null},
				{name: 'MorbusTubMDRStudyResult_id', xtype: 'hidden', value: null},
				{
					fieldLabel: lang['data'],
					name: 'MorbusTubMDRStudyDrugResult_setDT',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['preparat'],
					sortField:'TubDrug_Code',
					comboSubject: 'TubDrug',
					anchor:'100%',
					hiddenName: 'TubDrug_id',
					xtype: 'swtubcommonsprcombo',
					isMDR: true,
					allowBlank: false
				}, {
					fieldLabel: lang['rezultat_testa'],
					hiddenName: 'TubDiagResultType_id',
					allowBlank: false,
					anchor:'100%',
					sortField:'TubDiagResultType_Code',
					comboSubject: 'TubDiagResultType',
					xtype: 'swcommonsprcombo'
				}
			],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MorbusTubMDRStudyDrugResult_id'},
				{name: 'MorbusTubMDRStudyResult_id'},
				{name: 'MorbusTubMDRStudyDrugResult_setDT'},
				{name: 'TubDrug_id'},
				{name: 'TubDiagResultType_id'}
			]),
			url: '/?c=MorbusTub&m=saveMorbusTubMDRStudyDrugResult'
		});
		Ext.apply(this, {
			buttons:
			[{
				handler: function() 
				{
					me.doSave();
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
					me.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		sw.Promed.swMorbusTubMDRStudyDrugResultWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('swMorbusTubMDRStudyDrugResultEditForm').getForm();
	}	
});