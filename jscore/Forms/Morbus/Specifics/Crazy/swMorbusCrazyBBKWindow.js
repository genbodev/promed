/**
* swMorbusCrazyBBKWindow - окно редактирования "Военно-врачебная комиссия"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       A. Markoff copypasted by A. Kurakin
* @version      2016/09
* @comment      
*/

sw.Promed.swMorbusCrazyBBKWindow = Ext.extend(sw.Promed.BaseForm, {
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
	titleWin: lang['voenno_vrachebnaya_komissia'],
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
					that.findById('swMorbusCrazyBBKEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		var setDT = that.form.findField('MorbusCrazyBBK_setDT').getValue();
		var firstDT = that.form.findField('MorbusCrazyBBK_firstDT').getValue();
		var lastDT = that.form.findField('MorbusCrazyBBK_lidDT').getValue();

		if(firstDT < setDT) {
			sw.swMsg.alert(lang['soobschenie'], '«Дата установки диагноза» должна быть больше/равна «Дате осмотра»');
            return false;
		}
		if(lastDT < firstDT) {
			sw.swMsg.alert(lang['soobschenie'], '«Дата установки заключительного диагноза» должна быть больше/равна «Дате установки диагноза»');
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
				that.callback(that.owner, action.result.MorbusCrazyBBK_id);
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
		sw.Promed.swMorbusCrazyBBKWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.MorbusCrazyBBK_id = null;
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
		if ( arguments[0].MorbusCrazyBBK_id ) {
			this.MorbusCrazyBBK_id = arguments[0].MorbusCrazyBBK_id;
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
				that.form.findField('MorbusCrazyBBK_setDT').focus(true,200);

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
						MorbusCrazyBBK_id: that.MorbusCrazyBBK_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						that.getLoadMask().hide();
						if (!result[0]) { return false; }
						that.form.setValues(result[0]);
						that.InformationPanel.load({
							Person_id: person_id
						});
						that.form.findField('MorbusCrazyBBK_setDT').focus(true,200);
					},
					url:'/?c=MorbusCrazy&m=loadMorbusCrazyBBK'
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
			id: 'swMorbusCrazyBBKEditForm',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			frame: true,
			border: false,
			labelWidth: 220,
			labelAlign: 'right',
			region: 'center',
			items: [
				{name: 'MorbusCrazyBBK_id', xtype: 'hidden', value: null},
				{name: 'MorbusCrazy_id', xtype: 'hidden', value: null},
				{name: 'Evn_id', xtype: 'hidden', value: null},
				{
					fieldLabel: lang['data_osmotra'],
					name: 'MorbusCrazyBBK_setDT',
					allowBlank:false,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					listeners: {
						change: function(field,newVal){
							var first = this.form.findField('MorbusCrazyBBK_firstDT');
							if(Ext.isEmpty(first.getValue())){
								first.setValue(newVal);
								first.fireEvent('change',first,newVal);
							}

						}.createDelegate(this)
					}
				}, {
					fieldLabel: lang['diagnoz_predvaritelniy'],
					anchor:'100%',
					hiddenName: 'CrazyDiag_id',
					xtype: 'swcommonsprcombo',
					allowBlank: false,
					sortField:'CrazyDiag_Code',
					comboSubject: 'CrazyDiag'
				}, {
					fieldLabel: lang['data_ustanovki_diagnoza'],
					name: 'MorbusCrazyBBK_firstDT',
					allowBlank:false,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					listeners: {
						change: function(field,newVal){
							var last = this.form.findField('MorbusCrazyBBK_lidDT');
							if(Ext.isEmpty(last.getValue())){
								last.setValue(newVal);
							}
						}.createDelegate(this)
					}
				}, {
					displayField: 'MedicalCareType_Name',
					editable: false,
					allowBlank: false,
					fieldLabel: lang['vvk'],
					hiddenName: 'MedicalCareType_id',
					mode: 'local',
					store: new Ext.data.SimpleStore({
						key: 'MedicalCareType_id',
						autoLoad: false,
						fields: [
							{name:'MedicalCareType_id',type:'int'},
							{name:'MedicalCareType_Name',type:'string'}
						],
						data: [
							['1','амбулаторно'],
							['2','стационарно']
						]
					}),
					tpl:'<tpl for="."><div class="x-combo-list-item"><table border="0" width="100%"><tr><td style="width: 100px">{MedicalCareType_Name}</td>'+
							'</tr></table>'+
							'</div></tpl>',
					valueField: 'MedicalCareType_id',
					anchor:'100%',
					xtype: 'swbaselocalcombo'
				}, {
					fieldLabel: lang['zakluchitelniy_diagnoz'],
					anchor:'100%',
					hiddenName: 'CrazyDiag_lid',
					xtype: 'swcommonsprcombo',
					allowBlank: false,
					sortField:'CrazyDiag_Code',
					comboSubject: 'CrazyDiag'
				}, {
					fieldLabel: lang['data_ustanovki_zakluchitelnogo_diagnoza'],
					name: 'MorbusCrazyBBK_lidDT',
					allowBlank:false,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MorbusCrazyBBK_id'},
				{name: 'MorbusCrazy_id'},
				{name: 'MorbusCrazyBBK_setDT'},
				{name: 'CrazyDiag_id'},
				{name: 'MorbusCrazyBBK_firstDT'},
				{name: 'MedicalCareType_id'},
				{name: 'CrazyDiag_lid'},
				{name: 'MorbusCrazyBBK_lidDT'}
			]),
			url: '/?c=MorbusCrazy&m=saveMorbusCrazyBBK'
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
		sw.Promed.swMorbusCrazyBBKWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('swMorbusCrazyBBKEditForm').getForm();
	}	
});