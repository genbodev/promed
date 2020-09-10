/**
* swEvnReceptSearchWindow - окно поиска рецептов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      14.05.2009
* @comment      Префикс для id компонентов ERS (EvnReceptSearch)
* @comment      tabIndex от 301 до 400
*/

sw.Promed.swEvnReceptSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: "hide",
	collapsible: true,
	deleteEvnRecept: function() {
		var current_window = this;
		var grid = current_window.findById('ERS_EvnReceptSearchViewGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		if ( !record.get('EvnRecept_id') ) {
			return false;
		}
		/*else if ( record.get('ReceptRemoveCauseType_id') ) {
			sw.swMsg.alert(lang['oshibka'], lang['retsept_uje_otmechen_kak_udalennyiy']);
			return false;
		}*/
		var DeleteType = 0; //Пометка к удалению
		if (isSuperAdmin() || isLpuAdmin() || isUserGroup('ChiefLLO') || getRegionNick() == 'msk') {
			DeleteType = 1;
		}
		else
		{
			if (record.get('ReceptType_Code') == 2 && record.get('EvnRecept_IsSigned') == 'false' && record.get('EvnRecept_IsPrinted') == 'false') { //Если тип рецепта - "На листе" и рецепт не подписан
				DeleteType = 1; //Удаление
			}
		}
		if(DeleteType == 0 && record.get('Recept_MarkDeleted')=='true'){ //Не даем дважды помечать к удалению
			sw.swMsg.alert(lang['oshibka'], 'Рецепт уже помечен к удалению');
			return false;
		}
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					getWnd('swEvnReceptDeleteWindow').show({
						callback: function() {
							grid.getStore().reload();
						},
						EvnRecept_id: record.get('EvnRecept_id'),
						DeleteType: DeleteType,
						onHide: function() {
							
						}
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: langs((getRegionNick() == 'msk' ? 'Аннулировать' : 'Удалить') + ' рецепт?'),
			title: lang['vopros']
		});
	},
	UndoDeleteEvnRecept: function(){
		if ( getRegionNick() == 'msk' ) {
			return false;
		}

		var current_window = this;
		var grid = current_window.findById('ERS_EvnReceptSearchViewGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		if ( !record.get('EvnRecept_id') ) {
			return false;
		}
		var that = this;
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							grid.getStore().reload();
						}.createDelegate(that),
						params: {
							EvnRecept_id: record.get('EvnRecept_id')
						},
						url: '/?c=EvnRecept&m=UndoDeleteEvnRecept'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: 'Вы действительно желаете восстановить рецепт?',
			title: lang['vopros']
		});
	},
	doReset: function(reset_form_flag) {
		var form = this.findById('EvnReceptSearchForm');
		var grid = this.findById('ERS_EvnReceptSearchViewGrid').getGrid();

		if ( reset_form_flag == true ) {
			form.getForm().reset();

			form.getForm().findField('EvnRecept_Is7Noz').setValue(1);

			if ( form.getForm().findField('AttachLpu_id') != null ) {
				if(getWnd('swWorkPlaceMZSpecWindow').isVisible())
				{
					form.getForm().findField('AttachLpu_id').setValue(getGlobalOptions().lpu_id);
					form.getForm().findField('AttachLpu_id').fireEvent('change', form.getForm().findField('AttachLpu_id'), form.getForm().findField('AttachLpu_id').getValue(),0);
				}
				else	
					form.getForm().findField('AttachLpu_id').fireEvent('change', form.getForm().findField('AttachLpu_id'), 0, 1);
			}

			if ( form.getForm().findField('ER_PrivilegeType_id') != null ) {
				form.getForm().findField('ER_PrivilegeType_id').lastQuery = '';
			}

			if ( form.getForm().findField('LpuRegion_id') != null ) {
				form.getForm().findField('LpuRegion_id').lastQuery = '';
				form.getForm().findField('LpuRegion_id').getStore().clearFilter();
			}

			if ( form.getForm().findField('PrivilegeType_id') != null ) {
				form.getForm().findField('PrivilegeType_id').lastQuery = '';
			}

			if ( form.getForm().findField('LpuRegionType_id') != null ) {
				form.getForm().findField('LpuRegionType_id').getStore().clearFilter();
			}

			if ( form.getForm().findField('PersonCardStateType_id') != null ) {
				form.getForm().findField('PersonCardStateType_id').fireEvent('change', form.getForm().findField('PersonCardStateType_id'), 1, 0);
			}

			if ( form.getForm().findField('PrivilegeStateType_id') != null ) {
				form.getForm().findField('PrivilegeStateType_id').fireEvent('change', form.getForm().findField('PrivilegeStateType_id'), 1, 0);
			}
		}

		grid.getStore().removeAll();

		//grid.getTopToolbar().items.items[2].disable();
		/*grid.getTopToolbar().items.items[4].disable();
		grid.getTopToolbar().items.items[5].disable();
		LoadEmptyRow(grid, 'data');*/

		if ( form.getForm().findField('RegisterSelector_id').rendered && !getRegionNick().inlist([ 'kz' ]) ) {
			form.getForm().findField('RegisterSelector_id').clearValue();
			form.getForm().findField('RegisterSelector_id').fireEvent('change', form.getForm().findField('RegisterSelector_id'), null, 1);
		}

		form.findById('ERS_SearchFilterTabbar').setActiveTab(0);
		form.findById('ERS_SearchFilterTabbar').getActiveTab().fireEvent('activate', form.findById('ERS_SearchFilterTabbar').getActiveTab());

		form.getForm().findField('EvnRecept_IsKEK').setVKProtocolFieldsVisible();
	},
	doSearch: function(params) {
		if ( params && params['soc_card_id'] )
			var soc_card_id = params['soc_card_id'];
		var current_window = this;

		var form = current_window.findById('EvnReceptSearchForm');
		
		if ( form.isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			return false;
		}
		
		var grid = current_window.findById('ERS_EvnReceptSearchViewGrid').getGrid();

		grid.getStore().removeAll();

		//grid.getTopToolbar().items.items[2].disable();
		/*grid.getTopToolbar().items.items[4].disable();
		grid.getTopToolbar().items.items[5].disable();*/

		if ( !form.getForm().isValid() ) {
			sw.swMsg.alert(lang['poisk'], lang['proverte_pravilnost_zapolneniya_poley_na_forme_poiska']);
			return false;
		}

		if ( form.getForm().findField('PersonPeriodicType_id').getValue() == 2 && (typeof params != 'object' || !params.ignorePersonPeriodicType ) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						this.doSearch({
							ignorePersonPeriodicType: true
						});
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: lang['vyibran_tip_poiska_cheloveka_po_sostoyaniyu_na_moment_sluchaya_pri_vyibrannom_variante_poisk_rabotaet_znachitelno_medlennee_hotite_prodoljit_poisk'],
				title: lang['preduprejdenie']
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет поиск..." });
		loadMask.show();

		var post = getAllFormFieldValues(form);

		if ( post.PersonCardStateType_id == null ) {
			post.PersonCardStateType_id = 1;
		}

		if ( post.PrivilegeStateType_id == null ) {
			post.PrivilegeStateType_id = 1;
		}
		
		if ( soc_card_id )
		{
			var post = {
				soc_card_id: soc_card_id,
				SearchFormType: post.SearchFormType
			};
		}
		else
		{
			grid.getStore().baseParams = getAllFormFieldValues(form);
		}
				
		grid.getStore().baseParams.SearchFormType = 'EvnRecept';
		post.limit = 100;
		post.start = 0;
		if (!Ext.isEmpty(post.autoLoadArchiveRecords)) {
			current_window.findById('ERS_EvnReceptSearchViewGrid').showArchive = true;
		} else {
			current_window.findById('ERS_EvnReceptSearchViewGrid').showArchive = false;
		}

		grid.getStore().load({
			callback: function(records, options, success) {
				loadMask.hide();
			},
			params: post
		});
	},
	draggable: true,
	getLpuUnitPolkaCount: function(params){
		params = Ext.applyIf(params, {callback: Ext.emptyFn});
		Ext.Ajax.request({
			params: {Lpu_id: getGlobalOptions().lpu_id, LpuUnitType_SysNick: 'polka'},
			url: '/?c=LpuStructure&m=getLpuUnitCountByType',
			callback: function(options, success, response) {
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					params.callback(response_obj);
				}
			}.createDelegate(this)
		});
	},
	height: 560,
	id: 'EvnReceptSearchWindow',
	initComponent: function() {
		var that = this;
		/*var btnAdd = new Object();
        var that = this;
		if (getRegionNick()=='perm') {
			btnAdd.tooltip = lang['retsept_po_obscheterapevticheskoy_gruppe_zabolevaniy'];
			btnAdd.handler = function(){ this.openEvnReceptEditWindow('add', 1); }.createDelegate(this);
		}
		else if (getRegionNick().inlist([ 'khak', 'krym', 'pskov', 'saratov' ])) {
			btnAdd.tooltip = lang['retsept_po_obscheterapevticheskoy_gruppe_zabolevaniy'];
			btnAdd.handler = function(){ this.openEvnReceptEditWindow('add', 2); }.createDelegate(this)
		}
        else if (getRegionNick()=='buryatiya'){
            btnAdd.tooltip = lang['retsept_po_osobyim_gruppam_zabolevaniy'];
            btnAdd.handler = function(){ this.openEvnReceptEditWindow('add', 2); }.createDelegate(this);
        }
		else {
			btnAdd.menu = new Ext.menu.Menu({
				id: 'EvnReceptAddMenu',
				items: [{
					text: lang['retsept_po_obscheterapevticheskoy_gruppe_zabolevaniy'],
					tooltip: lang['retsept_po_obscheterapevticheskoy_gruppe_zabolevaniy'],
					handler: function() {
						this.openEvnReceptEditWindow('add', 1);
					}.createDelegate(this)
				}, {
					text: lang['retsept_po_osobyim_gruppam_zabolevaniy'],
					tooltip: lang['retsept_po_osobyim_gruppam_zabolevaniy'],
					handler: function() {
						this.openEvnReceptEditWindow('add', 2);
					}.createDelegate(this)
				}]
			});
		}*/

		this.ReceptGridPanel = new sw.Promed.ViewFrame({
			useArchive: 1,
			actions: [
				/*{
					id: this.id + '_action_add',
					name: 'action_add',
					tooltip: btnAdd.tooltip,
					handler: btnAdd.handler,
					menu: btnAdd.menu
				},*/
				{ name: 'action_add', handler: function(){ this.openEvnReceptEditWindow('add'); }.createDelegate(this) },
				{ name: 'action_edit', disabled: true, handler: function(){ this.openEvnReceptEditWindow('view'); }.createDelegate(this) },
				{ name: 'action_view', handler: function(){ this.openEvnReceptEditWindow('view'); }.createDelegate(this) },
				{ name: 'action_delete', handler: function(){ this.deleteEvnRecept(); }.createDelegate(this), text: langs(getRegionNick() == 'msk' ? 'Аннулировать' : 'Удалить') },
				{
					name: 'action_print',
					menuConfig: {
						printObject: { handler: function(){ this.printEvnRecept(); }.createDelegate(this) },
						printObjectListFull: { handler: function(){ this.printEvnReceptList(); }.createDelegate(this) }
					}
				}
			],
			autoExpandMin: 100,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			paging: true,
			pageSize: 100,
			root: 'data',
			totalProperty: 'totalCount',
			border: false,
			id: 'ERS_EvnReceptSearchViewGrid',
			region: 'center',
			stringfields: [
				{name: 'EvnRecept_id', type: 'int', header: 'ID', key: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'ReceptRemoveCauseType_id', type: 'int', hidden: true},
				{name: 'MorbusType_id', type: 'int', hidden: true},
				{name: 'Person_Surname', type: 'string', header: lang['familiya']},
				{name: 'Person_Firname', type: 'string', header: lang['imya']},
				{name: 'Person_Secname', type: 'string', header: lang['otchestvo']},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['data_rojdeniya'], width: 70},
				{name: 'Person_deadDT', type: 'date', format: 'd.m.Y', header: lang['data_smerti'], width: 70},
				{name: 'EvnRecept_setDate', type: 'date', format: 'd.m.Y', header: lang['data'], width: 70},
				{name: 'ReceptType_Code', type: 'string', hidden: true},
                {name: 'ReceptForm_id', type: 'string', hidden: true},
                {name: 'ReceptForm_Code', type: 'string', header: langs('Форма рецепта'), width: 120},
				{name: 'Recept_MarkDeleted', type: 'checkbox', header: 'Помечен к удалению', width: 120, hidden: getRegionNick() == 'msk'},
				{name: 'EvnRecept_Ser', type: 'string', header: langs('Серия'), width: 70, hidden: getRegionNick().inlist(['kz'])},
				{name: 'EvnRecept_Num', type: 'string', header: langs('Номер'), width: 70},
				{name: 'ReceptType_Name', header: langs('Тип рецепта'), width: 120, renderer: function(v, p, r) {
					return r.get('ReceptType_Code') == '3' ? langs('ЭД') : r.get('ReceptType_Name'); //3 - Электронный документ
				}},
				{name: 'MedPersonal_Fio', type: 'string', header: langs('Врач'), width: 200},
				{name: 'Drug_Name', type: 'string', header: langs('Медикамент'), id: 'autoexpand'},
				{name: 'EvnRecept_Kolvo', type: 'string', header: langs('Количество'), width: 80, css: 'text-align: right;'},
				{name: 'Person_IsBDZ', type: 'checkbox', header: langs('БДЗ'), width: 30},
				{name: 'EvnRecept_IsSigned', type: 'checkbox', header: langs('Подписан'), width: 50},
				{name: 'EvnRecept_IsPrinted', type: 'checkbox', header: 'Распечатан', width: 50},
				{name: 'Drug_rlsid', type: 'int', hidden:true},
				{name: 'Drug_id', type: 'int', hidden:true},
				{name: 'DrugComplexMnn_id', type: 'int', hidden:true}
			],
			onRowSelect: function(sm, index, record) {
				var disabled = false;
				// Запретить удаление архивных записей
				if (getGlobalOptions().archive_database_enable) {
					disabled = disabled || (record.get('archiveRecord') == 1);
				}

				//disabled = disabled || record.get('ReceptRemoveCauseType_id');
                /*if (that.closeActions == true)
                    disabled = true;*/
				this.getAction('action_delete').setDisabled(disabled);

				if ( getRegionNick() != 'msk' ) {
					if(record.get('Recept_MarkDeleted')=='true'){
						this.getAction('action_undo_delete').setDisabled(false);
					}
					else {
						this.getAction('action_undo_delete').setDisabled(true);
					}
				}
				if (that.closeActions || getWnd('swWorkPlaceMZSpecWindow').isVisible()) {
					this.getAction('action_edit').setDisabled(true);
					this.getAction('action_delete').setDisabled(true);
					this.getAction('action_print').setDisabled(true);

					if ( getRegionNick() != 'msk' ) {
						this.getAction('action_undo_delete').setDisabled(true);
					}
				}
			}
		});

		this.ReceptGridPanel.getGrid().getView().getRowClass = function (row, index)
		{
			var cls = '';

			if(row.get('Person_deadDT')){
				cls = cls+'x-grid-rowgray ';
			}
			return cls;
		};

		this.ReceptGridPanel.getGrid().getView().addListener('rowupdated', function(view, first, record) {
			//log('update');
			view.getRowClass(record);
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				tabIndex: TABINDEX_EVNRECSF + 96,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset(true);
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EVNRECSF + 97,
				text: BTN_FRMRESET
			}, /*{
				handler: function() {
					this.printEvnReceptList();
					//this.findById('EvnReceptSearchForm').getForm().submit();
				}.createDelegate(this),
				iconCls: 'print16',
				tabIndex: TABINDEX_EVNRECSF + 98,
				text: lang['pechat_spiska']
			},*/ {
				handler: function() {
					this.findById('EvnReceptSearchForm').getRecordsCount();
				}.createDelegate(this),
				// iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EVNRECSF + 99,
				text: BTN_FRMCOUNT
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					this.buttons[5].focus();
				}.createDelegate(this),
				onTabAction: function () {
					this.findById('ERS_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('ERS_SearchFilterTabbar').getActiveTab());
				}.createDelegate(this),
				tabIndex: TABINDEX_EVNRECSF + 100,
				text: BTN_FRMCANCEL
			}],
			getFilterForm: function() {
				if ( this.filterForm == undefined ) {
					this.filterForm = this.findById('EvnReceptSearchForm');
				}
				return this.filterForm;
			},
			items: [ getBaseSearchFiltersFrame({
				useArchive: 1,
				allowPersonPeriodicSelect: true,
				id: 'EvnReceptSearchForm',
				ownerWindow: this,
				searchFormType: 'EvnRecept',
				tabIndexBase: TABINDEX_EVNRECSF,
				tabPanelId: 'ERS_SearchFilterTabbar',
				tabs: [{
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					layout: 'form',
					listeners: {
						'activate': function(panel) {
							if ( this.getFilterForm().getForm().findField('ER_MedPersonal_id').getStore().getCount() == 0 ) {
								this.getFilterForm().getForm().findField('ER_MedPersonal_id').getStore().load({
									callback: function(records, options, success) {
										if ( !success ) {
											sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_spravochnika_med_personala_retsept']);
											return false;
										}
									}
								});
							}
							
							this.getFilterForm().getForm().findField('EvnRecept_Ser').focus(250, true);
						}.createDelegate(this)
					},
					title: lang['6_retsept'],
					items: [{
						layout: 'column',
						border: false,
						items: [{
							layout: 'form',
							border: false,
							hidden: getRegionNick() == 'kz',
							items: [{
								fieldLabel: lang['seriya'],
								enableKeyEvents: true,
								listeners: {
									'keydown': function (inp, e) {
										if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
											e.stopEvent();
											// Переход к последней кнопке в окне
											this.buttons[this.buttons.length-1].focus();
										}
									}.createDelegate(this)
								},
								name: 'EvnRecept_Ser',
								tabIndex: TABINDEX_EVNRECSF + 56,
								width: 100,
								maskRe: /[^%]/,
								xtype: "textfield"
							}]
						}, {
							layout: 'form',
							border: false,
							labelWidth: (getRegionNick() == 'kz')?130:150,
							items: [{
								fieldLabel: langs('Номер'),
								name: 'EvnRecept_Num',
								tabIndex: TABINDEX_EVNRECSF + 57,
								width: 100,
								maskRe: /[^%]/,
								xtype: "textfield"
							}]
						}, {
							layout: 'form',
							border: false,
							labelWidth: 180,
							hidden: getRegionNick() == 'msk',
							items: [{
								fieldLabel: 'Помеченные на удаление',
								name: 'EvnRecept_MarkDeleted',
								width: 50,
								xtype: 'checkbox'
							}]
						}],
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: "Дата выписки",
								name: "EvnRecept_setDate",
								plugins: [
									new Ext.ux.InputTextMask('99.99.9999', false)
								],
								tabIndex: TABINDEX_EVNRECSF + 58,
								width: 100,
								xtype: "swdatefield"
							}, {
								disabled: true,
								fieldLabel: "Дата отпуска",
								name: "EvnRecept_otpDate",
								plugins: [
									new Ext.ux.InputTextMask('99.99.9999', false)
								],
								tabIndex: TABINDEX_EVNRECSF + 60,
								width: 100,
								xtype: "swdatefield"
							}]
						}, 
						{
							layout: 'form',
							border: false,
							labelWidth: 150,
							items: [{
								fieldLabel: "Диапазон дат выписки",
								name: "EvnRecept_setDate_Range",
								plugins: [
									new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
								],
								tabIndex: TABINDEX_EVNRECSF + 59,
								width: 170,
								xtype: "daterangefield"
							}, {
								disabled: true,
								fieldLabel: "Диапазон дат отпуска",
								name: "EvnRecept_otpDate_Range",
								plugins: [
									new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
								],
								tabIndex: TABINDEX_EVNRECSF + 61,
								width: 170,
								xtype: "daterangefield"
							}]
						}]
					}, {
						codeField: 'PrivilegeType_Code',
						displayField: 'PrivilegeType_Name',
						editable: false,
						fieldLabel: lang['kategoriya'],
						forceSelection : true,
						hiddenName: 'ER_PrivilegeType_id',
						listWidth: 250,
						store: new Ext.db.AdapterStore({
							autoLoad: true,
							dbFile: 'Promed.db',
							fields: [
								{ name: 'PrivilegeType_id', type: 'int'},
								{ name: 'PrivilegeType_Code', type: 'int'},
								{ name: 'PrivilegeType_Name', type: 'string'}
							],
							key: 'PrivilegeType_id',
							sortInfo: { field: 'PrivilegeType_Code' },
							tableName: 'PrivilegeType'
						}),
						tabIndex: TABINDEX_EVNRECSF + 62,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<font color="red">{PrivilegeType_Code}</font>&nbsp;{PrivilegeType_Name}',
							'</div></tpl>'
						),
						valueField: 'PrivilegeType_id',
						width: 220,
						xtype: 'swbaselocalcombo'
					}, {
						codeField: 'MedPersonal_Code',
						editable: false,
						displayField: 'MedPersonal_Fio',
						fieldLabel: lang['vrach'],
						hiddenName: 'ER_MedPersonal_id',
						hideTrigger: false,
						store: new Ext.data.Store({
							autoLoad: false,
							reader: new Ext.data.JsonReader({
								id: 'MedPersonal_id'
							}, [
								{ name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio' },
								{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
								{ name: 'MedPersonal_Code', mapping: 'MedPersonal_Code' }
							]),
							url: C_MP_DLO_LOADLIST
						}),
						tabIndex: TABINDEX_EVNRECSF + 63,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<table style="border: 0;"><td style="width: 70px"><font color="red">{MedPersonal_Code}</font></td><td><h3>{MedPersonal_Fio}</h3></td></tr></table>',
							'</div></tpl>'
						),
						triggerAction: 'all',
						valueField: 'MedPersonal_id',
						width: 520,
						xtype: 'swbaselocalcombo'
					}, {
						autoHeight: true,
						labelWidth: 125,
						style: 'margin: 0px 5px 0px 5px; padding: 0px;',
						title: lang['diagnoz'],
						width: 755,
						xtype: 'fieldset',
		
						items: [{
							fieldLabel: lang['kod_diagnoza_s'],
							hiddenName: 'ER_Diag_Code_From',
							listWidth: 620,
							tabIndex: TABINDEX_EVNRECSF + 64,
							valueField: 'Diag_Code',
							width: 620,
							xtype: 'swdiagcombo'
						}, {
							fieldLabel: lang['po'],
							hiddenName: 'ER_Diag_Code_To',
							listWidth: 620,
							tabIndex: TABINDEX_EVNRECSF + 65,
							valueField: 'Diag_Code',
							width: 620,
							xtype: 'swdiagcombo'
						}]
					}]
				}, {
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					layout: 'form',
					listeners: {
						'activate': function(panel) {
							
							this.getFilterForm().getForm().findField('Drug_id').getStore().baseParams.searchFull = 1;
							this.getFilterForm().getForm().findField('DrugMnn_id').getStore().baseParams.searchFull = 1;
						
							if ( this.getFilterForm().getForm().findField('OrgFarmacy_id').getStore().getCount() == 0 ) {
								this.getFilterForm().getForm().findField('OrgFarmacy_id').getStore().load({
									callback: function(records, options, success) {
										if ( !success ) {
											this.getFilterForm().getForm().findField('OrgFarmacy_id').getStore().removeAll();
											sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_spravochnika_aptek']);
											return false;
										}
									}
								});
							}

							this.getFilterForm().getForm().findField('ReceptType_id').focus(250, true);
						}.createDelegate(this)
					},
					title: lang['7_retsept_dop'],

					// tabIndexStart: this.tabindex_base + 68
					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [
                                {
                                    codeField: 'ReceptForm_Code',
                                    displayField: 'ReceptForm_Name',
                                    editable: false,
                                    fieldLabel: lang['forma_retsepta'],
                                    hiddenName: 'ReceptForm_id',
                                    lastQuery: '',
                                    store: new Ext.data.Store({
                                        autoLoad: true,
                                        reader: new Ext.data.JsonReader({
                                            id: 'ReceptForm_id'
                                        }, [
                                            { name: 'ReceptForm_id', mapping: 'ReceptForm_id', type: 'int', hidden: 'true'},
                                            { name: 'ReceptForm_Code', mapping: 'ReceptForm_Code'},
                                            { name: 'ReceptForm_Name', mapping: 'ReceptForm_Name' }
                                        ]),
                                        url: C_RECEPTFORM_GET_LIST
                                    }),

                                    tpl: new Ext.XTemplate(
                                        '<tpl for="."><div class="x-combo-list-item">',
                                        '<table style="border: 0;"><tr><td style="width: 25px;"><font color="red">{ReceptForm_Code}</font></td><td style="font-weight: normal;">{ReceptForm_Name}</td></tr></table>',
                                        '</div></tpl>'
                                    ),
                                    validateOnBlur: true,
                                    valueField: 'ReceptForm_id',
                                    width: 200,
                                    xtype: 'swbaselocalcombo'
                                },
                             {
								comboSubject: 'ReceptType',
								fieldLabel: lang['tip_retsepta'],
								hiddenName: 'ReceptType_id',
								lastQuery: '',
								listeners: {
									'keydown': function (inp, e) {
										if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
											e.stopEvent();
											// Переход к последней кнопке в окне
											this.buttons[this.buttons.length-1].focus();
										}
									}.createDelegate(this)
								},
								tabIndex: TABINDEX_EVNRECSF + 68,
								validateOnBlur: true,
								width: 200,
								xtype: 'swcommonsprcombo'
							}, {
								autoLoad: false,
								comboSubject: 'ReceptFinance',
								fieldLabel: lang['finansirovanie'],
								hiddenName: 'ReceptFinance_id',
								lastQuery: '',
								listeners: {
									'render': function(combo) {
										combo.getStore().load({
											params: {
												where: 'where ReceptFinance_Code in (1, 2)'
											}
										});
									}.createDelegate(this)
								},
								listWidth: 200,
								tabIndex: TABINDEX_EVNRECSF + 70,
								validateOnBlur: true,
								width: 200,
								xtype: 'swcommonsprcombo'
							}, {
								xtype: 'swwhsdocumentcostitemtypecombo',
								hidden : getRegionNick() == 'perm',
								hideLabel: getRegionNick() == 'perm',
								fieldLabel: 'Статья расхода',
								name: 'WhsDocumentCostItemType_id',
								width: 370
							},	{
								comboSubject: 'YesNo',
								fieldLabel: lang['7_nozologiy'],
								hideLabel: !getRegionNick().inlist(['perm','ufa']),
								hidden: !getRegionNick().inlist(['perm','ufa']),
								hiddenName: 'EvnRecept_Is7Noz',
								lastQuery: '',
								tabIndex: TABINDEX_EVNRECSF + 72,
								validateOnBlur: true,
								width: 100,
								xtype: 'swcommonsprcombo'
							}]
						}, {
							layout: 'form',
							border: false,
							labelWidth: 150,
							items: [{
								comboSubject: 'ReceptValid',
								fieldLabel: lang['srok_deystviya'],
								hiddenName: 'ReceptValid_id',
								lastQuery: '',
								listWidth: 100,
								tabIndex: TABINDEX_EVNRECSF + 69,
								validateOnBlur: true,
								width: 100,
								xtype: 'swcommonsprcombo'
							}, {
								comboSubject: 'ReceptDiscount',
								fieldLabel: lang['skidka'],
								hiddenName: 'ReceptDiscount_id',
								lastQuery: '',
								listWidth: 100,
								tabIndex: TABINDEX_EVNRECSF + 71,
								validateOnBlur: true,
								width: 100,
								xtype: 'swcommonsprcombo'
							},
							{
								fieldLabel: 'Подписан ЭП',
								hiddenName: 'EvnRecept_IsSigned',
								width: 100,
								xtype: 'swyesnocombo'
							}
							]
							}]
					}, {
						autoHeight: true,
						style: 'padding: 0px;',
						title: lang['medikament'],
						width: 755,
						xtype: 'fieldset',

						items: [{
							allowBlank: true,
							emptyText: lang['nachnite_vvodit_mnn'],
							lastQuery: '',
							listeners: {
								'keydown': function(inp, e) {
									if ( e.getKey() == Ext.EventObject.DELETE ) {
										e.stopEvent();
		
										if ( e.browserEvent.stopPropagation ) {
											e.browserEvent.stopPropagation();
										}
										else {
											e.browserEvent.cancelBubble = true;
										}

										if ( e.browserEvent.preventDefault ) {
											e.browserEvent.preventDefault();
										}
										else {
											e.browserEvent.returnValue = false;
										}

										e.browserEvent.returnValue = false;
										e.returnValue = false;

										if ( Ext.isIE ) {
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}

										switch ( e.getKey() ) {
											case Ext.EventObject.DELETE:
												inp.setValue('');
												inp.setRawValue('');
											break;
										}
									}
								},
								'beforeselect': function(combo) {
									combo.lastQuery = '';
								},
								'change': function(combo, newValue, oldValue) {
									var drug_combo = this.getFilterForm().getForm().findField('Drug_id');

									drug_combo.clearValue();
									drug_combo.getStore().removeAll();
									drug_combo.lastQuery = '';
									drug_combo.getStore().baseParams.DrugMnn_id = newValue;

									if ( newValue > 0 ) {
										drug_combo.getStore().load();
									}
								}.createDelegate(this),
								'blur': function(combo) {
									if ( combo.getRawValue() == '' ) {
										combo.setValue('');
									}
									else {
										return false;
									}
								}
							},
							listWidth: 800,
							minLengthText: lang['pole_doljno_byit_zapolneno'],
							onTrigger2Click: Ext.emptyFn,
							plugins: [ new Ext.ux.translit(true) ],
							queryDelay: 250,
							tabIndex: TABINDEX_EVNRECSF + 73,
							trigger2Class: 'hideTrigger',
							validateOnBlur: false,
							width: 500,
							xtype: 'swdrugmnncombo'
						}, {
							allowBlank: true,
							listeners: {
								'beforeselect': function(combo, record, index) {
									combo.setValue(record.get('Drug_id'));

									var drug_mnn_combo = this.getFilterForm().getForm().findField('DrugMnn_id');
									var drug_mnn_record = drug_mnn_combo.getStore().getById(record.get('DrugMnn_id'));

									if ( drug_mnn_record ) {
										drug_mnn_combo.setValue(record.get('DrugMnn_id'));
									}
									else {
										if ( combo.getRawValue() != '' ) {
											drug_mnn_combo.getStore().load({
												callback: function() {
													drug_mnn_combo.setValue(record.get('DrugMnn_id'));
												},
												params: {
													DrugMnn_id: record.get('DrugMnn_id')
												}
											})
										}
									}
								}.createDelegate(this),
								'keydown': function(inp, e) {
									if ( e.getKey() == Ext.EventObject.DELETE ) {
										e.stopEvent();

										if ( e.browserEvent.stopPropagation ) {
											e.browserEvent.stopPropagation();
										}
										else {
											e.browserEvent.cancelBubble = true;
										}

										if ( e.browserEvent.preventDefault ) {
											e.browserEvent.preventDefault();
										}
										else {
											e.browserEvent.returnValue = false;
										}

										e.browserEvent.returnValue = false;
										e.returnValue = false;

										if ( Ext.isIE ) {
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}

										switch ( e.getKey() ) {
											case Ext.EventObject.DELETE:
												inp.setValue('');
												inp.setRawValue('');
											break;
										}
									}
								}
							},
							listWidth: 800,
							loadingText: lang['idet_poisk'],
							minLengthText: lang['pole_doljno_byit_zapolneno'],
							onTrigger2Click: Ext.emptyFn,
							tabIndex: TABINDEX_EVNRECSF + 74,
							trigger2Class: 'hideTrigger',
							validateOnBlur: false,
							width: 500,
							xtype: 'swdrugcombo'
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
                                labelWidth: 190,
								layout: 'form',
								items: [ new sw.Promed.SwYesNoCombo({
									fieldLabel: langs('Выписка по решению ВК'),
									hiddenName: 'EvnRecept_IsKEK',
									tabIndex: TABINDEX_EVNRECSF + 75,
									width: 100,
                                    listeners: {
                                        'select': function(combo, newValue, oldValue) {
                                            combo.setVKProtocolFieldsVisible();
                                        }
                                    },
                                    clearValue: function() {
                                        sw.Promed.SwYesNoCombo.superclass.clearValue.apply(this, arguments);
                                        this.setVKProtocolFieldsVisible();
                                    },
                                    setVKProtocolFieldsVisible: function() {
                                        var base_form = that.getFilterForm().getForm();
                                        var vk_combo = base_form.findField('EvnRecept_IsKEK');
                                        var num_field = base_form.findField('EvnRecept_VKProtocolNum');
                                        var date_field = base_form.findField('EvnRecept_VKProtocolDT');
                                        var is_vk = (vk_combo.getValue() == 2);
                                        var is_visible = (getRegionNick() == 'msk' && is_vk);

                                        if (is_visible) {
                                            num_field.ownerCt.show();
                                            date_field.ownerCt.show();
                                        } else {
                                            num_field.ownerCt.hide();
                                            date_field.ownerCt.hide();
                                            num_field.setValue(null);
                                            date_field.setValue(null);
                                        }
                                        that.doLayout();
                                    }
								})]
							}, {
                                layout: 'form',
                                autoHeight: true,
                                border: false,
                                labelWidth: 130,
                                items: [{
                                    fieldLabel: 'Номер протокола ВК',
                                    name: 'EvnRecept_VKProtocolNum',
                                    width: 80,
                                    xtype: 'textfield',
                                    tabIndex: TABINDEX_EVNRECSF + 75,
                                }]
                            }, {
                                layout: 'form',
                                autoHeight: true,
                                border: false,
                                labelWidth: 130,
                                width: 235,
                                items: [{
                                    fieldLabel: 'Дата протокола ВК',
                                    name: 'EvnRecept_VKProtocolDT',
                                    xtype: 'swdatefield',
                                    format: 'd.m.Y',
                                    plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                                    validateOnBlur: true,
                                    tabIndex: TABINDEX_EVNRECSF + 75,
                                }]
                            }]
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								labelWidth: 190,
								layout: 'form',
								items: [ new sw.Promed.SwYesNoCombo({
									fieldLabel: lang['vyipiska_bez_nalichiya_v_apteke'],
									hiddenName: 'EvnRecept_IsNotOstat',
									tabIndex: TABINDEX_EVNRECSF + 76,
									width: 100
								})]
							}]
						}, {
							labelWidth: 500,
							name: 'OrgFarmacy_id',
							tabIndex: TABINDEX_EVNRECSF + 77,
							width: 483,
							xtype: 'sworgfarmacycombo'
						}]
					}, {
						fieldLabel: lang['ekstemporalnyiy'],
						hiddenName: 'EvnRecept_IsExtemp',
						tabIndex: TABINDEX_EVNRECSF + 78,
						width: 100,
						xtype: 'swyesnocombo'
					}]
				}]
			}),
			this.ReceptGridPanel
			/*this.ReceptGridPanel = new Ext.grid.GridPanel({
				autoExpandColumn: 'autoexpand_recept',
				autoExpandMin: 100,
				bbar: new Ext.PagingToolbar({
					displayInfo: true,
					displayMsg: lang['otobrajaemyie_stroki_{0}_-_{1}_iz_{2}'],
					emptyMsg: "Нет записей для отображения",
					pageSize: 100,
					store: evnReceptSearchGridStore
				}),
				border: false,
				columns: [{
					dataIndex: "Person_Surname",
					header: +lang['familiya']+,
					hidden: false,
					sortable: true
				}, {
					dataIndex: "Person_Firname",
					header: +lang['imya']+,
					hidden: false,
					sortable: true
				}, {
					dataIndex: "Person_Secname",
					header: +lang['otchestvo']+,
					hidden: false,
					sortable: true
				}, {
					dataIndex: "Person_Birthday",
					header: "Дата рождения",
					hidden: false,
					renderer: Ext.util.Format.dateRenderer('d.m.Y'),
					sortable: true,
					width: 70
				}, {
					dataIndex: "Person_deadDT",
					header: "Дата смерти",
					hidden: false,
					renderer: Ext.util.Format.dateRenderer('d.m.Y'),
					sortable: true,
					width: 70
				}, {
					dataIndex: "EvnRecept_setDate",
					header: +lang['data']+,
					hidden: false,
					renderer: Ext.util.Format.dateRenderer('d.m.Y'),
					sortable: true,
					width: 70
				}, {
					dataIndex: "EvnRecept_Ser",
					header: +lang['seriya']+,
					hidden: false,
					sortable: true,
					width: 70
				}, {
					dataIndex: "EvnRecept_Num",
					header: +lang['nomer']+,
					hidden: false,
					sortable: true,
					width: 70
				}, {
					dataIndex: "MedPersonal_Fio",
					header: +lang['vrach']+,
					hidden: false,
					sortable: true,
					width: 200
				}, {
					dataIndex: "Drug_Name",
					header: +lang['medikament']+,
					hidden : false,
					id: 'autoexpand_recept',
					sortable: true,
					width: 150
				}, {
					css: 'text-align: right;',
					dataIndex: "EvnRecept_Kolvo",
					header: +lang['kolichestvo']+,
					hidden: false,
					sortable: true,
					width: 80
				}, {
					dataIndex: 'Person_IsBDZ',
					header: lang['bdz'],
					hidden: false,
					renderer: sw.Promed.Format.checkColumn,
					sortable: true,
					width: 30
				}, {
					dataIndex: 'EvnRecept_IsSigned',
					header: lang['podpisan'],
					hidden: false,
					renderer: sw.Promed.Format.checkColumn,
					sortable: true,
					width: 50
				}],
				id: 'ERS_EvnReceptSearchViewGrid',
				keys: [{
					key: [
						Ext.EventObject.DELETE,
						Ext.EventObject.END,
						Ext.EventObject.ENTER,
						Ext.EventObject.F3,
						Ext.EventObject.F5,
						Ext.EventObject.F6,
						Ext.EventObject.F10,
						Ext.EventObject.F11,
						Ext.EventObject.F12,
						Ext.EventObject.HOME,
						Ext.EventObject.INSERT,
						Ext.EventObject.PAGE_DOWN,
						Ext.EventObject.PAGE_UP,
						Ext.EventObject.TAB
					],
					fn: function(inp, e) {
						e.stopEvent();

						if ( e.browserEvent.stopPropagation )
							e.browserEvent.stopPropagation();
						else
							e.browserEvent.cancelBubble = true;

						if ( e.browserEvent.preventDefault )
							e.browserEvent.preventDefault();

						e.browserEvent.returnValue = false;
						e.returnValue = false;

						if ( Ext.isIE ) {
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}

						var grid = Ext.getCmp('ERS_EvnReceptSearchViewGrid');
						var params = new Object();
						var selected_record = grid.getSelectionModel().getSelected();

						if ( selected_record ) {
							params.onHide = function() {
								var index = grid.getStore().indexOf(selected_record);

								grid.focus();
								grid.getView().focusRow(index);
								grid.getSelectionModel().selectRow(index);
							};
							params.Person_Birthday = selected_record.get('Person_Birthday');
							params.Person_Firname = selected_record.get('Person_Firname');
							params.Person_id = selected_record.get('Person_id');
							params.Person_Secname = selected_record.get('Person_Secname');
							params.Person_Surname = selected_record.get('Person_Surname');
							params.Server_id = selected_record.get('Server_id');
						}

						switch ( e.getKey() ) {
							case Ext.EventObject.DELETE:
								grid.ownerCt.deleteEvnRecept();
							break;

							case Ext.EventObject.END:
								GridEnd(grid);
							break;

							case Ext.EventObject.ENTER:
							case Ext.EventObject.F3:
							case Ext.EventObject.F4:
								if ( !selected_record ) {
									return false;
								}
								if ( !e.altKey ) {
									grid.ownerCt.openEvnReceptEditWindow('view');
								} else {
									params['key_id'] = selected_record.get('EvnRecept_id');
									params['key_field'] = 'EvnRecept_id';
									if (!Ext.isEmpty(params['key_id'])) {
										getWnd('swAuditWindow').show(params);
									}
								}
							break;

							case Ext.EventObject.F5:
								grid.getStore().reload();
							break;

							case Ext.EventObject.F6:
								if ( !selected_record ) {
									return false;
								}

								getWnd('swPersonCardHistoryWindow').show(params);
							break;

							case Ext.EventObject.F10:
								if ( !selected_record ) {
									return false;
								}

								getWnd('swPersonEditWindow').show({
									action: 'edit',
									onClose: function() {
										var index = grid.getStore().indexOf(selected_record);

										grid.focus();
										grid.getView().focusRow(index);
										grid.getSelectionModel().selectRow(index);
									},
									Person_id: selected_record.get('Person_id'),
									Server_id: selected_record.get('Server_id')
								});
							break;

							case Ext.EventObject.F11:
								if ( !selected_record ) {
									return false;
								}

								getWnd('swPersonCureHistoryWindow').show(params);
							break;

							case Ext.EventObject.F12:
								if ( !selected_record ) {
									return false;
								}

								if (e.ctrlKey == true) {
									getWnd('swPersonDispHistoryWindow').show(params);
								}
								else {
									getWnd('swPersonPrivilegeViewWindow').show(params);
								}
							break;

							case Ext.EventObject.HOME:
								GridHome(grid);
							break;

							case Ext.EventObject.INSERT:
								grid.ownerCt.openEvnReceptEditWindow('add', (e.shiftKey == true ? 2 : 1));
								break;

							case Ext.EventObject.PAGE_DOWN:
								GridPageDown(grid);
							break;

							case Ext.EventObject.PAGE_UP:
								GridPageUp(grid);
							break;

							case Ext.EventObject.TAB:
								Ext.getCmp('EvnReceptSearchWindow').buttons[0].focus(false, 100);
							break;
						}
					},
					stopEvent: true
				}],
				listeners: {
					'rowdblclick': function(grid, number, obj) {
						grid.ownerCt.openEvnReceptEditWindow('view');
					}
				},
				region: 'center',
				sm: new Ext.grid.RowSelectionModel({
					listeners: {
						'rowselect': function(sm, rowIndex, record) {
							var evn_recept_id = sm.getSelected().get('EvnRecept_id');

							this.grid.getTopToolbar().items.items[11].el.innerHTML = String(rowIndex + 1) + ' / ' + this.grid.getStore().getCount();

							this.grid.getTopToolbar().items.items[4].disable();
							this.grid.getTopToolbar().items.items[5].disable();

							if ( evn_recept_id ) {
								this.grid.getTopToolbar().items.items[4].enable();

								if ( !sm.getSelected().get('ReceptRemoveCauseType_id') ) {
									this.grid.getTopToolbar().items.items[5].enable();
								}
							}
						}
					}
				}),
				store: evnReceptSearchGridStore,
				stripeRows: true,
				// tabIndex: TABINDEX_EVNRECSF + 12,
				tbar: new sw.Promed.Toolbar({
					// Решение для Перми - пока показываем кнопку "Добавить"
					// https://redmine.swan.perm.ru/issues/23416
					buttons: [{
						hidden: !(getGlobalOptions().region && getGlobalOptions().region.nick=='perm'),
						iconCls: 'add16',
						text: BTN_GRIDADD,
						tooltip: lang['retsept_po_obscheterapevticheskoy_gruppe_zabolevaniy'],
						handler: function() {
							this.openEvnReceptEditWindow('add', 1);
						}.createDelegate(this)
					}, {
						iconCls: 'add16',
						hidden: (getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'khak', 'perm', 'pskov', 'saratov' ])),
						menu: new Ext.menu.Menu({
							id: 'EvnReceptAddMenu',
							items: [{
								text: lang['retsept_po_obscheterapevticheskoy_gruppe_zabolevaniy'],
								tooltip: lang['retsept_po_obscheterapevticheskoy_gruppe_zabolevaniy'],
								handler: function() {
									this.openEvnReceptEditWindow('add', 1);
								}.createDelegate(this)
							}, {
								text: lang['retsept_po_osobyim_gruppam_zabolevaniy'],
								tooltip: lang['retsept_po_osobyim_gruppam_zabolevaniy'],
								handler: function() {
									this.openEvnReceptEditWindow('add', 2);
								}.createDelegate(this)
							}]
						}),
						text: BTN_GRIDADD
					}, {
						hidden: !getGlobalOptions().region.nick.inlist([ 'khak', 'pskov', 'saratov' ]),
						handler: function() {
							this.openEvnReceptEditWindow('add', 2);
						}.createDelegate(this),
						iconCls: 'add16',
						text: BTN_GRIDADD
					}, {
						disabled: true,
						handler: function() {
							this.openEvnReceptEditWindow('view');
						}.createDelegate(this),
						iconCls: 'edit16',
						text: BTN_GRIDEDIT
					}, {
						handler: function() {
							this.openEvnReceptEditWindow('view');
						}.createDelegate(this),
						iconCls: 'view16',
						text: BTN_GRIDVIEW
					}, {
						handler: function() {
							this.deleteEvnRecept();
						}.createDelegate(this),
						iconCls: 'delete16',
						text: BTN_GRIDDEL
					}, {
						xtype: 'tbseparator'
					}, {
						handler: function() {
							this.findById('ERS_EvnReceptSearchViewGrid').getStore().reload();
						}.createDelegate(this),
						iconCls: 'refresh16',
						text: BTN_GRIDREFR
					}, {
						xtype: 'tbseparator'
					}, {
						handler: function() {
							this.printEvnRecept();
						}.createDelegate(this),
						iconCls: 'print16',
						text: BTN_GRIDPRINT
					}, {
						xtype: 'tbfill'
					}, {
						text: '0 / 0',
						xtype: 'tbtext'
					}]
				}),
				view: new Ext.grid.GridView({
					getRowClass: function (row, index) {
						var cls = '';

						if ( parseInt(row.get('ReceptRemoveCauseType_id')) > 0 ) {
							cls = cls + 'x-grid-rowgray';
						}
						else {
							cls = 'x-grid-panel';
						}

						return cls;
					}
				})
			})*/]
		});
		sw.Promed.swEvnReceptSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		key: [ Ext.EventObject.INSERT ],
		fn: function(inp, e) {
            if (Ext.getCmp('ERS_EvnReceptSearchViewGrid').getAction('action_add').isDisabled()) {
                return false;
            }

			Ext.getCmp('EvnReceptSearchWindow').openEvnReceptEditWindow('add', (e.shiftKey == true ? 2 : 1));
		},
		stopEvent: true
	}, {
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnReceptSearchWindow');
			var form = current_window.findById('EvnReceptSearchForm');
			var search_filter_tabbar = current_window.findById('ERS_SearchFilterTabbar');

			switch ( e.getKey() ) {
				case Ext.EventObject.A:
					if ( !getRegionNick().inlist([ 'kz' ]) ) {
						var register_combo = form.getForm().findField('RegisterSelector_id');
						var register_value = register_combo.getValue();

						if ( register_value != 1 ) {
							// current_window.doReset(false);
							register_combo.setValue(1);
							register_combo.fireEvent('change', register_combo, 1, null);
						}
					}
				break;

				case Ext.EventObject.C:
					current_window.doReset(true);
				break;

				case Ext.EventObject.H:
					if ( !getRegionNick().inlist([ 'kz' ]) ) {
						var register_combo = form.getForm().findField('RegisterSelector_id');
						var register_value = register_combo.getValue();

						if ( register_value != 2 ) {
							// current_window.doReset(false);
							register_combo.setValue(2);
							register_combo.fireEvent('change', register_combo, 2, null);
						}
					}
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;

				case Ext.EventObject.NUM_ONE:
				case Ext.EventObject.ONE:
					search_filter_tabbar.setActiveTab(0);
				break;

				case Ext.EventObject.NUM_TWO:
				case Ext.EventObject.TWO:
					search_filter_tabbar.setActiveTab(1);
				break;

				case Ext.EventObject.NUM_THREE:
				case Ext.EventObject.THREE:
					search_filter_tabbar.setActiveTab(2);
				break;

				case Ext.EventObject.NUM_FOUR:
				case Ext.EventObject.FOUR:
					search_filter_tabbar.setActiveTab(3);
				break;

				case Ext.EventObject.NUM_FIVE:
				case Ext.EventObject.FIVE:
					search_filter_tabbar.setActiveTab(4);
				break;

				case Ext.EventObject.NUM_SIX:
				case Ext.EventObject.SIX:
					search_filter_tabbar.setActiveTab(5);
				break;

				case Ext.EventObject.NUM_SEVEN:
				case Ext.EventObject.SEVEN:
					search_filter_tabbar.setActiveTab(6);
				break;

				case Ext.EventObject.NUM_EIGHT:
				case Ext.EventObject.EIGHT:
					search_filter_tabbar.setActiveTab(7);
				break;
			}
		},
		key: [
			Ext.EventObject.A,
			Ext.EventObject.C,
			Ext.EventObject.EIGHT,
			Ext.EventObject.FIVE,
			Ext.EventObject.FOUR,
			Ext.EventObject.H,
			Ext.EventObject.J,
			Ext.EventObject.NUM_EIGHT,
			Ext.EventObject.NUM_FIVE,
			Ext.EventObject.NUM_FOUR,
			Ext.EventObject.NUM_ONE,
			Ext.EventObject.NUM_SEVEN,
			Ext.EventObject.NUM_SIX,
			Ext.EventObject.NUM_TWO,
			Ext.EventObject.NUM_THREE,
			Ext.EventObject.ONE,
			Ext.EventObject.SEVEN,
			Ext.EventObject.SIX,
			Ext.EventObject.TWO,
			Ext.EventObject.THREE
		],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.doReset(true);
		},
		'maximize': function(win) {
			win.findById('EvnReceptSearchForm').doLayout();
		},
		'restore': function(win) {
			win.findById('EvnReceptSearchForm').doLayout();
		},
                'resize': function (win, nW, nH, oW, oH) {
//                    log(nW);
                    win.findById('ERS_SearchFilterTabbar').setWidth(nW - 5);
                    win.findById('EvnReceptSearchForm').setWidth(nW - 5);
                }
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	openEvnReceptEditWindow: function(action, MorbusType_id) {
		if ( action != 'add' && action != 'view' ) {
			return false;
		}
		else if ( getRegionNick() == 'perm' && MorbusType_id == 2 ) {
			return false;
		}

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		var current_window = this;
		var grid = current_window.findById('ERS_EvnReceptSearchViewGrid').getGrid();
		var params = new Object();
		var wnd;

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.EvnReceptData ) {
				grid.getStore().reload();
			}
			else {
				setGridRecord(grid, data.EvnReceptData);
			}
		};

		if ( action == 'add' ) {
			/*if ( MorbusType_id == 2 || getRegionNick().inlist([ 'khak', 'krym', 'pskov', 'saratov' ]) ) {
				wnd = 'swEvnReceptRlsEditWindow';
			}
			else {
				wnd = 'swEvnReceptEditWindow';
			}*/

			if(getGlobalOptions().drug_spr_using == 'dbo')
				wnd = 'swEvnReceptEditWindow';
			else
				wnd = 'swEvnReceptRlsEditWindow';

			if ( getWnd(wnd).isVisible() ) {
				sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_retsepta_uje_otkryito']);
				return false;
			}

			getWnd('swPersonSearchWindow').show({
				onClose: Ext.emptyFn,
				onSelect: function(person_data) {
					params.onHide = function() {
						// TODO: Продумать использование getWnd в таких случаях
						getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 250);
					};
					params.Person_id =  person_data.Person_id;
					params.PersonEvn_id = person_data.PersonEvn_id;
					params.Server_id = person_data.Server_id;

					getWnd(wnd).show( params );
				},
				personFirname: current_window.findById('EvnReceptSearchForm').getForm().findField('Person_Firname').getValue(),
				personSecname: current_window.findById('EvnReceptSearchForm').getForm().findField('Person_Secname').getValue(),
				personSurname: current_window.findById('EvnReceptSearchForm').getForm().findField('Person_Surname').getValue(),
				searchMode: 'all'
			});
		}
		else {
			if ( !grid.getSelectionModel().getSelected() ) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			if (!Ext.isEmpty(selected_record.get('Drug_id'))) {
				wnd = 'swEvnReceptEditWindow'; // для Перми
			} else if (!Ext.isEmpty(selected_record.get('Drug_rlsid')) || !Ext.isEmpty(selected_record.get('DrugComplexMnn_id'))) {
				wnd = 'swEvnReceptRlsEditWindow'; // для Уфы
			} else {
				sw.swMsg.alert("Ошибка", "Не выбран медикамент в рецепте"); // так не может быть
				return false;
			}

			if ( getWnd(wnd).isVisible() ) {
				sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_retsepta_uje_otkryito']);
				return false;
			}

			var evn_recept_id = selected_record.get('EvnRecept_id');
			var person_id = selected_record.get('Person_id');
			var person_evn_id = selected_record.get('PersonEvn_id');
			var server_id = selected_record.get('Server_id');

			if ( evn_recept_id && person_id && person_evn_id && server_id >= 0 ) {
				if (getGlobalOptions().archive_database_enable) {
					params.archiveRecord = selected_record.get('archiveRecord');
				}

				params.EvnRecept_id = evn_recept_id;
				params.onHide = function() {
					grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				};
				params.Person_id = person_id;
				params.PersonEvn_id = person_evn_id;
				params.Server_id = server_id;
				params.viewOnly = ((action=='view' && !this.hasPolka) || getWnd('swWorkPlaceMZSpecWindow').isVisible());

				getWnd(wnd).show( params );
			}
		}
	},
	plain: true,
	printEvnRecept: function() {
        var region_nick = getRegionNick();
		var grid = this.findById('ERS_EvnReceptSearchViewGrid').getGrid();
		var record = grid.getSelectionModel().getSelected();

		if ( !record || !record.get('EvnRecept_id') ) {
			return false;
		}

		if(record.get('ReceptType_Code') == 1){
			Ext.Msg.alert(lang['oshibka'], 'Для рецептов, выписанных на бланке, печатная форма не предусмотрена');
			return false;
		}
		if(record.get('Recept_MarkDeleted')=='true' && region_nick != 'msk'){
			Ext.Msg.alert(lang['oshibka'], 'Рецепт удален и не может быть распечатан');
			return false;
		}
		if (getRegionNick() != 'kz' && record.get('ReceptType_Code') == 3 && !record.get('EvnRecept_IsSigned')) {
			Ext.Msg.alert(langs('Ошибка'), 'Рецепт в форме электронного документа можно распечатать после подписания рецепта ЭП. Подпишите рецепт и повторите печать.');
			return false;
		}
        var ReceptForm_id = record.get('ReceptForm_id')*1;
        var ReceptForm_Code = record.get('ReceptForm_Code');
		var evn_recept_set_date = record.get('EvnRecept_setDate').format('Y-m-d');
		var evn_recept_id = record.get('EvnRecept_id');
		var that = this;
		saveEvnReceptIsPrinted({
			allowQuestion: false
			,callback: function(success) {
				if ( success == true ) {
					record.set('EvnRecept_IsPrinted', 'true');
					record.commit();
					if (Ext.globalOptions.recepts.print_extension == 3) {
						if(ReceptForm_Code != lang['1-mi'])
							window.open(C_EVNREC_PRINT_DS, '_blank');
						window.open(C_EVNREC_PRINT + '&EvnRecept_id=' + evn_recept_id, '_blank');
					} else {
						Ext.Ajax.request({
							url: '/?c=EvnRecept&m=getPrintType',
							callback: function(options, success, response) {
								if (success) {
									var result = Ext.util.JSON.decode(response.responseText);
									var PrintType = '';
									switch(result.PrintType) {
									case '1':
										PrintType = 2;
										break;
									case '2':
										PrintType = 3;
										break;
									case '3':
										PrintType = '';
										break;
									}

                                    switch (ReceptForm_id) {
                                        case 2: //1-МИ
										if(result.CopiesCount == 1){
											printBirt({
												'Report_FileName': 'EvnReceptPrint4_1MI.rptdesign',
												'Report_Params': '&paramEvnRecept=' + evn_recept_id,
												'Report_Format': 'pdf'
											});
                                            } else {
											if(PrintType=='') {
												printBirt({
													'Report_FileName': 'EvnReceptPrint1_1MI.rptdesign',
													'Report_Params': '&paramEvnRecept=' + evn_recept_id,
													'Report_Format': 'pdf'
												});
											} else {
												printBirt({
													'Report_FileName': 'EvnReceptPrint' + PrintType + '_1MI.rptdesign',
													'Report_Params': '&paramEvnRecept=' + evn_recept_id,
													'Report_Format': 'pdf'
												});
											}
										}
                                            break;
                                        case 9: //148-1/у-04(л)
                                            if (region_nick == 'msk') {
                                                printBirt({
                                                    'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2020.rptdesign',
                                                    'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                    'Report_Format': 'pdf'
                                                });
                                            } else {
                                                //игнорируем настройки и печатаем сразу обе стороны
                                                printBirt({
                                                    'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2019.rptdesign',
                                                    'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                    'Report_Format': 'pdf'
                                                });
                                            }
                                            printBirt({
                                                'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2019Oborot.rptdesign',
                                                'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                'Report_Format': 'pdf'
                                            });
                                            break;
                                        case 1: //148-1/у-04(л), 148-1/у-06(л)
                                            if (region_nick == 'msk') {
                                                printBirt({
                                                    'Report_FileName': 'EvnReceptPrint_148_1u04_4InA4_2019.rptdesign',
                                                    'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                    'Report_Format': 'pdf'
                                                });
                                                break; //в пределах условия для того, чтобы в других регионах выполнение проваливалось в дефолтную секцию
                                            }
                                        default:
										var ReportName = 'EvnReceptPrint' + PrintType;
										var ReportNameOb = 'EvnReceptPrintOb' + PrintType;
                                            if(result.CopiesCount == 1) {
                                                if(evn_recept_set_date >= '2016-07-30') {
												ReportName = 'EvnReceptPrint4_2016_new';
                                                } else if(evn_recept_set_date >= '2016-01-01') {
												ReportName = 'EvnReceptPrint4_2016';
                                                } else {
												ReportName = 'EvnReceptPrint2_2015';
											}
											ReportNameOb = 'EvnReceptPrintOb2_2015';
                                            } else {
                                                if (evn_recept_set_date >= '2016-07-30') {
												ReportName = ReportName + '_2016_new';
												} else if(evn_recept_set_date >= '2016-01-01') {
												ReportName = ReportName + '_2016';
										}
                                            }
										if (Ext.globalOptions.recepts.print_extension == 1) {
											printBirt({
												'Report_FileName': ReportNameOb + '.rptdesign',
												'Report_Params': '&paramEvnRecept=' + evn_recept_id + '&paramProMedPort=' + result.server_port + '&paramProMedProto=' + result.server_http,
												'Report_Format': 'pdf'
											});
										}
										if(result.server_port != null) {
											printBirt({
												'Report_FileName': ReportName + '.rptdesign',
												'Report_Params': '&paramEvnRecept=' + evn_recept_id + '&paramProMedPort=' + result.server_port + '&paramProMedProto=' + result.server_http,
												'Report_Format': 'pdf'
											});
										} else {
											printBirt({
												'Report_FileName': ReportName + '.rptdesign',
												'Report_Params': '&paramEvnRecept=' + evn_recept_id + '&paramProMedProto=' + result.server_http,
												'Report_Format': 'pdf'
											});
										}
                                            break;
									}
								}
							}.createDelegate(that)
						});
					}

				}
				else {
					sw.swMsg.alert('Ошибка', 'Ошибка при сохранении признака распечатывания рецепта');
				}
			}.createDelegate(this)
			,Evn_id: evn_recept_id
		});
	},
	printEvnReceptList: function() {
		//Ext.ux.GridPrinter.print(this.findById('ERS_EvnReceptSearchViewGrid'), { tableHeaderText: 'Список рецептов', pageTitle: 'Печать списка рецептов' });
		var base_form = this.findById('EvnReceptSearchForm').getForm();

		var baseParams = this.findById('ERS_EvnReceptSearchViewGrid').getGrid().getStore().baseParams;

		base_form.submit();
	},
	resizable: true,
	show: function() {
		sw.Promed.swEvnReceptSearchWindow.superclass.show.apply(this, arguments);

		var form = this;
		var base_form = this.findById('EvnReceptSearchForm').getForm();
		if(!this.ReceptGridPanel.getAction('action_undo_delete') && getRegionNick() != 'msk'){
			this.ReceptGridPanel.addActions({
				disabled: true,
				handler: function() {
					this.UndoDeleteEvnRecept();
				}.createDelegate(this),
				name: 'action_undo_delete',
				text: 'Удалить пометку к удалению '
			},4);
		}
		this.findById('ERS_SearchFilterTabbar').setActiveTab(0);

		this.restore();
		this.center();
		this.maximize();
		this.doReset(true);

		base_form.getEl().dom.action = "/?c=Search&m=printSearchResults";
		base_form.getEl().dom.method = "post";
		base_form.getEl().dom.target = "_blank";
		base_form.standardSubmit = true;
        this.closeActions = false;
        this.viewOnly = false;
        if(arguments[0])
        {
            if(arguments[0].onlyView){
                this.closeActions = true;
                this.ReceptGridPanel.setActionDisabled('action_add', true);
                this.ReceptGridPanel.setActionDisabled('action_edit', true);
                this.ReceptGridPanel.setActionDisabled('action_delete', true);
            }
            if(arguments[0].viewOnly)
			{
				this.viewOnly = arguments[0].viewOnly;
			}
        }

		this.hasPolka = false;
		this.getLpuUnitPolkaCount({callback: function(data){
			form.hasPolka = (data && data.LpuUnitCount > 0);
			form.ReceptGridPanel.setActionDisabled('action_add', (form.closeActions || !form.hasPolka || getRegionNick() == 'msk'));
			form.ReceptGridPanel.getAction('action_print').menu.printObject.setHidden(form.closeActions || !form.hasPolka);
		}.createDelegate(this)});
	},
	title: WND_DLO_RECSEARCH,
	width: 800
});