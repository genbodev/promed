/**
* Форма Журнал направлений во внешние лаборатории по КВИ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
*/

sw.Promed.swEvnDirectionCVIJournalWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Журнал направлений во внешние лаборатории по КВИ',
	modal: true,
	resizable: false,
	maximized: true,
	shim: false,
	plain: true,
	layout: 'fit',
	buttonAlign: "right",
	closeAction: 'hide',
	show: function() {
		sw.Promed.swEvnDirectionCVIJournalWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		var base_form = this.SearchFilters.getForm();
		
		base_form.findField('MedPersonal_id').getStore().load({
			params: {Lpu_id: getGlobalOptions().lpu_id}
		});
		
		base_form.findField('MedPersonal_tid').getStore().load({
			params: {Lpu_id: getGlobalOptions().lpu_id}
		});
		
		this.doReset();
	},
	
	doSearch: function() {
		var grid = this.EvnDirectionCVIGrid.getGrid(),
			form = this.SearchFilters.getForm();
			
		if( !form.isValid() ) {
			return false;
		}
		
		grid.getStore().baseParams = form.getValues();
		grid.getStore().load();
	},
	
	doReset: function() {
		this.SearchFilters.getForm().reset();
		this.doSearch();
	},
	
	addEvnDirectionCvi: function() {
		var win = this;
		
		if (getWnd('swPersonSearchWindow').isVisible()) {
			Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}
		
		getWnd('swPersonSearchWindow').show({
			onSelect: function(person_data) {
				getWnd('swPersonSearchWindow').hide();
				getWnd('swEvnDirectionCviEditWindow').show({
					action: 'add',
					formParams: {
						Person_id: person_data.Person_id,
						PersonEvn_id: person_data.PersonEvn_id,
						Server_id: person_data.Server_id
					},
					callback: function () {
						win.doSearch();
					}
				});		
			}
		});
	},
	
	editEvnDirectionCvi: function(action) {
		
		var win = this,
			rec = this.EvnDirectionCVIGrid.getGrid().getSelectionModel().getSelected();
			
		if ( !rec || !rec.get('EvnDirectionCVI_id') ) return false;
		
		getWnd('swEvnDirectionCviEditWindow').show({
			action: action,
			EvnDirectionCVI_id: rec.get('EvnDirectionCVI_id'),
			callback: function () {
				win.doSearch();
			}
		});
	},

	deleteTicket: function() {
		console.log('Delete');
	},
	
	initComponent: function() {
		var win = this;
		
		this.SearchFilters = new Ext.form.FormPanel({
			bodyStyle: 'padding: 5px 5px 0',
			border: true,
			frame: true,
			region: 'north',
			labelAlign: 'right',
			height: 265,
			labelWidth: 120,
			items: [{
				layout: 'column',
				border: false,
				defaults: {
					layout: 'form',
					xtype: 'fieldset',
					height: 245,
					style: 'padding: 7px 10px 10px; margin-right: 15px;'
				},
				items: [{
					title: langs('Пациент'),
					items: [{
						xtype: 'textfieldpmw',
						width: 250,
						name: 'Person_SurName',
						fieldLabel: 'Фамилия',
						maskRe: /[^%]/
					}, {
						xtype: 'textfieldpmw',
						width: 250,
						name: 'Person_FirName',
						fieldLabel: 'Имя',
						maskRe: /[^%]/
					}, {
						xtype: 'textfieldpmw',
						width: 250,
						name: 'Person_SecName',
						fieldLabel: 'Отчество',
						maskRe: /[^%]/
					}, {
						fieldLabel: 'Дата рождения',
						name: 'Person_BirthDay',
						width: 180,
						xtype: 'swdatefield'
					}, {
						layout: 'column',
						items: [{
							layout: 'form',
							items: [{
								xtype: 'numberfield',
								name: 'Person_AgeFrom',
								fieldLabel: 'Возраст с',
								width: 75
							}]
						}, {
							layout: 'form',
							labelWidth: 25,
							items: [{
								xtype: 'numberfield',
								name: 'Person_AgeTo',
								fieldLabel: 'по',
								width: 75
							}]
						}]
					}, {
						layout: 'column',
						items: [{
							layout: 'form',
							items: [{
								xtype: 'numberfield',
								name: 'PersonBirthYearFrom',
								fieldLabel: 'Год рождения с',
								width: 75
							}]
						}, {
							layout: 'form',
							labelWidth: 25,
							items: [{
								xtype: 'numberfield',
								name: 'PersonBirthYearTo',
								fieldLabel: 'по',
								width: 75
							}]
						}]
					}]
				}, {
					title: langs('Направление'),
					labelWidth: 230,
					items: [{
						fieldLabel: 'Регистрационный номер',
						width: 100,
						name: 'EvnDirectionCVI_RegNumber',
						xtype: 'textfield',
						maskRe: /\d/,
						autoCreate: {tag: "input", maxLength: 12, autocomplete: "off"}
					}, {
						fieldLabel: 'Лаборатория сдачи образцов',
						width: 300,
						name: 'EvnDirectionCVI_Lab',
						xtype: 'textfield',
						autoCreate: {tag: "input", maxLength: 120, autocomplete: "off"}
					}, {
						fieldLabel: 'Предварительный диагноз',
						width: 300,
						name: 'Diag_id',
						xtype: 'swdiagcombo'
					}, {
						fieldLabel: 'Дата заболевания',
						name: 'EvnDirectionCVI_setDate_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 180,
						xtype: 'daterangefield'
					}, {
						fieldLabel: 'Направивший врач',
						hiddenName: 'MedPersonal_id',
						allowBlank: true,
						width: 300,
						listWidth: 400,
						xtype: 'swmedpersonalcombo',
					}, {
						fieldLabel: 'Дата взятия образца',
						name: 'EvnDirectionCVI_takeDate_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 180,
						xtype: 'daterangefield'
					}, {
						fieldLabel: 'Сотрудник, взявший образец',
						hiddenName: 'MedPersonal_tid',
						allowBlank: true,
						width: 300,
						listWidth: 400,
						xtype: 'swmedpersonalcombo',
					}, {
						fieldLabel: 'Номер образца',
						width: 100,
						name: 'EvnDirectionCVI_Number',
						xtype: 'textfield',
						maskRe: /\d/,
						autoCreate: {tag: "input", maxLength: 8, autocomplete: "off"}
					}, {
						fieldLabel: 'Дата отправки образца в лабораторию',
						name: 'EvnDirectionCVI_sendDate_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 180,
						xtype: 'daterangefield'
					}]
				}]
			}],
			keys: [{
				fn: function(e) {
					win.doSearch();
				},
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});
		
		this.EvnDirectionCVIGrid = new sw.Promed.ViewFrame({
			id: this.id + 'EvnDirectionCVIGrid',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			anchor: '100%',
			region: 'center',
            root: 'data',
			border: false,
			enableColumnHide: false,
			obj_isEvn: true,
			object: 'EvnDirectionCVI',
			linkedTables: '',
			actions: [
				{ name: 'action_add', handler: this.addEvnDirectionCvi.createDelegate(this) },
				{ name: 'action_edit', handler: this.editEvnDirectionCvi.createDelegate(this, ['edit']) },
				{ name: 'action_view', handler: this.editEvnDirectionCvi.createDelegate(this, ['view']) },
				{ name: 'action_delete', msg: 'Удалить выбранное направление?', url: '/?c=EvnDirectionCVI&m=delete' },
				{ name: 'action_refresh', hidden: true },
				{ name: 'action_print', menu: new Ext.menu.Menu({
					items: [{
						text: 'Печать направления',
						handler: function() {
							var rec = win.EvnDirectionCVIGrid.getGrid().getSelectionModel().getSelected();
								
							if ( !rec || !rec.get('EvnDirectionCVI_id') ) return false;
							
							printBirt({
								'Report_FileName': 'EvnDirectionCVI_Print.rptdesign',
								'Report_Params': '&paramEvnDirectionCVI=' + rec.get('EvnDirectionCVI_id'),
								'Report_Format': 'pdf'
							});
						}
					}, {
						text: 'Печать списка',
						handler: function() {
							win.EvnDirectionCVIGrid.printObjectListFull()
						}
					}]
				})}
			],
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{ name: 'EvnDirectionCVI_id', type: 'int', hidden: true, key: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'Person_Fio', type: 'string', header: 'ФИО пациента', width: 200},
				{ name: 'Diag_Name', type: 'string', header: 'Предварительный диагноз', width: 170},
				{ name: 'Evn_Name', type: 'string', header: 'Случай лечения', width: 150},
				{ name: 'EvnDirectionCVI_setDate', type: 'string', header: 'Дата заболевания', width: 100},
				{ name: 'MedPersonal_Fio', type: 'string', header: 'Направивший врач', width: 120},
				{ name: 'material', type: 'string', header: 'Биоматериал заявки', width: 120},
				{ name: 'EvnDirectionCVI_takeDT', type: 'string', header: 'Дата взятия образца', width: 120},
				{ name: 'MedPersonal_tFio', type: 'string', header: 'Образец взял', width: 120},
				{ name: 'tests', type: 'string', header: 'Взятые образцы', width: 150},
				{ name: 'EvnDirectionCVI_IsCito', type: 'checkbox', header: 'Cito!', width: 50},
				{ name: 'EvnDirectionCVI_sendDT', type: 'string', header: 'Дата отправки в лабораторию', width: 120},
				{ name: 'EvnDirectionCVI_Lab', type: 'string', header: 'Лаборатория', width: 120},
				{ name: 'results', type: 'string', header: 'Результаты', width: 150, id: 'autoexpand'},
			],
			onRowSelect: function(sm, rowIdx, rec) {
				
			},
			paging: true,
			pageSize: 50,
			dataUrl: '/?c=EvnDirectionCVI&m=loadJournal',
			totalProperty: 'totalCount'
		});
		
		Ext.apply(this,	{
			layout: 'border',
			buttons: [{
				handler: this.doSearch.createDelegate(this),
				iconCls: 'search16',
				text: BTN_FRMSEARCH
			},
			{
				handler: this.doReset.createDelegate(this),
				iconCls: 'resetsearch16',
				text: BTN_FRMRESET
			},
			'-',
			HelpButton(this),
			{
				text: BTN_FRMCLOSE,
				tabIndex: -1,
				tooltip: BTN_FRMCLOSE,
				iconCls: 'cancel16',
				handler: this.hide.createDelegate(this, [])
			}],
			items: [
				this.SearchFilters, 
				this.EvnDirectionCVIGrid
			]
		});
		
		sw.Promed.swEvnDirectionCVIJournalWindow.superclass.initComponent.apply(this, arguments);
	}
});