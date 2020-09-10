/**
* swSkladMO - окно просмотра списка параметров
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Alexander Permyakov (alexpm)
* @version      07.2013
* @comment      
*/

/*NO PARSE JSON*/

sw.Promed.swMonitorBirthSpecWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swMonitorBirthSpecWindow',
	objectSrc: '/jscore/Forms/Hospital/swMonitorBirthSpecWindow.js',
	type:null,
	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: lang['monitoring_novorojdennyih'],
	draggable: true,
	id: 'swMonitorBirthSpecWindow',
	width: 700,
	height: 500,
	modal: true,
	plain: true,
	resizable: false,
	maximized: true,
	//входные параметры
	action: null,
	onSelect: Ext.emptyFn,
	onHide: Ext.emptyFn,
	
	doReset: function() {
		var form = this.filterPanel.getForm(),
			grid = this.viewFrame.getGrid();
		var dt = form.findField('Period_DateRange').getValue();
		
		form.reset();
		if(dt){
			form.findField('Period_DateRange').setValue(dt)
		}else{
			var date1 = (Date.parseDate(getGlobalOptions().date, 'd.m.Y'));
			var dayOfWeek = (date1.getDay() + 6) % 7;
			date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
			var date2 = date1.add(Date.DAY, 6).clearTime();
			form.findField('Period_DateRange').setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		}
		if(form.findField('Lpu_bid').disabled){
			form.findField('Lpu_bid').setValue(getGloablOptions().lpu_id);
		}
		grid.getStore().baseParams = {};
		this.viewFrame.removeAll(true);
		this.viewFrame.ViewGridPanel.getStore().removeAll();
		this.doSearch();
	},
	doSearch: function() 
	{
		var form = this.filterPanel.getForm();
			//grid = this.viewFrame.getGrid(),
		var	params = {};
		
		params = form.getValues();
		if(form.findField('Lpu_bid').disabled){
			params.Lpu_bid = form.findField('Lpu_bid').getValue();
		}
		log(params,form.findField('PersonNewBorn_IsHighRisk'),this.viewFrame);
		params.limit = 100;
		params.start = 0;
		this.viewFrame.getGrid().getStore().baseParams={};
		this.viewFrame.getGrid().getStore().removeAll();
		this.viewFrame.getGrid().getStore().load({params:params});
	},
	initComponent: function() {
		var win = this;
		 var xg = Ext.grid;
		 
		this.filterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			id: 'MonitorBirthSpecWindowSearchForm',
			labelAlign: 'right',
			labelWidth: 160,
			region: 'north',
			items: [
				{
					xtype: 'daterangefield',
					fieldLabel: lang['rodilis_v_period'],
					name: 'Period_DateRange',
					hiddenName:'Period_DateRange',
					plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 170
				},
				{
					layout:'column',
					items:[
						{
							layout:'form',
							items:[
								{
									fieldLabel:lang['tekuschee_sostoyanie'],
									comboSubject:'State',
									hiddenName:'State_id',
									name:'State_id',
									width:300,
									xtype:'swcommonsprcombo'
								}
							]
						},
						{
							layout:'form',
							labelWidth: 170,
							items:[
								{
									enableKeyEvents: true,
									fieldLabel: lang['proba_dlya_neon_skrininga'],
									name: 'PersonNewBorn_IsNeonatal',
									width:80,
									xtype: 'checkbox',
									listeners: {
										check: function(combo,value){
										}
									}
								}
							]
						},
						{
							layout:'form',
							labelWidth: 100,
							items:[
								{
									enableKeyEvents: true,
									fieldLabel: lang['vyisokiy_risk'],
									name: 'PersonNewBorn_IsHighRisk',
									width:80,
									xtype: 'checkbox',
									listeners: {
										check: function(combo,value){
										}
									}
								},
							]
						},
					]
				}
				,
				{
					xtype: 'textfieldpmw',
					width: 300,
					id: 'mpwpSearch_FIO',
					fieldLabel: lang['fio'],
					name:'Person_FIO'
				},{
					layout:'column',
					items:[
						{
							layout:'form',
							items:[
								{
									fieldLabel: lang['mo_rojdeniya'],
									listeners: {
										'blur': function(combo) {
											if (!combo.getValue()) {
												combo.setRawValue('');
											}
										}
									},
									listWidth: 300,
									width:300,
									allowTextInput: true,
									ctxSerach:true,
									xtype: 'swlpucombo',
									hiddenName: 'Lpu_bid',
									name: 'Lpu_bid'
								}
							]
						},{
							layout:'form',
							labelWidth: 170,
							items:[
								{
									fieldLabel: lang['mo_gospitalizatsii'],
									listeners: {
										'blur': function(combo) {
											if (!combo.getValue()) {
												combo.setRawValue('');
											}
										}
									},
									listWidth: 300,
									width:300,
									allowTextInput: true,
									ctxSerach:true,
									xtype: 'swlpucombo',
									hiddenName: 'Lpu_hid',
									name: 'Lpu_hid'
								}
							]
						}
					]
					} 
			],
            buttons: [{
                handler: function() {
                    win.doSearch();
                },
                iconCls: 'search16',
                text: BTN_FRMSEARCH
            }, {
                handler: function() {
                    win.doReset();
					win.doSearch();
                },
                iconCls: 'resetsearch16',
                text: BTN_FRMRESET
            }],
            keys: [{
				fn: function(e) {
					this.doSearch();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		this.viewFrame = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 250,
			autoLoadData: false,
			dataUrl: '/?c=PersonNewBorn&m=loadMonitorBirthSpecGrid',
			id: 'MonitorBirthSpec',
			actions:
			[
				{name:'action_add', hidden:true },
				{name:'action_edit', hidden:true},
				{name:'action_view', handler:function(){
					this.openPersonBirthSpecific();
				}.createDelegate(this)},
				{name:'action_delete', hidden:true},
			],
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			totalProperty: 'totalCount',
			showCountInTop: false,
			stringfields: [
				{ header: 'PersonNewBorn_id', hidden:true, type: 'int', name: 'PersonNewBorn_id', key: true },
				
				{ header: 'isHighRisk', hidden:true, type: 'int', name: 'isHighRisk' },
				{ header: 'Person_cid', hidden:true, type: 'int', name: 'Person_cid' },
				{ header: 'Server_id', hidden:true, type: 'int', name: 'Server_id' },
				{ header: 'Person_mid', hidden:true, type: 'int', name: 'Person_mid' },
                { header: lang['fio'],  type: 'string', name: 'Person_FIO', id: 'autoexpand' },
                { header: lang['d_r'],  type: 'date', name: 'Person_BirthDay', width: 70 },
				{ header: lang['tekuschee_sostoyanie'],  type: 'string', name: 'State', width: 150 },
				{ header: lang['massa'],  type: 'string', name: 'PersonNewBorn_Weight', width: 70 },
				{ header: lang['otsenka'],  type: 'int', name: 'NewbornApgarRate_Values', width: 60 },
				{ header: lang['mo_rojdeniya'], name: 'LpuBirth', width: 100, type:'string' },
                { header: lang['mo_gospitalizatsii'],  type: 'string',width:120, name: 'LpuHosp' },
				{ header: lang['proba_dlya_neon_skrininga'], width:150, type: 'checkcolumn', name: 'PersonNewBorn_IsNeonatal' },
				{ header: lang['predstavitel'],  type: 'string', name: 'Deputy_FIO', width: 100 },
				{ header: lang['adres'],  type: 'string', name: 'Deputy_Addres', width: 100 },
				{ header: lang['telefon'],  type: 'string', name: 'Deputy_Phone', width: 100 },
				{ header: 'BirthSvid_id', hidden:true, type: 'int', name: 'BirthSvid_id' },
				{ header: 'PntDeathSvid_id', hidden:true, type: 'int', name: 'PntDeathSvid_id' },
				{ header: lang['med_svid_o_rojdenii'], width:130, name: 'BirthSvid',renderer: function(value, cellEl, rec) {
						var result = "";
						// разделить value по ,
						if (!Ext.isEmpty(value)) {
							result ="<a href='javascript://' onClick='getWnd(\"swMedSvidBirthEditWindow\").show({\"action\":\"view\",\"formParams\":{\"BirthSvid_id\":"+rec.get('BirthSvid_id')+"}})'>"+value+"</a>";
						}
						
						return  result;
					} },
				{ header: lang['med_svid_o_per_smerti'],width:150, name: 'DeathSvid',renderer: function(value, cellEl, rec) {
						var result = "";
						// разделить value по ,
						if (!Ext.isEmpty(value)) {
							result ="<a href='javascript://' onClick='getWnd(\"swMedSvidPntDeathEditWindow\").show({\"action\":\"view\",\"formParams\":{\"PntDeathSvid_id\":"+rec.get('PntDeathSvid_id')+"}})'>"+value+"</a>";
						}
						
						return  result;
					} },
				{ header: lang['vich_inf_u_materi'], width:120, type: 'checkcolumn', name: 'PersonNewborn_IsAidsMother' },
				{ header: lang['otkaz_ot_rebenka'],  type: 'checkcolumn', name: 'PersonNewborn_IsRejection' }
			],
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
              
			},
			onDblClick: function(grid, rowIdx, colIdx, event) {
				this.openPersonBirthSpecific(grid);
			}.createDelegate(this),
			onEnter: function()
			{
				
			}
		});
		this.viewFrame.getGrid().view = new Ext.grid.GridView({
			getRowClass : function (row, index)
			{
				var cls = '';

				if (row.get('isHighRisk') == 1) {
					cls = cls + 'x-grid-rowred ';
				}

				return cls;
			},
			listeners:
			{
				rowupdated: function(view, first, record)
				{
					view.getRowClass(record);
				}
			}
		});
		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					win.hide();
				},
				iconCls: 'cancel16',
				onTabElement: 'WIN_ParameterValue_Alias',
				text: BTN_FRMCLOSE
			}],
			items: [ 
				this.filterPanel,
				this.viewFrame
			]
		});
		sw.Promed.swMonitorBirthSpecWindow.superclass.initComponent.apply(this, arguments);
        this.viewFrame.ViewToolbar.on('render', function(vt){
            this.ViewActions['actions'] = new Ext.Action({
                name:'action_openemk',
				tooltip: lang['otkryit_emk'],
				iconCls : 'x-btn-text',
				icon: 'img/icons/actions16.png',
				handler: function() {win.emkOpen(1)},
				key: 'actions',
				text:lang['otkryit_emk']
            });
            vt.insertButton(1,this.ViewActions['actions']);
			this.ViewActions['actions'] = new Ext.Action({
                name:'action_openmotheremk',
				tooltip: lang['otkryit_emk_materi'],
				iconCls : 'x-btn-text',
				icon: 'img/icons/actions16.png',
				handler: function() {win.emkOpen(2)},
				key: 'actions',
				text:lang['otkryit_emk_materi']
            });
            vt.insertButton(1,this.ViewActions['actions']);
            return true;
        }, this.viewFrame);
	},
	emkOpen: function(type)
	{
		var grid = this.viewFrame.getGrid();
		var that = this;
		var record = grid.getSelectionModel().getSelected();
		if ( !record || (type==1 &&!record.get('Person_cid')) || (type==2 &&!record.get('Person_mid')) )
		{
			if(type==2 &&!record.get('Person_mid')){
				Ext.Msg.alert(lang['oshibka'], lang['u_vyibrannoy_zapisi_otsutstvuet_svyaz_s_materyu']);
			}else if(type==1 &&!record.get('Person_cid')){
				Ext.Msg.alert(lang['oshibka'], lang['ne_peredan_identifikator_cheloveka']);
			}else{
				Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			}
			
			return false;
		}
		getWnd('swPersonEmkWindow').show({
			Person_id: (type==1)?record.get('Person_cid'):record.get('Person_mid'),
			readOnly: (that.editType=='onlyRegister')?true:false,
			Server_id: record.get('Server_id'),
			userMedStaffFact: this.userMedStaffFact,
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			ARMType: 'common',
			addStacActions: (that.editType=='onlyRegister')?[]:["action_New_EvnPS", "action_StacSvid", "action_EvnPrescrVK", "action_EvnJournal"],
			callback: function()
			{
				//
			}.createDelegate(this)
		});
	},
	openPersonBirthSpecific: function(grid){
		if(!grid)
			var grid = this.viewFrame.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if ( typeof record != 'object' || Ext.isEmpty(record.get('Person_cid')) ) {
			return false;
		}

		var params = {
			action: 'view',
			Person_id: record.get('Person_cid')
		};

		getWnd('swPersonBirthSpecific').show(params);
	},
	show: function() {
		
		sw.Promed.swMonitorBirthSpecWindow.superclass.show.apply(this, arguments);
		this.userMedStaffFact= null;
		if (!arguments[0])
		{
			arguments = [{}];
		}else{
			if(arguments[0].action){
				this.action = arguments[0].action;
			}
			if(arguments[0].userMedStaffFact){
				this.userMedStaffFact = arguments[0].userMedStaffFact;
			}
		}
		this.editType = 'all';
        if(arguments[0] && arguments[0].editType){
            this.editType = arguments[0].editType;
        }
		var base_form = this.filterPanel.getForm();
		
		var store = [
				{State_id:1,	State_Name:lang['vyipisan']},
				{State_id:2,	State_Name:lang['umer']},
				{State_id:3,	State_Name:lang['v_statsionare']},
			];
		base_form.findField('State_id').getStore().loadData(store,false);
		if(this.action == 'view'){
			this.viewFrame.ViewToolbar.items.items[0].hide();
		}else{
			this.viewFrame.ViewToolbar.items.items[0].show();
		}
		//var base_form = this.filterPanel.getForm();
		var grid = this.viewFrame.getGrid();
		base_form.reset();
		base_form.findField('PersonNewBorn_IsHighRisk').setValue(false);
		grid.getStore().baseParams = {};
		this.viewFrame.removeAll(true);
		this.viewFrame.ViewGridPanel.getStore().removeAll();
		
		var date1 = (Date.parseDate(getGlobalOptions().date, 'd.m.Y'));
		var dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		var date2 = date1.add(Date.DAY, 6).clearTime();
		base_form.findField('Period_DateRange').setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		if (!isUserGroup('OperRegBirth') && !haveArmType('spec_mz')) {
			base_form.findField('Lpu_bid').setValue(getGlobalOptions().lpu_id);
			base_form.findField('Lpu_bid').disable();
		} else {
			base_form.findField('Lpu_bid').enable();
		}
		//log(base_form.findField('Lpu_bid'),22222)
		this.doSearch();
	}
});