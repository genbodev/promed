/**
* swEvnPrescrLimitEditWindow - Параметры референсных значений
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      03.02.2014
*/
/*NO PARSE JSON*/

sw.Promed.swEvnPrescrLimitEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnPrescrLimitEditWindow',
	objectSrc: '/jscore/Forms/Prescription/swEvnPrescrLimitEditWindow.js',
	begDate: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
		var win = this;
		// options @Object
		
		var preg = false;
		var phase = false;

		if (win.EvnPrescrLimitGrid.getGrid().activeEditor) {
			win.EvnPrescrLimitGrid.getGrid().stopEditing();
		}
		
		win.EvnPrescrLimitGrid.getGrid().getStore().each(function(record) {
			if( !Ext.isEmpty(record.get('LimitType_id')) ) {
				if (record.get('LimitType_SysNick') == 'HormonalPhaseType' && !Ext.isEmpty(record.get('EvnPrescrLimit_Values'))) {
					phase = true;
				}
				
				if (record.get('LimitType_SysNick') == 'PregnancyUnitType' && !Ext.isEmpty(record.get('EvnPrescrLimit_ValuesNum'))) {
					preg = true;
				}
			}
		});
		
		if (preg && phase) {
			sw.swMsg.alert(lang['oshibka'], lang['nelzya_ukazat_odnovremenno_i_beremennost_i_fazu_tsikla']);
			return false;
		}
		
		var EvnPrescrLimitData = Ext.util.JSON.encode(getStoreRecords(win.EvnPrescrLimitGrid.getGrid().getStore(), {
			exceptionFields: [
				'LimitType_SysNick',
				'LimitType_isCatalog',
				'Limit_UnitText',
				'EvnPrescrLimit_ValuesText',
				'LimitType_Name',
				'Limit_IsActiv',
				'Limit_Unit'
			]
		}));
		
		this.hide();
		this.callback(EvnPrescrLimitData);
	},
	draggable: true,
	formStatus: 'edit',
	height: 300,
	id: 'EvnPrescrLimitEditWindow',
	blockStartingEditing: false,
	getComboEditor: function(object) {
		var win = this;
		return new sw.Promed.SwCommonSprCombo({
			allowBlank: true,
			comboSubject: object,
			codeField: object + '_Code',
			editable: true,
			enableKeyEvents: true,
			fireAfterEditOnEmpty: true,
			listeners: {
				'render': function() {
					// как появился нужно и прогрузиться
					this.getStore().load();
				}
			}
		});
	},
	getNumberFieldEditor: function(LimitType_id) {
		var win = this;
		var options = {
			enableKeyEvents: true,
			fireAfterEditOnEmpty: true,
			minValue: 0
		};
		if (LimitType_id == 7) {
			options.maxValue = 24;
		}
		return new Ext.form.NumberField(options);
	},
	startEditData: function() {
		if (this.blockStartingEditing) {
			return false;
		}
		var win = this;
		var grid = this.EvnPrescrLimitGrid.getGrid();
		
		// если ещё редактируется
		var editor = grid.getColumnModel().getCellEditor(4);
		if (editor && !editor.hidden) {
			return false;
		}
		
		this.blockStartingEditing = true;

		var cell = grid.getSelectionModel().getSelectedCell();
		var record = grid.getSelectionModel().getSelected();
		if ( !record || !record.get('LimitType_id') ) {
			return false;
		}
		
		/*
		grid.getSelectionModel().select(cell[0], 4);
		grid.getView().focusCell(cell[0], 4);
		*/
		
		var editor = null;
		
		if (record.get('LimitType_isCatalog') == 2 && cell[1] == 5 && !Ext.isEmpty(record.get('LimitType_SysNick'))) {
			editor = new Ext.grid.GridEditor(win.getComboEditor(record.get('LimitType_SysNick')));
		} else if (record.get('LimitType_isCatalog') == 1 && cell[1] == 6) {
			editor = new Ext.grid.GridEditor(win.getNumberFieldEditor(record.get('LimitType_id')));
		}
		
		if (!Ext.isEmpty(editor)) {
			grid.getColumnModel().setEditor(cell[1], editor);
			grid.getColumnModel().setEditable(cell[1], true);
			grid.startEditing(cell[0], cell[1]);
		} else {
			grid.getColumnModel().setEditable(cell[1], false);
		}
		
		this.blockStartingEditing = false;
	},
	initComponent: function() {
		var win = this;
		
		this.EvnPrescrLimitGrid = new sw.Promed.ViewFrame({
			actions:[
				{name:'action_add', hidden:true, disabled: true},
				{name:'action_edit', hidden:true, disabled: true},
				{name:'action_view', hidden:true, disabled: true},
				{name:'action_delete', hidden:true, disabled: true},
				{name:'action_refresh'},
				{name:'action_print', hidden:true}
			],
			saveRecord: function() {
				// на сервер не отправляем, сохранится вместе со всей формой
			},
			id: this.id + '_Grid',
			selectionModel: 'cell',
			saveAtOnce: false, 
			saveAllParams: false, 
			onAfterEdit: function(o) {
				o.grid.stopEditing(true);
				o.grid.getColumnModel().setEditable(o.column, false);
				if (o && o.field) {
					if (o.field == 'EvnPrescrLimit_ValuesText') {
						o.record.set('EvnPrescrLimit_Values', o.value);
						o.record.set('EvnPrescrLimit_ValuesText', o.rawvalue);
					}
				}
			},
			onCellSelect: function(sm,rowIdx,colIdx) {
				win.startEditData();
			},
			autoExpandColumn:'autoexpand',
			autoExpandMin:150,
			autoLoadData:false,
			border:true,
			dataUrl:'/?c=EvnPrescrLimit&m=loadList',
			region:'center',
			scheme: 'lis',
			object: 'EvnPrescrLimit',
			uniqueId: true,
			paging: false,
			totalProperty: 'totalCount',
			editformclassname:'swEvnPrescrLimitEditWindow',
			style:'margin-bottom: 10px',
			stringfields:[
				{name:'LimitType_id', type:'int', header:'ID', key:true},
				{name:'EvnPrescrLimit_id', type:'int', hidden: true},
				{name:'LimitType_Name', type:'string', header: lang['naimenovanie'], width: 120, id: 'autoexpand'},
				{name:'LimitType_isCatalog', type:'int', hidden: true},
				{name:'LimitType_SysNick', type:'string', hidden: true},
				{name:'EvnPrescrLimit_ValuesText', type:'string', header: lang['spravochnoe_znachenie'], width: 120},
				{name:'EvnPrescrLimit_ValuesNum', type:'string', header: lang['chislovoe_znachenie'], width: 120},
				{name:'Limit_UnitText', type:'string', header: lang['edinitsa_izmereniya'], width: 80},
				{name:'EvnPrescrLimit_Values', type:'int', hidden: true},
				{name:'Limit_IsActiv', type:'checkcolumnedit', hidden: true},
				{name:'Limit_Unit', type:'int', hidden: true}
			],
			title:'',
			toolbar:false
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					win.doSave();
				},
				iconCls: 'save16',
				text: lang['ok']
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					win.onCancel();
					win.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [
				win.EvnPrescrLimitGrid
			],
			layout: 'border'
		});

		sw.Promed.swEvnPrescrLimitEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var win = Ext.getCmp('EvnPrescrLimitEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					win.doSave();
				break;

				case Ext.EventObject.J:
					win.onCancel();
					win.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		scope: this,
		stopEvent: false
	}],
	layout: 'form',
	loadMask: null,
	maximizable: false,
	maximized: false,
	minHeight: 150,
	minWidth: 750,
	modal: true,
	onCancel: Ext.emptyFn,
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swEvnPrescrLimitEditWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		if ( arguments[0].onCancel && typeof arguments[0].onCancel == 'function' ) {
			this.onCancel = arguments[0].onCancel;
		}
		
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		
		win.EvnPrescr_id = arguments[0].EvnPrescr_id || null;
		win.MedService_id = arguments[0].MedService_id || null;
		win.UslugaComplex_id = arguments[0].UslugaComplex_id || null;
		win.Person_id = arguments[0].Person_id || null;
		
		win.EvnPrescrLimitGrid.loadData({
			params: {
				EvnPrescr_id: win.EvnPrescr_id,
				MedService_id: win.MedService_id,
				UslugaComplex_id: win.UslugaComplex_id,
				Person_id: win.Person_id
			},
			globalFilters: {
				EvnPrescr_id: win.EvnPrescr_id,
				MedService_id: win.MedService_id,
				UslugaComplex_id: win.UslugaComplex_id,
				Person_id: win.Person_id
			}
		});
	},
	title: lang['parametryi_referensnyih_znacheniy'],
	width: 600
});