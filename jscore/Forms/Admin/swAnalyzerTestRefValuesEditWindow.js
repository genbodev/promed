/**
 * swAnalyzerTestRefValuesEditWindow - окно редактирования референсных значений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version      30.10.2013
 * @comment
 */
sw.Promed.swAnalyzerTestRefValuesEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	objectName: 'swAnalyzerTestRefValuesEditWindow',
	objectSrc: '/jscore/Forms/Admin/swAnalyzerTestRefValuesEditWindow.js',
	title: lang['referensnyie_znacheniya'],
	layout: 'form',
	id: 'AnalyzerTestRefValuesEditWindow',
	modal: true,
	shim: false,
	width: 800,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSave: function(callback) {
		var win = this;
		if ( !this.form.isValid() )
		{
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						win.findById('AnalyzerTestRefValuesEditForm').getFirstInvalidEl().focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}
		
		if (
			this.AnalyzerTestType_Code.inlist([1,3]) &&
			Ext.isEmpty(this.form.findField('RefValues_LowerLimit').getValue()) &&
			Ext.isEmpty(this.form.findField('RefValues_UpperLimit').getValue()) && 
			Ext.isEmpty(this.form.findField('RefValues_BotCritValue').getValue()) && 
			Ext.isEmpty(this.form.findField('RefValues_TopCritValue').getValue()) 
		) {
			sw.swMsg.alert(lang['oshibka'], lang['doljno_byit_zapolneno_znachenie_hotya_byi_odnogo_iz_poley_nijnee_normalnoe_verhnee_normalnoe_nijnee_kriticheskoe_verhnee_kriticheskoe'], function() { win.form.findField('RefValues_LowerLimit').focus(true); });
			return false;
		}
		
		var errPeriod1 = false;
		var errPeriod2 = false;
		var errFromTo = false;
		var errCatalog = false;
		
		var preg = false;
		var phase = false;
		
		win.LimitGrid.getGrid().getStore().each(function(record) {
			if( !Ext.isEmpty(record.get('LimitType_id')) ) {
				if (record.get('LimitType_SysNick') == 'HormonalPhaseType' && !Ext.isEmpty(record.get('Limit_Values'))) {
					phase = true;
				}
				
				if (record.get('LimitType_SysNick') == 'PregnancyUnitType' && (!Ext.isEmpty(record.get('Limit_Unit')) || !Ext.isEmpty(record.get('Limit_ValuesFrom')) || !Ext.isEmpty(record.get('Limit_ValuesTo')))) {
					preg = true;
				}
				
				if (!Ext.isEmpty(record.get('Limit_ValuesFrom')) && !Ext.isEmpty(record.get('Limit_ValuesTo')) && record.get('Limit_ValuesFrom') > record.get('Limit_ValuesTo')) {
					errFromTo = true;
				}
				
				if (record.get('LimitType_isCatalog') == 1 && !Ext.isEmpty(record.get('Limit_Unit')) && Ext.isEmpty(record.get('Limit_ValuesFrom')) && Ext.isEmpty(record.get('Limit_ValuesTo'))) {
					errPeriod1 = true;
				}
				
				if (record.get('LimitType_isCatalog') == 1 && Ext.isEmpty(record.get('Limit_Unit')) && (!Ext.isEmpty(record.get('Limit_ValuesFrom')) || !Ext.isEmpty(record.get('Limit_ValuesTo')))) {
					if (record.get('LimitType_id') != 7) {
						errPeriod2 = true;
					}
				}
			}
		});
		
		if (preg && phase) {
			sw.swMsg.alert(lang['oshibka'], lang['nelzya_ukazat_odnovremenno_i_beremennost_i_fazu_tsikla']);
			return false;
		}
		
		if ( errPeriod1 ) {
			sw.swMsg.alert('Ошибка', 'Для периода при указанной единице измерения должно быть указано хотя бы одно из полей «от» или «до»');
			return false;
		}
		
		if ( errPeriod2 ) {
			sw.swMsg.alert('Ошибка', 'Для периода при указанном значении в поле «от» или «до» должна быть выбрана единица измерения');
			return false;
		}
		
		if ( errFromTo ) {
			sw.swMsg.alert('Ошибка', 'Значение в поле «от» не может быть больше значения в поле «до»');
			return false;
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = new Object();
		params.action = win.action;
		
		params.LimitData = Ext.util.JSON.encode(getStoreRecords(win.LimitGrid.getGrid().getStore(), {
			exceptionFields: [
				'LimitType_SysNick',
				'LimitType_isCatalog',
				'Limit_UnitText',
				'Limit_ValuesText',
				'LimitType_Name',
				'LimitType_isCalculate',
				'LimitType_isCatalogText'
			]
		}));
		
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
				win.callback(win.owner, action.result.AnalyzerTestRefValues_id);
				win.AnalyzerTestRefValues_id = action.result.AnalyzerTestRefValues_id;
				win.form.findField('AnalyzerTestRefValues_id').setValue(action.result.AnalyzerTestRefValues_id);
				win.QualitativeTestAnswerReferValueGrid.loadData({params:{AnalyzerTestRefValues_id:win.AnalyzerTestRefValues_id}, globalFilters:{AnalyzerTestRefValues_id:win.AnalyzerTestRefValues_id}});
				
				if (typeof callback == 'function') {
					callback();
				} else {
					win.hide();
				}
			}
		});
		
		return true;
	},
	filterUnitCombo: function() {
		var win = this;
		win.form.findField('Unit_id').lastQuery = '';
		win.form.findField('Unit_id').getStore().filterBy(function(rec) {
			if ( rec.get('Unit_id').inlist(win.allowedUnits) ) {
				return true;
			} else {
				return false;
			}
		});
	},
	show: function() {
		var win = this;
		sw.Promed.swAnalyzerTestRefValuesEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.AnalyzerTestRefValues_id = null;
		this.allowedUnits = [];
		this.AnalyzerTestType_Code = 1;
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { win.hide(); });
			return false;
		}
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].AnalyzerTestType_Code ) {
			this.AnalyzerTestType_Code = arguments[0].AnalyzerTestType_Code;
		}
		if ( arguments[0].allowedUnits ) {
			this.allowedUnits = arguments[0].allowedUnits;
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
		if ( arguments[0].AnalyzerTestRefValues_id ) {
			this.AnalyzerTestRefValues_id = arguments[0].AnalyzerTestRefValues_id;
		}
		if ( undefined != arguments[0].AnalyzerTest_id ) {
			this.AnalyzerTest_id = arguments[0].AnalyzerTest_id;
		} else {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_obyazatelnyiy_parametr_-_analyzertest_id'], function() { win.hide(); });
		}
		this.form.reset();
		win.QualitativeTestAnswerReferValueGrid.getGrid().getStore().removeAll();
		win.LimitGrid.getGrid().getStore().removeAll();
		win.checkSexPregPhase();
		win.form.findField('AnalyzerTest_id').setValue(win.AnalyzerTest_id);
		win.filterUnitCombo();
		
		if (this.AnalyzerTestType_Code.inlist([1,3])) {
			win.form.findField('Unit_id').showContainer();
			win.form.findField('RefValues_LowerLimit').showContainer();
			win.form.findField('RefValues_UpperLimit').showContainer();
			win.form.findField('RefValues_BotCritValue').showContainer();
			win.form.findField('RefValues_TopCritValue').showContainer();
			win.QualitativeTestAnswerReferValueGrid.hide();
		} else {
			win.form.findField('Unit_id').hideContainer();
			win.form.findField('RefValues_LowerLimit').hideContainer();
			win.form.findField('RefValues_UpperLimit').hideContainer();
			win.form.findField('RefValues_BotCritValue').hideContainer();
			win.form.findField('RefValues_TopCritValue').hideContainer();
			win.QualitativeTestAnswerReferValueGrid.show();
		}
		
		win.center();
		win.syncShadow();
		
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		switch (arguments[0].action) {
			case 'add':
				loadMask.hide();
				win.setTitle(lang['referensnyie_znacheniya_dobavlenie']);
				win.enableEdit(true);
				win.LimitGrid.loadData({params:{AnalyzerTestRefValues_id:null}, globalFilters:{AnalyzerTestRefValues_id:null}});
				win.form.findField('RefValues_Name').focus();
				break;
			case 'edit':
			case 'view':
				if (arguments[0].action == 'edit') {
					win.setTitle(lang['referensnyie_znacheniya_redaktirovanie']);
					win.enableEdit(true);
				} else {
					win.setTitle(lang['referensnyie_znacheniya_prosmotr']);
					win.enableEdit(false);
				}
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						win.hide();
					},
					params:{
						AnalyzerTestRefValues_id: win.AnalyzerTestRefValues_id
					},
					success: function (response) {
						loadMask.hide();
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) { return false}
						win.form.setValues(result[0]);
						win.AnalyzerTest_id = result[0].AnalyzerTest_id;
						
						if (!Ext.isEmpty(win.form.findField('Unit_id').getValue()) && !win.form.findField('Unit_id').getValue().inlist(win.allowedUnits)) {
							win.form.findField('Unit_id').clearValue();
						}
						
						win.form.findField('RefValues_Name').focus();
					},
					url:'/?c=AnalyzerTestRefValues&m=load'
				});
				
				win.QualitativeTestAnswerReferValueGrid.loadData({params:{AnalyzerTestRefValues_id:win.AnalyzerTestRefValues_id}, globalFilters:{AnalyzerTestRefValues_id:win.AnalyzerTestRefValues_id}});
				win.LimitGrid.loadData({params:{AnalyzerTestRefValues_id:win.AnalyzerTestRefValues_id}, globalFilters:{AnalyzerTestRefValues_id:win.AnalyzerTestRefValues_id}});
				
				break;
		}
	},
	openQualitativeTestAnswerReferValueEditWindow: function(action) {
		var win = this;
		var grid = win.QualitativeTestAnswerReferValueGrid.getGrid();
		var selected_record = grid.getSelectionModel().getSelected();
						
		if (Ext.isEmpty(win.form.findField('AnalyzerTestRefValues_id').getValue())) {
			// первым делом надо сохранить само референсное значение
			win.doSave(function() {
				win.openQualitativeTestAnswerReferValueEditWindow(action);
			});
			
			return false;
		}
		
		var params = {
			action: action,
			AnalyzerTestRefValues_id: win.form.findField('AnalyzerTestRefValues_id').getValue(),
			callback: function() {
				grid.getStore().reload();
				win.callback(win.owner, win.form.findField('AnalyzerTestRefValues_id').getValue());
			}
		};
		
		if (action == 'edit') {
			if (selected_record && selected_record.get('QualitativeTestAnswerReferValue_id')) {
				params.QualitativeTestAnswerReferValue_id = selected_record.get('QualitativeTestAnswerReferValue_id');
			} else {
				return false;
			}
		}
		
		getWnd('swQualitativeTestAnswerReferValueEditWindow').show(params);
	},
	getComboEditor: function(object) {
		var win = this;
		return new sw.Promed.SwCommonSprCombo({
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
	checkSexPregPhase: function() {
		var win = this;
		win.isMan = false;
		win.isWoman = false;
		win.LimitGrid.getGrid().getStore().each(function(record) {
			if( !Ext.isEmpty(record.get('LimitType_id')) ) {
				if (record.get('LimitType_SysNick') == 'Sex' && record.get('Limit_Values') == 1) {
					win.isMan = true;
				}
				
				if (record.get('LimitType_SysNick') == 'PregnancyUnitType' && (!Ext.isEmpty(record.get('Limit_Unit')) || !Ext.isEmpty(record.get('Limit_ValuesFrom')) || !Ext.isEmpty(record.get('Limit_ValuesTo')))) {
					win.isWoman = true;
				}
				
				if (record.get('LimitType_SysNick') == 'HormonalPhaseType' && !Ext.isEmpty(record.get('Limit_Values'))) {
					win.isWoman = true;
				}
			}
		});
		if (win.isWoman) {
			win.isMan = false;
			// ставим пол женский
			win.LimitGrid.getGrid().getStore().each(function(record) {
				if( !Ext.isEmpty(record.get('LimitType_id')) ) {
					if (record.get('LimitType_SysNick') == 'Sex') {
						record.set('Limit_Values', 2);
						record.set('Limit_ValuesText', lang['jenskiy']);
					}
				}
			});
		}
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
	blockStartingEditing: false,
	startEditData: function() {
		if (this.blockStartingEditing) {
			return false;
		}
		var win = this;
		var grid = this.LimitGrid.getGrid();
		
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
		
		// - если выбран "пол" = "мужской", то "Беременность от до" и "Фаза цикла" не доступны для ввода
		// - если указана "Беременность от до" или "Фаза цикла", то автоматом заполнять "пол" = "женский", недоступно для изменения
		if (!(
			(win.isWoman && record.get('LimitType_SysNick') == 'Sex') || 
			(win.isMan && record.get('LimitType_SysNick').inlist(['PregnancyUnitType', 'HormonalPhaseType']))
		)) {
			if (record.get('LimitType_isCatalog') == 2 && cell[1] == 5 && !Ext.isEmpty(record.get('LimitType_SysNick'))) {
				editor = new Ext.grid.GridEditor(win.getComboEditor(record.get('LimitType_SysNick')));
			} else if (record.get('LimitType_isCatalog') == 1 && cell[1] == 6) {
				editor = new Ext.grid.GridEditor(win.getNumberFieldEditor(record.get('LimitType_id')));
			} else if (record.get('LimitType_isCatalog') == 1 && cell[1] == 7) {
				editor = new Ext.grid.GridEditor(win.getNumberFieldEditor(record.get('LimitType_id')));
			} else if (record.get('LimitType_isCatalog') == 1 && cell[1] == 8 && !Ext.isEmpty(record.get('LimitType_SysNick'))) {
				editor = new Ext.grid.GridEditor(win.getComboEditor(record.get('LimitType_SysNick')));
			}
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
		
		win.QualitativeTestAnswerReferValueGrid = new sw.Promed.ViewFrame({
			actions:[
				{name:'action_add', handler: function() { win.openQualitativeTestAnswerReferValueEditWindow('add'); }},
				{name:'action_edit', handler: function() { win.openQualitativeTestAnswerReferValueEditWindow('edit'); }},
				{name:'action_view', hidden:true},
				{name:'action_delete'},
				{name:'action_refresh', hidden:true},
				{name:'action_print', hidden:true}
			],
			autoExpandColumn:'autoexpand',
			autoExpandMin:150,
			autoLoadData:false,
			border:true,
			dataUrl:'/?c=QualitativeTestAnswerReferValue&m=loadList',
			height:180,
			region:'center',
			scheme: 'lis',
			object: 'QualitativeTestAnswerReferValue',
			uniqueId: true,
			paging: false,
			totalProperty: 'totalCount',
			editformclassname:'swQualitativeTestAnswerReferValueEditWindow',
			style:'margin-bottom: 10px',
			stringfields:[
				{name:'QualitativeTestAnswerReferValue_id', type:'int', header:'ID', key:true},
				{name:'AnalyzerTestRefValues_id', type:'int', hidden:true, isparams:true},
				{name:'QualitativeTestAnswerAnalyzerTest_id', type:'int', hidden:true, isparams:true},
				{name:'QualitativeTestAnswerAnalyzerTest_Answer', type:'string', header:lang['znachenie'], width:120, id: 'autoexpand'}
			],
			title:lang['normalnyie_znacheniya'],
			toolbar:true
		});
		
		win.LimitGrid = new sw.Promed.ViewFrame({
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
				if (o && o.field) {
					if (o.field == 'Limit_UnitText') {
						o.record.set('Limit_Unit', o.value);
						o.record.set('Limit_UnitText', o.rawvalue);
					}
					
					if (o.field == 'Limit_ValuesText') {
						o.record.set('Limit_Values', o.value);
						o.record.set('Limit_ValuesText', o.rawvalue);
					}
				}
				
				win.checkSexPregPhase();
			},
			onCellSelect: function(sm,rowIdx,colIdx) {
				win.startEditData();
			},
			onLoadData: function() {
				win.checkSexPregPhase();
			},
			autoExpandColumn:'autoexpand',
			autoExpandMin:150,
			autoLoadData:false,
			border:true,
			dataUrl:'/?c=Limit&m=loadList',
			height:180,
			region:'center',
			scheme: 'lis',
			object: 'Limit',
			uniqueId: true,
			paging: false,
			totalProperty: 'totalCount',
			//editformclassname:'swLimitEditWindow',
			style:'margin-bottom: 10px',
			stringfields:[
				{name:'LimitType_id', type:'int', header:'ID', key:true},
				{name:'Limit_id', type:'int', hidden: true},
				{name:'LimitType_isCatalogText', type:'string', header: lang['tip_ogranicheniya'], width: 100},
				{name:'LimitType_isCalculate', type:'checkbox', header: lang['vyichislyaemyiy'], width: 60},
				{name:'LimitType_Name', type:'string', header: lang['naimenovanie'], width: 120, id: 'autoexpand'},
				{name:'Limit_ValuesText', type:'string', header: lang['znachenie'], width: 120},
				{name:'Limit_ValuesFrom', type:'string', header: lang['ot'], width: 60},
				{name:'Limit_ValuesTo', type:'string', header: lang['do'], width: 60},
				{name:'Limit_UnitText', type:'string', header: lang['edinitsa_izmereniya'], width: 80},
				{name:'Limit_IsActiv', type:'checkcolumnedit', hidden: true},
				{name:'LimitType_isCatalog', type:'int', hidden: true},
				{name:'LimitType_SysNick', type:'string', hidden: true},
				{name:'Limit_Values', type:'int', hidden: true},
				{name:'Limit_Unit', type:'int', hidden: true}
			],
			title:lang['ogranicheniya'],
			toolbar:false
		});
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'AnalyzerTestRefValuesEditForm',
				labelAlign: 'right',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;',
				border: true,
				labelWidth: 150,
				collapsible: true,
				region: 'north',
				url:'/?c=AnalyzerTestRefValues&m=save',
				items: [{
					name: 'AnalyzerTestRefValues_id',
					xtype: 'hidden'
				}, {
					name: 'AnalyzerTest_id',
					xtype: 'hidden'
				}, {
					name: 'RefValues_id',
					xtype: 'hidden'
				}, {
					fieldLabel: lang['naimenovanie'],
					allowBlank: false,
					name: 'RefValues_Name',
					xtype: 'textfield',
					width: 300
				}, {
					fieldLabel: lang['edinitsa_izmereniya'],
					hiddenName: 'Unit_id',
					onLoadStore: function() {
						win.filterUnitCombo();
					},
					xtype: 'swcommonsprcombo',
					prefix: 'lis_',
					comboSubject: 'Unit',
					width: 150
				}, {
					name: 'RefValues_LowerLimit',
					maskRe: /[0-9+-.]/,
					fieldLabel: lang['nijnee_normalnoe'],
					xtype: 'textfield',
					width: 100
				}, {
					name: 'RefValues_UpperLimit',
					maskRe: /[0-9+-.]/,
					fieldLabel: lang['verhnee_normalnoe'],
					xtype: 'textfield',
					width: 100
				}, {
					name: 'RefValues_BotCritValue',
					maskRe: /[0-9+-.]/,
					fieldLabel: lang['nijnee_kriticheskoe'],
					xtype: 'textfield',
					width: 100
				}, {
					name: 'RefValues_TopCritValue',
					maskRe: /[0-9+-.]/,
					fieldLabel: lang['verhnee_kriticheskoe'],
					xtype: 'textfield',
					width: 100
				},
				win.QualitativeTestAnswerReferValueGrid,
				{
					fieldLabel: lang['kommentariy'],
					name: 'RefValues_Description',
					xtype: 'textfield',
					width: 300
				},
				win.LimitGrid
				]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'AnalyzerTestRefValues_id'},
				{name: 'AnalyzerTest_id'},
				{name: 'RefValues_id'},
				{name: 'RefValues_Name'},
				{name: 'Unit_id'},
				{name: 'RefValues_LowerLimit'},
				{name: 'RefValues_UpperLimit'},
				{name: 'RefValues_BotCritValue'},
				{name: 'RefValues_TopCritValue'},
				{name: 'RefValues_Description'}
			]),
			url: '/?c=AnalyzerTestRefValues&m=save'
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
				onTabAction: function() {
					win.form.findField('RefValues_Name').focus();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		sw.Promed.swAnalyzerTestRefValuesEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('AnalyzerTestRefValuesEditForm').getForm();
	}
});