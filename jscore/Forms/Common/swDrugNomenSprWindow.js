/**
* swDrugNomenSprWindow - форма просмотра номенклатурного справочника
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2013 Swan Ltd.
* @comment      
*
*/

sw.Promed.swDrugNomenSprWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swDrugNomenSprWindow',
	objectSrc: '/jscore/Forms/Common/swDrugNomenSprWindow.js',
	id: 'DrugNomenSprWindow',
    setSprType: function() {
        var base_form = this.FilterPanel.getForm();
        var spr_type = this.sprtype_combo.getValue();

        switch(spr_type) {
            case 'llo_nom':
            case 'reg_nom':
                base_form.findField('DrugNomen_Code').showContainer();
                base_form.findField('DrugNomenOrgLink_Code').hideContainer();
                base_form.findField('DrugNomenOrgLink_Code').setValue(null);
                this.DrugNomenGrid.Code_Column = spr_type == 'reg_nom' ? 'DrugNomen_Code' : 'DrugNomenOrgLink_Code';
                break;
            case 'org_nom':
                base_form.findField('DrugNomen_Code').hideContainer();
                base_form.findField('DrugNomen_Code').setValue(null);
                base_form.findField('DrugNomenOrgLink_Code').showContainer();
                this.DrugNomenGrid.Code_Column = 'DrugNomenOrgLink_Code';
                break;
        }

        this.DrugNomenGrid.setParam('SprType_Code', this.sprtype_combo.getValue(), false);
    },
    setDefaultSprType: function() {
        var msf_store = sw.Promed.MedStaffFactByUser.store;

        var spr_type = 'org_nom';

        if (isSuperAdmin()){
        	spr_type = 'reg_nom';
        } else if (
            msf_store.findBy(function(rec) { return rec.get('ARMType') == 'minzdravdlo'; }) >= 0 || //minzdravdlo - АРМ специалиста ЛЛО ОУЗ
                msf_store.findBy(function(rec) { return rec.get('ARMType') == 'adminllo'; }) >= 0 || //adminllo - АРМ Администратора ЛЛО
                msf_store.findBy(function(rec) { return rec.get('ARMType') == 'dpoint'; }) >= 0 || //dpoint - АРМ Провизора
                (msf_store.findBy(function(rec) { return rec.get('ARMType') == 'merch'; }) >= 0 && isUserGroup('FarmacyUser')) || //merch - АРМ Товароведа
                msf_store.findBy(function(rec) { return rec.get('ARMType') == 'pmllo'; }) >= 0 //pmllo - АРМ Поставщика
            ) {
            spr_type = 'llo_nom';
        }

        this.sprtype_combo.setValue(spr_type)
        this.setSprType();
        this.DrugNomenGrid.setCodeColumnHidden();
    },
	doSearch: function(prep_class_id) {
		var form = this;
		var base_form = form.FilterPanel.getForm();
		var params = new Object();
		var node = form.PrepClassTree.getSelectionModel().selNode;

		form.DrugNomenGrid.removeAll();

		params = base_form.getValues();

		if (prep_class_id) {
			params.PrepClass_id = prep_class_id;
		} else
		if (node && node.attributes.object_value) {
			params.PrepClass_id = node.attributes.object_value
		} else {
			return;
		}

        params.DrugNomenOrgLink_Org_id = null;

        if (params.SprType_Code == 'org_nom') {
            params.DrugNomenOrgLink_Org_id = getGlobalOptions().org_id;
        }

        if (params.SprType_Code == 'llo_nom') {
            params.DrugNomenOrgLink_Org_id = getGlobalOptions().minzdrav_org_id;
        }

        params.no_rmz = base_form.findField('no_rmz').checked ? 1 : 0;
		params.start = 0;
		params.limit = 100;

		form.DrugNomenGrid.loadData({params: params, globalFilters: params});
	},
	doReset: function() {
		var form = this;
		var base_form = form.FilterPanel.getForm();
		base_form.reset();
        this.setDefaultSprType();
	},
	initComponent: function() {
		var form = this;

        form.sprtype_combo = new sw.Promed.SwBaseLocalCombo({
            hiddenName: 'SprType_Code',
            valueField: 'SprType_Code',
            displayField: 'SprType_Name',
            fieldLabel: 'Тип справочника',
            allowBlank: false,
            editable: false,
            width: 190,
            store: new Ext.data.SimpleStore({
                key: 'SprType_id',
                autoLoad: false,
                fields: [
                    {name: 'SprType_id', type: 'int'},
                    {name: 'SprType_Code', type: 'string'},
                    {name: 'SprType_Name', type: 'string'}
                ],
                data: [
                    [1, 'reg_nom', 'Региональная номенклатура'],
                    [2, 'org_nom', 'Номенклатура организации'],
                    [3, 'llo_nom', 'Номенклатура ЛЛО']
                ]
            }),
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '{SprType_Name}&nbsp;',
                '</div></tpl>'
            ),
            listeners: {
                select: function() {
                    form.setSprType();
                }
            }
        });

		form.PrepClassTree = new Ext.tree.TreePanel({
			animate: false,
			autoLoad: false,
			autoScroll: true,
			border: true,
			enableDD: false,
			getLoadTreeMask: function(MSG) {
				if ( MSG )  {
					delete(this.loadMask);
				}

				if ( !this.loadMask ) {
					this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: MSG });
				}

				return this.loadMask;
			},
			loader: new Ext.tree.TreeLoader( {
				listeners: {
					'beforeload': function (tl, node) {
						if (!form.mode)
						{
							return false;
						}
						form.PrepClassTree.getLoadTreeMask(lang['zagruzka_dereva_klassov_preparatov']).show();

						tl.baseParams.level = node.getDepth();
						tl.baseParams.mode = form.mode;
						if ( node.getDepth() > 0 ) {
							tl.baseParams.PrepClass_pid = node.attributes.object_value;
						} else {
							tl.baseParams.PrepClass_pid = null;
						}
					},
					'load': function(node) {
						callback: {
							var mynode, child;
							if (node.baseParams.level == 0) {
								if (form.mode == 'lab') {
									mynode = form.PrepClassTree.getRootNode();
									child = mynode.findChild('object_value', '10');
									form.PrepClassTree.fireEvent('click', child);
								} else {
									mynode = form.PrepClassTree.getRootNode();
									child = mynode.firstChild;
									form.PrepClassTree.fireEvent('click', child);
								}
							}
							form.PrepClassTree.getLoadTreeMask().hide();
						}
					}
				},
				dataUrl:'/?c=DrugNomen&m=loadPrepClassTree'
			}),
			region: 'west',
			root: {
				nodeType: 'async',
				text: lang['klassyi_nomenklaturyi'],
				id: 'root',
				expanded: true
			},
			rootVisible: false,
			selModel: new Ext.tree.KeyHandleTreeSelectionModel(),
			split: true,
			title: lang['klassyi_nomenklaturyi'],
			width: 300
		});
		
		// Двойной клик на ноде выполняет соответствующий акшен
		form.PrepClassTree.on('dblclick', function(node, event) {
			var tree = node.getOwnerTree();
		});

		form.PrepClassTree.getSelectionModel().on('selectionchange', function(sm, node) {
			form.onTreeSelect(sm, node);
		});

		//По наименованию
		form.FilterNaimPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 140,
			border: false,
			frame: true,
			items: [
                form.sprtype_combo,
            {
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'textfield',
						fieldLabel: lang['kod'],
						name: 'DrugNomen_Code',
						width: 120
					}, {
						xtype: 'textfield',
						fieldLabel: lang['kod'],
						name: 'DrugNomenOrgLink_Code',
						width: 120
					}]
				}, {
					layout: 'form',
					labelWidth: 125,
					items: [{
						xtype: 'textfield',
						fieldLabel: 'Код групп. торг.',
						name: 'DrugPrepFasCode_Code',
						width: 120
					}]
				}, {
					layout: 'form',
					labelWidth: 125,
					items: [{
						xtype: 'textfield',
						fieldLabel: lang['kod_kompl_mnn'],
						name: 'DrugComplexMnnCode_Code',
						width: 120
					}]
				}]
			}, {
				fieldLabel: 'МНН',
				anchor: '80%',
				name: 'RlsActmatters_RusName',
				xtype: 'textfield'
				/*hiddenName: 'Actmatters_id',
				xtype: 'swrlsactmatterscombo'*/
			}, {
				fieldLabel: lang['torg_naimenovanie'],
				anchor: '80%',
				name: 'RlsTorg_Name',
				xtype: 'textfield'
				/*hiddenName: 'Tradenames_id',
				xtype: 'swrlstradenamescombo'*/
			}, {
				fieldLabel: lang['forma_vyipuska'],
				anchor: '80%',
				name: 'RlsClsdrugforms_Name',
				xtype: 'textfield'
				/*hiddenName: 'Clsdrugforms_id',
				xtype: 'swrlsclsdrugformscombo'*/
			}, {
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
                xtype: 'checkbox',
                fieldLabel: lang['ne_svyazanyi_s_rzn'],
                name: 'no_rmz'
			}]
				}, {
					layout: 'form',
                    labelWidth: 482,
					items: [{
						xtype: 'combo',
						fieldLabel: langs('Связаны со справочником медикаментов'),
						hiddenName: 'rls_drug_link',
                        displayField: 'Name',
                        valueField: 'Code',
                        editable: false,
                        width: 120,
                        mode: 'local',
                        forceSelection: true,
                        triggerAction: 'all',
						allowBlank: false,
						value: 'all',
                        store: new Ext.data.SimpleStore({
                            id: 0,
                            fields: [
                                'Code',
                                'Name'
                            ],
                            data: [
                                ['all', langs('Все')],
                                ['yes', langs('Да')],
                                ['no', langs('Нет')]
                            ]
                        })
					}]
				}]
			}]
		});
		//По классификации
		form.FilterClassPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 140,
			border: false,
			frame: true,

			items: [{
				xtype: 'swrlsclspharmagroupremotecombo',
				width: 500,
				anchor: '80%',
				fieldLabel: lang['farmgruppa'],
				hiddenName: 'CLSPHARMAGROUP_ID'
			}, {
				xtype: 'swrlsclsatcremotecombo',
				width: 500,
				anchor: '80%',
				fieldLabel: lang['ath'],
				hiddenName: 'CLSATC_ID'
			}, {
				xtype: 'swrlsclsmzphgroupremotecombo',
				width: 500,
				anchor: '80%',
				fieldLabel: lang['ftg'],
				hiddenName: 'CLS_MZ_PHGROUP_ID'
			}, {
				xtype: 'swrlsstronggroupscombo',
				fieldLabel: lang['silnodeystvuyuschie'],
				hiddenName: 'STRONGGROUPS_ID'
			}, {
				xtype: 'swrlsnarcogroupscombo',
				fieldLabel: lang['narkoticheskie'],
				hiddenName: 'NARCOGROUPS_ID'
			}]
		});
		//По производителю
		form.FilterProducerPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 140,
			border: false,
			frame: true,

			items: [{
				xtype: 'swrlsfirmscombo',
				fieldLabel: lang['firma'],
				hiddenName: 'FIRMS_ID'
			}, {
				xtype: 'swrlscountrycombo',
				fieldLabel: lang['strana'],
				hiddenName: 'COUNTRIES_ID'
			}]
		});

		form.DrugNomenTabs = new Ext.TabPanel({
			id: 'DNSW_DrugNomenTabsPanel',
			autoScroll: true,
			activeTab: 0,
			border: true,
			resizeTabs: true,
			region: 'north',
			enableTabScroll: true,
			height: 185,
			minTabWidth: 120,
			tabWidth: 'auto',
			layoutOnTabChange: true,
			items:[
				{
					title: lang['po_naimenovaniyu'],
					layout: 'fit',
					border:false,
					items: [form.FilterNaimPanel]
				},
				{
					title: lang['po_klassifikatsii'],
					layout: 'fit',
					border:false,
					items: [form.FilterClassPanel]
				},
				{
					title: lang['po_proizvoditelyu'],
					layout: 'fit',
					border:false,
					items: [form.FilterProducerPanel]
				}
			]
		});

		form.FilterButtonsPanel = new sw.Promed.Panel({
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,

			items: [{
				layout: 'column',
				items: [{
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						id: 'DNSW_BtnSearch',
						text: lang['nayti'],
						iconCls: 'search16',
						minWidth: 100,
						handler: function()
						{
							form.doSearch();
						}.createDelegate(this)
					}]
				}, {
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						id: 'DNSW_BtnReset',
						text: lang['sbros'],
						iconCls: 'reset16',
						minWidth: 100,
						handler: function()
						{
							form.doReset();
                            form.setDefaultSprType();
							form.doSearch();
						}.createDelegate(this)
					}]
				}]
			}]
		});

		form.FilterPanel = getBaseFiltersFrame({
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: form,
			toolBar: form.WindowToolbar,
			items: [
				form.DrugNomenTabs,
				form.FilterButtonsPanel
			]
		});

		form.DrugNomenGrid = new sw.Promed.ViewFrame( {
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			editformclassname: 'swDrugNomenEditWindow',
			dataUrl: '/?c=DrugNomen&m=loadDrugNomenGrid',
			id: form.id + 'DrugNomenGrid',
			object: 'DrugNomen',
			scheme: 'rls',
            setCodeColumnHidden: function() { //устанавливаем отображение поля с кодом
                if (this.Code_Column == 'DrugNomenOrgLink_Code') {
                    this.setColumnHidden('DrugNomen_Code', true);
                    this.setColumnHidden('DrugNomenOrgLink_Code', false);
                } else {
                    this.setColumnHidden('DrugNomen_Code', false);
                    this.setColumnHidden('DrugNomenOrgLink_Code', true);
                }
            },
			onLoadData: function() {
                this.getAction('action_add').setDisabled(form.action == 'view' || this.readOnly);
                this.getAction('action_edit').setDisabled(form.action == 'view' || this.readOnly);
                this.getAction('action_delete').setDisabled(form.action == 'view' || this.readOnly);
                this.setCodeColumnHidden();
			},
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{ name: 'DrugNomen_id', type: 'int', header: 'ID', key: true },
				{ name: 'DrugNomen_Code', header: 'Код', width: 80 },
				{ name: 'DrugNomen_Name', header: 'Наименование', width: 80 },
				{ name: 'DrugNomenOrgLink_Code', header: 'Код', width: 80 },
				{ name: 'DrugPrepFasCode_Code', header: 'Код гр. торг.', width: 80 },
                { name: 'DrugMnnCode_Code', header: 'Код МНН', width: 80, hidden: true},
                { name: 'DrugTorgCode_Code', header: 'Код торг. наим.', width: 80 },
				{ name: 'DrugComplexMnnCode_Code', header: lang['kod_kompl_mnn'], width: 100 },
				{ name: 'DrugRPN_id', header: lang['kod_rzn'], width: 100 },
				{ name: 'DrugComplexMnn_RusName', header: lang['kompleksnoe_mnn'], width: 180},
				{ name: 'DrugComplexMnnCode_DosKurs', editor: new Ext.form.TextField(), header: 'Кол-во уп. в месяц', width: 180 },
				{ name: 'ActMatters_RusName', header: 'МНН', width: 180, hidden: true },
				{ name: 'RlsTorg_Name', header: lang['torgovoe_naimenovanie'], width: 180, hidden: true },
				{ name: 'RlsClsdrugforms_Name', header: lang['lekarstvennaya_forma'], width: 180, hidden: true },
				{ name: 'Drug_Dose', header: lang['dozirovka'], width: 100, hidden: true },
				{ name: 'Drug_Fas', header: lang['fasovka'], width: 100, hidden: true },
				{ name: 'Reg_Num', header: lang['ru'], width: 140 },
				{ name: 'Reg_Firm', header: lang['derjatel_vladelets_ru'], width: 140 },
				{ name: 'Reg_Country', header: lang['strana_derjatelya_vladeltsa_ru'], width: 140 },
				{ name: 'Reg_Period', header: lang['period_deystviya_ru'], width: 140 },
				{ name: 'Reg_ReRegDate', header: lang['data_pereoformleniya_ru'], width: 140 },
				{ name: 'Drug_Ean', header: lang['kod_ean'], width: 140 },
				{ name: 'DrugPack_Name', header: lang['upakovka'], width: 140 },
				{ name: 'PrepClass_id', hidden: true, type: 'int' }
			],
			totalProperty: 'totalCount'
		});

		form.rightPanel = new Ext.Panel({
			items: [
				form.FilterPanel,
				form.DrugNomenGrid
			],
			title: lang['nomenklaturyi'],
			layout: 'border',
			region: 'center',
			listeners: {
				'resize': function() {
					if(this.layout.layout)
						this.doLayout();
				}
			},
			split: true
		});

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					form.hide();
				},
				iconCls: 'cancel16',
				// tabIndex: -1,
				text: BTN_FRMCLOSE,
				tooltip: lang['zakryit']
			}],
			defaults: {
				split: true
			},
			layout: 'border',
			region: 'center',

			items: [
				 form.PrepClassTree
				,form.rightPanel
			]
		});

		sw.Promed.swDrugNomenSprWindow.superclass.initComponent.apply(this, arguments);
	},
	isUserFace: function() {
		return (!isAdmin && getGlobalOptions().CurMedStaffFact_id && getGlobalOptions().CurLpuSection_id && getGlobalOptions().CurLpuSectionProfile_id);
	},
	layout: 'border',
	loadTree: function () {
		var node = this.PrepClassTree.getSelectionModel().selNode;

		if ( node ) {
			if ( node.parentNode ) {
				node = node.parentNode;
			}
		}
		else {
			node = this.PrepClassTree.getRootNode();
		}

		if ( node ) {
			if ( node.isExpanded() ) {
				node.collapse();
				this.PrepClassTree.getLoader().load(node);
			}
			node.expand();
			//this.PrepClassTree.getRootNode().collapse();
			// Выбираем первую ноду и эмулируем клик 
			node.select();
			this.PrepClassTree.fireEvent('click', node);
		}
	},
	maximized: true,
	maximizable: false,
	mode: null,
	// функция выбора элемента дерева 
	onTreeSelect: function(sm, node) {
		if ( !node ) {
			return false;
		}

		/*if ( node.attributes.leaf == 0 ) {
			return false;
		}*/

		var prep_class_id = node.attributes.object_value;
		
		if (Ext.isEmpty(prep_class_id)) {
			return false;
		}

		this.doReset();
        this.setDefaultSprType();
		this.doSearch(prep_class_id);
	},
	reloadPrepClassTree: function(reload) {
		var tree = this.PrepClassTree;
		var root = tree.getRootNode();

		root.select();
		//tree.fireEvent('click', root);
		tree.getLoader().load(root);
		root.expand();
		root.select();

		if ( reload ) {
			this.onTreeSelect(tree.getSelectionModel(), root);
		}
		
		//this.PrepClassTree.fireEvent('click', root);
	},
	shim: false,
	/** Данный метод вызывается при открытии формы.
	* @param - {Object} массив содержащий входные функции и переменные
	*/
	show: function() {
		sw.Promed.swDrugNomenSprWindow.superclass.show.apply(this, arguments);

		var base_form = this.FilterPanel.getForm();

		this.action = 'edit';
		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0] && arguments[0].mode) {
			this.mode = arguments[0].mode;
		} else {
			this.mode = 'all';
			//this.mode = 'common';
		}
		if (arguments[0] && arguments[0].readOnly !== undefined) {
			this.readOnly = arguments[0].readOnly;
		} else {
			this.readOnly = true;
		}

		if (haveArmType('minzdravdlo') || haveArmType('adminllo') || haveArmType('superadmin')) {
			this.action = 'edit';
			this.readOnly = false;
		}

		base_form.reset();
        this.setDefaultSprType();
		this.DrugNomenGrid.removeAll();

		this.DrugNomenGrid.setReadOnly(this.readOnly);

		this.DrugNomenTabs.setActiveTab(2);
		this.DrugNomenTabs.setActiveTab(1);
		this.DrugNomenTabs.setActiveTab(0);

		base_form.findField('CLSATC_ID').getStore().load({params: {maxCodeLength: 5}});
		base_form.findField('CLSPHARMAGROUP_ID').getStore().load();
		base_form.findField('CLS_MZ_PHGROUP_ID').getStore().load();

        if (getRegionNick() == 'vologda') {
            base_form.findField('rls_drug_link').showContainer();
            this.DrugNomenGrid.setColumnHidden('DrugNomen_Name', false);
		} else {
            base_form.findField('rls_drug_link').hideContainer();
            this.DrugNomenGrid.setColumnHidden('DrugNomen_Name', true);
		}

        this.DrugNomenGrid.setActionDisabled('action_add', this.action == 'view' || this.readOnly);

		this.PrepClassTree.getRootNode().select();
		this.loadTree();
	},
	title: lang['nomenklaturnyiy_spravochnik']
});
