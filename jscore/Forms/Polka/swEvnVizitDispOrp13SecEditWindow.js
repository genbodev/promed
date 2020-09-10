/**
* swEvnVizitDispOrp13SecEditWindow - окно редактирования/добавления осмотра по диспасеризации детей-сирот
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package    Polka
* @access     public
* @copyright  Copyright (c) 2009 Swan Ltd.
* @author     Марков Андрей
* @version    май 2010
* @comment    Префикс для id компонентов EVDO13SEF (swEvnVizitDispOrp13SecEditWindow)
*	            TABINDEX_EVDO13SEF: 9400
*
*
* Использует: окно редактирования талона по диспасеризации детей-сирот (swEvnPLDispOrpEditWindow)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnVizitDispOrp13SecEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	/* */
	codeRefresh: true,
	objectName: 'swEvnVizitDispOrp13SecEditWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnVizitDispOrp13SecEditWindow.js',
	action: null,
	buttonAlign: 'left',
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    collapsible: true,
	doSave: function(callback) {
		var add_flag = true;
		var current_window = this;
		var base_form = this.findById('EvnVizitDispOrp13SecEditForm').getForm();
		var index = -1;
		var UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
		var UslugaComplex_Name = '';
		var UslugaComplex_Code = '';
		var lpu_section_id = current_window.findById('EVDO13SEFLpuSectionCombo').getValue();
		var lpu_section_name = '';
		var med_staff_fact_id = current_window.findById('EVDO13SEFMedPersonalCombo').getValue();
		current_window.findById('EVDO13SEFDiagCombo').fireEvent('blur', current_window.findById('EVDO13SEFDiagCombo'));
		var diag_id = current_window.findById('EVDO13SEFDiagCombo').getValue();
		var diag_code = '';
		var diag_name = '';
		var orpdispdiagtype_id = current_window.findById('EVDO13SEFDopDispDiagTypeCombo').getValue();
		var DopDispAlien_id = base_form.findField('DopDispAlien_id').getValue();
		var DopDispAlien_Name = '';
		var record_status = current_window.findById('EVDO13SEFRecord_Status').getValue();
		var personinfoframe = current_window.findById('EVDO13SEFPersonInformationFrame');

		// Проверка на наличие у врача кода ДЛО или специальности https://redmine.swan.perm.ru/issues/47172
		// Проверку кода ДЛО убрали в https://redmine.swan.perm.ru/issues/118763
		if ( getRegionNick().inlist([ 'kareliya', 'penza' ]) ) {
			var MedSpecOms_id = base_form.findField('MedStaffFact_id').getFieldValue('MedSpecOms_id');

			if ( Ext.isEmpty(MedSpecOms_id) ) {
				sw.swMsg.alert(lang['soobschenie'], lang['u_vracha_ne_ukazana_spetsialnost'], function() { base_form.findField('MedStaffFact_id').clearValue(); } );
				return false;
			}
		}

		if (!current_window.findById('EvnVizitDispOrp13SecEditForm').getForm().isValid())
		{
            Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                	current_window.findById('EVDO13SEFEvnVizitDispOrp_setDate').focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
		}
		
		index = base_form.findField('UslugaComplex_id').getStore().findBy(function(rec) { return rec.get('UslugaComplex_id') == UslugaComplex_id; });
		if (index >= 0)
		{
			UslugaComplex_Name = base_form.findField('UslugaComplex_id').getStore().getAt(index).data.UslugaComplex_Name;
			UslugaComplex_Code = base_form.findField('UslugaComplex_id').getStore().getAt(index).data.UslugaComplex_Code;
		}
		
		index = base_form.findField('DopDispAlien_id').getStore().findBy(function(rec) { return rec.get('DopDispAlien_id') == DopDispAlien_id; });
		if (index >= 0)
		{
			DopDispAlien_Name = base_form.findField('DopDispAlien_id').getStore().getAt(index).data.DopDispAlien_Name;
		}

		var pedcodes = ['01090128'];
		if (getRegionNick() == 'ekb') {
			pedcodes = ['B04.031.002'];
		} else if (getRegionNick() == 'astra') {
			pedcodes = ['B04.031.004'];
		} else if (getRegionNick() == 'pskov') {
			pedcodes = ['B04.031.001'];
		} else if (getRegionNick() == 'krym') {
			pedcodes = ['B04.031.001'];
		} else if (getRegionNick() == 'buryatiya') {
			pedcodes = ['161014', '161078', '161150'];
		}
		
		if ( UslugaComplex_Code && UslugaComplex_Code.inlist(pedcodes) && (current_window.findById('EVDO13SEFEvnVizitDispOrp_setDate').getValue() < current_window.max_date) )
		{
			Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                	current_window.findById('EVDO13SEFEvnVizitDispOrp_setDate').focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: "Осмотр педиатра не может быть проведен ранее других осмотров или даты получения результатов исследований.",
                title: ERR_INVFIELDS_TIT
            });
            return false;		
		}

		var set_date = base_form.findField('EvnVizitDispOrp_setDate').getValue();
		var set_time = base_form.findField('EvnVizitDispOrp_setTime').getValue();
		var dis_date = base_form.findField('EvnVizitDispOrp_disDate').getValue();
		var dis_time = base_form.findField('EvnVizitDispOrp_disTime').getValue();

		if (!Ext.isEmpty(dis_date)) {
			var setDateStr = Ext.util.Format.date(set_date, 'Y-m-d')+' '+(Ext.isEmpty(set_time)?'00:00':set_time);
			var disDateStr = Ext.util.Format.date(dis_date, 'Y-m-d')+' '+(Ext.isEmpty(dis_time)?'00:00':dis_time);

			if (Date.parseDate(setDateStr, 'Y-m-d H:i') > Date.parseDate(disDateStr, 'Y-m-d H:i')) {
				Ext.MessageBox.show({
					buttons: Ext.Msg.OK,
					fn: function() {base_form.findField('EvnVizitDispOrp_setDate').focus(false)},
					icon: Ext.Msg.WARNING,
					msg: lang['data_okonchaniya_vyipolneniya_uslugi_ne_mojet_byit_menshe_datyi_nachala_vyipolneniya_uslugi'],
					title: lang['oshibka']
				});
				return false;
			}
		}

		if ( set_date < Date.parseDate('01.01.' + set_date.getFullYear(), 'd.m.Y') )
		{
			Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                	current_window.findById('EVDO13SEFEvnVizitDispOrp_setDate').focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: lang['data_nachala_ne_mojet_byit_menshe_01_01'] + set_date.getFullYear() + '.',
                title: lang['oshibka']
            });
            return false;
		}

		if (record_status == 1)
		{
			record_status = 2;
		}
		
		index = current_window.findById('EVDO13SEFLpuSectionCombo').getStore().findBy(function(rec) { return rec.get('LpuSection_id') == lpu_section_id; });
		if (index >= 0)
		{
			lpu_section_name = current_window.findById('EVDO13SEFLpuSectionCombo').getStore().getAt(index).data.LpuSection_Name;
		}

		
		record = current_window.findById('EVDO13SEFMedPersonalCombo').getStore().getById(med_staff_fact_id);
		if ( record ) {
			med_personal_fio = record.get('MedPersonal_Fio');
			med_personal_id = record.get('MedPersonal_id');
		} else {
			med_personal_fio = null;
			med_personal_id = null;
		}

		base_form.findField('MedPersonal_id').setValue(med_personal_id);

		index = current_window.findById('EVDO13SEFDiagCombo').getStore().findBy(function(rec) { return rec.get('Diag_id') == diag_id; });
		if (index >= 0)
		{
			diag_code = current_window.findById('EVDO13SEFDiagCombo').getStore().getAt(index).data.Diag_Code;
			diag_name = diag_code + '. ' + current_window.findById('EVDO13SEFDiagCombo').getStore().getAt(index).data.Diag_Name;
		}
		
		if (current_window.action != 'add')
		{
			add_flag = false;
		}

		var data = {
			'EvnVizitDispOrp_id': current_window.findById('EVDO13SEFEvnVizitDispOrp_id').getValue(),
			'DispClass_id': current_window.DispClass_id,
			'PersonEvn_id': personinfoframe.getFieldValue('PersonEvn_id'),
			'Server_id': personinfoframe.getFieldValue('Server_id'),
			'UslugaComplex_id': UslugaComplex_id,
			'LpuSection_id': lpu_section_id,
			'Lpu_uid': base_form.findField('Lpu_uid').getValue(),
			'MedPersonal_id': med_personal_id,
			'MedStaffFact_id': med_staff_fact_id,
			'LpuSectionProfile_id': base_form.findField('LpuSectionProfile_id').getValue(),
			'MedSpecOms_id': base_form.findField('MedSpecOms_id').getValue(),
			'Diag_id': diag_id,
			'UslugaComplex_Name': UslugaComplex_Name,
			'UslugaComplex_Code': UslugaComplex_Code,
			'EvnVizitDispOrp_setDate': base_form.findField('EvnVizitDispOrp_setDate').getValue(),
			'EvnVizitDispOrp_setTime': base_form.findField('EvnVizitDispOrp_setTime').getValue(),
			'EvnVizitDispOrp_disDate': base_form.findField('EvnVizitDispOrp_disDate').getValue(),
			'EvnVizitDispOrp_disTime': base_form.findField('EvnVizitDispOrp_disTime').getValue(),
			'LpuSection_Name': lpu_section_name,
			'MedPersonal_Fio': med_personal_fio,
			'Diag_Code': diag_code,
			'Diag_Name': diag_name,
			'DopDispDiagType_id': orpdispdiagtype_id,
			'DopDispAlien_id': DopDispAlien_id,
			'DopDispAlien_Name': DopDispAlien_Name,
			'Record_Status': record_status
		};

		current_window.EvnDiagDopDispGrid.getGrid().getStore().clearFilter();
		data.EvnDiagDopDispGridData = Ext.util.JSON.encode(getStoreRecords( current_window.EvnDiagDopDispGrid.getGrid().getStore() ));
		current_window.EvnDiagDopDispGrid.getGrid().getStore().filterBy(function(record) {
			if (record.data.Record_Status != 3) { return true; } else { return false; }
		});

		current_window.getLoadMask("Подождите, идет сохранение...").show();
		base_form.submit({
			url: '/?c=EvnPLDispOrp13&m=saveEvnVizitDispOrpSec',
			failure: function(result_form, action) {
				current_window.formStatus = 'edit';
				current_window.getLoadMask().hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
					}
				}
			},
			params: data,
			success: function(result_form, action) {
				current_window.formStatus = 'edit';
				current_window.getLoadMask().hide();
				if ( action.result ) {
					if ( action.result.EvnVizitDispOrp_id ) {

						base_form.findField('EvnVizitDispOrp_id').setValue(action.result.EvnVizitDispOrp_id);
						data.EvnVizitDispOrp_id = action.result.EvnVizitDispOrp_id;
						current_window.EvnDirectionGrid.getGrid().getStore().baseParams.EvnDirection_pid = base_form.findField('EvnVizitDispOrp_id').getValue();
						
						current_window.callback([data], add_flag);
						if (typeof callback == 'function') {
							callback();
						} else {
							current_window.hide();
						}
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
						}
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
				}
			}
		});
    },
	draggable: true,
    height: 490,
	id: 'EvnVizitDispOrp13SecEditWindow',
	deleteEvnDiagDopDisp: function() {
		var win = this;

		if (win.action == 'view') {
			return false;
		}

		var grid = this.EvnDiagDopDispGrid.getGrid();

		sw.swMsg.show({
			buttons: sw.swMsg.YESNO,
			fn: function(buttonId, text, obj)
			{
				if ('yes' == buttonId)
				{
					if (!grid.getSelectionModel().getSelected())
					{
						return false;
					}

					var selected_record = grid.getSelectionModel().getSelected();
					var EvnDiagDopDisp_id = selected_record.get('EvnDiagDopDisp_id');

					if (selected_record.data.Record_Status == 0)
					{
						grid.getStore().remove(selected_record);
					}
					else
					{
						selected_record.set('Record_Status', 3);
						selected_record.commit();
						grid.getStore().filterBy(function(record)
						{
							if (record.data.Record_Status != 3)
							{
								return true;
							}
						});
					}

					if (grid.getStore().getCount() == 0)
					{
						grid.getTopToolbar().items.items[1].disable();
						grid.getTopToolbar().items.items[2].disable();
						grid.getTopToolbar().items.items[3].disable();
					}
					else
					{
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_soputstvuyuschiy_diagnoz'],
			title: lang['vopros']
		});
	},
	openEvnDiagDopDispEditWindow: function(action) {
		var win = this;

		if (win.action == 'view') {
			if (action == 'add') {
				return false;
			}
			action = 'view';
		}

		var base_form = win.findById('EvnVizitDispOrp13SecEditForm').getForm();
		var grid = this.EvnDiagDopDispGrid.getGrid();

		var params = new Object();
		params.action = action;
		params.formParams = new Object();
		params.formParams.EvnDiagDopDisp_pid = base_form.findField('EvnVizitDispOrp_id').getValue();
		params.EvnDiagDopDispGridStore = grid.getStore();

		if (action == 'add') {
			params.formParams.EvnDiagDopDisp_id = swGenTempId(grid.getStore(), 'EvnDiagDopDisp_id');
			params.formParams.Record_Status = 0;
		} else {
			var selected_record = grid.getSelectionModel().getSelected();
			if (!selected_record.get('EvnDiagDopDisp_id')) { return false; }
			params.formParams = selected_record.data;
		}

		params.formMode = 'local';

		params.callback = function(data) {
			var i;
			var evndiag_fields = new Array();

			grid.getStore().fields.eachKey(function(key, item) {
				evndiag_fields.push(key);
			});

			if ( action == 'add' )
			{
				grid.getStore().clearFilter();
				grid.getStore().loadData(data, true);
				grid.getStore().filterBy(function(record) {
					if (record.data.Record_Status != 3)
					{
						return true;
					}
				});
			}
			else {
				index = grid.getStore().findBy(function(rec) { return rec.get('EvnDiagDopDisp_id') == data[0].EvnDiagDopDisp_id; });

				if (index == -1)
				{
					return false;
				}

				var record = grid.getStore().getAt(index);
				for (i = 0; i < evndiag_fields.length; i++)
				{
					record.set(evndiag_fields[i], data[0][evndiag_fields[i]]);
				}

				record.commit();
			}

			return true;
		};

		getWnd('swEvnDiagDopDispEditWindow').show(params);
	},
    initComponent: function() {
		var win = this;

		this.EvnDiagDopDispGrid = new sw.Promed.ViewFrame({
			useEmptyRecord: false,
			autoLoadData: false,
			uniqueId: true,
			editformclassname: 'swEvnDiagDopDispEditForm',
			object: 'EvnDiagDopDisp',
			actions: [
				{ name: 'action_add', handler: function() { win.openEvnDiagDopDispEditWindow('add'); } },
				{ name: 'action_edit', handler: function() { win.openEvnDiagDopDispEditWindow('edit'); } },
				{ name: 'action_view', handler: function() { win.openEvnDiagDopDispEditWindow('view'); } },
				{ name: 'action_delete', handler: function() { win.deleteEvnDiagDopDisp(); } },
				{ name: 'action_refresh', hidden: true, disabled: true },
				{ name: 'action_print'}
			],
			dataUrl: '/?c=EvnDiagDopDisp&m=loadEvnDiagDopDispSoputGrid',
			region: 'center',
			height: 200,
			title: lang['soputstvuyuschie_diagnozyi'],
			toolbar: true,
			stringfields: [
				{ name: 'EvnDiagDopDisp_id', type: 'int', header: 'ID', key: true },
				{ name: 'Diag_Code', type: 'string', header: lang['kod']},
				{ name: 'Diag_id', type: 'int', hidden: true},
				{ name: 'DeseaseDispType_id', type: 'int', hidden: true},
				{ name: 'Record_Status', type: 'int', hidden: true},
				{ name: 'Diag_Name', type: 'string', header: lang['naimenovanie'], id: 'autoexpand' },
				{ name: 'DeseaseDispType_Name', type: 'string', header: lang['harakter_zabolevaniya']}
			]
		});
		
		this.EvnDirectionGrid = new sw.Promed.ViewFrame({
			id: 'EVDO13EW_EvnDirectionGrid',
			object: 'EvnDirection',
			dataUrl: '/?c=EvnDirection&m=loadEvnDirectionGrid',
			layout: 'fit',
			region: 'center',
			paging: false,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: true,
			autoExpandMin: 100,
			autoLoadData: false,
			title: langs('Направление на дообследование'),
			stringfields: [
				{name: 'EvnDirection_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnDirection_pid', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Diag_id', type: 'int', hidden: true},
				{name: 'DirType_id', type: 'int', hidden: true},
				{name: 'LpuSection_id', type: 'int', hidden: true},
				{name: 'MedPersonal_id', type: 'int', hidden: true},
				{name: 'MedPersonal_zid', type: 'int', hidden: true},
				{name: 'LpuSectionProfile_id', type: 'int', hidden: true},
				{name: 'EvnDirection_Descr', type: 'string', hidden: true},
				{name: 'TimetableGraf_id', type: 'string', hidden: true},
				{name: 'TimetableMedService_id', type: 'string', hidden: true},
				{name: 'TimetableResource_id', type: 'string', hidden: true},
				{name: 'EvnQueue_id', type: 'string', hidden: true},
				{name: 'EvnStatus_id', type: 'string', hidden: true},
				{name: 'EvnDirection_setDate', type: 'date', dateFormat: 'd.m.Y', header: langs('Дата выписки направления'), width: 100},
				{name: 'EvnDirection_Num', type: 'int', header: langs('Номер направления'), width: 120},
				{name: 'DirType_Name', type: 'string', header: langs('Тип направления'), width: 150},
				{name: 'LpuSectionProfile_Name', type: 'string', header: langs('Профиль'), autoexpand: true}
			],
			actions:[{
					name:'action_add',
					text: BTN_GRIDADD,
					tooltip: BTN_GRIDADD_TIP,
					id: 'EVDO13EW_EvnDirectionGrid_add',
					menu: [{
						text: 'На исследование',
						handler: function() {
							win.addDirectionIssled();
						}
					}, {
						text: 'На консультацию',
						handler: function() {
							win.addDirectionConsult();
						}
					}, {
						text: 'На поликлинический прием',
						handler: function() {
							win.addDirectionPolka();
						}
					}]
				},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', handler: function(row, el, a) {
					var personinfoframe = win.findById('EVDO13SEFPersonInformationFrame'),
						grid = win.EvnDirectionGrid.getGrid(),
						rec = grid.getSelectionModel().getSelected();
					if (!rec || !rec.get('EvnDirection_id')) return false;
					var params = rec.data;
					getWnd('swEvnDirectionEditWindow').show({
						action: 'view',
						EvnDirection_id: rec.get('EvnDirection_id'),
						Person_id: personinfoframe.getFieldValue('Person_id'),
						formParams: params,
						Person_Birthday: personinfoframe.getFieldValue('Person_Birthday'),
						Person_Firname: personinfoframe.getFieldValue('Person_Firname'),
						Person_Secname: personinfoframe.getFieldValue('Person_Secname'),
						Person_Surname: personinfoframe.getFieldValue('Person_Surname')
					});
				}},
				{name:'action_delete', text: 'Отменить', tooltip: 'Отменить', handler: function() {
					var grid = win.EvnDirectionGrid.getGrid();
					rec = grid.getSelectionModel().getSelected();
					if (!rec || !rec.get('EvnDirection_id')) return false;
					sw.Promed.Direction.cancel({
						cancelType: 'cancel',
						ownerWindow: win,
						formType: 'reg',
						allowRedirect: true,
						userMedStaffFact: win.userMedStaffFact,
						EvnDirection_id: rec.get('EvnDirection_id')||null,
						DirType_Code: rec.get('DirType_id')||null,
						TimetableGraf_id: rec.get('TimetableGraf_id')||null,
						TimetableMedService_id: rec.get('TimetableMedService_id')||null,
						TimetableResource_id: rec.get('TimetableResource_id')||null,
						EvnQueue_id: rec.get('EvnQueue_id')||null,
						callback: function(cfg) {
							win.EvnDirectionGrid.getGrid().getStore().reload();
						}
					});
				}},
				{name:'action_refresh', hidden: true, disabled: true},
				{name:'action_print', handler: function() { 
					var grid = win.EvnDirectionGrid.getGrid();
					rec = grid.getSelectionModel().getSelected();
					if (!rec || !rec.get('EvnDirection_id')) return false;

					sw.Promed.Direction.print({
						EvnDirection_id: rec.get('EvnDirection_id')
					});
				}}
			],
			onRowSelect: function(sm,index,rec){
				this.getAction('action_delete').setDisabled(!rec || !rec.get('EvnDirection_id') || rec.get('EvnStatus_id').inlist([12,13,15]));
			}
		});

        Ext.apply(this, {
			buttons: [{
				handler: function() {
					win.doSave();
				},
				iconCls: 'save16',
				id: 'EVDO13SEFSaveButton',
				tabIndex: TABINDEX_EVDO13SEF+12,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
				HelpButton(this, -1),
			{
				handler: function() {
					win.hide();
				},
				iconCls: 'cancel16',
				id: 'EVDO13SEFCancelButton',
				onTabAction: function() {
					Ext.getCmp('EVDO13SEFEvnVizitDispOrp_setDate').focus(true, 200);
				},
				onShiftTabAction: function() {
					Ext.getCmp('EVDO13SEFSaveButton').focus(true, 200);
				},
				tabIndex: TABINDEX_EVDO13SEF+13,
				text: BTN_FRMCANCEL
			}],
            items: [
				new	sw.Promed.PersonInformationPanelShort({
					id: 'EVDO13SEFPersonInformationFrame',
					region: 'north'
				}),
				new Ext.form.FormPanel({
					autoScroll: true,
					bodyBorder: false,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					frame: false,
					id: 'EvnVizitDispOrp13SecEditForm',
					labelAlign: 'right',
					labelWidth: 150,
					items: [{
						id: 'EVDO13SEFEvnVizitDispOrp_id',
						name: 'EvnVizitDispOrp_id',
						value: 0,
						xtype: 'hidden'
					}, {
						id: 'EVDO13SEFRecord_Status',
						name: 'Record_Status',
						value: 0,
						xtype: 'hidden'
					}, {
						name: 'EvnPLDispOrp_id',
						xtype: 'hidden'
					}, {
						name: 'MedPersonal_id',
						xtype: 'hidden'
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							labelWidth: 180,
							items: [{
								allowBlank: false,
								enableKeyEvents: true,
								fieldLabel: lang['data_osmotra'],
								format: 'd.m.Y',
								id: 'EVDO13SEFEvnVizitDispOrp_setDate',
								listeners: {
									'keydown':  function(inp, e) {
										if ( e.shiftKey && e.getKey() == Ext.EventObject.TAB )
										{
											e.stopEvent();
											Ext.getCmp('EVDO13SEFCancelButton').focus(true, 200);
										}
									},
									'change': function(field, newValue, oldValue) {
										if (blockedDateAfterPersonDeath('personpanelid', 'EVDO13SEFPersonInformationFrame', field, newValue, oldValue)) return;

										this.filterLpuCombo();
										this.setLpuSectionAndMedStaffFactFilter();

										var uslugacategory_combo = this.findById('EVDO13SEFUslugaCategoryCombo');
										if (newValue && getRegionNick() == 'perm') {
											if (newValue < new Date('2014-12-31')) {
												uslugacategory_combo.setFieldValue('UslugaCategory_SysNick', 'tfoms');
											} else {
												uslugacategory_combo.setFieldValue('UslugaCategory_SysNick', 'gost2011');
											}
											uslugacategory_combo.fireEvent('select', uslugacategory_combo, uslugacategory_combo.getStore().getById(uslugacategory_combo.getValue()));
										}
										
										this.setDisDT();

										var base_form = this.findById('EvnVizitDispOrp13SecEditForm').getForm();
										base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = (typeof newValue == 'object' ? Ext.util.Format.date(newValue, 'd.m.Y') : null);
										base_form.findField('UslugaComplex_id').clearValue();
										base_form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';
									}.createDelegate(this)
								},
								maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
								//minValue: Date.parseDate('01.01.' + Date.parseDate(getGlobalOptions().date, 'd.m.Y').getFullYear(), 'd.m.Y'),
								name: 'EvnVizitDispOrp_setDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								tabIndex: TABINDEX_EVDO13SEF + 1,
								width: 100,
								xtype: 'swdatefield'
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 50,
							items: [{
								fieldLabel: lang['vremya'],
								listeners: {
									'change': function() {
										this.setDisDT();
									}.createDelegate(this),
									'keydown': function (inp, e) {
										if ( e.getKey() == Ext.EventObject.F4 ) {
											e.stopEvent();
											inp.onTriggerClick();
										}
									}
								},
								id: 'EVDO13SEFEvnVizitDispOrp_setTime',
								name: 'EvnVizitDispOrp_setTime',
								onTriggerClick: function() {
									var base_form = this.findById('EvnVizitDispOrp13SecEditForm').getForm();

									var time_field = base_form.findField('EvnVizitDispOrp_setTime');

									if ( time_field.disabled ) {
										return false;
									}

									setCurrentDateTime({
										callback: function() {
											this.setDisDT();
										}.createDelegate(this),
										dateField: base_form.findField('EvnVizitDispOrp_setDate'),
										loadMask: true,
										setDate: true,
										setDateMaxValue: true,
										setDateMinValue: false,
										setTime: true,
										timeField: time_field,
										windowId: this.id
									});
								}.createDelegate(this),
								plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
								tabIndex: TABINDEX_EVDO13SEF + 1,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield'
							}]
						}, {
							layout: 'form',
							style: 'padding-left: 45px',
							border: false,
							items: [{
								xtype: 'button',
								id: 'EVDO13SEF_ToggleVisibleDisDTBtn',
								text: lang['utochnit_period_vyipolneniya'],
								handler: function() {
									this.toggleVisibleDisDTPanel();
								}.createDelegate(this)
							}]
						}]
					}, {
						border: false,
						layout: 'column',
						id: 'EVDO13SEF_EvnVizitDisDTPanel',
						items: [{
							border: false,
							layout: 'form',
							labelWidth: 180,
							items: [{
								fieldLabel: lang['data_okonchaniya_vyipolneniya'],
								format: 'd.m.Y',
								id: 'EVDO13SEFEvnVizitDispOrp_disDate',
								name: 'EvnVizitDispOrp_disDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								tabIndex: TABINDEX_EVDO13SEF + 3,
								width: 100,
								xtype: 'swdatefield'
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 50,
							items: [{
								fieldLabel: lang['vremya'],
								listeners: {
									'keydown': function (inp, e) {
										if ( e.getKey() == Ext.EventObject.F4 ) {
											e.stopEvent();
											inp.onTriggerClick();
										}
									}
								},
								id: 'EVDO13SEFEvnVizitDispOrp_disTime',
								name: 'EvnVizitDispOrp_disTime',
								onTriggerClick: function() {
									var base_form = this.findById('EvnVizitDispOrp13SecEditForm').getForm();

									var time_field = base_form.findField('EvnVizitDispOrp_disTime');

									if ( time_field.disabled ) {
										return false;
									}

									setCurrentDateTime({
										dateField: base_form.findField('EvnVizitDispOrp_disDate'),
										loadMask: true,
										setDate: true,
										setDateMaxValue: true,
										setDateMinValue: false,
										setTime: true,
										timeField: time_field,
										windowId: this.id
									});
								}.createDelegate(this),
								plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
								tabIndex: TABINDEX_EVDO13SEF + 4,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield'
							}]
						}, {
							layout: 'form',
							border: false,
							items: [{
								xtype: 'button',
								id: 'EVDO13SEF_DTCopyBtn',
								text: '=',
								handler: function() {
									var base_form = this.findById('EvnVizitDispOrp13SecEditForm').getForm();

									base_form.findField('EvnVizitDispOrp_disDate').setValue(base_form.findField('EvnVizitDispOrp_setDate').getValue());
									base_form.findField('EvnVizitDispOrp_disTime').setValue(base_form.findField('EvnVizitDispOrp_setTime').getValue());
								}.createDelegate(this)
							}]
						}]
					}, {
						layout: 'form',
						hidden: (!getRegionNick().inlist(['perm', 'ufa', 'adygeya', 'ekb', 'khak', 'vologda'])),
						border: false,
						items: [{
							allowBlank: (!getRegionNick().inlist(['perm', 'ufa', 'adygeya', 'ekb', 'khak', 'vologda'])),
							id: 'EVDO13SEFUslugaCategoryCombo',
							fieldLabel: lang['kategoriya_uslugi'],
							hiddenName: 'UslugaCategory_id',
							listeners: {
								'select': function (combo, record) {
									var usluga_combo = win.findById('EVDO13SEFUslugaComplexCombo');

									usluga_combo.clearValue();
									usluga_combo.getStore().removeAll();

									if ( !record ) {
										usluga_combo.setUslugaCategoryList();
										return false;
									}

									usluga_combo.setUslugaCategoryList([ record.get('UslugaCategory_SysNick') ]);

									return true;
								}
							},
							listWidth: 400,
							tabIndex: TABINDEX_ATAEW + 1,
							width: 250,
							xtype: 'swuslugacategorycombo'
						}]
					}, {
						allowBlank: false,
						id: 'EVDO13SEFUslugaComplexCombo',
						listWidth: 500,
						fieldLabel: lang['osmotr'],
						hiddenName: 'UslugaComplex_id',
						tabIndex: TABINDEX_EVDO13SEF + 2,
						width: 450,
						nonDispOnly: false,
						xtype: 'swuslugacomplexnewcombo'
					},{
						allowBlank: false,
						fieldLabel: lang['storonniy_spetsialist'],
						hiddenName: 'DopDispAlien_id',
						comboSubject: 'DopDispAlien',
						sortField: 'DopDispAlien_Code',
						tabIndex: TABINDEX_EVDO13SEF + 3,
						width: 200,
						xtype: 'swcustomobjectcombo',
						lastQuery: '',
						listeners: {
							'change': function(combo, newValue) {
								var base_form = win.findById('EvnVizitDispOrp13SecEditForm').getForm();
								
								if ( getRegionNick().inlist([ 'krym' ]) ) {
									var index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function(rec) {
										return (rec.get('LpuSectionProfile_id') == base_form.findField('LpuSectionProfile_id').getValue());
									}), ucid;
								
									if (base_form.findField('LpuSectionProfile_id').getStore().getCount() == 1) {
										ucid = base_form.findField('LpuSectionProfile_id').getStore().getAt(0).get('LpuSectionProfile_id');
										base_form.findField('LpuSectionProfile_id').setValue(ucid);
									} else if (base_form.findField('LpuSectionProfile_id').getStore().getCount() > 1) {
										if ( index >= 0 ) {
											ucid = base_form.findField('LpuSectionProfile_id').getStore().getAt(index).get('LpuSectionProfile_id');
										} else {
											ucid = base_form.findField('LpuSectionProfile_id').getStore().getAt(0).get('LpuSectionProfile_id');
										}
										base_form.findField('LpuSectionProfile_id').setValue(ucid);
									}
									
									base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());
									
									var index = base_form.findField('MedSpecOms_id').getStore().findBy(function(rec) {
										return (rec.get('MedSpecOms_id') == base_form.findField('MedSpecOms_id').getValue());
									});
									
									if (base_form.findField('MedSpecOms_id').getStore().getCount() == 1) {
										ucid = base_form.findField('MedSpecOms_id').getStore().getAt(0).get('MedSpecOms_id');
										base_form.findField('MedSpecOms_id').setValue(ucid);
									} else if (base_form.findField('MedSpecOms_id').getStore().getCount() > 1) {
										if ( index >= 0 ) {
											ucid = base_form.findField('MedSpecOms_id').getStore().getAt(index).get('MedSpecOms_id');
										} else {
											ucid = base_form.findField('MedSpecOms_id').getStore().getAt(0).get('MedSpecOms_id');
										}
										base_form.findField('MedSpecOms_id').setValue(ucid);
									}
									
									base_form.findField('MedSpecOms_id').fireEvent('change', base_form.findField('MedSpecOms_id'), base_form.findField('MedSpecOms_id').getValue());
								}

								win.setLpuSectionAndMedStaffFactFilter();
							}.createDelegate(this)
						}
					}, {
						comboSubject: 'Lpu',
						fieldLabel: lang['mo'],
						xtype: 'swcommonsprcombo',
						editable: true,
						forceSelection: true,
						displayField: 'Lpu_Nick',
						codeField: 'Lpu_Code',
						orderBy: 'Nick',
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{Lpu_Nick}',
							'</div></tpl>'
						),
						moreFields: [
							{name: 'Lpu_Nick', mapping: 'Lpu_Nick'},
							{name: 'Lpu_EndDate', mapping: 'Lpu_EndDate'}
						],
						tabIndex: TABINDEX_EUDD13EW + 06,
						width: 450,
						hiddenName: 'Lpu_uid',
						onLoadStore: function() {
							win.filterLpuCombo();
						},
						listeners: {
							'change': function(field, newValue, oldValue) {
								win.setLpuSectionAndMedStaffFactFilter();
							}
						}
					}, {
						comboSubject: 'LpuSectionProfile',
						fieldLabel: langs('Профиль'),
						hiddenName: 'LpuSectionProfile_id',
						listeners: {
							'change': function(field, newValue, oldValue) {
								win.setLpuSectionAndMedStaffFactFilter();
							}
						},
						tabIndex: TABINDEX_EVDD13SEF + 9,
						width: 450,
						xtype: 'swcommonsprcombo'
					}, {
						allowBlank: false,
						hiddenName: 'LpuSection_id',
						id: 'EVDO13SEFLpuSectionCombo',
						lastQuery: '',
						listWidth: 650,
						linkedElements: [
							'EVDO13SEFMedPersonalCombo'
						],
						tabIndex: TABINDEX_EVDO13SEF + 4,
						width: 450,
						xtype: 'swlpusectionglobalcombo'
					}, {
						comboSubject: 'MedSpecOms',
						fieldLabel: langs('Специальность'),
						hiddenName: 'MedSpecOms_id',
						listeners: {
							'change': function(field, newValue, oldValue) {
								win.setLpuSectionAndMedStaffFactFilter();
							}
						},
						listWidth: 650,
						tabIndex: TABINDEX_EVDD13SEF + 11,
						width: 450,
						xtype: 'swcommonsprcombo'
					}, {
						allowBlank: false,
						hiddenName: 'MedStaffFact_id',
						id: 'EVDO13SEFMedPersonalCombo',
						lastQuery: '',
						listWidth: 650,
						parentElementId: 'EVDO13SEFLpuSectionCombo',
						listeners: {
							'change': function(field, newValue, oldValue) {
								if ( getRegionNick().inlist([ 'kareliya', 'penza' ]) && !Ext.isEmpty(newValue) ) {
									var index = field.getStore().findBy(function(rec) {
										return (rec.get('MedStaffFact_id') == newValue);
									});

									if ( index >= 0 ) {
										var
											MedSpecOms_id = field.getStore().getAt(index).get('MedSpecOms_id'),
											MedPersonal_Snils = field.getStore().getAt(index).get('Person_Snils');

										if ( Ext.isEmpty(MedSpecOms_id) ) {
											sw.swMsg.alert(lang['soobschenie'], lang['u_vracha_ne_ukazana_spetsialnost'], function() { field.clearValue(); } );
											return false;
										}
										else if ( Ext.isEmpty(MedPersonal_Snils) ) {
											sw.swMsg.alert(lang['soobschenie'], lang['u_vracha_ne_ukazan_snils'], function() { field.clearValue(); } );
											return false;
										}
									}
								}
							}
						},
						tabIndex: TABINDEX_EVDO13SEF + 5,
						width: 450,
						xtype: 'swmedstafffactglobalcombo'
					}, {
						allowBlank: false,
						id: 'EVDO13SEFDiagCombo',
						listWidth: 580,
						tabIndex: TABINDEX_EVDO13SEF + 6,
						width: 450,
						xtype: 'swdiagcombo'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['zabolevanie'],
						id: 'EVDO13SEFDopDispDiagTypeCombo',
						name: 'DopDispDiagType_id',
						tabIndex: TABINDEX_EVDO13SEF + 7,
						validateOnBlur: false,
						width: 350,
						xtype: 'swdopdispdiagtypecombo'
					}, win.EvnDiagDopDispGrid, this.EvnDirectionGrid],
					layout: 'form',
					reader: new Ext.data.JsonReader({
						success: function() { }
					}, [
						{ name: 'EvnVizitDispOrp_id' }
					]),
					region: 'center'
				})
			]
        });	
    	sw.Promed.swEvnVizitDispOrp13SecEditWindow.superclass.initComponent.apply(this, arguments);

		this.findById('EVDO13SEFLpuSectionCombo').addListener('change', function(combo, newValue, oldValue) {
			// перезагрузить комбо врачей если сторонний специалист
			var base_form = win.findById('EvnVizitDispOrp13SecEditForm').getForm();
			var DopDispAlien_id = base_form.findField('DopDispAlien_id').getValue();
			if ( !Ext.isEmpty(DopDispAlien_id) && DopDispAlien_id == 2 ) {
				if (!Ext.isEmpty(base_form.findField('LpuSection_id').getValue())) {
					base_form.findField('MedStaffFact_id').getStore().load({
						callback: function () {
							var index = base_form.findField('MedStaffFact_id').getStore().findBy(function (rec) {
								return (rec.get('MedPersonal_id') == base_form.findField('MedPersonal_id').getValue());
							});

							if (base_form.findField('MedStaffFact_id').getStore().getCount() == 1) {
								ucid = base_form.findField('MedStaffFact_id').getStore().getAt(0).get('MedStaffFact_id');
								base_form.findField('MedStaffFact_id').setValue(ucid);
							} else if (base_form.findField('MedStaffFact_id').getStore().getCount() > 1) {
								if (index >= 0) {
									ucid = base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id');
								} else {
									ucid = base_form.findField('MedStaffFact_id').getStore().getAt(0).get('MedStaffFact_id');
								}
								base_form.findField('MedStaffFact_id').setValue(ucid);
							} else {
								base_form.findField('MedStaffFact_id').clearValue();
							}
						}.createDelegate(this),
						params: {
							mode: 'combo',
							Lpu_id: base_form.findField('Lpu_uid').getValue(),
							LpuSection_id: base_form.findField('LpuSection_id').getValue()
						}
					});
				} else {
					base_form.findField('MedStaffFact_id').getStore().removeAll();
					base_form.findField('MedStaffFact_id').clearValue();
				}
			}
		});

		this.findById('EVDO13SEFDiagCombo').addListener('change', function(combo, newValue, oldValue) {
			var base_form = win.findById('EvnVizitDispOrp13SecEditForm').getForm();
			var diag_code = combo.getFieldValue('Diag_Code');

			if ( Ext.isEmpty(diag_code) || diag_code.substr(0, 1) != 'Z' ) {
				base_form.findField('DopDispDiagType_id').enable();
				base_form.findField('DopDispDiagType_id').setAllowBlank(false);
			}
			else {
				base_form.findField('DopDispDiagType_id').disable();
				base_form.findField('DopDispDiagType_id').setAllowBlank(true);
				base_form.findField('DopDispDiagType_id').clearValue();
			}
		});
		
		this.findById('EVDO13SEFUslugaComplexCombo').addListener('change', function(combo, newValue, oldValue) {
			this.setLpuSectionAndMedStaffFactFilter();
		}.createDelegate(this));
    },
    keys: [{
    	alt: true,
        fn: function(inp, e) {
            e.stopEvent();

            if (e.browserEvent.stopPropagation)
                e.browserEvent.stopPropagation();
            else
                e.browserEvent.cancelBubble = true;

            if (e.browserEvent.preventDefault)
                e.browserEvent.preventDefault();
            else
                e.browserEvent.returnValue = false;

            e.browserEvent.returnValue = false;
            e.returnValue = false;

            if (Ext.isIE)
            {
            	e.browserEvent.keyCode = 0;
            	e.browserEvent.which = 0;
            }

        	var current_window = Ext.getCmp('EvnVizitDispOrp13SecEditWindow');

            if (e.getKey() == Ext.EventObject.J)
            {
            	current_window.hide();
            }
			else if (e.getKey() == Ext.EventObject.C)
			{
	        	if ('view' != current_window.action)
	        	{
	            	current_window.doSave();
	            }
			}
        },
        key: [ Ext.EventObject.C, Ext.EventObject.J ],
        scope: this,
        stopEvent: false
    }],
    layout: 'border',
    listeners: {
    	'hide': function() {
    		this.onHide();
    	}
    },
    maximizable: true,
    minHeight: 370,
    minWidth: 700,
    modal: true,
    onHide: Ext.emptyFn,

		ownerWindow: null,
    plain: true,
    resizable: true,
	filterLpuCombo: function() {
		var base_form = this.findById('EvnVizitDispOrp13SecEditForm').getForm();
		// фильтр на МО (отображать только открытые действующие)
		var curDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
		if ( !Ext.isEmpty(base_form.findField('EvnVizitDispOrp_setDate').getValue()) ) {
			curDate = base_form.findField('EvnVizitDispOrp_setDate').getValue();
		}
		base_form.findField('Lpu_uid').lastQuery = '';
		base_form.findField('Lpu_uid').getStore().clearFilter();
		base_form.findField('Lpu_uid').setBaseFilter(function(rec, id) {
			if (!Ext.isEmpty(rec.get('Lpu_EndDate'))) {
				var lpuEndDate = Date.parseDate(rec.get('Lpu_EndDate'), 'd.m.Y');
				if (lpuEndDate < curDate) {
					return false;
				}
			}
			if (!Ext.isEmpty(getGlobalOptions().lpu_id) && rec.get('Lpu_id') == getGlobalOptions().lpu_id) {
				return false;
			}
			return true;
		});
	},
	setLpuSectionAndMedStaffFactFilter: function ()
	{
		var win = this;
		var base_form = this.findById('EvnVizitDispOrp13SecEditForm').getForm();
		
		// Учитываем дату и место выполнения
		var EvnVizitDispOrp_setDate = base_form.findField('EvnVizitDispOrp_setDate').getValue();
		var DopDispAlien_id = base_form.findField('DopDispAlien_id').getValue();
		
		base_form.findField('LpuSection_id').setAllowBlank(false);
		base_form.findField('MedStaffFact_id').setAllowBlank(false);
			
		if ( !Ext.isEmpty(DopDispAlien_id) && DopDispAlien_id == 2 ) {
			// показать поля МО
			base_form.findField('Lpu_uid').showContainer();
			base_form.findField('LpuSection_id').disableLinkedElements();
			base_form.findField('MedStaffFact_id').disableParentElement();

			if ( !getRegionNick().inlist([ 'krym' ]) ) {
				base_form.findField('LpuSectionProfile_id').hideContainer();
				base_form.findField('MedSpecOms_id').hideContainer();
			}
			else {
				base_form.findField('LpuSectionProfile_id').showContainer();
				base_form.findField('MedSpecOms_id').showContainer();
			}

			base_form.findField('Lpu_uid').setAllowBlank(getRegionNick().inlist([ 'pskov', 'ufa', 'ekb' ]));
			base_form.findField('LpuSectionProfile_id').setAllowBlank(!getRegionNick().inlist([ 'krym' ]));
			base_form.findField('MedSpecOms_id').setAllowBlank(!getRegionNick().inlist([ 'krym' ]));

			base_form.findField('LpuSection_id').setAllowBlank(getRegionNick().inlist([ 'krym' ]));
			base_form.findField('MedStaffFact_id').setAllowBlank(getRegionNick().inlist([ 'krym' ]));

			if (Ext.isEmpty(base_form.findField('Lpu_uid').getValue())) {
				base_form.findField('LpuSection_id').getStore().removeAll();
				base_form.findField('LpuSection_id').clearValue();
				base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), base_form.findField('LpuSection_id').getValue());
				win.lastLpu_uid1 = base_form.findField('Lpu_uid').getValue();
			}

			if (
				!Ext.isEmpty(base_form.findField('Lpu_uid').getValue()) && base_form.findField('Lpu_uid').getValue() != win.lastLpu_uid1
			) {
				win.lastLpu_uid1 = base_form.findField('Lpu_uid').getValue();

				base_form.findField('LpuSection_id').getStore().load({
					callback: function () {
						var index = base_form.findField('LpuSection_id').getStore().findBy(function (rec) {
							return (rec.get('LpuSection_id') == base_form.findField('LpuSection_id').getValue());
						});

						if (base_form.findField('LpuSection_id').getStore().getCount() == 1) {
							ucid = base_form.findField('LpuSection_id').getStore().getAt(0).get('LpuSection_id');
							base_form.findField('LpuSection_id').setValue(ucid);
							base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), base_form.findField('LpuSection_id').getValue());
						} else if (base_form.findField('LpuSection_id').getStore().getCount() > 1) {
							if (index >= 0) {
								ucid = base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_id');
							} else {
								ucid = base_form.findField('LpuSection_id').getStore().getAt(0).get('LpuSection_id');
							}
							base_form.findField('LpuSection_id').setValue(ucid);
							base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), base_form.findField('LpuSection_id').getValue());
						} else {
							base_form.findField('LpuSection_id').clearValue();
							base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), base_form.findField('LpuSection_id').getValue());
						}
					}.createDelegate(this),
					params: {
						Lpu_id: base_form.findField('Lpu_uid').getValue(),
						mode: 'combo'
					}
				});
			}
		} else {
			// скрыть поля МО
			base_form.findField('Lpu_uid').clearValue();
			base_form.findField('Lpu_uid').setAllowBlank(true);
			base_form.findField('Lpu_uid').hideContainer();
			base_form.findField('LpuSectionProfile_id').clearValue();
			base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
			base_form.findField('LpuSectionProfile_id').hideContainer();
			base_form.findField('MedSpecOms_id').clearValue();
			base_form.findField('MedSpecOms_id').setAllowBlank(true);
			base_form.findField('MedSpecOms_id').hideContainer();

			base_form.findField('LpuSection_id').enableLinkedElements();
			base_form.findField('MedStaffFact_id').enableParentElement();

			base_form.findField('LpuSection_id').setAllowBlank(false);
			base_form.findField('MedStaffFact_id').setAllowBlank(false);
			
			// Сохраняем текущие значения
			var LpuSection_id = base_form.findField('LpuSection_id').getValue();
			var MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();

			base_form.findField('LpuSection_id').clearValue();
			base_form.findField('MedStaffFact_id').clearValue();
			
			var set_date = Ext.getCmp('EVDO13SEFEvnVizitDispOrp_setDate').getValue();
			
			var params = {};
			
			if ( !getRegionNick().inlist(['ekb']) ) {
				params.isAliens = (DopDispAlien_id == 2);
				params.isPolkaAndStomAndOther = true;
			}
			
			if ( getRegionNick().inlist(['ekb']) ) {
				if (!Ext.isEmpty(this.MedSpecOms_id)) {
					params.MedSpecOms_id = this.MedSpecOms_id;
				}
			}


			if (getRegionNick() === 'pskov' )
			{
				var UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
				if (UslugaComplex_id && EvnVizitDispOrp_setDate)
				{
					params.UslugaComplex_MedSpecOms = {
						UslugaComplex_id: UslugaComplex_id,
						didDate: Ext.util.Format.date(EvnVizitDispOrp_setDate, 'd.m.Y')
					};
				}
			}

			setLpuSectionGlobalStoreFilter(params);
			setMedStaffFactGlobalStoreFilter(params);

			base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
			base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

			index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
				return (rec.get('LpuSection_id') == LpuSection_id);
			});

			if ( index >= 0 ) {
				base_form.findField('LpuSection_id').setValue(LpuSection_id);
			}

			index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
				return (rec.get('MedStaffFact_id') == MedStaffFact_id);
			});

			if ( index >= 0 ) {
				base_form.findField('MedStaffFact_id').setValue(MedStaffFact_id);
			}
		}
	},
    show: function() {
		sw.Promed.swEvnVizitDispOrp13SecEditWindow.superclass.show.apply(this, arguments);

		var current_window = this;

		current_window.restore();
		current_window.center();

        var form = current_window.findById('EvnVizitDispOrp13SecEditForm');
		var base_form = form.getForm();
		base_form.reset();
		current_window.EvnDiagDopDispGrid.removeAll({ clearAll: true });

       	current_window.callback = Ext.emptyFn;
       	current_window.onHide = Ext.emptyFn;
		current_window.ownerWindow = null;
		current_window.isVisibleDisDTPanel = false;

		current_window.toggleVisibleDisDTPanel('hide');

        if (!arguments[0] || !arguments[0].formParams || !arguments[0].ownerWindow || !arguments[0].DispClass_id)
        {
        	Ext.Msg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { current_window.hide(); } );
        	return false;
        }
		
		this.lastLpu_uid1 = null;
		this.DispClass_id = arguments[0].DispClass_id;
		
		this.setLpuSectionAndMedStaffFactFilter();

		base_form.findField('DopDispAlien_id').lastQuery = '';
		base_form.findField('DopDispAlien_id').getStore().filterBy(function(rec) {
			return rec.get('DopDispAlien_Code').toString().inlist([ '0', '1' ]);
		});
		
        if (arguments[0].action)
        {
        	current_window.action = arguments[0].action;
        }
		
		this.MedSpecOms_id = null;
		
        if (arguments[0].callback)
        {
            current_window.callback = arguments[0].callback;
        }

        if (arguments[0].onHide)
        {
        	current_window.onHide = arguments[0].onHide;
        }

        if (arguments[0].ownerWindow)
        {
        	current_window.ownerWindow = arguments[0].ownerWindow;
        }
		
		if (arguments[0].max_date)
        {
        	current_window.max_date = arguments[0].max_date;
        }
				
		if ( arguments[0].Year ) {
			this.Year = arguments[0].Year;
		}
		else 
			this.Year = null; //Date.parseDate(getGlobalOptions().date, 'd.m.Y').getFullYear();
		
		current_window.Not_Z_Group_Diag = false;
		if ( arguments[0].formParams.Not_Z_Group_Diag )
        {
        	current_window.Not_Z_Group_Diag = arguments[0].formParams.Not_Z_Group_Diag;			
        }
		
		this.usedUslugaComplexCodeList = [];
		if ( arguments[0].formParams.usedUslugaComplexCodeList )
        {
        	current_window.usedUslugaComplexCodeList = arguments[0].formParams.usedUslugaComplexCodeList;			
        }

		this.Person_id = null;
		if (arguments[0].Person_id) {
			this.Person_id = arguments[0].Person_id;
		}
		
		this.EvnDirectionGrid.getGrid().getStore().removeAll();
		
		this.EvnDirectionGrid.setReadOnly('view' == this.action);
		
		current_window.findById('EVDO13SEFPersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnVizitDispOrp_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EVDO13SEFPersonInformationFrame', field);
			}
		});
		
  		var loadMask = new Ext.LoadMask(Ext.get('EvnVizitDispOrp13SecEditWindow'), { msg: LOAD_WAIT });
		loadMask.show();

        base_form.setValues(arguments[0].formParams);
        base_form.clearInvalid();
		if (arguments[0].formParams && arguments[0].formParams.EvnDiagDopDispGridData) {
			current_window.EvnDiagDopDispGrid.getGrid().getStore().loadData(Ext.util.JSON.decode(arguments[0].formParams.EvnDiagDopDispGridData));
		}
		
		this.sex_id = arguments[0].Sex_id;
		this.age = arguments[0].Person_Age;		

		var uslugacategory_combo = base_form.findField('UslugaCategory_id');
		var uslugacomplex_combo = current_window.findById('EVDO13SEFUslugaComplexCombo');
		
		var uslugacategorylist = ['nothing'];
		switch ( getRegionNick() ) {
			//case 'perm':
			case 'buryatiya':
				uslugacategorylist.push('tfoms');
			break;

			case 'kz':
				uslugacategorylist.push('classmedus');
			break;

			case 'ekb':
				uslugacategorylist.push('gost2011');
				uslugacategorylist.push('tfoms');
			break;

			default:
				uslugacategorylist.push('gost2011');
				break;
		}

		if (getRegionNick() != 'perm') {
			uslugacomplex_combo.getStore().baseParams['uslugaCategoryList'] = Ext.util.JSON.encode(uslugacategorylist);
		}
		// фильтрация для ддс 2 этап 
		uslugacomplex_combo.getStore().baseParams['DispFilter'] = "DispOrp13SecVizit";
		uslugacomplex_combo.getStore().baseParams.Person_id = this.Person_id;
		uslugacomplex_combo.getStore().baseParams['DispClass_id'] = this.DispClass_id;
		uslugacomplex_combo.getStore().baseParams['disallowedUslugaComplexCodeList'] = Ext.util.JSON.encode(this.usedUslugaComplexCodeList);
		uslugacomplex_combo.getStore().load();
		uslugacomplex_combo.fireEvent('change', uslugacomplex_combo, uslugacomplex_combo.getValue());
		
		this.Person_BirthDay = arguments[0].Person_Birthday;
		current_window.findById('EVDO13SEFEvnVizitDispOrp_setDate').setValue(arguments[0].formParams['EvnVizitDispOrp_setDate']);
		
		var med_personal_id = arguments[0].formParams.MedPersonal_id;
		var lpu_section_id = arguments[0].formParams.LpuSection_id;
		
		switch (current_window.action)
        {
            case 'add':
                current_window.setTitle(WND_POL_EVDDADD);
                current_window.enableEdit(true);
				var sex_id = arguments[0].Sex_id;								
				loadMask.hide();
				current_window.findById('EVDO13SEFEvnVizitDispOrp_setDate').focus(false, 250);
				var diag_combo = this.findById('EVDO13SEFDiagCombo');
				diag_combo.getStore().load({
					callback: function() {
						diag_combo.getStore().each(function(record) {
							if (record.data.Diag_Code == 'Z00.0')
							{
								diag_combo.setValue(record.data.Diag_id);
								diag_combo.fireEvent('select', diag_combo, record, 0);
								diag_combo.fireEvent('change', diag_combo, record.data.Diag_id, 0);
							}
						});
					},
					params: { where: "where DiagLevel_id = 4 and Diag_Code = 'Z00.0'"}
				});
				// ограничиваем годом пришедшим извне.
				this.setMinDate(current_window.action);

				if (getRegionNick() == 'perm') {
					var date1 = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
					var date2 = new Date('2014-12-31');
					if (!Ext.isEmpty(base_form.findField('EvnVizitDispOrp_setDate').getValue())) {
						date1 = base_form.findField('EvnVizitDispOrp_setDate').getValue();
					}
					if (date1 < date2) {
						uslugacategory_combo.setFieldValue('UslugaCategory_SysNick', 'tfoms');
					} else {
						uslugacategory_combo.setFieldValue('UslugaCategory_SysNick', 'gost2011');
					}
					uslugacategory_combo.fireEvent('select', uslugacategory_combo, uslugacategory_combo.getStore().getById(uslugacategory_combo.getValue()));
				}
				else if(getRegionNick().inlist(['perm', 'ufa', 'adygeya', 'ekb', 'khak', 'vologda'])) {
					uslugacategory_combo.setFieldValue('UslugaCategory_SysNick', 'gost2011');
					uslugacategory_combo.fireEvent('select', uslugacategory_combo, uslugacategory_combo.getStore().getById(uslugacategory_combo.getValue()));
				}
				
				base_form.findField('DopDispAlien_id').setValue(1);
                break;

        	case 'edit':
			case 'view':
				if (current_window.action == 'edit') {
					current_window.setTitle(WND_POL_EVDDEDIT);
					current_window.enableEdit(true);
				} else {
					current_window.setTitle(WND_POL_EVDDVIEW);
					current_window.enableEdit(false);
				}
				base_form.findField('UslugaComplex_id').fireEvent('change', base_form.findField('UslugaComplex_id'), base_form.findField('UslugaComplex_id').getValue() );

				var setDate = base_form.findField('EvnVizitDispOrp_setDate').getValue();
				var setTime = base_form.findField('EvnVizitDispOrp_setTime').getValue();
				var disDate = base_form.findField('EvnVizitDispOrp_disDate').getValue();
				var disTime = base_form.findField('EvnVizitDispOrp_disTime').getValue();

				if ((!Ext.isEmpty(disDate) || !Ext.isEmpty(disTime)) && (disDate-setDate != 0 || setTime != disTime)) {
					this.toggleVisibleDisDTPanel('show');
				}

				var diag_combo = this.findById('EVDO13SEFDiagCombo');
				var diag_id = diag_combo.getValue();
				if (diag_id != null && diag_id.toString().length > 0)
				{
					diag_combo.getStore().load({
						callback: function() {
							diag_combo.getStore().each(function(record) {
								if (record.data.Diag_id == diag_id)
								{
									diag_combo.setValue(record.data.Diag_id);
									diag_combo.fireEvent('select', diag_combo, record, 0);
									diag_combo.fireEvent('change', diag_combo, record.data.Diag_id, 0);
								}
							});
						},
						params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
					});
				}

				var uslugacomplex_id = uslugacomplex_combo.getValue();
				if (uslugacomplex_id != null && uslugacomplex_id.toString().length > 0)
				{
					uslugacomplex_combo.getStore().load({
						callback: function() {
							uslugacomplex_combo.getStore().each(function(record) {
								if (record.data.UslugaComplex_id == uslugacomplex_id)
								{
									uslugacomplex_combo.setValue(uslugacomplex_id);
									uslugacategory_combo.setValue(uslugacomplex_combo.getFieldValue('UslugaCategory_id'));
									uslugacomplex_combo.fireEvent('change', uslugacomplex_combo, uslugacomplex_combo.getValue());
								}
							});
						},
						params: { UslugaComplex_id: uslugacomplex_id }
					});
				}
				
				// устанавливаем врача
				var med_personal_id = base_form.findField('MedPersonal_id').getValue();
				var LpuSection_id = base_form.findField('LpuSection_id').getValue();
				if (!Ext.isEmpty(med_personal_id)) {
					var index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
						if ( Number(rec.get('MedPersonal_id')) == Number(med_personal_id) && Number(rec.get('LpuSection_id')) == Number(LpuSection_id) ) {
							return true;
						}
						else {
							return false;
						}
					});
					var med_personal_record = base_form.findField('MedStaffFact_id').getStore().getAt(index);

					if ( med_personal_record ) {
						base_form.findField('MedStaffFact_id').setValue(med_personal_record.get('MedStaffFact_id'));
					}
				}
				
				this.EvnDirectionGrid.loadData({
					params: {EvnDirection_pid: base_form.findField('EvnVizitDispOrp_id').getValue(), includeDeleted: 1},
					globalFilters: {EvnDirection_pid: base_form.findField('EvnVizitDispOrp_id').getValue(), includeDeleted: 1}
				});
				
				this.setMinDate(current_window.action);
				loadMask.hide();
				current_window.findById('EVDO13SEFEvnVizitDispOrp_setDate').fireEvent('change', current_window.findById('EVDO13SEFEvnVizitDispOrp_setDate'), current_window.findById('EVDO13SEFEvnVizitDispOrp_setDate').getValue());
				current_window.findById('EVDO13SEFEvnVizitDispOrp_setDate').focus(false, 250);
                break;
        }

        form.getForm().clearInvalid();
	},
	setMinDate: function(action)
	{
		if (action=='add')
		{
			// ограничиваем годом пришедшим извне.
			if (this.Year && this.Year>0)
			{
				this.findById('EVDO13SEFEvnVizitDispOrp_setDate').setMinValue(Date.parseDate('01.01.' + this.Year, 'd.m.Y'));
			}
			else 
			{
				this.findById('EVDO13SEFEvnVizitDispOrp_setDate').setMinValue(Date.parseDate('01.01.' + (Date.parseDate(getGlobalOptions().date, 'd.m.Y').getFullYear()-1), 'd.m.Y'));
			}
		}
		else 
		{
			var year = this.findById('EVDO13SEFEvnVizitDispOrp_setDate').getValue().getFullYear();
			if (year)
			{
				this.findById('EVDO13SEFEvnVizitDispOrp_setDate').setMinValue(Date.parseDate('01.01.' + year, 'd.m.Y'));
			}
		}
	},
	setDisDT: function() {
		if ( this.isVisibleDisDTPanel ) {
			return false;
		}

		var base_form = this.findById('EvnVizitDispOrp13SecEditForm').getForm();

		base_form.findField('EvnVizitDispOrp_disDate').setValue(base_form.findField('EvnVizitDispOrp_setDate').getValue());
		base_form.findField('EvnVizitDispOrp_disTime').setValue(base_form.findField('EvnVizitDispOrp_setTime').getValue());
	},
	toggleVisibleDisDTPanel: function(action)
	{
		var base_form = this.findById('EvnVizitDispOrp13SecEditForm').getForm();

		if (action == 'show') {
			this.isVisibleDisDTPanel = false;
		} else if (action == 'hide') {
			this.isVisibleDisDTPanel = true;
		}

		if (this.isVisibleDisDTPanel) {
			this.findById('EVDO13SEF_EvnVizitDisDTPanel').hide();
			this.findById('EVDO13SEF_ToggleVisibleDisDTBtn').setText(lang['utochnit_period_vyipolneniya']);
			base_form.findField('EvnVizitDispOrp_disDate').setAllowBlank(true);
			base_form.findField('EvnVizitDispOrp_disTime').setAllowBlank(true);
			base_form.findField('EvnVizitDispOrp_disDate').setValue(null);
			base_form.findField('EvnVizitDispOrp_disTime').setValue(null);
			base_form.findField('EvnVizitDispOrp_disDate').setMaxValue(undefined);
			this.isVisibleDisDTPanel = false;
		} else {
			this.findById('EVDO13SEF_EvnVizitDisDTPanel').show();
			this.findById('EVDO13SEF_ToggleVisibleDisDTBtn').setText(lang['skryit_polya']);
			base_form.findField('EvnVizitDispOrp_disDate').setAllowBlank(false);
			base_form.findField('EvnVizitDispOrp_disTime').setAllowBlank(false);
			base_form.findField('EvnVizitDispOrp_disDate').setMaxValue(getGlobalOptions().date);
			this.isVisibleDisDTPanel = true;
		}
	},
	addDirectionIssled: function() {
		var win = this;
		var base_form = win.findById('EvnVizitDispOrp13SecEditForm').getForm();

		// если не сохранено, то нужно сначала сохранить
		if (Ext.isEmpty(base_form.findField('EvnVizitDispOrp_id').getValue())) {
			this.doSave(function() {
				win.addDirectionIssled();
			}, true);
			return false;
		}

		var EvnDirection_pid = base_form.findField('EvnVizitDispOrp_id').getValue();

		var personinfoframe = win.findById('EVDO13SEFPersonInformationFrame');
		getWnd('swDirectionMasterWindow').show({
			userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
			personData: {
				Person_id: personinfoframe.getFieldValue('Person_id'),
				Server_id: personinfoframe.getFieldValue('Server_id'),
				PersonEvn_id: personinfoframe.getFieldValue('PersonEvn_id'),
				Person_Firname: personinfoframe.getFieldValue('Person_Firname'),
				Person_Secname: personinfoframe.getFieldValue('Person_Secname'),
				Person_Surname: personinfoframe.getFieldValue('Person_Surname'),
				Person_Birthday: personinfoframe.getFieldValue('Person_Birthday')
			},
			dirTypeData: {
				DirType_id: 10,
				DirType_Code: 9,
				DirType_Name: 'На исследование'
			},
			dirTypeCodeIncList: ['9'],
			directionData: {
				EvnDirection_pid: EvnDirection_pid
				,DirType_id: 10
				,Lpu_sid: getGlobalOptions().lpu_id
			},
			onHide: function () {
				win.EvnDirectionGrid.getGrid().getStore().reload();
			}
		});
	},
	addDirectionConsult: function() {
		var win = this;
		var base_form = win.findById('EvnVizitDispOrp13SecEditForm').getForm();

		// если не сохранено, то нужно сначала сохранить
		if (Ext.isEmpty(base_form.findField('EvnVizitDispOrp_id').getValue())) {
			this.doSave(function() {
				win.addDirectionConsult();
			}, true);
			return false;
		}

		var EvnDirection_pid = base_form.findField('EvnVizitDispOrp_id').getValue();

		var personinfoframe = win.findById('EVDO13SEFPersonInformationFrame');
		getWnd('swDirectionMasterWindow').show({
			userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
			personData: {
				Person_id: personinfoframe.getFieldValue('Person_id'),
				Server_id: personinfoframe.getFieldValue('Server_id'),
				PersonEvn_id: personinfoframe.getFieldValue('PersonEvn_id'),
				Person_Firname: personinfoframe.getFieldValue('Person_Firname'),
				Person_Secname: personinfoframe.getFieldValue('Person_Secname'),
				Person_Surname: personinfoframe.getFieldValue('Person_Surname'),
				Person_Birthday: personinfoframe.getFieldValue('Person_Birthday')
			},
			dirTypeData: {
				DirType_id: 3,
				DirType_Code: 3,
				DirType_Name: 'На консультацию'
			},
			dirTypeCodeExcList: ['1','4','5','6','7','8','9','10','11','13','14','15','16','17','18'],
			directionData: {
				EvnDirection_pid: EvnDirection_pid
				,DirType_id: 3
				,Lpu_sid: getGlobalOptions().lpu_id
			},
			onHide: function () {
				win.EvnDirectionGrid.getGrid().getStore().reload();
			}
		});
	},
	addDirectionPolka: function() {
		var win = this;
		var base_form = win.findById('EvnVizitDispOrp13SecEditForm').getForm();

		// если не сохранено, то нужно сначала сохранить
		if (Ext.isEmpty(base_form.findField('EvnVizitDispOrp_id').getValue())) {
			this.doSave(function() {
				win.addDirectionPolka();
			}, true);
			return false;
		}

		var EvnDirection_pid = base_form.findField('EvnVizitDispOrp_id').getValue();

		var personinfoframe = win.findById('EVDO13SEFPersonInformationFrame');
		getWnd('swDirectionMasterWindow').show({
			userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
			personData: {
				Person_id: personinfoframe.getFieldValue('Person_id'),
				Server_id: personinfoframe.getFieldValue('Server_id'),
				PersonEvn_id: personinfoframe.getFieldValue('PersonEvn_id'),
				Person_Firname: personinfoframe.getFieldValue('Person_Firname'),
				Person_Secname: personinfoframe.getFieldValue('Person_Secname'),
				Person_Surname: personinfoframe.getFieldValue('Person_Surname'),
				Person_Birthday: personinfoframe.getFieldValue('Person_Birthday')
			},
			dirTypeData: {
				DirType_id: 16,
				DirType_Code: 12,
				DirType_Name: 'На поликлинический прием'
			},
			dirTypeCodeExcList: ['1','4','5','6','7','8','9','10','11','13','14','15','16','17','18'],
			directionData: {
				EvnDirection_pid: EvnDirection_pid
				,DirType_id: 16
				,Lpu_sid: getGlobalOptions().lpu_id
			},
			onHide: function () {
				win.EvnDirectionGrid.getGrid().getStore().reload();
			}
		});
	},
	width: 700
});