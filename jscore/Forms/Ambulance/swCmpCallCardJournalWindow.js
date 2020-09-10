/**
* swCmpCallCardJournalWindow - журнал вызовов СМП АРМ поликлиники
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Ambulance
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenitcyn Ivan aka IvP (ipshon@gmail.com)
* @version      11.04.2010
* @comment      Префикс для id компонентов CCCJW (CmpCallCardJournalWindow)
*/

sw.Promed.swCmpCallCardJournalWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'CmpCallCardJournalWindow',
	maximized: true,	
	border: false,
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	modal: true,
	plain: false,
	resizable: false,
	title: lang['jurnal_vyizovov_smp'],
	//объект с параметрами рабочего места, с которыми была открыта форма АРМа
	userMedStaffFact: null,
	openEPHForm: function()
	{
		var record = this.grid.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert(lang['oshibka'], lang['oshibka_vyibora_zapisi']);
			return false;
		}
		if (getWnd('swPersonEmkWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['forma_emk_epz_v_dannyiy_moment_otkryita']);
			return false;
		}
		else 
		{
			var params = {
				userMedStaffFact: this.userMedStaffFact,
				Person_id: record.get('Person_id'),
				Server_id: record.get('Server_id'),
				PersonEvn_id: record.get('PersonEvn_id'),
				mode: 'workplace',
				ARMType: 'common',
				readOnly: this.viewOnly
			};
			getWnd('swPersonEmkWindow').show(params);
		}
	},
	openCmpCallCardEditWindow: function(action) {
		if ( !action || !action.toString().inlist([ 'add', 'edit', 'view']) ) {
			return false;
		}

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if ( getWnd('swCmpCallCardEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_kartyi_vyizova_uje_otkryito']);
			return false;
		}

		var formParams = new Object();
		var grid = this.grid.getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.cmpCallCardData ) {
				return false;
			}

			// Обновить запись в grid
			var record = grid.getStore().getById(data.cmpCallCardData.CmpCallCard_id);

			if ( record ) {
				var grid_fields = new Array();
				var i = 0;

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.cmpCallCardData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('CmpCallCard_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData({'data': [ data.cmpCallCardData ]}, true);
			}
		};

		if ( action == 'add' ) {
			getWnd('swPersonSearchWindow').show({
				onClose: Ext.emptyFn,
				onSelect: function(person_data) {
					formParams.Person_id =  person_data.Person_id;
					formParams.Server_id = person_data.Server_id;

					params.onHide = function() {
						getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 250);
					};

					params.formParams = formParams;

					getWnd('swCmpCallCardEditWindow').show(params);
				},
				personFirname: this.FilterPanel.getForm().findField('Person_Firname').getValue(),
				personSecname: this.FilterPanel.getForm().findField('Person_Secname').getValue(),
				personSurname: this.FilterPanel.getForm().findField('Person_Surname').getValue(),
				searchMode: 'all'
			});
		}
		else {
			if ( !grid.getSelectionModel().getSelected() ) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record.get('CmpCallCard_id') ) {
				return false;
			}

			formParams.CmpCallCard_id = selected_record.get('CmpCallCard_id');

			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			};

			params.formParams = formParams;

			getWnd('swCmpCallCardEditWindow').show(params);
		}
	},
	openForm: function(action)
	{
		var record = this.grid.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert(lang['oshibka'], lang['oshibka_vyibora_zapisi']);
			return false;
		}
		var id = record.get('EvnPS_id');
		var Person_id = record.get('Person_id');
		var PersonEvn_id = record.get('PersonEvn_id');
		var Server_id = record.get('Server_id');
		var open_form = 'swEvnPSEditWindow';
		var params = {action: action, Person_id: Person_id, PersonEvn_id: PersonEvn_id, Server_id: Server_id, EvnPS_id: id};
		getWnd(open_form).show(params);
	},
	resetForm: function(isLoad)
	{
		var that = this;
		this.findById('CCCJW_CmpCallCard_prmDT_From').setValue(isLoad ? getGlobalOptions().date : null);
		this.findById('CCCJW_CmpCallCard_prmDT_To').setValue(isLoad ? getGlobalOptions().date : null);
		this.findById('CCCJW_LpuAttachType_id').setValue(1);
		this.findById('CCCJW_CmpCallCard_IsPoli').clearValue();
		if ( this.findById('CCCJW_Lpu_aid').getStore().getCount() > 0 ) {
				if(Ext.isEmpty(getGlobalOptions().lpu_id) || getWnd('swWorkPlaceMZSpecWindow').isVisible())
					this.findById('CCCJW_Lpu_aid').setValue(-1);
				else
					this.findById('CCCJW_Lpu_aid').setValue(getGlobalOptions().lpu_id);
		}
		this.findById('CCCJW_MedPersonal_id').clearValue();
		this.findById('CCCJW_LpuRegion_id').clearValue();
		this.findById('CCCJW_CmpLpu_id').clearValue();
		var grid = this.grid.getGrid();
		grid.getTopToolbar().items.items[12].el.innerHTML = '0 / 0';
		this.findById('CCCJW_CmpCallCard_prmDT_From').focus();
		grid.getStore().removeAll();
	},
	begDate: null,
	setBegDate: function(isLoad) {
		if ( this.begDate )
		{
			this.findById('CCCJW_CmpCallCard_prmDT_From').setValue(this.begDate);
			if (isLoad) this.doSearch();
		}
		else
		{
			Ext.Ajax.request({
				callback: function(opt, success, response) {
					if ( success && response.responseText != '' ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						var begDate = Date.parseDate(response_obj.begDate, 'd.m.Y');
						this.begDate = begDate.format('d.m.Y');
						this.findById('CCCJW_CmpCallCard_prmDT_From').setValue(this.begDate);
						if (isLoad) this.doSearch();
					}
				}.createDelegate(this),
				url: C_LOAD_CURTIME
			});
		}
	},
	searchInProgress: false,
	doSearch: function() {
		this.loadGridWithFilter(false);
	},
	loadGridWithFilter: function(clear) {
		if (this.searchInProgress) {
			log(lang['poisk_uje_vyipolnyaetsya']);
			return false;
		} else {
			this.searchInProgress = true;
		}
		var thisWindow = this;
		var grid = this.grid;
				
		var params = {
			limit: 100,
			start: 0
		};
		
		params.CmpCallCard_prmDT_From = Ext.util.Format.date(Ext.getCmp('CCCJW_CmpCallCard_prmDT_From').getValue(), 'd.m.Y');
		params.CmpCallCard_prmDT_To = Ext.util.Format.date(Ext.getCmp('CCCJW_CmpCallCard_prmDT_To').getValue(), 'd.m.Y');
		params.LpuAttachType_id = Ext.getCmp('CCCJW_LpuAttachType_id').getValue();
		params.CmpCallCard_IsPoli = Ext.getCmp('CCCJW_CmpCallCard_IsPoli').getValue();
		params.Lpu_aid = Ext.getCmp('CCCJW_Lpu_aid').getValue();
		params.MedPersonal_id = Ext.getCmp('CCCJW_MedPersonal_id').getValue();
		params.LpuRegion_id = Ext.getCmp('CCCJW_LpuRegion_id').getValue();
		params.CmpLpu_id = Ext.getCmp('CCCJW_CmpLpu_id').getValue();

		grid.loadData({
			globalFilters: params
		});
	},
	show: function()
	{
		sw.Promed.swCmpCallCardJournalWindow.superclass.show.apply(this, arguments);
		this.userMedStaffFact = null;
		/*if ((!arguments[0]) || (!arguments[0].userMedStaffFact))
		{
			this.hide();
			Ext.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Не указаны параметры АРМа врача.');
		} else {
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}*/
		//getGlobalOptions().lpu_id
		this.center();
		this.resetForm(true);
		current_window = this;
		this.viewOnly = true;
		if(arguments[0] && arguments[0].viewOnly) {
			this.viewOnly = arguments[0].viewOnly;
		}
		this.grid.addActions({
			handler: function() {
				current_window.openEPHForm();
			}.createDelegate(this),
			iconCls: 'open16',
			name: 'open_emk',
			text: lang['otkryit_emk'],
			tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'],
			disabled: true
		});
		if ( Ext.getCmp('CCCJW_Lpu_aid').getStore().getCount() == 0 ) {
			Ext.getCmp('CCCJW_Lpu_aid').getStore().load({
				callback: function(records, options, success) {
					if ( !success ) {
						Ext.getCmp('CCCJW_Lpu_aid').getStore().removeAll();
						sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке справочника МО'));
						return false;
					}

					if(Ext.isEmpty(getGlobalOptions().lpu_id) || getWnd('swWorkPlaceMZSpecWindow').isVisible())
			Ext.getCmp('CCCJW_Lpu_aid').setValue(-1);
					else
						Ext.getCmp('CCCJW_Lpu_aid').setValue(getGlobalOptions().lpu_id);

		Ext.getCmp('CCCJW_Lpu_aid').fireEvent('change', Ext.getCmp('CCCJW_Lpu_aid'), Ext.getCmp('CCCJW_Lpu_aid').getValue());
				}
			});
		}
		else {
			if(Ext.isEmpty(getGlobalOptions().lpu_id) || getWnd('swWorkPlaceMZSpecWindow').isVisible())
				Ext.getCmp('CCCJW_Lpu_aid').setValue(-1);
			else
				Ext.getCmp('CCCJW_Lpu_aid').setValue(getGlobalOptions().lpu_id);

			Ext.getCmp('CCCJW_Lpu_aid').fireEvent('change', Ext.getCmp('CCCJW_Lpu_aid'), Ext.getCmp('CCCJW_Lpu_aid').getValue());
		}
		this.findById('CCCJW_CmpCallCard_prmDT_From').focus(true, 100);
	},
	initComponent: function()
	{
		var current_window = this;
		this.filter = new Ext.form.FieldSet(
		{
			region: 'north',
			xtype: 'fieldset',
			autoHeight: true,
			title: lang['filtr'],
			layout: 'column',
			keys: [
				{
					fn: function(inp, e) {
						if ( e.getKey() == Ext.EventObject.ENTER || (e.altKey && e.getKey() == Ext.EventObject.S ) )
							Ext.getCmp('CmpCallCardJournalWindow').doSearch();
					},
					key: [
						Ext.EventObject.ENTER,
						Ext.EventObject.S
					],
					stopEvent: true
				}
			],
			items:
			[{
				layout: 'form',
				labelAlign: 'right',
				id: 'CCCJW_CmpCallCard_FilterForm',
				labelWidth: 170,
				border: false,
				bodyStyle: 'background-color: transparent; padding-left: 5px;',
				items:
				[{
					fieldLabel: lang['data_vyizova_s'],
					allowBlank: true,
					disabled: false,
					tabIndex: TABINDEX_CCCJW + 1,
					format: 'd.m.Y',
					name: 'CmpCallCard_prmDT_From',
					id: 'CCCJW_CmpCallCard_prmDT_From',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					xtype: 'swdatefield'
				},
				{
					fieldLabel: lang['data_vyizova_po'],
					allowBlank: true,
					disabled: false,
					tabIndex: TABINDEX_CCCJW + 2,
					format: 'd.m.Y',
					name: 'CmpCallCard_prmDT_To',
					id: 'CCCJW_CmpCallCard_prmDT_To',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					xtype: 'swdatefield'
				}, {
					allowBlank: false,
					codeField: 'LpuAttachType_Code',
					displayField: 'LpuAttachType_Name',
					editable: false,
					fieldLabel: lang['prikreplenie'],
					hiddenName: 'LpuAttachType_id',
					id: 'CCCJW_LpuAttachType_id',
					hideEmptyRow: true,
					listeners: {
						'blur': function(combo)  {
								if ( combo.value == '' )
										combo.setValue(1);
						}
					},
					store: new Ext.data.SimpleStore({
							autoLoad: true,
							data: [
									[ 1, 1, lang['s_tekuschim_prikrepleniem'] ],
									[ 2, 2, lang['s_prikrepleniem_na_datu_vyizova'] ]
							],
							fields: [
									{name: 'LpuAttachType_id', type: 'int'},
									{name: 'LpuAttachType_Code', type: 'int'},
									{name: 'LpuAttachType_Name', type: 'string'}
							],
							key: 'LpuAttachType_id',
							sortInfo: {field: 'LpuAttachType_Code'}
					}),
					tabIndex: TABINDEX_CCCJW + 3,
					tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<font color="red">{LpuAttachType_Code}</font>&nbsp;{LpuAttachType_Name}',
							'</div></tpl>'
					),
					value: 1,
					valueField: 'LpuAttachType_id',
					width: 300,
					xtype: 'swbaselocalcombo'
				},
				new sw.Promed.SwYesNoCombo({
					allowBlank: true,
					disabled: false,
					fieldLabel: lang['aktiv_v_polikliniku'],
					id: 'CCCJW_CmpCallCard_IsPoli',
					hiddenName: 'CmpCallCard_IsPoli',
					tabIndex: TABINDEX_CCCJW + 3,
					width: 150
				})]
			},
			{
				layout: 'form',
				labelAlign: 'right',
				labelWidth: 170,
				border: false,
				bodyStyle: 'background-color: transparent; padding-left: 5px;',
				items:
				[{
					//выбор лпу и участков в этих лпу только под суперадмином
					additionalRecord: {
						value: -1,
						text: langs('Все')
					},
					allowBlank: false,
					tabIndex: TABINDEX_CCCJW + 4,
					width: 300,
					listWidth: 350,
					fieldLabel: lang['mo_prikrepleniya'],
					id: 'CCCJW_Lpu_aid',
					hiddenName: 'Lpu_aid',
					xtype: 'swlpusearchcombo',
					listeners: {
						'change': function(combo, value){
							var lpu_id = value;
							if (lpu_id && lpu_id > 0) {
								current_window.findById('CCCJW_MedPersonal_id').setValue(null);
								current_window.findById('CCCJW_MedPersonal_id').getStore().load(
									{
										params: {Lpu_id: lpu_id},
										callback: function () {
											if ((getGlobalOptions().medpersonal_id > 0) && (!isSuperAdmin())) {
												current_window.findById('CCCJW_MedPersonal_id').setFieldValue('MedPersonal_id', getGlobalOptions().medpersonal_id);
											}
										}.createDelegate(this)
									});
							} else {
								current_window.findById('CCCJW_MedPersonal_id').setValue(null);
								current_window.findById('CCCJW_MedPersonal_id').getStore().removeAll();
							}
						}
					}
				},
				{
					// врач
					fieldLabel: lang['vrach'],
					width: 300,
					listWidth: 450,
					hiddenName: 'MedPersonal_id',
					id: 'CCCJW_MedPersonal_id',
					tabIndex: TABINDEX_CCCJW + 4,
					xtype: 'swmedpersonalwithlpuregioncombo',
					listeners: {
						'change': function( combo, value ) {
							var medpersonal_id = value;
							var lpu_id = Ext.getCmp('CmpCallCardJournalWindow').findById('CCCJW_Lpu_aid').getValue();
							if (current_window.first)
							{
								current_window.setBegDate(true);
								current_window.first = false;
							}
							if ( (medpersonal_id) && (medpersonal_id>0) )
							{
								current_window.findById('CCCJW_LpuRegion_id').setValue(null);
								current_window.findById('CCCJW_LpuRegion_id').getStore().load(
								{
									params: {MedPersonal_id: medpersonal_id}
								});
							}
							else
							{
								current_window.findById('CCCJW_LpuRegion_id').setValue(null);
								current_window.findById('CCCJW_LpuRegion_id').getStore().load(
								{
									params: {Lpu_id: lpu_id}
								});
							}
						}
					},
					allowBlank: true
				},
				{
					// для врача только фильтрация по своим участкам если их несколько
					fieldLabel: lang['uchastok'],
					width: 300,
					hiddenName: 'LpuRegion_id',
					id: 'CCCJW_LpuRegion_id',
					tabIndex: TABINDEX_CCCJW + 5,
					xtype: 'swlpuregioncombo',
					allowBlank: true
				}, {
					//выбор лпу и участков в этих лпу только под суперадмином
					allowBlank: true,
					autoLoad: true,
					tabIndex: TABINDEX_CCCJW + 6,
					width: 300,
					listWidth: 350,
					fieldLabel: 'МО (куда доставлен)',
					id: 'CCCJW_CmpLpu_id',
					hiddenName: 'CmpLpu_id',
					xtype: 'swlpulocalcombo'
				}]
			}]
		});


		this.grid = new sw.Promed.ViewFrame(
		{
			id: 'CCCJW_JournalGrid',
			object: 'CmpCallCard',
			dataUrl: '/?c=CmpCallCard&m=loadCmpCallCardJournalGrid',
			layout: 'fit',
			region: 'center',
			paging: true,
			root: 'data',
			title: lang['spisok_kart_vyizova_rezultat_poiska'],
			totalProperty: 'totalCount',
			toolbar: true,
			autoLoadData: false,
			stringfields:
			[
				{name: 'CmpCallCard_id', type: 'int', header: 'ID', key: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Person_SurName', autoexpand: true, type: 'string', header: lang['familiya']},
				{name: 'Person_FirName', type: 'string', width: 120, header: lang['imya']},
				{name: 'Person_SecName', type: 'string', width: 120, header: lang['otchestvo']},
				{name: 'Person_Birthday', type: 'date', header: lang['data_rojdeniya'], width: 90},
				{name: 'CmpCallCard_prmDT', type: 'datetime', header: lang['data_vyizova'], width: 90},
				{name: 'CmpReason_Name', type: 'string', header: lang['povod'], width: 120},
				{name: 'CmpLpu_Name', type: 'string', header: lang['lpu'], width: 80},
				{name: 'Diag_UName', header: lang['diagnoz_smp'], width: 150},
				{name: 'Diag_SName', header: lang['diagnoz_statsionara'], width: 150},
				{name: 'CmpCallCard_isPoli', type: 'checkbox', header: lang['aktiv'], width: 60},
				{name: 'EvnVizitPL_setDate', type: 'date', header: lang['poseschenie'], width: 100},
				{name: 'Diag_VName', header: lang['diagnoz_posescheniya'], width: 150}
			],
			actions:
			[
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_view', text: lang['prosmotr'], handler: function() {this.openCmpCallCardEditWindow('view')}.createDelegate(this)},
				{name:'action_edit', hidden: true, disabled: true, handler: function() {this.openForm('view')}.createDelegate(this)},
				{name:'action_delete', hidden: true, disabled: true},
				{name:'action_refresh'},
				{name:'action_print'}
			],
			onCellClick: function(grid,rowIdx,colIdx,e) {
				var record = grid.getStore().getAt(rowIdx);
				if ( !record ) {
					return false;
				}
				// Открываем просмотр направления по клику по иконке направления
				if (12 == colIdx && record.data.IsEvnDirection && record.data.EvnDirection_id)
				{
					getWnd('swEvnDirectionEditWindow').show({
						action: 'view',
						formParams: new Object(),
						EvnDirection_id: record.data.EvnDirection_id
					});
				}
			},
			onRowSelect: function(sm,rowIdx,record) {
				
				if (record.get('Person_id')) {
					this.grid.ViewActions['open_emk'].setDisabled(false);			
				} else {
					this.grid.ViewActions['open_emk'].setDisabled(true);
				}
				
			}.createDelegate(this),
			onLoadData: function() {
				this.searchInProgress = false;
			}.createDelegate(this)
		});

		Ext.apply(this,
		{
			region: 'center',
			layout: 'border',
			items: [
			this.filter,
			this.grid
			],
			buttons: [{
				id: 'CCCJW_BtnSearch',
				text: BTN_FRMSEARCH,
				tabIndex: TABINDEX_CCCJW + 19,
				iconCls: 'search16',
				handler: function()
				{
					var form = Ext.getCmp('CmpCallCardJournalWindow');
					form.doSearch();
				}
			},
			{
				id: 'CCCJW_BtnClear1',
				text: lang['sbros'],
				tabIndex: TABINDEX_CCCJW + 20,
				iconCls: 'resetsearch16',
				handler: function()
				{
					var form = Ext.getCmp('CmpCallCardJournalWindow');
					form.resetForm();

				}
			},
			{
				text: '-'
			},
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				id: 'CCCJW_HelpButton',
				handler: function(button, event)
				{
					ShowHelp(this.title);
				}.createDelegate(this)
			},
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				tabIndex: TABINDEX_CCCJW + 50,
				handler: function() {
					this.hide();
				}.createDelegate(this)
			}],
			enableKeyEvents: true,
			keys:
			[{
				alt: true,
				fn: function(inp, e)
				{
					if (e.getKey() == Ext.EventObject.ESC)
					{
						Ext.getCmp('CmpCallCardJournalWindow').hide();
						return false;
					}
				},
				key: [ Ext.EventObject.ESC ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swCmpCallCardJournalWindow.superclass.initComponent.apply(this, arguments);
	}
});