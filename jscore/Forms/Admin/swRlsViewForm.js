/**
* Регистр лекарственных средств
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      27.06.2011
*/

sw.Promed.swRlsViewForm = Ext.extend(sw.Promed.BaseForm,
{
	title: getRLSTitle(),
	maximized: true,
	maximizable: false,
	shim: false,
	signpanel: null,
	treeParents: {
		pharm: [],
		noz: [],
		atc: {
			id: null,
			parents: []
		}
	},
	RlsPharmagroup_id: null,
	RlsNozology_id: null,
	CurNomen_id: null,
	buttonAlign: "right",
	objectName: 'swRlsViewForm',
	closeAction: 'hide',
	id: 'RlsViewForm',
	formmode: null,
	objectSrc: '/jscore/Forms/Admin/swRlsViewForm.js',
	buttons:
		[
			{
				handler: function()
				{
					this.ownerCt.doSearch();
				},
				iconCls: 'search16',
				text: BTN_FRMSEARCH
			}, {
				handler: function()
				{
					this.ownerCt.doReset();
				},
				iconCls: 'resetsearch16',
				text: BTN_FRMRESET
			}, {
				handler: function()
				{
					this.ownerCt.print();
				},
				iconCls: 'print16',
				text: langs('Печать')
			},
			'-',
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event)
				{
					ShowHelp(this.ownerCt.title);
				}
			}, {
				text      : BTN_FRMCLOSE,
				tabIndex  : -1,
				tooltip   : langs('Закрыть'),
				iconCls   : 'cancel16',
				handler   : function()
				{
					this.ownerCt.hide();
				}
			}
		],
	listeners: {
		hide: function(win) {
			for(var j=1; j<=win.tabs_count; j++) {
				win.setActivePanel(j);
				win.doReset();
			}
		}
	},
	show: function()
	{
		sw.Promed.swRlsViewForm.superclass.show.apply(this, arguments);
		
		var win = this;
		var i = 1;
		while(typeof this.CenterTabPanel.getItem('p_'+i) != 'undefined') {
			this.CenterTabPanel.hideTabStripItem('p_'+i);
			this.CenterTabPanel.getItem('p_'+i).doLayout();
			i++;
		}
		this.tabs_count = i;
		this.CenterTabPanel.syncSize();
		this.doLayout();
		this.setActivePanel(1);
		
		
		if(!this.RlsSearchViewGrid.getAction('goto_tradenames')){
			var gototn_action = {
				name: 'goto_tradenames',
				text: langs('Перейти на препарат'),
				handler: function(){
					var rec = win.RlsSearchViewGrid.ViewGridPanel.getSelectionModel().getSelected();
					if(!rec) return false;
					win.CurNomen_id = rec.get('RlsNomen_id');
					win.setactive(2, rec.get('RlsTorg_id'));
				}
			};
			this.RlsSearchViewGrid.ViewActions[gototn_action.name] = new Ext.Action(gototn_action);
			this.RlsSearchViewGrid.ViewToolbar.insertButton(0, gototn_action);
			this.RlsSearchViewGrid.ViewContextMenu.add(this.RlsSearchViewGrid.ViewActions[gototn_action.name]);
		}
		this.updateAddMenu();
        this.onlyView = false;

        if(arguments[0] && arguments[0].onlyView){
            this.onlyView = true;
        }
        this.RlsSearchViewGrid.setReadOnly(this.onlyView);

		if( arguments[0] ) {
			if( arguments[0].ActMatters_Name ) {
				this.formmode = 'actView';
			}
		} else {
			this.formmode = 'common';
		}
		
		switch(this.formmode) {
			case 'actView':
				this.setActivePanel(3);
				this.findById('RlsActmattersSearch').getForm().findField('KeyWord_filter').setValue(arguments[0].ActMatters_Name);
				this.buttons[0].handler();
				break;
			default:
				//
				break;
		}

		if (this.findById('RlsSearchForm')) {
			this.findById('RlsSearchForm').getForm().findField('CLS_MZ_PHGROUP_ID').getStore().load();
		}
	},
	updateAddMenu: function(){
		var win = this;
		var a = win.RlsSearchViewGrid.getAction('action_add');
		var add_button = win.RlsSearchViewGrid.ViewToolbar.items.items[1];
		if(a.menu && typeof a.menu.destroy == 'function'){
			a.menu.destroy();
		}
		a.menu = new Ext.menu.Menu();
		a.menu.add({
			text: 'Справочник медикаментов: добавление'
			,name: 'rlsAddOld'
			,scope: win
			,handler: win.addPrep.createDelegate(win)
		});
		a.menu.add({
			text: 'Справочник медикаментов: добавление медикамента'
			,name: 'rlsAddNew'
			,scope: win
			,handler: win.addPrepNew.createDelegate(win)
		});
		a.setHandler(function(){log(a);
			if(a.menu.isVisible()){
				a.menu.hide();
			} else {
				var add_button = this.RlsSearchViewGrid.ViewToolbar.items.items[1].el.getXY();log(add_button);
				this.doLayout();
				if(add_button[1] > 0){
					add_button[1] = add_button[1]+25;
				} else {
					add_button = this.RlsSearchViewGrid.ViewToolbar.el.getXY();
					add_button[1] = add_button[1]+25;
					add_button[0] = add_button[0]+100;
				}
				a.menu.showAt(add_button);
			}
		},this);
		a.removeComponent(a.items[0]);
		win.doLayout();
	},
	print: function(){
		switch (this.signpanel) {
			case 2:
				var data = this.findById('TorgNameView').body.dom.innerHTML;
				break;
			
			case 3:
				var data = this.findById('AstmattersView').body.dom.innerHTML;
				break;
			
			case 4:
				var data = this.findById('FirmsView').body.dom.innerHTML;
				break;
			
			case 5:
				var data1 = this.findById('Pharma_View1').body.dom.innerHTML;
				var data2 = this.findById('Pharma_View2').body.dom.innerHTML;
				var data = data1 + data2;
				break;
			
			case 6:
				var data = this.findById('Nozology_View').body.dom.innerHTML;
				break;
			
			case 7:
				var data = this.findById('ATX_View').body.dom.innerHTML;
				break;		
		}
		if(data && data != '') {
			data = '<html><head></head><body>'+data+'</body></html>';
			openNewWindow(data);
		} else {
			sw.swMsg.alert(langs('Ошибка'), langs('Нет данных для печати!'));
		}
	},
	searchInProgress: false,
	doSearch: function() {
		if (this.searchInProgress) {
			log(langs('Поиск уже выполняется!'));
			return false;
		} else {
			this.searchInProgress = true;
		}
		var thisWindow = this;
		var win = this;
		switch (this.signpanel)
		{
			case 2:
				var form = this.findById('RlsTorgNamesSearch');
				var grid = this.TorgNameGrid.ViewGridPanel;
			break;

			case 3:
				var grid = this.ActmattersGrid.ViewGridPanel;
				var form = this.findById('RlsActmattersSearch');
			break;

			case 4:
				var grid = this.FirmsGrid.ViewGridPanel;
				var form = this.findById('RlsFirmsSearch');
			break;

			default:
				var form = this.findById('RlsSearchForm');
				var grid = this.RlsSearchViewGrid.ViewGridPanel;
			break;
		}
			
			var params = form.getForm().getValues();
			
			if ( params.KeyWord_filter )
				params.KeyWord_filter = form.getForm().findField('KeyWord_filter').getValue();

			params.start = 0;
			params.limit = 50;
			grid.getStore().removeAll();
			grid.getStore().baseParams = params;

			grid.getStore().load({
				params: params,
				callback: function(r) {
					thisWindow.searchInProgress = false;
					if ( r.length > 0 ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
						var row = grid.getSelectionModel().getSelected();
						if(win.signpanel != null && win.signpanel != 1)
							grid.fireEvent('rowclick', row);
					}
				}
			});
	},
	doReset: function() {
		switch (this.signpanel) {
			case 2:
				var grid = this.TorgNameGrid;
				var form = this.findById('RlsTorgNamesSearch');
				var view = this.findById('TorgNameView');
			break;
			
			case 3:
				var grid = this.ActmattersGrid;
				var form = this.findById('RlsActmattersSearch');
				var view = this.findById('AstmattersView');
			break;
			
			case 4:
				var grid = this.FirmsGrid;
				var form = this.findById('RlsFirmsSearch');
				var view = this.findById('FirmsView');
			break;
			
			case 5:
				var tree = this.findById('pharm');
				var view1 = this.findById('Pharma_View1');
				var view2 = this.findById('Pharma_View2');

				emptytpl = [];
				view1.tpl = new Ext.Template(emptytpl);
				view2.tpl = new Ext.Template(emptytpl);
				
				view1.tpl.overwrite(view1.body, emptytpl);
				view2.tpl.overwrite(view2.body, emptytpl);
			break;
			
			case 6:
				var tree = this.findById('noz');
				var view = this.findById('Nozology_View');
			break;
			
			case 7:
				var tree = this.findById('atc');
				var view = this.findById('ATX_View');
			break;

			default:
				var grid = this.RlsSearchViewGrid;
				var form = this.findById('RlsSearchForm');
			break;
		}
		if(grid)
			grid.ViewGridPanel.getStore().removeAll();
		
		if(form)
			form.getForm().reset();
		
		if(tree)
		{
			tree.collapseAll();
			var root = tree.getRootNode();
			root.select();
		}
		
		if(view)
		{
			emptytpl = [];
			view.tpl = new Ext.Template(emptytpl);
			view.tpl.overwrite(view.body, emptytpl);
		}
		this.doLayout();
	},
	setactive: function(pnum, id)
	{
		this.setActivePanel(pnum);
		switch(pnum)
		{
			case 2:
			case 3:
			case 4:
				this.gridpresentation(id, pnum);
			break;
			
			case 5:
			case 6:
			case 7:
				this.treepresentation(id, pnum);
			break;
		}
	},
	
	setActivePanel: function(pnum)
	{
		this.signpanel = pnum;
		this.CenterTabPanel.setActiveTab(pnum-1);
		this.CenterTabPanel.getActiveTab().doLayout();
		this.CenterTabPanel.getActiveTab().syncSize();
		switch(pnum) {
			case 2:
				var grid = this.TorgNameGrid.ViewGridPanel;
			break;
			
			case 3:
				var grid = this.ActmattersGrid.ViewGridPanel;
			break;
			
			case 4:
				var grid = this.FirmsGrid.ViewGridPanel;
			break;
		}
		if(grid && grid.getStore().getCount() == 0 && this.formmode == 'common') {
			this.buttons[0].handler();
		}
	},
	
	gridpresentation: function(id, pnum)
	{
		var win = this;
		switch (pnum) {
			case 2:
				grid = this.TorgNameGrid;
				FormSign = 'tradenames';
			break;
			
			case 3:
				grid = this.ActmattersGrid;
				FormSign = 'actmatters';
			break;
			
			case 4:
				grid = this.FirmsGrid;
				FormSign = 'firms';
			break;
		}
		
		this.doReset(); //
		
		grid.ViewGridPanel.getStore().baseParams = {}
		grid.ViewGridPanel.getStore().removeAll();
		
		grid.ViewGridPanel.getStore().load({
			params: {
				FormSign: FormSign,
				id: id,
				start: 0,
				limit: 1
			},
			callback: function(r) {
				win.searchInProgress = false;
				if ( r.length > 0 ) {
					grid.ViewGridPanel.getSelectionModel().selectFirstRow();
					row = grid.ViewGridPanel.getSelectionModel().getSelected();
					grid.ViewGridPanel.fireEvent('rowclick', row);
				}
			}
		});
	},
	
	treepresentation: function(id, pnum)
	{
		switch (pnum) {
			case 5:
				var t = 'pharm';
				this.RlsPharmagroup_id = id;
			break;
			
			case 6:
				var t = 'noz';
				this.RlsNozology_id = id;
			break;
			
			case 7:
				var t = 'atc';
			break;
		}
	
		var tree = this.findById(t);
		var selNode = tree.getSelectionModel().getSelectedNode();
		if(selNode && selNode.id == id)
			return false;
		var root = tree.getRootNode();
		root.mode = 'search';
		if(root.expanded)
			tree.getLoader().load(root);
	},
	
	showInfoPanel: function()
	{
		if(this.infoPanel.isVisible())
			this.infoPanel.setVisible(false);
		else
			this.infoPanel.setVisible(true);
		this.doLayout();
	},
	
	addPrep: function()
	{
		var win = this;
		getWnd('swRlsSelectPrepTypeWindow').show({
			mode : 'old',
			onSelect: function(data){
				data.action = 'add';
				data.callback = function(){
					win.updateAddMenu();
				};
				getWnd('swRlsPrepEditWindow').show(data);
			}
		});
		return true;
	},

	addPrepNew: function()
	{
		getWnd('swRlsSelectPrepTypeWindow').show({
			onSelect: function(data){
				data.action = 'add';
				getWnd('swRlsPrepNewEditWindow').show(data);
			}
		});
		return true;
	},
	
	openPrepForEdit: function(action)
	{
		var record = this.RlsSearchViewGrid.ViewGridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		getWnd('swRlsPrepNewEditWindow').show({
			action: action,
			PrepType_id: record.get('RlsPrepType_id'),
			NTFR_id: record.get('RlsNTFR_id'),
			Nomen_id: record.get('RlsNomen_id')
		});
	
	},
	
	deleteNomen: function() {
		var grid = this.RlsSearchViewGrid.ViewGridPanel,
			record = grid.getSelectionModel().getSelected();
		if( !record ) return false;
		
		Ext.Msg.show({
			title: langs('Внимание'),
			msg: langs('Вы действительно хотите удалить выбранную номенклатуру?'),
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					this.getLoadMask(langs('Удаление...')).show();
					Ext.Ajax.request({
						url: '/?c=Rls&m=deleteNomen',
						params: {
							Nomen_id: record.get('RlsNomen_id'),
							Prep_id: record.get('RlsPrep_id'),
							TradeNames_id: record.get('RlsTorg_id')
						},
						callback: function(options, success, response) {
							this.getLoadMask().hide();
							if (success) {	
								var obj = Ext.util.JSON.decode(response.responseText);
								if( obj.success )
									grid.getStore().remove(record);
							} else {
								sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при удалении номенклатуры!'));
							}
						}.createDelegate(this)
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION
		});
	},

	initComponent: function(){

		var cur_win = this;
	
		this.nozolTitle = langs('<b>Согласно Приказов №162н от 4.04.08 и №255н от 20.05.09 к 7 нозологиям относится:</b><br />')+
			langs('1. Миелолейкоз Злокачественные новообразования лимфоидной,')+
			langs('кроветворной и родственных им тканей')+
			langs('(миелолейкоз и другие гемобластозы):<br />')+
			langs('хронический миелоидный лейкоз C 92.1<br />')+
			langs('макроглобулинемия Вальденстрема C 88.0<br />')+
			langs('множественная миелома C 90.0<br />')+
			langs('фолликулярная (нодулярная) неходжкинская лимфома C 82<br />')+
			langs('мелкоклеточная (диффузная) неходжкинская C 83.0<br />')+
			langs('лимфома')+
			langs('мелкоклеточная с расщепленными ядрами (диффузная) C 83.1<br />')+
			langs('неходжкинская лимфома')+
			langs('крупноклеточная (диффузная) неходжкинская лимфома C 83.3<br />')+
			langs('иммунобластная (диффузная) неходжкинская лимфома C 83.4<br />')+
			langs('другие типы диффузных неходжкинских лимфом C 83.8<br />')+
			langs('диффузная неходжкинская лимфома неуточненная C 83.9<br />')+
			langs('периферические и кожные Т-клеточные лимфомы C 84<br />')+
			langs('С 84.0')+
			langs('С 84.1')+
			langs('другие неуточненные Т-клеточные лимфомы C 84.5<br />')+
			langs('другие и неуточненные типы неходжкинской лимфомы C 85<br />')+
			langs('хронический лимфоцитарный лейкоз C 91.1<br />')+
			langs('2. Муковисцидоз кистозный фиброз E 84.0<br />')+
			langs('3. Гемофилия наследственный дефицит фактора VIII D 66.0<br />')+
			langs('наследственный дефицит фактора IX D 67.0<br />')+
			langs('болезнь Виллебранда D 68.0<br />')+
			langs('(в ред. Приказа Минздравсоцразвития РФ от 03.06.2008 N 255н)<br />')+
			langs('4. Рассеянный рассеянный склероз G 35.0<br />')+
			langs('5. Гипофизарный Гипопитуитаризм E 23.0<br />')+
			langs('6. Болезнь Гоше другие нарушения накопления липидов E 75.5<br />')+
			langs('7. Наличие трансплантированного органа или ткани Z 94<br />')+
			langs('Наличие трансплантированной почки Z 94.0<br />')+
			langs('Наличие трансплантированного сердца Z 94.1<br />')+
			langs('Наличие трансплантированной печени Z 94.4<br />')+
			langs('Наличие других трансплантированных органов и тканей Z 94.8');
		
		this.nozolTip = new Ext.ToolTip({
			id: 'rlsTooltip',
			title: cur_win.nozolTitle,
			autoHide: false
		});
		
		this.infoPanel = new Ext.Panel({
			autoHeight: true,
			hidden: true,
			bodyStyle: 'margin: 10px;',
			border: false,
			html: '<div style="background: #eee; padding: 10px; border: 1px solid #000;">'+cur_win.nozolTitle+'</div>'
		});
		
		this.Torgtpl = [
			'<style>'+
			'.header {font-weight: bold; color: blue;}'+
			'</style>'+
			'<div style="background: white; padding: 5px;"><table width="100%" style="height:150px; text-align: center;"><tr>'+
			'<td width="50%"><font style="font-weight: bold; font-size: 12pt; color: #0047ab;">{RlsPrep_Name}</font></td>'+
			'<td><a href="{image_url}" target="_blank"><img height="150" src="{image_url}" /></a></td>'+
			'</tr></table>'+
			langs('<font class="header">Уникальный код:</font> {RlsUniqueCode}<br />')+
			'{RlsPrep_Latname}'+
			'{RlsActmatter}'+
			'{GosRegistrNumber}'+
			'{RlsPharmagroups}'+
			'{RlsAtc}'+
			'{RlsNozolgroups}'+
			'{RlsPrep_lifetime}'+
			'{RlsPrep_cond}'+
			'{RlsPrep_eancode}'+
			'{RlsPrep_composition}'+
			'{RlsPrep_chatacters}'+
			'{RlsPrep_pharmaactions}'+
			'{RlsPrep_actonorg}'+
			'{RlsPrep_compproperties}'+
			'{RlsPharmakinetic}'+
			'{RlsPharmadynamic}'+
			'{RlsClinicalPharmacology}'+
			'{RlsDirection}'+
			'{RlsPrep_recommendations}'+
			'{RlsPrep_contraindications}'+
			'{RlsPrep_pregnancyuse}'+
			'{RlsPrep_sideactions}'+
			'{RlsInteractions}'+
			'{RlsPrep_usemethodanddoses}'+
			'{RlsInstrforPac}'+
			'{RlsOverdose}'+
			'{RlsPrep_precautions}'+
			'{RlsSpecialguidelines}'+
			'</div>'
		];
		
		this.Actmattpl = [
			'<div style="background: white;"><div style="background:#DFE8F6; text-align: center; padding: 5px; font-weight: bold; font-style: italic; font-size: 10pt; color: #a43232;">'+
			'{RlsActmatter_rusname}<br />({RlsActmatter_latname})</div>'+
			'<div style="background: #fff; padding: 5px; font-weight: bold; color: #0047ab; border: 1px solid #ddd;">'+
			langs('Торговые названия препаратов действующего вещества:</div>{tradenames_1}')+
			'{pharmagroups_1}'+
			'{nozology_1}'+
			'<div style="background: #fff; padding: 5px; font-weight: bold; color: #0047ab; border: 1px solid #ddd;">'+
			'Группа сильнодействующих или ядовитых веществ: <span style="color: #2e8b57;">{RlsStronggroups}</span></div>'+
			'<div style="background: #fff; padding: 5px; font-weight: bold; color: #0047ab; border: 1px solid #ddd;">'+
			'Группа наркотических веществ: <span style="color: #2e8b57;">{RlsNarcogroups}</span></div>'+
			'<div style="background: #fff; padding: 5px; font-weight: bold; color: #0047ab; border: 1px solid #ddd;">'+
			'Относится ли к жизненноважным лекарственным средствам по классификации МЗ РФ по действующим веществам: <span style="color: #2e8b57;">{RlsVital}</span></div>'+
			'<div style="background: #fff; padding: 5px; font-weight: bold; color: #0047ab; border: 1px solid #ddd;">'+
			'Является ли препаратом льготного ассортимента: <span style="color: #2e8b57;">{RlsPreferential}</span></div>'+
			'</div>'
		];
		
		this.Firmstpl = [
			'<div style="background: white;"><div style="text-align: center; padding: 5px; font-weight: bold; font-size: 10pt; color: #0047ab;">'+
			'{RlsFirms_name} [{RlsCountries_name}]</div>'+
			'<div style="margin-left:5px;"><b>Адрес основного офиса:</b><br />{RlsFirms_addr}<br />'+
			langs('<b>Адреса в ') + getCountryName('predl') + ':</b><br />{RlsFirms_addr_rus}<br /><br />'+
			'<b>Адреса в странах ближнего зарубежья:</b><br />{RlsFirms_addr_ussr}<br /><br />'+
			'<p style="font-weight: bold; color: #0047ab;">Торговые названия [{count_tradenames}]</p></div>'+
			'{tradenames_1}'+
			'</div>'
		];
		
		this.RlsSearchViewGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			id: 'SRVF_RlsSearchViewGrid',
			anchor: '100%',
			pageSize: 50,
			border: false,
			actions: [
				{ name: 'action_add', hidden: !((isSuperAdmin() || isUserGroup('OperDrugsSpr')) && IS_DEBUG), 
					disabled: !((isSuperAdmin() || isUserGroup('OperDrugsSpr')) && IS_DEBUG), 
					menu: new Ext.menu.Menu({
						items: [
							new Ext.Action({
								text: 'Справочник медикаментов: добавление'
								,scope: this
								,handler: this.addPrep.createDelegate(this)
							}),
							new Ext.Action({
								text: 'Справочник медикаментов: добавление медикамента'
								,scope: this
								,handler: this.addPrepNew.createDelegate(this)
							})
						]
					})
				},
				{ name: 'action_edit', hidden: !((isSuperAdmin() || isUserGroup('OperDrugsSpr')) && IS_DEBUG), 
					disabled: !((isSuperAdmin() || isUserGroup('OperDrugsSpr')) && IS_DEBUG), 
					handler: this.openPrepForEdit.createDelegate(this, ['edit']) 
				},
				{ name: 'action_view', handler: this.openPrepForEdit.createDelegate(this, ['view'])},
				{ name: 'action_delete', handler: this.deleteNomen.createDelegate(this) },
				{ name: 'action_refresh' },
				{ name: 'action_print', hidden: false }
			],
			autoLoadData: false,
			root: 'data',
			stringfields: [
				{ name: 'RlsNomen_id', type: 'int', key: true},
				{ name: 'RlsPrep_id', type: 'int', hidden: true},
				{ name: 'RlsTorg_id', type: 'int', hidden: true },
				{ name: 'RlsPrepType_id', type: 'int', hidden: true },
				{ name: 'RlsNTFR_id', type: 'int', hidden: true },
				{ name: 'icon', hideable: false, header: '', width: 30, renderer: function(v, p, r){
					var val = '';
					if(r.get('RlsPrepType_id') != null) {
						switch(r.get('RlsPrepType_id')){
							case 1: val += '<img title="Препарат, принадлежащий РЛС" src="/img/icons/rls16.png" />'; break;
							case 2: val += '<img title="Экстемпоральное лекарственное средство" src="/img/icons/pill16.png" />'; break;
							case 3: val += '<img title="Медицинский товар" src="/img/icons/product16.png" />'; break;
						}
					}
					return val;
				} },
				{ name: 'RlsTorg_Name', type: 'string', header: langs('Торговое название'), width: 250 },
				{ name: 'RlsPack_Code', type: 'string', header: langs('Торговая упаковка'), id: 'autoexpand' },
				{ name: 'RlsFirms_Name', type: 'string', header: langs('Производитель'), width: 250 },
				{ name: 'RlsRegcert_Number', type: 'string', header: langs('Номер'), width: 140 },
				{ name: 'RlsRegcert_Date', type: 'date', header: langs('Дата'), width: 80, renderer: Ext.util.Format.dateRenderer('d.m.Y') },
				{ name: 'RlsFirms_Name', type: 'string', header: langs('Регистратор'), width: 250 }
			],
			paging: true,
			region: 'center',
			dataUrl: C_RLS_SEARCH,
			toolbar: true,
			totalProperty: 'totalCount'
		});
		
		this.RlsSearchViewGrid.ViewGridPanel.on('rowclick', function(){
			var record = this.getSelectionModel().getSelected();
			if(!record)
				return false;
			//cur_win.setactive(2, record.get('RlsTorg_id'));
		});
		
		this.RlsSearchViewGrid.ViewGridPanel.getSelectionModel().on('rowselect', function(sm, rIdx, rec){
			var actions = cur_win.RlsSearchViewGrid.ViewActions;
			if(isSuperAdmin() || isUserGroup('OperDrugsSpr')) {
				actions.action_delete.enable();
				actions.action_edit.enable();
			} else {
				actions.action_delete.disable();
				actions.action_edit.disable();
			}
		});
		
		
		this.TorgNameGrid = new sw.Promed.ViewFrame({
			border: false,
			region: 'center',
			id: 'SRVF_TorgNameGrid',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_refresh', hidden: true },
				{ name: 'action_print', hidden: false }
			],
			autoLoadData: false,
			root: 'data',
			stringfields: [
				{ name: 'RlsTradename_id', type: 'int', header: 'ID', key: true },
				{ name: 'RlsTradename_name', type: 'string', header: langs('Торговое название'), id: 'autoexpand'}
			],
			pageSize: 50,
			totalProperty: 'totalCount',
			paging: true,
			dataUrl: C_RLS_SEARCH,
			toolbar: false
		});
		
		
		this.RlsPackCodeCombo = new Ext.form.ComboBox({
			hideLabel: true,
			name: 'RlsPackCodeCombo',
			hideEmptyRow: true,
			anchor: '100%',
			displayField: 'RlsPack_Code',
			valueField: 'RlsPack_id',
			editable: false,
			mode: 'local',
			triggerAction: 'all',
			emptyText: langs('Выберите торговое название препарата...'),
			border: false,
			store: new Ext.data.Store({
				url: C_RLS_GETPACKCODE,
				autoLoad: false,
				listeners: {
					load: function(store, records, options)	{
						cur_win.getLoadMask().hide();
						if ( records[0] ) {
							this.RlsPackCodeCombo.setValue( (cur_win.CurNomen_id != null) ? cur_win.CurNomen_id : records[0].get('RlsPack_id') );
							this.RlsPackCodeCombo.fireEvent('select');
						}
						cur_win.CurNomen_id = null;
					}.createDelegate(this)
				},
				reader: new Ext.data.JsonReader({
					success: function(){}
				}, [
					{name: 'RlsPack_id', type: 'int'},
					{name: 'RlsPack_Code', type: 'string'}
				])
			}),
			listeners: {
				select: function() {
					var loadmask = new Ext.LoadMask(Ext.get('TorgNameView'), {msg: langs('Идет загрузка, пожалуйста подождите...')});
					loadmask.show();
					Ext.Ajax.request({
						autoLoad: false,
						url: C_RLS_GETTORGVIEW,
						params:	{
							NOMEN_ID: this.getValue()
						},
						callback: function(options, success, response) {
							loadmask.hide();
							if ( success ) {
								var obj = Ext.util.JSON.decode(response.responseText);
								
								obj.RlsPharmagroups_1 = '';
								var j = 1;
								for(var z=0; z<obj.RlsPharmagroups.length; z++) {
									if (typeof(obj.RlsPharmagroups[z].RlsPharmagroup_Name) != 'undefined') {
										obj.RlsPharmagroups_1 += '<a href="javascript:Ext.getCmp(&quot;RlsViewForm&quot;).setactive(5, '+obj.RlsPharmagroups[z].RlsPharmagroup_id+');">'+obj.RlsPharmagroups[z].RlsPharmagroup_Name+'</a>';
										
										cur_win.treeParents.pharm[z] = {
											id: obj.RlsPharmagroups[z].RlsPharmagroup_id,
											parents: obj.RlsPharmagroups[z].pharm_parents
										}
										if (obj.RlsPharmagroups.length != j)
											obj.RlsPharmagroups_1 += ', ';
										j++;
									}
								}
								obj.RlsPharmagroups = (obj.RlsPharmagroups_1 != '')?'<font class="header">Фармгруппа(ФГ):</font> '+obj.RlsPharmagroups_1+'<br />':'';
								
								obj.RlsNozolgroups_1 = '';
								j = 1;
								for(var z=0; z<obj.RlsNozolgroups.length; z++) {
									if (typeof(obj.RlsNozolgroups[z].RlsNozology_name) != 'undefined') {
										obj.RlsNozolgroups_1 += '<a href="javascript:Ext.getCmp(&quot;RlsViewForm&quot;).setactive(6, '+obj.RlsNozolgroups[z].RlsNozology_id+');">'+obj.RlsNozolgroups[z].RlsNozology_name+'</a>';
										
										cur_win.treeParents.noz[z] = {
											id: obj.RlsNozolgroups[z].RlsNozology_id,
											parents: obj.RlsNozolgroups[z].noz_parents
										}
										if (obj.RlsNozolgroups.length != j)
											obj.RlsNozolgroups_1 += ', ';
										j++;
									}
								}
								obj.RlsNozolgroups = (obj.RlsNozolgroups_1 != '')?'<font class="header">Нозологическая классификация(МКБ-10):</font> '+obj.RlsNozolgroups_1+'<br />':'';
									
								if (obj.RlsActmatter_id != '' && obj.RlsActmatter_id != 0) {
									obj.RlsActmatter = '<font class="header">Действующее вещество(ДВ):</font>' +
										' <a href="javascript:Ext.getCmp(&quot;RlsViewForm&quot;).setactive(3, '+
										obj.RlsActmatter_id+');">'+obj.RlsActmatter_Name+' ('+obj.RlsActmatter_Latname+')</a><br />';
								} else {
									obj.RlsActmatter = '';
								}
								
								obj.RlsPrep_Latname = (obj.RlsPrep_Latname != null) ? '<font class="header">Латинское название:</font> '+obj.RlsPrep_Latname+'<br />' : '';
								obj.GosRegistrNumber = (obj.GosRegistrNumber != null)?'<font class="header">Номер госрегистрации:</font> '+obj.GosRegistrNumber+'<br />':'';
								obj.RlsPrep_lifetime = (obj.RlsPrep_lifetime != null)?'<font class="header">Срок годности:</font> '+obj.RlsPrep_lifetime+'<br />':'';
								obj.RlsPrep_cond = (obj.RlsPrep_cond != null)?'<font class="header">Условия хранения:</font> '+obj.RlsPrep_cond+'<br />':'';
								obj.RlsPrep_eancode = (obj.RlsPrep_eancode != null)?'<font class="header">Код EAN:</font> '+obj.RlsPrep_eancode+'<br />':'';
								obj.RlsPrep_composition = (obj.RlsPrep_composition != null)?'<font class="header">Состав и форма выпуска.</font> '+obj.RlsPrep_composition+'<br />':'';
								obj.RlsPrep_characters = (obj.RlsPrep_characters != null)?'<font class="header">Характеристика.</font> '+obj.RlsPrep_characters+'<br />':'';
								obj.RlsPrep_pharmaactions = (obj.RlsPrep_pharmaactions != null)?'<font class="header">Фармакологическое действие.</font> '+obj.RlsPrep_pharmaactions+'<br />':'';
								obj.RlsPrep_actonorg = (obj.RlsPrep_actonorg != null)?'<font class="header">Действие на организм.</font> '+obj.RlsPrep_actonorg+'<br />':'';
								obj.RlsPrep_compproperties = (obj.RlsPrep_compproperties != null)?'<font class="header">Свойства компонентов.</font> '+obj.RlsPrep_compproperties+'<br />':'';
								obj.RlsPharmakinetic = ( obj.RlsPharmakinetic != null ) ? '<font class="header">Фармакокинетика.</font> '+obj.RlsPharmakinetic+'<br />' : '';
								obj.RlsPharmadynamic = ( obj.RlsPharmadynamic != null ) ? '<font class="header">Фармакодинамика.</font> '+obj.RlsPharmadynamic+'<br />' : '';
								obj.RlsClinicalPharmacology = ( obj.RlsClinicalPharmacology != null ) ? '<font class="header">Клиническая фармакология.</font> '+obj.RlsClinicalPharmacology+'<br />' : '';
								obj.RlsDirection = ( obj.RlsDirection != null ) ? '<font class="header">Инструкция.</font> '+obj.RlsDirection+'<br />' : '';
								obj.RlsPrep_recommendations = (obj.RlsPrep_recommendations != null)?'<font class="header">Рекомендуется.</font> '+obj.RlsPrep_recommendations+'<br />':'';
								obj.RlsPrep_contraindications = (obj.RlsPrep_contraindications != null)?'<font class="header">Противопоказания.</font> '+obj.RlsPrep_contraindications+'<br />':'';
								obj.RlsPrep_pregnancyuse = (obj.RlsPrep_pregnancyuse != null)?'<font class="header">Применение при беременности и кормлении грудью.</font> '+obj.RlsPrep_pregnancyuse+'<br />':'';
								obj.RlsPrep_sideactions = (obj.RlsPrep_sideactions != null)?'<font class="header">Побочные действия.</font> '+obj.RlsPrep_sideactions+'<br />':'';
								obj.RlsInteractions = ( obj.RlsInteractions != null ) ? '<font class="header">Взаимодействие.</font> '+obj.RlsInteractions+'<br />' : '';
								obj.RlsPrep_usemethodanddoses = (obj.RlsPrep_usemethodanddoses != null)?'<font class="header">Способ применения и дозы.</font> '+obj.RlsPrep_usemethodanddoses+'<br />':'';
								obj.RlsInstrforPac = ( obj.RlsInstrforPac != null ) ? '<font class="header">Инструкция для пациента.</font> '+obj.RlsInstrforPac+'<br />' : '';
								obj.RlsOverdose = ( obj.RlsOverdose != null ) ? '<font class="header">Передозировка.</font> '+obj.RlsOverdose+'<br />' : '';
								obj.RlsPrep_precautions = (obj.RlsPrep_precautions != null)?'<font class="header">Меры предосторожности.</font> '+obj.RlsPrep_precautions+'<br />':'';
								obj.RlsSpecialguidelines = ( obj.RlsSpecialguidelines != null ) ? '<font class="header">Особые указания.</font> '+obj.RlsSpecialguidelines+'<br />' : '';
								obj.RlsAtc = ( obj.RlsAtc_id != null )?'<font class="header">Анатомно-терапевтическо-химическая классификация(АТХ):</font> <a href="javascript:Ext.getCmp(&quot;RlsViewForm&quot;).setactive(7, '+obj.RlsAtc_id+');">'+obj.RlsAtc_name+'</a><br />':'';
								
								cur_win.treeParents.atc.parents = obj.atc_parents;
								cur_win.treeParents.atc.id = obj.RlsAtc_id;
								var TorgNameView = cur_win.findById('TorgNameView');
								TorgNameView.tpl = new Ext.Template(cur_win.Torgtpl);
								TorgNameView.tpl.overwrite(TorgNameView.body, obj);
							}
						}
					});
				}
			}
		});
		
		this.TorgNameGrid.ViewGridPanel.on('rowclick', function(){
			
			var emptytpl = [];
			var TorgNameView = cur_win.findById('TorgNameView');
			TorgNameView.tpl = new Ext.Template(emptytpl);
			TorgNameView.tpl.overwrite(TorgNameView.body, emptytpl);
			cur_win.RlsPackCodeCombo.reset();
			cur_win.RlsPackCodeCombo.getStore().removeAll();
			var record = this.getSelectionModel().getSelected();
			if(!record)
				return false;
				
			if(record.get('RlsTradename_id')==0)
				return false;
				
			cur_win.RlsPackCodeCombo.getStore().baseParams = {
				id: record.get('RlsTradename_id')
			}
			cur_win.getLoadMask(langs('Идет загрузка, пожалуйста подождите...')).show();
			cur_win.RlsPackCodeCombo.getStore().load({
				callback: function(){
					cur_win.getLoadMask().hide();
				}
			});
		});
		
		this.ActmattersGrid = new sw.Promed.ViewFrame({
			border: false,
			id: 'SRVF_ActmattersGrid',
			autoExpandColumn: 'autoexpand',
			region: 'center',
			autoExpandMin: 100,
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_refresh', hidden: true },
				{ name: 'action_print', hidden: false }
			],
			autoLoadData: false,
			root: 'data',
			stringfields: [
				{ name: 'RlsActmatters_id', type: 'int', header: 'ID', key: true },
				{ name: 'RlsActmatters_RusName', id: 'autoexpand', type: 'string', header: langs('Действующее вещество')}
			],
			pageSize: 50,
			paging: true,
			autoScroll: true,
			totalProperty: 'totalCount',
			dataUrl: C_RLS_SEARCH,
			toolbar: false
		});
		
		this.ActmattersGrid.ViewGridPanel.on('rowclick', function(){
			var loadmask = cur_win.getLoadMask(langs('Идет загрузка, пожалуйста подождите...'));
			loadmask.show();
			Ext.Ajax.request({
				autoLoad: false,
				url: C_RLS_GETACTMATTERSVIEW,
				params:
				{
					id: this.getSelectionModel().getSelected().get('RlsActmatters_id')
				},
				callback: function(options, success, response)
				{
					loadmask.hide();
					if ( success ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
										
						if(response_obj.RlsStronggroups == "")
							response_obj.RlsStronggroups = "нет";
						
						if(response_obj.RlsNarcogroups == "")
							response_obj.RlsNarcogroups = "нет";
																
						if(response_obj.RlsVital != "")
							response_obj.RlsVital = "да";
						else
							response_obj.RlsVital = "нет";
				
						if(response_obj.RlsPreferential != "")
							response_obj.RlsPreferential = "да";
						else
							response_obj.RlsPreferential = "нет";
																
						response_obj.tradenames_1 = '';
						for(i in response_obj.tradenames)
						{
							if(typeof(response_obj.tradenames[i].RlsTorgNames_name) != 'undefined')
								response_obj.tradenames_1 += '<div style="background: #fff; padding: 5px; border: 1px solid #ddd;">'+
								'<a style="color: #2e8b57; text-decoration: none;" href="javascript:Ext.getCmp(&quot;RlsViewForm&quot;).setactive(2, '+response_obj.tradenames[i].RlsTorgNames_id+');">'+
								response_obj.tradenames[i].RlsTorgNames_name+'</a></div>';
						}
						
						response_obj.pharmagroups_1 = '';
						for(var i=0; i<response_obj.pharmagroups.length; i++)
						{
							if(typeof(response_obj.pharmagroups[i].RlsPharmagroup_name) != 'undefined')
							{
								response_obj.pharmagroups_1 += '<div style="background: #fff; padding: 5px; color: #2e8b57; border: 1px solid #ddd;">'+
								'<a style="color: #2e8b57; text-decoration: none;" href="javascript:Ext.getCmp(&quot;RlsViewForm&quot;).setactive(5, '+response_obj.pharmagroups[i].RlsPharmagroup_id+');">'+
								response_obj.pharmagroups[i].RlsPharmagroup_name+'</a></div>';
								
								cur_win.treeParents.pharm[i] = {
									id: response_obj.pharmagroups[i].RlsPharmagroup_id,
									parents: response_obj.pharmagroups[i].pharm_parents
								}
							}
						}
						response_obj.pharmagroups_1 = (response_obj.pharmagroups_1 != '')?'<div style="background: #fff; padding: 5px; font-weight: bold; color: #0047ab; border: 1px solid #ddd;">Фармгруппы:</div>'+response_obj.pharmagroups_1:'';
						
						response_obj.nozology_1 = '';
						for(var i=0; i<response_obj.nozology.length; i++)
						{
							if(typeof(response_obj.nozology[i].RlsNozology_name) != 'undefined')
							{
								response_obj.nozology_1 += '<div style="background: #fff; padding: 5px; color: #2e8b57; border: 1px solid #ddd;">'+
								'<a style="color: #2e8b57; text-decoration: none;" href="javascript:Ext.getCmp(&quot;RlsViewForm&quot;).setactive(6, '+response_obj.nozology[i].RlsNozology_id+');">'+
								response_obj.nozology[i].RlsNozology_name+'</a></div>';
								
								cur_win.treeParents.noz[i] = {
									id: response_obj.nozology[i].RlsNozology_id,
									parents: response_obj.nozology[i].noz_parents
								}
							}
						}
						response_obj.nozology_1 = (response_obj.nozology_1 != '')?'<div style="background: #fff; padding: 5px; font-weight: bold; color: #0047ab; border: 1px solid #ddd;">Нозологическая классификация(МКБ-10):</div>'+response_obj.nozology_1:'';
						
						var AstmattersView = cur_win.findById('AstmattersView');
						AstmattersView.tpl = new Ext.Template(cur_win.Actmattpl);
						AstmattersView.tpl.overwrite(AstmattersView.body, response_obj);
					}
				}
			});
		});
		
		this.FirmsGrid = new sw.Promed.ViewFrame({
			border: false,
			id: 'SRVF_FirmsGrid',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_refresh', hidden: true },
				{ name: 'action_print', hidden: false }
			],
			//style: 'margin-top: 3px;',
			autoLoadData: false,
			root: 'data',
			stringfields: [
				{ name: 'RlsFirms_id', type: 'int', header: 'ID', key: true },
				{ name: 'RlsFirms_name', header: langs('Производитель'), id: 'autoexpand', renderer: function(v, p, r){
					return v + ' [' + r.get('RlsCountries_name') + ']';
				} },
				{ name: 'RlsCountries_name', type: 'string', hidden: true }
			],
			pageSize: 50,
			paging: true,
			region: 'center',
			autoScroll: true,
			totalProperty: 'totalCount',
			dataUrl: C_RLS_SEARCH,
			toolbar: false
		});
		this.FirmsGrid.ViewGridPanel.on('rowclick', function(){
			var loadmask = new Ext.LoadMask(Ext.get('FirmsView'), {msg: langs('Идет загрузка, пожалуйста подождите...')});
			loadmask.show();
			Ext.Ajax.request({
				url: C_RLS_GETFIRMSVIEW,
				params: {
					id: this.getSelectionModel().getSelected().get('RlsFirms_id')
				},
				callback: function(options, success, response) {
					loadmask.hide();
					if ( success ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						
						response_obj.tradenames_1 = '';
						for(i in response_obj.tradenames) {
							if(typeof(response_obj.tradenames[i].RlsTorgNames_name) != 'undefined')
							response_obj.tradenames_1 += '<div style="background: #fff; padding: 5px; border: 1px solid #ddd;">'+
							'<a style="color: #2e8b57; text-decoration: none;" href="javascript:Ext.getCmp(&quot;RlsViewForm&quot;).setactive(2, '+response_obj.tradenames[i].RlsTorgNames_id+');">'+
							response_obj.tradenames[i].RlsTorgNames_name+'</a></div>';
						}
						
						if(response_obj.RlsFirms_addr == '')
							response_obj.RlsFirms_addr = langs('не найден');
						
						if(response_obj.RlsFirms_addr_rus == '')
							response_obj.RlsFirms_addr_rus = langs('не найдены');
						
						if(response_obj.RlsFirms_addr_ussr == '')
							response_obj.RlsFirms_addr_ussr = langs('не найдены');

						var FirmsView = cur_win.findById('FirmsView');
						FirmsView.tpl = new Ext.Template(cur_win.Firmstpl);
						FirmsView.tpl.overwrite(FirmsView.body, response_obj);
					}
				}
			});
		});
		
		this.ButtonsMenuPanel = new Ext.Panel({
			region: 'west',
			title: ' ',
			collapsible: true,
			titleCollapse: true,
			animCollapse: false,
			split: true,
			floatable: false,
			width: 60,
			bodyStyle: 'background: white; padding: 5px;',
			minSize: 60,
			maxSize: 120,
			items: [
				{
					text: '',
					tooltip: BTN_FRMSEARCH,
					cls: 'x-btn-large',
					iconCls: 'search32',
					handler: function()
					{
						this.setActivePanel(1);
						this.signpanel = null;
					}.createDelegate(this),
					xtype: 'button'
				}, {
					text: '',
					tooltip: langs('Торговое название'),
					cls: 'x-btn-large',
					iconCls: 'rls-torg32',
					handler: function()
					{
						this.setActivePanel(2);
						this.signpanel = 2;
					}.createDelegate(this),
					xtype: 'button'
				}, {
					text: '',
					tooltip: langs('Действующее вещество'),
					cls: 'x-btn-large',
					iconCls: 'rls-subst32',
					handler: function()
					{
						this.setActivePanel(3);
						this.signpanel = 3;
					}.createDelegate(this),
					xtype: 'button'
				}, {
					text: '',
					tooltip: langs('Производитель'),
					cls: 'x-btn-large',
					iconCls: 'manufacturer32',
					handler: function()
					{
						this.setActivePanel(4);
						this.signpanel = 4;
					}.createDelegate(this),
					xtype: 'button'
				}, {
					text: '',
					tooltip: langs('Фармакология'),
					cls: 'x-btn-large',
					iconCls: 'rls-pharm32',
					handler: function()
					{
						this.setActivePanel(5);
						this.signpanel = 5;
					}.createDelegate(this),
					xtype: 'button'
				}, {
					text: '',
					tooltip: langs('Нозология'),
					cls: 'x-btn-large',
					iconCls: 'rls-nos32',
					handler: function()
					{
						this.setActivePanel(6);
						this.signpanel = 6;
					}.createDelegate(this),
					xtype: 'button'
				}, {
					text: '',
					tooltip: langs('АТХ'),
					cls: 'x-btn-large',
					iconCls: 'rls-ath32',
					handler: function()
					{
						this.setActivePanel(7);
						this.signpanel = 7;
					}.createDelegate(this),
					xtype: 'button'
				}, {
					text: '',
					tooltip: langs('Взаимодействие ЛС'),
					cls: 'x-btn-large',
					iconCls: 'plan32',
					handler: function()
					{
						getWnd('swLsLinkViewWindow').show();
					}.createDelegate(this),
					xtype: 'button'
				}
			]
		});
		
		this.CenterTabPanel = new Ext.TabPanel({
			region: 'center',
			border: false,
			activeTab: 0,
			keys: [{
				fn: function(inp, e) {
					this.doSearch();
				},
				key: [ Ext.EventObject.ENTER ],
				scope: this,
				stopEvent: true
			}],
			headerCfg: {border: false},
			//layout: 'fit',
			items: [
				{
					id: 'p_1',
					title: '', //Поиск по основным параметрам
					layout: 'border',
					//hidden: true,
					defaults:
					{
						collapsible: true,
						split: true
					},
					//iconCls: 'search16',
					items: [
						{
							title: langs('Поиск по основным параметрам'),
							xtype: 'panel',
							plugins: [ Ext.ux.PanelCollapsedTitle ],
							titleCollapse: true,
							animCollapse: false,
							floatable: false,
							region: 'west',
							autoScroll: true,
							width: 580,
							minSize: 400,
							maxSize: 700,
							items: [
								new Ext.form.FormPanel({
									title: '',
									xtype: 'panel',
									id: 'RlsSearchForm',
									border: false,
									items: [
										{
											layout: 'column',
											style: 'font-size: 10pt;',
											border: false,
											items: [
												{
													border: false,
													layout: 'form',
													style: 'padding-top: 5px; margin-left: 5px; text-align: right;',
													width: 580,
													labelWidth: 212,
													labelAlign: 'right',
													items: [
														{
															xtype: 'combo',
															anchor: '80%',
															displayField: 'NAME',
															enableKeyEvents: true,
															mode: 'local',
															triggerAction: 'none',
															doQuery: function(q, forceAll)
															{
																var combo = this;
																if(q.length<2)
																	return false;
																combo.fireEvent('beforequery', combo);
																var where = ' and LOWER(NAME) like LOWER(\''+q+'%\')';
																combo.getStore().load({
																	params: {where: where}
																});
															},
															listeners: {
																change: function(c)
																{
																	if(typeof c.getValue() == 'string')
																	{
																		c.reset();
																		return false;
																	}
																}
															},
															valueField: 'TRADENAMES_ID',
															hiddenName: 'TRADENAMES_ID',
															store: new Ext.data.Store({
																autoLoad: false,
																reader: new Ext.data.JsonReader({
																	id: 'TRADENAMES_ID'
																}, [{
																	mapping: 'TRADENAMES_ID',
																	name: 'TRADENAMES_ID',
																	type: 'int'
																},{
																	mapping: 'NAME',
																	name: 'NAME',
																	type: 'string'
																}]),
																url: '/?c=Rls&m=getTorgNames'
															}),
															resizable: true,
															emptyText: langs('Введите название...'),
															fieldLabel: langs('Торговое название')
														}, {
															fieldLabel: 'Действующее вещество <font style="color: red;">(МНН)</font>',
															name: 'RlsActmatters_RusName',
															xtype: 'swrlsactmatterscombo'
														}, {
															xtype: 'combo',
															anchor: '80%',
															displayField: 'DrugNonpropNames_Nick',
															enableKeyEvents: true,
															resizable: true,
															mode: 'local',
															listWidth: 300,
															triggerAction: 'none',
															doQuery: function(q, forceAll)
															{
																var combo = this;
																if(q.length<2)
																	return false;
																combo.fireEvent('beforequery', combo);
																combo.getStore().load({
																	params: {DrugNonpropNames_Nick: q}
																});
															},
															listeners: {
																change: function(c)
																{
																	if(typeof c.getValue() == 'string')
																	{
																		c.reset();
																		return false;
																	}
																}
															},
															valueField: 'DrugNonpropNames_id',
															hiddenName: 'DrugNonpropNames_id',
															store: new Ext.data.Store({
																autoLoad: false,
																reader: new Ext.data.JsonReader({
																	id: 'DrugNonpropNames_id'
																}, [{
																	mapping: 'DrugNonpropNames_id',
																	name: 'DrugNonpropNames_id',
																	type: 'int'
																},{
																	mapping: 'DrugNonpropNames_Nick',
																	name: 'DrugNonpropNames_Nick',
																	type: 'string'
																}]),
																url: '/?c=DrugNonpropNames&m=loadDrugNonpropNamesList',
																baseParams: {forCombo:1}
															}),
															fieldLabel: langs('Непатентованное наименование')
														}, {
															fieldLabel: langs('Лекарственная форма'),
															name: 'RlsClsdrugforms_Name',
															xtype: 'swrlsclsdrugformscombo'
														}
													]
												}, {
													border: false,
													layout: 'form',
													width: 580,
													style: 'margin-left: 5px;',
													labelWidth: 212,
													labelAlign: 'right',
													items: [{
														fieldLabel: langs('Дозировка'),
														style: 'padding-top: 5px; margin-left: 17px; text-align: left;',
														anchor: '80%',
														name: 'RlsDrug_Dose',
														border: false,
														xtype: 'textfield'
													}]
												}, {
													border: false,
													layout: 'form',
													style: 'margin-left: 5px; text-align: right;',
													width: 580,
													labelWidth: 212,
													labelAlign: 'right',
													items: [{
															fieldLabel: langs('Страна'),
															name: 'RlsCountries_Name',
															xtype: 'swrlscountrycombo'
														}, {
															fieldLabel: langs('Фирма'),
															name: 'RlsFirms_Name',
															xtype: 'swrlsfirmscombo'
														}
													]
												}, {
													border: false,
													layout: 'form',
													width: 580,
													style: 'margin-left: 5px;',
													labelWidth: 212,
													labelAlign: 'right',
													items: [{
														fieldLabel: langs('№ РУ'),
														style: 'padding-top: 5px; margin-left: 17px; text-align: left;',
														anchor: '80%',
														name: 'RlsRegnum',
														border: false,
														xtype: 'textfield'
													}, {
														fieldLabel: langs('Владелец РУ'),
														style: 'padding-top: 5px; margin-left: 17px; text-align: left;',
														anchor: '80%',
														name: 'RlsRegOwnerFirm',
														border: false,
														xtype: 'textfield'
													}, {
														fieldLabel: langs('Производитель'),
														style: 'padding-top: 5px; margin-left: 17px; text-align: left;',
														anchor: '80%',
														name: 'RlsProdFirm',
														border: false,
														xtype: 'textfield'
													}, {
														fieldLabel: langs('Упаковщик'),
														style: 'padding-top: 5px; margin-left: 17px; text-align: left;',
														anchor: '80%',
														name: 'RlsPackFirm',
														border: false,
														xtype: 'textfield'
													}]
												}, {
													border: false,
													layout: 'form',
													style: 'margin-left: 5px; text-align: right;',
													width: 580,
													labelWidth: 212,
													labelAlign: 'right',
													items: [{
															fieldLabel: langs('Диапазон даты регистрации'),
															name: 'RlsDaterange',
															//width: 164,
															anchor: '80%',
															xtype: 'daterangefield'
														}, {
															fieldLabel: langs('Синоним'),
															name: 'RlsSynonim',
															xtype: 'swrlssynonimscombo'
														}, {
															fieldLabel: langs('Нозология (МКБ-10)'),
															name: 'RlsClsiic_Name',
															xtype: 'swrlsclsiiccombo'
														}, {
															fieldLabel: langs('АТХ'),
															name: 'RlsClsatc_Name',
															xtype: 'swrlsclsatccombo'
														}, {
															fieldLabel: langs('Фарм действие'),
															name: 'RlsDesctextes_Code',
															xtype: 'swrlsdesctextescombo'
														},  {
															fieldLabel: langs('Фармгруппа'),
															name: 'RlsPharmagroup_Name',
															xtype: 'swrlsclspharmagroupcombo'
														}, {
															fieldLabel: langs('ФТГ'),
															hiddenName: 'CLS_MZ_PHGROUP_ID',
															anchor: '80%',
															xtype: 'swrlsclsmzphgroupremotecombo'
														}
													]
												}
											]
										},/* {
											layout: 'column',
											border: false,
											style: 'margin-top: 5px; margin-bottom: 5px; margin-left: 20px;',
											items: [
												{
													boxLabel: langs('Отпуск без рецепта&nbsp;'),
													name: 'check_0_1',
													xtype: 'checkbox'
												}, {
													boxLabel: langs('Жизненно-важные&nbsp;'),
													name: 'check_0_2',
													xtype: 'checkbox'
												}, {
													boxLabel: langs('ЛЛО&nbsp;'),
													name: 'check_0_3',
													xtype: 'checkbox'
												}, {
													boxLabel: langs('Наркотические&nbsp;'),
													name: 'check_0_4',
													xtype: 'checkbox'
												}, {
													boxLabel: langs('Сильнодействующие'),
													name: 'check_0_5',
													xtype: 'checkbox'
												}
											]
										},*/{
											layout: 'column',
											defaults: {
												border: false
											},
											border: false,
											items: [
												{
													layout: 'form',
													width: 180,
													style: 'padding: 10px;',
													items: [
														{
															boxLabel: langs('Отпуск без рецепта'),
															name: 'check_0_1',
															hideLabel: true,
															xtype: 'checkbox'
														}, {
															boxLabel: langs('Жизненно-важные'),
															name: 'check_0_2',
															hideLabel: true,
															xtype: 'checkbox'
														}
													]
												}, {
													layout: 'form',
													width: 150,
													style: 'padding: 10px; margin-left: 10px;',
													items: [
														{
															boxLabel: langs('ЛЛО&nbsp;'),
															name: 'check_0_3',
															hideLabel: true,
															xtype: 'checkbox'
														}, {
															boxLabel: langs('Наркотические'),
															name: 'check_0_4',
															hideLabel: true,
															xtype: 'checkbox'
														}
													]
												}, {
													layout: 'form',
													width: 180,
													style: 'padding: 10px;',
													items: [
														{
															boxLabel: langs('Сильнодействующие'),
															name: 'check_0_5',
															hideLabel: true,
															xtype: 'checkbox'
														}, {
															boxLabel: '7 нозологий <a onClick="Ext.getCmp(&quot;RlsViewForm&quot;).showInfoPanel();" href="#">?</a>',
															name: 'sevennozology',
															hideLabel: true,
															xtype: 'checkbox'
														}
													]
												}
											]
										},
										cur_win.infoPanel,
										{
											layout: 'form',
											border: false,
											style: 'margin-left: 20px; margin-top: 15px;',
											width: 465,
											labelWidth: 120,
											items: [
												{
													fieldLabel: langs('Содержит текст'),
													anchor: '100%',
													name: 'RlsSearchKeyWord',
													listeners: {
														'keydown': function (inp, e)
														{
															if (e.getKey() == Ext.EventObject.ENTER)
															{
																e.stopEvent();
																this.doSearch();
															}
														}.createDelegate(this)
													},
													enableKeyEvents: true,
													labelStyle: 'color: blue; font-size: 10pt; font-weight: bold;',
													border: false,
													xtype: 'textfield'
												}
											]
										}, {
											layout: 'form',
											border: false,
											style: 'margin-left: 20px; margin-top: 15px;',
											width: 465,
											labelWidth: 120,
											items: [
												{
													fieldLabel: langs('Код EAN'),
													anchor: '100%',
													name: 'RlsSearchKodEAN',
													listeners: {
														'keydown': function (inp, e)
														{
															if (e.getKey() == Ext.EventObject.ENTER)
															{
																e.stopEvent();
																this.doSearch();
															}
														}.createDelegate(this)
													},
													enableKeyEvents: true,
													//labelStyle: 'color: blue; font-size: 10pt; font-weight: bold;',
													border: false,
													xtype: 'textfield'
												}
											]
										}
									]
								})
							]
						}, {
							xtype: 'panel',
							title: langs('Результаты'),
							collapsible: false,
							region: 'center',
							layout: 'fit',
							id: 'searchresults',
							items: [
								cur_win.RlsSearchViewGrid
							]	
						}
					]
				},
				{
					id: 'p_2',
					layout: 'border',
					width: '100%',
					border: false,
					defaults:
					{
						collapsible: true,
						split: true,
						bodyStyle:'background:#DFE8F6;'
					},
					items: [
						{
							region:'west',
							width: 500,
							minSize: 400,
							title: langs('Торговое название'),
							plugins: [ Ext.ux.PanelCollapsedTitle ],
							titleCollapse: true,
							animCollapse: false,
							floatable: false,
							maxSize: 600,
							layout: 'border',
							defaults: {
								bodyStyle:'background:#DFE8F6;'
							},
							listeners: {
								collapse: function() {
									cur_win.findById('PackCodeForm').doLayout();
								}
							},
							items: [
								new Ext.form.FormPanel({
									id: 'RlsTorgNamesSearch',
									region: 'north',
									autoHeight: true,
									style: 'text-align: left; padding:3px;',
									labelAlign: 'right',
									border: false,
									items: [
										{
											fieldLabel: langs('Фильтр'),
											name: 'RlsTorgNamesFilter_type',
											xtype: 'swrlstorgnamesfiltercombo'
										}, {
											name: 'FormSign',
											value: 'tradenames',
											xtype: 'hidden'
										}, {
											name: 'KeyWord_filter',
											anchor: '100%',
											hideLabel: true,
											value: '',
											enableKeyEvents: true,
											listeners: {
												keyup: function(f, e) {
													if(e.getKey() == Ext.EventObject.ENTER) {
														e.stopEvent();
														this.doSearch();
													}
												}.createDelegate(this)
											},
											emptyText: langs('Введите название...'),
											xtype: 'textfield',
											style: 'margin-top: 3px;'
										}
									]
								}),
								cur_win.TorgNameGrid
							]
						}, {
							region:'center',
							layout: 'border',
							defaults:
							{
								bodyStyle: 'background: #DFE8F6;'
							},
							title: langs('Описание'),
							collapsible: false,
							items: [
								{
									layout: 'fit',
									style: 'padding: 5px;',
									region: 'north',
									autoHeight: true,
									id: 'PackCodeForm',
									xtype: 'form',
									border: false,
									items: [
										cur_win.RlsPackCodeCombo
									]
								}, {
									style: 'padding: 0;',
									id: 'TorgNameView',
									region: 'center',
									bodyStyle: 'background: white',
									autoScroll: true,
									border: false,
									xtype: 'fieldset'
								}
							]
						}
					]
				},
				{
					id: 'p_3',
					title: '', //Действующее вещество
					layout: 'border',
					border: false,
					defaults:
					{
						collapsible: true,
						split: true,
						bodyStyle:'background:#DFE8F6;'
					},
					items: [
						{
							region:'west',
							width: 500,
							minSize: 400,
							layout: 'border',
							titleCollapse: true,
							animCollapse: false,
							floatable: false,
							plugins: [ Ext.ux.PanelCollapsedTitle ],
							title: langs('Действующее вещество'),
							maxSize: 600,
							defaults: {
								bodyStyle:'background:#DFE8F6;'
							},
							items: [
								{
									region: 'north',
									autoHeight: true,
									bodyStyle: 'padding: 3px; background:#DFE8F6;',
									id: 'RlsActmattersSearch',
									xtype: 'form',
									border: false,
									items: [
										{
											name: 'FormSign',
											value: 'actmatters',
											xtype: 'hidden'
										}, {
											xtype: 'textfield',
											name: 'KeyWord_filter',
											emptyText: langs('Введите название...'),
											enableKeyEvents: true,
											listeners: {
												keyup: function(f, e) {
													if(e.getKey() == Ext.EventObject.ENTER) {
														e.stopEvent();
														this.doSearch();
													}
												}.createDelegate(this)
											},
											anchor: '100%',
											hideLabel: true
										}
									]
								},
								cur_win.ActmattersGrid
							]
						}, {
							title: langs('Описание'),
							layout: 'fit',
							collapsible: false,
							region: 'center',
							items: [
								{
									style: 'padding: 0;',
									autoScroll: true,
									bodyStyle: 'background: white',
									id: 'AstmattersView',
									border: false
								}
							]
						}
					]
				},
				{
					id: 'p_4',
					layout: 'border',
					border: false,
					defaults:
					{
						collapsible: true,
						split: true,
						bodyStyle:'background:#DFE8F6;'
					},
					items: [
						{
							region:'west',
							title: langs('Производитель'),
							width: 500,
							plugins: [ Ext.ux.PanelCollapsedTitle ],
							titleCollapse: true,
							animCollapse: false,
							floatable: false,
							minSize: 400,
							layout: 'border',
							maxSize: 600,
							defaults:
							{
								bodyStyle:'background:#DFE8F6;'
							},
							items: [
								{
									layout: 'form',
									region: 'north',
									autoHeight: true,
									id: 'RlsFirmsSearch',
									style: 'padding: 3px;',
									xtype: 'form',
									border: false,
									items:[
										{
											name: 'FormSign',
											value: 'firms',
											xtype: 'hidden'
										}, {
											xtype: 'textfield',
											name: 'KeyWord_filter',
											emptyText: langs('Введите название...'),
											anchor: '100%',
											hideLabel: true
										}
									]
								},
								cur_win.FirmsGrid
							]
						}, {
							region:'center',
							layout: 'fit',
							collapsible: false,
							title: langs('Описание'),
							items: [
								{
									xtype: 'fieldset',
									autoScroll: true,
									bodyStyle: 'background: white',
									border: false,
									style: 'padding:0;',
									id: 'FirmsView'
								}
							]
						}
					]
				},
				{
					id: 'p_5',
					layout: 'border',
					border: false,
					defaults:
					{
						collapsible: true,
						split: true,
						bodyStyle:'background:#DFE8F6; width:100%;'
					},
					items: [
						{
							title: langs('Фармакология'),
							xtype: 'panel',
							layout: 'fit',
							plugins: [ Ext.ux.PanelCollapsedTitle ],
							titleCollapse: true,
							animCollapse: false,
							floatable: false,
							region: 'west',
							width: 500,
							minSize: 400,
							maxSize: 600,
							autoScroll: true,
							items: [
								new Ext.tree.TreePanel({
									id: 'pharm',
									rootVisible: false,
									autoLoad: false,
									enableDD: false,
									border: false,
									autoScroll: true,
									animate: false,
									root:
									{
										nodeType: 'async',
										text: langs('Фармакология'),
										id: 'all',
										draggable: false,
										expandable: true
									},
									listeners:
									{
										click: function(item) 
										{
											var loadmask1 = new Ext.LoadMask(Ext.get('Pharma_View1'), {
												msg: langs('Идет загрузка, пожалуйста подождите...')});
											loadmask1.show();
											Ext.Ajax.request({
												url: C_RLS_GETPHARMAVIEW,
												params:
												{
													node: item.id,
													view: 1
												},
												callback: function(options, success, response)
												{
													loadmask1.hide();
													var response_obj = Ext.util.JSON.decode(response.responseText);
															
													var Pharmatpl_1 = [
														'<p align="center" style="font-size:12pt; color: #0047ab;">{RlsPharmagroup_name}</p>'
													];
																	
													var Pharma_View1 = cur_win.findById('Pharma_View1');
																
													Pharma_View1.tpl = new Ext.Template(Pharmatpl_1);
													Pharma_View1.tpl.overwrite(Pharma_View1.body, response_obj);
												}
											});
																
											var loadmask2 = new Ext.LoadMask(Ext.get('Pharma_View2'), {
												msg: langs('Идет загрузка, пожалуйста подождите...')});
											loadmask2.show();
											Ext.Ajax.request({
												url: C_RLS_GETPHARMAVIEW,
												params:
												{
													node: item.id,
													view: 2
												},
												callback: function(options, success, response)
												{
													loadmask2.hide();
													var response_obj = Ext.util.JSON.decode(response.responseText);
																
													if ( success )
													{
														response_obj.pharma_obj = '';
														for (i in response_obj)
														{
															if(typeof(response_obj[i].RlsTorgNames_name) != 'undefined')
															{
																response_obj.pharma_obj += '<tr><td width="30%">'+
																'<a href="javascript:Ext.getCmp(&quot;RlsViewForm&quot;).setactive(2, '+response_obj[i].RlsTorgNames_id+');" style="text-decoration: none; font-size: 10pt; color: #2e8b57;">'+
																response_obj[i].RlsTorgNames_name+'</a></td><td></td></tr>';
																for (j in response_obj[i].actmatters)
																{
																	if(typeof(response_obj[i].actmatters[j].RlsActmatter_name) != 'undefined')
																	{
																		response_obj.pharma_obj += '<tr><td></td><td>'+
																		'<a href="javascript:Ext.getCmp(&quot;RlsViewForm&quot;).setactive(3, '+response_obj[i].actmatters[j].RlsActmatter_id+');" style="text-decoration: none; font-weight: bold; font-style: italic; font-size: 10pt; color: #a43232;">'+
																		response_obj[i].actmatters[j].RlsActmatter_name+'</a></td></tr>';
																	}
																}
															}
														}
													}
													response_obj.pharma_obj = (response_obj[0].RlsTorgNames_name != '')?'<table width="100%"><tr><td width="30%" style="font-size: 10pt; color: #2e8b57;">Торговое название</td><td></td></tr><tr><td></td><td style="font-weight: bold; font-style: italic; font-size: 10pt; color: #a43232;">Действующее вещество</td></tr></table><br /><br /><table width="100%">'+response_obj.pharma_obj+'</table>':'';
																
													var Pharmatpl_2 = [
														'{pharma_obj}'
													];
																
													var Pharma_View2 = cur_win.findById('Pharma_View2');
																	
													Pharma_View2.tpl = new Ext.Template(Pharmatpl_2);
													Pharma_View2.tpl.overwrite(Pharma_View2.body, response_obj);
													cur_win.doLayout(); //
												}
											});
										}.createDelegate(this)
									},
									loader: new Ext.tree.TreeLoader(
									{		
										listeners: {
											load: function (TreeLoader, node)
											{
												var tree = this.findById('pharm');
												var root = tree.getRootNode();
												if(root.mode == 'search')
												{
													var parents = [];
													for(var z=0; z<this.treeParents.pharm.length; z++)
													{
														if(this.RlsPharmagroup_id == this.treeParents.pharm[z].id)
														{
															parents = this.treeParents.pharm[z].parents;
															break;
														}
													}
												
													for(var i=0; i<parents.length; i++)
													{
														var n = tree.getNodeById(parents[i]);
														if(n)
														{
															n.expand();
															if(tree.getNodeById(this.RlsPharmagroup_id) && i==(parents.length-1))
															{
																var iNod = tree.getNodeById(this.RlsPharmagroup_id);
																iNod.select();
																iNod.fireEvent('click', iNod);
																root.mode = null;
															}
														}
													}
												}
											}.createDelegate(this)
										},
										dataUrl: C_RLS_GETPHARMASTRUCT
									})
								})
							]
						}, {
							region: 'center',
							title: '',
							layout: 'border',
							border: false,
							items: [
								{
									autoHeight: true,
									region: 'north',
									collapsible: false,
									layout: 'form',
									floatable: false,
									expanded: true,
									//autoScroll: true,
									xtype: 'panel',
									items: [
										{
											title: langs('Описание'),
											floatable: false,
											border: false,
											animCollapse: false,
											autoScroll: true,
											id: 'Pharma_View1',
											collapsible: true,
											titleCollapse: true,
											autoHeight: true,
											listeners: {
												collapse: function()
												{
													this.doLayout();
												}.createDelegate(this),
												expand: function()
												{
													this.doLayout();
												}.createDelegate(this)
											}
										}
									]
								}, {
									title: langs('Действующие вещества, торговые названия'),
									titleCollapse: true,
									region: 'center',
									animCollapse: false,
									id: 'Pharma_View2',
									//height: 440,
									collapsible: true,
									autoScroll: true,
									xtype: 'panel'
								}
							]
						}
					]
				},
				{
					id: 'p_6',
					layout: 'border',
					border: false,
					defaults:
					{
						collapsible: true,
						split: true,
						bodyStyle:'background:#DFE8F6;'
					},
					items: [
						{
							region: 'west',
							layout: 'fit',
							title: langs('Нозология'),
							width: 500,
							plugins: [ Ext.ux.PanelCollapsedTitle ],
							titleCollapse: true,
							animCollapse: false,
							floatable: false,
							minSize: 400,
							autoScroll: true,
							maxSize: 600,
							items: [
								new Ext.tree.TreePanel({
									rootVisible: false,
									id: 'noz',
									autoLoad: false,
									enableDD: false,
									autoScroll: true,
									border: false,
									animate: false,
									root:
									{
										nodeType: 'async',
										text: langs('Нозология'),
										id: 'all',
										draggable: false,
										expandable: true
									},
									listeners:
									{
										click: function(item) 
										{
											var loadmask = new Ext.LoadMask(Ext.get('Nozology_View'), {
												msg: langs('Идет загрузка, пожалуйста подождите...')});
											loadmask.show();
											Ext.Ajax.request({
												url: C_RLS_GETNOZOLVIEW,
												params:
												{
													node: item.id
												},
												callback: function(options, success, response)
												{
													loadmask.hide();
													if ( success )
													{
														var response_obj = Ext.util.JSON.decode(response.responseText);
																	
														response_obj.synonims = '';
														for(i in response_obj.Synonims)
														{
															if(typeof(response_obj.Synonims[i].RlsSynonimNozology_name) != 'undefined')
															{
																response_obj.synonims += '<li>- '+
																response_obj.Synonims[i].RlsSynonimNozology_name+'</li>';
															}
														}
														
														response_obj.synonims = (response_obj.synonims != '')?'Синонимы заболевания:<br /><font color="#0047ab"><ul>'+response_obj.synonims+'</ul></font><br />':'';
														
														response_obj.noz_obj = '';
														for (i in response_obj)
														{
															if (response_obj[i].RlsPharmagroup_id)
															{
																response_obj.noz_obj += '<br /><div style="text-align:center; font-weight:bold; font-size: 10pt; font-style: italic; color: #0047ab;">'+
																response_obj[i].RlsPharmagroup_name+'</div>';
																if (response_obj[i].actmatters)
																{
																	for (j in response_obj[i].actmatters)
																	{
																		if (typeof(response_obj[i].actmatters[j].RlsActmatter_name) != 'undefined')
																		{
																			response_obj.noz_obj += '<a href="javascript:Ext.getCmp(&quot;RlsViewForm&quot;).setactive(3, '+response_obj[i].actmatters[j].RlsActmatter_id+');" style="text-decoration: none; font-weight:bold; font-style: italic; font-size: 10pt; color: #a43232;">'+
																			response_obj[i].actmatters[j].RlsActmatter_name+'</a><br />';
																			if (response_obj[i].actmatters[j].tradenames)
																			{
																				for (z in response_obj[i].actmatters[j].tradenames)
																				{
																					if (typeof(response_obj[i].actmatters[j].tradenames[z].RlsTorgNames_name) != 'undefined')
																					{
																						response_obj.noz_obj += '<a href="javascript:Ext.getCmp(&quot;RlsViewForm&quot;).setactive(2, '+response_obj[i].actmatters[j].tradenames[z].RlsTorgNames_id+');" style="text-decoration: none; font-size: 10pt; color: #2e8b57;">'+
																						response_obj[i].actmatters[j].tradenames[z].RlsTorgNames_name+'</a><br />';
																					}
																				}
																			}
																		}
																	}
																}
															}
														}
														response_obj.noz_obj = (response_obj.noz_obj != '')?'<font style="font-weight: bold; font-size: 10pt; font-style: italic; color: #0047ab;">Фармгруппа</font>, <font style="font-weight: bold; font-style: italic; font-size: 10pt; color: #a43232;">ДВ</font>, <font style="font-size: 10pt; color: #2e8b57;">торговое название</font>'+response_obj.noz_obj:'';
																	
														var Noztpl = [
															'<div style="background: white; padding: 5px;"><p align="center" style="font-size:12pt; color: #0047ab;">{RlsNozology_name}</p><br /><p style="font-size: 10pt;">'+
															'{synonims}'+
															'{noz_obj}'+
															'</p></p></div>'
														];
																		
														var NozView = cur_win.findById('Nozology_View');
														NozView.tpl = new Ext.Template(Noztpl);
														NozView.tpl.overwrite(NozView.body, response_obj);
													}
												}
											});
										}
									},
									loader: new Ext.tree.TreeLoader(
									{
										listeners: {
											load: function (TreeLoader, node)
											{
												var tree = this.findById('noz');
												var root = tree.getRootNode();
												if(root.mode == 'search')
												{
													var parents = [];
													for(var z=0; z<this.treeParents.noz.length; z++)
													{
														if(this.RlsNozology_id == this.treeParents.noz[z].id)
														{
															parents = this.treeParents.noz[z].parents;
															break;
														}
													}
												
													for(var i=0; i<parents.length; i++)
													{
														var n = tree.getNodeById(parents[i]);
														if(n)
														{
															n.expand();
															if(tree.getNodeById(this.RlsNozology_id) && i==(parents.length-1))
															{
																var iNod = tree.getNodeById(this.RlsNozology_id);
																iNod.select();
																iNod.fireEvent('click', iNod);
																root.mode = null;
															}
														}
													}
												}
											}.createDelegate(this)
										},
										dataUrl: C_RLS_GETNOZOLSTRUCT
									})
								})
							]
						}, {
							region:'center',
							title: langs('Описание'),
							bodyStyle: 'background: white',
							collapsible: false,
							id: 'Nozology_View',
							autoScroll: true
						}
					]
				},
				{
					id: 'p_7',
					layout: 'border',
					border: false,
					defaults:
					{
						collapsible: true,
						split: true,
						bodyStyle:'background:#DFE8F6;'
					},
					items: [
						{
							region: 'west',
							title: langs('АТХ'),
							plugins: [ Ext.ux.PanelCollapsedTitle ],
							width: 500,
							titleCollapse: true,
							animCollapse: false,
							floatable: false,
							layout: 'fit',
							autoScroll: true,
							minSize: 400,
							maxSize: 600,
							items: [
								new Ext.tree.TreePanel({
									id: 'atc',
									rootVisible: false,
									autoLoad: false,
									enableDD: false,
									autoScroll: true,
									border: false,
									animate: false,
									root:
									{
										nodeType: 'async',
										text: langs('АТХ'),
										id: 'all',
										draggable: false,
										expandable: true
									},
									listeners:
									{
										click: function(item) 
										{
											var loadmask = new Ext.LoadMask(Ext.get('ATX_View'), {
												msg: langs('Идет загрузка, пожалуйста подождите...')});
											loadmask.show();
											Ext.Ajax.request({
												url: C_RLS_GETATXVIEW,
												params:	{
													node: item.id
												},
												callback: function(options, success, response)
												{
													loadmask.hide();
													if ( success )
													{
														var response_obj = Ext.util.JSON.decode(response.responseText);
														response_obj.atc_obj = '';
														for (i in response_obj)
														{
															if(i != 'RlsAtx_name' && typeof(response_obj[i].RlsActmatter_name) != 'undefined')
															{
																response_obj.atc_obj += '<p align="center">'+
																	'<a style="font-weight:bold; font-style: italic; font-size: 10pt; color: #a43232; text-decoration: none;" href="javascript:Ext.getCmp(&quot;RlsViewForm&quot;).setactive(3, '+response_obj[i].RlsActmatter_id+');">'+response_obj[i].RlsActmatter_name+'</a></p><br />';
																if( response_obj[i].tradenames )
																{
																	for (j in response_obj[i].tradenames)
																	{
																		if (typeof(response_obj[i].tradenames[j].RlsTorgNames_name) != 'undefined')
																		{
																			response_obj.atc_obj += '<a href="javascript:Ext.getCmp(&quot;RlsViewForm&quot;).setactive(2, '+response_obj[i].tradenames[j].RlsTorgNames_id+');" style="font-size: 10pt; color: #2e8b57; text-decoration: none;">'+
																			response_obj[i].tradenames[j].RlsTorgNames_name+'</a><br />';
																		}
																	}
																}
															}
														}
																			
														response_obj.atc_obj = (response_obj.atc_obj != '')?'<font style="font-weight: bold; font-style: italic; font-size: 10pt; color: #a43232;">Действующее вещество</font>, <font style="font-size: 10pt; color: #2e8b57;">торговое название</font><br /><br />'+response_obj.atc_obj:'';
																	
														var Atctpl = [
															'<div style="padding: 5px; background: white;"><p align="center" style="font-size:12pt; color: #0047ab;">{RlsAtx_name}</p><br />'+
															'{atc_obj}'+
															'</div>'
														];
														
														var AtcView = cur_win.findById('ATX_View');
														AtcView.tpl = new Ext.Template(Atctpl);
														AtcView.tpl.overwrite(AtcView.body, response_obj);
													}
												}
											});
										}
									},
									loader: new Ext.tree.TreeLoader(
									{
										listeners: 
										{
											load: function (TreeLoader, node)
											{
												var tree = this.findById('atc'); //
												var root = tree.getRootNode();
												if(root.mode == 'search')
												{
													for(var i=0; i<this.treeParents.atc.parents.length; i++)
													{
														var n = tree.getNodeById(this.treeParents.atc.parents[i]);
														if(n)
														{
															n.expand();
															if(tree.getNodeById(this.treeParents.atc.id) && i==(this.treeParents.atc.parents.length-1))
															{
																var iNod = tree.getNodeById(this.treeParents.atc.id);
																iNod.select();
																iNod.fireEvent('click', iNod);
																root.mode = null;
															}
														}
													}
												}
											}.createDelegate(this)
										},
										dataUrl: C_RLS_GETATXSTRUCT
									})
								})
							]
						}, {
							region:'center',
							title: langs('Описание'),
							bodyStyle: 'background: white',
							id: 'ATX_View',
							collapsible: false,
							autoScroll: true
						}
					]
				}
			]
		});
		
		this.swRlsPanel = new Ext.Panel({
			layout: 'border',
			border: false,
			maximized: true,
			items: [
				cur_win.ButtonsMenuPanel,
				cur_win.CenterTabPanel
			]
		});
		Ext.apply(this, {
			layout: 'fit',
			items: [this.swRlsPanel]
		});
		sw.Promed.swRlsViewForm.superclass.initComponent.apply(this, arguments);
	}
});