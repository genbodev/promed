/**
* swEvnVizitDispOrp13EditWindow - окно редактирования/добавления осмотра по диспасеризации детей-сирот
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
* @comment    Префикс для id компонентов EVDO13EW (swEvnVizitDispOrp13EditWindow)
*	            TABINDEX_EVDO13EF: 9400
*
*
* Использует: окно редактирования талона по диспасеризации детей-сирот (swEvnPLDispOrpEditWindow)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnVizitDispOrp13EditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	/* */
	codeRefresh: true,
	objectName: 'swEvnVizitDispOrp13EditWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnVizitDispOrp13EditWindow.js',
	action: null,
	buttonAlign: 'left',
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    collapsible: true,
	doSave: function(callback) {
		var add_flag = true;
		var current_window = this;
		var base_form = this.findById('EvnVizitDispOrp13EditForm').getForm();
		var index = -1;
		var orpdisp_spec_id = current_window.findById('EVDO13EWOrpDispSpecCombo').getValue();
		var orpdisp_spec_name = '';
		var orpdisp_spec_code = '';
		var lpu_section_id = current_window.findById('EVDO13EWLpuSectionCombo').getValue();
		var lpu_section_name = '';
		var med_staff_fact_id = current_window.findById('EVDO13EWMedPersonalCombo').getValue();
		current_window.findById('EVDO13EWDiagCombo').fireEvent('blur', current_window.findById('EVDO13EWDiagCombo'));
		var diag_id = current_window.findById('EVDO13EWDiagCombo').getValue();
		var diag_code = '';
		var diag_name = '';
		var orpdispdiagtype_id = current_window.findById('EVDO13EWDopDispDiagTypeCombo').getValue();
		var isalien_id = Ext.getCmp('EVDO13EWDopDispAlien_idCombo').getValue();
		var personinfoframe = current_window.findById('EVDO13EWPersonInformationFrame');
		
		var record_status = current_window.findById('EVDO13EWRecord_Status').getValue();

		// Проверка на наличие у врача кода ДЛО или специальности https://redmine.swan.perm.ru/issues/47172
		// Проверку кода ДЛО убрали в https://redmine.swan.perm.ru/issues/118763
		if ( getRegionNick().inlist([ 'kareliya', 'penza' ]) ) {
			var
				MedSpecOms_id = base_form.findField('MedStaffFact_id').getFieldValue('MedSpecOms_id'),
				MedPersonal_Snils = base_form.findField('MedStaffFact_id').getFieldValue('Person_Snils');

			if ( Ext.isEmpty(MedSpecOms_id) ) {
				sw.swMsg.alert(lang['soobschenie'], lang['u_vracha_ne_ukazana_spetsialnost'], function() {  } );
				return false;
			}
			else if ( Ext.isEmpty(MedPersonal_Snils) ) {
				sw.swMsg.alert(lang['soobschenie'], lang['u_vracha_ne_ukazan_snils'], function() {  } );
				return false;
			}
		}

		if (!current_window.findById('EvnVizitDispOrp13EditForm').getForm().isValid())
		{
            Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                	current_window.findById('EVDO13EWEvnVizitDispOrp_setDate').focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
		}
		
		if ( orpdisp_spec_id == 1 && (current_window.findById('EVDO13EWEvnVizitDispOrp_setDate').getValue() < current_window.max_date) )
		{
			Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                	current_window.findById('EVDO13EWEvnVizitDispOrp_setDate').focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: "Осмотр педиатра не может быть проведен ранее других осмотров или даты получения результатов исследований.",
                title: ERR_INVFIELDS_TIT
            });
            return false;		
		}

		if (record_status == 1)
		{
			record_status = 2;
		}

		index = current_window.findById('EVDO13EWOrpDispSpecCombo').getStore().findBy(function(rec) { return rec.get('OrpDispSpec_id') == orpdisp_spec_id; });
		if (index >= 0)
		{
			orpdisp_spec_name = current_window.findById('EVDO13EWOrpDispSpecCombo').getStore().getAt(index).data.OrpDispSpec_Name;
			orpdisp_spec_code = current_window.findById('EVDO13EWOrpDispSpecCombo').getStore().getAt(index).data.OrpDispSpec_Code;
		}
		
		index = current_window.findById('EVDO13EWLpuSectionCombo').getStore().findBy(function(rec) { return rec.get('LpuSection_id') == lpu_section_id; });
		if (index >= 0)
		{
			lpu_section_name = current_window.findById('EVDO13EWLpuSectionCombo').getStore().getAt(index).data.LpuSection_Name;
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

		var item = null;
		for(var key in this.dopDispInfoConsentData) {
			if (typeof this.dopDispInfoConsentData[key] == 'object' && !Ext.isEmpty(this.dopDispInfoConsentData[key].OrpDispSpec_Code) && this.dopDispInfoConsentData[key].OrpDispSpec_Code == orpdisp_spec_code) {
				item = this.dopDispInfoConsentData[key];
			}
		}

		dop_disp_info_consent_id = item.DopDispInfoConsent_id;
		dop_disp_info_consent_is_earlier = item.DopDispInfoConsent_IsEarlier;

		if (!dop_disp_info_consent_is_earlier && set_date < this.EvnPLDispOrp_setDate) {
			Ext.MessageBox.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					current_window.findById('EVDO13EWEvnVizitDispOrp_setDate').focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: lang['data_vyipolneniya_osmotra_issledovaniya_ne_doljna_byit_ranshe_datyi_nachala_dispanserizatsii'],
				title: lang['oshibka']
			});
			return false;
		}

		if ( set_date < Date.parseDate('01.01.' + set_date.getFullYear(), 'd.m.Y') )
		{
			Ext.MessageBox.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					current_window.findById('EVDO13EWEvnVizitDispOrp_setDate').focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: lang['data_nachala_ne_mojet_byit_menshe_01_01'] + set_date.getFullYear() + '.',
				title: lang['oshibka']
			});
			return false;
		}

		
		var record = current_window.findById('EVDO13EWMedPersonalCombo').getStore().getById(med_staff_fact_id);
		if ( record ) {
			med_personal_fio = record.get('MedPersonal_Fio');
			med_personal_id = record.get('MedPersonal_id');
		} else {
			med_personal_fio = null;
			med_personal_id = null;
		}

		base_form.findField('MedPersonal_id').setValue(med_personal_id);

		index = current_window.findById('EVDO13EWDiagCombo').getStore().findBy(function(rec) { return rec.get('Diag_id') == diag_id; });
		if (index >= 0)
		{
			diag_code = current_window.findById('EVDO13EWDiagCombo').getStore().getAt(index).data.Diag_Code;
			diag_name = diag_code + '. ' + current_window.findById('EVDO13EWDiagCombo').getStore().getAt(index).data.Diag_Name;
		}
		
		if (current_window.action != 'add')
		{
			add_flag = false;
		}
		//var tumor_record = base_form.findField('TumorStage_id').getStore().getById(base_form.findField('TumorStage_id').getValue());
		var tumor_name = null;
		/*if(tumor_record)
			tumor_name = tumor_record.get('TumorStage_Name');*/
		var data = {
			'EvnVizitDispOrp_id': current_window.findById('EVDO13EWEvnVizitDispOrp_id').getValue(),
			'DispClass_id': current_window.DispClass_id,
			'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
			'Server_id': base_form.findField('Server_id').getValue(),
			'OrpDispSpec_id': orpdisp_spec_id,
			'UslugaComplex_id': base_form.findField('UslugaComplex_id').getValue(),
			'DopDispInfoConsent_id': dop_disp_info_consent_id,
			'LpuSection_id': lpu_section_id,
			'Lpu_uid': base_form.findField('Lpu_uid').getValue(),
			'MedSpecOms_id': base_form.findField('MedSpecOms_id').getValue(),
			'LpuSectionProfile_id': base_form.findField('LpuSectionProfile_id').getValue(),
			'MedPersonal_id': med_personal_id,
			'MedStaffFact_id': med_staff_fact_id,
			'Diag_id': diag_id,
			'TumorStage_id': base_form.findField('TumorStage_id').getValue(),
			'TumorStage_Name': tumor_name,
			'OrpDispSpec_Name': orpdisp_spec_name,
			'OrpDispSpec_Code': orpdisp_spec_code,
			'EvnVizitDispOrp_setDate': base_form.findField('EvnVizitDispOrp_setDate').getValue(),
			'EvnVizitDispOrp_setTime': base_form.findField('EvnVizitDispOrp_setTime').getValue(),
			'EvnVizitDispOrp_disDate': base_form.findField('EvnVizitDispOrp_disDate').getValue(),
			'EvnVizitDispOrp_disTime': base_form.findField('EvnVizitDispOrp_disTime').getValue(),
			'LpuSection_Name': lpu_section_name,
			'MedPersonal_Fio': med_personal_fio,
			'Diag_Code': diag_code,
			'Diag_Name': diag_name,
			'DopDispDiagType_id': orpdispdiagtype_id,
			'DopDispAlien_id': isalien_id,
			'Record_Status': record_status
		};

		current_window.EvnDiagDopDispGrid.getGrid().getStore().clearFilter();
		data.EvnDiagDopDispGridData = Ext.util.JSON.encode(getStoreRecords( current_window.EvnDiagDopDispGrid.getGrid().getStore() ));
		current_window.EvnDiagDopDispGrid.getGrid().getStore().filterBy(function(record) {
			if (record.data.Record_Status != 3) { return true; } else { return false; }
		});
		
		current_window.getLoadMask("Подождите, идет сохранение...").show();
		base_form.submit({
			url: '/?c=EvnPLDispOrp13&m=saveEvnVizitDispOrp',
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
	id: 'EvnVizitDispOrp13EditWindow',
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

		var base_form = win.findById('EvnVizitDispOrp13EditForm').getForm();
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
					var personinfoframe = win.findById('EVDO13EWPersonInformationFrame'),
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
				id: 'EVDO13EWSaveButton',
				tabIndex: TABINDEX_EVDO13EF+11,
				text: BTN_FRMSAVE
			},
			{
				text: '-'
			},
				HelpButton(this, -1),
			{
				handler: function() {
					win.hide();
				},
				iconCls: 'cancel16',
				id: 'EVDO13EWCancelButton',
				onTabAction: function() {
					Ext.getCmp('EVDO13EWEvnVizitDispOrp_setDate').focus(true, 200);
				},
				onShiftTabAction: function() {
					Ext.getCmp('EVDO13EWSaveButton').focus(true, 200);
				},
				tabIndex: TABINDEX_EVDO13EF+12,
				text: BTN_FRMCANCEL
			}],
            items: [
				new	sw.Promed.PersonInformationPanelShort({
					id: 'EVDO13EWPersonInformationFrame',
					region: 'north'
				}),
				new Ext.form.FormPanel({
					autoScroll: true,
					bodyBorder: false,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					frame: false,
					id: 'EvnVizitDispOrp13EditForm',
					labelAlign: 'right',
					labelWidth: 150,
					items: [{
						id: 'EVDO13EWEvnVizitDispOrp_id',
						name: 'EvnVizitDispOrp_id',
						value: 0,
						xtype: 'hidden'
					}, {
						name: 'EvnPLDispOrp_id',
						xtype: 'hidden'
					}, {
						id: 'EVDO13EWRecord_Status',
						name: 'Record_Status',
						value: 0,
						xtype: 'hidden'
					}, {
						name: 'PersonEvn_id',
						xtype: 'hidden'
					}, {
						name: 'Server_id',
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
								fieldLabel: lang['data_nachala_vyipolneniya'],
								format: 'd.m.Y',
								id: 'EVDO13EWEvnVizitDispOrp_setDate',
								listeners: {
									'keydown':  function(inp, e) {
										if ( e.shiftKey && e.getKey() == Ext.EventObject.TAB )
										{
											e.stopEvent();
											Ext.getCmp('EVDO13EWCancelButton').focus(true, 200);
										}
									},
									'change': function(field, newValue, oldValue) {
										if (blockedDateAfterPersonDeath('personpanelid', 'EVDO13EWPersonInformationFrame', field, newValue, oldValue)) return;
										this.findById('EVDO13EWDiagCombo').setFilterByDate(newValue);
										this.setOrpDispSpecFilter();
										this.filterLpuCombo();
										this.setLpuSectionAndMedStaffFactFilter();
										this.filterProfileAndMedSpec();
										this.setDisDT();
									}.createDelegate(this)
								},
								maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
								//minValue: Date.parseDate('01.01.' + Date.parseDate(getGlobalOptions().date, 'd.m.Y').getFullYear(), 'd.m.Y'),
								name: 'EvnVizitDispOrp_setDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								tabIndex: TABINDEX_EVDO13EF + 1,
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
								id: 'EVDO13EWEvnVizitDispOrp_setTime',
								name: 'EvnVizitDispOrp_setTime',
								onTriggerClick: function() {
									var base_form = this.findById('EvnVizitDispOrp13EditForm').getForm();

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
								tabIndex: TABINDEX_EVDO13EF + 1,
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
								id: 'EVDO13EW_ToggleVisibleDisDTBtn',
								text: lang['utochnit_period_vyipolneniya'],
								handler: function() {
									this.toggleVisibleDisDTPanel();
								}.createDelegate(this)
							}]
						}]
					}, {
						border: false,
						layout: 'column',
						id: 'EVDO13EW_EvnVizitDisDTPanel',
						items: [{
							border: false,
							layout: 'form',
							labelWidth: 180,
							items: [{
								fieldLabel: lang['data_okonchaniya_vyipolneniya'],
								format: 'd.m.Y',
								id: 'EVDO13EWEvnVizitDispOrp_disDate',
								name: 'EvnVizitDispOrp_disDate',
								maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								tabIndex: TABINDEX_EVDO13EF + 3,
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
								id: 'EVDO13EWEvnVizitDispOrp_disTime',
								name: 'EvnVizitDispOrp_disTime',
								onTriggerClick: function() {
									var base_form = this.findById('EvnVizitDispOrp13EditForm').getForm();

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
								tabIndex: TABINDEX_EVDO13EF + 4,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield'
							}]
						}, {
							layout: 'form',
							border: false,
							items: [{
								xtype: 'button',
								id: 'EVDO13EW_DTCopyBtn',
								text: '=',
								handler: function() {
									var base_form = this.findById('EvnVizitDispOrp13EditForm').getForm();

									base_form.findField('EvnVizitDispOrp_disDate').setValue(base_form.findField('EvnVizitDispOrp_setDate').getValue());
									base_form.findField('EvnVizitDispOrp_disTime').setValue(base_form.findField('EvnVizitDispOrp_setTime').getValue());
								}.createDelegate(this)
							}]
						}]
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['spetsialnost_vracha'],
						id: 'EVDO13EWOrpDispSpecCombo',
						lastQuery: '',
						listeners: {
							'change': function(combo, newValue) {
								var base_form = win.findById('EvnVizitDispOrp13EditForm').getForm();
								if (getRegionNick().inlist(['ekb', 'ufa', 'perm', 'khak'])) {
									if (!Ext.isEmpty(newValue)) {
										var UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
										if (getRegionNick() == 'ufa') {
											// вычленяем SurveyTypeLink_id
											for(var key in win.dopDispInfoConsentData) {
												if (typeof win.dopDispInfoConsentData[key] == 'object' && !Ext.isEmpty(win.dopDispInfoConsentData[key].OrpDispSpec_Code) && win.dopDispInfoConsentData[key].OrpDispSpec_Code == base_form.findField('OrpDispSpec_id').getFieldValue('OrpDispSpec_Code')) {
													base_form.findField('UslugaComplex_id').getStore().baseParams.SurveyTypeLink_mid = this.dopDispInfoConsentData[key].SurveyTypeLink_id;
													base_form.findField('UslugaComplex_id').enable();
													win.loadUslugaComplexCombo();
													break;
												}
											}
										} else {
											base_form.findField('UslugaComplex_id').getStore().baseParams.OrpDispSpec_id = newValue;
											base_form.findField('UslugaComplex_id').enable();
											win.loadUslugaComplexCombo();
										}
									} else {
										base_form.findField('UslugaComplex_id').disable();
										base_form.findField('UslugaComplex_id').clearValue();
									}
								}
								this.filterProfileAndMedSpec();
								this.setLpuSectionAndMedStaffFactFilter();
								this.UpdateTumorField();
							}.createDelegate(this)
						},
						name: 'OrpDispSpec_id',
						tabIndex: TABINDEX_EVDO13EF + 2,
						validateOnBlur: false,
						width: 350,
						xtype: 'sworpdispspeccombo'
					}, {
						hidden: !getRegionNick().inlist(['ekb', 'perm', 'ufa', 'khak']),
						layout: 'form',
						border: false,
						items: [{
							hiddenName: 'UslugaComplex_id',
							allowBlank: !getRegionNick().inlist(['ekb', 'perm', 'ufa', 'khak']),
							width: 450,
							fieldLabel: lang['usluga'],
							nonDispOnly: false,
							xtype: 'swuslugacomplexnewcombo'
						}]
					}, {
						allowBlank: false,
						fieldLabel: lang['storonniy_spetsialist'],
						hiddenName: 'DopDispAlien_id',
						id: 'EVDO13EWDopDispAlien_idCombo',
						comboSubject: 'DopDispAlien',
						sortField: 'DopDispAlien_Code',
						tabIndex: TABINDEX_EVDO13EF + 3,
						width: 200,
						xtype: 'swcustomobjectcombo',
						lastQuery: '',
						listeners: {
							'change': function(combo, newValue) {
								var base_form = win.findById('EvnVizitDispOrp13EditForm').getForm();
								
								if ( getRegionNick().inlist([ 'krym', 'perm' ]) ) {
									var index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function(rec) {
										return (rec.get('LpuSectionProfile_id') == base_form.findField('LpuSectionProfile_id').getValue());
									});
								
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
						id: 'EVDO13EWLpuCombo',
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
						tabIndex: TABINDEX_EVDO13EF + 4,
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
						fieldLabel: lang['profil'],
						xtype: 'swlpusectionprofileremotecombo',
						tabIndex: TABINDEX_EVDO13EF + 5,
						width: 450,
						hiddenName: 'LpuSectionProfile_id',
						listeners: {
							'change': function(field, newValue, oldValue) {
								win.setLpuSectionAndMedStaffFactFilter();
							}
						}
					}, {
						fieldLabel: lang['spetsialnost'],
						xtype: 'swmedspecomsremotecombo',
						tabIndex: TABINDEX_EVDO13EF + 6,
						width: 450,
						hiddenName: 'MedSpecOms_id',
						listeners: {
							'change': function(field, newValue, oldValue) {
								win.setLpuSectionAndMedStaffFactFilter();
							}
						}
					}, {
						allowBlank: false,
						hiddenName: 'LpuSection_id',
						id: 'EVDO13EWLpuSectionCombo',
						lastQuery: '',
						listWidth: 650,
						linkedElements: [
							'EVDO13EWMedPersonalCombo'
						],
						listeners: {
							'select': function(combo, record, index) {
								combo.setValue(record.get('LpuSection_id'));
								combo.fireEvent('change', combo, combo.getValue());
							},
							'change': function (field, newValue, oldValue) {
								var base_form = win.findById('EvnVizitDispOrp13EditForm').getForm();

								if (getRegionNick() == 'ufa') {
									// услуга зависит от выбранного отделения
									base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
									win.loadUslugaComplexCombo();
								}
							}
						},
						tabIndex: TABINDEX_EVDO13EF + 7,
						width: 450,
						xtype: 'swlpusectionglobalcombo',
						parentElementId: 'EVDO13EWLpuCombo',
						allowBlank: !(getRegionNick() == 'buryatiya')
					}, {
						allowBlank: false,
						hiddenName: 'MedStaffFact_id',
						id: 'EVDO13EWMedPersonalCombo',
						lastQuery: '',
						listWidth: 650,
						parentElementId: 'EVDO13EWLpuSectionCombo',
						listeners: {
							'change': function(field, newValue, oldValue) {
								var base_form = win.findById('EvnVizitDispOrp13EditForm').getForm();

								if (getRegionNick() == 'ufa') {
									// услуга зависит от выбранного отделения
									base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
									win.loadUslugaComplexCombo();
								}

								if ( getRegionNick().inlist([ 'kareliya', 'penza' ]) && !Ext.isEmpty(newValue) ) {
									var index = field.getStore().findBy(function(rec) {
										return (rec.get('MedStaffFact_id') == newValue);
									});

									if ( index >= 0 ) {
										var
											MedSpecOms_id = field.getStore().getAt(index).get('MedSpecOms_id'),
											MedPersonal_Snils = field.getStore().getAt(index).get('Person_Snils');

										if ( Ext.isEmpty(MedSpecOms_id) ) {
											sw.swMsg.alert(lang['soobschenie'], lang['u_vracha_ne_ukazana_spetsialnost'], function() {  } );
											return false;
										}
										else if ( Ext.isEmpty(MedPersonal_Snils) ) {
											sw.swMsg.alert(lang['soobschenie'], lang['u_vracha_ne_ukazan_snils'], function() {  } );
											return false;
										}
									}
								}
							}
						},
						tabIndex: TABINDEX_EVDO13EF + 8,
						width: 450,
						xtype: 'swmedstafffactglobalcombo',
						allowBlank: !(getRegionNick() == 'buryatiya')
					}, {
						allowBlank: false,
						id: 'EVDO13EWDiagCombo',
						listWidth: 580,
						tabIndex: TABINDEX_EVDO13EF + 9,
						width: 450,
						xtype: 'swdiagcombo'
					}, 
					{
						xtype: 'swcommonsprcombo',
						comboSubject: 'TumorStage',
						hiddenName: 'TumorStage_id',
						fieldLabel: 'Стадия выявленного ЗНО',
						tabIndex: TABINDEX_EVDO13EF + 10,
						width: 450
					},
					{
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['zabolevanie'],
						id: 'EVDO13EWDopDispDiagTypeCombo',
						name: 'DopDispDiagType_id',
						tabIndex: TABINDEX_EVDO13EF + 11,
						validateOnBlur: false,
						width: 350,
						xtype: 'swdopdispdiagtypecombo'
					}, win.EvnDiagDopDispGrid, 
					win.EvnDirectionGrid],
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
    	sw.Promed.swEvnVizitDispOrp13EditWindow.superclass.initComponent.apply(this, arguments);
		
		this.findById('EVDO13EWDiagCombo').addListener('change', function(combo, newValue, oldValue) {
			var base_form = win.findById('EvnVizitDispOrp13EditForm').getForm();
			var diag_code = combo.getFieldValue('Diag_Code');

			if ( (Ext.isEmpty(diag_code) || diag_code.substr(0, 1) != 'Z') ) {
				if ( win.action == 'view' ) {
					base_form.findField('DopDispDiagType_id').disable();
				}
				else {
					base_form.findField('DopDispDiagType_id').enable();
				}

				base_form.findField('DopDispDiagType_id').setAllowBlank(false);
			}
			else {
				base_form.findField('DopDispDiagType_id').disable();
				base_form.findField('DopDispDiagType_id').setAllowBlank(true);
				base_form.findField('DopDispDiagType_id').clearValue();
			}
			win.UpdateTumorField();
		});
    },
	filterProfileAndMedSpec: function() {
		var win = this;
		var base_form = this.findById('EvnVizitDispOrp13EditForm').getForm();
		
		if (getRegionNick() == 'ekb') {
			win.MedSpecOms_id = null;
			
			for(var key in this.dopDispInfoConsentData) {
				if (typeof this.dopDispInfoConsentData[key] == 'object' && !Ext.isEmpty(this.dopDispInfoConsentData[key].OrpDispSpec_Code) && this.dopDispInfoConsentData[key].OrpDispSpec_Code == base_form.findField('OrpDispSpec_id').getFieldValue('OrpDispSpec_Code')) {
					win.MedSpecOms_id = this.dopDispInfoConsentData[key].MedSpecOms_id;
				}
			}
		}
		
		var curDate = getGlobalOptions().date;
		if ( !Ext.isEmpty(base_form.findField('EvnVizitDispOrp_setDate').getValue()) ) {
			curDate = Ext.util.Format.date(base_form.findField('EvnVizitDispOrp_setDate').getValue(), 'd.m.Y');
		}
		
		base_form.findField('LpuSectionProfile_id').getStore().removeAll();
		base_form.findField('MedSpecOms_id').getStore().removeAll();
		if (!Ext.isEmpty(base_form.findField('OrpDispSpec_id').getValue())) {
			// загружаем списки Профиль и Специальность в зависимости от Услуги
			base_form.findField('LpuSectionProfile_id').getStore().load({
				params: {
					OrpDispSpec_id: base_form.findField('OrpDispSpec_id').getValue(),
					onDate: curDate,
					DispClass_id: win.DispClass_id
				},
				callback: function() {
					base_form.findField('DopDispAlien_id').fireEvent('change', base_form.findField('DopDispAlien_id'), base_form.findField('DopDispAlien_id').getValue());
				}
			});
			base_form.findField('MedSpecOms_id').getStore().load({
				params: {
					OrpDispSpec_id: base_form.findField('OrpDispSpec_id').getValue(),
					onDate: curDate,
					DispClass_id: win.DispClass_id
				},
				callback: function() {
					base_form.findField('DopDispAlien_id').fireEvent('change', base_form.findField('DopDispAlien_id'), base_form.findField('DopDispAlien_id').getValue());
				}
			});
		}
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

        	var current_window = Ext.getCmp('EvnVizitDispOrp13EditWindow');

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
		var base_form = this.findById('EvnVizitDispOrp13EditForm').getForm();
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
		var base_form = this.findById('EvnVizitDispOrp13EditForm').getForm();
		
		// Учитываем дату и место выполнения
		var EvnVizitDispOrp_setDate = base_form.findField('EvnVizitDispOrp_setDate').getValue();
		var DopDispAlien_id = base_form.findField('DopDispAlien_id').getValue();
		var MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();

		base_form.findField('LpuSection_id').enableLinkedElements();
		base_form.findField('MedStaffFact_id').enableLinkedElements();

		if ( !Ext.isEmpty(DopDispAlien_id) && DopDispAlien_id == 2 ) {
			if(getRegionNick()== 'buryatiya' && base_form.findField('Lpu_uid').getValue()==getGlobalOptions().lpu_id){
				base_form.findField('LpuSection_id').clearValue();
				base_form.findField('MedStaffFact_id').clearValue();
				base_form.findField('Lpu_uid').clearValue();
			}
			// показать поля МО, Профиль, Специальность
			base_form.findField('Lpu_uid').showContainer();

			if ( !getRegionNick().inlist([ 'krym', 'perm' ]) ) {
				base_form.findField('LpuSectionProfile_id').hideContainer();
				base_form.findField('MedSpecOms_id').hideContainer();
			}
			else {
				base_form.findField('LpuSectionProfile_id').showContainer();
				base_form.findField('MedSpecOms_id').showContainer();
			}

			base_form.findField('Lpu_uid').setAllowBlank(getRegionNick().inlist([ 'pskov', 'ufa', 'ekb' ]));
			base_form.findField('LpuSectionProfile_id').setAllowBlank(!getRegionNick().inlist([ 'krym', 'perm' ]));
			base_form.findField('MedSpecOms_id').setAllowBlank(!getRegionNick().inlist([ 'krym', 'perm' ]));

			if(getRegionNick() != 'buryatiya') {
				base_form.findField('LpuSection_id').setAllowBlank(true);
				base_form.findField('MedStaffFact_id').setAllowBlank(true);
			}

			if ( getRegionNick().inlist([/* 'buryatiya', */'krym', 'perm' ]) ) {
				base_form.findField('LpuSection_id').disableLinkedElements();
				base_form.findField('MedStaffFact_id').disableParentElement();
			}

			if (
				(getRegionNick().inlist([ 'krym', 'perm' ]) && Ext.isEmpty(base_form.findField('LpuSectionProfile_id').getValue()))
				|| Ext.isEmpty(base_form.findField('Lpu_uid').getValue())
			) {
				base_form.findField('LpuSection_id').getStore().removeAll();
				base_form.findField('LpuSection_id').clearValue();
				win.lastLpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
				win.lastLpu_uid1 = base_form.findField('Lpu_uid').getValue();
			}
			
			if (
				(getRegionNick().inlist([ 'krym', 'perm' ]) && Ext.isEmpty(base_form.findField('MedSpecOms_id').getValue()))
				|| Ext.isEmpty(base_form.findField('Lpu_uid').getValue())
			) {
				base_form.findField('MedStaffFact_id').getStore().removeAll();
				base_form.findField('MedStaffFact_id').clearValue();
				win.lastMedSpecOms_id = base_form.findField('MedSpecOms_id').getValue();
				win.lastLpu_uid2 = base_form.findField('Lpu_uid').getValue();
			}

			var setDate = (!Ext.isEmpty(EvnVizitDispOrp_setDate) ? Ext.util.Format.date(EvnVizitDispOrp_setDate, 'd.m.Y') : null);
			
			if (
				(!Ext.isEmpty(base_form.findField('LpuSectionProfile_id').getValue()) || !getRegionNick().inlist([ 'krym', 'perm' ]))
				&& !Ext.isEmpty(base_form.findField('Lpu_uid').getValue())
				&& (
					base_form.findField('Lpu_uid').getValue() != win.lastLpu_uid1
					|| setDate != win.lastSetDate1
					|| (getRegionNick().inlist([ 'krym', 'perm' ]) && base_form.findField('LpuSectionProfile_id').getValue() != win.lastLpuSectionProfile_id)
				)
				||
				(
					getRegionNick().inlist([ 'buryatiya' ])
					&& Ext.isEmpty(base_form.findField('Lpu_uid').getValue())
				)
			) {
				win.lastLpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
				win.lastLpu_uid1 = base_form.findField('Lpu_uid').getValue();
				win.lastSetDate1 = setDate;

				base_form.findField('LpuSection_id').getStore().load({
					callback: function() {
						var store = base_form.findField('LpuSection_id').getStore();
						var ucid = null;
						var index = store.findBy(function (rec) {
							return (rec.get('LpuSection_id') == base_form.findField('LpuSection_id').getValue());
						});

						if (
							!(getRegionNick().inlist(['buryatiya']) && Ext.isEmpty(base_form.findField('Lpu_uid').getValue()))
						) {
							if (index >= 0) {
								ucid = store.getAt(index).get('LpuSection_id');
							} else if (store.getCount() && win.loadFirstMedPersonal) {
								ucid = store.getAt(0).get('LpuSection_id');
							}

							if (ucid) {
								base_form.findField('LpuSection_id').setValue(ucid);
							} else {
								base_form.findField('LpuSection_id').clearValue();
							}
						}
					}.createDelegate(this),
					params: {
						date: setDate,
						LpuSectionProfile_id: base_form.findField('LpuSectionProfile_id').getValue(),
						Lpu_id: base_form.findField('Lpu_uid').getValue(),
						mode: (getRegionNick().inlist([ 'krym', 'perm' ]))?'combo':'dispcontractcombo'
					}
				});
			}
			
			if (
				(!Ext.isEmpty(base_form.findField('MedSpecOms_id').getValue()) || !getRegionNick().inlist([ 'krym', 'perm' ]))
				&& !Ext.isEmpty(base_form.findField('Lpu_uid').getValue())
				&& (
					base_form.findField('Lpu_uid').getValue() != win.lastLpu_uid2
					|| setDate != win.lastSetDate2
					|| (getRegionNick().inlist([ 'krym', 'perm' ]) && base_form.findField('MedSpecOms_id').getValue() != win.lastMedSpecOms_id)
				)
				||
				(
					getRegionNick().inlist([ 'buryatiya' ])
					&& Ext.isEmpty(base_form.findField('Lpu_uid').getValue())
				)
			) {
				win.lastMedSpecOms_id = base_form.findField('MedSpecOms_id').getValue();
				win.lastLpu_uid2 = base_form.findField('Lpu_uid').getValue();
				win.lastSetDate2 = setDate;
				
				base_form.findField('MedStaffFact_id').getStore().load({
					callback: function() {
						var store = base_form.findField('MedStaffFact_id').getStore();
						var ucid = null;
						var index = store.findBy(function(rec) {
							return (rec.get('MedStaffFact_id') == MedStaffFact_id);
						});
						if ( index < 0 ) {
							index = store.findBy(function(rec) {
								return (rec.get('MedPersonal_id') == base_form.findField('MedPersonal_id').getValue());
							});
						}

						if ( index == -1 ) {
							index = store.findBy(function(rec) {
								return (rec.get('MedPersonal_id') == base_form.findField('MedPersonal_id').getValue());
							});
						}

						if (
							!(getRegionNick().inlist(['buryatiya']) && Ext.isEmpty(base_form.findField('Lpu_uid').getValue()))
						) {
							if (index >= 0) {
								ucid = store.getAt(index).get('MedStaffFact_id');
							} else if (store.getCount() && win.loadFirstMedPersonal) {
								ucid = store.getAt(0).get('MedStaffFact_id');
							}

							if (ucid) {
								base_form.findField('MedStaffFact_id').setValue(ucid);
								base_form.findField('LpuSection_id').setValue(base_form.findField('MedStaffFact_id').getFieldValue('LpuSection_id'));
								base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), base_form.findField('LpuSection_id').getValue());
							} else {
								base_form.findField('MedStaffFact_id').clearValue();
							}
						}
					}.createDelegate(this),
					params: {
						onDate: setDate,
						mode: (getRegionNick().inlist([ 'krym', 'perm' ]))?'combo':'dispcontractcombo',
						MedSpecOms_id: base_form.findField('MedSpecOms_id').getValue(),
						Lpu_id: base_form.findField('Lpu_uid').getValue()
					}
				});
			}
		} else {
			// скрыть поля МО, Профиль, Специальность
			base_form.findField('Lpu_uid').clearValue();
			base_form.findField('Lpu_uid').setAllowBlank(true);
			base_form.findField('Lpu_uid').hideContainer();
			base_form.findField('LpuSectionProfile_id').clearValue();
			base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
			base_form.findField('LpuSectionProfile_id').hideContainer();
			base_form.findField('MedSpecOms_id').clearValue();
			base_form.findField('MedSpecOms_id').setAllowBlank(true);
			base_form.findField('MedSpecOms_id').hideContainer();

			/*if ( getRegionNick().inlist([ 'buryatiya' ]) ) {
				base_form.findField('LpuSection_id').disableLinkedElements();
				base_form.findField('MedStaffFact_id').disableParentElement();
			}*/

			base_form.findField('LpuSection_id').setAllowBlank(false);
			base_form.findField('MedStaffFact_id').setAllowBlank(false);
			
			var OrpDispSpec_Code = base_form.findField('OrpDispSpec_id').getFieldValue('OrpDispSpec_Code');
			
			var isPerm = (getRegionNick() == 'perm');
			var isPskov = (getRegionNick() == 'pskov');
			var isUfa = (getRegionNick() == 'ufa');
			
			// Сохраняем текущие значения
			var LpuSection_id = base_form.findField('LpuSection_id').getValue();
			var MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();

			base_form.findField('LpuSection_id').clearValue();
			base_form.findField('MedStaffFact_id').clearValue();
			
			if ( OrpDispSpec_Code > 0 )
			{
				var set_date = Ext.getCmp('EVDO13EWEvnVizitDispOrp_setDate').getValue();
				
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

				if ( getRegionNick().inlist(['buryatiya']) ) {
					params.allowLowLevel = 'true';
				}

				if (isUfa) 
				{
					switch ( OrpDispSpec_Code )
					{	
						case 1: // Педиатрия
							params.arrayLpuSectionProfile = ['531','631','566','666','568','668','868','569','669','869'];
						break;
						case 2: // Неврология
							params.arrayLpuSectionProfile = ['537','637'];	
						break;
						case 3: // Офтальмология
							params.arrayLpuSectionProfile = ['542','642'];	
						break;
						case 4: // Детская хирургия
							params.arrayLpuSectionProfile = ['536','636'];	
						break;
						case 5: // Отоларингология
							params.arrayLpuSectionProfile = ['541','641'];	
						break;
						case 6: // Гинекология
							params.arrayLpuSectionProfile = ['540','640'];	
						break;
						case 7: // Стоматология детская
							params.arrayLpuSectionProfile = ['530','630'];	
						break;
						case 8: // Ортопедия-травматология
							params.arrayLpuSectionProfile = ['550','650','551','651','851'];
						break;
						case 10: // Детская урология-андрология
							params.arrayLpuSectionProfile = ['539','639'];	
						break;
						case 11: // Детская эндокринология
							params.arrayLpuSectionProfile = ['534', '634'];
						break;
						case 9: // Психиатрия
						case 12: // Детская психиатрия
						case 13: // Подростковая психиатрия
							params.arrayLpuSectionProfile = ['593', '693'];
						break;
					}
				}
				else if (isPskov) 
				{
					switch ( OrpDispSpec_Code )
					{	
						case 1: // Педиатрия
							params.arrayLpuSectionProfile = ['68'];
						break;
						case 2: // Неврология
							params.arrayLpuSectionProfile = ['53'];
						break;
						case 3: // Офтальмология
							params.arrayLpuSectionProfile = ['65'];
						break;
						case 4: // Детская хирургия
							params.arrayLpuSectionProfile = ['112','20'];
						break;
						case 5: // Отоларингология
							params.arrayLpuSectionProfile = ['162'];
						break;
						case 6: // Гинекология
							params.arrayLpuSectionProfile = ['136'];
						break;
						case 7: // Стоматология детская
							params.arrayLpuSectionProfile = ['85','86','63'];
						break;
						case 8: // Ортопедия-травматология
							params.arrayLpuSectionProfile = ['100','112','20'];
						break;
						case 10: // Детская урология-андрология
							params.arrayLpuSectionProfile = ['19','108','112','20'];
						break;
						case 11: // Детская эндокринология
							params.arrayLpuSectionProfile = ['122','21'];
						break;
						case 9: // Психиатрия
						case 12: // Детская психиатрия
						case 13: // Подростковая психиатрия
							params.arrayLpuSectionProfile = ['72','74'];
						break;
					}
				} else if ( isPerm ) {
					switch ( OrpDispSpec_Code )
					{	
						case 1:
							params.arrayLpuSectionProfile = ['917', '925', '0900', '0905', '1003', '1011', '57', '68', '151' ];
						break;
						case 2:
							params.arrayLpuSectionProfile = ['2800', '53'];
						break;
						case 3:
							params.arrayLpuSectionProfile = ['2700', '65'];
						break;
						case 4:
							params.arrayLpuSectionProfile = ['2300', '2350', '20', '112'];
						break;
						case 5:
							params.arrayLpuSectionProfile = ['2600', '162'];
						break;
						case 6:
							params.arrayLpuSectionProfile = ['2517', '2519', '136'];
						break;
						case 7:
							params.arrayLpuSectionProfile = ['1830', '1800', '1802', '1810', '85', '86', '89', '87', '171'];
						break;
						case 8:
							params.arrayLpuSectionProfile = ['1450', '2300', '2350', '20', '100', '112'];
						break;
						case 9:
						case 12:
						case 13:
							params.arrayLpuSectionProfile = ['3710', '72'];
						break;
						case 10:
							params.arrayLpuSectionProfile = ['1530', '1500', '2300', '2350', '19', '20', '108', '112'];
						break;
						case 11:
							params.arrayLpuSectionProfile = ['0530', '0510', '21', '122'];
						break;
					}
				}
				if ( set_date )
				{
					params.onDate = Ext.util.Format.date(set_date, 'd.m.Y');
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
		}
	},
	setOrpDispSpecFilter: function() {
		var win = this;
		var set_date = this.findById('EVDO13EWEvnVizitDispOrp_setDate').getValue();
		if ( !set_date || set_date == '' )
			var age = this.age;
		else
		{			
			var birth_date = this.Person_BirthDay;
			var age = (birth_date.getMonthsBetween(set_date) - (birth_date.getMonthsBetween(set_date) % 12)) / 12;
		}
		var sex_id = this.sex_id;
		var OrpDispSpec_id = Ext.getCmp('EVDO13EWOrpDispSpecCombo').getValue();

		// фильтруем сторе у специальности врача
		Ext.getCmp('EVDO13EWOrpDispSpecCombo').getStore().clearFilter();
		Ext.getCmp('EVDO13EWOrpDispSpecCombo').lastQuery = '';

		Ext.getCmp('EVDO13EWOrpDispSpecCombo').getStore().filterBy(function(record) 
		{
			// возможность выбора из справочника только тех специальностей врача, для которых еще не заведен осмотр
			// возможность выбора из справочника только тех специальностей врача, для которых в разделе «Информированное добровольное согласие» проставлен чекбокс «Согласие гражданина» либо «Пройдено ранее»
			// + фильтры из ТЗ
			if ( !record.get('OrpDispSpec_Code').inlist(win.orpDispSpecAllowed) ) {
				return false;
			}
			return true;
		});

		if ( !Ext.isEmpty(OrpDispSpec_id) ) {
			Ext.getCmp('EVDO13EWOrpDispSpecCombo').setValue(OrpDispSpec_id);
		}
	},
	orpDispSpecAllowed: [],
	dopDispInfoConsentData: [],
	loadUslugaComplexCombo: function() {
		var win = this;
		var base_form = win.findById('EvnVizitDispOrp13EditForm').getForm();

		// повторно грузить одно и то же не нужно
		var newUslugaComplexParams = Ext.util.JSON.encode(base_form.findField('UslugaComplex_id').getStore().baseParams);
		if (newUslugaComplexParams != win.lastUslugaComplexParams) {
			var currentUslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
			win.getLoadMask(lang['zagruzka_spiska_vozmojnyih_uslug_pojaluysta_podojdite']).show();
			base_form.findField('UslugaComplex_id').clearValue();
			base_form.findField('UslugaComplex_id').getStore().removeAll();
			win.lastUslugaComplexParams = newUslugaComplexParams;

			if (getRegionNick() == 'ufa' && Ext.isEmpty(base_form.findField('UslugaComplex_id').getStore().baseParams.SurveyTypeLink_mid)) {
				win.getLoadMask().hide();
				return false;
			}

			if (getRegionNick().inlist([ 'ekb', 'perm', 'khak' ]) && Ext.isEmpty(base_form.findField('UslugaComplex_id').getStore().baseParams.OrpDispSpec_id)) {
				win.getLoadMask().hide();
				return false;
			}

			base_form.findField('UslugaComplex_id').getStore().load({
				callback: function () {
					win.getLoadMask().hide();
					index = base_form.findField('UslugaComplex_id').getStore().findBy(function (rec) {
						return (rec.get('UslugaComplex_id') == currentUslugaComplex_id);
					});

					if (base_form.findField('UslugaComplex_id').getStore().getCount() == 1) {
						ucid = base_form.findField('UslugaComplex_id').getStore().getAt(0).get('UslugaComplex_id');
						base_form.findField('UslugaComplex_id').setValue(ucid);
						base_form.findField('UslugaComplex_id').disable();
					} else if (base_form.findField('UslugaComplex_id').getStore().getCount() > 1) {
						if (index >= 0) {
							ucid = base_form.findField('UslugaComplex_id').getStore().getAt(index).get('UslugaComplex_id');
						} else {
							// по умолчанию подставляем эти услуги
							if (getRegionNick() == 'perm') {
								index = base_form.findField('UslugaComplex_id').getStore().findBy(function (rec) {
									switch (base_form.findField('OrpDispSpec_id').getValue()) {
										case 7: // 35:
											return (rec.get('UslugaComplex_Code') == 'B04.064.002');
											break;
										case 4: // 29:
											return (rec.get('UslugaComplex_Code') == 'B04.010.002');
											break;
										case 10: // 34:
											return (rec.get('UslugaComplex_Code') == 'B04.053.004');
											break;
										case 11: // 36:
											return (rec.get('UslugaComplex_Code') == 'B04.058.003');
											break;
										case 1: // 27:
											return (rec.get('UslugaComplex_Code') == 'B04.031.004');
											break;
									}
									return false;
								});
							}
							else if (getRegionNick() == 'ekb') {
								index = base_form.findField('UslugaComplex_id').getStore().findBy(function (rec) {
									switch (base_form.findField('OrpDispSpec_id').getValue()) {
										case 1: // Педиатрия
											return (rec.get('UslugaComplex_Code') == 'B04.031.002');
											break;
										case 4: // Детская хирургия
											return (rec.get('UslugaComplex_Code') == 'B04.010.002');
											break;
										case 7: // Стоматология детская
											return (rec.get('UslugaComplex_Code') == 'B04.064.002');
											break;
										case 10: // Детская урология-андрология
											return (rec.get('UslugaComplex_Code') == 'B04.053.004');
											break;
									}
									return false;
								});
							}

							if (index >= 0) {
								ucid = base_form.findField('UslugaComplex_id').getStore().getAt(index).get('UslugaComplex_id');
							} else {
								ucid = base_form.findField('UslugaComplex_id').getStore().getAt(0).get('UslugaComplex_id');
							}
						}
						base_form.findField('UslugaComplex_id').setValue(ucid);
						if (win.action != 'view') {
							base_form.findField('UslugaComplex_id').enable();
						}
						else {
							base_form.findField('UslugaComplex_id').disable();
						}
					}

					base_form.findField('UslugaComplex_id').fireEvent('change', base_form.findField('UslugaComplex_id'), base_form.findField('UslugaComplex_id').getValue());
				}
			});
		}
	},
	
	UpdateTumorField: function() 
	{
		var base_form = this.findById('EvnVizitDispOrp13EditForm').getForm();
		var OrpDispSpec_Code = '0';
		if(this.OrpDispSpec_Code == '0')
		{
			//EVDO13EWOrpDispSpecCombo
			var OrpDispSpecCombo = this.findById('EVDO13EWOrpDispSpecCombo');
			if(OrpDispSpecCombo.getFieldValue('OrpDispSpec_Code'))
				OrpDispSpec_Code = OrpDispSpecCombo.getFieldValue('OrpDispSpec_Code');
		}
		else
			OrpDispSpec_Code = this.OrpDispSpec_Code;
		if(this.allowEditTumor && OrpDispSpec_Code.inlist(['1']))
		{
			var diag_code = this.findById('EVDO13EWDiagCombo').getFieldValue('Diag_Code');
			if(diag_code)
			{
				if((String(diag_code).slice(0,3) >= 'C00' && String(diag_code).slice(0,5) <= 'C80.9') || String(diag_code).slice(0,3) == 'C97')
				{
					base_form.findField('TumorStage_id').setContainerVisible(true);
					base_form.findField('TumorStage_id').setAllowBlank(false);
				}
				else
				{
					base_form.findField('TumorStage_id').setContainerVisible(false);
					base_form.findField('TumorStage_id').setAllowBlank(true);
					base_form.findField('TumorStage_id').setValue(null);
				}
			}
			else
			{
				base_form.findField('TumorStage_id').setContainerVisible(false);
				base_form.findField('TumorStage_id').setAllowBlank(true);
				base_form.findField('TumorStage_id').setValue(null);
			}
		}
	},
	
    show: function() {
		sw.Promed.swEvnVizitDispOrp13EditWindow.superclass.show.apply(this, arguments);

		var current_window = this;

		current_window.restore();
		current_window.center();

        var form = current_window.findById('EvnVizitDispOrp13EditForm');
		var base_form = form.getForm();
		form.getForm().reset();
		current_window.EvnDiagDopDispGrid.removeAll({ clearAll: true });

       	current_window.callback = Ext.emptyFn;
       	current_window.onHide = Ext.emptyFn;
		current_window.ownerWindow = null;
		current_window.isVisibleDisDTPanel = false;
		current_window.loadFirstMedPersonal = true;

		current_window.toggleVisibleDisDTPanel('hide');

        if (!arguments[0] || !arguments[0].formParams || !arguments[0].ownerWindow || !arguments[0].DispClass_id)
        {
        	Ext.Msg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { current_window.hide(); } );
        	return false;
        }

		this.EvnPLDisp_id = null;
		if (arguments[0].EvnPLDisp_id)
		{
			this.EvnPLDisp_id = arguments[0].EvnPLDisp_id;
		}
		
		this.lastLpuSectionProfile_id = null;
		this.lastLpu_uid1 = null;
		this.lastSetDate1 = null;
		this.lastLpu_uid2 = null;
		this.lastSetDate2 = null;
		this.lastMedSpecOms_id = null;
		this.DispClass_id = arguments[0].DispClass_id;
		this.EvnPLDispOrp_setDate = arguments[0].EvnPLDispOrp_setDate || null;
		this.findById('EVDO13EWDiagCombo').filterDate = null;

		this.setLpuSectionAndMedStaffFactFilter();

		base_form.findField('DopDispAlien_id').lastQuery = '';
		base_form.findField('DopDispAlien_id').getStore().filterBy(function(rec) {
			return rec.get('DopDispAlien_Code').toString().inlist([ '0', '1' ]);
		});

		base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = (typeof this.EvnPLDispOrp_setDate == 'object' ? Ext.util.Format.date(this.EvnPLDispOrp_setDate, 'd.m.Y') : this.EvnPLDispOrp_setDate);
		base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = null;
		base_form.findField('UslugaComplex_id').getStore().baseParams.EvnPLDisp_id = this.EvnPLDisp_id;
		base_form.findField('UslugaComplex_id').getStore().baseParams.SurveyTypeLink_mid = null;
		if (getRegionNick() == 'perm') {
			base_form.findField('UslugaComplex_id').setUslugaCategoryList(['gost2011']);
		} else if (getRegionNick() == 'ekb') {
			base_form.findField('UslugaComplex_id').setUslugaCategoryList(['gost2011', 'tfoms']);
		} else {
			base_form.findField('UslugaComplex_id').setUslugaCategoryList(['gost2011', 'lpusection']);
		}
		base_form.findField('UslugaComplex_id').getStore().baseParams.DispClass_id = current_window.DispClass_id;
		base_form.findField('UslugaComplex_id').clearValue();
		base_form.findField('UslugaComplex_id').getStore().removeAll();
		this.lastUslugaComplexParams = null;

        if (arguments[0].action)
        {
        	current_window.action = arguments[0].action;
        }
		
        if (arguments[0].orpDispSpecAllowed)
        {
        	current_window.orpDispSpecAllowed = arguments[0].orpDispSpecAllowed;
        }
		
		if (arguments[0].dopDispInfoConsentData)
        {
        	current_window.dopDispInfoConsentData = arguments[0].dopDispInfoConsentData;
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
		
		this.EvnDirectionGrid.getGrid().getStore().removeAll();
		
		this.EvnDirectionGrid.setReadOnly('view' == this.action);
		
		current_window.findById('EVDO13EWPersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnVizitDispOrp_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EVDO13EWPersonInformationFrame', field);
			}
		});
		
  		var loadMask = new Ext.LoadMask(Ext.get('EvnVizitDispOrp13EditWindow'), { msg: LOAD_WAIT });
		loadMask.show();

        form.getForm().setValues(arguments[0].formParams);
        form.getForm().clearInvalid();
		if (arguments[0].formParams && arguments[0].formParams.EvnDiagDopDispGridData) {
			current_window.EvnDiagDopDispGrid.getGrid().getStore().loadData(Ext.util.JSON.decode(arguments[0].formParams.EvnDiagDopDispGridData));
		}
		
		this.sex_id = arguments[0].Sex_id;
		this.age = arguments[0].Person_Age;		
		
		this.Person_BirthDay = arguments[0].Person_Birthday;
		current_window.findById('EVDO13EWEvnVizitDispOrp_setDate').setValue(arguments[0].formParams['EvnVizitDispOrp_setDate']);
		this.setOrpDispSpecFilter();
		
		var med_personal_id = arguments[0].formParams.MedPersonal_id;
		var lpu_section_id = arguments[0].formParams.LpuSection_id;

		this.EvnDiagDopDispGrid.setActionDisabled('action_add', current_window.action == 'view');
		
		//https://redmine.swan.perm.ru/issues/118948
		this.OrpDispSpec_Code = '0';
		if(arguments[0].formParams.OrpDispSpec_Code)
			this.OrpDispSpec_Code = arguments[0].formParams.OrpDispSpec_Code;
		var TumorAllowDate = new Date(2017,1,1);
		this.allowEditTumor = false;
		/*if(getRegionNick() == 'ekb' && this.EvnPLDispOrp_setDate && this.EvnPLDispOrp_setDate >= TumorAllowDate)
			this.allowEditTumor = true;*/
		base_form.findField('TumorStage_id').setContainerVisible(false);
		base_form.findField('TumorStage_id').setAllowBlank(true);
		
		switch (current_window.action)
        {
            case 'add':
                current_window.setTitle(WND_POL_EVDDADD);
                current_window.enableEdit(true);
				base_form.findField('OrpDispSpec_id').fireEvent('change', base_form.findField('OrpDispSpec_id'), base_form.findField('OrpDispSpec_id').getValue());

				var sex_id = arguments[0].Sex_id;
				loadMask.hide();
				current_window.findById('EVDO13EWEvnVizitDispOrp_setDate').focus(false, 250);
				var diag_combo = this.findById('EVDO13EWDiagCombo');
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
				
				Ext.getCmp('EVDO13EWDopDispAlien_idCombo').setValue(1);
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
				base_form.findField('OrpDispSpec_id').fireEvent('change', base_form.findField('OrpDispSpec_id'), base_form.findField('OrpDispSpec_id').getValue());

				var DopDispAlien_id = base_form.findField('DopDispAlien_id').getValue();

				if (!Ext.isEmpty(DopDispAlien_id) && DopDispAlien_id == 2) {
					current_window.loadFirstMedPersonal = false;
				}

				var setDate = base_form.findField('EvnVizitDispOrp_setDate').getValue();
				var setTime = base_form.findField('EvnVizitDispOrp_setTime').getValue();
				var disDate = base_form.findField('EvnVizitDispOrp_disDate').getValue();
				var disTime = base_form.findField('EvnVizitDispOrp_disTime').getValue();

				if ((!Ext.isEmpty(disDate) || !Ext.isEmpty(disTime)) && (disDate-setDate != 0 || setTime != disTime)) {
					this.toggleVisibleDisDTPanel('show');
				}

				var diag_combo = this.findById('EVDO13EWDiagCombo');
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
				
				// устанавливаем врача
				var
					LpuSection_id = base_form.findField('LpuSection_id').getValue(),
					MedPersonal_id = base_form.findField('MedPersonal_id').getValue(),
					MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();

				if ( !Ext.isEmpty(LpuSection_id) && getRegionNick() == 'ufa' ) {
					base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), base_form.findField('LpuSection_id').getValue());
				}

				if ( !Ext.isEmpty(MedPersonal_id) || !Ext.isEmpty(MedStaffFact_id) ) {
					var index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
						return (Number(rec.get('MedStaffFact_id')) == Number(MedStaffFact_id));
					});

					if ( index == -1 ) {
						index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
							return (Number(rec.get('MedPersonal_id')) == Number(MedPersonal_id) && Number(rec.get('LpuSection_id')) == Number(LpuSection_id));
						});
					}

					if ( index == -1 ) {
						index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
							return (Number(rec.get('MedPersonal_id')) == Number(MedPersonal_id));
						});
					}

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
				current_window.findById('EVDO13EWEvnVizitDispOrp_setDate').fireEvent('change', current_window.findById('EVDO13EWEvnVizitDispOrp_setDate'), current_window.findById('EVDO13EWEvnVizitDispOrp_setDate').getValue());
				current_window.findById('EVDO13EWEvnVizitDispOrp_setDate').focus(false, 250);
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
				this.findById('EVDO13EWEvnVizitDispOrp_setDate').setMinValue(Date.parseDate('01.01.' + this.Year, 'd.m.Y'));
			}
			else
			{
				this.findById('EVDO13EWEvnVizitDispOrp_setDate').setMinValue(Date.parseDate('01.01.' + (Date.parseDate(getGlobalOptions().date, 'd.m.Y').getFullYear()-1), 'd.m.Y'));
			}
		}
		else
		{
			var year = this.findById('EVDO13EWEvnVizitDispOrp_setDate').getValue().getFullYear();
			if (year)
			{
				this.findById('EVDO13EWEvnVizitDispOrp_setDate').setMinValue(Date.parseDate('01.01.' + year, 'd.m.Y'));
			}
		}
	},
	setDisDT: function() {
		if ( this.isVisibleDisDTPanel ) {
			return false;
		}

		var base_form = this.findById('EvnVizitDispOrp13EditForm').getForm();

		base_form.findField('EvnVizitDispOrp_disDate').setValue(base_form.findField('EvnVizitDispOrp_setDate').getValue());
		base_form.findField('EvnVizitDispOrp_disTime').setValue(base_form.findField('EvnVizitDispOrp_setTime').getValue());
	},
	toggleVisibleDisDTPanel: function(action)
	{
		var base_form = this.findById('EvnVizitDispOrp13EditForm').getForm();

		if (action == 'show') {
			this.isVisibleDisDTPanel = false;
		} else if (action == 'hide') {
			this.isVisibleDisDTPanel = true;
		}

		if (this.isVisibleDisDTPanel) {
			this.findById('EVDO13EW_EvnVizitDisDTPanel').hide();
			this.findById('EVDO13EW_ToggleVisibleDisDTBtn').setText(lang['utochnit_period_vyipolneniya']);
			base_form.findField('EvnVizitDispOrp_disDate').setAllowBlank(true);
			base_form.findField('EvnVizitDispOrp_disTime').setAllowBlank(true);
			base_form.findField('EvnVizitDispOrp_disDate').setValue(null);
			base_form.findField('EvnVizitDispOrp_disTime').setValue(null);
			base_form.findField('EvnVizitDispOrp_disDate').setMaxValue(undefined);
			this.isVisibleDisDTPanel = false;
		} else {
			this.findById('EVDO13EW_EvnVizitDisDTPanel').show();
			this.findById('EVDO13EW_ToggleVisibleDisDTBtn').setText(lang['skryit_polya']);
			base_form.findField('EvnVizitDispOrp_disDate').setAllowBlank(false);
			base_form.findField('EvnVizitDispOrp_disTime').setAllowBlank(false);
			base_form.findField('EvnVizitDispOrp_disDate').setMaxValue(getGlobalOptions().date);
			this.isVisibleDisDTPanel = true;
		}
	},
	addDirectionIssled: function() {
		var win = this;
		var base_form = win.findById('EvnVizitDispOrp13EditForm').getForm();

		// если не сохранено, то нужно сначала сохранить
		if (Ext.isEmpty(base_form.findField('EvnVizitDispOrp_id').getValue())) {
			this.doSave(function() {
				win.addDirectionIssled();
			}, true);
			return false;
		}

		var EvnDirection_pid = base_form.findField('EvnVizitDispOrp_id').getValue();

		var personinfoframe = win.findById('EVDO13EWPersonInformationFrame');
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
		var base_form = win.findById('EvnVizitDispOrp13EditForm').getForm();

		// если не сохранено, то нужно сначала сохранить
		if (Ext.isEmpty(base_form.findField('EvnVizitDispOrp_id').getValue())) {
			this.doSave(function() {
				win.addDirectionConsult();
			}, true);
			return false;
		}

		var EvnDirection_pid = base_form.findField('EvnVizitDispOrp_id').getValue();

		var personinfoframe = win.findById('EVDO13EWPersonInformationFrame');
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
		var base_form = win.findById('EvnVizitDispOrp13EditForm').getForm();

		// если не сохранено, то нужно сначала сохранить
		if (Ext.isEmpty(base_form.findField('EvnVizitDispOrp_id').getValue())) {
			this.doSave(function() {
				win.addDirectionPolka();
			}, true);
			return false;
		}

		var EvnDirection_pid = base_form.findField('EvnVizitDispOrp_id').getValue();

		var personinfoframe = win.findById('EVDO13EWPersonInformationFrame');
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