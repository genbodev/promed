/**
* swGlossarySearchWindow - окно поиска, просмотра записей глоссария и редактирование личного глоссария пользователя или базового глоссария
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Aleksandr Permyakov (alexpm)
* @version      08.06.2011
* @comment      tabIndex: TABINDEX_GL + (от 0 до 20)
*/

/*NO PARSE JSON*/

sw.Promed.swGlossarySearchWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swGlossarySearchWindow',
	objectSrc: '/jscore/Forms/Common/swGlossarySearchWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: '',
	draggable: true,
	id: 'swGlossarySearchWindow',
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
	EvnClass_id: null,
	GlossarySynonym_id: null,
	GlossaryTagType_id: null,
	
	_isEmptyObject: function(o)
	{
		var nef = true;
		if (o)
		{
			for(var key in o) 
			{
				if (o[key]!='')
				{
					nef = false;
					break;
				}
			}
		}
		return nef;
	},
	doReset: function() {
		var form = this.filterPanel.getForm(),
			grid = this.viewFrame.getGrid();
		form.reset();
		form.findField('Glossary_Word').focus(true, 250);
		grid.getStore().baseParams = {};
		this.viewFrame.removeAll(true);
		this.viewFrame.ViewGridPanel.getStore().removeAll();
		this.buttons[2].setDisabled(true);
	},
	doSearch: function() 
	{
		var form = this.filterPanel.getForm(),
			grid = this.viewFrame.getGrid(),
			params = form.getValues();
		if (this._isEmptyObject(params))
		{
			sw.swMsg.alert(lang['soobschenie'], lang['ne_zadanyi_usloviya_poiska'], function() {form.findField('Glossary_Word').focus(true, 250);});
			return false;
		}
		this.viewFrame.removeAll(true);
		params.start = 0; 
		params.limit = 100;
		this.viewFrame.loadData({globalFilters:params});
	},
	onOkButtonClick: function() {
		var record = this.viewFrame.getGrid().getSelectionModel().getSelected(),
			params = (record && record.data) || false;
		if (params && !this._isEmptyObject(params))
		{
			this.onSelect(params);
			this.hide();
		}
		else
		{
			sw.swMsg.alert(lang['soobschenie'], lang['vyi_nichego_ne_vyibrali']);
		}
	},
	getTagComboParams: function() {
		var tag_combo_params = {},
			filter = 'where (1=1) ';
		if(this.EvnClass_id)
		{
			filter = filter + 'and EvnClass_id = ' + this.EvnClass_id;
		}
		if(this.GlossaryTagType_id)
		{
			filter = filter + 'and GlossaryTagType_id = ' + this.GlossaryTagType_id;
		}
		if(filter.length > 15)
		{
			tag_combo_params.where = filter;
		}
		return tag_combo_params;
	},
	getSynComboParams: function() {
		var combo_params = {};
		if(this.GlossarySynonym_id)
		{
			combo_params.Glossary_id = this.GlossarySynonym_id;
		}
		if(this.GlossaryTagType_id)
		{
			combo_params.GlossaryTagType_id = this.GlossaryTagType_id;
		}
		return combo_params;
	},
	allowCopy: false,// - добавление с возможностью копирования
	openEditWindow: function(a) {
		var win = this,
			grid = win.viewFrame.getGrid(),
			callback = function(data) {
				if ( !data || !data.Glossary_id )
				{
					return false;
				}
				var record = grid.getStore().getById(data.Glossary_id);
				if ( !record )
				{
					win.viewFrame.removeAll(true);
					win.filterPanel.getForm().reset();
					var params = data;
					params.start = 0; 
					params.limit = 100;
					win.viewFrame.loadData({globalFilters:params});
				}
				else {
					var glossary_fields = new Array();
					var i = 0;
					grid.getStore().fields.eachKey(function(key, item) {
						glossary_fields.push(key);
					});
					for ( i = 0; i < glossary_fields.length; i++ )
					{
						record.set(glossary_fields[i], data[glossary_fields[i]]);
					}
					record.commit();
				}
			},
			onHide,
			record = this.viewFrame.getGrid().getSelectionModel().getSelected(),
			record_data = (record && record.data) || {};
			params = {};

		switch (a) {
			case 'view':
			case 'edit':
				//log(record_data);
				if (this._isEmptyObject(record_data) || !record_data.Glossary_id)
				{
					sw.swMsg.alert(lang['soobschenie'], lang['vyi_nichego_ne_vyibrali']);
					return false;
				}
				if(!isAdmin && record_data.Glossary_IsPers == 'false')
				{
					a = 'view';
				}
				params = record_data;
			break;

			case 'add':
				params.Glossary_id = 0;
				params.pmUser_did = (isAdmin)?null:getGlobalOptions().pmuser_id;
				params.Glossary_Word = (this.allowCopy && record_data.Glossary_Word) || '';
				params.GlossarySynonym_id = (this.allowCopy && record_data.GlossarySynonym_id) || null;
				params.GlossaryTagType_id = (this.allowCopy && record_data.GlossaryTagType_id) || null;
				params.Glossary_Descr = (this.allowCopy && record_data.Glossary_Descr) || '';
			break;

			default:
				return false;
			break;
		}
		params.action = a;
		params.callback = callback;
		params.onHide = onHide;
		getWnd('swGlossaryEditWindow').show(params);
	},
	

	initComponent: function() {
		var win = this;
		this.filterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			id: 'GlossarySearchForm',
			labelAlign: 'left',
			labelWidth: 120,
			region: 'north',
			items: [{
				anchor: '100%',
				fieldLabel: lang['termin_fraza'],
				name: 'Glossary_Word',
				id: 'GSF_Glossary_Word',
				maskRe: /[^%]/,
				tabIndex: TABINDEX_GL,
				enableKeyEvents: true,
				xtype: 'textfield'
			}, {
				anchor: '100%',
				fieldLabel: lang['sinonim_termina'],
				hiddenName: 'GlossarySynonym_id',
				tabIndex: TABINDEX_GL + 1,
				enableKeyEvents: true,
				xtype: 'swglossarysynonymcombo'
			}, {
				anchor: '100%',
				fieldLabel: lang['kontekst_termina'],
				hiddenName: 'GlossaryTagType_id',
				comboSubject: 'GlossaryTagType',
				allowSysNick: true,
				autoLoad: false,
				tabIndex: TABINDEX_GL + 2,
				enableKeyEvents: true,
				xtype: 'swcommonsprcombo'
			},
			{
				anchor: '100%',
				fieldLabel: lang['slovar'],
				mode: 'local',
				store: new Ext.data.SimpleStore(
				{
					key: 'GlossaryType_id',
					fields:
					[
						{name: 'GlossaryType_id', type: 'int'},
						{name: 'GlossaryType_Name', type: 'string'}
					],
					data: [[0, ''], [1,'1. Базовый словарь'], [2,'2. Свой словарь']]
				}),
				editable: false,
				triggerAction: 'all',
				displayField: 'GlossaryType_Name',
				valueField: 'GlossaryType_id',
				tpl: '<tpl for="."><div class="x-combo-list-item">{GlossaryType_Name}</div></tpl>',
				hiddenName: 'GlossaryType_id',
				tabIndex: TABINDEX_GL + 3,
				xtype: 'combo'
			},
			{
				name: 'pmUser_did',
				xtype: 'hidden'
				/*
			}, {
				anchor: '100%',
				fieldLabel: lang['vladelets'],
				id: 'GSF_pmUser_did',
				name: 'pmUser_did',
				tabIndex: 1500,
				disabled: true,
				enableKeyEvents: true,
				listeners: {
					'keydown': function (inp, e) {
						if ( e.getKey() == Ext.EventObject.ENTER ) {
							e.stopEvent();
							//inp.ownerCt.findById('OSW_Org_Nick').focus(true, 50);
						}
					}
				},
				xtype: 'textfield'
				*/
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
			dataUrl: '/?c=Glossary&m=loadRecordGrid',
			id: 'GlossaryGrid',
			object: 'Glossary',
			editformclassname: 'swGlossaryEditWindow',
			actions:
			[
				{name:'action_add', tooltip: lang['dobavit_slovo_frazu'], handler: function(){ win.openEditWindow('add'); } },
				{name:'action_edit', tooltip: lang['redaktirovat_slovo_frazu'], handler: function(){ win.openEditWindow('edit'); } },
				{name:'action_view', tooltip: lang['otkryit_slovo_frazu'], handler: function(){ win.openEditWindow('view'); } },
				{name:'action_delete'}
			],
			afterDeleteRecord: function(o) {
				//sw.Promed.Glossary.store.deleteFromLocal(o.id);
			},
			pageSize: 100,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			stringfields: [
				{ header: 'ID', type: 'int', name: 'Glossary_id', key: true },
				{ header: lang['termin'],  type: 'string', name: 'Glossary_Word', id: 'autoexpand', isparams: true },
				{ header: lang['kontekst'],  type: 'int', name: 'GlossaryTagType_id', hidden: true, isparams: true },
				{ header: lang['svoy_termin'], name: 'Glossary_IsPers', width: 30, renderer: sw.Promed.Format.checkColumn },
				{ header: lang['kontekst'],  type: 'string', name: 'GlossaryTagType_Name', width: 200 },
				{ header: lang['sinonim'],  type: 'int', name: 'GlossarySynonym_id', hidden: true, isparams: true },
				{ header: lang['vladelets'],  type: 'int', name: 'pmUser_did', hidden: true, isparams: true }
			],
			toolbar: true,
			// focus 
			onLoadData: function(flag) {
				if (flag) //this.ViewGridPanel.getStore().getCount()>0
				{
					this.ViewGridPanel.getView().focusRow(0);
					if (this.selectionModel!='cell') {
						this.ViewGridPanel.getSelectionModel().selectFirstRow();
						this.onRowSelect(this.ViewGridPanel.getSelectionModel(),0,this.ViewGridPanel.getSelectionModel().getSelected());
					}
				}
			},
			onRowSelect: function(sm,rowIdx,record) {
				win.buttons[2].setDisabled(false);
				this.setActionDisabled('action_edit',(!isAdmin && record.get('Glossary_IsPers') == 'false'));
				this.setActionDisabled('action_delete',(!isAdmin && record.get('Glossary_IsPers') == 'false'));
			},
			onCellDblClick: function(grid, rowIdx, colIdx, event) {
				return false;
			},
			onEnter: function()
			{
				var win = this.ownerCt;
				if(win.action == 'select')
				{
					win.onOkButtonClick();
				}
				else
				{
					this.runAction('action_edit');
				}
			}
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.doSearch()
				},
				iconCls: 'search16',
				tabIndex: TABINDEX_GL + 10,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.ownerCt.doReset();
				},
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_GL + 11,
				text: BTN_FRMRESET
			}, {
				id: 'GSF_OkButton',
				handler: function() {
					this.ownerCt.onOkButtonClick();
				},
				iconCls: 'ok16',
				tabIndex: TABINDEX_GL + 12,
				text: lang['vyibrat']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				onTabElement: 'GSF_Glossary_Word',
				tabIndex: TABINDEX_GL + 15,
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.filterPanel,
				this.viewFrame
			]
		});
		sw.Promed.swGlossarySearchWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swGlossarySearchWindow.superclass.show.apply(this, arguments);
		if (!arguments[0])
		{
			arguments = [{}];
		}
		this.action = arguments[0].action || 'edit';
		this.onSelect = arguments[0].onSelect || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		this.EvnClass_id = arguments[0].EvnClass_id ||  null;
		this.GlossarySynonym_id = arguments[0].GlossarySynonym_id || null;
		this.GlossaryTagType_id = arguments[0].GlossaryTagType_id || null;

		this.doReset();
		
		var form = this.filterPanel.getForm(),
			grid = this.viewFrame.getGrid(),
			syn_combo = form.findField('GlossarySynonym_id'),
			tag_combo = form.findField('GlossaryTagType_id');

		switch (this.action) {
			case 'edit':
				this.setTitle(lang['prosmotr_i_redaktirovanie_glossariya']);
				this.buttons[2].hide();
			break;

			case 'select':
				this.setTitle(lang['poisk_zapisi_glossariya']);
				this.buttons[2].show();
			break;

			default:
				log('swGlossarySearchWindow - action invalid');
				return false;
			break;
		}

		syn_combo.getStore().removeAll();
		/*
		syn_combo.getStore().load({
			params: this.getSynComboParams(),
			callback: function(r,o,s){}
		});
		*/

		tag_combo.getStore().removeAll();
		tag_combo.getStore().load({
			params: this.getTagComboParams(),
			callback: function(r,o,s){}
		});

		this.syncSize();
		this.doLayout();

	}
});