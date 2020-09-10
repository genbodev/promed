/**
* swOrgSearchWindow - окно поиска организации.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      29.03.2009
*/

sw.Promed.swOrgSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	allowDuplicateOpening: true,
	buttonAlign: 'left',
	closeAction: 'hide',
	disableAllActions: function(disable) {
		if ( (disable === true) || (disable == undefined) ) {
			this.SearchGrid.setReadOnly(true);
		}
		else {
			this.SearchGrid.setReadOnly(false);
		}
	},
	doReset: function() {
		this.SearchGrid.removeAll({clearAll: true});
		this.findById(this.id + '_' + 'OrgSearchForm').getForm().reset();
		this.findById(this.id + '_' + 'Org_Nick').focus(true, 250);
	},
	searchInProgress: false,
	doSearch: function() {
		if (this.searchInProgress) {
			log(lang['poisk_uje_vyipolnyaetsya']);
			return false;
		} else {
			this.searchInProgress = true;
		}
		var thisWindow = this;
		var Mask = new Ext.LoadMask(this.getEl(), { msg: SEARCH_WAIT});
		var params = this.findById(this.id + '_' + 'OrgSearchForm').getForm().getValues();

		if ( !params.Org_Nick && !params.Org_Name && !this.enableOrgType) {
			sw.swMsg.alert(lang['oshibka'], lang['vvedite_usloviya_poiska'], function() { _this.findById(thisWindow.id + '_' + 'Org_Nick').focus(true, 250); });
			thisWindow.searchInProgress = false;
			return false;
		}

		params.OrgServed_Type = this.findById(this.id + '_' + 'OrgType').getValue();

		if ( this.obj != '' ) {
			params.OrgType = this.obj;
		}
		if ( this.OMSSprTerr_Code > 0 ) {
			params.OMSSprTerr_Code = this.OMSSprTerr_Code;
		}
		if (this.DepartAffilType_id) {
			params.DepartAffilType_id = this.DepartAffilType_id;
		}
		if (!Ext.isEmpty(this.DispClass_id)) {
			params.DispClass_id = this.DispClass_id;
		}
		if (!Ext.isEmpty(this.Disp_consDate)) {
			params.Disp_consDate = this.Disp_consDate;
		}
		if (!Ext.isEmpty(this.activeInDate)) {
			params.activeInDate = this.activeInDate;
		}
		if (this.isNotForSystem) {
			params.isNotForSystem = 1;
		}

		if (this.KLRgn_id > 0) {
			params.KLRgn_id = this.KLRgn_id;
		}

		params.onlyFromDictionary = this.onlyFromDictionary;
		params.needOrgType = 1;

		this.SearchGrid.removeAll({clearAll: true});
		Mask.show();

		this.SearchGrid.loadData({
			callback: function() {
				thisWindow.searchInProgress = false;
				Mask.hide();

				//активируем кнопки после поиска #152799
				thisWindow.SearchGrid.setActionDisabled('action_add', false);
				thisWindow.SearchGrid.setActionDisabled('action_refresh', false);
			},
			globalFilters: params
		});
	},
	draggable: true,
	height: 500,
	id: 'swOrgSearchWindow',
	initComponent: function() {
		var win = this;
		var _this = this;

		_this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function() {
					if(!(getGlobalOptions().enable_action_reference_by_admref_group) || isUserGroup('AdminOrgReference') ){
						if ( _this.obj == 'bank' )
							return;
						var obj = _this.obj;
						getWnd('swOrgEditWindow').show({
							action: 'add',
							callback: function(Org_id) {
								if (Org_id) {
									_this.onOrgSelect({Org_id: Org_id});
								}
							},
							onHide: function() {
								_this.findById(_this.id + '_' + 'Org_Nick').focus(true, 50);
							},
							orgType: obj,
							allowEmptyUAddress: _this.allowEmptyUAddress
						});
					}
					else{
						sw.swMsg.alert(lang['oshibka'], lang['obratites_k_adminu_spravochnika_org']+getGlobalOptions().contact_info);
						return false;
					}

				}},
				{name: 'action_edit', handler: function() {
					if(!(getGlobalOptions().enable_action_reference_by_admref_group) || isUserGroup('AdminOrgReference') ){
						var grid = _this.SearchGrid.getGrid();
						if ( _this.obj == 'bank'||_this.obj == 'lpu' )
							return;
						if ( !grid.getSelectionModel().getSelected() )
							return;
						var obj = _this.obj;
						var orgIdField = 'Org_pid';

						var record = grid.getSelectionModel().getSelected();

						if (( record.get('Server_id') && record.get('Server_id') == 0 && !isSuperAdmin()) || !Ext.isEmpty(record.get('OrgStac_Code')))
							return;

						if ( obj.inlist([ 'orgstac', 'orgstaceducation', 'lpu' ]) ) {
							orgIdField = 'Org_id';
						}

						getWnd('swOrgEditWindow').show({
							action: 'edit',
							callback: function(Org_id) {
								_this.doSearch();
							},
							onHide: function() {
								_this.findById(_this.id + '_' + 'Org_Nick').focus(true, 50);
							},
							Org_id: grid.getSelectionModel().getSelected().get(orgIdField),
							orgType: obj,
							allowEmptyUAddress: _this.allowEmptyUAddress
						});
					}
					else{
						sw.swMsg.alert(lang['oshibka'], lang['obratites_k_adminu_spravochnika_org']+getGlobalOptions().contact_info);
						return false;
					}
				}},
				{name: 'action_view', handler: function() {
					var grid = _this.SearchGrid.getGrid();
					if ( !grid.getSelectionModel().getSelected() )
						return;
					var obj = _this.obj;
					var orgIdField = 'Org_pid';

					if ( obj.inlist([ 'orgstac', 'orgstaceducation', 'lpu' ]) ) {
						orgIdField = 'Org_id';
					}

					getWnd('swOrgEditWindow').show({
						action: 'view',
						onHide: function() {
							_this.findById(_this.id + '_' + 'Org_Nick').focus(true, 50);
						},
						Org_id: grid.getSelectionModel().getSelected().get(orgIdField),
						orgType: obj
					});
				}},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_print'}
			],
			region: 'center',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: C_ORG_LIST,
			height: 180,
			object: 'Org',
			uniqueId: true,
			paging: false,
			onRowSelect: function (sm,index,record) {
				var nowDate = new Date.parse(getGlobalOptions().date);
				_this.SearchGrid.setActionDisabled('action_edit', true);
				_this.SearchGrid.setActionDisabled('action_view', true);

				if ( typeof record == 'object' && !Ext.isEmpty(record.get('Org_id')) ) {
					_this.SearchGrid.setActionDisabled('action_view', false);

					if ( (record.get('Server_id') && record.get('Server_id') == 0 && !isSuperAdmin()) || !Ext.isEmpty(record.get('OrgStac_Code')) || _this.obj == 'lpu' || _this.obj == 'bank' || _this.disableEdit) {
						_this.SearchGrid.setActionDisabled('action_edit', true);
					} else {
						_this.SearchGrid.setActionDisabled('action_edit', false);
					}
					if (!Ext.isEmpty(record.get('Org_endDate')) && record.get('Org_endDate') <= nowDate) {
						Ext.getCmp(win.id + '_selectButton').disable();
					} else {
						Ext.getCmp(win.id + '_selectButton').enable();
					}
				}
			},
			onDblClick: function() {
				var nowDate = new Date.parse(getGlobalOptions().date);
				var record = win.SearchGrid.getGrid().getSelectionModel().getSelected();
				
				if(Ext.isEmpty(record.get('Org_endDate')) || record.get('Org_endDate') > nowDate) {
					_this.onOkButtonClick();
				}
			},
			stringfields: [
				{ name: 'Org_id', type: 'int', hidden: true },
				{ name: 'OrgType_id', type: 'int', hidden: true },
				{ name: 'Org_pid', type: 'int', hidden: true },
				{ name: 'OrgSMO_id', type: 'int', hidden: true },
				{ name: 'OrgAnatom_id', type: 'int', hidden: true },
				{ name: 'Lpu_id', type: 'int', hidden: true },
				{ name: 'Org_Code', type: 'string', hidden: true },
				{ name: 'Org_StickNick', type: 'string', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'OrgType_SysNick', type: 'string', hidden: true },
				{ name: 'Org_Nick', header: lang['sokraschenie'], type: 'string', width: 180 },
				{ name: 'OrgStac_Code', header: lang['federalnyiy_kod'], type: 'string' },
				{ name: 'Org_Name', header: lang['naimenovanie'], type: 'string', id: 'autoexpand' },
				{ name: 'Org_begDate', header: lang['data_otkryitiya'], type: 'date', width: 100 },
				{ name: 'Org_endDate', header: lang['data_zakryitiya'], type: 'date', width: 100 },
				{ name: 'Org_Address', header: lang['adres'], type: 'string', width: 200 },
				{ name: 'Lpu_f003mcod', header: lang['reestrovyiy_nomer'], type: 'string', hidden: true, width: 120 }
			],
			title: '',
			toolbar: true
		});

		_this.SearchGrid.getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';

				if ( !Ext.isEmpty(row.get('Org_endDate')) && getValidDT(row.get('Org_endDate'), '') < getValidDT(_this.onDate, '') ) {
					cls = cls+'x-grid-rowgray ';
				}

				return cls;
			}
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.doSearch()
				},
				iconCls: 'search16',
				tabIndex: 1501,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.ownerCt.doReset();
				},
				iconCls: 'resetsearch16',
				tabIndex: 1502,
				text: BTN_FRMRESET
			}, {
				handler: function() {
					this.ownerCt.onOkButtonClick();
				},
				iconCls: 'ok16',
				id: _this.id + '_selectButton',
				tabIndex: 1503,
				text: lang['vyibrat']
			}, {
				text: '-'
			},
			HelpButton(this, 1504),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				onTabElement: _this.id + '_' + 'Org_Nick',
				tabIndex: 1505,
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.form.FormPanel({
				autoHeight: true,
				buttonAlign: 'left',
				frame: true,
				id: _this.id + '_' + 'OrgSearchForm',
				labelAlign: 'top',
				region: 'north',
				items: [{
					anchor: '100%',
					enableKeyEvents: true,
					fieldLabel: lang['sokraschenie'],
					id: _this.id + '_' + 'Org_Nick',
					listeners: {
						'keydown': function (inp, e) {
							if ( e.shiftKey == false && e.getKey() == Ext.EventObject.TAB ) {
								e.stopEvent();
								inp.ownerCt.findById(_this.id + '_' + 'Org_Name').focus(true, 50);
							}
						}
					},
					name: 'Org_Nick',
					tabIndex: 1506,
					xtype: 'textfield'
				}, {
					anchor: '100%',
					enableKeyEvents: true,
					fieldLabel: lang['naimenovanie'],
					id: _this.id + '_' + 'Org_Name',
					listeners: {
						'keydown': function (inp, e) {
							if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
								e.stopEvent();
								inp.ownerCt.findById(_this.id + '_' + 'Org_Nick').focus(true, 50);
							}
						}
					},
					name: 'Org_Name',
					tabIndex: 1507,
					xtype: 'textfield'
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						border: false,
						columnWidth: .40,
						items:[{
							allowBlank: true,
							comboSubject: 'OrgType',
							typeCode: 'int',
							fieldLabel: lang['tip_organizatsii'],
							id: _this.id + '_' + 'OrgType',
							hiddenName: 'OrgType_id',
							tabIndex: 1509,
							width: 300,
							xtype: 'swcommonsprcombo',
							listeners: {
								select: function (combo, newValue, oldValue) {
									if(getRegionNick() == 'vologda') {
										var AdditionalFeature = _this.findById(_this.id + '_' + 'OrgSearchForm').getForm().findField('AdditionalFeature_id');
										if (combo.getFieldValue('OrgType_Code') == 11)
											AdditionalFeature.showContainer();
										else
											AdditionalFeature.hideContainer();
									}
								}
							}
						}]
					},{
						layout: 'form',
						border: false,
						columnWidth: .40,
						items:[{
							xtype: 'swbaselocalcombo',
							typeCode: 'int',
							fieldLabel: 'Доп. признак',
							codeField: 'AdditionalFeature_Code',
							displayField: 'AdditionalFeature_Name',
							hiddenName: 'AdditionalFeature_id',
							valueField: 'AdditionalFeature_id',
							tabIndex: 1512,
							width: 300,
							store: new Ext.data.SimpleStore({
								autoLoad: true,
								data: [
									[ 1, 1, langs('Областные МО') ],
									[ 2, 2, langs('Городские МО') ],
									[ 3, 3, langs('Ведомственные МО') ],
									[ 4, 4, langs('ЦРБ') ],
									[ 5, 5, langs('Участковая больница') ],
									[ 6, 6, langs('ФАП') ],
									[ 7, 7, langs('Амбулатория') ],
									[ 8, 8, langs('Круглосуточный/дневной стационар') ],
									[ 9, 9, langs('ССП / ОСП') ],
									[ 10, 10, langs('МО других территорий') ],
									[ 11, 11, langs('Частная медицинская клиника') ]
								],
								fields: [
									{ name: 'AdditionalFeature_id', type: 'int'},
									{ name: 'AdditionalFeature_Code', type: 'int'},
									{ name: 'AdditionalFeature_Name', type: 'string'}
								],
								key: 'AdditionalFeature_id',
								sortInfo: { field: 'AdditionalFeature_Code' }
							}),
						}]
					},{
						layout: 'form',
						border: false,
						bodyStyle:'margin:15px 0 0 0;',
						columnWidth: .20,
						items:[{
							xtype: 'checkbox',
							height:22,
							hideLabel: true,
							name: 'WithOrgStacCode',
							tabIndex: 1511,
							boxLabel: lang['s_federalnyim_kodom']
						}]
					}]
				}],
				keys: [{
					fn: function(e) {
						_this.doSearch();
					},
					key: Ext.EventObject.ENTER,
					stopEvent: true
				}]
			}), _this.SearchGrid
			]
		});
		sw.Promed.swOrgSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onWinClose();

			if ( this.isWindowCopy == true ) {
				this.destroy();
			}
		}
	},
	modal: true,
	onOkButtonClick: function() {
        var rec = this.SearchGrid.getGrid().getSelectionModel().getSelected();

		if ( rec ) {
			this.onOrgSelect({
				Org_id: rec.get('Org_id') || null,
				OrgType_id: rec.get('OrgType_id') || null,
				OrgSMO_id: rec.get('OrgSMO_id') || null,
				OrgAnatom_id: rec.get('OrgAnatom_id') || null,
				Org_pid: rec.get('Org_pid') || null,
				Lpu_id: rec.get('Lpu_id') || null,
				Org_Nick: rec.get('Org_Nick') || null,
				Org_Name: rec.get('Org_Name') || null,
				Org_StickNick: rec.get('Org_StickNick') || null,
				Org_begDate: rec.get('Org_begDate') || null,
				Org_endDate: rec.get('Org_endDate') || null,
				OrgStac_Code: rec.get('OrgStac_Code') || null,
				OrgType_SysNick: rec.get('OrgType_SysNick') || null
			});

			this.hide();
		}
	},
	onOrgSelect: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swOrgSearchWindow.superclass.show.apply(this, arguments);

		this.obj = 'org';
		this.OMSSprTerr_Code = 0;
		this.onOrgSelect = Ext.emptyFn;
		this.onWinClose = Ext.emptyFn;
		this.enableOrgType = false;
		this.defaultOrgType = null;
		this.DepartAffilType_id = null;
		this.DispClass_id = null;
		this.Disp_consDate = null;
		this.activeInDate = null;
		this.KLRgn_id = null;
		this.onlyFromDictionary = false;
        this.allowEmptyUAddress = '1';
        this.disableEdit = false;
		this.doReset();
		this.onDate = getGlobalOptions().date;

		var base_form = this.findById(this.id + '_' + 'OrgSearchForm').getForm();
		base_form.reset();
		
		if ( arguments[0] ) {
			if ( arguments[0].onSelect ) {
				this.onOrgSelect = arguments[0].onSelect;
			}

			if ( arguments[0].onDate ) {
				if ( typeof arguments[0].onDate == 'object' ) {
					this.onDate = Ext.util.Format.date(arguments[0].onDate, 'd.m.Y');
				} else {
					this.onDate = arguments[0].onDate;
				}
			}
			
			if ( arguments[0].DispClass_id ) {
				this.DispClass_id = arguments[0].DispClass_id;
			}
			
			if ( arguments[0].Disp_consDate ) {
				this.Disp_consDate = arguments[0].Disp_consDate;
			}

			if ( arguments[0].activeInDate ) {
				this.activeInDate = arguments[0].activeInDate;
			}

			if ( arguments[0].onClose ) {
				this.onWinClose = arguments[0].onClose;
			}

			if ( arguments[0].disableEdit ) {
				this.disableEdit = arguments[0].disableEdit;
			}
			
			if ( arguments[0].DepartAffilType_id ) {
				this.DepartAffilType_id = arguments[0].DepartAffilType_id;
			}
			
			if ( arguments[0].KLRgn_id ) {
				this.KLRgn_id = arguments[0].KLRgn_id;
			}
			
			if ( arguments[0].OrgType_id ) {
				base_form.findField('OrgType_id').setValue(arguments[0].OrgType_id);
			}

			if (( arguments[0].object ) && (arguments[0].object!='Org_Served')) {
				this.obj = arguments[0].object;
			}

			if ( arguments[0].OMSSprTerr_Code ) {
				this.OMSSprTerr_Code = arguments[0].OMSSprTerr_Code;
			}

			if ( arguments[0].object && arguments[0].object.inlist(['Org_Served','org','Org']) ){
				this.enableOrgType = true;
			}

			if ( arguments[0].enableOrgType ){
				this.enableOrgType = arguments[0].enableOrgType;
			}
			
			if ( arguments[0].defaultOrgType ){
				this.defaultOrgType = arguments[0].defaultOrgType;
			}


			if ( arguments[0].onlyFromDictionary ) {
				this.onlyFromDictionary = arguments[0].onlyFromDictionary;
			}

            if ( arguments[0].showOrgStacFilters ){
                this.showOrgStacFilters = arguments[0].showOrgStacFilters;
            } else {
                this.showOrgStacFilters = null
            }

            if ( arguments[0].allowEmptyUAddress ) {
                this.allowEmptyUAddress = arguments[0].allowEmptyUAddress;
            }
			
			this.isNotForSystem = arguments[0].isNotForSystem || false;
			
			if (this.isNotForSystem) {
				this.enableOrgType = false;
			}
		}

		// ставим доступность поля "Тип организации"
		this.findById(this.id + '_' + 'OrgType').setDisabled(!this.enableOrgType);
		
		if(this.defaultOrgType){
			this.findById(this.id + '_' + 'OrgType').setValue(this.defaultOrgType);
		}

		if ( this.obj != 'smo' ) {
			this.disableAllActions(false);
		}
		else {
			this.disableAllActions();
		}
		this.grid = this.SearchGrid.getGrid();
		this.SearchGrid.setActionDisabled('action_add', false);
		this.grid.getTopToolbar().items.items[0].enable();
		this.grid.getColumnModel().setHidden(this.grid.getColumnModel().findColumnIndex('Lpu_f003mcod'), true);

		this.grid.getColumnModel().setHidden(this.grid.getColumnModel().findColumnIndex('OrgStac_Code'), !this.showOrgStacFilters);
		this.showOrgStacFilters?base_form.findField('WithOrgStacCode').show(true):base_form.findField('WithOrgStacCode').hide(true);

		var org_type_combo = base_form.findField('OrgType_id');

		switch ( this.obj ) {
			case 'anatom':
				this.setTitle(WND_SEARCH_ORGANATOM);
			break;

			case 'lpu':
				this.setTitle(WND_SEARCH_LPU);
				this.grid.getColumnModel().setHidden(this.grid.getColumnModel().findColumnIndex('Lpu_f003mcod'), false);
				org_type_combo.setFieldValue('OrgType_Code',11);
			break;
			
			case 'bank':
				this.setTitle(WND_SEARCH_BANK);
				this.SearchGrid.setActionDisabled('action_add', true);
				org_type_combo.setFieldValue('OrgType_Code',2);
			break;

			case 'military':
				this.setTitle(WND_SEARCH_MILITARY);
				org_type_combo.setFieldValue('OrgType_Code',15);
			break;

			case 'rjd':
				org_type_combo.setFieldValue('OrgType_Code',22);
			break;

			case 'patronage':
				org_type_combo.setFieldValue('OrgType_Code',23);
				break;

			case 'court':
				org_type_combo.setFieldValue('OrgType_Code',25);
				break;

			default:
				this.setTitle(WND_SEARCH_ORG);
			break;
		}

		if(getRegionNick() == 'vologda'){
			if (base_form.findField('OrgType_id').getValue() == 11)
				base_form.findField('AdditionalFeature_id').showContainer();
			else
				base_form.findField('AdditionalFeature_id').hideContainer();
		}

		if (this.disableEdit) {
			this.SearchGrid.setReadOnly(true);

			this.SearchGrid.setActionHidden('action_add', true);
			this.SearchGrid.setActionHidden('action_edit', true);
		} else {
			this.SearchGrid.setActionHidden('action_add', false);
			this.SearchGrid.setActionHidden('action_edit', false);
		}

		//дизаблим добавление и обновление грида при открытии формы #152799
		this.SearchGrid.setActionDisabled('action_add', true);
		this.SearchGrid.setActionDisabled('action_refresh', true);
	},
	title: WND_SEARCH_ORG,
	width: 900
});