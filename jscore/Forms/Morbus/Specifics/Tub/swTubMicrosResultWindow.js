/**
* swTubMicrosResultWindow - окно редактирования "Результаты микроскопических исследований"
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

sw.Promed.swTubMicrosResultWindow = Ext.extend(sw.Promed.BaseForm, {
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
	width: 500,
	titleWin: lang['rezultatyi_mikroskopicheskih_issledovaniy'],
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
					that.findById('swTubMicrosResultEditForm').getFirstInvalidEl().focus(true);
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
				that.callback(that.owner, action.result.TubMicrosResult_id);
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
		sw.Promed.swTubMicrosResultWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.TubMicrosResult_id = null;
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
		if ( arguments[0].TubMicrosResult_id ) {
			this.TubMicrosResult_id = arguments[0].TubMicrosResult_id;
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
		that.form.setValues(arguments[0]);
		
		var person_id = arguments[0].Person_id;
		this.getLoadMask().show();
		switch (arguments[0].action) {
			case 'add':
				that.form.setValues(arguments[0].formParams);
				that.InformationPanel.load({
					Person_id: person_id
				});
				that.form.findField('TubMicrosResult_MicrosDT').focus(true,200);
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
						TubMicrosResult_id: that.TubMicrosResult_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						that.getLoadMask().hide();
						if (!result[0]) { return false; }
						that.form.setValues(result[0]);
						that.InformationPanel.load({
							Person_id: person_id
						});
						var combo = that.form.findField('TubMicrosResultType_id');
						combo.fireEvent('select', combo, combo.getValue(), null);
						
						that.form.findField('TubMicrosResult_MicrosDT').focus(true,200);
					},
					url:'/?c=MorbusTub&m=loadTubMicrosResult'
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
			id: 'swTubMicrosResultEditForm',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			frame: true,
			border: false,
			labelWidth: 200,
			labelAlign: 'right',
			region: 'center',
			items: [
				{name: 'TubMicrosResult_id', xtype: 'hidden', value: null},
				{name: 'EvnDirectionTub_id', xtype: 'hidden', value: null},
				{name: 'Evn_id', xtype: 'hidden', value: null},
				{
					fieldLabel: lang['data_issledovaniya'],
					name: 'TubMicrosResult_MicrosDT',
					allowBlank:false,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					xtype: 'fieldset',
					autoHeight: true,
					title: lang['obraztsyi'],
					labelWidth: 190,
					items: [{
						fieldLabel: lang['№_obraztsa_mokrotyi'],
						name: 'TubMicrosResult_Num',
						xtype: 'textfield',
						allowBlank:true
					}, {
						fieldLabel: lang['data_sbora_obraztsov'],
						name: 'TubMicrosResult_setDT',
						allowBlank:false,
						xtype: 'swdatefield',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
					}]
				}, {
					xtype: 'fieldset',
					autoHeight: true,
					title: lang['rezultat'],
					labelWidth: 190,
					items: [{
						fieldLabel: lang['rezultat_mikroskopii'],
						anchor:'100%',
						hiddenName: 'TubMicrosResultType_id',
						xtype: 'swcommonsprcombo',
						typeCode: 'int',
						allowBlank:false,
						sortField:'TubMicrosResultType_Code',
						comboSubject: 'TubMicrosResultType', 
						listeners: {
							change: function(combo, nv, ov) {
								this.form.findField('TubMicrosResult_EdResult').setDisabled((nv!=2));
								this.form.findField('TubMicrosResult_EdResult').setValue(null);
							}.createDelegate(this)
						}/*, 
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{TubMicrosResultType_Name}',
							'</div></tpl>'
						)*/
					}, {
						allowBlank: false,
						allowNegative: false,
						decimalPrecision: 3,
						changeDisabled: false,
						disabled: true,
						fieldLabel: lang['chislo_mikobakteriy'],
						name: 'TubMicrosResult_EdResult',
						width: 100,
						xtype: 'numberfield'
					}]
				}, {
					fieldLabel: lang['primechanie'],
					anchor:'100%',
					name: 'TubMicrosResult_Comment',
					xtype: 'textfield',
					allowBlank:true
				}],
				
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'TubMicrosResult_id'},
				{name: 'EvnDirectionTub_id'},
				{name: 'TubMicrosResult_Num'},
				{name: 'TubMicrosResult_setDT'},
				{name: 'TubMicrosResult_MicrosDT'},
				{name: 'TubMicrosResultType_id'},
				{name: 'TubMicrosResult_EdResult'},
				{name: 'TubMicrosResult_Comment'}
			]),
			url: '/?c=MorbusTub&m=saveTubMicrosResult'
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
		sw.Promed.swTubMicrosResultWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('swTubMicrosResultEditForm').getForm();
	}	
});