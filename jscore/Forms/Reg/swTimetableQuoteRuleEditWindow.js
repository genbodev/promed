/**
* swTimetableQuoteRuleEditWindow - окно добавления/редактирования правила квоты
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
* @version      12.11.2013
*/

/*NO PARSE JSON*/

sw.Promed.swTimetableQuoteRuleEditWindow = function(){

	var me = this;

	// Субъекты внешней квоты
	this.TimetableQuoteRuleSubjectGrid = new sw.Promed.ViewFrame({
		title: langs('Субъекты внешней квоты'),
		object: 'TimetableQuoteRuleSubject',
		editformclassname: 'swTimetableQuoteRuleSubjectEditWindow',
		dataUrl: C_TTQUOTE_SUBJECTS_LIST,
		autoLoadData: false,
		editable: true,
		selectionModel: 'cell',
		useEmptyRecord: false,
		saveAtOnce: false,
		stringfields: [
			{
				name: 'TimetableQuoteRuleSubject_id',
				type: 'int',
				header: 'ID',
				key: true,
				hidden: true
			},
			{
				name: 'Lpu_id',
				type: 'int',
				header: langs('ЛПУ'),
				hidden: true
			},
			{
				name: 'ERTerr_id',
				type: 'int',
				header: langs('Территория'),
				hidden: true
			},
			{
				name: 'PayType_id',
				type: 'int',
				header: langs('Вид оплаты'),
				hidden: true
			},
			{
				name: 'SubjectType_id',
				header: 'Тип субъекта',
				width: 100,
				renderer: function(v, p, row){
					if (v == 2) {
						return 'Территория';
					}

					if (v == 1) {
						return 'МО';
					}

					return v;
				}.createDelegate(this),
				editor: new sw.Promed.SwBaseLocalCombo({
					allowBlank: false,
					store: new Ext.data.SimpleStore({
						key: 'SubjectType_id',
						sortInfo: { field: 'SubjectType_Code' },
						fields: [
							{ name: 'SubjectType_id', type: 'int'},
							{ name: 'SubjectType_Code', type: 'int'},
							{ name: 'SubjectType_Name', type: 'string'}
						],
						data: [
							[ 1, 1, 'МО' ],
							[ 2, 2, 'Территория' ]
						]
					}),
					codeField: 'SubjectType_Code',
					displayField: 'SubjectType_Name',
					valueField: 'SubjectType_id',
					editable: true,
					enableKeyEvents: true,
					fireAfterEditOnEmpty: true,
					listWidth: 400,
					listeners: {
						'keypress': function(field, e) {
							if ( e.getKey() == e.TAB )
								field.fireEvent('blur', field);
						}.createDelegate(this)
					}
				})
			},
			{
				name: 'Lpu_Nick',
				type: 'string',
				header: langs('МО'),
				id: 'autoexpand',
				editor: new sw.Promed.SwLpuCombo({
					allowBlank: true,
					editable: true,
					enableKeyEvents: true,
					fireAfterEditOnEmpty: true,
					listWidth: 400,
					listeners: {
						'keypress': function(field, e) {
							if ( e.getKey() == e.TAB )
								field.fireEvent('blur', field);
						},
						'render': function() {
							// как появился нужно и прогрузиться
							this.getStore().load();
						}
					}
				})
			},
			{
				name: 'ERTerr_Name',
				type: 'string',
				header: langs('Территория'),
				width: 200,
				editor: new sw.Promed.SwERTerrCombo({
					allowBlank: true,
					editable: true,
					enableKeyEvents: true,
					fireAfterEditOnEmpty: true,
					listeners: {
						'keypress': function(field, e) {
							if ( e.getKey() == e.TAB )
								field.fireEvent('blur', field);
						}.createDelegate(this),
						'render': function() {
							// как появился нужно и прогрузиться
							this.getStore().load();
						}
					}
				})
			},
			{
				name: 'PayType_Name',
				type: 'string',
				header: 'Вид оплаты',
				hidden: getRegionNick()=='kz',
				width: 120,
				editor: new sw.Promed.SwPayTypeCombo({
					allowBlank: true,
					editable: true,
					enableKeyEvents: true,
					fireAfterEditOnEmpty: true,
					listeners: {
						'keypress': function(field, e) {
							if ( e.getKey() == e.TAB )
								field.fireEvent('blur', field);
						}.createDelegate(this),
						'render': function() {
							// как появился нужно и прогрузиться
							this.getStore().load();
						}
					}
				})
			},
			{
				name: 'TimetableQuote_Amount',
				type: 'int',
				header: langs('Значение'),
				editor: new Ext.form.NumberField()
			}
		],
		actions: [
			{
				name:'action_add',
				handler: function() {
					this.TimetableQuoteRuleSubjectGrid.addEmptyRow();
				}.createDelegate(this)
			},
			{
				name:'action_edit',
				handler: function() {
					this.TimetableQuoteRuleSubjectGrid.editSelectedCell();
				}.createDelegate(this)
			},
			{
				name:'action_view',
				hidden: true
			},
			{
				name:'action_delete',
				handler: function() {
					this.TimetableQuoteRuleSubjectGrid.deleteRow();
				}.createDelegate(this)
			},
			{
				name:'action_refresh',
				hidden: true
			},
			{
				name:'action_print'
			},
			{
				name:'action_save',
				hidden: true
			}
		],
		focusOn: {name:'tqreOk',type:'button'},
		focusPrev: {name:'tqreMedStaffFact_id',type:'field'},
		focusOnFirstLoad: false,
		addEmptyRow: function() {
			var grid = this.TimetableQuoteRuleSubjectGrid.getGrid();

			// Генерируем значение идентификатора с отрицательным значением
			// чтобы оперировать несохраненными записями
			var id = - swGenTempId( grid.getStore() );

			grid.getStore().loadData([{ TimetableQuoteRule_id: id, SubjectType_id: 1, Lpu_id: getGlobalOptions()['lpu_id'], Lpu_Nick: getGlobalOptions()['lpu_nick'] }], true);

			var rowsCnt = grid.getStore().getCount() - 1;
			var rowSel = 1;
			grid.getSelectionModel().select( rowsCnt, rowSel );
			grid.getView().focusCell( rowsCnt, rowSel );

			var cell = grid.getSelectionModel().getSelectedCell();
			if ( !cell || cell.length == 0 || cell[1] != rowSel ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();
			if ( !record ) {
				return false;
			}


		}.createDelegate(this),
		editSelectedCell: function(){
			var grid = this.TimetableQuoteRuleSubjectGrid.getGrid();

			var rowsCnt = grid.getStore().getCount() - 1;
			var rowSel = 1;
			var cell = grid.getSelectionModel().getSelectedCell();
			if ( !cell || cell.length == 0 ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();
			if ( !record ) {
				return false;
			}

			grid.getColumnModel().setEditable( rowSel, true );
			grid.startEditing( cell[0], cell[1] );
		}.createDelegate(this),
		onBeforeEdit: function(o) {
			if (o.field == 'Lpu_Nick' && o.record.get('SubjectType_id') != 1) {
				return false;
			}
			if (o.field == 'ERTerr_Name' && o.record.get('SubjectType_id') != 2) {
				return false;
			}
			return o;
		}.createDelegate(this),
		onAfterEdit: function(o) {
			o.grid.stopEditing(true);

			if (o && o.field) {
				if (o.field == 'Lpu_Nick') {
					o.record.set('Lpu_id', o.value);
					o.record.set('Lpu_Nick', o.rawvalue);
					if ( o.value != '' ) {
						o.record.set('ERTerr_id', null);
						o.record.set('ERTerr_Name', null);
					}
				}
				if (o.field == 'ERTerr_Name') {
					o.record.set('ERTerr_id', o.value);
					o.record.set('ERTerr_Name', o.rawvalue);
					if ( o.value != '' ) {
						o.record.set('Lpu_id', null);
						o.record.set('Lpu_Nick', null);
					}
				}
				if (o.field == 'PayType_Name') {
					o.record.set('PayType_id', o.value);
					o.record.set('PayType_Name', o.rawvalue);
				}
			}

			o.record.commit();

		}.createDelegate(this),
		deleteRow: function() {
			var grid = this.TimetableQuoteRuleSubjectGrid.getGrid();

			var record = grid.getSelectionModel().getSelected();
			if ( !record ) {
				alert(langs('Не выбрана запись!'));
				return false;
			}
			// Запись еще не сохранена? Просто вычеркиваем
			grid.getStore().remove(record);
			if ( grid.getStore().getCount() > 0 ) {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}
		}.createDelegate(this)
	});

	// Субъекты внутренней квоты
	this.TimetableQuoteRuleSubjectGridMedStaffFact = new sw.Promed.ViewFrame({
		title:langs('Субъекты внутренней квоты'),
		object: 'TimetableQuoteRuleSubjectMedStaffFact',
		editformclassname: 'swTimetableQuoteRuleSubjectEditWindow',
		dataUrl: C_TTQUOTE_SUBJECTS_LIST,
		autoLoadData: false,
		editable: true,
		selectionModel: 'cell',
		useEmptyRecord: false,
		saveAtOnce: false,
		hidden: true,
		stringfields: [
			{
				name: 'SubjectType_id',
				header: 'Тип субъекта',
				hidden: false,
				width: 100,
				renderer: function(v, p, row){
					if (v == 1) return 'Отделение';
					if (v == 2) return 'Врач';
					if (v == 3) return 'Участок';
					return v;
				}.createDelegate(this),
				editor: new sw.Promed.SwBaseLocalCombo({
					allowBlank: false,
					store: new Ext.data.SimpleStore({
						key: 'SubjectType_id',
						sortInfo: { field: 'SubjectType_Code' },
						fields: [
							{ name: 'SubjectType_id', type: 'int'},
							{ name: 'SubjectType_Code', type: 'int'},
							{ name: 'SubjectType_Name', type: 'string'}
						],
						data: [
							[ 1, 1, 'Отделение' ],
							[ 2, 2, 'Врач' ],
							[ 3, 3, 'Участок' ]
						]
					}),
					codeField: 'SubjectType_Code',
					displayField: 'SubjectType_Name',
					valueField: 'SubjectType_id',
					editable: true,
					enableKeyEvents: true,
					fireAfterEditOnEmpty: true,
					listWidth: 200,
					listeners: {
						'keypress': function(field, e) {
							if ( e.getKey() == e.TAB ){
								field.fireEvent('blur', field);
							}
						}.createDelegate(this)
					}
				})
			},
			{
				name: 'LpuSection_Name',
				type: 'string',
				header: 'Отделение',
				hidden: false,
				width: 150,
				editor: new sw.Promed.SwLpuSectionGlobalCombo({
					allowBlank: false,
					editable: true,
					enableKeyEvents: true,
					fireAfterEditOnEmpty: true,
					listWidth: 400,
					listeners: {
						'keypress': function(field, e) {
							if ( e.getKey() == e.TAB ){
								field.fireEvent('blur', field);
							}
						}.createDelegate(this),
						'render': function() {
							setLpuSectionGlobalStoreFilter({
								onDate: getGlobalOptions().date
							});
							this.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
						}
					},
					hiddenName: 'LpuSection_sid'
				})},
			{
				name: 'LpuRegion_Name',
				type: 'string',
				header: 'Участок',
				hidden: false,
				width: 150,
				editor: new sw.Promed.SwLpuRegionCombo({
					allowBlank: true,
					editable: true,
					enableKeyEvents: true,
					fireAfterEditOnEmpty: true,
					listWidth: 400,
					listeners: {
						'keypress': function(field, e) {
							if ( e.getKey() == e.TAB )
								field.fireEvent('blur', field);
						}.createDelegate(this),
						'render': function() {
							this.getStore().load({params:{showOpenerOnlyLpuRegions: 1}});
						}
					},
					hiddenName: 'LpuRegion_sid'
				})},
			{
				name: 'MedStaffFact_FIO',
				type: 'string',
				header: langs('Врач'),
				id: 'autoexpand',
				editor: new sw.Promed.SwMedStaffFactGlobalCombo({
					allowBlank: false,
					editable: true,
					enableKeyEvents: true,
					fireAfterEditOnEmpty: true,
					listWidth: 400,
					listeners: {
						'keypress': function(field, e) {
							if ( e.getKey() == e.TAB )
								field.fireEvent('blur', field);
						}.createDelegate(this),
						'show': function() {
							// как появился нужно и прогрузиться
							var record = me.TimetableQuoteRuleSubjectGridMedStaffFact.getGrid().getSelectionModel().getSelected();

							var lpusection_id = record.get('LpuSection_id');

							if (!lpusection_id) return false;

							setMedStaffFactGlobalStoreFilter({
								LpuSection_id: lpusection_id,
								onDate: getGlobalOptions().date
							});

							this.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
						}
					},
					hiddenName: 'MedStaffFact_sid'
				})
			},
			{
				name: 'PayType_Name',
				type: 'string',
				header: 'Вид оплаты',
				hidden: getRegionNick()=='kz',
				width: 120,
				editor: new sw.Promed.SwPayTypeCombo({
					allowBlank: true,
					editable: true,
					enableKeyEvents: true,
					fireAfterEditOnEmpty: true,
					listeners: {
						'keypress': function(field, e) {
							if ( e.getKey() == e.TAB )
								field.fireEvent('blur', field);
						}.createDelegate(this),
						'render': function() {
							// как появился нужно и прогрузиться
							this.getStore().load();
						}
					}
				})
			},
			{
				name: 'TimetableQuote_Amount',
				type: 'int',
				header: langs('Значение'),
				editor: new Ext.form.NumberField()
			},
			{
				name: 'TimetableQuoteRuleSubject_id',
				type: 'int',
				header: 'ID',
				key: true,
				hidden: true
			},
			{
				name: 'LpuSection_id',
				type: 'int',
				hidden: true
			},
			{
				name: 'LpuRegion_id',
				type: 'int',
				hidden: true
			},
			{
				name: 'MedStaffFact_id',
				type: 'int',
				header: langs('Врач'),
				hidden: true
			},
			{
				name: 'PayType_id',
				type: 'int',
				header: langs('Вид оплаты'),
				hidden: true
			}
		],
		actions: [
			{name:'action_add',
				handler: function() {
					this.TimetableQuoteRuleSubjectGridMedStaffFact.addEmptyRow();
				}.createDelegate(this)
			},
			{name:'action_edit', handler: function() {
					this.TimetableQuoteRuleSubjectGridMedStaffFact.editSelectedCell();
				}.createDelegate(this)
			},
			{name:'action_view', hidden: true},
			{name:'action_delete', handler: function() {
					this.TimetableQuoteRuleSubjectGridMedStaffFact.deleteRow();
				}.createDelegate(this)
			},
			{name:'action_refresh', hidden: true},
			{name:'action_print'},
			{name:'action_save', hidden: true}
		],
		focusOn: {
			name:'tqreOk',type:'button'
		},
		focusPrev: {
			name:'tqreMedStaffFact_id', type:'field'
		},
		focusOnFirstLoad: false,
		addEmptyRow: function() {
			var grid = this.TimetableQuoteRuleSubjectGridMedStaffFact.getGrid();

			// Генерируем значение идентификатора с отрицательным значением
			// чтобы оперировать несохраненными записями
			var id = - swGenTempId( grid.getStore() );

			grid.getStore().loadData([{ TimetableQuoteRule_id: id, MedStaffFact_id: null, MedStaffFact_FIO: null, SubjectType_id: 1}], true);

			var rowsCnt = grid.getStore().getCount() - 1;
			var colSel = 0;
			grid.getSelectionModel().select( rowsCnt, colSel );
			grid.getView().focusCell( rowsCnt, colSel );

			var cell = grid.getSelectionModel().getSelectedCell();
			if ( !cell || cell.length == 0 || cell[1] != colSel ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();
			if ( !record ) {
				return false;
			}

			grid.getColumnModel().setEditable( colSel, true );
			grid.startEditing( cell[0], cell[1] );
		}.createDelegate(this),
		editSelectedCell: function(){
			var grid = this.TimetableQuoteRuleSubjectGridMedStaffFact.getGrid();

			var rowsCnt = grid.getStore().getCount() - 1;
			var rowSel = 1;
			var cell = grid.getSelectionModel().getSelectedCell();
			if ( !cell || cell.length == 0 ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();
			if ( !record ) {
				return false;
			}

			grid.getColumnModel().setEditable( rowSel, true );
			grid.startEditing( cell[0], cell[1] );
		}.createDelegate(this),
		onBeforeEdit: function(o){
			if (o.field == 'MedStaffFact_FIO' && o.record.get('SubjectType_id') != 2) {
				return false;
			}

			if (o.field == 'LpuRegion_Name' && o.record.get('SubjectType_id') != 3) {
				return false;
			}

			return o;
		}.createDelegate(this),
		onAfterEdit: function(o){
			o.grid.stopEditing(true);
			//o.grid.getColumnModel().setEditable(4, false);
			if (o && o.field) {
				if (o.field == 'SubjectType_id') {
					if (o.value != 2) {
						o.record.set('MedStaffFact_id', null);
						o.record.set('MedStaffFact_FIO', null);
					}
					if (o.value != 3) {
						o.record.set('LpuRegion_id', null);
						o.record.set('LpuRegion_Name', null);
					}
				}
				if (o.field == 'LpuSection_Name') {
					o.record.set('LpuSection_id', o.value);
					o.record.set('LpuSection_Name', o.rawvalue);
					o.record.set('MedStaffFact_id', null);
					o.record.set('MedStaffFact_FIO', null);
					o.record.set('LpuRegion_id', null);
					o.record.set('LpuRegion_Name', null);
				}
				if (o.field == 'LpuRegion_Name') {
					o.record.set('LpuRegion_id', o.value);
					o.record.set('LpuRegion_Name', o.rawvalue);
				}
				if (o.field == 'MedStaffFact_FIO') {
					o.record.set('MedStaffFact_id', o.value);
					o.record.set('MedStaffFact_FIO', o.rawvalue);
				}
				if (o.field == 'PayType_Name') {
					o.record.set('PayType_id', o.value);
					o.record.set('PayType_Name', o.rawvalue);
				}
			}

			o.record.commit();

		}.createDelegate(this),
		deleteRow: function(){
			var grid = this.TimetableQuoteRuleSubjectGridMedStaffFact.getGrid();

			var record = grid.getSelectionModel().getSelected();
			if ( !record ) {
				alert(langs('Не выбрана запись!'));
				return false;
			}
			// Запись еще не сохранена? Просто вычеркиваем
			grid.getStore().remove(record);
			if ( grid.getStore().getCount() > 0 ) {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}
		}.createDelegate(this)
	});

	this.MainPanel = new sw.Promed.FormPanel({
		id:'TimetableQuoteRuleEditFormPanel',
		url: C_TTQUOTE_SAVE,
		labelWidth: 150,
		region: 'center',
		items: [
			{
				name: 'TimetableQuoteRule_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'tqreTimetableQuoteRule_id'
			},
			{
				allowBlank: false,
				emptyText: langs('Не выбрано'),
				enableKeyEvents: true,
				fieldLabel: langs('Тип квоты'),
				name: 'TimetableQuoteType_id',
				tabIndex: TABINDEX_TQRE + 1,
				anchor: '-10',
				comboSubject: 'TimetableQuoteType',
				xtype: 'swcommonsprcombo',
				listeners: {
					'select': function(combo, record, index) {
						if (record.get('TimetableQuoteType_id') >= 3) {
							this.TimetableQuoteRuleSubjectGrid.hide();
							this.TimetableQuoteRuleSubjectGridMedStaffFact.show();
							this.TimetableQuoteRuleSubjectGridMedStaffFact.doLayout();
						} else {
							this.TimetableQuoteRuleSubjectGrid.show();
							this.TimetableQuoteRuleSubjectGridMedStaffFact.hide();
							this.TimetableQuoteRuleSubjectGrid.doLayout();
						}

					}.createDelegate(this)
				}
			},
			{
				fieldLabel: langs('Промежуток действия'),
				tabIndex: TABINDEX_TQRE + 2,
				xtype: 'daterangefield',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
				name: 'TimetableQuoteRule_dateRange',
				id: 'tqreTimetableQuoteRule_dateRange',
				allowBlank:false,
				width: 180
			},
			new Ext.form.FieldSet({
				title: langs('Принимающая сторона'),
				autoHeight: true,
				items:[

					// Подразделение
					{
						hiddenName: 'LpuUnit_id',
						fieldLabel: langs('Подразделение'),
						linkedElements: [
							'tqreMedStaffFact_id',
							'tqreLpuSection_id',
							'tqreMedService_id',
						],
						tabIndex: TABINDEX_TQRE + 3,
						xtype: 'swlpuunitglobalcombo',
						id: 'tqreLpuUnit_id',
						anchor: '-10',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = me.MainPanel.getForm();

								base_form.findField('UslugaComplex_id').clearValue();
								
								switch(base_form.findField('QuoteType_id').getValue()) {
									case 4:
										me.filterOnlyDiagnostika_MedService_id();
										break;
									case 5:
										me.filterNotDiagnostika_MedService_id();
										break;
								}
							}.createDelegate(this)
						}
					},

					// Объект квотирования
					{
						xtype: 'swbaselocalcombo',
						allowBlank: false,
						tpl: '<tpl for="."><div class="x-combo-list-item"><font color="red">{QuoteType_Code}</font>&nbsp;{QuoteType_Name}</div></tpl>',
						fieldLabel: 'Объект квотирования',
						hiddenName: 'QuoteType_id',
						codeField: 'QuoteType_Code',
						displayField: 'QuoteType_Name',
						valueField: 'QuoteType_id',
						linkedElements: [
							'tqreMedService_id'
						],
						listeners: {
							'change': function(combo, newValue) {
								var base_form = this.MainPanel.getForm();
								base_form.findField('LpuSectionProfile_id').hideContainer();
								base_form.findField('LpuSection_id').hideContainer();
								base_form.findField('MedStaffFact_id').hideContainer();
								base_form.findField('MedService_id').hideContainer();
								base_form.findField('Resource_id').hideContainer();
								base_form.findField('UslugaComplex_id').hideContainer();
								base_form.findField('Resource_id').setAllowBlank(true);
								base_form.findField('MedService_id').setAllowBlank(true);
								switch(newValue) {

									// Профиль
									case 1:
										base_form.findField('LpuSectionProfile_id').showContainer();
										break;

									// Отделение
									case 2:
										base_form.findField('LpuSection_id').showContainer();
										break;

									// Врач
									case 3:
										base_form.findField('MedStaffFact_id').showContainer();
										break;

									// Ресурс
									case 4:

										// Фильтруем Службы (службы с типом «Диагностика»)
										this.filterOnlyDiagnostika_MedService_id();

										// Служба - комбобокс
										var comboMedService = base_form.findField('MedService_id');
										comboMedService.setAllowBlank(false);
										comboMedService.showContainer();
										comboMedService.fireEvent('change', comboMedService, comboMedService.getValue());
										
										// Ресурс
										base_form.findField('Resource_id').setAllowBlank(false);
										base_form.findField('Resource_id').showContainer();
										break;

									// Услуга
									case 5:

										// Фильтруем Службы (служб с типом отличным от «Диагностика»)
										this.filterNotDiagnostika_MedService_id();

										// Служба - комбобокс
										var comboMedService = base_form.findField('MedService_id');
										comboMedService.setAllowBlank(false);
										comboMedService.showContainer();
										comboMedService.fireEvent('change', comboMedService, comboMedService.getValue());

										// Услуга – комбобокс
										base_form.findField('UslugaComplex_id').showContainer();

										base_form.findField('Resource_id').clearValue();
										break;
								}
							}.createDelegate(this)
						},
						store: new Ext.data.SimpleStore({
							key: 'QuoteType_id',
							sortInfo: { field: 'QuoteType_Code' },
							fields: [
								{ name: 'QuoteType_id', type: 'int'},
								{ name: 'QuoteType_Code', type: 'int'},
								{ name: 'QuoteType_Name', type: 'string'}
							],
							data: [
								[ 1, 1, 'Профиль' ],
								[ 2, 2, 'Отделение' ],
								[ 3, 3, 'Врач' ],
								[ 4, 4, 'Ресурс' ],
								[ 5, 5, 'Служба' ]
							]
						})
					},


					// Профиль
					{
						hiddenName: 'LpuSectionProfile_id',
						fieldLabel: langs('Профиль'),
						tabIndex: TABINDEX_TQRE + 4,
						xtype: 'swlpusectionprofileremotecombotimetable',
						lastQuery: '',
						id: 'tqreLpuSectionProfile_id',
						anchor: '-10',
						doQuery: function(q, forceAll) {
							this.MainPanel.getForm().findField('LpuSectionProfile_id').getStore().load({
								params: { LpuUnit_id: this.MainPanel.getForm().findField('LpuUnit_id').getValue() }
							});
						}.createDelegate(this),
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var record = combo.getStore().getById(newValue);
								if ( newValue != '' && newValue != 0 ) {
									this.MainPanel.getForm().findField('LpuSection_id').clearValue();
									this.MainPanel.getForm().findField('MedStaffFact_id').clearValue();
									this.MainPanel.getForm().findField('MedService_id').clearValue();
									this.MainPanel.getForm().findField('Resource_id').clearValue();
								}
							}.createDelegate(this)
						}
					},


					// Отделение
					{
						hiddenName: 'LpuSection_id',
						fieldLabel: langs('Отделение'),
						linkedElements: [
							'tqreMedStaffFact_id'
						],
						parentElementId: 'tqreLpuUnit_id',
						tabIndex: TABINDEX_TQRE + 5,
						xtype: 'swlpusectionglobalcombo',
						id: 'tqreLpuSection_id',
						anchor: '-10',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var record = combo.getStore().getById(newValue);
								if ( newValue != '' && newValue != 0 ) {
									this.MainPanel.getForm().findField('LpuSectionProfile_id').clearValue();
									this.MainPanel.getForm().findField('MedStaffFact_id').clearValue();
									this.MainPanel.getForm().findField('MedService_id').clearValue();
									this.MainPanel.getForm().findField('Resource_id').clearValue();
									this.MainPanel.getForm().findField('UslugaComplex_id').clearValue();
								}
							}.createDelegate(this)
						}
					},


					// Врач
					{
						hiddenName: 'MedStaffFact_id',
						fieldLabel: langs('Врач'),
						parentElementId: 'tqreLpuSection_id',
						tabIndex: TABINDEX_TQRE + 6,
						xtype: 'swmedstafffactglobalcombo',
						id: 'tqreMedStaffFact_id',
						anchor: '-10',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var record = combo.getStore().getById(newValue);
								if ( newValue != '' && newValue != 0 ) {
									this.MainPanel.getForm().findField('LpuSection_id').clearValue();
									this.MainPanel.getForm().findField('LpuSectionProfile_id').clearValue();
									this.MainPanel.getForm().findField('MedService_id').clearValue();
									this.MainPanel.getForm().findField('Resource_id').clearValue();
									this.MainPanel.getForm().findField('UslugaComplex_id').clearValue();
								}
							}.createDelegate(this)
						}
					},


					// Служба
					{
						hiddenName: 'MedService_id',
						fieldLabel: 'Служба',
						tabIndex: TABINDEX_TQRE + 6,
						xtype: 'swmedserviceglobalcombo',
						id: 'tqreMedService_id',

						anchor: '-10',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.MainPanel.getForm();
								var record = combo.getStore().getById(newValue);
								if(Ext.isEmpty(record)) {
									combo.clearValue();
								} else
								if ( newValue != '' && newValue != 0 ) {
									base_form.findField('LpuSection_id').clearValue();
									base_form.findField('LpuSectionProfile_id').clearValue();
									base_form.findField('MedStaffFact_id').clearValue();
								}

								if (base_form.findField('Resource_id').getFieldValue('MedService_id') != newValue) {
									base_form.findField('Resource_id').clearValue();
								}
								base_form.findField('Resource_id').getStore().baseParams.MedService_id = newValue;
								base_form.findField('Resource_id').lastQuery = 'This query sample that is not will never appear';
							}.createDelegate(this)
						}
					},

					// Ресурс
					{
						hiddenName: 'Resource_id',
						fieldLabel: langs('Ресурс'),
						onTrigger2Click: function() {
							this.clearValue();
							this.fireEvent('change', this, this.getValue());
						}.createDelegate(this),
						tabIndex: TABINDEX_TQRE + 6,
						xtype: 'swresourceremotecombo',
						id: 'tqreResource_id',
						anchor: '-10',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.MainPanel.getForm();
								var record = combo.getStore().getById(newValue);
								if ( newValue != '' && newValue != 0 ) {
									base_form.findField('LpuSection_id').clearValue();
									base_form.findField('LpuSectionProfile_id').clearValue();
									base_form.findField('MedStaffFact_id').clearValue();

									base_form.findField('MedService_id').setValue(combo.getFieldValue('MedService_id'));

									base_form.findField('MedService_id').fireEvent('change', base_form.findField('MedService_id'), base_form.findField('MedService_id').getValue());
								}
							}.createDelegate(this)
						}
					},


					// Услуга
					{
						hiddenName: 'UslugaComplex_id',
						fieldLabel: langs('Услуга'),
						tabIndex: TABINDEX_TQRE + 7,
						xtype: 'swuslugacomplexpidcombo',
						id: 'tqreUslugaComplex_id',
						anchor: '-10',
						doQuery: function(q, forceAll) {
							this.MainPanel.getForm().findField('UslugaComplex_id').getStore().load({
								params:{
									LpuUnit_id: this.MainPanel.getForm().findField('LpuUnit_id').getValue(),
									MedService_id: this.MainPanel.getForm().findField('MedService_id').getValue()
								}
							});
						}.createDelegate(this)
					},

				]
			}),

			this.TimetableQuoteRuleSubjectGrid,
			this.TimetableQuoteRuleSubjectGridMedStaffFact
		],
		reader: new Ext.data.JsonReader({success:function(){}}, [
			{name: 'TimetableQuoteRule_id'},
			{name: 'TimetableQuoteType_id'},
			{name: 'QuoteType_id'},
			{name: 'LpuUnit_id'},
			{name: 'LpuSectionProfile_id'},
			{name: 'LpuSection_id'},
			{name: 'MedStaffFact_id'},
			{name: 'MedService_id'},
			{name: 'Resource_id'},
			{name: 'UslugaComplex_id'},
			{name: 'TimetableQuoteRule_dateRange'}
		])
	});

	sw.Promed.swTimetableQuoteRuleEditWindow.superclass.constructor.call(this, {
		/**
		 * Настройки для отладки
		 */
		codeRefresh: true,
		objectName: 'swTimetableQuoteRuleEditWindow',
		objectSrc: '/jscore/Forms/Reg/swTimetableQuoteRuleEditWindow.js',

		/**
		 * Настройки окна
		 */
		buttonAlign: 'right',
		closable: true,
		closeAction: 'hide',
		draggable: true,
		height: 500,
		id: 'TimetableQuoteRuleEditWindow',
		layout: 'fit',
		maximized: false,
		modal: true,
		plain: true,
		resizable: false,
		title: WND_REG_QUOTERULEEDIT,
		width: 750,

		buttons: [
			// Сохранить
			{
				text: BTN_FRMSAVE,
				id: 'tqreOk',
				tabIndex: TABINDEX_TQRE + 20,
				iconCls: 'save16',
				handler: this.doSave.createDelegate(this)
			},

			{
				text:'-'
			},

			// Помощь
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				tabIndex: TABINDEX_TQRE + 21,
				handler: this.doHelp.createDelegate(this)
			},

			// Закрыть
			{
				text: BTN_FRMCANCEL,
				id: 'tqreCancel',
				tabIndex: TABINDEX_TQRE + 22,
				iconCls: 'cancel16',
				handler: this.doHide.createDelegate(this)
			}
		],
		items: [
			this.MainPanel
		]
	});
}

Ext.extend(sw.Promed.swTimetableQuoteRuleEditWindow, sw.Promed.BaseForm, {


	
	/**
	 * Конструктор
	 */
	initComponent: function() {
		sw.Promed.swTimetableQuoteRuleEditWindow.superclass.initComponent.apply(this, arguments);
	},


	/**
	 * Отображение окна
	 */
	show: function() {
		sw.Promed.swTimetableQuoteRuleEditWindow.superclass.show.apply(this, arguments);


		var me = this;
		var form = this.MainPanel.getForm();



		form.reset();



		// mode
		var mode = '';
		if ( arguments[0].mode && arguments[0].mode == 'copy') {
			mode = 'copy';
		}



		// Загружаем данные если передан TimetableQuoteRule_id
		var dataRule = null;
		if ( arguments[0] && arguments[0].TimetableQuoteRule_id) {
			dataRule = me._loadData_TimetableQuoteRule(arguments[0].TimetableQuoteRule_id);
		}



		// Title
		this.setTitle(WND_REG_QUOTERULEADD);
		if (mode != 'copy') {
			this.setTitle(WND_REG_QUOTERULEEDIT);
		}




		// TimetableQuoteRule_id
		var TimetableQuoteType_id = null;
		if(dataRule && ! Ext.isEmpty(dataRule.TimetableQuoteType_id)){
			if(mode != 'copy'){
				TimetableQuoteType_id = dataRule.TimetableQuoteRule_id;
			}
		}
		me._processValue_TimetableQuoteRule_id(TimetableQuoteType_id);





		// TimetableQuoteRule_dateRange
		var TimetableQuoteRule_dateRange = null;
		if(dataRule && ! Ext.isEmpty(dataRule.TimetableQuoteRule_dateRange)){
			if(mode != 'copy'){
				TimetableQuoteRule_dateRange = dataRule.TimetableQuoteRule_dateRange;
			}
		}
		me._processValue_TimetableQuoteRule_dateRange(TimetableQuoteRule_dateRange);




		// TimetableQuoteType_id
		var TimetableQuoteType_id = null;
		if(dataRule && ! Ext.isEmpty(dataRule.TimetableQuoteType_id)){
			TimetableQuoteType_id = dataRule.TimetableQuoteType_id;
		} else {
			TimetableQuoteType_id = 1;
		}
		me._processValue_TimetableQuoteType_id(TimetableQuoteType_id);











		// Подразделение (LpuUnit_id)
		var LpuUnit_id = null
		if(dataRule && ! Ext.isEmpty(dataRule.LpuUnit_id)){
			LpuUnit_id = dataRule.LpuUnit_id;
		} else {
			if (arguments[0].LpuUnit_id) {
				LpuUnit_id = arguments[0].LpuUnit_id;
			}
		}
		me._processValue_LpuUnit_id(LpuUnit_id);





		// Объект квотирования (QuoteType_id)
		var QuoteType_id = null;
		if(dataRule && ! Ext.isEmpty(dataRule.QuoteType_id)){
			QuoteType_id = dataRule.QuoteType_id;
		} else {
			QuoteType_id = 1;
		}
		me._processValue_QuoteType_id(QuoteType_id);




		// Профиль (LpuSectionProfile_id)
		var LpuSectionProfile_id = null;
		if(dataRule && ! Ext.isEmpty(dataRule.LpuSectionProfile_id)){
			LpuSectionProfile_id = dataRule.LpuSectionProfile_id;
		} else {
			if (arguments[0].LpuSectionProfile_id){
				LpuSectionProfile_id = arguments[0].LpuSectionProfile_id;
			}
		}
		me._processValue_LpuSectionProfile_id(LpuSectionProfile_id);





		// Отделение (LpuSection_id)
		var LpuSection_id = null;
		if(dataRule && ! Ext.isEmpty(dataRule.LpuSection_id)){
			LpuSection_id = dataRule.LpuSection_id;
		} else {
			if (arguments[0].LpuSection_id) {
				LpuSection_id = arguments[0].LpuSection_id;
			}
		}
		me._processValue_LpuSection_id(LpuSection_id);






		// Врач (MedStaffFact_id)
		var MedStaffFact_id = null;
		if(dataRule && ! Ext.isEmpty(dataRule.MedStaffFact_id)){
			MedStaffFact_id = dataRule.MedStaffFact_id;
		} else {
			if (arguments[0].MedStaffFact_id) {
				MedStaffFact_id = arguments[0].MedStaffFact_id;
			}
		}
		me._processValue_MedStaffFact_id(MedStaffFact_id);






		// Служба (MedService_id)
		var MedService_id = null;
		if(dataRule && ! Ext.isEmpty(dataRule.MedService_id)){
			MedService_id = dataRule.MedService_id;
		} else {
			if (arguments[0].MedService_id) {
				MedService_id = arguments[0].MedService_id;
			}
		}
		me._processValue_MedService_id(MedService_id);




		// Ресурс (Resource_id)
		var Resource_id = null;
		if(dataRule && ! Ext.isEmpty(dataRule.Resource_id)){
			Resource_id = dataRule.Resource_id
		} else {
			if (arguments[0].Resource_id) {
				Resource_id = arguments[0].Resource_id;
			}
		}
		me._processValue_Resource_id(Resource_id);




		// Услуга (UslugaComplex_id)
		var UslugaComplex_id = null;
		if(dataRule && ! Ext.isEmpty(dataRule.UslugaComplex_id)){
			UslugaComplex_id = dataRule.UslugaComplex_id;
		} else {
			if (arguments[0].UslugaComplex_id) {
				UslugaComplex_id = arguments[0].UslugaComplex_id;
			}
		}
		me._processValue_UslugaComplex_id(UslugaComplex_id);








		this.TimetableQuoteRuleSubjectGrid.getGrid().getStore().removeAll();
		this.TimetableQuoteRuleSubjectGridMedStaffFact.getGrid().getStore().removeAll();
		if(dataRule && ! Ext.isEmpty(dataRule.TimetableQuoteType_id)){
			if(dataRule.TimetableQuoteType_id < 3){

				this.TimetableQuoteRuleSubjectGridMedStaffFact.hide();

				this.TimetableQuoteRuleSubjectGrid.show();
				if(dataRule && ! Ext.isEmpty(dataRule.TimetableQuoteRule_id)) {
					this.TimetableQuoteRuleSubjectGrid.loadData({
						globalFilters: {
							'TimetableQuoteRule_id': dataRule.TimetableQuoteRule_id
						}
					});
				}

			} else {
				this.TimetableQuoteRuleSubjectGrid.hide();


				this.TimetableQuoteRuleSubjectGridMedStaffFact.show();
				this.TimetableQuoteRuleSubjectGridMedStaffFact.doLayout();
				
				if(dataRule && ! Ext.isEmpty(dataRule.TimetableQuoteRule_id)) {
					this.TimetableQuoteRuleSubjectGridMedStaffFact.loadData({
						globalFilters: {
							'TimetableQuoteRule_id': dataRule.TimetableQuoteRule_id
						}
					});
				}
			}
		} else {

			this.TimetableQuoteRuleSubjectGridMedStaffFact.hide();

			this.TimetableQuoteRuleSubjectGrid.show();
			this.TimetableQuoteRuleSubjectGrid.doLayout();
		}

	},



	_loadData_TimetableQuoteRule: function(TimetableQuoteRule_id){

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();

		var dataRule = null;
		$.ajax({
			method: "POST",
			url: C_TTQUOTE_LOAD,
			data: {
				'TimetableQuoteRule_id': TimetableQuoteRule_id
			},
			async: false, // ВАЖНО!!
			success: function (response) {
				var result = Ext.util.JSON.decode(response);
				if ( ! Ext.isEmpty(result) && !Ext.isEmpty(result[0])) {
					dataRule = result[0];
				}
			}
		});

		loadMask.hide();

		return dataRule;
	},




	// -----------------------------------------------------------------------------------------------------------------
	// Обработка полученных значений формы при открытии формы
	_processValue_TimetableQuoteRule_id: function(TimetableQuoteRule_id){
		var me = this;
		var form = this.MainPanel.getForm();

		if( ! Ext.isEmpty(TimetableQuoteRule_id)) {
			form.findField('TimetableQuoteRule_id').setValue(TimetableQuoteRule_id);
		}
		return me;
	},
	_processValue_TimetableQuoteRule_dateRange: function(TimetableQuoteRule_dateRange){
		var me = this;
		var form = this.MainPanel.getForm();

		form.findField('TimetableQuoteRule_dateRange').validate();

		if( ! Ext.isEmpty(TimetableQuoteRule_dateRange)) {
			form.findField('TimetableQuoteRule_dateRange').setValue(TimetableQuoteRule_dateRange);
		}

		return me;
	},
	_processValue_TimetableQuoteType_id: function(TimetableQuoteType_id){
		var me = this;
		var form = this.MainPanel.getForm();

		if( ! Ext.isEmpty(TimetableQuoteType_id)) {
			form.findField('TimetableQuoteType_id').setValue(TimetableQuoteType_id);
		}

		return me;
	},
	_processValue_QuoteType_id: function(QuoteType_id){
		var me = this;
		var form = this.MainPanel.getForm();

		if( ! Ext.isEmpty(QuoteType_id)) {
			form.findField('QuoteType_id').setValue(QuoteType_id);
			form.findField('QuoteType_id').fireEvent('change', form.findField('QuoteType_id'), form.findField('QuoteType_id').getValue());
		}

		return me;
	},
	_processValue_MedStaffFact_id: function(MedStaffFact_id){
		var me = this;
		var form = this.MainPanel.getForm();

		form.findField('MedStaffFact_id').clearValue();
		swMedStaffFactGlobalStore.clearFilter();
		setMedStaffFactGlobalStoreFilter({
			'onDate': getGlobalOptions().date,
			'disableInDoc': false,
			'all': true
		});
		form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

		if( ! Ext.isEmpty(MedStaffFact_id)){
			form.findField('MedStaffFact_id').setValue(MedStaffFact_id);
		}
		return me;
	},
	_processValue_LpuUnit_id: function(LpuUnit_id){
		var me = this;
		var form = this.MainPanel.getForm();

		form.findField('LpuUnit_id').clearValue();

		form.findField('LpuUnit_id').getStore().loadData(getStoreRecords(swLpuUnitGlobalStore));

		if( ! Ext.isEmpty(LpuUnit_id)) {
			form.findField('LpuUnit_id').setValue(LpuUnit_id);
			form.findField('LpuUnit_id').fireEvent('change', form.findField('LpuUnit_id'), form.findField('LpuUnit_id').getValue());
		}

		return me;
	},
	_processValue_LpuSectionProfile_id: function(LpuSectionProfile_id){
		var me = this;
		var form = this.MainPanel.getForm();

		form.findField('LpuSectionProfile_id').clearValue();
		form.findField('LpuSectionProfile_id').getStore().load({
			callback: function(){
				if( ! Ext.isEmpty(LpuSectionProfile_id)){
					form.findField('LpuSectionProfile_id').setValue(LpuSectionProfile_id);
				}
			}
		});

		return me;
	},
	_processValue_LpuSection_id: function(LpuSection_id){
		var me = this;
		var form = this.MainPanel.getForm();

		form.findField('LpuSection_id').clearValue();

		swLpuSectionGlobalStore.clearFilter();
		setLpuSectionGlobalStoreFilter({
			onDate: getGlobalOptions().date
		});


		form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

		if( ! Ext.isEmpty(LpuSection_id)) {
			form.findField('LpuSection_id').setValue(LpuSection_id);
		}
		return me;
	},
	// -----------------------------------------------------------------------------------------------------------------



	// -----------------------------------------------------------------------------------------------------------------
	// MedService_id
	_processValue_MedService_id: function(MedService_id){

		var me = this;
		var form = this.MainPanel.getForm();

		form.findField('MedService_id').clearValue();

		swMedServiceGlobalStore.clearFilter();
		setMedServiceGlobalStoreFilter({
			onDate: getGlobalOptions().date
		});
		form.findField('MedService_id').getStore().loadData(getStoreRecords(swMedServiceGlobalStore));


		if( ! Ext.isEmpty(MedService_id)) {
			form.findField('MedService_id').setValue(MedService_id);
			form.findField('MedService_id').fireEvent('change', form.findField('MedService_id'), form.findField('MedService_id').getValue());
		}

		return me;
	},
	/**
	 * Фильтруем список "Служба"
	 *
	 * @param mode (
	 * 		only_diagnostika - службы с типом «Диагностика»,
	 * 		not_diagnostika - служб с типом отличным от «Диагностика»
	 * )
	 *
	 */
	_filter_MedService_id: function(mode){
		var me = this;


		var base_form = me.MainPanel.getForm();

		var LpuUnit_id = base_form.findField('LpuUnit_id').getValue();
		
		var MedService = base_form.findField('MedService_id');

		// Служба - комбобокс
		MedService.clearBaseFilterAdvanced();
		MedService.getStore().clearFilter();
				
		MedService.setBaseFilterAdvanced(function(rec) {
			// пропускаем все значения
			var result = true,
				filterLpu = true;//если LpuUnit_id еще пусто - оставляем, чтобы было потом что фильтровать в базовом фильтре

			switch(mode){

				// службы с типом «Диагностика»
				case 'only_diagnostika':
					result = (rec.get('MedServiceType_SysNick') == 'func');
					break;

				// служб с типом отличным от «Диагностика»
				case 'not_diagnostika':
					result = (rec.get('MedServiceType_SysNick') != 'func');
					break;
			}
			if(!Ext.isEmpty(LpuUnit_id) && rec.get('LpuUnit_id') != LpuUnit_id) filterLpu = false;
			return (result && filterLpu);
		});
	},
	filterOnlyDiagnostika_MedService_id: function(){
		this._filter_MedService_id('only_diagnostika');
	},
	filterNotDiagnostika_MedService_id: function(){
		this._filter_MedService_id('not_diagnostika');
	},
	// -----------------------------------------------------------------------------------------------------------------




	_processValue_Resource_id: function(Resource_id){
		var me = this;
		var form = this.MainPanel.getForm();

		form.findField('Resource_id').clearValue();

		form.findField('Resource_id').getStore().baseParams.onDate = getGlobalOptions().date;
		form.findField('Resource_id').getStore().load({
			params: {
				MedService_id: form.findField('MedService_id').getValue()
			},
			callback: function(){

				if( ! Ext.isEmpty(Resource_id)){
					form.findField('Resource_id').setValue(Resource_id);
					form.findField('Resource_id').fireEvent('change', form.findField('Resource_id'), form.findField('Resource_id').getValue());
				}

			}
		});


		return me;
	},
	_processValue_UslugaComplex_id: function(UslugaComplex_id){
		var me = this;
		var form = this.MainPanel.getForm();

		form.findField('UslugaComplex_id').clearValue();

		form.findField('UslugaComplex_id').getStore().load({
			params:{
				LpuUnit_id: form.findField('LpuUnit_id').getValue(),
				MedService_id: form.findField('MedService_id').getValue()
			},
			callback: function(){

				if( ! Ext.isEmpty(UslugaComplex_id)) {
					form.findField('UslugaComplex_id').setValue(UslugaComplex_id);
					form.findField('UslugaComplex_id').fireEvent('change', form.findField('UslugaComplex_id'), form.findField('UslugaComplex_id').getValue());
				}
			}
		});

		return me;
	},


	/**
	 * Сохранение правила
	 */
	doSave : function() {

		var me = this;
		var form = me.MainPanel.getForm();

		if ( ! form.isValid()){
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if ( form.findField('TimetableQuoteType_id').getValue() < 3 ) {
			var rule_subjects = getStoreRecords( me.TimetableQuoteRuleSubjectGrid.getGrid().getStore(), {} );
			if (rule_subjects.length == 0 ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {},
					icon: Ext.Msg.WARNING,
					msg: langs('Должен быть задан хотя бы один субъект квоты'),
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}
		else {
			var rule_subjects = getStoreRecords( me.TimetableQuoteRuleSubjectGridMedStaffFact.getGrid().getStore(), {} );
			if (rule_subjects.length == 0 ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {},
					icon: Ext.Msg.WARNING,
					msg: langs('Должен быть задан хотя бы один субъект квоты'),
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}


		


		var loadMask = new Ext.LoadMask(me.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		var params = {};
		
		params.action = me.action;
		
		if ( form.findField('TimetableQuoteType_id').getValue() < 3 ) {
			var rule_subjects = getStoreRecords( me.TimetableQuoteRuleSubjectGrid.getGrid().getStore(), {} );
		} else {
			var rule_subjects = getStoreRecords( me.TimetableQuoteRuleSubjectGridMedStaffFact.getGrid().getStore(), {} );
		}
		params.rule_subjects = Ext.util.JSON.encode( rule_subjects );
			
		var date_range = form.findField('TimetableQuoteRule_dateRange');
		params['TimetableQuoteRule_begDT'] = Ext.util.Format.date(date_range.getValue1(),'d.m.Y');
		params['TimetableQuoteRule_endDT'] = Ext.util.Format.date(date_range.getValue2(),'d.m.Y');
		
		form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					me.doHide();
					Ext.getCmp('TimetableQuoteEditorWindow').applyFilters();
				}
			}.createDelegate(this)
		});
		return true;
	},
	doHide: function(){
		this.hide();
	},
	doHelp: function(){
		ShowHelp(this.title)
	},


});

