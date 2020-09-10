/**
* swReceptStreamInputWindow - окно потокового ввода рецептов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-31.07.2009
* @comment      Префикс для id компонентов RSIF (ReceptStreamInputForm)
*/

sw.Promed.swReceptStreamInputWindow = Ext.extend(sw.Promed.BaseForm, {
	begDate: null,
	begTime: null,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	deleteEvnRecept: function() {
		var grid = this.findById('RSIF_EvnReceptGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected()) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		if ( !selected_record.get('EvnRecept_id') ) {
			return false;
		}
		else if ( selected_record.get('ReceptRemoveCauseType_id') && getRegionNick() != 'msk' ) {
			sw.swMsg.alert(lang['oshibka'], lang['retsept_uje_otmechen_kak_udalennyiy']);
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
						EvnRecept_id: selected_record.get('EvnRecept_id'),
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
	id: 'ReceptStreamInputWindow',
	initComponent: function() {
        var _this = this;

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this, TABINDEX_ERSIF + 6),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				tabIndex: TABINDEX_ERSIF + 7,
				text: lang['zakryit']
			}],
			items: [{
				autoHeight: true,
				layout: 'form',
				region: 'north',
				items: [ new Ext.form.FormPanel({
					bodyStyle: 'padding: 5px',
					border: false,
					frame: false,
					id: 'RSIF_ReceptStreamInformationForm',
					items: [{
						disabled: true,
						fieldLabel: lang['polzovatel'],
						id: 'RSIF_pmUser_Name',
						width: 380,
						xtype: 'textfield'
					}, {
						disabled: true,
						fieldLabel: lang['data_nachala_vvoda'],
						id: 'RSIF_Stream_begDateTime',
						width: 130,
						xtype: 'textfield'
					}],
					labelAlign: 'right',
					labelWidth: 120
				}),
				new Ext.form.FormPanel({
					animCollapse: false,
					autoHeight: true,
					bodyBorder: false,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					// collapsible: true,
					frame: false,
					id: 'ReceptStreamInputParams',
					items: [{
						allowBlank: true,
						fieldLabel: lang['data'],
						format: 'd.m.Y',
						id: 'RSIF_EvnRecept_setDate',
						listeners: {
							'change': function(field, newValue, oldValue) {
								var base_form = this.findById('ReceptStreamInputParams').getForm();

								var lpu_section_id = base_form.findField('LpuSection_id').getValue();
								var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();


								base_form.findField('LpuSection_id').clearValue();
								base_form.findField('MedStaffFact_id').clearValue();

								if ( !newValue ) {
									setLpuSectionGlobalStoreFilter({
										allowLowLevel: 'yes',
										isDlo: true
									});

									setMedStaffFactGlobalStoreFilter({
										allowLowLevel: 'yes',
										isDlo: true,
                                        regionCode: getGlobalOptions().region.number
									});
								}
								else {
									setLpuSectionGlobalStoreFilter({
										allowLowLevel: 'yes',
										isDlo: true,
										onDate: Ext.util.Format.date(newValue, 'd.m.Y')
									});

									setMedStaffFactGlobalStoreFilter({
										allowLowLevel: 'yes',
										isDlo: true,
                                        regionCode: getGlobalOptions().region.number,
										onDate: Ext.util.Format.date(newValue, 'd.m.Y')
									});
								}

								base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
								base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

								if ( base_form.findField('LpuSection_id').getStore().getById(lpu_section_id) ) {
									base_form.findField('LpuSection_id').setValue(lpu_section_id);
								}

								if ( base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
									base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
								}
							}.createDelegate(this),
							'keydown': function (inp, e) {
								if ( e.getKey() == e.TAB && e.shiftKey == false ) {
									e.stopEvent();
									inp.ownerCt.findById("RSIF_LpuBuildingCombo").focus(false);
								}
							}
						},
						name: 'EvnRecept_setDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: TABINDEX_ERSIF + 8,
						width: 150,
						xtype: 'swdatefield'
					},
					new sw.Promed.SwLpuBuildingGlobalCombo({
						focusOnShiftTab: 'RSIF_EvnRecept_setDate',
						id: 'RSIF_LpuBuildingCombo',
						lastQuery: '',
						listWidth: 700,
						linkedElements: [
							'RSIF_LpuSectionCombo'
						],
						tabIndex: TABINDEX_ERSIF + 1,
						width: 400
					}),
					new sw.Promed.SwLpuSectionGlobalCombo({
						id: 'RSIF_LpuSectionCombo',
						lastQuery: '',
						listWidth: 700,
						linkedElements: [
							'RSIF_MedPersonalCombo'
						],
						parentElementId: 'RSIF_LpuBuildingCombo',
						tabIndex: TABINDEX_ERSIF + 2,
						width: 400
					}),
					new sw.Promed.SwMedStaffFactGlobalCombo({
						id: 'RSIF_MedPersonalCombo',
						lastQuery: '',
						listWidth: 700,
						parentElementId: 'RSIF_LpuSectionCombo',
						tabIndex: TABINDEX_ERSIF + 3,
						width: 400
					}), {
						allowBlank: false,
						id: 'RSIF_ReceptTypeCombo',
						listeners: {
							'keydown': function (inp, e) {
								if ( e.getKey() == e.TAB ) {
									if ( e.shiftKey == false ) {
										if ( inp.ownerCt.ownerCt.ownerCt.findById("RSIF_EvnReceptGrid").getStore().getCount() > 0 ) {
											e.stopEvent();
											inp.ownerCt.ownerCt.ownerCt.findById("RSIF_EvnReceptGrid").getView().focusRow(0);
											inp.ownerCt.ownerCt.ownerCt.findById("RSIF_EvnReceptGrid").getSelectionModel().selectFirstRow();
										}
									}
									else {
										e.stopEvent();
										inp.ownerCt.ownerCt.ownerCt.findById("RSIF_MedPersonalCombo").focus(false);
									}
								}
							}
						},
						tabIndex: TABINDEX_ERSIF + 4,
						width: 150,
						xtype: 'swrecepttypecombo'
					}],
					labelAlign: 'right',
					labelWidth: 120,
					title: lang['parametryi_vvoda']
				})]
			},
            new Ext.form.FormPanel({
                animCollapse: false,
                bodyBorder: false,
                border: false,
                frame: false,
                region: 'center',
                items: [
                    new sw.Promed.ViewFrame({
                        autoExpandColumn: 'autoexpand',
                        autoExpandMin: 100,
                        border: false,
                        title: lang['lgotnyie_retseptyi'],
                        id: 'RSIF_EvnReceptGrid',
                        autoLoadData: false,
                        tabIndex: TABINDEX_ERSIF + 5,
                        loadMask: true,
                        stringfields: [
                            { name: 'EvnRecept_id', type: 'string', header: 'EvnRecept_id',  key: true },
                            { name: 'PersonEvn_id', type: 'string', header: 'PersonEvn_id',  hidden: true },
                            { name: 'EvnRecept_pid', type: 'string', header: 'EvnRecept_pid',  hidden: true },
                            { name: 'MorbusType_SysNick', type: 'string', header: 'MorbusType_SysNick',  hidden: true },
                            { name: 'ReceptRemoveCauseType_id', type: 'string', header: 'ReceptRemoveCauseType_id',  hidden: true },
                            { name: 'Server_id', type: 'string', header: 'Server_id',  hidden: true },
                            { name: 'Person_Birthday', type: 'string', header: lang['den_rojdeniya'],  hidden: true },
							{ name: 'Person_id', type: 'string', header: 'PersonEvn_id',  hidden: true},
                            { name: 'Person_Surname', type: 'string', header: lang['familiya'], sort: true , width: 130 },
                            { name: 'Person_Firname', type: 'string', header: lang['imya'], sort: true , width: 130 },
                            { name: 'Person_Secname', type: 'string', header: lang['otchestvo'], sort: true , width: 130 },
                            { name: 'EvnRecept_setDate', type: 'date', header: lang['data'], sort: true , width: 130 },
                            { name: 'Drug_Name', type: 'string', header: lang['medikament'], id: 'autoexpand', sort: true , width: 100 },
                            { name: 'EvnRecept_Ser', type: 'string', header: lang['seriya'], sort: true , width: 130 },
                            { name: 'EvnRecept_Num', type: 'string', header: lang['nomer'], sort: true , width: 130 },
                            { name: 'MedPersonal_Fio', type: 'string', header: lang['vrach'], sort: true , width: 130 },
                            { name: 'EvnRecept_IsSigned', type: 'checkcolumn', header: lang['podpisan'], sort: true , width: 130 },
                            { name: 'Drug_rlsid', type: 'int', hidden:true },
                            { name: 'Drug_id', type: 'int', hidden:true },
                            { name: 'DrugComplexMnn_id', type: 'int', hidden:true }
                        ],
                        actions: [
                            //{name:'action_add', hidden: !getRegionNick().inlist(['perm', 'buryatiya','khak', 'pskov', 'saratov', 'krym' ]), handler: function(){ _this.openEvnReceptEditWindow('add', getRegionNick().inlist(['khak', 'pskov', 'saratov', 'buryatiya', 'krym' ])? 'pregnancy' : 'common');}, tooltip: getRegionNick()=='buryatiya'? lang['retsept_po_osobyim_gruppam_zabolevaniy'] : lang['retsept_po_obscheterapevticheskoy_gruppe_zabolevaniy']},
                            {name:'action_add',handler:function(){_this.openEvnReceptEditWindow('add','common');}, disabled: getRegionNick() == 'msk'},
                            {name:'action_edit', hidden: true, handler: function() { _this.openEvnReceptEditWindow('edit'); }},
                            {name:'action_view', handler: function() { _this.openEvnReceptEditWindow('view'); }},
                            {name:'action_delete', iconCls: 'delete16', handler: function() { _this.deleteEvnRecept(); }, text: langs(getRegionNick() == 'msk' ? 'Аннулировать' : 'Удалить')},
                            {name:'action_print'}

                        ],
                        onDblClick: function(){
                            _this.openEvnReceptEditWindow('edit');
                        },
                        dataUrl: C_EVNREC_STREAM,
                        stripeRows: true
                    })
                ]
            })]
		});
		sw.Promed.swReceptStreamInputWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		key: [ Ext.EventObject.INSERT ],
		fn: function(inp, e) {
			if (Ext.getCmp('RSIF_EvnReceptGrid').getAction('action_add').isDisabled()) {
				return false;
			}

			Ext.getCmp('ReceptStreamInputWindow').openEvnReceptEditWindow('add', (e.shiftKey == true ? 'pregnancy' : 'common'));
		},
		stopEvent: true
	}, {
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('ReceptStreamInputWindow').hide();
		},
		key: [ Ext.EventObject.P ],
		stopEvent: true
	}],
	layout: 'border',
	maximizable: false,
	maximized: true,
	minimizable: false,
	modal: true,
	openEvnReceptEditWindow: function(action, MorbusType_SysNick) {
		if ( action != 'add' && action != 'view' ) {
			return false;
		}
		if ( getRegionNick() == 'perm' && MorbusType_SysNick == 'pregnancy' ) {
			return false;
		}

		var current_window = this;
		var evn_recept_grid = current_window.findById('RSIF_EvnReceptGrid').getGrid();
		var form = current_window.findById('ReceptStreamInputParams');
		var wnd;

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if ( action == 'add' ) {
			if ( !form.getForm().isValid() ) {
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

			/*if ( MorbusType_SysNick == 'pregnancy' || getRegionNick().inlist([ 'khak', 'krym', 'pskov', 'saratov', 'buryatiya' ])) {
				wnd = 'swEvnReceptRlsEditWindow';
			} else {
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

			var lpu_section_id = form.findById('RSIF_LpuSectionCombo').getValue();
			var med_personal_id = null;
			var med_staff_fact_id = form.findById('RSIF_MedPersonalCombo').getValue();
			var evn_recept_set_date = form.findById('RSIF_EvnRecept_setDate').getValue();
			var recept_type_id = form.findById('RSIF_ReceptTypeCombo').getValue();

			var record = form.findById('RSIF_MedPersonalCombo').getStore().getById(med_staff_fact_id);
			if ( record ) {
				med_personal_id = record.get('MedPersonal_id');
			}

			getWnd('swPersonSearchWindow').show({
				onClose: function() {
					form.findById('RSIF_EvnRecept_setDate').focus(false, 500);
					current_window.findById('RSIF_EvnReceptGrid').getGrid().getStore().reload();
				},
				onSelect: function(person_data) {
					getWnd(wnd).show({
						action: action,
						callback: function() {
							evn_recept_grid.getStore().reload();
						},
						EvnRecept_setDate: evn_recept_set_date,
						LpuSection_id: lpu_section_id,
						MedPersonal_id: med_staff_fact_id,
						onHide: function() {
							// TODO: Продумать использование getWnd в таких случаях
							getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 250);
						},
						Person_id: person_data.Person_id,
						PersonEvn_id: person_data.PersonEvn_id,
						ReceptType_id: recept_type_id,
						Server_id: person_data.Server_id,
						streamInput: true
					});
				},
				searchMode: 'all'
			});
		}
		else {
			if ( !evn_recept_grid.getSelectionModel().getSelected() ) {
				return false;
			}

			var selected_record = evn_recept_grid.getSelectionModel().getSelected();
			
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

			getWnd(wnd).show({
				action: action,
				callback: function() {
					evn_recept_grid.getStore().reload();
				},
				EvnRecept_id: selected_record.get('EvnRecept_id'),
				onHide: function() {
					evn_recept_grid.getSelectionModel().selectRow(evn_recept_grid.getStore().indexOf(selected_record));
				},
				Person_id: selected_record.get('Person_id'),
				PersonEvn_id: selected_record.get('PersonEvn_id'),
				Server_id: selected_record.get('Server_id'),
				viewOnly: !form.hasPolka
			});
		}
	},
	plain: true,
	pmUser_Name: null,
	printRecept: function() {
		var current_window = this;
		var evn_recept_grid = current_window.findById('RSIF_EvnReceptGrid').getGrid();

		if ( !evn_recept_grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var evn_recept_id = evn_recept_grid.getSelectionModel().getSelected().get('EvnRecept_id');
		var server_id = evn_recept_grid.getSelectionModel().getSelected().get('Server_id');

		window.open(C_EVNREC_PRINT_DS, '_blank');
		window.open(C_EVNREC_PRINT + '&EvnRecept_id=' + evn_recept_id + '&Server_id=' + server_id, '_blank');
	},
	resizable: false,
	setBegDateTime: function() {
		var current_window = this;

		Ext.Ajax.request({
			callback: function(opt, success, response) {
				if ( success && response.responseText != '' ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					current_window.begDate = response_obj.begDate;
					current_window.begTime = response_obj.begTime;

					current_window.findById('RSIF_ReceptStreamInformationForm').findById('RSIF_pmUser_Name').setValue(response_obj.pmUser_Name);
					current_window.findById('RSIF_ReceptStreamInformationForm').findById('RSIF_Stream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
					current_window.findById('RSIF_EvnReceptGrid').getGrid().getStore().baseParams.begDate = response_obj.begDate;
					current_window.findById('RSIF_EvnReceptGrid').getGrid().getStore().baseParams.begTime = response_obj.begTime;
				}
			},
			url: C_LOAD_CURTIME
		});
	},
	show: function() {
		sw.Promed.swReceptStreamInputWindow.superclass.show.apply(this, arguments);

		var form = this;
		this.begDate = null;
		this.begTime = null;

		var base_form = this.findById('ReceptStreamInputParams').getForm();
		base_form.reset();

		// Загружаем список подразделений
		swLpuBuildingGlobalStore.clearFilter();
		base_form.findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));

		// Заполнение полей "Пользователь" и "Дата начала ввода"
		this.setBegDateTime();

		

		/*this.findById('RSIF_EvnReceptGrid').addActions({
			name:'action_new',
			text:lang['dobavit'],
			iconCls: 'add16',
            hidden: getRegionNick().inlist([ 'khak', 'perm', 'pskov', 'saratov', 'buryatiya', 'krym' ]),
			menu: new Ext.menu.Menu({
                items:[
                    new Ext.Action({
                        text: lang['retsept_po_obscheterapevticheskoy_gruppe_zabolevaniy'],
                        tooltip: lang['retsept_po_obscheterapevticheskoy_gruppe_zabolevaniy'],
                        handler: function() {form.openEvnReceptEditWindow('add', 'common');}
                    }),
                    new Ext.Action({
                        text: lang['retsept_po_osobyim_gruppam_zabolevaniy'],
                        tooltip: lang['retsept_po_osobyim_gruppam_zabolevaniy'],
                        handler: function() { form.openEvnReceptEditWindow('add', 'pregnancy');}
                    })
                ]
            })
		},0);*/


		// Загрузка пустой строки в грид
		//LoadEmptyRow(this.findById('RSIF_EvnReceptGrid'));

		// this.findById('RSIF_EvnReceptGrid').getTopToolbar().items.items[2].disable();
		/*this.findById('RSIF_EvnReceptGrid').getGrid().getTopToolbar().items.items[4].disable();
		this.findById('RSIF_EvnReceptGrid').getGrid().getTopToolbar().items.items[5].disable();
		this.findById('RSIF_EvnReceptGrid').getGrid().getTopToolbar().items.items[11].disable();
		if(!this.findById('RSIF_EvnReceptGrid').getGrid().getTopToolbar().items.items[7].menu){
		    this.findById('RSIF_EvnReceptGrid').getGrid().getTopToolbar().items.items[7].disable();
		}//refs #21677*/
		var dt = new Date();

		base_form.findField('EvnRecept_setDate').setValue(dt.format('d.m.Y'));
		base_form.findField('EvnRecept_setDate').focus(false, 500);
		if (getRegionNick() == 'kz') {
			base_form.findField('ReceptType_id').setValue(1);
			base_form.findField('ReceptType_id').disable();
		} else {
			base_form.findField('ReceptType_id').setValue(2);
			base_form.findField('ReceptType_id').enable();
		}

		base_form.findField('EvnRecept_setDate').fireEvent('change', base_form.findField('EvnRecept_setDate'), dt.format('d.m.Y'));

		this.hasPolka = false;
		this.getLpuUnitPolkaCount({callback: function(data){
			form.hasPolka = (data && data.LpuUnitCount > 0);
			form.findById('RSIF_EvnReceptGrid').getGrid().getTopToolbar().items.items[0].setDisabled((!form.hasPolka && getRegionNick().inlist(['khak', 'pskov', 'saratov'])) || getRegionNick() == 'msk');
		}.createDelegate(this)});
	},
	title: WND_DLO_RECSTREAM
});