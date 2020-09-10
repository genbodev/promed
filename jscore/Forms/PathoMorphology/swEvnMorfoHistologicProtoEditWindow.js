/**
* swEvnMorfoHistologicProtoEditWindow - протокол патоморфогистологического исследования
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      PathoMorphology
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      10.02.2011
* @comment      Префикс для id компонентов EMHPEF (EvnMorfoHistologicProtoEditForm)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnMorfoHistologicProtoEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnMorfoHistologicProtoEditWindow',
	objectSrc: '/jscore/Forms/PathoMorphology/swEvnMorfoHistologicProtoEditWindow.js',

	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	deleteGridRecord: function(object) {
		if ( this.action == 'view' ) {
			return false;
		}

		if ( typeof object != 'string' || !(object.inlist([ 'EvnMorfoHistologicDiagDiscrepancy', 'EvnDiagPS', 'EvnMorfoHistologicMember' ])) ) {
			return false;
		}

		var grid = this.findById('EMHPEF_' + object + 'Grid').getGrid();

		if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(object + '_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		switch ( Number(record.get('RecordStatus_Code')) ) {
			case 0:
				grid.getStore().remove(record);
			break;

			case 1:
			case 2:
				record.set('RecordStatus_Code', 3);
				record.commit();

				grid.getStore().filterBy(function(rec) {
					if ( Number(rec.get('RecordStatus_Code')) == 3 ) {
						return false;
					}
					else {
						return true;
					}
				});
			break;
		}

		if ( grid.getStore().getCount() == 0 ) {
			LoadEmptyRow(grid);
		}

		grid.getView().focusRow(0);
		grid.getSelectionModel().selectFirstRow();
	},
	doSave: function(options) {
		// options @Object
		// options.openChildWindow @Function Открыть дочернее окно после сохранения
		// options.print @Boolean Вызывать печать протокола патоморфогистологического исследования, если true

		if ( this.action == 'view' ) {
			return false;
		}

		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';
		this.onCancelActionFlag = false;

		var form = this.FormPanel;
		var base_form = form.getForm();


		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.onCancelActionFlag = true;
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var index;
		var lpu_section_id = base_form.findField('LpuSection_id').getValue();
		var lpu_section_name = '';
		var med_personal_fio = '';
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
		var params = new Object();

		params.EvnMorfoHistologicProto_Ser = base_form.findField('EvnMorfoHistologicProto_Ser').getValue();

		var corpseRecieptDate = base_form.findField('MorfoHistologicCorpse_recieptDate').getValue(),
			deathDate = base_form.findField('EvnMorfoHistologicProto_deathDate').getValue(),
			autopsyDate = base_form.findField('EvnMorfoHistologicProto_autopsyDate').getValue();
		
		if (corpseRecieptDate) {
			if (deathDate && corpseRecieptDate < deathDate) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], lang['data_postupleniya_tela_ne_mozhet_byit_ranshe_chem_data_smerti']);
				return false;
			}
			else if (autopsyDate && corpseRecieptDate > autopsyDate){
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], lang['data_postupleniya_tela_ne_mozhet_byit_pozhe_chem_data_vskryitiya']);
				return false;
			}
		}
		
		if (deathDate && autopsyDate && autopsyDate < deathDate) {
			this.formStatus = 'edit';
			sw.swMsg.alert(lang['oshibka'], lang['data_vskryitiya_tela_ne_mozhet_byit_ranshe_chem_data_smerti']);
			return false;
		}

		index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
			if ( rec.get('LpuSection_id') == lpu_section_id ) {
				return true;
			}
			else {
				return false;
			}
		});

		if ( index >= 0 ) {
			lpu_section_name = base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_Name');
		}

		index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
			if ( rec.get('MedStaffFact_id') == med_staff_fact_id ) {
				return true;
			}
			else {
				return false;
			}
		});

		if ( index >= 0 ) {
			med_personal_fio = base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedPersonal_Fio');
			base_form.findField('MedPersonal_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedPersonal_id'));
		}

		// Собираем данные из гридов

		var evn_morfo_histologic_diag_discrepancy_grid = this.findById('EMHPEF_EvnMorfoHistologicDiagDiscrepancyGrid').getGrid();
		var evn_morfo_histologic_member_grid = this.findById('EMHPEF_EvnMorfoHistologicMemberGrid').getGrid();

		evn_morfo_histologic_diag_discrepancy_grid.getStore().clearFilter();
		evn_morfo_histologic_member_grid.getStore().clearFilter();

		if ( evn_morfo_histologic_diag_discrepancy_grid.getStore().getCount() > 0 ) {
			var evn_morfo_histologic_diag_discrepancy_data = getStoreRecords(evn_morfo_histologic_diag_discrepancy_grid.getStore(), {
				exceptionFields: [
					'DiagClinicalErrType_Name',
					'DiagReasonDiscrepancy_Name'
				]
			});

			params.evnMorfoHistologicDiagDiscrepancyData = Ext.util.JSON.encode(evn_morfo_histologic_diag_discrepancy_data);

			evn_morfo_histologic_diag_discrepancy_grid.getStore().filterBy(function(rec) {
				if ( Number(rec.get('RecordStatus_Code')) == 3 ) {
					return false;
				}
				else {
					return true;
				}
			});
		}

		if ( evn_morfo_histologic_member_grid.getStore().getCount() > 0 ) {
			var evn_morfo_histologic_member_data = getStoreRecords(evn_morfo_histologic_member_grid.getStore(), {
				exceptionFields: [
					'Lpu_id',
					'Lpu_Name',
					'MedPersonal_Code',
					'MedPersonal_Fio'
				]
			});

			params.evnMorfoHistologicMemberData = Ext.util.JSON.encode(evn_morfo_histologic_member_data);

			evn_morfo_histologic_member_grid.getStore().filterBy(function(rec) {
				if ( Number(rec.get('RecordStatus_Code')) == 3 ) {
					return false;
				}
				else {
					return true;
				}
			});
		}

		if ( base_form.findField('MedPersonal_aid').disabled ) {
			params.MedPersonal_aid = base_form.findField('MedPersonal_aid').getValue();
		}

		var death_svid_value = base_form.findField('DeathSvid_id').getValue();
		var is_pnt_death_svid = base_form.findField('DeathSvid_id').getStore().findBy(function(rec) {
			if ( rec.get('DeathSvid_id') == death_svid_value && rec.get('Type_MSoS') == "PntDeathSvid" ) {
				return true;
			}
			else {
				return false;
			}
		});
		// если выбрано свидетельство о перинатальной смерти, заполняем нужное поле
		if( is_pnt_death_svid > -1 ) {
			base_form.findField('PntDeathSvid_id').setValue(death_svid_value);
			base_form.findField('DeathSvid_id').setValue('');
		}else{
			base_form.findField('PntDeathSvid_id').setValue('');
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение протокола..." });
		loadMask.show();
		
		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				this.onCancelActionFlag = true;
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.EvnMorfoHistologicProto_id > 0 ) {
						base_form.findField('EvnMorfoHistologicProto_id').setValue(action.result.EvnMorfoHistologicProto_id);

						if ( options && typeof options.openChildWindow == 'function' && this.action == 'add' ) {
							this.onCancelActionFlag = true;
							options.openChildWindow();
						}
						else {
							var data = new Object();

							data.evnHistologicProtoData = [{
								'EvnMorfoHistologicProto_id': base_form.findField('EvnMorfoHistologicProto_id').getValue(),
								'accessType': 'edit',
								'EvnDirectionMorfoHistologic_IsBad': 0,
								'Person_id': base_form.findField('Person_id').getValue(),
								'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
								'Server_id': base_form.findField('Server_id').getValue(),
								'EvnMorfoHistologicProto_Ser': base_form.findField('EvnMorfoHistologicProto_Ser').getValue(),
								'EvnMorfoHistologicProto_Num': base_form.findField('EvnMorfoHistologicProto_Num').getValue(),
								'EvnMorfoHistologicProto_setDate': base_form.findField('EvnMorfoHistologicProto_setDate').getValue(),
								'Lpu_Name': '',
								'LpuSection_Name': lpu_section_name,
								// 'EvnDirectionMorfoHistologic_NumCard': base_form.findField('EvnMorfoHistologicProto_NumCard').getValue(),
								'Person_Surname': this.PersonInfo.getFieldValue('Person_Surname'),
								'Person_Firname': this.PersonInfo.getFieldValue('Person_Firname'),
								'Person_Secname': this.PersonInfo.getFieldValue('Person_Secname'),
								'Person_Birthday': this.PersonInfo.getFieldValue('Person_Birthday'),
								'MedPersonal_Fio': med_personal_fio
							}];

							this.callback(data);

							if ( options && options.print ) {
								this.buttons[1].focus();
								window.open('/?c=EvnMorfoHistologicProto&m=printEvnMorfoHistologicProto&EvnMorfoHistologicProto_id=' + base_form.findField('EvnMorfoHistologicProto_id').getValue(), '_blank');
							}
							else {
								this.hide();
							}
						}
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
						}
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
			'DeathSvid_id',
			'PntDeathSvid_id',
			'Diag_did',
			'Diag_sid',
			'Diag_vid',
			'Diag_vid_Descr',
			'Diag_wid',
			'Diag_wid_Descr',
			'Diag_xid',
			'Diag_xid_Descr',
			'Diag_yid',
			'Diag_yid_Descr',
			'Diag_zid',
			'Diag_zid_Descr',
			'EvnMorfoHistologicProto_autopsyDate',
			'EvnDirectionMorfoHistologic_SerNum',
			'EvnMorfoHistologicProto_BitCount',
			'EvnMorfoHistologicProto_BlockCount',
			'EvnMorfoHistologicProto_BrainWeight',
			'EvnMorfoHistologicProto_deathDate',
			'EvnMorfoHistologicProto_deathTime',
			// 'EvnMorfoHistologicProto_DeliveryDays',
			// 'EvnMorfoHistologicProto_DeliveryHours',
			'EvnMorfoHistologicProto_DiagDescr',
			'EvnMorfoHistologicProto_DiagNameDirect',
			'EvnMorfoHistologicProto_DiagNameSupply',
			'EvnMorfoHistologicProto_DiagPathology',
			'EvnMorfoHistologicProto_DiagSetDate',
			'EvnMorfoHistologicProto_Epicrisis',
			'EvnMorfoHistologicProto_HeartWeight',
			'EvnMorfoHistologicProto_KidneyLeftWeight',
			'EvnMorfoHistologicProto_KidneyRightWeight',
			// 'EvnMorfoHistologicProto_KoikoDays',
			// 'EvnMorfoHistologicProto_KoikoHours',
			'EvnMorfoHistologicProto_LiverWeight',
			'EvnMorfoHistologicProto_LungsWeight',
			'EvnMorfoHistologicProto_Num',
			// 'EvnMorfoHistologicProto_NumCard',
			// 'EvnMorfoHistologicProto_NumDeath',
			'EvnMorfoHistologicProto_MethodDescr',
			'EvnMorfoHistologicProto_ProtocolDescr',
			'EvnMorfoHistologicProto_ResultLabStudy',
			'EvnMorfoHistologicProto_setDate',
			'EvnMorfoHistologicProto_SpleenWeight',
			'EvnPS_id',
			'LpuSection_id',
			'MedPersonal_aid',
			'MedPersonal_zid',
			'MedStaffFact_id',
			'PathologicCategoryType_id'
		);
		var i = 0;

		if ( getRegionNick() == 'kz' ) {
			form_fields.push('EvnMorfoHistologicProto_Ser');
		}

		for ( i = 0; i < form_fields.length; i++ ) {
			if ( enable ) {
				base_form.findField(form_fields[i]).enable();
			}
			else {
				base_form.findField(form_fields[i]).disable();
			}
		}

		if ( enable ) {
			this.buttons[0].show();
		}
		else {
			this.buttons[0].hide();
		}
	},
	formStatus: 'edit',
	height: 550,
	id: 'EvnMorfoHistologicProtoEditWindow',
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'EvnMorfoHistologicProtoEditForm',
			labelAlign: 'right',
			labelWidth: 200,
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{ name: 'accessType' },
				{ name: 'DeathSvid_id' },
				{ name: 'PntDeathSvid_id' },
				{ name: 'Diag_did' },
				{ name: 'Diag_sid' },
				{ name: 'Diag_vid' },
				{ name: 'Diag_vid_Descr' },
				{ name: 'Diag_wid' },
				{ name: 'Diag_wid_Descr' },
				{ name: 'Diag_xid' },
				{ name: 'Diag_xid_Descr' },
				{ name: 'Diag_yid' },
				{ name: 'Diag_yid_Descr' },
				{ name: 'Diag_zid' },
				{ name: 'Diag_zid_Descr' },
				{ name: 'EvnDirectionMorfoHistologic_id' },
				{ name: 'EvnDirectionMorfoHistologic_SerNum' },
				{ name: 'EvnMorfoHistologicProto_autopsyDate' },
				{ name: 'MorfoHistologicCorpse_recieptDate' },
				{ name: 'EvnMorfoHistologicProto_BitCount' },
				{ name: 'EvnMorfoHistologicProto_BlockCount' },
				{ name: 'EvnMorfoHistologicProto_BrainWeight' },
				{ name: 'EvnMorfoHistologicProto_deathDate' },
				{ name: 'EvnMorfoHistologicProto_deathTime' },
				{ name: 'EvnMorfoHistologicProto_DeliveryDays' },
				{ name: 'EvnMorfoHistologicProto_DeliveryHours' },
				{ name: 'EvnMorfoHistologicProto_DiagDescr' },
				{ name: 'EvnMorfoHistologicProto_DiagNameDirect' },
				{ name: 'EvnMorfoHistologicProto_DiagNameSupply' },
				{ name: 'EvnMorfoHistologicProto_DiagPathology' },
				{ name: 'EvnMorfoHistologicProto_DiagSetDate' },
				{ name: 'EvnMorfoHistologicProto_Epicrisis' },
				{ name: 'EvnMorfoHistologicProto_HeartWeight' },
				{ name: 'EvnMorfoHistologicProto_id' },
				{ name: 'EvnMorfoHistologicProto_KidneyLeftWeight' },
				{ name: 'EvnMorfoHistologicProto_KidneyRightWeight' },
				{ name: 'EvnMorfoHistologicProto_KoikoDays' },
				{ name: 'EvnMorfoHistologicProto_KoikoHours' },
				{ name: 'EvnMorfoHistologicProto_LiverWeight' },
				{ name: 'EvnMorfoHistologicProto_LungsWeight' },
				{ name: 'EvnMorfoHistologicProto_MethodDescr' },
				{ name: 'EvnMorfoHistologicProto_Num' },
				// { name: 'EvnMorfoHistologicProto_NumCard' },
				// { name: 'EvnMorfoHistologicProto_NumDeath' },
				{ name: 'EvnMorfoHistologicProto_ProtocolDescr' },
				{ name: 'EvnMorfoHistologicProto_ResultLabStudy' },
				{ name: 'EvnMorfoHistologicProto_Ser' },
				{ name: 'EvnMorfoHistologicProto_setDate' },
				{ name: 'EvnMorfoHistologicProto_SpleenWeight' },
				{ name: 'EvnPS_id' },
				{ name: 'LpuSection_id' },
				{ name: 'MedPersonal_aid' },
				{ name: 'MedPersonal_id' },
				{ name: 'MedPersonal_zid' },
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'pmUser_Name' },
				{ name: 'Server_id' },
				{ name: 'PathologicCategoryType_id' }
			]),
			region: 'center',
			url: '/?c=EvnMorfoHistologicProto&m=saveEvnMorfoHistologicProto',

			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'EvnMorfoHistologicProto_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'PntDeathSvid_id',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'EvnDirectionMorfoHistologic_id',
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'MedPersonal_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				value: -1,
				xtype: 'hidden'
			}, {
				border: false,
				hidden: true,
				id: 'EMHPEF_Caption',
				layout: 'form',
				xtype: 'panel',

				items: [{
					fieldLabel: lang['annulirovano'],
					name: 'pmUser_Name',
					readOnly: true,
					style: 'color: #ff8870',
					width: 500,
					xtype: 'textfield'
				}]
			}, {
				allowBlank: false,
				enableKeyEvents: true,
				fieldLabel: lang['napravlenie'],
				listeners: {
					'keydown': function(inp, e) {
						switch ( e.getKey() ) {
							case Ext.EventObject.F4:
								if ( this.action == 'view' || inp.disabled ) {
									return false;
								}

								e.stopEvent();
								this.openEvnDirectionMorfoHistologicListWindow();
							break;

							case Ext.EventObject.TAB:
								if ( e.shiftKey == true ) {
									e.stopEvent();
									this.buttons[this.buttons.length - 1].focus();
								}
							break;
						}
					}.createDelegate(this)
				},
				name: 'EvnDirectionMorfoHistologic_SerNum',
				onTriggerClick: function() {
					this.openEvnDirectionMorfoHistologicListWindow();
				}.createDelegate(this),
				readOnly: true,
				tabIndex: TABINDEX_EMHPEF + 1,
				triggerClass: 'x-form-search-trigger',
				width: 300,
				xtype: 'trigger'
			}, {
				allowBlank: (getRegionNick() == 'kz'),
				allowDecimals: false,
				allowNegative: false,
				disabled: true,
				fieldLabel: lang['seriya_issledovaniya'],
				name: 'EvnMorfoHistologicProto_Ser',
				tabIndex: TABINDEX_EMHPEF + 2,
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				enableKeyEvents: true,
				fieldLabel: lang['nomer_issledovaniya'],
				name: 'EvnMorfoHistologicProto_Num',
				tabIndex: TABINDEX_EMHPEF + 3,
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				fieldLabel: lang['data'],
				format: 'd.m.Y',
				listeners: {
					'change': function(field, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();

						var lpu_section_id = base_form.findField('LpuSection_id').getValue();
						var med_personal_aid = base_form.findField('MedPersonal_aid').getValue();
						var med_personal_zid = base_form.findField('MedPersonal_zid').getValue();
						var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();

						base_form.findField('LpuSection_id').clearValue();
						base_form.findField('LpuSection_id').disable();
						base_form.findField('MedPersonal_aid').clearValue();
						base_form.findField('MedPersonal_aid').disable();
						base_form.findField('MedPersonal_zid').clearValue();
						base_form.findField('MedPersonal_zid').disable();
						base_form.findField('MedStaffFact_id').clearValue();
						base_form.findField('MedStaffFact_id').disable();

						if ( !newValue ) {
							return false;
						}

						var lpu_section_filter_params = {
							isHisto: true,
							onDate: Ext.util.Format.date(newValue, 'd.m.Y'),
							regionCode: getGlobalOptions().region.number
						};

						var medstafffact_filter_params = {
							isHisto: true,
							onDate: Ext.util.Format.date(newValue, 'd.m.Y'),
							regionCode: getGlobalOptions().region.number
						};

						if ( this.action != 'view' ) {
							base_form.findField('LpuSection_id').enable();
							base_form.findField('MedPersonal_aid').enable();
							base_form.findField('MedPersonal_zid').enable();
							base_form.findField('MedStaffFact_id').enable();
						}

						base_form.findField('LpuSection_id').getStore().removeAll();
						base_form.findField('MedPersonal_aid').getStore().removeAll();
						base_form.findField('MedPersonal_zid').getStore().removeAll();
						base_form.findField('MedStaffFact_id').getStore().removeAll();

						setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);
						setLpuSectionGlobalStoreFilter(lpu_section_filter_params);

						base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
						base_form.findField('MedPersonal_zid').getStore().loadData(getMedPersonalListFromGlobal());
						base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

						if ( this.action == 'add' ) {
							// фильтр или на конкретное место работы или на список мест работы
							if ( this.UserMedStaffFact_id ) {
								medstafffact_filter_params.id = this.UserMedStaffFact_id;
							}
							else if ( typeof this.UserMedStaffFactList == 'object' && this.UserMedStaffFactList.length > 0 ) {
								medstafffact_filter_params.ids = this.UserMedStaffFactList;
							}
						}

						// загружаем локальный список мест работы
						setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);

						base_form.findField('MedPersonal_aid').getStore().loadData(getMedPersonalListFromGlobal());

						if ( base_form.findField('LpuSection_id').getStore().getById(lpu_section_id) ) {
							base_form.findField('LpuSection_id').setValue(lpu_section_id);
						}

						if ( base_form.findField('MedPersonal_aid').getStore().getById(med_personal_aid) ) {
							base_form.findField('MedPersonal_aid').setValue(med_personal_aid);
						}

						if ( base_form.findField('MedPersonal_zid').getStore().getById(med_personal_zid) ) {
							base_form.findField('MedPersonal_zid').setValue(med_personal_zid);
						}

						if ( base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
							base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
						}

						// Если задано место работы или список мест работы, то не даем редактировать поле "Врач"
						if ( this.action.toString().inlist([ 'add', 'edit' ]) && (this.UserMedStaffFact_id || (typeof this.UserMedStaffFactList == 'object' && this.UserMedStaffFactList.length > 0)) ) {
							base_form.findField('MedPersonal_aid').disable();

							if ( this.action == 'add' && base_form.findField('MedPersonal_aid').getStore().getCount() > 0 ) {
								base_form.findField('MedPersonal_aid').setValue(base_form.findField('MedPersonal_aid').getStore().getAt(0).get('MedPersonal_id'));
							}
						}
						base_form.findField('Diag_did').setFilterByDate(newValue);
						base_form.findField('Diag_sid').setFilterByDate(newValue);
					}.createDelegate(this)
				},
				name: 'EvnMorfoHistologicProto_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				tabIndex: TABINDEX_EMHPEF + 4,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: lang['otdelenie'],
				hiddenName: 'LpuSection_id',
				id: 'EMHPEF_LpuSectionCombo',
				linkedElements: [
					'EMHPEF_MedStaffactCombo'
				],
				listWidth: 650,
				tabIndex: TABINDEX_EMHPEF + 5,
				width: 500,
				xtype: 'swlpusectionglobalcombo'
			}, {
				allowBlank: true,
				displayField: 'EvnPS_NumCard',
				fieldLabel: lang['karta_patsienta'],
				hiddenName: 'EvnPS_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();

						var index = combo.getStore().findBy(function(rec) {
							if ( rec.get('EvnPS_id') == newValue ) {
								return true;
							}
							else {
								return false;
							}
						});

						var record = combo.getStore().getAt(index);

						if ( record ) {
							base_form.findField('EvnMorfoHistologicProto_deathDate').setValue(record.get('EvnPS_deathDate'));
							base_form.findField('EvnMorfoHistologicProto_deathTime').setValue(record.get('EvnPS_deathTime'));
						}
					}.createDelegate(this)
				},
				store: new Ext.data.Store({
					autoLoad: false,
					reader: new Ext.data.JsonReader({
						id: 'EvnPS_id'
					}, [
						{ name: 'EvnPS_id', mapping: 'EvnPS_id' },
						{ name: 'EvnPS_NumCard', mapping: 'EvnPS_NumCard' },
						{ name: 'PrehospType_id', mapping: 'PrehospType_id' },
						{ name: 'EvnPS_deathDate', mapping: 'EvnPS_deathDate' },
						{ name: 'EvnPS_deathTime', mapping: 'EvnPS_deathTime' },
						{ name: 'EvnPS_setDate', mapping: 'EvnPS_setDate' },
						{ name: 'EvnPS_disDate', mapping: 'EvnPS_disDate' }
					]),
					sortInfo: {
						field: 'EvnPS_NumCard'
					},
					url: '/?c=EvnPS&m=loadEvnPSList'
				}),
				tabIndex: TABINDEX_EMHPEF + 6,
				valueField: 'EvnPS_id',
				width: 200,
				xtype: 'swbaselocalcombo'
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel: lang['data_smerti'],
						format: 'd.m.Y',
						listeners: {
							'change': function(field, newValue, oldValue) {
								// blockedDateAfterPersonDeath('personpanel', this.PersonInfo, field, newValue, oldValue);
							}.createDelegate(this)
						},
						name: 'EvnMorfoHistologicProto_deathDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						selectOnFocus: true,
						tabIndex: TABINDEX_EMHPEF + 7,
						width: 100,
						xtype: 'swdatefield'
					}]
				}, {
					border: false,
					labelWidth: 50,
					layout: 'form',
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
						name: 'EvnMorfoHistologicProto_deathTime',
						onTriggerClick: function() {
							var base_form = this.FormPanel.getForm();
							var time_field = base_form.findField('EvnMorfoHistologicProto_deathTime');

							if ( time_field.disabled ) {
								return false;
							}

							setCurrentDateTime({
								dateField: base_form.findField('EvnMorfoHistologicProto_deathDate'),
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
						tabIndex: TABINDEX_EMHPEF + 8,
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}]
				}]
			}, {
				allowBlank: false,
				fieldLabel: lang['data_vskryitiya'],
				format: 'd.m.Y',
				listeners: {
					'change': function(field, newValue, oldValue) {
						// blockedDateAfterPersonDeath('personpanel', this.PersonInfo, field, newValue, oldValue);
					}.createDelegate(this)
				},
				name: 'EvnMorfoHistologicProto_autopsyDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				tabIndex: TABINDEX_EMHPEF + 9,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: lang['data_postupleniya_tela'],
				format: 'd.m.Y',
				name: 'MorfoHistologicCorpse_recieptDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				disabled: true,
				width: 100,
				xtype: 'swdatefield'
			},{
				allowBlank: false,
				enableKeyEvents: true,
				hidden: getRegionNick() == 'kz',
				hideLabel: getRegionNick() == 'kz',
				fieldLabel: lang['slojnost'],
				hiddenName: 'PathologicCategoryType_id',
				tabIndex: TABINDEX_EMHPEF + 10,
				width: 200,
				xtype: 'swpathologiccategorytypecombo'
			}, {
				allowBlank: false,
				fieldLabel: (getRegionNick() == 'msk' ? langs('med_rabotnik_napravivshiy_telo') : langs('lechaschiy_vrach')),
				hiddenName: 'MedStaffFact_id',
				id: 'EMHPEF_MedStaffactCombo',
				listWidth: 650,
				parentElementId: 'EMHPEF_LpuSectionCombo',
				tabIndex: TABINDEX_EMHPEF + 11,
				width: 500,
				xtype: 'swmedstafffactglobalcombo'
			},
			new sw.Promed.Panel({
				border: true,
				collapsible: true,
				height: 200,
				id: 'EMHPEF_EvnMorfoHistologicMemberPanel',
				isLoaded: false,
				layout: 'border',
				listeners: {
					'expand': function(panel) {
						if ( panel.isLoaded === false ) {
							panel.isLoaded = true;
							panel.findById('EMHPEF_EvnMorfoHistologicMemberGrid').getGrid().getStore().load({
								params: {
									EvnMorfoHistologicProto_id: this.FormPanel.getForm().findField('EvnMorfoHistologicProto_id').getValue()
								}
							});
						}

						panel.doLayout();
					}.createDelegate(this)
				},
				style: 'margin-bottom: 0.5em;',
				title: lang['prisutstvovali_na_vskryitii'],

				items: [ new sw.Promed.ViewFrame({
					actions: [
						{ name: 'action_add', handler: function() { this.openEvnMorfoHistologicMemberEditWindow('add'); }.createDelegate(this) },
						{ name: 'action_edit', handler: function() { this.openEvnMorfoHistologicMemberEditWindow('edit'); }.createDelegate(this) },
						{ name: 'action_view', handler: function() { this.openEvnMorfoHistologicMemberEditWindow('view'); }.createDelegate(this) },
						{ name: 'action_delete', handler: function() { this.deleteGridRecord('EvnMorfoHistologicMember'); }.createDelegate(this) },
						{ name: 'action_refresh', disabled: true },
						{ name: 'action_print', disabled: true }
					],
					autoLoadData: false,
					border: false,
					dataUrl: '/?c=EvnMorfoHistologicProto&m=loadEvnMorfoHistologicMemberGrid',
					height: 150,
					id: 'EMHPEF_EvnMorfoHistologicMemberGrid',
					onDblClick: function() {
						if ( !this.ViewActions.action_edit.isDisabled() ) {
							this.ViewActions.action_edit.execute();
						}
					},
					onEnter: function() {
						if ( !this.ViewActions.action_edit.isDisabled() ) {
							this.ViewActions.action_edit.execute();
						}
					},
					onLoadData: function() {
						//
					},
					onRowSelect: function(sm, index, record) {
						//
					},
					paging: false,
					region: 'center',
					stringfields: [
						{ name: 'EvnMorfoHistologicMember_id', type: 'int', header: 'ID', key: true },
						{ name: 'Lpu_id', type: 'int', hidden: true },
						{ name: 'MedStaffFact_id', type: 'int', hidden: true },
						{ name: 'RecordStatus_Code', type: 'int', hidden: true },
						{ name: 'MedPersonal_Code', type: 'string', header: lang['kod_vracha'], width: 70 },
						{ name: 'MedPersonal_Fio', type: 'string', header: lang['fio_vracha'], id: 'autoexpand' },
						{ name: 'Lpu_Name', type: 'string', header: lang['lpu'], width: 250 }
					]
				})]
			}), {
				allowBlank: false,
				fieldLabel: lang['diagnoz_napravivshego_uchrejdeniya'],
				hiddenName: 'Diag_did',
				listWidth: 600,
				tabIndex: TABINDEX_EMHPEF + 12,
				width: 500,
				xtype: 'swdiagcombo'
			}, {
				allowBlank: true,
				fieldLabel: '',
				height: 50,
				labelSeparator: '',
				name: 'EvnMorfoHistologicProto_DiagNameDirect',
				tabIndex: TABINDEX_EMHPEF + 13,
				width: 500,
				xtype: 'textarea'
			}, {
				allowBlank: false,
				fieldLabel: lang['diagnoz_pri_postuplenii'],
				hiddenName: 'Diag_sid',
				listWidth: 600,
				tabIndex: TABINDEX_EMHPEF + 14,
				width: 500,
				xtype: 'swdiagcombo'
			}, {
				allowBlank: true,
				fieldLabel: '',
				height: 50,
				labelSeparator: '',
				name: 'EvnMorfoHistologicProto_DiagNameSupply',
				tabIndex: TABINDEX_EMHPEF + 15,
				width: 500,
				xtype: 'textarea'
			},
			new sw.Promed.Panel({
				border: true,
				collapsible: true,
				height: 200,
				id: 'EMHPEF_EvnDiagPSPanel',
				isLoaded: false,
				layout: 'border',
				listeners: {
					'expand': function(panel) {
						panel.doLayout();
					}.createDelegate(this)
				},
				style: 'margin-bottom: 0.5em;',
				title: lang['klinicheskie_diagnozyi_v_statsionare_i_datyi_ih_ustanovleniya'],

				items: [ new sw.Promed.ViewFrame({
					actions: [
						{ name: 'action_add', hidden: true, handler: function() { this.openEvnDiagPSEditWindow('add'); }.createDelegate(this) },
						{ name: 'action_edit', handler: function() { this.openEvnDiagPSEditWindow('edit'); }.createDelegate(this) },
						{ name: 'action_view', handler: function() { this.openEvnDiagPSEditWindow('view'); }.createDelegate(this) },
						{ name: 'action_delete', handler: function() { this.deleteGridRecord('EvnDiagPS'); }.createDelegate(this) },
						{ name: 'action_refresh', disabled: true },
						{ name: 'action_print', disabled: true }
					],
					autoLoadData: false,
					border: false,
					dataUrl: '/?c=EvnMorfoHistologicProto&m=loadEvnDiagPSGrid',
					height: 150,
					id: 'EMHPEF_EvnDiagPSGrid',
					onDblClick: function() {
						if ( !this.ViewActions.action_edit.isDisabled() ) {
							this.ViewActions.action_edit.execute();
						}
					},
					onEnter: function() {
						if ( !this.ViewActions.action_edit.isDisabled() ) {
							this.ViewActions.action_edit.execute();
						}
					},
					onLoadData: function() {
						//
					},
					onRowSelect: function(sm, index, record) {
						//
					},
					paging: false,
					region: 'center',
					stringfields: [
						{ name: 'EvnDiagPS_id', type: 'int', header: 'ID', key: true },
						{ name: 'Lpu_id', type: 'int', hidden: true },
						{ name: 'Diag_id', type: 'int', hidden: true },
						{ name: 'MedPersonal_id', type: 'int', hidden: true },
						{ name: 'EvnDiagPS_setDate', type: 'date', header: lang['data_ustanovleniya'] },
						{ name: 'Diag_Code', type: 'string', header: lang['kod'], width: 70 },
						{ name: 'Diag_Name', type: 'string', header: lang['diagnoz'], id: 'autoexpand' },
						{ name: 'MedPersonal_Fio', type: 'string', header: lang['fio_vracha'], width: 200 }
					]
				})]
			}), {
				allowBlank: false,
				fieldLabel: lang['data_ustanovleniya'],
				format: 'd.m.Y',
				listeners: {
					'change': function(field, newValue, oldValue) {
						blockedDateAfterPersonDeath('personpanel', this.PersonInfo, field, newValue, oldValue);
					}.createDelegate(this)
				},
				name: 'EvnMorfoHistologicProto_DiagSetDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				tabIndex: TABINDEX_EMHPEF + 16,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: true,
				fieldLabel: lang['tekst_diagnoza'],
				height: 50,
				name: 'EvnMorfoHistologicProto_DiagDescr',
				tabIndex: TABINDEX_EMHPEF + 17,
				width: 500,
				xtype: 'textarea'
			}, {
				allowBlank: true,
				fieldLabel: lang['rezultatyi_kliniko-laboratornyih_issledovaniy'],
				height: 50,
				name: 'EvnMorfoHistologicProto_ResultLabStudy',
				tabIndex: TABINDEX_EMHPEF + 18,
				width: 500,
				xtype: 'textarea'
			}, {
				allowBlank: true,
				fieldLabel: lang['patologoanatomicheskiy_diagnoz_osnovnoe_zabolevanie_oslojneniya_soputstvuyuschie_zabolevaniya'],
				height: 50,
				name: 'EvnMorfoHistologicProto_DiagPathology',
				tabIndex: TABINDEX_EMHPEF + 19,
				width: 500,
				xtype: 'textarea'
			},
			new sw.Promed.Panel({
				autoHeight: true,
				bodyStyle: 'padding-top: 0px;',
				border: true,
				collapsible: true,
				id: 'EMHPEF_ClinicalDiagErrorsPanel',
				isLoaded: false,
				layout: 'form',
				listeners: {
					'expand': function(panel) {
						if ( panel.isLoaded === false ) {
							panel.isLoaded = true;
							panel.findById('EMHPEF_EvnMorfoHistologicDiagDiscrepancyGrid').getGrid().getStore().load({
								params: {
									EvnMorfoHistologicProto_id: this.FormPanel.getForm().findField('EvnMorfoHistologicProto_id').getValue()
								}
							});
						}

						panel.doLayout();
					}.createDelegate(this)
				},
				style: 'margin-bottom: 0.5em;',
				title: lang['2_oshibki_klinicheskoy_diagnostiki'],
				items: [ new sw.Promed.ViewFrame({
					actions: [
						{ name: 'action_add', handler: function() { this.openEvnMorfoHistologicDiagDiscrepancyEditWindow('add'); }.createDelegate(this) },
						{ name: 'action_edit', handler: function() { this.openEvnMorfoHistologicDiagDiscrepancyEditWindow('edit'); }.createDelegate(this) },
						{ name: 'action_view', handler: function() { this.openEvnMorfoHistologicDiagDiscrepancyEditWindow('view'); }.createDelegate(this) },
						{ name: 'action_delete', handler: function() { this.deleteGridRecord('EvnMorfoHistologicDiagDiscrepancy'); }.createDelegate(this) },
						{ name: 'action_refresh', disabled: true },
						{ name: 'action_print', disabled: true }
					],
					autoLoadData: false,
					border: true,
					dataUrl: '/?c=EvnMorfoHistologicProto&m=loadEvnMorfoHistologicDiagDiscrepancyGrid',
					height: 150,
					id: 'EMHPEF_EvnMorfoHistologicDiagDiscrepancyGrid',
					onDblClick: function() {
						if ( !this.ViewActions.action_edit.isDisabled() ) {
							this.ViewActions.action_edit.execute();
						}
					},
					onEnter: function() {
						if ( !this.ViewActions.action_edit.isDisabled() ) {
							this.ViewActions.action_edit.execute();
						}
					},
					onLoadData: function() {
						//
					},
					onRowSelect: function(sm, index, record) {
						//
					},
					paging: false,
					region: 'center',
					stringfields: [
						{ name: 'EvnMorfoHistologicDiagDiscrepancy_id', type: 'int', header: 'ID', key: true },
						{ name: 'DiagClinicalErrType_id', type: 'int', hidden: true },
						{ name: 'DiagReasonDiscrepancy_id', type: 'int', hidden: true },
						{ name: 'EvnMorfoHistologicDiagDiscrepancy_Note', type: 'string', hidden: true },
						{ name: 'RecordStatus_Code', type: 'int', hidden: true },
						{ name: 'DiagClinicalErrType_Name', type: 'string', header: lang['tip_rashojdeniya'], width: 330 },
						{ name: 'DiagReasonDiscrepancy_Name', type: 'string', header: lang['prichina_rashojdeniya'], id: 'autoexpand' }
					],
					style: 'margin-bottom: 0.5em;',
					title: lang['rashojdenie_diagnozov_po_osnovnomu_zabolevaniyu']
				}), {
					layout: 'column',
					border: false,
					items: [
						{
							border: false,
							layout: 'form',
							items: [{
								displayField: 'DeathSvid_Num',
								fieldLabel: lang['svidetelstvo_o_smerti'],
								hiddenName: 'DeathSvid_id',
								store: new Ext.data.Store({
									autoLoad: false,
									reader: new Ext.data.JsonReader({
										id: 'EvnPS_id'
									}, [
										{ name: 'DeathSvid_id', mapping: 'DeathSvid_id' },
										{ name: 'DeathSvid_Num', mapping: 'DeathSvid_Num' },
							{ name: 'DeathSvid_GiveDate', mapping: 'DeathSvid_GiveDate' },
							{ name: 'Type_MSoS', mapping: 'Type_MSoS' }
									]),
									sortInfo: {
										field: 'DeathSvid_Num'
									},
						url: '/?c=MedSvid&m=loadDeathSvidListWithPntDeath'
								}),
								tabIndex: TABINDEX_EMHPEF + 19,
								valueField: 'DeathSvid_id',
								width: 300,
								xtype: 'swbaselocalcombo'
							}]
						},{
							border: false,
							layout: 'form',
							id: 'AddDeathSvidButton',
							items: [{border: false,
								layout: 'form',
								items: [{
									handler: function() {
										var base_form = this.FormPanel.getForm();
										var me = this;

										var params = {
											'Person_id': base_form.findField('Person_id').getValue(),
											'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
											'Server_id': base_form.findField('Server_id').getValue()
										}

										var callback = function(){
											getWnd('swMedSvidDeathEditWindow').hide();
											base_form.findField('DeathSvid_id').getStore().load({
												callback: function() {
													if ( base_form.findField('DeathSvid_id').getStore().getCount() == 1 ) {
														base_form.findField('DeathSvid_id').setValue(base_form.findField('DeathSvid_id').getStore().getAt(0).get('DeathSvid_id'));
														me.findById('AddDeathSvidButton').disable();
													} else {
														me.findById('AddDeathSvidButton').enable();
													}
												},
												params: {
													Person_id: base_form.findField('Person_id').getValue()
												}
											})
										}

										getWnd('swMedSvidDeathEditWindow').show({
											action: 'add',
											formParams: params,
											callback: callback
										});
									}.createDelegate(this),
									icon: 'img/icons/add16.png',
									iconCls: 'x-btn-text',
									tabIndex: TABINDEX_EMHPEF + 19,
									xtype: 'button'
								}]
							}]
						}
					]
				}, {
					autoHeight: true,
					style: 'padding: 0px;',
					title: lang['kodyi'],
					xtype: 'fieldset',

					items: [{
						allowBlank: true,
						fieldLabel: 'I.	а)',
						hiddenName: 'Diag_vid',
						listWidth: 600,
						tabIndex: TABINDEX_EMHPEF + 21,
						width: 500,
						xtype: 'swdiagcombo'
					}, {
						allowBlank: true,
						emptyText: lang['bolezn_ili_sostoyanie_neposredstvenno_privedshie_k_smerti'],
						fieldLabel: '',
						height: 50,
						labelSeparator: '',
						name: 'Diag_vid_Descr',
						tabIndex: TABINDEX_EMHPEF + 22,
						width: 500,
						xtype: 'textarea'
					}, {
						allowBlank: true,
						fieldLabel: 'б)',
						hiddenName: 'Diag_wid',
						listWidth: 600,
						tabIndex: TABINDEX_EMHPEF + 23,
						width: 500,
						xtype: 'swdiagcombo'
					}, {
						allowBlank: true,
						emptyText: lang['patologicheskoe_sostoyanie_kotoroe_privelo_k_vyisheukazannoy_prichine'],
						fieldLabel: '',
						height: 50,
						labelSeparator: '',
						name: 'Diag_wid_Descr',
						tabIndex: TABINDEX_EMHPEF + 24,
						width: 500,
						xtype: 'textarea'
					}, {
						allowBlank: true,
						fieldLabel: 'в)',
						hiddenName: 'Diag_xid',
						listWidth: 600,
						tabIndex: TABINDEX_EMHPEF + 25,
						width: 500,
						xtype: 'swdiagcombo'
					}, {
						allowBlank: true,
						emptyText: lang['pervonachalnaya_prichina_smerti'],
						fieldLabel: '',
						height: 50,
						labelSeparator: '',
						name: 'Diag_xid_Descr',
						tabIndex: TABINDEX_EMHPEF + 26,
						width: 500,
						xtype: 'textarea'
					}, {
						allowBlank: true,
						fieldLabel: 'г)',
						hiddenName: 'Diag_yid',
						listWidth: 600,
						tabIndex: TABINDEX_EMHPEF + 27,
						width: 500,
						xtype: 'swdiagcombo'
					}, {
						allowBlank: true,
						emptyText: lang['vneshnyaya_prichina_smerti_pri_travmah_i_otravleniyah'],
						fieldLabel: '',
						height: 50,
						labelSeparator: '',
						name: 'Diag_yid_Descr',
						tabIndex: TABINDEX_EMHPEF + 28,
						width: 500,
						xtype: 'textarea'
					}, {
						allowBlank: true,
						fieldLabel: 'II.',
						hiddenName: 'Diag_zid',
						listWidth: 600,
						tabIndex: TABINDEX_EMHPEF + 29,
						width: 500,
						xtype: 'swdiagcombo'
					}, {
						allowBlank: true,
						emptyText: lang['prochie_vajnyie_sostoyaniya_sposobstvovavshie_smerti_no_ne_svyazannyie_s_boleznyu_ili_patologicheskim_sostoyaniem_privedshim_k_ney_vklyuchaya_upotreblenie_alkogolya_narkoticheskih_sredstv_psihotropnyih_i_drugih_toksicheskih_veschestv_soderjanie_ih_v_krovi_a_takje_operatsii_nazvanie_data'],
						fieldLabel: '',
						height: 50,
						labelSeparator: '',
						name: 'Diag_zid_Descr',
						tabIndex: TABINDEX_EMHPEF + 30,
						width: 500,
						xtype: 'textarea'
					}]
				}, {
					allowBlank: true,
					fieldLabel: lang['kliniko-patologoanatomicheskiy_epikriz'],
					height: 50,
					name: 'EvnMorfoHistologicProto_Epicrisis',
					tabIndex: TABINDEX_EMHPEF + 31,
					width: 500,
					xtype: 'textarea'
				}, {
					allowBlank: false,
					codeField: 'MedPersonal_TabCode',
					displayField: 'MedPersonal_Fio',
					enableKeyEvents: true,
					fieldLabel: lang['patologoanatom'],
					hiddenName: 'MedPersonal_aid',
					listWidth: 650,
					store: new Ext.data.Store({
						autoLoad: false,
						reader: new Ext.data.JsonReader({
							id: 'MedPersonal_id'
						}, [
							{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
							{ name: 'MedPersonal_TabCode', mapping: 'MedPersonal_TabCode' },
							{ name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio' }
						]),
						sortInfo: {
							field: 'MedPersonal_Fio'
						},
						url: C_MP_LOADLIST
					}),
					tabIndex: TABINDEX_EMHPEF + 32,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<table style="border: 0;"><td style="width: 50px"><font color="red">{MedPersonal_TabCode}</font></td><td><h3>{MedPersonal_Fio}&nbsp;</h3></td></tr></table>',
						'</div></tpl>'
					),
					valueField: 'MedPersonal_id',
					width: 500,
					xtype: 'swbaselocalcombo'
				}, {
					allowBlank: false,
					codeField: 'MedPersonal_TabCode',
					displayField: 'MedPersonal_Fio',
					enableKeyEvents: true,
					fieldLabel: lang['zaveduyuschiy_otdeleniem'],
					hiddenName: 'MedPersonal_zid',
					listWidth: 650,
					store: new Ext.data.Store({
						autoLoad: false,
						reader: new Ext.data.JsonReader({
							id: 'MedPersonal_id'
						}, [
							{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
							{ name: 'MedPersonal_TabCode', mapping: 'MedPersonal_TabCode' },
							{ name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio' }
						]),
						sortInfo: {
							field: 'MedPersonal_Fio'
						},
						url: C_MP_LOADLIST
					}),
					tabIndex: TABINDEX_EMHPEF + 33,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<table style="border: 0;"><td style="width: 50px"><font color="red">{MedPersonal_TabCode}</font></td><td><h3>{MedPersonal_Fio}&nbsp;</h3></td></tr></table>',
						'</div></tpl>'
					),
					valueField: 'MedPersonal_id',
					width: 500,
					xtype: 'swbaselocalcombo'
				}]
			}),
			new sw.Promed.Panel({
				autoHeight: true,
				bodyStyle: 'padding-top: 0.5em;',
				border: true,
				collapsible: true,
				id: 'EMHPEF_EvnMorfoHistologicProtoResultsPanel',
				layout: 'form',
				listeners: {
					'expand': function(panel) {
						// this.FormPanel.getForm().findField('EvnMorfoHistologicProto_BrainWeight').focus(true);
					}.createDelegate(this)
				},
				style: 'margin-bottom: 0.5em;',
				title: lang['3_rezultatyi_patologoanatomicheskogo_issledovaniya'],
				items: [{
					autoHeight: true,
					style: 'padding: 2px 0px 2px 0px;',
					title: lang['ves_organov_tela_v_grammah'],
					xtype: 'fieldset',

					items: [{
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						enableKeyEvents: true,
						fieldLabel: lang['mozg'],
						name: 'EvnMorfoHistologicProto_BrainWeight',
						tabIndex: TABINDEX_EMHPEF + 34,
						width: 100,
						xtype: 'numberfield'
					}, {
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						enableKeyEvents: true,
						fieldLabel: lang['serdtse'],
						name: 'EvnMorfoHistologicProto_HeartWeight',
						tabIndex: TABINDEX_EMHPEF + 35,
						width: 100,
						xtype: 'numberfield'
					}, {
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						enableKeyEvents: true,
						fieldLabel: lang['legkie'],
						name: 'EvnMorfoHistologicProto_LungsWeight',
						tabIndex: TABINDEX_EMHPEF + 36,
						width: 100,
						xtype: 'numberfield'
					}, {
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						enableKeyEvents: true,
						fieldLabel: lang['pechen'],
						name: 'EvnMorfoHistologicProto_LiverWeight',
						tabIndex: TABINDEX_EMHPEF + 37,
						width: 100,
						xtype: 'numberfield'
					}, {
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						enableKeyEvents: true,
						fieldLabel: lang['selezenka'],
						name: 'EvnMorfoHistologicProto_SpleenWeight',
						tabIndex: TABINDEX_EMHPEF + 38,
						width: 100,
						xtype: 'numberfield'
					}, {
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						enableKeyEvents: true,
						fieldLabel: lang['pochka_levaya'],
						name: 'EvnMorfoHistologicProto_KidneyLeftWeight',
						tabIndex: TABINDEX_EMHPEF + 39,
						width: 100,
						xtype: 'numberfield'
					}, {
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						enableKeyEvents: true,
						fieldLabel: lang['pochka_pravaya'],
						name: 'EvnMorfoHistologicProto_KidneyRightWeight',
						tabIndex: TABINDEX_EMHPEF + 40,
						width: 100,
						xtype: 'numberfield'
					}]
				}, {
					allowBlank: true,
					allowDecimals: false,
					allowNegative: false,
					enableKeyEvents: true,
					fieldLabel: lang['vzyato_kusochkov'],
					name: 'EvnMorfoHistologicProto_BitCount',
					tabIndex: TABINDEX_EMHPEF + 41,
					width: 100,
					xtype: 'numberfield'
				}, {
					allowBlank: true,
					allowDecimals: false,
					allowNegative: false,
					enableKeyEvents: true,
					fieldLabel: lang['izgotovleno_blokov'],
					name: 'EvnMorfoHistologicProto_BlockCount',
					tabIndex: TABINDEX_EMHPEF + 42,
					width: 100,
					xtype: 'numberfield'
				}, {
					allowBlank: true,
					fieldLabel: lang['vzyat_material_dlya_drugih_metodov_issledovaniya'],
					height: 100,
					name: 'EvnMorfoHistologicProto_MethodDescr',
					tabIndex: TABINDEX_EMHPEF + 43,
					width: 500,
					xtype: 'textarea'
				}, {
					allowBlank: true,
					fieldLabel: lang['tekst_protokola'],
					height: 50,
					name: 'EvnMorfoHistologicProto_ProtocolDescr',
					tabIndex: TABINDEX_EMHPEF + 44,
					width: 500,
					xtype: 'textarea'
				}]
			})]
		});

		this.PersonInfo = new sw.Promed.PersonInfoPanel({
			button1OnHide: function() {
				if ( this.action == 'view' ) {
					this.buttons[this.buttons.length - 1].focus();
				}
				else {
					this.FormPanel.getForm().findField('EvnDirectionMorfoHistologic_SerNum').focus(true);
				}
			}.createDelegate(this),
			button2Callback: function(callback_data) {
				this.FormPanel.getForm().findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
				this.FormPanel.getForm().findField('Server_id').setValue(callback_data.Server_id);

				this.PersonInfo.load({ Person_id: callback_data.Person_id, Server_id: callback_data.Server_id });
			}.createDelegate(this),
			button2OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			button3OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			button4OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			button5OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			collapsible: true,
			collapsed: true,
			floatable: false,
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			region: 'north',
			title: lang['zagruzka'],
			titleCollapse: true
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = this.FormPanel.getForm();

					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus(true);
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[1].focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_EMHPEF + 45,
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.printEvnMorfoHistologicProto();
				}.createDelegate(this),
				iconCls: 'print16',
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.buttons[0].focus(true);
					}
					else {
						this.buttons[this.buttons.length - 1].focus(true);
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_EMHPEF + 46,
				text: BTN_FRMPRINT
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					this.buttons[1].focus();
				}.createDelegate(this),
				onTabAction: function () {
					if ( this.action != 'view' ) {
						this.FormPanel.getForm().findField('EvnDirectionMorfoHistologic_SerNum').focus(true);
					}
					else {
						this.buttons[1].focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EMHPEF + 47,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.PersonInfo,
				this.FormPanel
			],
			layout: 'border'
		});

		sw.Promed.swEvnMorfoHistologicProtoEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnMorfoHistologicProtoEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'beforehide': function(win) {
			win.onCancelAction();
		},
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			win.findById('EMHPEF_ClinicalDiagErrorsPanel').doLayout();
			win.findById('EMHPEF_EvnDiagPSPanel').doLayout();
			win.findById('EMHPEF_EvnMorfoHistologicMemberPanel').doLayout();
			win.findById('EMHPEF_EvnMorfoHistologicProtoResultsPanel').doLayout();
		},
		'restore': function(win) {
			win.fireEvent('maximize', win);
		}
	},
	maximizable: true,
	maximized: false,
	minHeight: 550,
	minWidth: 750,
	modal: true,
	onCancelAction: function() {
		var base_form = this.FormPanel.getForm();
		var evn_morfo_histologic_proto_id = base_form.findField('EvnMorfoHistologicProto_id').getValue();

		if ( this.onCancelActionFlag == true && evn_morfo_histologic_proto_id > 0 && this.action == 'add') {
			// удалить протокол патоморфогистологического исследования
			// закрыть окно после успешного удаления
			var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление протокола..." });
			loadMask.show();

			Ext.Ajax.request({
				failure: function(response, options) {
					loadMask.hide();
					sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_protokola_patomorfogistologicheskogo_issledovaniya_voznikli_oshibki_[tip_oshibki_2]']);
					return false;
				},
				params: {
					EvnMorfoHistologicProto_id: evn_morfo_histologic_proto_id
				},
				success: function(response, options) {
					loadMask.hide();

					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['pri_udalenii_protokola_patomorfogistologicheskogo_issledovaniya_voznikli_oshibki_[tip_oshibki_3]']);
						return false;
					}
				},
				url: '/?c=EvnMorfoHistologicProto&m=deleteEvnMorfoHistologicProto'
			});
		}
	},
	onCancelActionFlag: true,
	onHide: Ext.emptyFn,
	openEvnDirectionMorfoHistologicListWindow: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();

		if ( base_form.findField('EvnDirectionMorfoHistologic_SerNum').disabled ) {
			return false;
		}

		if ( getWnd('swEvnDirectionMorfoHistologicListWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_prosmotra_spiska_napravleniy_uje_otkryito']);
			return false;
		}

		var params = new Object();

		params.callback = function(data) {
			if ( !data ) {
				return false;
			}

			base_form.findField('EvnDirectionMorfoHistologic_id').setValue(data.EvnDirectionMorfoHistologic_id);
			base_form.findField('EvnDirectionMorfoHistologic_SerNum').setValue(data.EvnDirectionMorfoHistologic_Ser + ' ' + data.EvnDirectionMorfoHistologic_Num + ', ' + Ext.util.Format.date(data.EvnDirectionMorfoHistologic_setDate, 'd.m.Y'));
		}.createDelegate(this);
		params.onHide = function() {
			base_form.findField('EvnDirectionMorfoHistologic_SerNum').focus();
		}.createDelegate(this);

		params.formParams = {
			'PersonEvn_id': win.PersonEvn_id,
			'Person_id': win.Person_id,
			'Server_id': win.Server_id
		};
		params.Person_Birthday = this.PersonInfo.getFieldValue('Person_Birthday');
		params.Person_Firname = this.PersonInfo.getFieldValue('Person_Firname');
		params.Person_id = base_form.findField('Person_id').getValue();
		params.Person_Secname = this.PersonInfo.getFieldValue('Person_Secname');
		params.Person_Surname = this.PersonInfo.getFieldValue('Person_Surname');

		getWnd('swEvnDirectionMorfoHistologicListWindow').show(params);
	},
	openEvnDiagPSEditWindow: Ext.emptyFn,
	openEvnMorfoHistologicDiagDiscrepancyEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view'])) ) {
			return false;
		}

		if ( this.action == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swEvnMorfoHistologicDiagDiscrepancyEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_oshibki_klinicheskoy_diagnostiki_uje_otkryito']);
			return false;
		}

		var base_form = this.FormPanel.getForm();
		var grid = this.findById('EMHPEF_EvnMorfoHistologicDiagDiscrepancyGrid').getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.evnMorfoHistologicDiagDiscrepancyData != 'object' ) {
				return false;
			}

			data.evnMorfoHistologicDiagDiscrepancyData.RecordStatus_Code = 0;

			var record = grid.getStore().getById(data.evnMorfoHistologicDiagDiscrepancyData.EvnMorfoHistologicDiagDiscrepancy_id);

			if ( record ) {
				if ( record.get('RecordStatus_Code') == 1 ) {
					data.evnMorfoHistologicDiagDiscrepancyData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.evnMorfoHistologicDiagDiscrepancyData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnMorfoHistologicDiagDiscrepancy_id') ) {
					grid.getStore().removeAll();
				}

				data.evnMorfoHistologicDiagDiscrepancyData.EvnMorfoHistologicDiagDiscrepancy_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.evnMorfoHistologicDiagDiscrepancyData ], true);
			}
		}.createDelegate(this);
		params.formParams = new Object();

		if ( action == 'add' ) {
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnMorfoHistologicDiagDiscrepancy_id') ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			params.formParams = record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
			};
		}

		getWnd('swEvnMorfoHistologicDiagDiscrepancyEditWindow').show(params);
	},
	openEvnMorfoHistologicMemberEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view'])) ) {
			return false;
		}

		if ( this.action == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swEvnMorfoHistologicMemberEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_prisutstvovavshego_pri_vskryitii_uje_otkryito']);
			return false;
		}

		var base_form = this.FormPanel.getForm();
		var grid = this.findById('EMHPEF_EvnMorfoHistologicMemberGrid').getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.evnMorfoHistologicMemberData != 'object' ) {
				return false;
			}

			data.evnMorfoHistologicMemberData.RecordStatus_Code = 0;

			var record = grid.getStore().getById(data.evnMorfoHistologicMemberData.EvnMorfoHistologicMember_id);

			if ( record ) {
				if ( record.get('RecordStatus_Code') == 1 ) {
					data.evnMorfoHistologicMemberData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.evnMorfoHistologicMemberData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnMorfoHistologicMember_id') ) {
					grid.getStore().removeAll();
				}

				data.evnMorfoHistologicMemberData.EvnMorfoHistologicMember_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.evnMorfoHistologicMemberData ], true);
			}
		}.createDelegate(this);
		params.formParams = new Object();

		if ( action == 'add' ) {
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnMorfoHistologicMember_id') ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			params.formParams = record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
			};
		}

		getWnd('swEvnMorfoHistologicMemberEditWindow').show(params);
	},
	plain: true,
	printEvnMorfoHistologicProto: function() {
		switch ( this.action ) {
			case 'add':
			case 'edit':
				this.doSave({
					print: true
				});
			break;

			case 'view':
				var evn_morfo_histologic_proto_id = this.FormPanel.getForm().findField('EvnMorfoHistologicProto_id').getValue();
				window.open('/?c=EvnMorfoHistologicProto&m=printEvnMorfoHistologicProto&EvnMorfoHistologicProto_id=' + evn_morfo_histologic_proto_id, '_blank');
			break;
		}
	},
	resizable: true,
	setMorfoHistologicCorpseRecieptDate: function() {
		var base_form = this.FormPanel.getForm();
		var EvnDirectionMorfoHistologic_id = base_form.findField('EvnDirectionMorfoHistologic_id').getValue();
		if (EvnDirectionMorfoHistologic_id > 0) {
			Ext.Ajax.request({
				params: {
					EvnDirectionMorfoHistologic_id: EvnDirectionMorfoHistologic_id
				},
				callback: function(options, success, response) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (!response_obj.Error_Msg) {
						if (!Ext.isEmpty(response_obj[0])) {
							base_form.findField('MorfoHistologicCorpse_recieptDate').setValue(response_obj[0].MorfoHistologicCorpse_recieptDate);
						}
					}
					else {
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
					}
				}.createDelegate(this),
				url: '/?c=MorfoHistologicCorpseReciept&m=getMorfoHistologicCorpseRecieptDate'
			});
		}
	},
	setEvnMorfoHistologicProtoNumber: function() {
		var base_form = this.FormPanel.getForm();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					base_form.findField('EvnMorfoHistologicProto_Num').setValue(response_obj[0].EvnMorfoHistologicProto_Num);
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_nomera_protokola'], function() { base_form.findField('EvnMorfoHistologicProto_Num').focus(true); }.createDelegate(this) );
				}
			}.createDelegate(this),
			url: '/?c=EvnMorfoHistologicProto&m=getEvnMorfoHistologicProtoNumber'
		});
	},
	show: function() {
		sw.Promed.swEvnMorfoHistologicProtoEditWindow.superclass.show.apply(this, arguments);

		this.findById('EMHPEF_ClinicalDiagErrorsPanel').collapse();
		this.findById('EMHPEF_EvnDiagPSPanel').collapse();
		this.findById('EMHPEF_EvnMorfoHistologicMemberPanel').collapse();
		this.findById('EMHPEF_EvnMorfoHistologicProtoResultsPanel').collapse();

		this.restore();
		this.center();
		this.maximize();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		// base_form.findField('DeathSvid_id').getStore().removeAll();
		base_form.findField('EvnPS_id').getStore().removeAll();

		this.findById('EMHPEF_Caption').hide();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.onCancelActionFlag = true;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.UserMedStaffFact_id = null;
		this.UserMedStaffFactList = new Array();

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		if(arguments[0].formParams.PersonEvn_id) {
			this.PersonEvn_id = arguments[0].formParams.PersonEvn_id;
		}

		if(arguments[0].formParams.Person_id) {
			this.Person_id = arguments[0].formParams.Person_id;
		}

		if(arguments[0].formParams.Server_id) {
			this.Server_id = arguments[0].formParams.Server_id;
		}

		// определенный медстафффакт
		if ( arguments[0].UserMedStaffFact_id && arguments[0].UserMedStaffFact_id > 0 ) {
			this.UserMedStaffFact_id = arguments[0].UserMedStaffFact_id;
		}
		// если в настройках есть medstafffact, то имеем список мест работы
		else if ( Ext.globalOptions.globals['medstafffact'] && Ext.globalOptions.globals['medstafffact'].length > 0 ) {
			this.UserMedStaffFactList = Ext.globalOptions.globals['medstafffact'];
		}

		base_form.setValues(arguments[0].formParams);

		this.PersonInfo.setTitle('...');
		this.PersonInfo.load({
			callback: function() {
				this.PersonInfo.setPersonTitle();
			}.createDelegate(this),
			Person_id: base_form.findField('Person_id').getValue(),
			Server_id: base_form.findField('Server_id').getValue()
		});

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( this.action == 'add' ) {
			this.findById('EMHPEF_ClinicalDiagErrorsPanel').isLoaded = true;
			this.findById('EMHPEF_EvnMorfoHistologicMemberPanel').isLoaded = true;
		}
		else {
			this.findById('EMHPEF_ClinicalDiagErrorsPanel').isLoaded = false;
			this.findById('EMHPEF_EvnMorfoHistologicMemberPanel').isLoaded = false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_PATHOMORPH_EMHPEFADD);
				this.enableEdit(true);

				LoadEmptyRow(this.findById('EMHPEF_EvnMorfoHistologicDiagDiscrepancyGrid').getGrid());
				LoadEmptyRow(this.findById('EMHPEF_EvnMorfoHistologicMemberGrid').getGrid());

				base_form.findField('EvnMorfoHistologicProto_setDate').fireEvent('change', base_form.findField('EvnMorfoHistologicProto_setDate'), base_form.findField('EvnMorfoHistologicProto_setDate').getValue());

				// Генерируем серию протокола
				var lpu_id = Ext.globalOptions.globals.lpu_id;

				var lpu_store = new Ext.db.AdapterStore({
					autoLoad: false,
					dbFile: 'Promed.db',
					fields: [
						{ name: 'Lpu_id', type: 'int' },
						{ name: 'Lpu_Ouz', type: 'int' },
						{ name: 'Lpu_RegNomC2', type: 'int' },
						{ name: 'Lpu_RegNomN2', type: 'int' }
					], 
					key: 'Lpu_id',
					tableName: 'Lpu'
				});

				lpu_store.load({
					callback: function(records, options, success) {
						var serial = '';

						for ( var i = 0; i < records.length; i++ ) {
							if ( records[i].get('Lpu_id') == lpu_id ) {
								serial = records[i].get('Lpu_Ouz');
							}
						}

						base_form.findField('EvnMorfoHistologicProto_Ser').setValue(serial);
					}
				});

				// Получаем номер направления
				this.setEvnMorfoHistologicProtoNumber();
				// Получаемдату поступления тела
				this.setMorfoHistologicCorpseRecieptDate();
				// Загружаем список КВС
				base_form.findField('EvnPS_id').getStore().load({
					callback: function() {
						if ( base_form.findField('EvnPS_id').getStore().getCount() == 1 ) {
							base_form.findField('EvnPS_id').setValue(base_form.findField('EvnPS_id').getStore().getAt(0).get('EvnPS_id'));
							base_form.findField('EvnPS_id').fireEvent('change', base_form.findField('EvnPS_id'), base_form.findField('EvnPS_id').getValue());
						}
					},
					params: {
						Person_id: base_form.findField('Person_id').getValue()
					}
				});

				// Загружаем список свидетельств о смерти
				var me = this;
				base_form.findField('DeathSvid_id').getStore().load({
					callback: function() {
						if ( base_form.findField('DeathSvid_id').getStore().getCount() == 1 ) {
							base_form.findField('DeathSvid_id').setValue(base_form.findField('DeathSvid_id').getStore().getAt(0).get('DeathSvid_id'));
						}

						if ( base_form.findField('DeathSvid_id').getStore().getCount() > 0 ) {
							me.findById('AddDeathSvidButton').disable();
						} else {
							me.findById('AddDeathSvidButton').enable();
						}
					},
					params: {
						Person_id: base_form.findField('Person_id').getValue()
					}
				});

				loadMask.hide();

				base_form.items.each(function(f){ f.validate(); });

				base_form.findField('EvnDirectionMorfoHistologic_SerNum').focus(true, 250);

				//@task https://redmine.swan-it.ru/issues/194662
				if(getRegionNick() == 'msk') {
					Ext.Ajax.request({
						url: '?c=EvnDirectionMorfoHistologic&m=loadEvnDirectionMorfoHistologicList',
						params: {
							Person_id: this.Person_id,
						},
						success: function (response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							//применение только если есть только одно направление
							if (response_obj.length == 1) {
								//Дата поступления заполняется текущей датой
								base_form.findField('EvnMorfoHistologicProto_setDate').setValue(getValidDT(getGlobalOptions().date, ''));
								base_form.findField('EvnMorfoHistologicProto_setDate').fireEvent('change', base_form.findField('EvnMorfoHistologicProto_setDate').getValue(), getValidDT(getGlobalOptions().date, ''));
								
								// вставка значений направления в протокол
								// выбираем единственный протокол
								var response_item = response_obj[0];
								// диагноз
								if (response_item.EvnDirectionMorfoHistologic_Diag) {
									base_form.findField('Diag_did').getStore().load({
										callback: function () {
											base_form.findField('Diag_did').getStore().each(function (record) {
												if (record.get('Diag_id') == response_item.EvnDirectionMorfoHistologic_Diag) {
													base_form.findField('Diag_did').setValue(response_item.EvnDirectionMorfoHistologic_Diag);
													base_form.findField('Diag_did').disable();
													base_form.findField('Diag_did').fireEvent('select', base_form.findField('Diag_did'), record, 0);
												}
											});
										},
										params: {where: "where DiagLevel_id = 4 and Diag_id = " + response_item.EvnDirectionMorfoHistologic_Diag}
									});
								}
								base_form.findField('Diag_did').disable();

								// дата-время смерти
								if (response_item.EvnMorfoHistologic_deathDate) {
									base_form.findField('EvnMorfoHistologicProto_deathDate').setValue(response_item.EvnMorfoHistologic_deathDate);
									base_form.findField('EvnMorfoHistologicProto_deathDate').disable();
								}
								if (response_item.EvnMorfoHistologic_deathTime) {
									base_form.findField('EvnMorfoHistologicProto_deathTime').setValue(response_item.EvnMorfoHistologic_deathTime);
									base_form.findField('EvnMorfoHistologicProto_deathTime').disable();
								}
								// направление: номер и ID (required)
								if (response_item.EvnDirectionMorfoHistologic_Num) {
									base_form.findField('EvnDirectionMorfoHistologic_SerNum').setValue(response_item.EvnDirectionMorfoHistologic_Num);
								}
								if (response_item.EvnDirectionMorfoHistologic_id) {
									base_form.findField('EvnDirectionMorfoHistologic_id').setValue(response_item.EvnDirectionMorfoHistologic_id);
								}
								
								if (response_item.EvnDirectionMorfoHistologic_MedPersonal_id && response_item.EvnDirectionMorfoHistologic_LpuSection_id) {
									// отделение
									base_form.findField('LpuSection_id').getStore().load({
										callback: function () {
											var index = base_form.findField('LpuSection_id').getStore().findBy(function (rec) {
												if (rec.get('LpuSection_id') == response_item.EvnDirectionMorfoHistologic_LpuSection_id) {
													return true;
												} else {
													return false;
												}
											});

											if (index >= 0) {
												base_form.findField('LpuSection_id').setValue(base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_id'));
												base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_id'));
											}
										}.createDelegate(this),
										params: {
											LpuSection_id: response_item.EvnDirectionMorfoHistologic_LpuSection_id,
											mode: 'combo'
										}
									});
									base_form.findField('LpuSection_id').disable();

									// мед. работник
									base_form.findField('MedStaffFact_id').getStore().load({
										callback: function () {
											var index = base_form.findField('MedStaffFact_id').getStore().findBy(function (rec) {
												if (rec.get('MedPersonal_id') == ajax_med_personal_id) {
													return true;
												} else {
													return false;
												}
											});

											if (index >= 0) {
												base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
												base_form.findField('MedStaffFact_id').fireEvent('change', base_form.findField('MedStaffFact_id'), base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
											}

										}.createDelegate(this),
										params: {
											LpuSection_id: response_item.EvnDirectionMorfoHistologic_LpuSection_id,
											mode: 'combo'
										}
									});
									base_form.findField('MedStaffFact_id').disable();
								}
							}

						}.createDelegate(this),
						failure: function () {

						}
					});
				}
			break;

			case 'edit':
			case 'view':
				var evn_morfo_histologic_proto_id = base_form.findField('EvnMorfoHistologicProto_id').getValue();

				if ( !evn_morfo_histologic_proto_id ) {
					loadMask.hide();
					this.hide();
					return false;
				}

				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						'EvnMorfoHistologicProto_id': evn_morfo_histologic_proto_id
					},
					success: function() {
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}

						if ( this.action == 'edit' ) {
							this.setTitle(WND_PATHOMORPH_EMHPEFEDIT);
							this.enableEdit(true);
						}
						else {
							this.setTitle(WND_PATHOMORPH_EMHPEFVIEW);
							this.enableEdit(false);
						}

						if ( base_form.findField('pmUser_Name').getValue().toString().length > 0 ) {
							this.findById('EMHPEF_Caption').show();
						}

						var death_svid_id = base_form.findField('DeathSvid_id').getValue();
						var pnt_death_svid_id = base_form.findField('PntDeathSvid_id').getValue();
						var diag_did = base_form.findField('Diag_did').getValue();
						var diag_sid = base_form.findField('Diag_sid').getValue();
						var diag_vid = base_form.findField('Diag_vid').getValue();
						var diag_wid = base_form.findField('Diag_wid').getValue();
						var diag_xid = base_form.findField('Diag_xid').getValue();
						var diag_yid = base_form.findField('Diag_yid').getValue();
						var diag_zid = base_form.findField('Diag_zid').getValue();
						var evnps_id = base_form.findField('EvnPS_id').getValue();
						var lpu_section_id = base_form.findField('LpuSection_id').getValue();
						var med_personal_aid = base_form.findField('MedPersonal_aid').getValue();
						var med_personal_id = base_form.findField('MedPersonal_id').getValue();
						var med_personal_zid = base_form.findField('MedPersonal_zid').getValue();
						var index;
						var record;

						if ( this.action == 'edit' ) {
							base_form.findField('EvnMorfoHistologicProto_setDate').fireEvent('change', base_form.findField('EvnMorfoHistologicProto_setDate'), base_form.findField('EvnMorfoHistologicProto_setDate').getValue());

							index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec, id) {
								if ( rec.get('LpuSection_id') == lpu_section_id && rec.get('MedPersonal_id') == med_personal_id ) {
									return true;
								}
								else {
									return false;
								}
							})
							record = base_form.findField('MedStaffFact_id').getStore().getAt(index);

							if ( record ) {
								base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
							}
						}
						else {
							base_form.findField('LpuSection_id').getStore().load({
								callback: function() {
									index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
										if ( rec.get('LpuSection_id') == lpu_section_id ) {
											return true;
										}
										else {
											return false;
										}
									});

									if ( index >= 0 ) {
										base_form.findField('LpuSection_id').setValue(lpu_section_id);
									}
								}.createDelegate(this),
								params: {
									LpuSection_id: lpu_section_id,
									mode: 'combo'
								}
							});

							base_form.findField('MedStaffFact_id').getStore().load({
								callback: function() {
									index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
										if ( rec.get('MedPersonal_id') == med_personal_id && rec.get('LpuSection_id') == lpu_section_id ) {
											return true;
										}
										else {
											return false;
										}
									});

									if ( index >= 0 ) {
										base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
									}
								}.createDelegate(this),
								params: {
									LpuSection_id: lpu_section_id,
									MedPersonal_id: med_personal_id
								}
							});

							base_form.findField('MedPersonal_aid').getStore().load({
								callback: function() {
									index = base_form.findField('MedPersonal_aid').getStore().findBy(function(rec) {
										if ( rec.get('MedPersonal_id') == med_personal_aid ) {
											return true;
										}
										else {
											return false;
										}
									});

									if ( index >= 0 ) {
										base_form.findField('MedPersonal_aid').setValue(med_personal_aid);
									}
									else {
										base_form.findField('MedPersonal_aid').clearValue();
									}
								}.createDelegate(this),
								params: {
									MedPersonal_id: med_personal_aid
								}
							});

							base_form.findField('MedPersonal_zid').getStore().load({
								callback: function() {
									index = base_form.findField('MedPersonal_zid').getStore().findBy(function(rec) {
										if ( rec.get('MedPersonal_id') == med_personal_zid ) {
											return true;
										}
										else {
											return false;
										}
									});

									if ( index >= 0 ) {
										base_form.findField('MedPersonal_zid').setValue(med_personal_zid);
									}
									else {
										base_form.findField('MedPersonal_zid').clearValue();
									}
								}.createDelegate(this),
								params: {
									MedPersonal_id: med_personal_zid
								}
							});
						}

						var me = this;

						// подмена id свидетельства о смерти, в случае если тип свидетельства перинатальный
						if( pnt_death_svid_id && !death_svid_id ){
							death_svid_id = pnt_death_svid_id;
						}

						if ( death_svid_id ) {
							base_form.findField('DeathSvid_id').getStore().load({
								callback: function() {
									index = base_form.findField('DeathSvid_id').getStore().findBy(function(rec) {
										if ( rec.get('DeathSvid_id') == death_svid_id ) {
											return true;
										}
										else {
											return false;
										}
									});
									record = base_form.findField('DeathSvid_id').getStore().getAt(index);

									if ( record ) {
										base_form.findField('DeathSvid_id').setValue(death_svid_id);
                                        me.findById('AddDeathSvidButton').disable();
									} else {
										base_form.findField('DeathSvid_id').clearValue();
                                        me.findById('AddDeathSvidButton').enable();
									}
								},
								params: {
									Person_id: base_form.findField('Person_id').getValue()
								}
							});
						}

						if ( evnps_id ) {
							base_form.findField('EvnPS_id').getStore().load({
								callback: function() {
									index = base_form.findField('EvnPS_id').getStore().findBy(function(rec) {
										if ( rec.get('EvnPS_id') == evnps_id ) {
											return true;
										}
										else {
											return false;
										}
									});
									record = base_form.findField('EvnPS_id').getStore().getAt(index);

									if ( record ) {
										base_form.findField('EvnPS_id').setValue(evnps_id);
									}
									else {
										base_form.findField('EvnPS_id').clearValue();
									}
								},
								params: {
									Person_id: base_form.findField('Person_id').getValue()
								}
							});
						}

						if ( diag_did ) {
							base_form.findField('Diag_did').getStore().load({
								callback: function() {
									base_form.findField('Diag_did').getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_did ) {
											base_form.findField('Diag_did').fireEvent('select', base_form.findField('Diag_did'), record, 0);
											base_form.findField('Diag_did').focus();
											base_form.findField('Diag_did').blur();
										}
									});
								},
								params: { where: "where Diag_id = " + diag_did }
							});
						}

						if ( diag_sid ) {
							base_form.findField('Diag_sid').getStore().load({
								callback: function() {
									base_form.findField('Diag_sid').getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_sid ) {
											base_form.findField('Diag_sid').fireEvent('select', base_form.findField('Diag_sid'), record, 0);
											base_form.findField('Diag_sid').focus();
											base_form.findField('Diag_sid').blur();
										}
									});
								},
								params: { where: "where Diag_id = " + diag_sid }
							});
						}

						if ( diag_vid ) {
							base_form.findField('Diag_vid').getStore().load({
								callback: function() {
									base_form.findField('Diag_vid').getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_vid ) {
											base_form.findField('Diag_vid').fireEvent('select', base_form.findField('Diag_vid'), record, 0);
										}
									});
								},
								params: { where: "where Diag_id = " + diag_vid }
							});
						}

						if ( diag_wid ) {
							base_form.findField('Diag_wid').getStore().load({
								callback: function() {
									base_form.findField('Diag_wid').getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_wid ) {
											base_form.findField('Diag_wid').fireEvent('select', base_form.findField('Diag_wid'), record, 0);
										}
									});
								},
								params: { where: "where Diag_id = " + diag_wid }
							});
						}

						if ( diag_xid ) {
							base_form.findField('Diag_xid').getStore().load({
								callback: function() {
									base_form.findField('Diag_xid').getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_xid ) {
											base_form.findField('Diag_xid').fireEvent('select', base_form.findField('Diag_xid'), record, 0);
										}
									});
								},
								params: { where: "where Diag_id = " + diag_xid }
							});
						}

						if ( diag_yid ) {
							base_form.findField('Diag_yid').getStore().load({
								callback: function() {
									base_form.findField('Diag_yid').getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_yid ) {
											base_form.findField('Diag_yid').fireEvent('select', base_form.findField('Diag_yid'), record, 0);
										}
									});
								},
								params: { where: "where Diag_id = " + diag_yid }
							});
						}

						if ( diag_zid ) {
							base_form.findField('Diag_zid').getStore().load({
								callback: function() {
									base_form.findField('Diag_zid').getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_zid ) {
											base_form.findField('Diag_zid').fireEvent('select', base_form.findField('Diag_zid'), record, 0);
										}
									});
								},
								params: { where: "where Diag_id = " + diag_zid }
							});
						}

						loadMask.hide();

						//base_form.items.each(function(f){ f.validate(); });
						base_form.clearInvalid();

						if ( this.action == 'edit' ) {
							base_form.findField('EvnDirectionMorfoHistologic_SerNum').focus(true, 250);
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
					}.createDelegate(this),
					url: '/?c=EvnMorfoHistologicProto&m=loadEvnMorfoHistologicProtoEditForm'
				});
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
	},
	width: 750
});