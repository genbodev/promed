/**
* swMorbusCrazyForceTreatWindow - окно редактирования "Принудительное лечение"
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

sw.Promed.swMorbusCrazyForceTreatWindow = Ext.extend(sw.Promed.BaseForm, {
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
	titleWin: lang['prinuditelnoe_lechenie'],
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
					that.findById('swMorbusCrazyForceTreatEditForm').getFirstInvalidEl().focus(true);
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
	submit: function(onlySave) {
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
					var id = action.result.MorbusCrazyForceTreat_id;
					if (id) {
						if (!onlySave || (onlySave!==1)) {
							that.callback(that.owner, id);
							that.hide();
						}
						else
						{
							that.form.findField('MorbusCrazyForceTreat_id').setValue(id);
							that.grid.params = 
							{
								MorbusCrazyForceTreat_id: id,
								Person_id: that.person_id
							};
							that.grid.gFilters = 
							{
								MorbusCrazyForceTreat_id: id,
								Person_id: that.person_id
							};
							that.action = 'edit';
							that.grid.run_function_add = false;
							that.grid.getAction('action_add').execute();
						}
					}
				}
			},
			success: function(result_form, action)
			{
				loadMask.hide();
				that.callback(that.owner, action.result.MorbusCrazyForceTreat_id);
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
		sw.Promed.swMorbusCrazyForceTreatWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.MorbusCrazyForceTreat_id = null;
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
		if ( arguments[0].MorbusCrazyForceTreat_id ) {
			this.MorbusCrazyForceTreat_id = arguments[0].MorbusCrazyForceTreat_id;
		}

		this.form.reset();
		this.grid.removeAll({clearAll: true});
		
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
		that.person_id = arguments[0].formParams.Person_id;
        this.getLoadMask().show();
		switch (arguments[0].action) {
			case 'add':
				that.form.setValues(arguments[0].formParams);
				that.InformationPanel.load({
					Person_id: that.person_id
				});
				that.grid.setParam('Person_id', that.person_id, false);
				that.form.findField('MorbusCrazyForceTreat_begDT').focus(true,200);

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
						MorbusCrazyForceTreat_id: that.MorbusCrazyForceTreat_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						that.getLoadMask().hide();
						if (!result[0]) { return false; }
						that.form.setValues(result[0]);
						that.InformationPanel.load({
							Person_id: that.person_id
						});
						that.form.findField('MorbusCrazyForceTreat_begDT').focus(true,200);
						that.grid.loadData({globalFilters:{MorbusCrazyForceTreat_id:that.MorbusCrazyForceTreat_id, Person_id: that.person_id}, params: {MorbusCrazyForceTreat_id:that.MorbusCrazyForceTreat_id, Person_id: that.person_id}, noFocusOnLoad:true});
					},
					url:'/?c=MorbusCrazy&m=loadMorbusCrazyForceTreat'
				});				
			break;	
		}
	},
	initComponent: function() {
		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});
		
		this.MainRecordAdd = function() {
		
			if (this.form.isValid()) {
				
				if (this.form.findField('MorbusCrazyForceTreat_id').getValue()>0) {
					this.grid.run_function_add = false;
					this.grid.getAction('action_add').execute();;
				} else {
					this.submit(1);
				}
				
			}
			return false;
		}.createDelegate(this);

		this.grid = new sw.Promed.ViewFrame(
		{
			title:lang['izmeneniya_vida'],
			object: 'MorbusCrazyUpForceTreat',
			editformclassname: 'swMorbusCrazyUpForceTreatWindow',
			dataUrl: '/?c=MorbusCrazy&m=loadMorbusCrazyUpForceTreat',
			autoLoadData: false,
			stringfields:
			[
				{name: 'MorbusCrazyUpForceTreat_id', type: 'int', header: 'ID', key: true},
				{name: 'MorbusCrazyForceTreat_id', type: 'int', hidden: true, isparams: true},
				{name: 'CrazyForceTreatType_id', type: 'int', hidden: true, isparams: true},
				{name: 'MorbusCrazyUpForceTreat_setDT', type: 'date', header: lang['data_izmeneniya']},
				{name: 'CrazyForceTreatType_Name', type: 'string', header: lang['vid'], autoexpand: true}
			],
			actions:
			[
				{name:'action_add', func: function() { this.MainRecordAdd() }.createDelegate(this)},
				{name:'action_edit'},
				{name:'action_view'},
				{name:'action_delete'},
				{name:'action_refresh'},
				{name:'action_print', visible: false}
			],
			//focusOn: {name:'lrOk',type:'button'},
			focusPrev: {name:'CrazyForceTreatType_id',type:'field'},
			focusOnFirstLoad: false
		});
		
		var form = new Ext.form.FormPanel({
			autoHeight: true,
			id: 'swMorbusCrazyForceTreatEditForm',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			frame: true,
			border: false,
			labelWidth: 200,
			labelAlign: 'right',
			region: 'center',
			items: [
				{name: 'MorbusCrazyForceTreat_id', xtype: 'hidden', value: null},
				{name: 'MorbusCrazyBase_id', xtype: 'hidden', value: null},
				{name: 'Evn_id', xtype: 'hidden', value: null},
				{
					fieldLabel: lang['data_nachala'],
					name: 'MorbusCrazyForceTreat_begDT',
					allowBlank:false,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['data_okonchaniya'],
					name: 'MorbusCrazyForceTreat_endDT',
					allowBlank:true,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['vid_prinuditelnogo_lecheniya'],
					anchor:'100%',
					hiddenName: 'CrazyForceTreatType_id',
					xtype: 'swcommonsprcombo',
					allowBlank: false,
					sortField:'CrazyForceTreatType_Code',
					comboSubject: 'CrazyForceTreatType'
				}
			],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MorbusCrazyForceTreat_id'},
				{name: 'MorbusCrazyBase_id'},
				{name: 'MorbusCrazyForceTreat_begDT'},
				{name: 'MorbusCrazyForceTreat_endDT'},
				{name: 'CrazyForceTreatType_id'}, 
				{name: 'Evn_id'}
			]),
			url: '/?c=MorbusCrazy&m=saveMorbusCrazyForceTreat'
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
			items:[this.InformationPanel,form, this.grid]
		});
		sw.Promed.swMorbusCrazyForceTreatWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('swMorbusCrazyForceTreatEditForm').getForm();
	}	
});