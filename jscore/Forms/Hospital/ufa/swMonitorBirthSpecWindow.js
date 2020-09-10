/**
* swMonitorBirthSpecWindow - основное окно регистра новорожденных для Уфа
*
* @author		gilmijarov artur (ufa)
* @version		25.12.2019
* @comment
*/

/*NO PARSE JSON*/

sw.Promed.swMonitorBirthSpecWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swMonitorBirthSpecWindow',
	objectSrc: '/jscore/Forms/Hospital/ufa/swMonitorBirthSpecWindow.js',
	type:null,
	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: 'Мониторинг детей первого года жизни',
	draggable: true,
	id: 'swMonitorBirthSpecWindow',
	width: 700,
	height: 500,
	modal: true,
	plain: true,
	resizable: false,
	maximized: true,
	action: null,
	onSelect: Ext.emptyFn,
	onHide: Ext.emptyFn,

	doReset: function() {
		var form = this.NewBornFilterPanel.getForm(),
			grid = this.NewBornGridPanel.getGrid(),
			gridoneage = this.OneAgeGridPanel.getGrid(),
			gridallage = this.AllAgeGridPanel.getGrid(),
			formmonitor = this.MonitorCenterFilterPanel.getForm(),
			gridmonitor = this.MonitorCenterGridPanel.getGrid();
		form.reset();
		formmonitor.reset();

		grid.getStore().baseParams = {};
		this.NewBornGridPanel.removeAll(true);
		this.NewBornGridPanel.ViewGridPanel.getStore().removeAll();

		gridoneage.getStore().baseParams = {};
		this.OneAgeGridPanel.removeAll(true);
		this.OneAgeGridPanel.ViewGridPanel.getStore().removeAll();

		gridallage.getStore().baseParams = {};
		this.AllAgeGridPanel.removeAll(true);
		this.AllAgeGridPanel.ViewGridPanel.getStore().removeAll();

		gridmonitor.getStore().baseParams = {};
		this.MonitorCenterGridPanel.removeAll(true);
		this.MonitorCenterGridPanel.ViewGridPanel.getStore().removeAll();

	},
	doSearch: function()
	{
		tabId = this.TabPanel.getActiveTab().getId();
		switch (tabId){
			case 'OneAgePanel':
					var filterPanel = this.NewBornFilterPanel;
					var gridPanel = this.OneAgeGridPanel;
				break;
			case 'AllAgePanel':
					var filterPanel = this.NewBornFilterPanel;
					var gridPanel = this.AllAgeGridPanel;
				break;
			case 'NewBornPanel':
					var filterPanel = this.NewBornFilterPanel;
					var gridPanel = this.NewBornGridPanel;
				break;
			default:
					var filterPanel = this.MonitorCenterFilterPanel;
					var gridPanel = this.MonitorCenterGridPanel;
				break;
		}

		var form = filterPanel.getForm();
		var	params = {};

		params = form.getValues();
		params.limit = 100;
		params.start = 0;
		gridPanel.getGrid().getStore().baseParams={Type:tabId};
		gridPanel.getGrid().getStore().removeAll();
		gridPanel.getGrid().getStore().load({params:params});
	},
	initComponent: function() {
		var win = this;
		var xg = Ext.grid;

		this.NewBornFilterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			id: 'NewBornFilterPanel',
			labelAlign: 'right',
			labelWidth: 160,
			region: 'north',
			items: [
				{
					layout:'column',
					items:[
						{
							layout:'form',
							items:[
								{
									xtype: 'daterangefield',
									fieldLabel: lang['rodilis_v_period'],
									name: 'Period_DateRange',
									hiddenName:'Period_DateRange',
									plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
									width: 170
								},
								{
									fieldLabel:lang['tekuschee_sostoyanie'],
									comboSubject:'State',
									hiddenName:'State_id',
									name:'State_id',
									width:170,
									xtype:'swcommonsprcombo',
									onLoad : function(){
										if (win.TabPanel.getActiveTab().id == 'NewBornPanel'){
											this.getStore().filterBy(function(rec, id) {return ',11,1,2,3,4,5,6,'.indexOf(','+id+',') >0;}, this);
										}
										if (win.TabPanel.getActiveTab().id == 'AllAgePanel' || win.TabPanel.getActiveTab().id == 'OneAgePanel'){
											this.getStore().filterBy(function(rec, id) {return ',11,7,8,'.indexOf(','+id+',') >0;}, this);
										}
										this.insertEmptyRecord();
										this.expand();
									}
								},
								{
									xtype: 'textfieldpmw',
									width: 170,
									id: 'mpwpSearch_FIO',
									fieldLabel: lang['fio'],
									name:'Person_FIO'
								},
								{
									showCodefield: false,
									fieldLabel: 'Возраст (мес.)',
									comboSubject:'NumberList',
									hiddenName:'NumberList_id',
									name:'NumberList_id',
									width:170,
									autoLoad: true,
									xtype:'swcommonsprcombo',
									onLoad : function(){
										this.getStore().filterBy(function(rec) {
											return (Number(rec.get('NumberList_id')) < 13);
										});
										this.expand();
									},
									tpl: new Ext.XTemplate(
										'<tpl for="."><div class="x-combo-list-item">',
										'<span style="color:black;">{NumberList_Name}</span> ',
										'</div></tpl>'
									)
								},
								{
									showCodefield: false,
									fieldLabel: 'Возраст (лет)',
									comboSubject:'NumberList',
									hiddenName:'NumberList_aid',
									name:'NumberList_aid',
									width:170,
									autoLoad: true,
									xtype:'swcommonsprcombo',
									onLoad : function(){
										this.getStore().filterBy(function(rec) {
											return (Number(rec.get('NumberList_Code')) > 1);
										});
										this.expand();
									},
									tpl: new Ext.XTemplate(
										'<tpl for="."><div class="x-combo-list-item">',
										'<span style="color:black;">{NumberList_Name}</span> ',
										'</div></tpl>'
									)
								},
								{
									allowBlank: true,
									comboSubject: 'DegreeOfPrematurity',
									fieldLabel: 'Степень недоношенности',
									hiddenName: 'DegreeOfPrematurity_id',
									width: 170,
									xtype: 'swcommonsprcombo'
								}
							]
						},
						{
							layout:'form',
							labelWidth: 200,
							items:[
								{
									enableKeyEvents: true,
									fieldLabel: lang['vyisokiy_risk'],
									name: 'PersonNewBorn_IsHighRisk',
									xtype: 'checkbox',
									listeners: {
										check: function(combo,value){
										}
									}
								},
								{
									enableKeyEvents: true,
									fieldLabel: 'Диспансерное наблюдение',
									name: 'DispensaryObservation',
									xtype: 'checkbox',
									listeners: {
										check: function(combo,value){
										}
									}
								},
								{
									comboSubject: 'YesNo',
									fieldLabel: 'Вакц. БЦЖ',
									hiddenName: 'PersonNewBorn_IsBCG',
									width: 100,
									xtype: 'swcommonsprcombo'
								},
								{
									comboSubject: 'YesNo',
									fieldLabel: 'Вакц. ВГВ',
									hiddenName: 'PersonNewBorn_IsHepatit',
									width: 100,
									xtype: 'swcommonsprcombo'
								}
							]
						},
						{
							layout:'form',
							labelWidth: 200,
							items:[
								{
									fieldLabel: lang['mo_rojdeniya'],
									listeners: {
										'blur': function(combo) {
											if (!combo.getValue()) {
												combo.setRawValue('');
											}
											if (combo.getRawValue() == '') {
												combo.setValue('');
											}
										}
									},
									width:200,
									allowTextInput: true,
									ctxSerach:true,
									xtype: 'swlpucombo',
									hiddenName: 'Lpu_bid',
									name: 'Lpu_bid'
								},
								{
									fieldLabel: lang['mo_gospitalizatsii'],
									listeners: {
										'blur': function(combo) {
											if (!combo.getValue()) {
												combo.setRawValue('');
											}
											if (combo.getRawValue() == '') {
												combo.setValue('');
											}
										}
									},
									width:200,
									allowTextInput: true,
									ctxSerach:true,
									xtype: 'swlpucombo',
									hiddenName: 'Lpu_hid',
									name: 'Lpu_hid'
								},
								{
									fieldLabel: 'МО прикрепления',
									listeners: {
										'blur': function(combo) {
											if (!combo.getValue()) {
												combo.setRawValue('');
											}
											if (combo.getRawValue() == '') {
												combo.setValue('');
											}
										}
									},
									width:200,
									allowTextInput: true,
									ctxSerach:true,
									xtype: 'swlpucombo',
									hiddenName: 'Lpu_tid',
									name: 'Lpu_tid'
								},
								{
									fieldLabel: 'МО патронажа',
									listeners: {
										'blur': function(combo) {
											if (!combo.getValue()) {
												combo.setRawValue('');
											}
											if (combo.getRawValue() == '') {
												combo.setValue('');
											}
										}
									},
									width:200,
									allowTextInput: true,
									ctxSerach:true,
									xtype: 'swlpucombo',
									hiddenName: 'Lpu_lid',
									name: 'Lpu_lid'
								},
							]
						},
						{
							layout:'form',
							labelWidth: 200,
							items:[
								{
									fieldLabel: 'МО патронажа',
									listeners: {
										'blur': function(combo) {
											if (!combo.getValue()) {
												combo.setRawValue('');
											}
											if (combo.getRawValue() == '') {
												combo.setValue('');
											}
										}
									},
									width:200,
									allowTextInput: true,
									ctxSerach:true,
									xtype: 'swlpucombo',
									hiddenName: 'Lpu_pid',
									name: 'Lpu_pid'
								},
								{
									fieldLabel: 'Направления',
									listeners: {
										'blur': function(combo) {
											if (!combo.getValue()) {
												combo.setRawValue('');
											}
										}
									},
									width:200,
									allowTextInput: true,
									ctxSerach:true,
									xtype: 'swcommonsprcombo',
									hiddenName: 'DirType_id',
									comboSubject: 'DirType',
									name: 'DirType_id',
									onLoad : function(){
										this.getStore().filterBy(function(rec) {
											var ids = [1,3,4,5,6,9,8,10,11,12,13,23,25];
											return (ids.indexOf(Number(rec.get('DirType_Code')))>-1);
										});
										this.expand();
									},
								},
								{
									fieldLabel: 'Проба для неон. скрининга',
									listeners: {
										'blur': function(combo) {
											if (!combo.getValue()) {
												combo.setRawValue('');
											}
										}
									},
									width:200,
									allowTextInput: true,
									ctxSerach:true,
									xtype: 'swcommonsprcombo',
									hiddenName: 'PersonNewBorn_IsNeonatal',
									comboSubject: 'YesNo',
									name: 'PersonNewBorn_IsNeonatal'
								}, {
									xtype: 'swdiagcombo',
									hiddenName: 'Diag_Code_From',
									valueField: 'Diag_Code',
									fieldLabel: 'Основной диагноз с',
									width: 200
								}, {
									xtype: 'swdiagcombo',
									hiddenName: 'Diag_Code_To',
									valueField: 'Diag_Code',
									fieldLabel: 'по',
									width: 200
								}
							]
						},
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

		this.MonitorCenterFilterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			id: 'MonitorCenterFilterPanel',
			labelAlign: 'right',
			labelWidth: 160,
			region: 'north',
			items: [
				{
					layout:'column',
					items:[
						{
							layout:'form',
							items:[{
									xtype: 'textfield',
									name: 'Person_SurName',
									fieldLabel: 'Фамилия',
									width: 180
								}, {
									xtype: 'textfield',
									name: 'Person_FirName',
									fieldLabel: 'Имя',
									width: 180
								}, {
									xtype: 'textfield',
									name: 'Person_SecName',
									fieldLabel: 'Отчество',
									width: 180
								}
							]
						},
						{
							layout:'form',
							labelWidth: 200,
							items:[
								{
									xtype: 'daterangefield',
									fieldLabel: 'Родились в период',
									name: 'Period_DDateRange',
									hiddenName:'Period_DDateRange',
									plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
									width: 170
								},
								{
									showCodefield: false,
									fieldLabel: 'Возраст (мес.)',
									comboSubject:'NumberList',
									hiddenName:'NumberList_idd',
									name:'NumberList_idd',
									width:170,
									xtype:'swcommonsprcombo',
									onLoad : function(){
										this.getStore().filterBy(function(rec) {
											return (Number(rec.get('NumberList_id')) < 13);
										});
										this.expand();
									},
									tpl: new Ext.XTemplate(
										'<tpl for="."><div class="x-combo-list-item">',
										'<span style="color:black;">{NumberList_Name}</span> ',
										'</div></tpl>'
									)
								},
								{
									enableKeyEvents: true,
									fieldLabel: 'Направлен на госпитализацию',
									name: 'Hospitalization',
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
							labelWidth: 200,
							items:[
								{
									fieldLabel: lang['mo_gospitalizatsii'],
									listeners: {
										'blur': function(combo) {
											if (!combo.getValue()) {
												combo.setRawValue('');
											}
											if (combo.getRawValue() == '') {
												combo.setValue('');
											}
										}
									},
									width:200,
									allowTextInput: true,
									ctxSerach:true,
									xtype: 'swlpucombo',
									hiddenName: 'Lpu_hhid',
									name: 'Lpu_hhid'
								},
								{
									fieldLabel: 'МО прикрепления',
									listeners: {
										'blur': function(combo) {
											if (!combo.getValue()) {
												combo.setRawValue('');
											}
											if (combo.getRawValue() == '') {
												combo.setValue('');
											}
										}
									},
									width:200,
									allowTextInput: true,
									ctxSerach:true,
									xtype: 'swlpucombo',
									hiddenName: 'Lpu_ttid',
									name: 'Lpu_ttid'
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

		var stringfields = [
			{ header: 'PersonNewBorn_id', hidden:true, type: 'int', name: 'PersonNewBorn_id'},
			{ header: 'Person_id', hidden:true, type: 'int', name: 'Person_id', key: true },
			{ header: 'isHighRisk', hidden:true, type: 'int', name: 'isHighRisk' },
			{ header: 'Person_cid', hidden:true, type: 'int', name: 'Person_cid' },
			{ header: 'Server_id', hidden:true, type: 'int', name: 'Server_id' },
			{ header: 'Person_mid', hidden:true, type: 'int', name: 'Person_mid' },
			{ header: lang['fio'],  type: 'string', name: 'Person_FIO', id: 'autoexpand' },
			{ header: lang['d_r'],  type: 'date', name: 'Person_BirthDay', width: 70 },
			{ header: lang['tekuschee_sostoyanie'],  type: 'string', name: 'State', width: 150 },
			{ header: lang['massa'],  type: 'string', name: 'PersonNewBorn_Weight', width: 70 },
			{ header: 'Степень недоношенности',  type: 'string', name: 'DegreeOfPrematurity_Name', width: 150 },
			{ header: lang['otsenka'],  type: 'int', name: 'NewbornApgarRate_Values', width: 60 },
			{ header: lang['mo_rojdeniya'], name: 'LpuBirth', width: 100, type:'string' },
			{ header: lang['mo_gospitalizatsii'],  type: 'string',width:120, name: 'LpuHosp' },
			{ header: 'Диагноз',  type: 'string',width:120, name: 'Diag' },
			{ header: 'Вакц. ВГВ',  type: 'string',width:70, name: 'PersonNewBorn_IsHepatit' },
			{ header: 'Вакц. БЦЖ',  type: 'string',width:70, name: 'PersonNewborn_IsBCG' },
			{ header: lang['proba_dlya_neon_skrininga'], width:150, type: 'checkcolumn', name: 'PersonNewBorn_IsNeonatal' },
			{ header: lang['predstavitel'],  type: 'string', name: 'Deputy_FIO', width: 100 },
			{ header: lang['adres'],  type: 'string', name: 'Deputy_Addres', width: 100 },
			{ header: lang['telefon'],  type: 'string', name: 'Deputy_Phone', width: 100 },
			{ header: 'BirthSvid_id', hidden:true, type: 'int', name: 'BirthSvid_id' },
			{ header: 'PntDeathSvid_id', hidden:true, type: 'int', name: 'PntDeathSvid_id' },
			{ header: lang['med_svid_o_rojdenii'], width:130, name: 'BirthSvid',renderer: function(value, cellEl, rec) {
					var result = "";
					if (!Ext.isEmpty(value)) {
						result ="<a href='javascript://' onClick='getWnd(\"swMedSvidBirthEditWindow\").show({\"action\":\"view\",\"formParams\":{\"BirthSvid_id\":"+rec.get('BirthSvid_id')+"}})'>"+value+"</a>";
					}
					return  result;
				} },
			{ header: lang['med_svid_o_per_smerti'],width:150, name: 'DeathSvid',renderer: function(value, cellEl, rec) {
					var result = "";
					if (!Ext.isEmpty(value)) {
						result ="<a href='javascript://' onClick='getWnd(\"swMedSvidPntDeathEditWindow\").show({\"action\":\"view\",\"formParams\":{\"PntDeathSvid_id\":"+rec.get('PntDeathSvid_id')+"}})'>"+value+"</a>";
					}
					return  result;
				} },
			{ header: lang['vich_inf_u_materi'], width:120, type: 'checkcolumn', name: 'PersonNewborn_IsAidsMother' },
			{ header: lang['otkaz_ot_rebenka'],  type: 'checkcolumn', name: 'PersonNewborn_IsRejection' },
			{ header: 'Критерии риска',  type: 'string',width:200, name: 'listHighRisk', hidden:true }
		];

		var stringfieldsOneAge =
		[
			{ header: 'PersonNewBorn_id', hidden:true, type: 'int', name: 'PersonNewBorn_id'},
			{ header: 'Person_id', hidden:true, type: 'int', name: 'Person_id', key: true },
			{ header: 'isHighRisk', hidden:true, type: 'int', name: 'isHighRisk' },
			{ header: 'Person_cid', hidden:true, type: 'int', name: 'Person_cid' },
			{ header: 'Server_id', hidden:true, type: 'int', name: 'Server_id' },
			{ header: 'Person_mid', hidden:true, type: 'int', name: 'Person_mid' },
			{ header: 'Проф осмотры', hidden:true, type: 'int', name: 'TeenInspection_id' },
			{ header: lang['fio'],  type: 'string', name: 'Person_FIO', /*id: 'autoexpand',*/ width: 300 },
			{ header: lang['d_r'],  type: 'date', name: 'Person_BirthDay', width: 70 },
			{ header: 'МО прикрепления',  type: 'string', name: 'LpuAttach_Nick', width: 150 },
			{ header: 'МО госпитализации',  type: 'string', name: 'LpuHosp', width: 200 },
			{ header: 'МО лечения',  type: 'string', name: 'LpuLech_Nick', width: 200 },
			{ header: 'Проф. осмотры', name: 'TeenInspection_disDate', width: 100, type:'string', align:'center',
				renderer: function(value, cellEl, rec) {
					if (Ext.isEmpty(value))
						return '';
					return '<a href=\"javascript://\" onclick=\"var params = {}; params[\'action\']=\'view\'; params[\'formParams\'] = {}; params[\'EvnPLDispTeenInspection_id\'] = \''+ rec.get('TeenInspection_id')+'\'; params[\'Server_id\'] = \''+ rec.get('Server_id')+'\';params[\'Person_id\'] = \''+ rec.get('Person_id')+'\'; getWnd(\'swEvnPLDispTeenInspectionProfEditWindow\').show(params); \">'+value+'</a>';
				}
			},
			{ header: 'Диспансерное наблюдение', name: 'PersonDisp', width: 200, type:'string' },
			{ header: 'Направления', name: 'EvnDirectList', width: 100, type:'string', align:'center',
				renderer: function(value, cellEl, rec) {
					if (Ext.isEmpty(value))
						return '';
					var values = value.split('|');
					var res = '';
					for (var i = 0; i < values.length; i++) {
						var item = values[i].split('=');
						if (item.length>1){
							if (!Ext.isEmpty(res)){
								res = res + '<br>';
							}
							res = res +
							'<a href=\"javascript://\" onclick=\"var params = {}; params[\'action\']=\'view\'; params[\'formParams\'] = {}; params[\'EvnDirection_id\'] = \''+ item[0]+'\'; params[\'Person_id\'] = \''+ rec.get('Person_id')+'\'; getWnd(\'swEvnDirectionEditWindow\').show(params); \">'+item[1]+'</a>';
						}
					}
					return res;
				}
			},
			{ header: 'Выявленное заболевание',  type: 'string',width:220, name: 'FirstDiag' },
			{ header: 'Критерии риска',  type: 'string',width:200, name: 'listHighRisk', hidden:true }
		];

		var stringfieldsMonitor =
		[
			{ header: 'Person_id', hidden:true, type: 'int', name: 'Person_id', key: true  },
			{ header: 'isHighRisk', hidden:true, type: 'int', name: 'isHighRisk' },
			{ header: 'Person_cid', hidden:true, type: 'int', name: 'Person_cid' },
			{ header: 'Server_id', hidden:true, type: 'int', name: 'Server_id' },
			{ header: 'Person_mid', hidden:true, type: 'int', name: 'Person_mid' },
			{ header: 'Diag_pid', hidden:true, type: 'int', name: 'Diag_pid' },
			{ header: 'EvnPS_setDate', hidden:true, type: 'string', name: 'EvnPS_setDate' },
			{ header: 'EvnPL_id', hidden:true, type: 'int', name: 'EvnPL_id' },
			{ header: 'CmpCallCard_id', hidden:true, type: 'int', name: 'CmpCallCard_id' },
			{ header: 'EvnDirection_id', hidden:true, type: 'int', name: 'EvnDirection_id' },
			{ header: 'Фамилия', type: 'string', name: 'Person_SurName', width: 100 },
			{ header: 'Имя', type: 'string', name: 'Person_FirName', width: 100 },
			{ header: 'Отчество', type: 'string', name: 'Person_SecName', width: 100 },
			{ header: 'Дата рождения',  type: 'date', name: 'Person_BirthDay', width: 90 },
			{ header: 'Возраст (мес.)', type: 'string', name: 'age', width: 85 },
			{ header: 'Критерии риска',  type: 'string',width:200, name: 'listHighRisk' },
			{ header: 'МО прикрепления',  type: 'string', name: 'LpuAttach_Nick', width: 140 },
			{ header: 'МО госпитализации',  type: 'string', name: 'LpuHosp', width: 140 },
			{ header: 'Направление <br>на госпитализацию',  type: 'string',width:110, name: 'EvnDirectList', align: 'center',
				renderer: function(value, cellEl, rec) {
					if (Ext.isEmpty(value))
						return '';
					var values = value.split('|');
					var res = '';
					for (var i = 0; i < values.length; i++) {
						var item = values[i].split('=');
						if (item.length>1){
							if (!Ext.isEmpty(res)){
								res = res + '<br>';
							}
							res = res +
							'<a href=\"javascript://\" onclick=\"var params = {}; params[\'action\']=\'view\'; params[\'formParams\'] = {}; params[\'EvnDirection_id\'] = \''+ item[0]+'\'; params[\'Person_id\'] = \''+ rec.get('Person_id')+'\'; getWnd(\'swEvnDirectionEditWindow\').show(params); \">'+item[1]+'</a>';
						}
					}
					return res;
				}
			},
			{ header: 'Дни <br>госпитализации',  type: 'string', name: 'DayHosp', width: 100, align: 'center' },
			{ header: 'Реанимация',  type: 'string', name: 'PersReanim', width: 77, align: 'center' },
			{ header: 'Диагноз КВС',  type: 'string', name: 'EvnSectionDiag', width: 150,
				renderer: function(value, cellEl, rec) {
					if (Ext.isEmpty(value))
						return '';
					if (rec.get('EvnSectionDiag').indexOf('!') >= 0)
						return '<div style="background-color:#ffdddd">'+value.replace('!', '')+'</div>';
					return value;
				}
			},
			{ header: 'Случай АПЛ', name: 'EvnPL_NumCard', width: 80, type:'string', align: 'center',
				renderer: function(value, cellEl, rec) {
					if (Ext.isEmpty(value))
						return '';
					return '<a href=\"javascript://\" onclick=\"Ext.getCmp(\'swMonitorBirthSpecWindow\').emkOpen(\''+ rec.get('EvnPL_id')+'\', 1)\">'+value+'</a>';
				}
			},
			{ header: 'Дни', name: 'DayPL', width: 33, type:'string', align: 'center' },
			{ header: 'Диагноз ТАП', name: 'plDiag', width: 200, type:'string' },
			{ header: 'Вызов СМП', name: 'CmpCallCard_Numv', width: 80, type:'string',
				renderer: function(value, cellEl, rec) {
					if (Ext.isEmpty(value))
						return '';
					//return '<a href=\"javascript://\" onclick=\"var formParams = {CmpCallCard_id : \''+ rec.get('CmpCallCard_id')+'\'};   var params = {}; params[\'action\']=\'view\';  params[\'formParams\'] = formParams; getWnd(\'swCmpCallCardEditWindow\').show(params); \">'+value+'</a>';
					return '<a href=\"javascript://\" onclick=\"var formParams = {ARMType: \'smpadmin\',CmpCallCard_id : \''+ rec.get('CmpCallCard_id')+'\'};   var params = {}; params[\'action\']=\'view\';  params[\'formParams\'] = formParams; getWnd(\'swCmpCallCardNewCloseCardWindow\').show(params); \">'+value+'</a>';
				}
			},
			{ header: 'Дата вызова',  type: 'date',width:80, name: 'CmpCallCard_prmDT' },
			{ header: 'Повод вызова',  type: 'string',width:100, name: 'CmpReasonNew_Name' },
			{ header: 'Патронаж',  type: 'string',width:80, name: 'LpuPatr_Nick', align: 'center' }
		];

		this.NewBornGridPanel = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 250,
			autoLoadData: false,
			dataUrl: '/?c=PersonNewBorn&m=loadMonitorBirthSpecGrid',
			id: 'NewBorn',
			actions:
			[
				{name:'action_add', hidden:true },
				{name:'action_edit', hidden:true},
				{name:'action_view', handler:function(){
					this.openPersonBirthSpecific();
				}.createDelegate(this)},
				{name:'action_refresh', handler:function(){
					this.doSearch();
				}.createDelegate(this)},
				{name:'action_delete', hidden:true},
			],
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			totalProperty: 'totalCount',
			showCountInTop: false,
			stringfields: stringfields,
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				//onRowSelect(this.NewGridPanel, sm, index, record);
			},
			onDblClick: function(grid, rowIdx, colIdx, event) {
				this.openPersonBirthSpecific(grid);
			}.createDelegate(this),
			onEnter: function()
			{

			}
		});

		this.OneAgeGridPanel = new sw.Promed.ViewFrame({
			//autoExpandColumn: 'autoexpand',
			autoExpandMin: 250,
			autoLoadData: false,
			dataUrl: '/?c=PersonNewBorn&m=loadMonitorBirthSpecGrid',
			id: 'OneAge',
			actions:
			[
				{name:'action_add', hidden:true },
				{name:'action_edit', hidden:true},
				{name:'action_view', handler:function(){
					this.openPersonBirthSpecific(this.OneAgeGridPanel.getGrid());
				}.createDelegate(this)},
				{name:'action_refresh', handler:function(){
					this.doSearch();
				}.createDelegate(this)},
				{name:'action_delete', hidden:true},
			],
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			totalProperty: 'totalCount',
			//showCountInTop: false,
			stringfields: stringfieldsOneAge,
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				//onRowSelect(this.NewGridPanel, sm, index, record);
			},
			onDblClick: function(grid, rowIdx, colIdx, event) {
				this.openPersonBirthSpecific(grid);
			}.createDelegate(this),
			onEnter: function()
			{

			}
		});

		this.AllAgeGridPanel = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 250,
			autoLoadData: false,
			dataUrl: '/?c=PersonNewBorn&m=loadMonitorBirthSpecGrid',
			actions:
			[
				{name:'action_add', hidden:true },
				{name:'action_edit', hidden:true},
				{name:'action_view', handler:function(){
					this.openPersonBirthSpecific(this.AllAgeGridPanel.getGrid());
				}.createDelegate(this)},
				{name:'action_refresh', handler:function(){
					this.doSearch();
				}.createDelegate(this)},
				{name:'action_delete', hidden:true},
			],
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			id: 'AllAge',
			totalProperty: 'totalCount',
			showCountInTop: false,
			stringfields: stringfieldsOneAge,
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				//onRowSelect(this.NewGridPanel, sm, index, record);
			},
			onDblClick: function(grid, rowIdx, colIdx, event) {
				this.openPersonBirthSpecific(grid);
			}.createDelegate(this),
			onEnter: function()
			{

			}
		});

		this.MonitorCenterGridPanel = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 250,
			autoLoadData: false,
			dataUrl: '/?c=PersonNewBorn&m=loadMonitorBirthSpecGrid',
			actions:
			[
				{name:'action_add', hidden:true },
				{name:'action_edit', hidden:true},
				{name:'action_view', handler:function(){
					this.openPersonBirthSpecific(this.MonitorCenterGridPanel.getGrid());
				}.createDelegate(this)},
				{name:'action_refresh', handler:function(){
					this.doSearch();
				}.createDelegate(this)},
				{name:'action_delete', hidden:true},
			],
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			totalProperty: 'totalCount',
			id: "MonitorCenterGridPanel",
			showCountInTop: false,
			stringfields: stringfieldsMonitor,
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				//onRowSelect(this.NewGridPanel, sm, index, record);
			},
			onDblClick: function(grid, rowIdx, colIdx, event) {
				this.openPersonBirthSpecific(grid);
			}.createDelegate(this),
			onEnter: function()
			{

			}
		});

		tabPanelItems = [{
				id: 'NewBornPanel',
				layout: 'fit',
				title: 'Новорожденные',
				items: [this.NewBornGridPanel]
				//items: [this.AllGridPanel]
			}, {
				id: 'OneAgePanel',
				layout: 'fit',
				title: 'Дети до года',
				items: [this.OneAgeGridPanel]
			}, {
				id: 'AllAgePanel',
				layout: 'fit',
				title: 'Все',
				items: [this.AllAgeGridPanel]
				//items: [this.NewBornGridPanel]
			}, {
				id: 'CenterPanel',
				layout: 'fit',
				title: 'Центр мониторинга',
				items: [this.MonitorCenterGridPanel],
				disabled: !isUserGroup('OperRegBirth')
			}];

		this.TabPanel = new Ext.TabPanel({
			border: true,
			activeTab: 0,
			id: 'NewBorn_TabPanel',
			region: 'center',
			items: tabPanelItems,
			listeners:
			{
				tabchange: function(tab, panel) {
					this.doLayout();
					this.NewBornFilterPanel.hide();
					this.MonitorCenterFilterPanel.hide();
					if (panel.getId() == 'NewBornPanel'){
						this.NewBornFilterPanel.show();
						var pp_form = this.NewBornFilterPanel.getForm();
						pp_form.findField('Person_FIO').showContainer();
						pp_form.findField('PersonNewBorn_IsNeonatal').showContainer();
						pp_form.findField('DispensaryObservation').hideContainer();
						//var pp_formonitor = this.NewBornFilterPanel.getForm();
						//pp_form.findField('Hospitalization').hideContainer();
						pp_form.findField('NumberList_id').hideContainer();
						pp_form.findField('DirType_id').hideContainer();
						pp_form.findField('NumberList_aid').hideContainer();
						pp_form.findField('PersonNewBorn_IsHighRisk').showContainer();
						var date1 = (Date.parseDate(getGlobalOptions().date, 'd.m.Y'));
						var dayOfWeek = (date1.getDay() + 6) % 7;
						date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
						var date2 = date1.add(Date.DAY, 6).clearTime();
						pp_form.findField('Period_DateRange').setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
						pp_form.findField('NumberList_aid').setValue('');
						pp_form.findField('Lpu_pid').hideContainer();
						pp_form.findField('Lpu_lid').showContainer();
						pp_form.findField('DegreeOfPrematurity_id').showContainer();
						pp_form.findField('PersonNewBorn_IsBCG').showContainer();
						pp_form.findField('PersonNewBorn_IsHepatit').showContainer();
						pp_form.findField('Diag_Code_From').showContainer();
						pp_form.findField('Diag_Code_To').showContainer();
						var stateid = pp_form.findField('State_id');
						stateid.setValue('');
					}

					if (panel.getId() == 'OneAgePanel'){
						this.NewBornFilterPanel.show();
						var pp_form = this.NewBornFilterPanel.getForm();
						pp_form.findField('Person_FIO').hideContainer();
						pp_form.findField('PersonNewBorn_IsNeonatal').hideContainer();
						pp_form.findField('DispensaryObservation').showContainer();
						//pp_form.findField('Hospitalization').showContainer();
						pp_form.findField('DirType_id').showContainer();
						pp_form.findField('NumberList_id').showContainer();
						pp_form.findField('NumberList_aid').hideContainer();
						pp_form.findField('PersonNewBorn_IsHighRisk').showContainer();
						var date1 = (Date.parseDate(getGlobalOptions().date, 'd.m.Y'));
						var dayOfWeek = (date1.getDay() + 6) % 7;
						date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
						var date2 = date1.add(Date.DAY, 6).clearTime();
						pp_form.findField('Period_DateRange').setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
						pp_form.findField('NumberList_aid').setValue('');
						pp_form.findField('Lpu_lid').hideContainer();
						pp_form.findField('Lpu_pid').showContainer();
						pp_form.findField('DegreeOfPrematurity_id').hideContainer();
						pp_form.findField('PersonNewBorn_IsBCG').hideContainer();
						pp_form.findField('PersonNewBorn_IsHepatit').hideContainer();
						pp_form.findField('Diag_Code_From').hideContainer();
						pp_form.findField('Diag_Code_To').hideContainer();
						var stateid = pp_form.findField('State_id');
						stateid.setValue('');
					}

					if (panel.getId() == 'AllAgePanel'){
						this.NewBornFilterPanel.show();
						var pp_form = this.NewBornFilterPanel.getForm();
						pp_form.findField('Person_FIO').hideContainer();
						pp_form.findField('PersonNewBorn_IsNeonatal').hideContainer();
						pp_form.findField('DispensaryObservation').showContainer();
						//pp_form.findField('Hospitalization').showContainer();
						pp_form.findField('NumberList_aid').showContainer();
						pp_form.findField('NumberList_id').hideContainer();
						pp_form.findField('PersonNewBorn_IsHighRisk').hideContainer();
						pp_form.findField('DirType_id').showContainer();
						pp_form.findField('Period_DateRange').setValue();
						pp_form.findField('NumberList_id').setValue('');
						pp_form.findField('Lpu_lid').hideContainer();
						pp_form.findField('Lpu_pid').showContainer();
						pp_form.findField('DegreeOfPrematurity_id').hideContainer();
						pp_form.findField('PersonNewBorn_IsBCG').hideContainer();
						pp_form.findField('PersonNewBorn_IsHepatit').hideContainer();
						pp_form.findField('Diag_Code_From').hideContainer();
						pp_form.findField('Diag_Code_To').hideContainer();
						var stateid = pp_form.findField('State_id');
						stateid.setValue('');
					}

					if (panel.getId() == 'CenterPanel')
						this.MonitorCenterFilterPanel.show();

						var pp_form = this.NewBornFilterPanel.getForm();
						var numbermonth = pp_form.findField("NumberList_id");
						if (numbermonth){
							numbermonth.getStore().lastQuery = '';
							numbermonth.getStore().clearFilter();

							numbermonth.getStore().filterBy(function(rec) {
								return (Number(rec.get('NumberList_id')) < 12);
							});
							numbermonth.fireEvent('change', numbermonth, null, null);
						}
					this.doLayout();
				}.createDelegate(this)
			}
		});

		this.NewBornGridPanel.getGrid().view = new Ext.grid.GridView({
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('listHighRisk') != '' && row.get('listHighRisk') != 'Кесарево сечение<br>') {
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

		this.OneAgeGridPanel.getGrid().view = new Ext.grid.GridView({
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('listHighRisk') != '' && row.get('listHighRisk') != 'Кесарево сечение<br>') {
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

		this.MonitorCenterGridPanel.getGrid().view = new Ext.grid.GridView({
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('listHighRisk') != '' && row.get('listHighRisk') != 'Кесарево сечение<br>') {
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
				{
					region: 'north',
					layout: 'form',
					border: false,
					autoHeight: true,
					items: [
						this.NewBornFilterPanel,
						this.MonitorCenterFilterPanel
					]
				},
				this.TabPanel
			]
		});
		sw.Promed.swMonitorBirthSpecWindow.superclass.initComponent.apply(this, arguments);

		gridpanelOne = this.OneAgeGridPanel;
		this.OneAgeGridPanel.ViewToolbar.on('render', function(vt){
			this.ViewActions['actions'] = new Ext.Action({
				name:'action_openemk',
				tooltip: lang['otkryit_emk'],
				iconCls : 'x-btn-text',
				icon: 'img/icons/actions16.png',
				handler: function() {win.emkOpen(gridpanelOne, 1)},
				key: 'actions',
				text:lang['otkryit_emk']
			});
			vt.insertButton(1,this.ViewActions['actions']);
			this.ViewActions['actions'] = new Ext.Action({
				name:'action_openmotheremk',
				tooltip: lang['otkryit_emk_materi'],
				iconCls : 'x-btn-text',
				icon: 'img/icons/actions16.png',
				handler: function() {win.emkOpen(gridpanelOne, 2)},
				key: 'actions',
				text:lang['otkryit_emk_materi']
			});
			vt.insertButton(1,this.ViewActions['actions']);
			this.ViewActions['actions'] = new Ext.Action({
				name:'action_opendirectionmaster',
				tooltip: 'Мастер выписки направлений',
				iconCls : 'x-btn-text',
				icon: 'img/icons/actions16.png',
				handler: function() {win.masterOpen(gridpanelOne)},
				key: 'actions',
				text: 'Записать'
			});
			vt.insertButton(2,this.ViewActions['actions']);
			return true;
		}, this.OneAgeGridPanel);

		gridpanelAll = this.AllAgeGridPanel;
		this.AllAgeGridPanel.ViewToolbar.on('render', function(vt){
			this.ViewActions['actions'] = new Ext.Action({
				name:'action_openemk',
				tooltip: lang['otkryit_emk'],
				iconCls : 'x-btn-text',
				icon: 'img/icons/actions16.png',
				handler: function() {win.emkOpen(gridpanelAll, 1)},
				key: 'actions',
				text:lang['otkryit_emk']
			});
			vt.insertButton(1,this.ViewActions['actions']);
			this.ViewActions['actions'] = new Ext.Action({
				name:'action_openmotheremk',
				tooltip: lang['otkryit_emk_materi'],
				iconCls : 'x-btn-text',
				icon: 'img/icons/actions16.png',
				handler: function() {win.emkOpen(gridpanelAll, 2)},
				key: 'actions',
				text:lang['otkryit_emk_materi']
			});
			vt.insertButton(1,this.ViewActions['actions']);
			this.ViewActions['actions'] = new Ext.Action({
				name:'action_opendirectionmaster',
				tooltip: 'Мастер выписки направлений',
				iconCls : 'x-btn-text',
				icon: 'img/icons/actions16.png',
				handler: function() {win.masterOpen(gridpanelAll)},
				key: 'actions',
				text: 'Записать'
			});
			vt.insertButton(2,this.ViewActions['actions']);
			return true;
		}, this.AllAgeGridPanel);

		gridpanelNew = this.NewBornGridPanel;
		this.NewBornGridPanel.ViewToolbar.on('render', function(vt){
			this.ViewActions['actions'] = new Ext.Action({
				name:'action_openemk',
				tooltip: lang['otkryit_emk'],
				iconCls : 'x-btn-text',
				icon: 'img/icons/actions16.png',
				handler: function() {win.emkOpen(gridpanelNew, 1)},
				key: 'actions',
				text:lang['otkryit_emk']
			});
			vt.insertButton(1,this.ViewActions['actions']);
			this.ViewActions['actions'] = new Ext.Action({
				name:'action_openmotheremk',
				tooltip: lang['otkryit_emk_materi'],
				iconCls : 'x-btn-text',
				icon: 'img/icons/actions16.png',
				handler: function() {win.emkOpen(gridpanelNew, 2)},
				key: 'actions',
				text:lang['otkryit_emk_materi']
			});
			vt.insertButton(1,this.ViewActions['actions']);
			this.ViewActions['actions'] = new Ext.Action({
				name:'action_opendirectionmaster',
				tooltip: 'Мастер выписки направлений',
				iconCls : 'x-btn-text',
				icon: 'img/icons/actions16.png',
				handler: function() {win.masterOpen(gridpanelNew)},
				key: 'actions',
				text: 'Записать'
			});
			vt.insertButton(2,this.ViewActions['actions']);
			return true;
		}, this.NewBornGridPanel);

		gridpanelMonitor = this.MonitorCenterGridPanel;
		this.MonitorCenterGridPanel.ViewToolbar.on('render', function(vt){
			this.ViewActions['actions'] = new Ext.Action({
				name:'action_openemk',
				tooltip: lang['otkryit_emk'],
				iconCls : 'x-btn-text',
				icon: 'img/icons/actions16.png',
				handler: function() {win.emkOpen(gridpanelMonitor, 1)},
				key: 'actions',
				text:lang['otkryit_emk']
			});
			vt.insertButton(1,this.ViewActions['actions']);
			this.ViewActions['actions'] = new Ext.Action({
				name:'action_openmotheremk',
				tooltip: lang['otkryit_emk_materi'],
				iconCls : 'x-btn-text',
				icon: 'img/icons/actions16.png',
				handler: function() {win.emkOpen(gridpanelMonitor, 2)},
				key: 'actions',
				text:lang['otkryit_emk_materi']
			});
			vt.insertButton(1,this.ViewActions['actions']);
			this.ViewActions['actions'] = new Ext.Action({
				name:'action_opendirectionmaster',
				tooltip: 'Мастер выписки направлений',
				iconCls : 'x-btn-text',
				icon: 'img/icons/actions16.png',
				handler: function() {win.masterOpen(gridpanelMonitor)},
				key: 'actions',
				text: 'Записать'
			});
			vt.insertButton(2,this.ViewActions['actions']);
			return true;
		}, this.MonitorCenterGridPanel);

	},
	emkOpen: function(gridpanel, type)
	{
		var evnpl_id = '';
		if (!(gridpanel instanceof sw.Promed.ViewFrame)){
			evnpl_id=gridpanel;
			gridpanel = this.MonitorCenterGridPanel;
		}

		var grid = gridpanel.getGrid();
		var that = this;
		var record = grid.getSelectionModel().getSelected();

		if ( !record || (type==1 &&!record.get('Person_id')) || (type==2 &&!record.get('Person_mid')) )
		{
			if(type==2 &&!record.get('Person_mid')){
				Ext.Msg.alert(lang['oshibka'], lang['u_vyibrannoy_zapisi_otsutstvuet_svyaz_s_materyu']);
			}else if(type==1 &&!record.get('Person_id')){
				Ext.Msg.alert(lang['oshibka'], lang['ne_peredan_identifikator_cheloveka']);
			}else{
				Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			}
			return false;
		}
		getWnd('swPersonEmkWindow').show({
			Person_id: (type==1)?record.get('Person_id'):record.get('Person_mid'),
			readOnly: (that.editType=='onlyRegister')?true:false,
			Server_id: record.get('Server_id'),
			userMedStaffFact: this.userMedStaffFact,
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			ARMType: 'common',
			addStacActions: (that.editType=='onlyRegister')?[]:["action_New_EvnPS", "action_StacSvid", "action_EvnPrescrVK", "action_EvnJournal"],
			EvnPl_id: evnpl_id,
			callback: function()
			{

			}.createDelegate(this)
		});
	},
	openPersonBirthSpecific: function(grid){
		if(!grid)
			var grid = this.NewBornGridPanel.getGrid();
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

	masterOpen: function(gridPanel) {
		var grid = gridPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (record !== undefined && !Ext.isEmpty(record.get('Person_id'))) {
			getWnd('swDirectionMasterWindow').show({
				personData: record.data
			});
		} else {
			Ext.Msg.alert('Ошибка', 'Ни одна запись не выбрана');
		}
	},

	show: function() {

		sw.Promed.swMonitorBirthSpecWindow.superclass.show.apply(this, arguments);

		this.TabPanel.setActiveTab(3);
		this.TabPanel.setActiveTab(2);
		this.TabPanel.setActiveTab(1);
		this.TabPanel.setActiveTab(0);

		var store = [
			{State_id:1,	State_Name:'Отсутствует'},
			{State_id:2,	State_Name:'Все'},
			{State_id:3,	State_Name:lang['vyipisan']},
			{State_id:5,	State_Name:lang['umer']},
			{State_id:4,	State_Name:lang['v_statsionare']},
			{State_id:6,	State_Name:'Переведен в другую МО'},
			{State_id:7,	State_Name:'Госпитализирован'},
			{State_id:8,	State_Name:'Открыт случай'},
		];
		var base_form = this.NewBornFilterPanel.getForm();
		base_form.findField('State_id').getStore().loadData(store,false);

		var numbermonth = base_form.findField("NumberList_id");
		if (numbermonth){
			numbermonth.getStore().lastQuery = '';
			numbermonth.getStore().clearFilter();

			numbermonth.getStore().filterBy(function(rec) {
				return (Number(rec.get('NumberList_id')) < 12);
			});
			numbermonth.fireEvent('change', numbermonth, null, null);
		}

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

		this.doReset();
		var date1 = (Date.parseDate(getGlobalOptions().date, 'd.m.Y'));
		var dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		var date2 = date1.add(Date.DAY, 6).clearTime();
		base_form.findField('Period_DateRange').setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));

		this.doSearch();
	}
});