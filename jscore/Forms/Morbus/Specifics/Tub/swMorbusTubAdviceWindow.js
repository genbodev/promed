/**
* swMorbusTubAdviceWindow - окно редактирования "Консультация фтизиохирурга"
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

sw.Promed.swMorbusTubAdviceWindow = Ext.extend(sw.Promed.BaseForm, {
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
	titleWin: lang['konsultatsiya_ftiziohirurga'],
	autoHeight: true,
	//maximizable: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSave:  function(cfg) {
		var that = this;
		if ( !this.form.getForm().isValid() )
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					that.form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
        this.submit(cfg);
		return true;		
	},
	submit: function(cfg) {
		var that = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = new Object();
		params.action = that.action;
		this.form.getForm().submit({
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
				that.MorbusTubAdvice_id = action.result.MorbusTubAdvice_id;
				that.form.getForm().findField('MorbusTubAdvice_id').setValue(that.MorbusTubAdvice_id);
				if(cfg && typeof cfg.callback == 'function') {
					cfg.callback();
				} else {
					that.callback(that.owner, action.result.MorbusTubAdvice_id);
					that.hide();
				}
			}
		});
	},
	openMorbusTubAdviceOperWindow: function(action) 
	{
		var viewFrame = this.MorbusTubAdviceOperFrame,
			win_name = 'swMorbusTubAdviceOperWindow';
		this._openWindow(action, viewFrame, win_name, 'MorbusTubAdviceOper');
	},
	_openWindow: function(action, viewFrame, win_name, obj) 
	{
		if(!action || !action.toString().inlist(['add','edit']))
		{
			return false;
		}
		var grid = viewFrame.getGrid(),
			that = this,
			record = grid.getSelectionModel().getSelected(),
			params = {
				action: action,
				callback: function(data){
					viewFrame.removeAll(true);
					var params = { MorbusTubAdvice_id: that.MorbusTubAdvice_id };
					params.start = 0;
					params.limit = 100;
					viewFrame.loadData({globalFilters:params});
				},
				formParams: {}
			},
			id_key = obj+ '_id';
		if(action == 'add') {
			if (!this.MorbusTubAdvice_id || this.MorbusTubAdvice_id<0) { // запись пустая 
				this.doSave({
					callback: function(){
						that.action = 'edit';
						that.setTitle(that.titleWin+lang['_redaktirovanie']);
						params.formParams.MorbusTubAdvice_id = that.MorbusTubAdvice_id;
						getWnd(win_name).show(params);
					}
				});
			} else {
				params.formParams.MorbusTubAdvice_id = this.MorbusTubAdvice_id;
				getWnd(win_name).show(params);
			}
		} else {
			if(!record || !record.data)
			{
				return false;
			}
			params.formParams = record.data;
			params[id_key] = record.get(id_key);
			getWnd(win_name).show(params);
		}
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
		sw.Promed.swMorbusTubAdviceWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.MorbusTubAdvice_id = null;
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
		if ( arguments[0].MorbusTubAdvice_id ) {
			this.MorbusTubAdvice_id = arguments[0].MorbusTubAdvice_id;
		}
		if (this.MorbusTubAdvice_id<0) { // запись пустая 
			this.action = 'add';
			this.MorbusTubAdvice_id = null;
		}

		this.form.getForm().reset();
		this.MorbusTubAdviceOperFrame.removeAll(true);
		
		switch (this.action) {
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
		switch (this.action) {
			case 'add':
				that.form.getForm().setValues(arguments[0].formParams);
				that.InformationPanel.load({
					Person_id: arguments[0].formParams.Person_id
				});
				that.form.getForm().findField('MorbusTubAdvice_setDT').focus(true,200);

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
						MorbusTubAdvice_id: that.MorbusTubAdvice_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						that.getLoadMask().hide();
						if (!result[0]) { return false; }
						that.form.getForm().setValues(result[0]);
						that.InformationPanel.load({
							Person_id: person_id
						});
						that.form.getForm().findField('MorbusTubAdvice_setDT').focus(true,200);
						var params = { MorbusTubAdvice_id: that.MorbusTubAdvice_id };
						params.start = 0;
						params.limit = 100;
						that.MorbusTubAdviceOperFrame.loadData({globalFilters:params});
					},
					url:'/?c=MorbusTub&m=loadMorbusTubAdvice'
				});				
			break;	
		}
	},
	initComponent: function() {

		this.MorbusTubAdviceOperFrame = new sw.Promed.ViewFrame({
			title: lang['operativnoe_lechenie'],
			object: 'MorbusTubAdviceOper',
			editformclassname: 'swMorbusTubAdviceOperWindow',
			dataUrl: '/?c=MorbusTub&m=loadListMorbusTubAdviceOper',
			toolbar: true,
			border: true,
			height: 220,
			collapsible: true,
			autoScroll: true,
			autoLoadData: false,
			stringfields:[
				{name: 'MorbusTubAdviceOper_id', type: 'int', hidden: true, key: true},
				{name: 'MorbusTubAdvice_id', type: 'int', hidden: true, isparams: true},
				{name: 'MorbusTubAdviceOper_setDT',  type: 'date', header: lang['data_operatsii'], width: 150},
				{name: 'UslugaComplex_id', type: 'int', hidden: true},
				{name: 'UslugaComplex_Name',  type: 'string', header: lang['tip_operatsii'], autoexpand: true, autoExpandMin: 200}
			],
			actions: [
				{name:'action_add', handler: function() { this.openMorbusTubAdviceOperWindow('add'); }.createDelegate(this)},
				{name:'action_edit', handler: function() { this.openMorbusTubAdviceOperWindow('edit'); }.createDelegate(this)},
				{name:'action_view', hidden: true, disabled: true},
				{name:'action_delete'},
				{name:'action_refresh'},
				{name:'action_print'}
			],
			onRowSelect: function(sm,i,record){
				this.setActionDisabled('action_edit',!record.get('MorbusTubAdvice_id'));
				//this.setActionDisabled('action_view',!record.get('MorbusTubAdvice_id'));
				this.setActionDisabled('action_delete',!record.get('MorbusTubAdvice_id'));
			},
			paging: false,
			focusOnFirstLoad: false
		});
			
		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});
		this.form = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle:'background:#DFE8F6;padding:0px;',
			frame: false,
			border: false,
			labelWidth: 160,
			labelAlign: 'right',
			region: 'center',
			items: [
				{name: 'MorbusTubAdvice_id', xtype: 'hidden', value: null},
				{name: 'MorbusTub_id', xtype: 'hidden', value: null},
				{name: 'Evn_id', xtype: 'hidden', value: null},
				{name: 'Person_id', xtype: 'hidden', value: null},
				{
					fieldLabel: lang['data_konsultatsii'],
					name: 'MorbusTubAdvice_setDT',
					allowBlank: false,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['rezultat_konsultatsii'],
					anchor:'100%',
					hiddenName: 'TubAdviceResultType_id',
					xtype: 'swcommonsprcombo',
					typeCode: 'int',
					allowBlank: false,
					sortField:'TubAdviceResultType_Code',
					comboSubject: 'TubAdviceResultType'
				}
				,this.MorbusTubAdviceOperFrame
				],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MorbusTubAdvice_id'},
				{name: 'MorbusTub_id'},
				{name: 'Person_id'},
				{name: 'MorbusTubAdvice_setDT'},
				{name: 'TubAdviceResultType_id'}
			]),
			url: '/?c=MorbusTub&m=saveMorbusTubAdvice'
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
			items:[this.InformationPanel,this.form]
		});
		sw.Promed.swMorbusTubAdviceWindow.superclass.initComponent.apply(this, arguments);
	}	
});