/**
* swPersonCardSearchWindow - окно поиска в картотеке пациентов (ЕРПН: Поиск)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      01.06.2009
*/

sw.Promed.swPersonCardSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	addPersonCard: function() {
		var current_window = this;

		if ( getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно поиска человека уже открыто');
			return false;
		}

		if ( getWnd('swPersonCardEditWindow').isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования карты пациента уже открыто');
			return false;
		}

		getWnd('swPersonSearchWindow').show({
			onClose: function() {
				current_window.refreshPersonCardViewGrid();
			},
			onSelect: function(person_data) {
				getWnd('swPersonCardEditWindow').show({
					action: 'add',
					callback: function() {
						current_window.refreshPersonCardViewGrid();
					},
					onHide: function() {
						// TODO: Здесь надо будет переделать использование getWnd
						getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 500);
					},
					Person_id: person_data.Person_id,
					PersonEvn_id: person_data.PersonEvn_id,
					Server_id: person_data.Server_id
				});
			},
			searchMode: 'all'
		});
	},
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	deletePersonCard: function() {
		var current_window = this;
		var grid = current_window.findById('PersonCardViewGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();

		if ( !current_row ) {
			return;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' )
				{
					Ext.Ajax.request({
						url: C_PERSONCARD_DEL,
						params: {PersonCard_id: current_row.data.PersonCard_id},
						callback: function() {
							current_window.doSearch();
						}
					});
				}
			},
			msg: 'Вы действительно желаете удалить эту запись?',
			title: 'Подтверждение удаления'
		});
	},
	doReset: function(reset_form_flag) {
		var form = this.findById('PersonCardSearchForm');
		var grid = this.findById('PersonCardViewGrid');

		if ( reset_form_flag == true ) {
			form.getForm().reset();

			if ( form.getForm().findField('AttachLpu_id').getStore().getCount() > 0 ) {
				form.getForm().findField('AttachLpu_id').setValue(Ext.globalOptions.globals.lpu_id);
			}

			form.getForm().findField('AttachLpu_id').fireEvent('change', form.getForm().findField('AttachLpu_id'), form.getForm().findField('AttachLpu_id').getValue());

			form.getForm().findField('LpuRegion_id').lastQuery = '';
			form.getForm().findField('PrivilegeType_id').lastQuery = '';

			form.getForm().findField('LpuRegion_id').getStore().clearFilter();
			form.getForm().findField('LpuRegionType_id').getStore().clearFilter();

			form.getForm().findField('PrivilegeType_id').getStore().filterBy(function(record) {
				if ( record.get('PrivilegeType_Code') <= 500 ) {
					return true;
				}
				else {
					return false;
				}
			});

			form.getForm().findField('PersonCardStateType_id').fireEvent('change', form.getForm().findField('PersonCardStateType_id'), 1, 0);
			form.getForm().findField('PrivilegeStateType_id').fireEvent('change', form.getForm().findField('PrivilegeStateType_id'), 1, 0);
			
			form.getForm().findField('Diag_Code_From').disable();
			form.getForm().findField('Diag_Code_To').disable();
		}

		grid.removeAll();

		if ( !getRegionNick().inlist([ 'kz' ]) ) {
			form.getForm().findField('RegisterSelector_id').clearValue();
			form.getForm().findField('RegisterSelector_id').fireEvent('change', form.getForm().findField('RegisterSelector_id'), null, 1);
		}

		form.findById('PersonCardFilterTabPanel').setActiveTab(0);
		form.getForm().findField('Person_Surname').focus(true, 250);
	},
	searchInProgress: false,
	doSearch: function(params) {
		if (this.searchInProgress) {
			log('Поиск уже выполняется!');
			return false;
		} else {
			this.searchInProgress = true;
		}
		var thisWindow = this;
		if ( params && params['soc_card_id'] )
			var soc_card_id = params['soc_card_id'];
		var form = this.findById('PersonCardSearchForm');

		if ( form.isEmpty() && !(params && params['soc_card_id']) ) {
			sw.swMsg.alert('Ошибка', 'Не заполнено ни одно поле', function() {
				thisWindow.searchInProgress = false;
				Ext.getCmp('PersonCardFilterTabPanel').setActiveTab(0);
				form.getForm().findField('Person_Surname').focus();
			});
			thisWindow.searchInProgress = false;
			return false;
		}

		var grid = this.findById('PersonCardViewGrid').ViewGridPanel;
		var params = form.getForm().getValues();

		var arr = form.find('disabled', true);

		for ( i = 0; i < arr.length; i++ ) {
			if (arr[i].getValue)
				params[arr[i].hiddenName] = arr[i].getValue();
		}
		
		if ( soc_card_id )
		{
			var params = {
				soc_card_id: soc_card_id,
				SearchFormType: params.SearchFormType
			};
		}

		if (!Ext.isEmpty(params.PartMatchSearch) && !Ext.isEmpty(params.PersonCard_Code)) {
			if (params.PersonCard_Code.length < 2) {
				sw.swMsg.alert('Ошибка', 'Поиск по частичному совпадению номера амбулаторной карты не возможен менее чем по 2-м символам');
				thisWindow.searchInProgress = false;
				return false;
			}
		}

		params.start = 0;
		params.limit = 100;
		params.dontShowUnknowns = 1;// #158923 не показывать неизвестных

		grid.getStore().removeAll();
		grid.getStore().baseParams = params;
		grid.getStore().load({
			params: params,
			callback: function(r) {
				thisWindow.searchInProgress = false;
				if ( r.length > 0 ) {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
			}
		});
	},
	draggable: true,
	editPersonCard: function() {
		var current_window = this;
		var grid = current_window.findById('PersonCardViewGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();

		if ( !current_row ) {
			return;
		}

		if ( getWnd('swPersonCardEditWindow').isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования карты пациента уже открыто');
			return false;
		}

		getWnd('swPersonCardEditWindow').show({
			action: 'edit',
			callback: function() {
				current_window.refreshPersonCardViewGrid();
			},
			onHide: function() {
				current_window.refreshPersonCardViewGrid();
			},
			PersonCard_id: current_row.data.PersonCard_id,
			Person_id: current_row.data.Person_id,
			Server_id: current_row.data.Server_id
		});
	},
	getRecordsCount: function() {
		var current_window = this;

		var form = current_window.findById('PersonCardSearchForm');

		if ( !form.getForm().isValid() ) {
			sw.swMsg.alert('Поиск по картотеке', 'Проверьте правильность заполнения полей на форме поиска');
			return false;
		}

		var loadMask = new Ext.LoadMask(Ext.get('PersonCardSearchWindow'), { msg: "Подождите, идет подсчет записей..." });
		loadMask.show();

		var post = getAllFormFieldValues(form);

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.Records_Count != undefined ) {
						sw.swMsg.alert('Подсчет записей', 'Найдено записей: ' + response_obj.Records_Count);
					}
					else {
						sw.swMsg.alert('Подсчет записей', response_obj.Error_Msg);
					}
				}
				else {
					sw.swMsg.alert('Ошибка', 'При подсчете количества записей произошли ошибки');
				}
			},
			params: post,
			url: C_SEARCH_RECCNT
		});
	},
	exportToDBF: function() {
		var loadMask = new Ext.LoadMask(Ext.get("PersonCardSearchWindow"), { msg: 'Подождите. Идет выгрузка картотеки... ' });
		loadMask.show();
		Ext.Ajax.request(
		{
			url: '/?c=PersonCard&m=ExportPCToDBF',
			callback: function(options, success, response) 
			{
				loadMask.hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success ) {
						sw.swMsg.alert('Выгрузка картотеки', '<a target="_blank" href="' + response_obj.url + '">Скачать архив с картотекой</a>');
					}
					else {
						if ( response_obj.Error_Msg ) {
							sw.swMsg.alert('Выгрузка картотеки', response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert('Выгрузка картотеки', 'При формировании архива произошли ошибки');
						}
					}
				}
				else {
					sw.swMsg.alert('Ошибка', 'При выгрузке картотеки произошли ошибки');
				}
			}
		});
	},
	printMedCard: function() {
		var record = this.findById('PersonCardViewGrid').getGrid().getSelectionModel().getSelected();
		var PersonCard_id = (record)?record.get('PersonCard_id'):null;
		if ( PersonCard_id !== null ){
			if (getRegionNick() =='ufa'){
				printMedCard4Ufa(PersonCard_id);
				return;
			}
			if(getRegionNick().inlist([ 'buryatiya', 'astra', 'perm', 'ekb', 'pskov', 'krym', 'khak','penza', 'kaluga'])){
				var PersonCard = 0;
				if(!Ext.isEmpty(PersonCard_id)){
					var PersonCard = PersonCard_id;
				}
				printBirt({
                    'Report_FileName': 'pan_PersonCard_f025u.rptdesign',
                    'Report_Params': '&paramPerson=' + record.get('Person_id') + '&paramPersonCard=' + PersonCard + '&paramLpu=' + getLpuIdForPrint(),
                    'Report_Format': 'pdf'
                });
			} else {
				Ext.Ajax.request(
				{
					url : '/?c=PersonCard&m=printMedCard',
					params : 
					{
						PersonCard_id: PersonCard_id
					},
					callback: function(options, success, response)
					{
						if ( success ) {
							var responseData = Ext.util.JSON.decode(response.responseText);

							if ( getRegionNick() == 'ekb' ) {
								if ( !Ext.isEmpty(responseData.result1) ) {
									openNewWindow(responseData.result1);
								}

								if ( !Ext.isEmpty(responseData.result2) ) {
									openNewWindow(responseData.result2);
								}
							}
							else if ( !Ext.isEmpty(responseData.result) ) {
								openNewWindow(responseData.result);
							}
							else {
								sw.swMsg.alert('Ошибка', 'Ошибка при получении данных для печати');
							}
						}
						else {
							sw.swMsg.alert('Ошибка', 'Ошибка при получении данных для печати');
						}				}
				});
			}
		} else {
			sw.swMsg.alert('Сообщение', 'Не выбран пациент!');
		}
	},
	height: 550,
	id: 'PersonCardSearchWindow',
	initComponent: function() {
		var win = this;

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.doSearch();
				},
				iconCls: 'search16',
				id: 'PCSW_SearchButton',
				tabIndex: TABINDEX_PERSCARDSW + 76,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.ownerCt.doReset(true);
				},
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_PERSCARDSW + 77,
				text: BTN_FRMRESET
			},/* {
				handler: function() {
					var form = this.ownerCt.findById('PersonCardSearchForm');
					if(form.getForm().findField('AttachLpu_id').disabled == true){
						form.getForm().findField('AttachLpu_id').enable();
						this.ownerCt.findById('PersonCardSearchForm').getForm().submit();
						form.getForm().findField('AttachLpu_id').disable();
					}
					else {
						this.ownerCt.findById('PersonCardSearchForm').getForm().submit();
					}

				},
				iconCls: 'print16',
				tabIndex: TABINDEX_PERSCARDSW + 78,
				text: 'Печать'
			}, */{
				handler: function() {
					var params = new Object();
					params.type = 'EvnPL';

					var selected_record = this.findById('PersonCardViewGrid').getGrid().getSelectionModel().getSelected();

					if ( selected_record ) {
						params.personId = selected_record.get('Person_id');
					}

					switch ( getRegionNick() ) {
						case 'ufa':
							getWnd('swEvnPLBlankSettingsWindow').show(params);
						break;

						default:
							printEvnPLBlank(params);
						break;
					}
				}.createDelegate(this),
				iconCls: 'print16',
				tabIndex: TABINDEX_PERSCARDSW + 79,
				text: 'Печать бланка ТАП'
			}, 	{	
				handler: function() {
					this.ownerCt.printMedCard();
				},
				iconCls: 'print16',
				tabIndex: TABINDEX_PERSCARDSW + 80,
				text: 'Печать мед. карты',
				hidden: (getGlobalOptions().region.nick == 'ufa')?false:true
			},	{
				handler: function() {
					this.ownerCt.getRecordsCount();
				},
				// iconCls: 'resetsearch16',
				tabIndex: TABINDEX_PERSCARDSW + 81,
				text: BTN_FRMCOUNT
			},
			{
				handler: function() {
					this.ownerCt.exportToDBF();
				},
				// iconCls: 'resetsearch16',
				tabIndex: TABINDEX_PERSCARDSW + 82,
				text: 'Экспорт картотеки в DBF'
			},
			'-',
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) {
					ShowHelp(WND_POL_PERSCARDSEARCH);
				}.createDelegate(self),
				tabIndex: TABINDEX_PERSCARDSW + 83
			},
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					this.ownerCt.buttons[5].focus();
				},
				onTabAction: function() {
					var current_window = this.ownerCt;
					current_window.findById('PersonCardFilterTabPanel').getActiveTab().fireEvent('activate', current_window.findById('PersonCardFilterTabPanel').getActiveTab());
				},
				tabIndex: TABINDEX_PERSCARDSW + 84,
				text: BTN_FRMCLOSE
			}
			],
			items: [ this.FilterPanel = new Ext.form.FormPanel({
				autoScroll: true,
				autoHeight: true,
				bodyBorder: false,
				collapsible: true,
				collapsed: false,
				floatable: false,
				titleCollapse: false,
				title: '<div>Фильтры</div>',
				// bodyStyle: 'padding: 5px 5px 0',
				border: false,
				buttonAlign: 'left',
				frame: false,
				plugins: [Ext.ux.PanelCollapsedTitle],
				animCollapse: false,
				height: 250,
				id: 'PersonCardSearchForm',
				items: [ new Ext.TabPanel({
					activeTab: 0,
					height: 250,
					// border: false,
					defaults: { bodyStyle: 'padding: 0px' },
					id: 'PersonCardFilterTabPanel',
					layoutOnTabChange: true,
					listeners: {
						'tabchange': function(panel, tab) {
							this.findById('PersonCardSearchForm').fireEvent('expand', this);
						}.createDelegate(this)
					},
					plain: true,
					region: 'north',
					items: [{
						autoHeight: true,
						bodyStyle: 'margin-top: 5px;',
						border: false,
						layout: 'form',
						listeners: {
							'activate': function(panel) {
								panel.ownerCt.ownerCt.getForm().findField('Person_Surname').focus(250, true);
							}
						},
						title: '<u>1</u>. Пациент',

						// tabIndexStart: TABINDEX_PERSCARDSW + 1
						items: [{
							name: 'SearchFormType',
							value: 'PersonCard',
							xtype: 'hidden'
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: 'Фамилия',
									listeners: {
										'keydown': function (inp, e) {
											if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
												e.stopEvent();
												inp.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.buttons[6].focus();
											}
										}
									},
									name: 'Person_Surname',
									tabIndex: TABINDEX_PERSCARDSW + 1,
									width: 200,
									xtype: 'textfieldpmw'
								}, {
									fieldLabel: 'Имя',
									name: 'Person_Firname',
									tabIndex: TABINDEX_PERSCARDSW + 2,
									width: 200,
									xtype: 'textfieldpmw'
								}]
							}, {
								border: false,
								labelWidth: 160,
								layout: 'form',
								items: [{
									fieldLabel: 'Дата рождения',
									name: 'Person_Birthday',
									plugins: [
										new Ext.ux.InputTextMask('99.99.9999', false)
									],
									tabIndex: TABINDEX_PERSCARDSW + 5,
									width: 100,
									xtype: 'swdatefield'
								}, {
									fieldLabel: 'Диапазон дат рождения',
									name: 'Person_Birthday_Range',
									plugins: [
										new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
									],
									tabIndex: TABINDEX_PERSCARDSW + 6,
									width: 170,
									xtype: 'daterangefield'
								}]
							}, {
								border: false,
								width: 65,
								layout: 'form',
								style: 'padding-left: 5px',
								items: [{											
									xtype: 'button',
									hidden: !getGlobalOptions()['card_reader_is_enable'],
									cls: 'x-btn-large',
									iconCls: 'idcard32',
									tooltip: 'Считать с карты',
									handler: function() {
										win.readFromCard();
									}
								}]
							}]
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: 'Отчество',
									name: 'Person_Secname',
									tabIndex: TABINDEX_PERSCARDSW + 3,
									width: 200,
									xtype: 'textfieldpmw'
								}]
							}, {
								border: false,
								labelWidth: 160,
								layout: 'form',
								items: [{
									fieldLabel: 'Номер амб. карты',
									name: 'PersonCard_Code',
									tabIndex: TABINDEX_PERSCARDSW + 7,
									width: 100,
									xtype: 'textfield'
								}]
							}, {
								border: false,
								layout: 'form',
								hidden: getRegionNick() != 'ekb',
								style: 'padding-left: 5px',
								items: [{
									name: 'PartMatchSearch',
									hideLabel: true,
									boxLabel: 'Поиск по частичному совпадению',
									xtype: 'checkbox'
								}]
							}]
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									allowNegative: false,
									// allowDecimals: false,
									fieldLabel: 'Год рождения',
									name: 'PersonBirthdayYear',
									tabIndex: TABINDEX_PERSCARDSW + 4,
									width: 60,
									xtype: 'numberfield'
								}, {
									allowNegative: false,
									// allowDecimals: false,
									fieldLabel: 'Возраст',
									name: 'PersonAge',
									tabIndex: TABINDEX_PERSCARDSW + 10,
									width: 60,
									xtype: 'numberfield'
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									allowNegative: false,
									// allowDecimals: false,
									fieldLabel: 'Год рождения с',
									name: 'PersonBirthdayYear_Min',
									tabIndex: TABINDEX_PERSCARDSW + 8,
									width: 61,
									xtype: 'numberfield'
								}, {
									allowNegative: false,
									// allowDecimals: false,
									fieldLabel: 'Возраст с',
									name: 'PersonAge_Min',
									tabIndex: TABINDEX_PERSCARDSW + 11,
									width: 61,
									xtype: 'numberfield'
								}]
							}, {
								border: false,
								labelWidth: 40,
								layout: 'form',
								items: [{
									allowNegative: false,
									// allowDecimals: false,
									fieldLabel: 'по',
									name: 'PersonBirthdayYear_Max',
									tabIndex: TABINDEX_PERSCARDSW + 9,
									width: 61,
									xtype: 'numberfield'
								}, {
									allowNegative: false,
									// allowDecimals: false,
									fieldLabel: 'по',
									name: 'PersonAge_Max',
									tabIndex: TABINDEX_PERSCARDSW + 12,
									width: 61,
									xtype: 'numberfield'
								}]
							}]
						}, {
							border: false,
							hidden: (getRegionNick() != 'kz'),
							layout: 'form',
							style: 'padding: 0px;',
							items: [{
								allowBlank: true,
								autoCreate: {tag: "input", type: "text", size: "30", maxLength: "12", autocomplete: "off"},
								fieldLabel: 'ИИН',
								maskRe: /\d/,
								maxLength: 12,
								minLength: 12,
								name: 'Person_Inn',
								tabIndex: TABINDEX_PERSCARDSW + 13,
								width: 150,
								xtype: 'textfield'
							}]
						}, {
							autoHeight: true,
							hidden: (getRegionNick() == 'kz'),
							style: 'padding: 0px;',
							title: 'Полис',
							width: 755,
							xtype: 'fieldset',
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										fieldLabel: 'Серия',
										name: 'Polis_Ser',
										tabIndex: TABINDEX_PERSCARDSW + 14,
										width: 100,
										xtype: 'textfield'
									}]
								}, {
									border: false,
									labelWidth: 100,
									layout: 'form',
									items: [{
										fieldLabel: 'Номер',
										maskRe: /[^%]/,
										name: 'Polis_Num',
										tabIndex: TABINDEX_PERSCARDSW + 15,
										width: 100,
										xtype: 'textfield'
									}]
								}, {
									border: false,
									labelWidth: 130,
									layout: 'form',
									items: [{
										fieldLabel: 'Единый номер',
										name: 'Person_Code',
										tabIndex: TABINDEX_PERSCARDSW + 16,
										width: 162,
										maskRe: /\d/,
										xtype: 'textfield'
									}]
								}]
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										tabIndex: TABINDEX_PERSCARDSW + 17,
										width: 100,
										xtype: 'swpolistypecombo'
									}]
								}, {
									border: false,
									labelWidth: 100,
									layout: 'form',
									items: [{
										enableKeyEvents: true,
										forceSelection: false,
										hiddenName: 'OrgSmo_id',
										listeners: {
											'blur': function(combo) {
												if ( combo.getRawValue() == '' ) {
													combo.clearValue();
												}

												if ( combo.getStore().findBy(function(rec) { return rec.get(combo.displayField) == combo.getRawValue(); }) < 0 ) {
													combo.clearValue();
												}
											},
											'keydown': function( inp, e ) {
												if ( e.F4 == e.getKey() ) {
													if ( inp.disabled ) {
														return;
													}

													if ( e.browserEvent.stopPropagation )
														e.browserEvent.stopPropagation();
													else
														e.browserEvent.cancelBubble = true;

													if ( e.browserEvent.preventDefault )
														e.browserEvent.preventDefault();
													else
														e.browserEvent.returnValue = false;

													e.returnValue = false;

													if ( Ext.isIE )  {
														e.browserEvent.keyCode = 0;
														e.browserEvent.which = 0;
													}

													inp.onTrigger2Click();
													inp.collapse();

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
										listWidth: 400,
										minChars: 1,
										queryDelay: 1,
										tabIndex: TABINDEX_PERSCARDSW + 18,
										tpl: new Ext.XTemplate(
											'<tpl for="."><div class="x-combo-list-item">',
											'{OrgSMO_Nick}',
											'</div></tpl>'
										),
										typeAhead: true,
										typeAheadDelay: 1,
										width: 400,
										xtype: 'sworgsmocombo'
									}]
								}]
							}, /*{
								tabIndex: TABINDEX_PERSCARDSW + 19,
								width: 310,
								additionalRecord: {
									value: 100500,
									text: 'Иные территории',
									code: 0
								},
								xtype: 'swomssprterradditcombo'
							},*/
							{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										tabIndex: TABINDEX_PERSCARDSW + 19,
										width: 250,
										additionalRecord: {
											value: 100500,
											text: 'Иные территории',
											code: 0
										},
										xtype: 'swomssprterradditcombo'
									}]
								}, {
									border: false,
									labelWidth: 130,
									layout: 'form',
									hidden: (getRegionNick().inlist(['kz'])),
									items: [
										{
											allowBlank: true,
											displayField: 'HasPolis_Name',
											ignoreIsEmpty: true,
											editable: false,
											fieldLabel: 'Наличие полиса',
											hiddenName: 'HasPolis_Code',
											store: new Ext.data.SimpleStore({
												autoLoad: true,
												data: [
													[ 0, '' ],
													[ 1, 'Отсутствует полис ОМС' ],
													[ 2, 'В наличии полис ОМС' ]
												],
												fields: [
													{ name: 'HasPolis_Code', type: 'int'},
													{ name: 'HasPolis_Name', type: 'string'}
												],
												key: 'HasPolis_Code',
												sortInfo: { field: 'HasPolis_Code' }
											}),
											tabIndex: TABINDEX_PERSCARDSW + 38,
											tpl: new Ext.XTemplate(
												'<tpl for="."><div class="x-combo-list-item">',
												'<font color="black">{HasPolis_Name}&nbsp;</font>',
												'</div></tpl>'
											),
											valueField: 'HasPolis_Code',
											width: 220,
											xtype: 'swbaselocalcombo'
										}
									]
								}]
							},
							{
								border: false,
								layout: 'column',
								hidden: (getRegionNick().inlist(['kz'])),
								items: [{
									border: false,
									layout: 'form',
									items: [new sw.Promed.SwYesNoCombo({
										disabled: false,
										fieldLabel: 'БДЗ',
										hiddenName: 'IsBDZ',
										tabIndex: TABINDEX_PERSCARDSW + 24,
										width: 100
									})]
								}, {
									border: false,
									labelWidth: 200,
									layout: 'form',
									hidden: !(getRegionNick().inlist(['perm','ufa'])),
									items: [new sw.Promed.SwYesNoCombo({
										disabled: false,
										fieldLabel: 'Идентификатор с ТФОМС',
										hiddenName: 'TFOMSIdent',
										tabIndex: TABINDEX_PERSCARDSW + 24,
										width: 100
									})]
								}]
							},
							{
								border: false,
								layout: 'column',
								hidden: (getRegionNick().inlist(['kz'])),
								items: [{
									border: false,
									layout: 'form',
									items: [{
										border: false,
										labelWidth: 220,
										layout: 'form',
										items: [
											{
												allowBlank: true,
												displayField: 'PolisClosed_Name',
												ignoreIsEmpty: true,
												editable: false,
												fieldLabel: 'Данные о закрытии полиса',
												hiddenName: 'PolisClosed',
												listeners: {
													'change': function(combo, newValue, oldValue) {
														var current_window = Ext.getCmp('PersonCardSearchWindow');
														var form = current_window.findById('PersonCardSearchForm');
														if(newValue == 2){
															form.getForm().findField('PolisClosed_Date_Range').enable();
														}
														else
														{
															form.getForm().findField('PolisClosed_Date_Range').disable();
															form.getForm().findField('PolisClosed_Date_Range').setValue('');
														}
													}
												},
												store: new Ext.data.SimpleStore({
													autoLoad: true,
													data: [
														[ 0, '' ],
														[ 1, 'Полис открыт' ],
														[ 2, 'Полис закрыт' ]
													],
													fields: [
														{ name: 'PolisClosed_Code', type: 'int'},
														{ name: 'PolisClosed_Name', type: 'string'}
													],
													key: 'PolisClosed_Code',
													sortInfo: { field: 'PolisClosed_Code' }
												}),
												tabIndex: TABINDEX_PERSCARDSW + 38,
												tpl: new Ext.XTemplate(
													'<tpl for="."><div class="x-combo-list-item">',
													'<font color="black">{PolisClosed_Name}&nbsp;</font>',
													'</div></tpl>'
												),
												valueField: 'PolisClosed_Code',
												width: 120,
												xtype: 'swbaselocalcombo'
											}
										]
									}]
								}, {
									border: false,
									labelWidth: 200,
									layout: 'form',
									items: [{
										fieldLabel: 'Диапазон дат закрытия полиса',
										name: 'PolisClosed_Date_Range',
										disabled: true,
										plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
										tabIndex: TABINDEX_PERSCARDSW + 42,
										width: 190,
										xtype: 'daterangefield'
									}]
								}]
							}]
						}]
					}, {
						autoHeight: true,
						bodyStyle: 'margin-top: 5px;',
						border: false,
						layout: 'form',
						listeners: {
							'activate': function(panel) {
								panel.ownerCt.ownerCt.getForm().findField('Sex_id').focus(250, true);
							}
						},
						title: '<u>2</u>. Пациент (доп.)',

						// tabIndexStart: TABINDEX_PERSCARDSW + 21
						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: 'Пол',
									hiddenName: 'Sex_id',
									listeners: {
										'keydown': function (inp, e) {
											if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
												e.stopEvent();
												inp.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.buttons[6].focus();
											}
										}
									},
									tabIndex: TABINDEX_PERSCARDSW + 21,
									width: 150,
									xtype: 'swpersonsexcombo'
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: 'Соц. статус',
									hiddenName: 'SocStatus_id',
									tabIndex: TABINDEX_PERSCARDSW + 22,
									width: 250,
									xtype: 'swsocstatuscombo'
								}]
							}]
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								hidden: getRegionNick() == 'kz',
								layout: 'form',
								items: [
								new sw.Promed.SwYesNoCombo({
									fieldLabel: 'Наличие СНИЛС',
									hiddenName: 'SnilsExistence',
									listeners: {
										'change': function(combo, nv, ov) {
											var index = combo.getStore().findBy(function(rec) {
												return (rec.get(combo.valueField) == nv);
											});

											combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
										},
										'select': function(combo, record, idx) {
											var form = win.findById('PersonCardSearchForm');
											var Person_Snils = form.getForm().findField('Person_Snils');
											if ( typeof record == 'object' && (record.get('YesNo_Code') == 1 || record.get('YesNo_Code') == '') ) {
												// обработка вариантов "Да" и "пустое значение"
												Person_Snils.enable();
											} else {
												// обработка варианта "Нет"
												Person_Snils.setValue('');
												Person_Snils.disable();
											}
										}
									},
									tabIndex: TABINDEX_PERSCARDSW + 23,
									width: 100
								})]
							}, {
								border: false,
								layout: 'form',
								hidden: getRegionNick() == 'kz',
								items: [{
									fieldLabel: 'СНИЛС',
									name: 'Person_Snils',
									tabIndex: TABINDEX_PERSCARDSW + 24,
									width: 150,
									xtype: 'textfieldpmw'
								}]
							}, {
									border: false,
									layout: 'form',
									labelWidth: ((getRegionNick() != 'kz' ) ? false : 505),
									items: [
										new sw.Promed.SwYesNoCombo({
											disabled: true,
											fieldLabel: ((getRegionNick() != 'kz') ? 'Диспансерный учет' : 'Диспансерное наблюдение'),
											hiddenName: 'PersonDisp_id',
											tabIndex: TABINDEX_PERSCARDSW + 25,
											width: 100
										})]
							}]
						}, {
							autoHeight: true,
							labelWidth: 114,
							layout: 'form',
							style: 'margin: 0px 5px 0px 5px; padding: 0px;',
							title: 'Документ',
							width: 755,
							xtype: 'fieldset',
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										editable: false,
										forceSelection: true,
										fieldLabel: 'Тип документа',
										hiddenName: 'DocumentType_id',
										listWidth: 500,
										tabIndex: TABINDEX_PERSCARDSW + 26,
										width: 200,
										xtype: 'swdocumenttypecombo'
									}]
								}, {
									border: false,
									labelWidth: 80,
									layout: 'form',
									items: [{
										fieldLabel: 'Серия',
										name: 'Document_Ser',
										tabIndex: TABINDEX_PERSCARDSW + 27,
										width: 100,
										xtype: 'textfield'
									}]
								}, {
									border: false,
									labelWidth: 80,
									layout: 'form',
									items: [{
										allowNegative: false,
										// allowDecimals: false,
										fieldLabel: 'Номер',
										name: 'Document_Num',
										tabIndex: TABINDEX_PERSCARDSW + 28,
										width: 100,
										xtype: 'numberfield'
									}]
								}]
							}, {
								editable: false,
								enableKeyEvents: true,
								hiddenName: 'OrgDep_id',
								listeners: {
									'keydown': function( inp, e ) {
										if ( inp.disabled ) {
											return;
										}

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
								listWidth: 400,
								onTrigger1Click: function() {
									if ( this.disabled ) {
										return;
									}

									var combo = this;

									getWnd('swOrgSearchWindow').show({
										onSelect: function(orgData) {
											if ( orgData.Org_id > 0 ) {
												combo.getStore().load({
													callback: function() {
														combo.setValue(orgData.Org_id);
														combo.focus(true, 250);
														combo.fireEvent('change', combo);
													},
													params: {
														Object: 'OrgDep',
														OrgDep_id: orgData.Org_id,
														OrgDep_Name: ''
													}
												});
											}
											getWnd('swOrgSearchWindow').hide();
										},
										onClose: function() {
											combo.focus(true, 200)
										},
										object: 'dep'
									});
								},
								tabIndex: TABINDEX_PERSCARDSW + 29,
								width: 500,
								xtype: 'sworgdepcombo'
							}]
						}, {
							autoHeight: true,
							labelWidth: 114,
							layout: 'form',
							style: 'margin: 0px 5px 5px 5px; padding: 0px;',
							title: 'Место работы, учебы',
							width: 755,
							xtype: 'fieldset',
							items: [{
								editable: false,
								enableKeyEvents: true,
								fieldLabel: 'Организация',
								hiddenName: 'Org_id',
								listeners: {
									'keydown': function( inp, e ) {
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
								onTrigger1Click: function() {
									var ownerWindow = Ext.getCmp('PersonCardSearchWindow');
									var combo = this;

									getWnd('swOrgSearchWindow').show({
										onSelect: function(orgData) {
											if ( orgData.Org_id > 0 ) {
												combo.getStore().load({
													callback: function() {
														combo.setValue(orgData.Org_id);
														combo.focus(true, 500);
														combo.fireEvent('change', combo);
													},
													params: {
														Object: 'Org',
														Org_id: orgData.Org_id,
														Org_Name: ''
													}
												});
											}
											getWnd('swOrgSearchWindow').hide();
										},
										onClose: function() { combo.focus(true, 200) }
									});
								},
								tabIndex: TABINDEX_PERSCARDSW + 30,
								triggerAction: 'none',
								width: 500,
								xtype: 'sworgcombo'
							}/*, {
								forceSelection: false,
								hiddenName: 'Post_id',
								minChars: 0,
								queryDelay: 1,
								selectOnFocus: true,
								tabIndex: TABINDEX_PERSCARDSW + 30,
								typeAhead: true,
								typeAheadDelay: 1,
								width: 500,
								xtype: 'swpostcombo'
							}*/]
						}]
					}, {
						autoHeight: true,
						bodyStyle: 'margin-top: 5px;',
						border: false,
						labelWidth: 150,
						layout: 'form',
						listeners: {
							'activate': function(panel) {
								if ( !panel.ownerCt.ownerCt.getForm().findField('AttachLpu_id').disabled ) {
									panel.ownerCt.ownerCt.getForm().findField('AttachLpu_id').focus(250, true);
								}
								else {
									panel.ownerCt.ownerCt.getForm().findField('LpuAttachType_id').focus(250, true);
								}
							}
						},
						title: '<u>3</u>. Прикрепление',

						// tabIndexStart: TABINDEX_PERSCARDSW + 33
						items: [ new sw.Promed.SwLpuCombo({
							fieldLabel: 'ЛПУ прикрепления',
							hiddenName: 'AttachLpu_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if ( (newValue > 0) && newValue != getGlobalOptions().lpu_id) {
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('LpuRegion_id').clearValue();
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('LpuRegion_id').disable();
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('LpuRegionType_id').clearValue();
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('LpuRegionType_id').disable();
									}
									else {
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('LpuRegion_id').enable();
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('LpuRegionType_id').enable();
									}
                                    var lpuregion_combo = combo.ownerCt.ownerCt.ownerCt.getForm().findField('LpuRegion_id');
                                    var lpuregion_fap_combo = combo.ownerCt.ownerCt.ownerCt.getForm().findField('LpuRegion_Fapid');
                                    var params = new Object();
                                    params.add_without_region_line = true;
                                    if(!Ext.isEmpty(newValue) && newValue > 0)
                                        params.Lpu_id = newValue;
                                    lpuregion_combo.clearValue();
                                    lpuregion_combo.getStore().removeAll();
                                    lpuregion_fap_combo.clearValue();
                                    lpuregion_fap_combo.getStore().removeAll();
                                    lpuregion_combo.getStore().load({params: params});
                                    lpuregion_fap_combo.getStore().load({params: params});
								},
								'keydown': function (inp, e) {
									if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
										e.stopEvent();
										inp.ownerCt.ownerCt.ownerCt.ownerCt.buttons[6].focus();
									}
								}
							},
							listWidth: 400,
							tabIndex: TABINDEX_PERSCARDSW + 33,
							width: 310
						}), {
							hiddenName: 'LpuAttachType_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var form = combo.ownerCt.ownerCt.ownerCt;
									var lpu_region_type_combo = form.getForm().findField('LpuRegionType_id');
									var lpu_region_type_id = lpu_region_type_combo.getValue();

									lpu_region_type_combo.clearValue();
									lpu_region_type_combo.getStore().clearFilter();

									if ( newValue && getRegionNick() != 'kz' ) {
										var LpuRegionTypeArray = [];

										switch ( newValue ) {
											case 1:
												LpuRegionTypeArray = [ 'ter', 'ped', 'vop' ];

												if ( getRegionNick() == 'perm' ) {
													LpuRegionTypeArray = [ 'ter', 'ped', 'vop', 'comp', 'prip' ];
												}
											break;

											case 2:
												LpuRegionTypeArray = [ 'gin' ];
											break;

											case 3:
												LpuRegionTypeArray = [ 'stom' ];
											break;

											case 4:
												LpuRegionTypeArray = [ 'slug','vop','psdet','pspod','psvz'];
											break;
										}

										lpu_region_type_combo.getStore().filterBy(function(rec) {
											return (!Ext.isEmpty(rec.get('LpuRegionType_SysNick')) && rec.get('LpuRegionType_SysNick').inlist(LpuRegionTypeArray));
										});
									}

									var record = lpu_region_type_combo.getStore().getById(lpu_region_type_id);

									if ( newValue != 4 || getRegionNick() != 'ufa' ) {
										if ( record ) {
											lpu_region_type_combo.setValue(lpu_region_type_id);
											lpu_region_type_combo.fireEvent('change', lpu_region_type_combo, lpu_region_type_id, null);
										}
										else if ( lpu_region_type_combo.getStore().getCount() == 1 ) {
											lpu_region_type_combo.setValue(lpu_region_type_combo.getStore().getAt(0).get('LpuRegionType_id'));
											lpu_region_type_combo.fireEvent('change', lpu_region_type_combo, lpu_region_type_combo.getStore().getAt(0).get('LpuRegionType_id'), null);
										}
										else {
											lpu_region_type_combo.fireEvent('change', lpu_region_type_combo, null, lpu_region_type_id);
										}
									}
									else {
										lpu_region_type_combo.fireEvent('change', lpu_region_type_combo, null, lpu_region_type_id);
									}
								}
							},
							tabIndex: TABINDEX_PERSCARDSW + 34,
							width: 170,
							xtype: 'swlpuattachtypecombo'
						}, {
							hiddenName: 'LpuRegionType_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var form = combo.ownerCt.ownerCt.ownerCt;
									var lpu_attach_type_id = form.getForm().findField('LpuAttachType_id').getValue();
									var lpu_region_combo = form.getForm().findField('LpuRegion_id');
									var lpu_region_id = lpu_region_combo.getValue();

									lpu_region_combo.clearValue();
									lpu_region_combo.getStore().clearFilter();

									if ( newValue ) {
										lpu_region_combo.getStore().filterBy(function(rec) {
											if ( rec.get('LpuRegionType_id') == newValue) {
												return true;
											}
											else {
												return false;
											}
										});
									}
									else if ( lpu_attach_type_id ) {
										var LpuRegionTypeArray = [];

										switch ( lpu_attach_type_id ) {
											case 1:
												LpuRegionTypeArray = [ 'ter', 'ped', 'vop' ];

												if ( getRegionNick() == 'perm' ) {
													LpuRegionTypeArray = [ 'ter', 'ped', 'vop', 'comp', 'prip' ];
												}
											break;

											case 2:
												LpuRegionTypeArray = [ 'gin' ];
											break;

											case 3:
												LpuRegionTypeArray = [ 'stom' ];
											break;

											case 4:
												LpuRegionTypeArray = [ 'slug' ];
											break;
										}

										lpu_region_combo.getStore().filterBy(function(rec) {
											return (!Ext.isEmpty(rec.get('LpuRegionType_SysNick')) && rec.get('LpuRegionType_SysNick').inlist(LpuRegionTypeArray));
										});
									}

									var record = lpu_region_combo.getStore().getById(lpu_region_id);

									if ( record ) {
										lpu_region_combo.setValue(lpu_region_id);
									}
									else if ( lpu_region_combo.getStore().getCount() == 1 && (lpu_attach_type_id != 4 || getRegionNick() != 'ufa') ) {
										lpu_region_combo.setValue(lpu_region_combo.getStore().getAt(0).get('LpuRegion_id'));
									}
								}
							},
							tabIndex: TABINDEX_PERSCARDSW + 35,
							width: 170,
							xtype: 'swlpuregiontypecombo'
						}, {
							displayField: 'LpuRegion_Name',
							editable: true,
							fieldLabel: 'Участок',
							forceSelection: false,
							hiddenName: 'LpuRegion_id',
							tabIndex: TABINDEX_PERSCARDSW + 36,
							triggerAction: 'all',
							typeAhead: true,
							typeAheadDelay: 1,
							valueField: 'LpuRegion_id',
							width: 310,
							xtype: 'swlpuregioncombo'
						},
                            {
                                allowBlank: true,
                                displayField: 'LpuRegion_FapName',
                                fieldLabel: 'ФАП Участок',
                                forceSelection: true,
                                hiddenName: 'LpuRegion_Fapid',
                                hideLabel: !getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza','vologda']),//getRegionNick() != 'perm',
                                hidden: !getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza','vologda']),// getRegionNick() != 'perm',
                                id: 'PSPCAW_LpuRegion_Fapid',
                                minChars: 1,
                                mode: 'local',
                                queryDelay: 1,
                                setValue: function(v) {
                                    var text = v;
                                    if(this.valueField){
                                        var r = this.findRecord(this.valueField, v);
                                        if(r){
                                            text = r.data[this.displayField];
                                            if ( !(String(r.data['LpuRegion_FapDescr']).toUpperCase() == "NULL" || String(r.data['LpuRegion_FapDescr']) == "") )
                                            {
                                                if (r.data['LpuRegion_FapDescr']) {
                                                    text = text + ' ( '+ r.data['LpuRegion_FapDescr'] + ' )';
                                                }
                                            }
                                        } else if(this.valueNotFoundText !== undefined){
                                            text = this.valueNotFoundText;
                                        }
                                    }
                                    this.lastSelectionText = text;
                                    if(this.hiddenField){
                                        this.hiddenField.value = v;
                                    }
                                    Ext.form.ComboBox.superclass.setValue.call(this, text);
                                    this.value = v;
                                },
                                lastQuery: '',
                                store: new Ext.data.Store({
                                    autoLoad: true,
                                    reader: new Ext.data.JsonReader({
                                        id: 'LpuRegion_Fapid'
                                    }, [
                                        {name: 'LpuRegion_FapName', mapping: 'LpuRegion_FapName'},
                                        {name: 'LpuRegion_Fapid', mapping: 'LpuRegion_Fapid'},
                                        {name: 'LpuRegion_FapDescr', mapping: 'LpuRegion_FapDescr'}
                                    ]),
                                    url: '/?c=LpuRegion&m=getLpuRegionListFeld'
                                }),
                                tabIndex: 2106,
                                tpl: '<tpl for="."><div class="x-combo-list-item">{LpuRegion_FapName}</div></tpl>',
                                triggerAction: 'all',
                                typeAhead: true,
                                typeAheadDelay: 1,
                                valueField: 'LpuRegion_Fapid',
                                width : 180,
                                xtype: 'combo'
                            }, {
							allowBlank: false,
							codeField: 'PersonCardStateType_Code',
							displayField: 'PersonCardStateType_Name',
							ignoreIsEmpty: true,
							editable: false,
							fieldLabel: 'Актуальность прикр-я',
							hiddenName: 'PersonCardStateType_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if ( newValue == 1) {
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('PersonCard_endDate').setValue(null);
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('PersonCard_endDate').disable();
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('PersonCard_endDate_Range').setValue(null);
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('PersonCard_endDate_Range').disable();
									}
									else {
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('PersonCard_endDate').enable();
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('PersonCard_endDate_Range').enable();
									}
								}
							},
							store: new Ext.data.SimpleStore({
								autoLoad: true,
								data: [
									[ 1, 1, 'Актуальные прикрепления' ],
									[ 2, 2, 'Вся история прикреплений' ]
								],
								fields: [
									{ name: 'PersonCardStateType_id', type: 'int'},
									{ name: 'PersonCardStateType_Code', type: 'int'},
									{ name: 'PersonCardStateType_Name', type: 'string'}
								],
								key: 'PersonCardStateType_id',
								sortInfo: { field: 'PersonCardStateType_Code' }
							}),
							tabIndex: TABINDEX_PERSCARDSW + 38,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<font color="red">{PersonCardStateType_Code}</font>&nbsp;{PersonCardStateType_Name}',
								'</div></tpl>'
							),
							value: 1,
							valueField: 'PersonCardStateType_id',
							width: 310,
							xtype: 'swbaselocalcombo'
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: 'Дата прикрепления',
									name: 'PersonCard_begDate',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
									tabIndex: TABINDEX_PERSCARDSW + 39,
									width: 100,
									xtype: 'swdatefield'
								}, {
									fieldLabel: 'Дата открепления',
									name: 'PersonCard_endDate',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
									tabIndex: TABINDEX_PERSCARDSW + 41,
									width: 100,
									xtype: 'swdatefield'
								},
								new sw.Promed.SwYesNoCombo({
									fieldLabel: 'Условн. прикр.',
									hiddenName: 'PersonCard_IsAttachCondit',
									tabIndex: TABINDEX_PERSCARDSW + 43,
									width: 100
								})]
							}, {
								border: false,
								labelWidth: 220,
								layout: 'form',
								items: [{
									fieldLabel: 'Диапазон дат прикрепления',
									name: 'PersonCard_begDate_Range',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
									tabIndex: TABINDEX_PERSCARDSW + 40,
									width: 170,
									xtype: 'daterangefield'
								}, {
									fieldLabel: 'Диапазон дат открепления',
									name: 'PersonCard_endDate_Range',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
									tabIndex: TABINDEX_PERSCARDSW + 42,
									width: 170,
									xtype: 'daterangefield'
								},
								new sw.Promed.SwYesNoCombo({
										fieldLabel: 'ДМС прикрепление',
										hiddenName: 'PersonCard_IsDms',
										tabIndex: TABINDEX_PERSCARDSW + 43,
										width: 170
								})]
							},
								{
									border: false,
									layout: 'form',
									items: [
										new sw.Promed.SwYesNoCombo({
											fieldLabel: 'Заявление',
											hiddenName: 'PersonCardAttach',
											tabIndex: TABINDEX_PERSCARDSW + 43,
											width: 170
										})
									]
								}]
						}]
					}, {
						autoHeight: true,
						bodyStyle: 'margin-top: 5px;',
						border: false,
						layout: 'form',
						listeners: {
							'activate': function(panel) {
								panel.ownerCt.ownerCt.getForm().findField('AddressStateType_id').focus(250, true);
							}
						},
						title: '<u>4</u>. Адрес',

						// tabIndexStart: TABINDEX_PERSCARDSW + 46
						items: [{
							allowBlank: true,
							ignoreIsEmpty: true,
							codeField: 'AddressStateType_Code',
							displayField: 'AddressStateType_Name',
							editable: false,
							fieldLabel: 'Тип адреса',
							hiddenName: 'AddressStateType_id',
							ignoreIsEmpty: true,
							listeners: {
								'keydown': function (inp, e) {
									if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
										e.stopEvent();
										inp.ownerCt.ownerCt.ownerCt.ownerCt.buttons[6].focus();
									}
								}
							},
							store: new Ext.data.SimpleStore({
								autoLoad: true,
								data: [
									[ 1, 1, 'Адрес регистрации' ],
									[ 2, 2, 'Адрес проживания' ]
								],
								fields: [
									{ name: 'AddressStateType_id', type: 'int'},
									{ name: 'AddressStateType_Code', type: 'int'},
									{ name: 'AddressStateType_Name', type: 'string'}
								],
								key: 'AddressStateType_id',
								setValue: 1,
								sortInfo: { field: 'AddressStateType_Code' }
							}),
							tabIndex: TABINDEX_PERSCARDSW + 46,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<font color="red">{AddressStateType_Code}</font>&nbsp;{AddressStateType_Name}',
								'</div></tpl>'
							),
							value: 1,
							valueField: 'AddressStateType_id',
							width: 180,
							xtype: 'swbaselocalcombo'
						}, {
							codeField: 'KLAreaStat_Code',
							disabled: false,
							displayField: 'KLArea_Name',
							editable: true,
							enableKeyEvents: true,
							fieldLabel: 'Территория',
							hiddenName: 'KLAreaStat_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var current_window = Ext.getCmp('PersonCardSearchWindow');
									var current_record = combo.getStore().getById(newValue);
									var form = current_window.findById('PersonCardSearchForm');

									var country_combo = form.getForm().findField('KLCountry_id');
									var region_combo = form.getForm().findField('KLRgn_id');
									var sub_region_combo = form.getForm().findField('KLSubRgn_id').enable();
									var city_combo = form.getForm().findField('KLCity_id').enable();
									var town_combo = form.getForm().findField('KLTown_id').enable();
									var street_combo = form.getForm().findField('KLStreet_id').enable();

									country_combo.enable();
									region_combo.enable();
									sub_region_combo.enable();
									city_combo.enable();
									town_combo.enable();
									street_combo.enable();

									if ( !current_record ) {
										return false;
									}

									var country_id = current_record.get('KLCountry_id');
									var region_id = current_record.get('KLRGN_id');
									var subregion_id = current_record.get('KLSubRGN_id');
									var city_id = current_record.get('KLCity_id');
									var town_id = current_record.get('KLTown_id');
									var klarea_pid = 0;
									var level = 0;
									
									clearAddressCombo(
										country_combo.areaLevel, 
										{
											'Country': country_combo,
											'Region': region_combo,
											'SubRegion': sub_region_combo,
											'City': city_combo,
											'Town': town_combo,
											'Street': street_combo
										}
									);

									if ( country_id != null ) {
										country_combo.setValue(country_id);
										country_combo.disable();
									}
									else {
										return false;
									}

									region_combo.getStore().load({
										callback: function() {
											region_combo.setValue(region_id);
										},
										params: {
											country_id: country_id,
											level: 1,
											value: 0
										}
									});

									if ( region_id.toString().length > 0 ) {
										klarea_pid = region_id;
										level = 1;
									}

									sub_region_combo.getStore().load({
										callback: function() {
											sub_region_combo.setValue(subregion_id);
										},
										params: {
											country_id: 0,
											level: 2,
											value: klarea_pid
										}
									});

									if ( subregion_id.toString().length > 0 ) {
										klarea_pid = subregion_id;
										level = 2;
									}

									city_combo.getStore().load({
										callback: function() {
											city_combo.setValue(city_id);
										},
										params: {
											country_id: 0,
											level: 3,
											value: klarea_pid
										}
									});

									if ( city_id.toString().length > 0 ) {
										klarea_pid = city_id;
										level = 3;
									}

									town_combo.getStore().load({
										callback: function() {
											town_combo.setValue(town_id);
										},
										params: {
											country_id: 0,
											level: 4,
											value: klarea_pid
										}
									});

									if ( town_id.toString().length > 0 ) {
										klarea_pid = town_id;
										level = 4;
									}

									street_combo.getStore().load({
										params: {
											country_id: 0,
											level: 5,
											value: klarea_pid
										}
									});

									switch ( level ) {
										case 1:
											region_combo.disable();
											break;

										case 2:
											region_combo.disable();
											sub_region_combo.disable();
											break;

										case 3:
											region_combo.disable();
											sub_region_combo.disable();
											city_combo.disable();
											break;

										case 4:
											region_combo.disable();
											sub_region_combo.disable();
											city_combo.disable();
											town_combo.disable();
											break;
									}
								}
							},
							store: new Ext.db.AdapterStore({
								autoLoad: true,
								dbFile: 'Promed.db',
								fields: [
									{ name: 'KLAreaStat_id', type: 'int' },
									{ name: 'KLAreaStat_Code', type: 'int' },
									{ name: 'KLArea_Name', type: 'string' },
									{ name: 'KLCountry_id', type: 'int' },
									{ name: 'KLRGN_id', type: 'int' },
									{ name: 'KLSubRGN_id', type: 'int' },
									{ name: 'KLCity_id', type: 'int' },
									{ name: 'KLTown_id', type: 'int' }
								],
								key: 'KLAreaStat_id',
								sortInfo: {
									field: 'KLAreaStat_Code',
									direction: 'ASC'
								},
								tableName: 'KLAreaStat'
							}),
							tabIndex: TABINDEX_PERSCARDSW + 47,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<font color="red">{KLAreaStat_Code}</font>&nbsp;{KLArea_Name}',
								'</div></tpl>'
							),
							valueField: 'KLAreaStat_id',
							width: 300,
							xtype: 'swbaselocalcombo'
						}, {
							areaLevel: 0,
							codeField: 'KLCountry_Code',
							disabled: false,
							displayField: 'KLCountry_Name',
							editable: true,
							fieldLabel: 'Страна',
							hiddenName: 'KLCountry_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
										loadAddressCombo(
											combo.areaLevel, 
											{
												'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
												'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
												'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
												'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
												'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
												'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
											},
											combo.getValue(),
											combo.getValue(), 
											true
										);
									}
									else {
										clearAddressCombo(
											combo.areaLevel, 
											{
												'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
												'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
												'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
												'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
												'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
												'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
											}
										);
									}
								},
								'keydown': function(combo, e) {
									if ( e.getKey() == e.DELETE ) {
										if ( combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0 ) {
											combo.fireEvent('change', combo, null, combo.getValue());
										}
									}
								},
								'select': function(combo, record, index) {
									if ( record.get('KLCountry_id') == combo.getValue() ) {
										combo.collapse();
										return false;
									}
									combo.fireEvent('change', combo, record.get('KLArea_id'), null);
								}
							},
							store: new Ext.db.AdapterStore({
								autoLoad: true,
								dbFile: 'Promed.db',
								fields: [
									{ name: 'KLCountry_id', type: 'int' },
									{ name: 'KLCountry_Code', type: 'int' },
									{ name: 'KLCountry_Name', type: 'string' }
								],
								key: 'KLCountry_id',
								sortInfo: {
									field: 'KLCountry_Name'
								},
								tableName: 'KLCountry'
							}),
							tabIndex: TABINDEX_PERSCARDSW + 48,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<font color="red">{KLCountry_Code}</font>&nbsp;{KLCountry_Name}',
								'</div></tpl>'
							),
							valueField: 'KLCountry_id',
							width: 300,
							xtype: 'swbaselocalcombo'
						}, {
							areaLevel: 1,
							disabled: false,
							displayField: 'KLArea_Name',
							enableKeyEvents: true,
							fieldLabel: 'Регион',
							hiddenName: 'KLRgn_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
										loadAddressCombo(
											combo.areaLevel, 
											{
												'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
												'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
												'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
												'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
												'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
												'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
											},
											0,
											combo.getValue(), 
											true
										);
									}
									else {
										clearAddressCombo(
											combo.areaLevel, 
											{
												'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
												'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
												'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
												'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
												'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
												'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
											}
										);
									}
								},
								'keydown': function(combo, e) {
									if ( e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0 ) {
										combo.fireEvent('change', combo, null, combo.getValue());
									}
								},
								'select': function(combo, record, index) {
									if ( record.get('KLArea_id') == combo.getValue() ) {
										combo.collapse();
										return false;
									}
									combo.fireEvent('change', combo, record.get('KLArea_id'));
								}
							},
							minChars: 0,
							mode: 'local',
							queryDelay: 250,
							store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
									{ name: 'KLArea_id', type: 'int' },
									{ name: 'KLArea_Name', type: 'string' }
								],
								key: 'KLArea_id',
								sortInfo: {
									field: 'KLArea_Name'
								},
								url: C_LOAD_ADDRCOMBO
							}),
							tabIndex: TABINDEX_PERSCARDSW + 49,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{KLArea_Name}',
								'</div></tpl>'
							),
							triggerAction: 'all',
							valueField: 'KLArea_id',
							width: 300,
							xtype: 'combo'
						}, {
							areaLevel: 2,
							disabled: false,
							displayField: 'KLArea_Name',
							enableKeyEvents: true,
							fieldLabel: 'Район',
							hiddenName: 'KLSubRgn_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
										loadAddressCombo(
											combo.areaLevel, 
											{
												'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
												'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
												'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
												'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
												'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
												'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
											},
											0,
											combo.getValue(), 
											true
										);
									}
									else {
										clearAddressCombo(
											combo.areaLevel, 
											{
												'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
												'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
												'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
												'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
												'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
												'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
											}
										);
									}
								},
								'keydown': function(combo, e) {
									if ( e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0 ) {
										combo.fireEvent('change', combo, null, combo.getValue());
									}
								},
								'select': function(combo, record, index) {
									if ( record.get('KLArea_id') == combo.getValue() ) {
										combo.collapse();
										return false;
									}
									combo.fireEvent('change', combo, record.get('KLArea_id'));
								}
							},
							minChars: 0,
							mode: 'local',
							queryDelay: 250,
							store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
									{ name: 'KLArea_id', type: 'int' },
									{ name: 'KLArea_Name', type: 'string' }
								],
								key: 'KLArea_id',
								sortInfo: {
									field: 'KLArea_Name'
								},
								url: C_LOAD_ADDRCOMBO
							}),
							tabIndex: TABINDEX_PERSCARDSW + 50,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{KLArea_Name}',
								'</div></tpl>'
							),
							triggerAction: 'all',
							valueField: 'KLArea_id',
							width: 300,
							xtype: 'combo'
						}, {
							areaLevel: 3,
							disabled: false,
							displayField: 'KLArea_Name',
							enableKeyEvents: true,
							fieldLabel: 'Город',
							hiddenName: 'KLCity_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
										loadAddressCombo(
											combo.areaLevel, 
											{
												'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
												'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
												'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
												'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
												'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
												'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
											},
											0,
											combo.getValue(), 
											true
										);
									}
									else {
										clearAddressCombo(
											combo.areaLevel, 
											{
												'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
												'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
												'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
												'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
												'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
												'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
											}
										);
									}
								},
								'keydown': function(combo, e) {
									if ( e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0 ) {
										combo.fireEvent('change', combo, null, combo.getValue());
									}
								},
								'select': function(combo, record, index) {
									if ( record.get('KLArea_id') == combo.getValue() ) {
										combo.collapse();
										return false;
									}
									combo.fireEvent('change', combo, record.get('KLArea_id'));
								}
							},
							minChars: 0,
							mode: 'local',
							queryDelay: 250,
							store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
									{ name: 'KLArea_id', type: 'int' },
									{ name: 'KLArea_Name', type: 'string' }
								],
								key: 'KLArea_id',
								sortInfo: {
									field: 'KLArea_Name'
								},
								url: C_LOAD_ADDRCOMBO
							}),
							tabIndex: TABINDEX_PERSCARDSW + 51,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{KLArea_Name}',
								'</div></tpl>'
							),
							triggerAction: 'all',
							valueField: 'KLArea_id',
							width: 300,
							xtype: 'combo'
						}, {
							areaLevel: 4,
							disabled: false,
							displayField: 'KLArea_Name',
							enableKeyEvents: true,
							fieldLabel: 'Населенный пункт',
							hiddenName: 'KLTown_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
										loadAddressCombo(
											combo.areaLevel, 
											{
												'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
												'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
												'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
												'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
												'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
												'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
											},
											0,
											combo.getValue(), 
											true
										);
									}
									else {
										clearAddressCombo(
											combo.areaLevel, 
											{
												'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
												'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
												'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
												'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
												'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
												'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
											}
										);
									}
								},
								'keydown': function(combo, e) {
									if ( e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0 ) {
										combo.fireEvent('change', combo, null, combo.getValue());
									}
								},
								'select': function(combo, record, index) {
									/*alert(record.get('KLArea_id'));
									if ( record.get('KLArea_id') == combo.getValue() ) {
										combo.collapse();
										return false;
									}*/
									combo.fireEvent('change', combo, record.get('KLArea_id'));
								}
							},
							minChars: 0,
							mode: 'local',
							queryDelay: 250,
							store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
									{ name: 'KLArea_id', type: 'int' },
									{ name: 'KLArea_Name', type: 'string' }
								],
								key: 'KLArea_id',
								sortInfo: {
									field: 'KLArea_Name'
								},
								url: C_LOAD_ADDRCOMBO
							}),
							tabIndex: TABINDEX_PERSCARDSW + 52,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{KLArea_Name}',
								'</div></tpl>'
							),
							triggerAction: 'all',
							valueField: 'KLArea_id',
							width: 300,
							xtype: 'combo'
						}, {
							disabled: false,
							displayField: 'KLStreet_Name',
							enableKeyEvents: true,
							fieldLabel: 'Улица',
							hiddenName: 'KLStreet_id',
							minChars: 0,
							mode: 'local',
							queryDelay: 250,
							store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
									{ name: 'KLStreet_id', type: 'int' },
									{ name: 'KLStreet_Name', type: 'string' }
								],
								key: 'KLStreet_id',
								sortInfo: {
									field: 'KLStreet_Name'
								},
								url: C_LOAD_ADDRCOMBO
							}),
							tabIndex: TABINDEX_PERSCARDSW + 53,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{KLStreet_Name}',
								'</div></tpl>'
							),
							triggerAction: 'all',
							valueField: 'KLStreet_id',
							width: 300,
							xtype: 'combo'
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									disabled: false,
									fieldLabel: 'Дом',
									name: 'Address_House',
									tabIndex: TABINDEX_PERSCARDSW + 54,
									width: 100,
									xtype: 'textfield'
								}]
							},{
								border: false,
								layout: 'form',
								items: [{
									disabled: false,
									fieldLabel: 'Корпус',
									name: 'Address_Corpus',
									tabIndex: TABINDEX_PERSCARDSW + 54,
									width: 100,
									xtype: 'textfield'
								}]
							}, {
								border: false,
								labelWidth: 220,
								layout: 'form',
								items: [{
									tabIndex: TABINDEX_PERSCARDSW + 55,
									width: 100,
									xtype: 'swklareatypecombo'
								}]
							}]
						}]
					}, {
						autoHeight: true,
						bodyStyle: 'margin-top: 5px;',
						border: false,
						layout: 'form',
						listeners: {
							'activate': function(panel) {
								if ( getRegionNick().inlist([ 'kz' ]) ) {
									panel.ownerCt.ownerCt.getForm().findField('RegisterSelector_id').setContainerVisible(false);
									panel.ownerCt.ownerCt.getForm().findField('PrivilegeType_id').focus(250, true);
								}
								else {
									panel.ownerCt.ownerCt.getForm().findField('RegisterSelector_id').focus(250, true);
								}
							}
						},
						title: '<u>5</u>. Льгота',

						// tabIndexStart: TABINDEX_PERSCARDSW + 57
						items: [{
							codeField: 'RegisterSelector_Code',
							displayField: 'RegisterSelector_Name',
							editable: false,
							fieldLabel: 'Регистр',
							hiddenName: 'RegisterSelector_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var privilege_type_combo = combo.ownerCt.ownerCt.ownerCt.getForm().findField('PrivilegeType_id');

									privilege_type_combo.getStore().filterBy(function(record, id) {
										if ( newValue == 1 ) {
											privilege_type_combo.clearValue();

											if ( record.get('ReceptFinance_id') == 1 && record.get('PrivilegeType_Code') < 500  ) {
												return true;
											}
											else {
												return false;
											}
										}
										else if ( newValue == 2 ) {
											privilege_type_combo.clearValue();

											if ( record.get('ReceptFinance_id') == 2 && record.get('PrivilegeType_Code') < 500 ) {
												return true;
											}
											else {
												return false;
											}
										}
										else {
											return true;
										}
									});
								},
								'keydown': function (inp, e) {
									if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
										e.stopEvent();
										inp.ownerCt.ownerCt.ownerCt.ownerCt.buttons[6].focus();
									}
								}
							},
							store: new Ext.data.SimpleStore({
								autoLoad: true,
								data: [
									[ 1, 1, 'Федеральный' ],
									[ 2, 2, 'Региональный' ]
								],
								fields: [
									{ name: 'RegisterSelector_id', type: 'int'},
									{ name: 'RegisterSelector_Code', type: 'int'},
									{ name: 'RegisterSelector_Name', type: 'string'}
								],
								key: 'RegisterSelector_id',
								sortInfo: { field: 'RegisterSelector_Code' }
							}),
							tabIndex: TABINDEX_PERSCARDSW + 57,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<font color="red">{RegisterSelector_Code}</font>&nbsp;{RegisterSelector_Name}',
								'</div></tpl>'
							),
							valueField: 'RegisterSelector_id',
							width: 250,
							xtype: 'swbaselocalcombo'
						},
						new sw.Promed.SwPrivilegeTypeCombo({
							listWidth: 350,
							tabIndex: TABINDEX_PERSCARDSW + 58,
							width: 250
						}), {
							allowBlank: false,
							codeField: 'PrivilegeStateType_Code',
							displayField: 'PrivilegeStateType_Name',
							editable: false,							
							fieldLabel: 'Актуальность льготы',
							hiddenName: 'PrivilegeStateType_id',
							ignoreIsEmpty: true,
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if ( newValue == 1) {
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('Privilege_endDate').setValue(null);
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('Privilege_endDate').disable();
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('Privilege_endDate_Range').setValue(null);
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('Privilege_endDate_Range').disable();
									}
									else {
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('Privilege_endDate').enable();
										combo.ownerCt.ownerCt.ownerCt.getForm().findField('Privilege_endDate_Range').enable();
									}
								}
							},
							store: new Ext.data.SimpleStore({
								autoLoad: true,
								data: [
									[ 1, 1, 'Действующие льготы' ],
									[ 2, 2, 'Включая недействующие льготы' ]
								],
								fields: [
									{ name: 'PrivilegeStateType_id', type: 'int'},
									{ name: 'PrivilegeStateType_Code', type: 'int'},
									{ name: 'PrivilegeStateType_Name', type: 'string'}
								],
								key: 'PrivilegeStateType_id',
								sortInfo: { field: 'PrivilegeStateType_Code' }
							}),
							tabIndex: TABINDEX_PERSCARDSW + 59,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<font color="red">{PrivilegeStateType_Code}</font>&nbsp;{PrivilegeStateType_Name}',
								'</div></tpl>'
							),
							value: 1,
							valueField: 'PrivilegeStateType_id',
							width: 250,
							xtype: 'swbaselocalcombo'
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: 'Дата начала',
									name: 'Privilege_begDate',
									plugins: [
										new Ext.ux.InputTextMask('99.99.9999', false)
									],
									tabIndex: TABINDEX_PERSCARDSW + 60,
									width: 100,
									xtype: 'swdatefield'
								}, {
									fieldLabel: 'Дата окончания',
									name: 'Privilege_endDate',
									plugins: [
										new Ext.ux.InputTextMask('99.99.9999', false)
									],
									tabIndex: TABINDEX_PERSCARDSW + 62,
									width: 100,
									xtype: 'swdatefield'
								}]
							}, {
								border: false,
								labelWidth: 220,
								layout: 'form',
								items: [{
									fieldLabel: 'Диапазон дат начала',
									name: 'Privilege_begDate_Range',
									plugins: [
										new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
									],
									tabIndex: TABINDEX_PERSCARDSW + 61,
									width: 170,
									xtype: 'daterangefield'
								}, {
									fieldLabel: 'Диапазон дат окончания',
									name: 'Privilege_endDate_Range',
									plugins: [
										new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
									],
									tabIndex: TABINDEX_PERSCARDSW + 63,
									width: 170,
									xtype: 'daterangefield'
								}]
							}]
						},
						new sw.Promed.SwYesNoCombo({
							fieldLabel: 'Отказник',
							hiddenName: 'Refuse_id',
							tabIndex: TABINDEX_PERSCARDSW + 64,
							width: 100
						}),
						new sw.Promed.SwYesNoCombo({
							fieldLabel: 'Отказ на след. год',
							hiddenName: 'RefuseNextYear_id',
							tabIndex: TABINDEX_PERSCARDSW + 65,
							width: 100
						}),
						{
							border: false,
							layout: 'form',
							hidden: getRegionNick() != 'ufa',
							items: [{
								xtype: 'checkbox',
								fieldLabel: 'Наличие обратного талона МСЭ',
								name: 'hasObrTalonMse',
								listeners: {
									change: function(field, newVal, oldVal) {
										var diag_from = win.FilterPanel.getForm().findField('Diag_Code_From');
										var diag_to = win.FilterPanel.getForm().findField('Diag_Code_To');
										diag_from.setDisabled(!newVal);
										diag_to.setDisabled(!newVal);
									}
								}
							}]
						}, {
							border: false,
							layout: 'column',
							hidden: getRegionNick() != 'ufa',
							items: [{								
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: langs('Диагноз с'),
									hiddenName: 'Diag_Code_From',
									disabled: true,
									listeners: {
										'select': function(combo, record, index) {
											combo.setRawValue(record.get('Diag_Code') + " " + record.get('Diag_Name'));
										},
										'change': function(combo, newValue) {
											if ( newValue != '' )
												Ext.getCmp('PersonDispViewFilterForm').getForm().findField('Sickness_id').clearValue();	
										}.createDelegate(this)
									},
									listWidth: 600,
									//~ tabIndex: TABINDEX_PERSDISPSW + 66,
									valueField: 'Diag_Code',
									width: 230,
									xtype: 'swdiagcombo'
								}]
							}, {
								border: false,
								layout: 'form',
								labelWidth: 29,
								items: [{
									fieldLabel: langs('по'),
									hiddenName: 'Diag_Code_To',
									disabled: true,
									listeners: {
										'select': function(combo, record, index) {
											combo.setRawValue(record.get('Diag_Code') + " " + record.get('Diag_Name'));
										},
										'change': function(combo, newValue) {
											if ( newValue != '' )
												Ext.getCmp('PersonDispViewFilterForm').getForm().findField('Sickness_id').clearValue();	
										}.createDelegate(this)
									},
									listWidth: 600,
									//~ tabIndex: TABINDEX_PERSDISPSW + 67,
									valueField: 'Diag_Code',
									width: 230,
									xtype: 'swdiagcombo'
								}]
							}]
						}]
					}, {
						autoHeight: true,
						bodyStyle: 'margin-top: 5px;',
						border: false,
						layout: 'form',
						listeners: {
							'activate': function(panel) {
								panel.ownerCt.ownerCt.getForm().findField('pmUser_insID').focus(250, true);
							}
						},
						title: '<u>6</u>. Пользователь',

						// tabIndexStart: TABINDEX_PERSCARDSW + 68
						items: [{
							autoHeight: true,
							style: 'padding: 0px;',
							title: 'Добавление',
							width: 755,
							xtype: 'fieldset',

							items: [ new sw.Promed.SwProMedUserCombo({
								hiddenName: 'pmUser_insID',
								listeners: {
									'keydown': function (inp, e) {
										if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
											e.stopEvent();
											inp.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.buttons[6].focus();
										}
									}
								},
								tabIndex: TABINDEX_PERSCARDSW + 68,
								width: 300
							}), {
								fieldLabel: 'Дата',
								name: 'InsDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
								tabIndex: TABINDEX_PERSCARDSW + 69,
								width: 100,
								xtype: 'swdatefield'
							}, {
								fieldLabel: 'Диапазон дат',
								name: 'InsDate_Range',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex: TABINDEX_PERSCARDSW + 70,
								width: 170,
								xtype: 'daterangefield'
							}]
						}, {
							autoHeight: true,
							style: 'padding: 0px;',
							title: 'Изменение',
							width: 755,
							xtype: 'fieldset',

							items: [ new sw.Promed.SwProMedUserCombo({
								hiddenName: 'pmUser_updID',
								tabIndex: TABINDEX_PERSCARDSW + 71,
								width: 300
							}), {
								fieldLabel: 'Дата',
								name: 'UpdDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
								tabIndex: TABINDEX_PERSCARDSW + 72,
								width: 100,
								xtype: 'swdatefield'
							}, {
								fieldLabel: 'Диапазон дат',
								name: 'UpdDate_Range',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex: TABINDEX_PERSCARDSW + 73,
								width: 170,
								xtype: 'daterangefield'
							}]
						},{
							xtype  : 'panel',
							border : false,
							layout: 'form',
							hidden: !isSuperAdmin(),
							autoheight: true,
							items: [{
								fieldLabel: lang['sql-zapros'],
								name: 'onlySQL',
								tabIndex: TABINDEX_PERSCARDSW + 74,
								width: 180,
								xtype: 'checkbox'
							}]
						}]
					}]
				})],
				keys: [{
					fn: function(e) {
						Ext.getCmp('PersonCardSearchWindow').doSearch();
					},
					key: Ext.EventObject.ENTER,
					scope: this,
					stopEvent: true
				}],
				labelAlign: 'right',
				labelWidth: 130,
				region: 'north'
			}),
			new sw.Promed.ViewFrame({
				actions: [
					{ name: 'action_add', disabled: true},
					{ name: 'action_edit', disabled: true},
					{ name: 'action_view', handler: function() {Ext.getCmp('PersonCardSearchWindow').viewPersonCard(); } },
					{ name: 'action_delete', disabled: true},
					{ name: 'action_refresh' },
					{ name: 'action_print', hidden: false /*getRegionNick() == 'kareliya'*/,//#115835 не скрывать для Карелии
						menuConfig: {
							printTap2015: {name: 'printTap2015', text: 'Печать бланка ТАП (до 2015г)', 
								handler: function(){
									var grid = this.findById('PersonCardViewGrid').getGrid();
		                            var selected_record = grid.getSelectionModel().getSelected();
		                            var url = "";
								    url = '/?c=EvnPL&m=printEvnPLBlank';
		                            if (selected_record && selected_record.get('Person_id')) {
								        url = url + '&Person_id=' + selected_record.get('Person_id');
			                        }
			                        window.open(url, '_blank');
								}.createDelegate(this)
							}
						} 
					}
				],
				autoExpandColumn: 'autoexpand',
				autoExpandMin: 100,
				autoLoadData: false,
				dataUrl: C_SEARCH,
				focusOn: {
					name: 'PCSW_SearchButton',
					type: 'field'
				},
				id: 'PersonCardViewGrid',
				pageSize: 100,
				paging: true,
				region: 'center',
				root: 'data',
				onLoadData: function (data_is_exists) {
					var pc_grid = this.getGrid();
					if (true) {
						this.setActionDisabled('action_openac', true);
						this.setActionHidden('action_openac',true);
					}
					if (data_is_exists) {
						pc_grid.getSelectionModel().selectFirstRow();
						//Закоментировал, чтобы фокус оставался на этом гриде. Для хоткеев
						//pc_grid.getView().focusRow(0);
					}
				},
				onRowSelect:function (sm, rowIdx, record) {
					if (true) {
						var disable = false;
						log(record);
						disable = (Ext.isEmpty(record.get('PersonAmbulatCard_id')));
						this.setActionDisabled('action_openac', disable);
						this.setActionHidden('action_openac',disable);
					}
				},
				stringfields: [
					{ name: 'PersonCard_id', type: 'int', header: 'ID', key: true },
					{ name: 'Person_id', type: 'int', hidden: true },
					{ name: 'Server_id', type: 'int', hidden: true },
					{ name: 'PersonCard_Code', header: '№ амб карты', width: 80, renderer: function(v, p, row) {
						var store = win.findById('PersonCardViewGrid').getGrid().getStore();
						if (!Ext.isEmpty(store.baseParams.PartMatchSearch) && !Ext.isEmpty(store.baseParams.PersonCard_Code) && !Ext.isEmpty(v)) {
							// подсвечиваем
							v = v.replace(new RegExp(store.baseParams.PersonCard_Code), "<span style='background-color:yellow;'>"+store.baseParams.PersonCard_Code+"</span>");
						}
						return v;
					}},
					{ name: 'Person_Surname',  type: 'string', header: 'Фамилия', id: 'autoexpand', width: 100 },
					{ name: 'Person_Firname',  type: 'string', header: 'Имя', width: 100 },
					{ name: 'Person_Secname',  type: 'string', header: 'Отчество', width: 100 },
					{ name: 'Person_Birthday',  type: 'date', header: 'Дата рождения', renderer: Ext.util.Format.dateRenderer('d.m.Y') },
					{ name: 'Person_deadDT', type:'date', header: 'Дата смерти', renderer: Ext.util.Format.dateRenderer('d.m.Y') },
					{ name: 'PersonCard_begDate',  type: 'date', header: 'Прикрепление', renderer: Ext.util.Format.dateRenderer('d.m.Y') },
					{ name: 'LpuAttachType_id', type: 'int', hidden: true},
					{ name: 'PersonCard_endDate',  type: 'date', header: 'Открепление', renderer: Ext.util.Format.dateRenderer('d.m.Y') },
					{ name: 'LpuAttachType_Name',  type: 'string', header: 'Тип прикрепления', width: 200 },
					{ name: 'LpuRegionType_Name',  type: 'string', header: 'Тип участка', width: 200 },
					{ name: 'LpuRegion_Name',  type: 'string', header: 'Участок' },
                    { name: 'LpuRegion_FapName', type: 'string', header: 'ФАП участок',width:100, hidden: !getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza']) /*getRegionNick() != 'perm'*/},
					{ name: 'PersonCardAttach', type:'checkbox', header: 'Заявл.'},
					{ name: 'AmbulatCardLocatType_Name',  type: 'string', header: 'Местонахождение амб. карты' },
					{ name: 'PersonAmbulatCard_id',hidden:true,type:'int'},
					{ name: 'PersonCard_IsAttachCondit',  header: 'Усл. прикрепл.', type: 'checkbox' },
					{ name: 'Person_IsBDZ',  header: 'БДЗ', type: 'checkbox', width: 40 },
					{ name: 'Person_IsFedLgot',  header: (getRegionNick().inlist([ 'kz' ]) ? 'Льгота' : 'Фед.льг.'), type: 'checkbox', width: 40 },
					{ name: 'Person_IsRefuse',  header: 'Отказ', type: 'checkbox', width: 40 },
					{ name: 'Person_NextYearRefuse',  header: 'Отказ на след. год', type: 'checkbox', width: 110 },
					{ name: 'Person_IsRegLgot',  header: 'Рег.льг.', type: 'checkbox', width: 40, hidden: getRegionNick().inlist([ 'kz' ])},
					{ name: 'Person_Is7Noz',  header: '7 ноз.', type: 'checkbox', width: 40 },
					{ name: 'Person_UAddress',  header: langs('Адрес регистрации'), type: 'string', width: 240 },
					{ name: 'Person_PAddress',  header: langs('Адрес проживания'), type: 'string', width: 240 },
					{ name: 'Person_Phone',  header: langs('Телефон'), type: 'string', width: 70 },
					{ name: 'MseInvalidGroupType_Name', hidden: getRegionNick()!='ufa',  header: langs('Группа инвалидности'), type: 'string', width: 150 },
					{ name: 'MseDiag_Code', hidden: getRegionNick()!='ufa', header: langs('Диагноз'), type: 'string', width: 60 }
				],
				toolbar: true,
				totalProperty: 'totalCount'
			})]
		});
		sw.Promed.swPersonCardSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [/*{
		key: Ext.EventObject.INSERT,
		fn: function(e) {Ext.getCmp("PersonCardSearchWindow").addPersonCard();},
		stopEvent: true
	},*/ {
		key: "0123456789",
		alt: true,
		fn: function(e) {Ext.getCmp("PersonCardFilterTabPanel").setActiveTab(Ext.getCmp("PersonCardFilterTabPanel").items.items[ e - 49 ]);},
		stopEvent: true
	}, {
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('PersonCardSearchWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.J:
					current_window.hide();
				break;

				case Ext.EventObject.C:
					current_window.doReset(true);
				break;
			}
		},
		key: [ Ext.EventObject.J, Ext.EventObject.C ],
		stopEvent: true
	}],
	layout: 'border',
        listeners: {
            'resize': function (win, nW, nH, oW, oH) {
//                    log(nW);
                win.findById('PersonCardFilterTabPanel').setWidth(nW - 5);
                win.findById('PersonCardSearchForm').setWidth(nW - 5);
            }
        },
	maximizable: false,
	maximized: true,
	minHeight: 550,
	minWidth: 900,
	modal: false,
	plain: true,
	resizable: false,
	refreshPersonCardViewGrid: function() {
		// так как у нас грид не обновляется, то просто ставим фокус в первое поле ввода формы
		this.findById('PersonCardSearchForm').getForm().findField('Person_Surname').focus(true, 100);
	},
	openPersonAmbulatCard:function(){
	var win = this;
		var grid = Ext.getCmp('PersonCardViewGrid').getGrid();
		var params = {};
		var record = grid.getSelectionModel().getSelected();
		if(!record|| !record.get('PersonAmbulatCard_id')){
			var msg = 'Запись о прикреплении не связана с Амбулаторной картой';
			Ext.Msg.alert("Ошибка", msg);
			return false;
		}
		params ={
			action:'edit',
			PersonAmbulatCard_id:record.get('PersonAmbulatCard_id')
		}

		params.callback= function(){
			grid.getStore().reload();
		}
		getWnd('swPersonAmbulatCardEditWindow').show(params);
	 
	},
	show: function() {
		sw.Promed.swPersonCardSearchWindow.superclass.show.apply(this, arguments);
		var current_window = this;
		var form = current_window.findById('PersonCardSearchForm');

		var grid = current_window.findById('PersonCardViewGrid');
		this.viewOnly = false;
		if(arguments[0] && arguments[0].viewOnly)
			this.viewOnly = arguments[0].viewOnly;
		if(!this.viewOnly)
			grid.addActions({name:'action_openac', id: 'id_action_openac', handler: function() {this.openPersonAmbulatCard();}.createDelegate(this),hidden:true,disabled:true, text:'Амбулаторная карта', tooltip: 'Амбулаторная карта'});
		current_window.findById('PersonCardFilterTabPanel').setActiveTab(5);
		current_window.findById('PersonCardFilterTabPanel').setActiveTab(4);
		current_window.findById('PersonCardFilterTabPanel').setActiveTab(3);
		current_window.findById('PersonCardFilterTabPanel').setActiveTab(2);
		current_window.findById('PersonCardFilterTabPanel').setActiveTab(1);
		current_window.findById('PersonCardFilterTabPanel').setActiveTab(0);

		current_window.doReset(true);
		current_window.doLayout();

		// Автозаполнение полей: "Тип прикрепления:", "Тип участка:", "Участок:"
		var lpu_attach_type_id = null;
		var lpu_region_type_id = null;
		var lpu_region_id = null;
		if ( arguments[0] && arguments[0].LpuAttachType_id )
		{
			lpu_attach_type_id = arguments[0].LpuAttachType_id;
			lpu_region_type_id = arguments[0].LpuRegionType_id;
			lpu_region_id = arguments[0].LpuRegion_id;
			form.getForm().findField('LpuAttachType_id').setValue(lpu_attach_type_id);
			if ( form.getForm().findField('LpuRegionType_id').getStore().getCount() == 0 )
			{
				form.getForm().findField('LpuRegionType_id').getStore().load({
					callback: function(records, options, success) {
						form.getForm().findField('LpuRegionType_id').setValue(lpu_region_type_id);
					}
				});
			}
			else
			{
				form.getForm().findField('LpuRegionType_id').setValue(lpu_region_type_id);
			}
		}

		/*form.getForm().findField('LpuRegion_id').getStore().load({
			callback: function(records, options, success) {
				if ( !success ) {
					sw.swMsg.alert('Ошибка', 'Ошибка при загрузке справочника участков');
					return false;
				}
				if (lpu_region_id)
					form.getForm().findField('LpuRegion_id').setValue(lpu_region_id);
			},
			params: {
				'add_without_region_line': true
			}
		});*/

		form.getForm().findField('AttachLpu_id').disable();
		if ( form.getForm().findField('AttachLpu_id').getStore().getCount() == 0 ) {
			form.getForm().findField('AttachLpu_id').getStore().load({
				callback: function(records, options, success) {
					if ( !success ) {
						form.getForm().findField('AttachLpu_id').getStore().removeAll();
						sw.swMsg.alert('Ошибка', 'Ошибка при загрузке справочника ЛПУ');
						return false;
					}

					var lpu_id = Ext.globalOptions.globals.lpu_id;
					form.getForm().findField('AttachLpu_id').setValue(lpu_id);
				}
			});
		}
		

		form.getForm().getEl().dom.action = "/?c=Search&m=printSearchResults";
		form.getForm().getEl().dom.method = "post";
		form.getForm().getEl().dom.target = "_blank";
		form.getForm().standardSubmit = true;

		if ( getGlobalOptions().PersonCardCount == undefined ) {
			var loadMask = new Ext.LoadMask(Ext.get('PersonCardSearchWindow'), { msg: "Получение количества людей прикрепленных к ЛПУ..." });

			Ext.Ajax.request({
				callback: function(opt, success, resp) {
					loadMask.hide();
					if ( resp.responseText != '' ) {
						var response_data = Ext.util.JSON.decode(resp.responseText);

						if ( response_data && response_data[0]['PersonCard_Count'] ) {
							current_window.setTitle(WND_POL_PERSCARDSEARCH + ' (Прикреплено к ЛПУ: ' + response_data[0]['PersonCard_Count'] + ')');
							getGlobalOptions().PersonCardCount = response_data[0]['PersonCard_Count'];
						}
					}
				},
				url: C_PERSONCARD_COUNT
			});
		}
		else {
			current_window.setTitle(WND_POL_PERSCARDSEARCH + ' (Прикреплено к ЛПУ: ' + getGlobalOptions().PersonCardCount + ')');
		}

        //if(getRegionNick() == 'perm'){
		var LpuRegionType_Name_index = Ext.getCmp('PersonCardViewGrid').getGrid().colModel.findColumnIndex('LpuRegionType_Name');
		var LpuRegion_Name_index = Ext.getCmp('PersonCardViewGrid').getGrid().colModel.findColumnIndex('LpuRegion_Name');
		if(getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza','vologda'])){
            form.getForm().findField('LpuRegionType_id').setFieldLabel('Тип основного участка');
            form.getForm().findField('LpuRegion_id').setFieldLabel('Основной участок');
			Ext.getCmp('PersonCardViewGrid').getGrid().getColumnModel().setColumnHeader(LpuRegionType_Name_index,'Тип основного участка');
			Ext.getCmp('PersonCardViewGrid').getGrid().getColumnModel().setColumnHeader(LpuRegion_Name_index,'Основной участок');
        }
        else {
            form.getForm().findField('LpuRegionType_id').setFieldLabel('Тип участка');
            form.getForm().findField('LpuRegion_id').setFieldLabel('Участок');
			Ext.getCmp('PersonCardViewGrid').getGrid().getColumnModel().setColumnHeader(LpuRegionType_Name_index,'Тип участка');
			Ext.getCmp('PersonCardViewGrid').getGrid().getColumnModel().setColumnHeader(LpuRegion_Name_index,'Участок');
        }
        form.getForm().findField('LpuRegionType_id').getStore().filterBy(
            function(record)
            {
                if (record.data.LpuRegionType_SysNick.inlist(['feld']) && getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa'])/*getRegionNick() == 'perm'*/)
                    return false;
                else
                    return true;
            }
        );
	},
	title: WND_POL_PERSCARDSEARCH,
	viewPersonCard: function() {
		var current_window = this;
		var grid = current_window.findById('PersonCardViewGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();

		if ( !current_row ) {
			return;
		}

		if ( getWnd('swPersonCardEditWindow').isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования карты пациента уже открыто');
			return false;
		}

		var attachType = 0;
		if(!Ext.isEmpty(current_row.data.LpuAttachType_id)){
			switch ( current_row.data.LpuAttachType_id )
			{
				case 1:
					attachType = 'common_region';
					break;
				case 2:
					attachType = 'ginecol_region';
					break;
				case 3:
					attachType = 'stomat_region';
					break;
				case 4:
					attachType = 'service_region';
					break;
				case 5:
					attachType = 'dms_region';
					break;
				default:
					attachType = 0;
			}
		}
		getWnd('swPersonCardEditWindow').show({
			action: 'view',
			callback: function() {
				current_window.refreshPersonCardViewGrid();
			},
			onHide: function() {
				current_window.refreshPersonCardViewGrid();
			},
			PersonCard_id: current_row.data.PersonCard_id,
			Person_id: current_row.data.Person_id,
			Server_id: current_row.data.Server_id,
			attachType: attachType
		});
	},
	width: 900
});