/**
* swEvnOnkoNotifyListWindow - Журнал Извещений об онкобольных 
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @access      public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
* @comment      Префикс для id компонентов EONLW (EvnOnkoNotifyListWindow)
*
*/
sw.Promed.swEvnOnkoNotifyListWindow = Ext.extend(sw.Promed.BaseForm, {codeRefresh: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	getButtonSearch: function() {
		return Ext.getCmp('EONLW_SearchButton');
	},
	doReset: function() {
		
		var base_form = this.getFilterForm().getForm();
		base_form.reset();
		this.RootViewFrame.ViewActions.open_emk.setDisabled(true);
		this.RootViewFrame.ViewActions.person_register_include.setDisabled(true);
		this.RootViewFrame.ViewActions.onko_person_register_not_include.setDisabled(true);
		this.RootViewFrame.ViewActions.action_view.setDisabled(true);
		this.RootViewFrame.ViewActions.action_refresh.setDisabled(true);
		this.RootViewFrame.getGrid().getStore().removeAll();
				
	},
	doSearch: function(params) {
		
		var base_form = this.getFilterForm().getForm();
		
		if (typeof params != 'object') {
			params = {};
		}
		if ( !params.firstLoad && this.findById('EvnOnkoNotifyListFilterForm').isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			return false;
		}
		
		var grid = this.RootViewFrame.getGrid();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					//
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if ( base_form.findField('PersonPeriodicType_id').getValue().toString().inlist([ '2', '3' ]) && (typeof params != 'object' || !params.ignorePersonPeriodicType ) ) {
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
				msg: lang['vyibran_tip_poiska_cheloveka'] + (base_form.findField('PersonPeriodicType_id').getValue() == 2 ? lang['po_sostoyaniyu_na_moment_sluchaya'] : lang['po_vsem_periodikam']) + lang['pri_vyibrannom_variante_poisk_rabotaet_znachitelno_medlennee_hotite_prodoljit_poisk'],
				title: lang['preduprejdenie']
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.findById('EvnOnkoNotifyListFilterForm'));

		post.limit = 100;
		post.start = 0;

        if (String(getGlobalOptions().groups).indexOf('OnkoRegistry', 0) < 0) {
            // отображать список Извещений/Протоколов, созданных только данным пользователем
            post.isOnlyTheir = 1;
        }
		
		//log(post);

		if ( base_form.isValid() ) {
			this.RootViewFrame.ViewActions.action_refresh.setDisabled(false);
			grid.getStore().removeAll();
			grid.getStore().load({
				callback: function(records, options, success) {
					loadMask.hide();
				},
				params: post
			});
		}
        return true;
	},
	height: 550,
	openEvnOnkoNotifyNeglectedWindow: function() {
        if (getWnd('swEvnOnkoNotifyNeglectedEditWindow').isVisible()) {
            getWnd('swEvnOnkoNotifyNeglectedEditWindow').hide();
        }
        var grid = this.RootViewFrame.getGrid();
        var params = {};
        params.action = 'edit';
        params.callback = function(data) { };
        var selected_record = grid.getSelectionModel().getSelected();
        if (selected_record && selected_record.data.EvnOnkoNotifyNeglected_id) {
            params.formParams = selected_record.data;
            params.EvnOnkoNotifyNeglected_id = selected_record.data.EvnOnkoNotifyNeglected_id;
            getWnd('swEvnOnkoNotifyNeglectedEditWindow').show(params);
        }
	},
	getRecordsCount: function() {
		var base_form = this.getFilterForm().getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.alert(lang['poisk'], lang['proverte_pravilnost_zapolneniya_poley_na_forme_poiska']);
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет подсчет записей..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.getFilterForm());
		post.AttachLpu_id = base_form.findField('AttachLpu_id').getValue();
		//post.LpuAttachType_id=(isUserGroup("NarkoRegistry"))?base_form.findField('LpuAttachType_id').getValue():1;

		if ( post.PersonPeriodicType_id == null ) {
			post.PersonPeriodicType_id = 1;
		}

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.Records_Count != undefined ) {
						sw.swMsg.alert(lang['podschet_zapisey'], lang['naydeno_zapisey'] + response_obj.Records_Count);
					}
					else {
						sw.swMsg.alert(lang['podschet_zapisey'], response_obj.Error_Msg);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_podschete_kolichestva_zapisey_proizoshli_oshibki']);
				}
			},
			params: post,
			url: C_SEARCH_RECCNT
		});
	},
	commentRenderer: function(v, p, record) {
		if(record.get('EvnOnkoNotify_Comment') != ''){
			return '<a href="#">Текст</a>';
		} else {
			return '';
		}
	},
	initComponent: function() {
		var thas = this;

		this.RootViewFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', hidden: !isUserGroup('OnkoRegistryFullAccess'), text: langs('Редактировать Протокол'), tooltip: langs('Редактировать Протокол'), handler: function() {
                    thas.openEvnOnkoNotifyNeglectedWindow();
                }},
				{name: 'action_view', handler: function() {
					var selected_record = thas.RootViewFrame.getGrid().getSelectionModel().getSelected();
					if (!selected_record) {
						return false;
					}
					var id = selected_record.data.EvnOnkoNotify_id;
					window.open('/?c=EvnOnkoNotify&m=getPrintForm&EvnOnkoNotify_id=' + id, '_blank');
					id = selected_record.data.EvnOnkoNotifyNeglected_id;
					if(id) {
						window.open('/?c=EvnOnkoNotifyNeglected&m=getPrintForm&EvnOnkoNotifyNeglected_id=' + id, '_blank');
					}
                    return true;
				}, hidden: true},// ибо одна доработанная печатная форма лучше двух недоделанных #26936
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_refresh'},
				{name: 'action_print', menuConfig: {
					printObject: {hidden: true},
					printObjectList: {hidden: true},
					printObjectListFull: {hidden: true},
					printObjectListSelected: {hidden: true},
					printOnkoNotify: {name: 'printOnkoNotify', text: langs('Извещение об онкобольном'), handler: function() {
						var selected_record = this.RootViewFrame.getGrid().getSelectionModel().getSelected();
						if (!selected_record || !selected_record.data.EvnOnkoNotify_id) {
							return false;
						}
						var Morbus = selected_record.get('Morbus_id'),
							s;

						if (getRegionNick() == 'kareliya') {
							printBirt({
								'Report_FileName': 'f030grr.rptdesign',
								'Report_Params': '&paramMorbus=' + Morbus,
								'Report_Format': 'pdf'
							});
						} else {
							printBirt({
								'Report_FileName': 'OnkoNotify.rptdesign',
								'Report_Params': '&paramEvnOnkoNotify=' + selected_record.data.EvnOnkoNotify_id,
								'Report_Format': 'pdf'
							});
						}
					}.createDelegate(this)},
					printOnkoNotifyNeglected: {name: 'printOnkoNotifyNeglected', text: langs('Протокол о запущеной форме онкозаболевания'), handler: function() {
						var selected_record = this.RootViewFrame.getGrid().getSelectionModel().getSelected();
						if (!selected_record || !selected_record.data.EvnOnkoNotifyNeglected_id) {
							return false;
						}
						printBirt({
							'Report_FileName': 'OnkoNotifyNeglected.rptdesign',
							'Report_Params': '&paramEvnOnkoNotifyNeglected=' + selected_record.data.EvnOnkoNotifyNeglected_id,
							'Report_Format': 'pdf'
						});
					}.createDelegate(this)}
				}}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			id: 'EONLW_EvnOnkoNotifyListSearchGrid',
			object: 'EvnOnkoNotifyList',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'EvnOnkoNotify_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnOnkoNotifyNeglected_id', type: 'int', hidden: true},
				{name: 'EvnOnkoNotify_pid', type: 'int', hidden: true},
				{name: 'EvnOnkoNotify_setDT', type: 'date', format: 'd.m.Y', header: lang['data_zapolneniya'], width: 120},	
				{name: 'Person_id', type: 'int', hidden: true},			
				{name: 'Server_id', type: 'int', hidden: true},			
				{name: 'PersonEvn_id', type: 'int', hidden: true},	
				{name: 'Morbus_id', type: 'int', hidden: true},
				{name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 120},
				{name: 'Person_Firname', type: 'string', header: lang['imya'], width: 120},
				{name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 120},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['d_r'], width: 90},
				{name: 'Lpu_Nick', type: 'string', header: lang['lpu_prikr'], width: 150},
				{name: 'Lpu_sid', type: 'int', hidden: true},
				{name: 'Lpu_sid_Nick', type: 'string', header: lang['kuda_napravleno'], width: 200},
				{name: 'Diag_Name', type: 'string', header: lang['diagnoz_mkb-10'], width: 150, id: 'autoexpand'},
				{name: 'Diag_id', type: 'int', hidden: true},
				{name: 'OnkoDiag_Name', type: 'string', header: lang['morfologicheskiy_tip_opuholi'], width: 250},
				{name: 'TumorStage_Name', type: 'string', header: lang['stadiya_opuholevogo_protsessa'], width: 150},
				{name: 'IsIncluded', type: 'string', hidden:(getRegionNick() != 'perm'), header: lang['vklyuchen_v_registr'], width: 120},
				{name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y', header: lang['data_vkl_nevkl_v_registr'], width: 180},
				{name: 'EvnNotifyStatus_Name', type: 'string', header: lang['status_izvecheniya'], width: 200},	
				{name: 'EvnOnkoNotify_CommentLink', type: 'string', header: lang['kommentariy'], width: 150},
				{name: 'EvnOnkoNotify_Comment', type: 'string', hidden: true},	
				{name: 'MedPersonal_id', type: 'int', hidden: true},
				{name: 'pmUser_updId', type: 'int', hidden: true}
			],
			toolbar: true,
			totalProperty: 'totalCount', 
			onBeforeLoadData: function() {
				this.getButtonSearch().disable();
			}.createDelegate(this),
			onLoadData: function() {
				this.getButtonSearch().enable();
				this.RootViewFrame.getGrid().getStore().each(function(rec){
					if(!Ext.isEmpty(rec.get('EvnOnkoNotify_Comment'))){
						rec.set('EvnOnkoNotify_CommentLink','<a href="javascript:">Текст</a>');
						rec.commit();
					}
				});
			}.createDelegate(this),
			onRowSelect: function(sm,index,record) {
				var disabled = (Ext.isEmpty(record.get('Person_id')) || Ext.isEmpty(record.get('EvnOnkoNotifyNeglected_id')) || (String(getGlobalOptions().groups).indexOf('OnkoRegistry', 0) < 0));
                //this.getAction('action_edit').setDisabled( Ext.isEmpty(record.get('Person_id')) || Ext.isEmpty(record.get('EvnOnkoNotifyNeglected_id')));
                this.getAction('action_edit').setDisabled(disabled);
                this.getAction('open_emk').setDisabled( Ext.isEmpty(record.get('Person_id')));
                disabled = (Ext.isEmpty(record.get('Person_id')) || Ext.isEmpty(record.get('PersonRegister_setDate')) == false || (String(getGlobalOptions().groups).indexOf('OnkoRegistry', 0) < 0));
				//this.getAction('person_register_include').setDisabled( Ext.isEmpty(record.get('Person_id')) || Ext.isEmpty(record.get('PersonRegister_setDate')) == false );
				//this.getAction('onko_person_register_not_include').setDisabled( Ext.isEmpty(record.get('Person_id')) || Ext.isEmpty(record.get('PersonRegister_setDate')) == false );

				this.getAction('person_register_include').setDisabled(disabled);
				this.getAction('onko_person_register_not_include').setDisabled(disabled);

				var printOnkoNotifyNeglected = this.getAction('action_print').menu.printOnkoNotifyNeglected;

				if (Ext.isEmpty(record.get('EvnOnkoNotifyNeglected_id'))) {
					if (!printOnkoNotifyNeglected.isHidden()) {
						printOnkoNotifyNeglected.hide();
						this.initActionPrint();
					}
				} else {
					if (printOnkoNotifyNeglected.isHidden()) {
						printOnkoNotifyNeglected.show();
						this.initActionPrint();
					}
				}
			},
			onDblClick: function(sm,index,record) {
				this.getAction('action_view').execute();
			}
		});

		this.RootViewFrame.ViewGridPanel.on('cellclick', function(grid, rowIdx, colIdx) {
			var flag_idx = grid.getColumnModel().findColumnIndex('EvnOnkoNotify_CommentLink');
			var rec = grid.getSelectionModel().getSelected();
			if(colIdx == flag_idx){
				if(!rec || Ext.isEmpty(rec.get('EvnOnkoNotify_Comment'))) return false;
				getWnd('swEvnOnkoNotifyNotIncludeCommentWindow').show({
					action:'view',
					EvnOnkoNotify_Comment:rec.get('EvnOnkoNotify_Comment')
				});
			}
		}.createDelegate(this));

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				tabIndex: TABINDEX_EONLW + 120,
				id: 'EONLW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EONLW + 121,
				text: BTN_FRMRESET
			}, /*{
				handler: function() {
					var base_form = this.getFilterForm().getForm();
					var record;
					base_form.findField('MedPersonal_cid').setValue(null);
					if ( base_form.findField('MedStaffFact_cid') ) {
						var med_personal_record = base_form.findField('MedStaffFact_cid').getStore().getById(base_form.findField('MedStaffFact_cid').getValue());

						if ( med_personal_record ) {
							base_form.findField('MedPersonal_cid').setValue(med_personal_record.get('MedPersonal_id'));
						}
					}
					base_form.submit();
				}.createDelegate(this),
				iconCls: 'print16',
				tabIndex: TABINDEX_EONLW + 122,
				text: lang['pechat_spiska']
			},*/ {
				handler: function() {
					this.getRecordsCount();
				}.createDelegate(this),
				// iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EONLW + 123,
				text: BTN_FRMCOUNT
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					this.buttons[this.buttons.length - 2].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.findById('EONLW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('EONLW_SearchFilterTabbar').getActiveTab());
				}.createDelegate(this),
				tabIndex: TABINDEX_EONLW + 124,
				text: BTN_FRMCLOSE
			}],
			getFilterForm: function() {
				if ( this.filterForm == undefined ) {
					this.filterForm = this.findById('EvnOnkoNotifyListFilterForm');
				}
				return this.filterForm;
			},
			menuPrintForm: null,
			createMenuPrintForm: function() {
				this.menuPrintForm = new Ext.menu.Menu({id: 'menuPrintForm'});
				this.menuPrintForm.add({text: lang['izveschenie_ob_onkobolnom'], iconCls: '', handler: function() {
					var selected_record = this.RootViewFrame.getGrid().getSelectionModel().getSelected();
					if (!selected_record || !selected_record.data.EvnOnkoNotify_id) {
						return false;
					}
                    var Morbus = selected_record.get('Morbus_id'),
                        s;

                    if (getRegionNick() == 'kareliya') {
						printBirt({
							'Report_FileName': 'f030grr.rptdesign',
							'Report_Params': '&paramMorbus=' + Morbus,
							'Report_Format': 'pdf'
						});
                    } else {
						printBirt({
							'Report_FileName': 'OnkoNotify.rptdesign',
							'Report_Params': '&paramEvnOnkoNotify=' + selected_record.data.EvnOnkoNotify_id,
							'Report_Format': 'pdf'
						});
                    }
				}.createDelegate(this)});
				this.menuPrintForm.add({text: lang['protokol_o_zapuschennoy_forme_onkozabolevaniya'], iconCls: '', handler: function() {
					var selected_record = this.RootViewFrame.getGrid().getSelectionModel().getSelected();
					if (!selected_record || !selected_record.data.EvnOnkoNotifyNeglected_id) {
						return false;
					}
					printBirt({
						'Report_FileName': 'OnkoNotifyNeglected.rptdesign',
						'Report_Params': '&paramEvnOnkoNotifyNeglected=' + selected_record.data.EvnOnkoNotifyNeglected_id,
						'Report_Format': 'pdf'
					});
				}.createDelegate(this)});
				/*var a = this.RootViewFrame.getAction('action_print');
				a.items[0].menu = this.menuPrintForm;
				a.items[1].menu = this.menuPrintForm;*/
			},
			items: [ getBaseSearchFiltersFrame({
				allowPersonPeriodicSelect: true,
				id: 'EvnOnkoNotifyListFilterForm',
				labelWidth: 130,
				ownerWindow: this,
				searchFormType: 'EvnOnkoNotify',
				tabIndexBase: TABINDEX_EONLW,
				tabPanelHeight: 225,
				tabPanelId: 'EONLW_SearchFilterTabbar',
				tabs: [{
					id: 'EONLW_EvnOnkoNotifyTab',
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					labelWidth: 220,
					layout: 'form',
					listeners: {
						'activate': function(panel) {
							var form = this.getFilterForm().getForm();
							form.findField('Diag_Code_From').focus(250, true);
						}.createDelegate(this)
					},
					title: lang['6_izveschenie'],
					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',									
							items: [{
								fieldLabel: lang['kod_diagnoza_s'],
								hiddenName: 'Diag_Code_From',
								listWidth: 620,
								valueField: 'Diag_Code',
								width: 290,
                                MorbusType_SysNick: 'onko',
								xtype: 'swdiagcombo'
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 35,
							items: [{
								fieldLabel: lang['po'],
								hiddenName: 'Diag_Code_To',
								listWidth: 620,
								valueField: 'Diag_Code',
								width: 290,
                                MorbusType_SysNick: 'onko',
								xtype: 'swdiagcombo'
							}]
						}]
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',									
							items: [{
								fieldLabel: lang['morfologicheskiy_tip_opuholi_s'],
								hiddenName: 'OnkoDiag_Code_From',
								listWidth: 620,
								width: 290,
								xtype: 'swonkodiagcombo'
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 35,
							items: [{
								fieldLabel: lang['po'],
								hiddenName: 'OnkoDiag_Code_To',
								listWidth: 620,
								width: 290,
								xtype: 'swonkodiagcombo'
							}]
						}]
					}, {
						displayField: 'Lpu_Nick',
						allowBlank: true,
						editable: false,
						enableKeyEvents: true,
						fieldLabel: lang['lpu_kuda_napravleno_izveschenie'],
						hiddenName: 'Lpu_sid',
						listeners: {
							'keydown': function( inp, e ) {
								if ( inp.disabled )
									return;

								if ( e.F4 == e.getKey() ) {
									if ( e.browserEvent.stopPropagation )
										e.browserEvent.stopPropagation();
									else
										e.browserEvent.cancelBubble = true;

									if ( e.browserEvent.preventDefault )
										e.browserEvent.preventDefault();
									else
										e.browserEvent.returnValue = false;

									e.returnValue = false;

									if ( Ext.isIE ) {
										e.browserEvent.keyCode = 0;
										e.browserEvent.which = 0;
									}

									inp.onTrigger1Click();

									return false;
								}
							},
							'keyup': function(inp, e) {
								if ( e.F4 == e.getKey() ) {
									if ( e.browserEvent.stopPropagation )
										e.browserEvent.stopPropagation();
									else
										e.browserEvent.cancelBubble = true;

									if ( e.browserEvent.preventDefault )
										e.browserEvent.preventDefault();
									else
										e.browserEvent.returnValue = false;

									e.returnValue = false;

									if ( Ext.isIE ) {
										e.browserEvent.keyCode = 0;
										e.browserEvent.which = 0;
									}

									return false;
								}
							}
						},
						mode: 'local',
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'Lpu_id', type: 'int' },
								{ name: 'Lpu_Nick', type: 'string' },
								{ name: 'LpuType_Code', type: 'int' }
							],
							key: 'Lpu_id',
							sortInfo: {
								field: 'Lpu_Nick'
							},
							url: C_GETOBJECTLIST
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{Lpu_Nick}',
							'</div></tpl>'
						),
						listWidth: 620,
						width: 620,
						valueField: 'Lpu_id',
						xtype: 'swbaselocalcombo'
					}, {
						fieldLabel: lang['data_sozdaniya_izvescheniya'],
						name: 'EvnNotifyBase_setDT_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 170,
						xtype: 'daterangefield'
					}, {
						fieldLabel: lang['sostavlen_protokol'],
						xtype: 'swyesnocombo',
						hiddenName: 'isNeglected'
					}, {
						fieldLabel: lang['stadiya_opuholevogo_protsessa'],
						hiddenName: 'TumorStage_id',
						xtype: 'swcommonsprlikecombo',
						sortField:'TumorStage_Code',
						comboSubject: 'TumorStage'
					}, {
						fieldLabel: lang['obstoyatelstva_vyiyavleniya_opuholi'],
						hiddenName: 'TumorCircumIdentType_id',
						xtype: 'swcommonsprlikecombo',
						sortField:'TumorCircumIdentType_Code',
						comboSubject: 'TumorCircumIdentType'
					}, {
						fieldLabel: lang['status_izvecheniya'],
						mode: 'local',
						store: new Ext.data.SimpleStore(
						{
							key: 'Status_id',
							fields:
							[
								{name: 'Status_id', type: 'int'},
								{name: 'Status_Name', type: 'string'}
							],
							data: [
								[1,'Отправлено'], 
								[2,'Включено в регистр'], 
								[3,'Отклонено (ошибка в Извещении)'],
								[4,'Отклонено (решение оператора)']
							]
						}),
						editable: false,
						listWidth: 220,
						triggerAction: 'all',
						displayField: 'Status_Name',
						valueField: 'Status_id',
						tpl: '<tpl for="."><div class="x-combo-list-item">{Status_Name}</div></tpl>',
						hiddenName: 'EvnNotifyStatus_id',
						xtype: 'combo'
					}, {
						layout:'form',
						border:false,
						items:[{
							xtype: 'swyesnocombo',
							hiddenName: 'IsIncluded',
							fieldLabel: lang['vklyuchen_v_registr']
						}]
					}]
				}]
			}),
			this.RootViewFrame]
		});
		
		sw.Promed.swEvnOnkoNotifyListWindow.superclass.initComponent.apply(this, arguments);
		
	},
	layout: 'border',
	listeners: {
		'beforeShow': function(win) {
			/*if (String(getGlobalOptions().groups).indexOf('OnkoRegistry', 0) < 0)
			{
				sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «Регистр по онкологии»');
				return false;
			}*/
		},
		'hide': function(win) {
			win.doReset();
		},
		'maximize': function(win) {
			win.getFilterForm().doLayout();
		},
		'restore': function(win) {
			win.getFilterForm().doLayout();
		},
        'resize': function (win, nW, nH, oW, oH) {
			win.findById('EONLW_SearchFilterTabbar').setWidth(nW - 5);
			win.getFilterForm().setWidth(nW - 5);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swEvnOnkoNotifyListWindow.superclass.show.apply(this, arguments);
		
		this.RootViewFrame.addActions({
			name:'onko_person_register_not_include', 
			text:lang['ne_vklyuchat_v_registr'], 
			tooltip: lang['ne_vklyuchat_v_registr'],
			hidden: !isUserGroup('OnkoRegistryFullAccess'),
			iconCls: 'reset16',
			menu: new Ext.menu.Menu({id:'personRegisterNotIncludeMenu'})
		});
		
		this.RootViewFrame.addActions({
			name:'person_register_include', 
			text:lang['vklyuchit_v_registr'], 
			tooltip: lang['vklyuchit_v_registr'],
			hidden: !isUserGroup('OnkoRegistryFullAccess'),
			iconCls: 'ok16',
			handler: function() {
				this.personRegisterInclude();
			}.createDelegate(this)
		});
		
		this.RootViewFrame.addActions({
			name:'open_emk', 
			text:lang['otkryit_emk'], 
			tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'],
			iconCls: 'open16',
			handler: function() {
				this.emkOpen(!isUserGroup('OnkoRegistryFullAccess'));
			}.createDelegate(this)
		});
		
		var base_form = this.getFilterForm().getForm();

		this.restore();
		this.center();
		this.maximize();
		this.doReset();		
		
		if (arguments[0].userMedStaffFact)
		{
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		} else {
			if (sw.Promed.MedStaffFactByUser.last)
			{
				this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
			}
			else
			{
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: arguments[0].ARMType,
					onSelect: function(data) {
						this.userMedStaffFact = data;
					}.createDelegate(this)
				});
			}
		}
		
		base_form.findField('Lpu_sid').getStore().load({
			params: { object: 'Lpu' },
			callback: function () {
				var combo = base_form.findField('Lpu_sid');
				combo.getStore().clearFilter();
				combo.lastQuery = '';
				combo.getStore().filterBy(function(record) 
				{
					if ( record.get('LpuType_Code') == 30 || record.get('LpuType_Code') == 43 ) {
						return true;
					} else {
						return false;
					}
				});
			}
		});

		base_form.findField('EvnNotifyStatus_id').setValue(1);
		if(getRegionNick() == 'perm'){
			base_form.findField('IsIncluded').ownerCt.show();
		} else {
			base_form.findField('IsIncluded').ownerCt.hide();
		}
		this.getFilterForm().mainFilters.setActiveTab('EONLW_EvnOnkoNotifyTab');
		
		this.doLayout();
		this.setMenu(true);
		this.doSearch({firstLoad: true});
	},
	/** Создание меню
	 */
	setMenu: function(is_first) {
		if (is_first) {
			this.createPersonRegisterFailIncludeCauseMenu();
			//this.createMenuPrintForm();
		}
	},
	/** Создание меню причин не включения в регистр
	 */
	createPersonRegisterFailIncludeCauseMenu: function() {
		sw.Promed.personRegister.createPersonRegisterFailIncludeCauseMenu({
			id: 'personRegisterNotIncludeMenu',
			ownerWindow: this,
			getParams: function(){
				var record = this.RootViewFrame.getGrid().getSelectionModel().getSelected();
				if ( !record || !record.get('Person_id') )
				{
					Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
					return false;
				}
				return {
					EvnNotifyBase_id: record.get('EvnOnkoNotify_id'),
					RegisterType: 'onko'
				};
			}.createDelegate(this),
			onCreate: function(menu){
				var a = this.RootViewFrame.getAction('onko_person_register_not_include');
				a.items[0].menu = menu;
				a.items[1].menu = menu;
			}.createDelegate(this),
			callback: function(){
				this.RootViewFrame.getAction('action_refresh').execute();
			}.createDelegate(this)
		});
	},
	/** Включить в регистр
	 */
	personRegisterInclude: function()
	{
		var grid = this.RootViewFrame.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if ( !record || !record.get('Person_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var params = {
			EvnNotifyBase_id: record.get('EvnOnkoNotify_id'),
			Person_id: record.get('Person_id'),
			Diag_id: record.get('Diag_id'),
            MorbusType_SysNick: 'onko',
			Morbus_id: record.get('Morbus_id'),
			PersonRegisterType_SysNick: 'onko',
			ownerWindow: this,
			callback: function () {
				grid.getStore().reload();
			}
		};
		sw.Promed.personRegister.include(params);
	},
	emkOpen: function(readOnly)
	{
		var grid = this.RootViewFrame.getGrid();

		var record = grid.getSelectionModel().getSelected();
		if ( !record || !record.get('Person_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		
		getWnd('swPersonEmkWindow').show({
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			userMedStaffFact: this.userMedStaffFact,
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			readOnly: readOnly || getWnd('swWorkPlaceMZSpecWindow').isVisible()?true:false,
			ARMType: 'common',
			callback: function()
			{
				//
			}.createDelegate(this)
		});
	},
	title: lang['jurnal_izvescheniy_ob_onkobolnyih'],
	width: 800
});