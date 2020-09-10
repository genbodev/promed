/**
* swMorbusTubStudyResultWindow - окно редактирования "Результаты исследований"
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

sw.Promed.swMorbusTubStudyResultWindow = Ext.extend(sw.Promed.BaseForm, {
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
	titleWin: lang['rezultatyi_issledovaniy'],
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
		params.TubStageChemType_id = this.form.getForm().findField('TubStageChemType_id').getValue();
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
				that.MorbusTubStudyResult_id = action.result.MorbusTubStudyResult_id;
				that.form.getForm().findField('MorbusTubStudyResult_id').setValue(that.MorbusTubStudyResult_id);
				if(cfg && typeof cfg.callback == 'function') {
					cfg.callback();
				} else {
					that.callback(that.owner, action.result.MorbusTubStudyResult_id);
					that.hide();
				}
			}
		});
	},
	openMorbusTubStudyMicrosResultWindow: function(action) 
	{
		var viewFrame = this.MorbusTubStudyMicrosResultFrame,
			win_name = 'swMorbusTubStudyMicrosResultWindow';
		this._openWindow(action, viewFrame, win_name, 'MorbusTubStudyMicrosResult');
	},
	openMorbusTubStudySeedResultWindow: function(action) 
	{
		var viewFrame = this.MorbusTubStudySeedResultFrame,
			win_name = 'swMorbusTubStudySeedResultWindow';
		this._openWindow(action, viewFrame, win_name, 'MorbusTubStudySeedResult');
	},
	openMorbusTubStudyXrayResultWindow: function(action) 
	{
		var viewFrame = this.MorbusTubStudyXrayResultFrame,
			win_name = 'swMorbusTubStudyXrayResultWindow';
		this._openWindow(action, viewFrame, win_name, 'MorbusTubStudyXrayResult');
	},
	openMorbusTubStudyDrugResultWindow: function(action) 
	{
		var viewFrame = this.MorbusTubStudyDrugResultFrame,
			win_name = 'swMorbusTubStudyDrugResultWindow';
		this._openWindow(action, viewFrame, win_name, 'MorbusTubStudyDrugResult');
	},
	openMorbusTubMolecularWindow: function(action) 
	{
		var viewFrame = this.MorbusTubMolecularFrame,
			win_name = 'swMorbusTubMolecularWindow';
		this._openWindow(action, viewFrame, win_name, 'MorbusTubMolecular');
	},
	openMorbusTubStudyHistolResultWindow: function(action) 
	{
		var viewFrame = this.MorbusTubStudyHistolResultFrame,
			win_name = 'swMorbusTubStudyHistolResultWindow';
		this._openWindow(action, viewFrame, win_name, 'MorbusTubStudyHistolResult');
	},
	_openWindow: function(action, viewFrame, win_name, obj) 
	{
		if(!action || !action.toString().inlist(['add','edit','view']))
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
					var params = { MorbusTubStudyResult_id: that.MorbusTubStudyResult_id };
					params.start = 0;
					params.limit = 100;
					viewFrame.loadData({globalFilters:params});
				},
				formParams: {}
			},
			id_key = obj+ '_id';
		if(action == 'add') {
			if (!this.MorbusTubStudyResult_id || this.MorbusTubStudyResult_id<0) { // запись пустая 
				this.doSave({
					callback: function(){
						that.action = 'edit';
						that.setTitle(that.titleWin+lang['_redaktirovanie']);
						params.formParams.MorbusTubStudyResult_id = that.MorbusTubStudyResult_id;
						getWnd(win_name).show(params);
					}
				});
			} else {
				params.formParams.MorbusTubStudyResult_id = this.MorbusTubStudyResult_id;
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
		sw.Promed.swMorbusTubStudyResultWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.MorbusTubStudyResult_id = null;
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
		if ( arguments[0].MorbusTubStudyResult_id ) {
			this.MorbusTubStudyResult_id = arguments[0].MorbusTubStudyResult_id;
		}
		if (this.MorbusTubStudyResult_id<0) { // запись пустая 
			this.action = 'add';
			arguments[0].formParams.TubStageChemType_id = Math.abs(this.MorbusTubStudyResult_id);
			//this.form.findField('TubStageChemType_id').setValue();
			this.MorbusTubStudyResult_id = null;
		}

		this.form.getForm().reset();
		this.MorbusTubStudyDrugResultFrame.removeAll(true);
		this.MorbusTubMolecularFrame.removeAll(true);
		this.MorbusTubStudyMicrosResultFrame.removeAll(true);
		this.MorbusTubStudySeedResultFrame.removeAll(true);
		this.MorbusTubStudyHistolResultFrame.removeAll(true);
		this.MorbusTubStudyXrayResultFrame.removeAll(true);
		
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
				that.form.getForm().findField('PersonWeight_Weight').focus(true,200);

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
						MorbusTubStudyResult_id: that.MorbusTubStudyResult_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						that.getLoadMask().hide();
						if (!result[0]) { return false; }
						that.form.getForm().setValues(result[0]);
						that.InformationPanel.load({
							Person_id: person_id
						});
						that.form.getForm().findField('PersonWeight_Weight').focus(true,200);
						var params = { MorbusTubStudyResult_id: that.MorbusTubStudyResult_id };
						params.start = 0;
						params.limit = 100;
						that.MorbusTubStudyDrugResultFrame.loadData({globalFilters:params});
						that.MorbusTubMolecularFrame.loadData({globalFilters:params});
						that.MorbusTubStudyMicrosResultFrame.loadData({globalFilters:params});
						that.MorbusTubStudySeedResultFrame.loadData({globalFilters:params});
						that.MorbusTubStudyHistolResultFrame.loadData({globalFilters:params});
						that.MorbusTubStudyXrayResultFrame.loadData({globalFilters:params});
					},
					url:'/?c=MorbusTub&m=loadMorbusTubStudyResult'
				});				
			break;	
		}
	},
	initComponent: function() {

		this.MorbusTubStudyDrugResultFrame = new sw.Promed.ViewFrame({
			title: lang['test_na_lekarstvennuyu_ustoichivost'],
			object: 'MorbusTubStudyDrugResult',
            obj_isEvn: false,
			editformclassname: 'swMorbusTubStudyDrugResultWindow',
			dataUrl: '/?c=MorbusTub&m=loadListMorbusTubStudyDrugResult',
			toolbar: true,
			border: true,
			height: 120,
			collapsible: true,
			autoScroll: true,
			autoLoadData: false,
			stringfields:[
				{name: 'MorbusTubStudyDrugResult_id', type: 'int', hidden: true, key: true},
				{name: 'MorbusTubStudyResult_id', type: 'int', hidden: true, isparams: true},
				{name: 'TubDrug_id', type: 'int', hidden: true},
				{name: 'MorbusTubStudyDrugResult_IsResult', type: 'int', hidden: true},
				{name: 'MorbusTubStudyDrugResult_setDT',  type: 'date', header: lang['data'], width: 100},
				{name: 'TubDrug_Name',  type: 'string', header: lang['tip_preparata'], autoexpand: true, autoExpandMin: 200},
				{name: 'MorbusTubStudyDrugResult_IsResult_Name',  type: 'string', header: lang['rezultat_testa'], width: 100}
			],
			actions: [
				{name:'action_add', handler: function() { this.openMorbusTubStudyDrugResultWindow('add'); }.createDelegate(this)},
				{name:'action_edit', handler: function() { this.openMorbusTubStudyDrugResultWindow('edit'); }.createDelegate(this)},
				{name:'action_view', handler: function() { this.openMorbusTubStudyDrugResultWindow('view'); }.createDelegate(this)},
				{name:'action_delete'},
				{name:'action_refresh'},
				{name:'action_print'}
			],
			onRowSelect: function(sm,i,record){
				this.setActionDisabled('action_edit',!record);
				this.setActionDisabled('action_view',!record);
				this.setActionDisabled('action_delete',!record);
			},
			paging: false,
			focusOnFirstLoad: false
		});

		this.MorbusTubMolecularFrame = new sw.Promed.ViewFrame({
			title: lang['molekulyarno_geneticheskie_metody'],
			object: 'MorbusTubMolecular',
            obj_isEvn: false,
			editformclassname: 'swMorbusTubMolecularWindow',
			dataUrl: '/?c=MorbusTub&m=loadListMorbusTubMolecular',
			toolbar: true,
			border: true,
			height: 120,
			collapsible: true,
			autoScroll: true,
			autoLoadData: false,
			stringfields:[
				{name: 'MorbusTubMolecular_id', type: 'int', hidden: true, key: true},
				{name: 'MorbusTubStudyResult_id', type: 'int', hidden: true, isparams: true},
				{name: 'MorbusTubMolecularType_id', type: 'int', hidden: true},
				{name: 'MorbusTubMolecular_IsResult', type: 'int', hidden: true},
				{name: 'MorbusTubMolecular_IsResult_Name',  type: 'string', header: lang['rezultat'], width: 100},
				{name: 'MorbusTubMolecular_setDT',  type: 'date', header: lang['data'], width: 100},
				{name: 'MorbusTubMolecularType_Name',  type: 'string', header: lang['test_na_lekarstvennuyu_ustoichivost'], autoexpand: true, autoExpandMin: 200}
			],
			actions: [
				{name:'action_add', handler: function() { this.openMorbusTubMolecularWindow('add'); }.createDelegate(this)},
				{name:'action_edit', handler: function() { this.openMorbusTubMolecularWindow('edit'); }.createDelegate(this)},
				{name:'action_view', handler: function() { this.openMorbusTubMolecularWindow('view'); }.createDelegate(this)},
				{name:'action_delete'},
				{name:'action_refresh'},
				{name:'action_print'}
			],
			onRowSelect: function(sm,i,record){
				this.setActionDisabled('action_edit',!record);
				this.setActionDisabled('action_view',!record);
				this.setActionDisabled('action_delete',!record);
			},
			paging: false,
			focusOnFirstLoad: false
		});
			
		this.MorbusTubStudyMicrosResultFrame = new sw.Promed.ViewFrame({
			title: lang['mikroskopiya'],
			object: 'MorbusTubStudyMicrosResult',
            obj_isEvn: false,
			editformclassname: 'swMorbusTubStudyMicrosResultWindow',
			dataUrl: '/?c=MorbusTub&m=loadListMorbusTubStudyMicrosResult',
			toolbar: true,
			border: true,
			height: 120,
			collapsible: true,
			autoScroll: true,
			autoLoadData: false,
			stringfields:[
				{name: 'MorbusTubStudyMicrosResult_id', type: 'int', hidden: true, key: true},
				{name: 'MorbusTubStudyResult_id', type: 'int', hidden: true, isparams: true},
				{name: 'MorbusTubStudyMicrosResult_setDT',  type: 'date', header: lang['data'], width: 100},
				{name: 'MorbusTubStudyMicrosResult_NumLab',  type: 'string', header: lang['№_obraztsa'], width: 100},
				{name: 'MorbusTubStudyMicrosResult_EdResult', type: 'int', hidden: true},
				{name: 'TubMicrosResultType_id', type: 'int', hidden: true},
				{name: 'TubMicrosResultType_Name',  type: 'string', header: lang['rezultat'], autoexpand: true, autoExpandMin: 200}
			],
			actions: [
				{name:'action_add', handler: function() { this.openMorbusTubStudyMicrosResultWindow('add'); }.createDelegate(this)},
				{name:'action_edit', handler: function() { this.openMorbusTubStudyMicrosResultWindow('edit'); }.createDelegate(this)},
				{name:'action_view', handler: function() { this.openMorbusTubStudyMicrosResultWindow('view'); }.createDelegate(this)},
				{name:'action_delete'},
				{name:'action_refresh'},
				{name:'action_print'}
			],
			onRowSelect: function(sm,i,record){
				this.setActionDisabled('action_edit',!record);
				this.setActionDisabled('action_view',!record);
				this.setActionDisabled('action_delete',!record);
			},
			paging: false,
			focusOnFirstLoad: false
		});
		
		this.MorbusTubStudySeedResultFrame = new sw.Promed.ViewFrame({
			title: lang['posev'],
			object: 'MorbusTubStudySeedResult',
            obj_isEvn: false,
			editformclassname: 'swMorbusTubStudySeedResultWindow',
			dataUrl: '/?c=MorbusTub&m=loadListMorbusTubStudySeedResult',
			toolbar: true,
			border: true,
			height: 120,
			collapsible: true,
			autoScroll: true,
			autoLoadData: false,
			stringfields:[
				{name: 'MorbusTubStudySeedResult_id', type: 'int', hidden: true, key: true},
				{name: 'MorbusTubStudyResult_id', type: 'int', hidden: true, isparams: true},
				{name: 'MorbusTubStudySeedResult_setDT',  type: 'date', header: lang['data'], width: 100},
				{name: 'TubSeedResultType_id', type: 'int', hidden: true},
				{name: 'TubSeedResultType_Name',  type: 'string', header: lang['rezultat'], autoexpand: true, autoExpandMin: 200}
			],
			actions: [
				{name:'action_add', handler: function() { this.openMorbusTubStudySeedResultWindow('add'); }.createDelegate(this)},
				{name:'action_edit', handler: function() { this.openMorbusTubStudySeedResultWindow('edit'); }.createDelegate(this)},
				{name:'action_view', handler: function() { this.openMorbusTubStudySeedResultWindow('view'); }.createDelegate(this)},
				{name:'action_delete'},
				{name:'action_refresh'},
				{name:'action_print'}
			],
			onRowSelect: function(sm,i,record){
				this.setActionDisabled('action_edit',!record);
				this.setActionDisabled('action_view',!record);
				this.setActionDisabled('action_delete',!record);
			},
			paging: false,
			focusOnFirstLoad: false
		});
		
		this.MorbusTubStudyHistolResultFrame = new sw.Promed.ViewFrame({
			title: lang['gistologiya'],
			object: 'MorbusTubStudyHistolResult',
            obj_isEvn: false,
			editformclassname: 'swMorbusTubStudyHistolResultWindow',
			dataUrl: '/?c=MorbusTub&m=loadListMorbusTubStudyHistolResult',
			toolbar: true,
			border: true,
			height: 120,
			collapsible: true,
			autoScroll: true,
			autoLoadData: false,
			stringfields:[
				{name: 'MorbusTubStudyHistolResult_id', type: 'int', hidden: true, key: true},
				{name: 'MorbusTubStudyResult_id', type: 'int', hidden: true, isparams: true},
				{name: 'MorbusTubStudyHistolResult_setDT',  type: 'date', header: lang['data'], width: 100},
				{name: 'TubDiagnosticMaterialType_id', type: 'int', hidden: true},
				{name: 'TubDiagnosticMaterialType_Name',  type: 'string', header: lang['material'], width: 200},
				{name: 'TubHistolResultType_id', type: 'int', hidden: true},
				{name: 'TubHistolResultType_Name',  type: 'string', header: lang['rezultat'], autoexpand: true, autoExpandMin: 200}
			],
			actions: [
				{name:'action_add', handler: function() { this.openMorbusTubStudyHistolResultWindow('add'); }.createDelegate(this)},
				{name:'action_edit', handler: function() { this.openMorbusTubStudyHistolResultWindow('edit'); }.createDelegate(this)},
				{name:'action_view', handler: function() { this.openMorbusTubStudyHistolResultWindow('view'); }.createDelegate(this)},
				{name:'action_delete'},
				{name:'action_refresh'},
				{name:'action_print'}
			],
			onRowSelect: function(sm,i,record){
				this.setActionDisabled('action_edit',!record);
				this.setActionDisabled('action_view',!record);
				this.setActionDisabled('action_delete',!record);
			},
			paging: false,
			focusOnFirstLoad: false
		});
		
		this.MorbusTubStudyXrayResultFrame = new sw.Promed.ViewFrame({
			title: lang['rentgen'],
			object: 'MorbusTubStudyXrayResult',
            obj_isEvn: false,
			editformclassname: 'swMorbusTubStudyXrayResultWindow',
			dataUrl: '/?c=MorbusTub&m=loadListMorbusTubStudyXrayResult',
			toolbar: true,
			border: true,
			height: 120,
			collapsible: true,
			autoScroll: true,
			autoLoadData: false,
			stringfields:[
				{name: 'MorbusTubStudyXrayResult_id', type: 'int', hidden: true, key: true},
				{name: 'MorbusTubStudyResult_id', type: 'int', hidden: true, isparams: true},
				{name: 'MorbusTubStudyXrayResult_setDT',  type: 'date', header: lang['data'], width: 100},
				{name: 'TubXrayResultType_id', type: 'int', hidden: true},
				{name: 'TubXrayResultType_Name',  type: 'string', header: lang['rezultat'], autoexpand: true, autoExpandMin: 200},
				{name: 'MorbusTubStudyXrayResult_Comment',  type: 'string', header: lang['primechanie'], width: 200}
			],
			actions: [
				{name:'action_add', handler: function() { this.openMorbusTubStudyXrayResultWindow('add'); }.createDelegate(this)},
				{name:'action_edit', handler: function() { this.openMorbusTubStudyXrayResultWindow('edit'); }.createDelegate(this)},
				{name:'action_view', handler: function() { this.openMorbusTubStudyXrayResultWindow('view'); }.createDelegate(this)},
				{name:'action_delete'},
				{name:'action_refresh'},
				{name:'action_print'}
			],
			onRowSelect: function(sm,i,record){
				this.setActionDisabled('action_edit',!record);
				this.setActionDisabled('action_view',!record);
				this.setActionDisabled('action_delete',!record);
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
			labelWidth: 150,
			labelAlign: 'right',
			region: 'center',
			items: [
				{name: 'MorbusTubStudyResult_id', xtype: 'hidden', value: null},
				{name: 'MorbusTub_id', xtype: 'hidden', value: null},
				{name: 'Evn_id', xtype: 'hidden', value: null},
				{name: 'Person_id', xtype: 'hidden', value: null},
				{name: 'PersonWeight_id', xtype: 'hidden', value: null},
				{
					fieldLabel: lang['mesyats_faza_lecheniya'],
					anchor:'100%',
					hiddenName: 'TubStageChemType_id',
					changeDisabled: false,
					disabled: true,
					xtype: 'swcommonsprcombo',
					typeCode: 'int',
					allowBlank: false,
					sortField:'TubStageChemType_Code',
					comboSubject: 'TubStageChemType'
				}, {
					allowBlank: true,
					allowNegative: false,
					decimalPrecision: 3,
					fieldLabel: lang['ves_cheloveka_v_kg'],
					name: 'PersonWeight_Weight',
					width: 100,
					xtype: 'numberfield'
				}
				,{
					autoScroll: true,
					//autoHeight: true,
					height: 400,
					items: [
						this.MorbusTubStudyDrugResultFrame
						,this.MorbusTubMolecularFrame
						,this.MorbusTubStudyMicrosResultFrame
						,this.MorbusTubStudySeedResultFrame
						,this.MorbusTubStudyHistolResultFrame
						,this.MorbusTubStudyXrayResultFrame
					]
				}
				],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MorbusTubStudyResult_id'},
				{name: 'MorbusTub_id'},
				{name: 'Person_id'},
				{name: 'PersonWeight_id'},
				{name: 'PersonWeight_Weight'},
				{name: 'TubStageChemType_id'}
			]),
			url: '/?c=MorbusTub&m=saveMorbusTubStudyResult'
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
		sw.Promed.swMorbusTubStudyResultWindow.superclass.initComponent.apply(this, arguments);
	}	
});