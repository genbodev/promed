sw.Promed.swCrazySpecificsTestWindow = Ext.extend(sw.Promed.BaseForm, {
	autoScroll: true,
	codeRefresh: true,
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	height: 500,
	id: 'swCrazySpecificsTestWindow',
	listeners: {
		'resize': function (win) {
			win.findById('CrazySpecificsForm').doLayout();
		}
	},
    collectGridData:function (gridName) {
        var result = '';
        var grid = this.findById('CSEW_' + gridName).getGrid();
        grid.getStore().clearFilter();
        if (grid.getStore().getCount() > 0) {
            if ((grid.getStore().getCount() == 1) && ((grid.getStore().getAt(0).data.RecordStatus_Code == undefined))) {
                return '';
            }
            var gridData = getStoreRecords(grid.getStore(), {convertDateFields:true});
            result = Ext.util.JSON.encode(gridData);
        }
        grid.getStore().filterBy(function (rec) {
            return Number(rec.get('RecordStatus_Code')) != 3;
        });
        return result;
    },
    doSave: function() {
        if ( this.form.Status == 'save' ) {
   			return false;
   		}
   		var thisWindow = this;
        //thisWindow.form.Status = 'save';
   		if ( !this.form.getForm().isValid() ) {
   			sw.swMsg.show({
   				buttons: Ext.Msg.OK,
   				fn: function() {
                       thisWindow.form.Status = 'edit';
                       thisWindow.form.getFirstInvalidEl().focus(true, 100);
   				},
   				icon: Ext.Msg.WARNING,
   				msg: ERR_INVFIELDS_MSG,
   				title: ERR_INVFIELDS_TIT
   			});
   			return false;
   		}

   		var additionalChecks = true;

   		//todo: place additional checks here. If those checks fails, set the 'additionalChecks' variable to false

   		if (!additionalChecks) {
   			sw.swMsg.show({
   				buttons: Ext.Msg.OK,
   				icon: Ext.Msg.WARNING,
   				msg: 'You have to fill some fields in the right way',
   				title: 'Some error occured'
   			});
   			return false;
   		}

   		var params = new Object();
   		params.Evn_id = this.Evn_id;
        params.Morbus_id = this.Morbus_id;
        //собираем данные из гридов
        params.MorbusCrazyForceTreat = this.collectGridData('MorbusCrazyForceTreat');
        params.MorbusCrazyBaseDrugStart = this.collectGridData('MorbusCrazyBaseDrugStart');
        params.MorbusCrazyDrug = this.collectGridData('MorbusCrazyDrug');
        this.form.getForm().submit({
   			failure: function(/*result_form, action*/) {
                   //alert('Something goes wrong...');
                   thisWindow.form.Status = 'edit';
            },
   			params: params,
   			success: function(/*result_form, action*/) {
                   thisWindow.hide();
            }
   		});
   	},
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), {msg: lang['podojdite']});
		}
		return this.loadMask;
	},
	openMorbusCrazyForceTreatWindow: function(action) {
		if (!action || !action.toString().inlist(['add', 'edit', 'view'])) {
			return false;
		}

		if (action == 'add' && getWnd('swMorbusCrazyForceTreatWindow').isVisible()) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_izmeneniya_prodleniya_prinuditelnogo_lecheniya_uje_otkryito']);
			return false;
		}

		var grid = this.findById('CSEW_MorbusCrazyForceTreat').getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			
			if (!data || !data.forceTreatData) {
				return false;
			}
			
			data.forceTreatData.RecordStatus_Code = 0;

			// Обновить запись в grid
			var record = grid.getStore().getById(data.forceTreatData.MorbusCrazyForceTreat_id);

			if (record) {
				if (record.get('RecordStatus_Code') == 1) {
					data.forceTreatData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for (i = 0; i < grid_fields.length; i++) {
					record.set(grid_fields[i], data.forceTreatData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('MorbusCrazyForceTreat_id')) {
					grid.getStore().removeAll();
				}

				data.forceTreatData.MorbusCrazyForceTreat_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.forceTreatData ], true);
			}
		}
		params.formMode = 'local';
		params.formParams = new Object();

		if (action == 'add') {
			params.onHide = Ext.emptyFn;
		}
		else {
			if (!grid.getSelectionModel().getSelected()) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			params.formParams = selected_record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			};
		}

		getWnd('swMorbusCrazyForceTreatWindow').show(params);
	},
	openMorbusCrazyBaseDrugStartWindow: function(action) {
		if (!action || !action.toString().inlist(['add', 'edit', 'view'])) {
			return false;
		}

		if (action == 'add' && getWnd('swMorbusCrazyBaseDrugStartWindow').isVisible()) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_vozrasta_nachala_upotrebleniya_psihoaktivnyih_sredstv_uje_otkryito']);
			return false;
		}

		var grid = this.findById('CSEW_MorbusCrazyBaseDrugStart').getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			
			if (!data || !data.crazyBaseDrugStartData) {
				return false;
			}
			
			data.crazyBaseDrugStartData.RecordStatus_Code = 0;

			// Обновить запись в grid
			var record = grid.getStore().getById(data.crazyBaseDrugStartData.MorbusCrazyBaseDrugStart_id);

			if (record) {
				if (record.get('RecordStatus_Code') == 1) {
					data.crazyBaseDrugStartData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for (i = 0; i < grid_fields.length; i++) {
					record.set(grid_fields[i], data.crazyBaseDrugStartData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('MorbusCrazyBaseDrugStart_id')) {
					grid.getStore().removeAll();
				}

				data.crazyBaseDrugStartData.MorbusCrazyBaseDrugStart_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.crazyBaseDrugStartData ], true);
			}
		}
		params.formMode = 'local';
		params.formParams = new Object();

		if (action == 'add') {
			params.onHide = Ext.emptyFn;
		}
		else {
			if (!grid.getSelectionModel().getSelected()) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			params.formParams = selected_record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			};
		}

		getWnd('swMorbusCrazyBaseDrugStartWindow').show(params);
	},
	openMorbusCrazyDrugWindow: function(action) {
		if (!action || !action.toString().inlist(['add', 'edit', 'view'])) {
			return false;
		}

		if (action == 'add' && getWnd('swMorbusCrazyDrugWindow').isVisible()) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_upotrebleniya_psihoaktivnyih_veschestv_na_moment_gospitalizatsii_uje_otkryito']);
			return false;
		}

		var grid = this.findById('CSEW_MorbusCrazyDrug').getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			
			if (!data || !data.crazySectionDrugData) {
				return false;
			}
			
			data.crazySectionDrugData.RecordStatus_Code = 0;

			// Обновить запись в grid
			var record = grid.getStore().getById(data.crazySectionDrugData.MorbusCrazyDrug_id);

			if (record) {
				if (record.get('RecordStatus_Code') == 1) {
					data.crazySectionDrugData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for (i = 0; i < grid_fields.length; i++) {
					record.set(grid_fields[i], data.crazySectionDrugData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('MorbusCrazyDrug_id')) {
					grid.getStore().removeAll();
				}

				data.crazySectionDrugData.MorbusCrazyDrug_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.crazySectionDrugData ], true);
			}
		}
		params.formMode = 'local';
		params.formParams = new Object();

		if (action == 'add') {
			params.onHide = Ext.emptyFn;
		}
		else {
			if (!grid.getSelectionModel().getSelected()) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			params.formParams = selected_record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			};
		}

		getWnd('swMorbusCrazyDrugWindow').show(params);
	},
	deleteGridSelectedRecord: function(gridId, idField) {
		var grid = this.findById(gridId).getGrid();
		var record = grid.getSelectionModel().getSelected();
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if (buttonId == 'yes') {
					if (!grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField)) {
						return false;
					}
					switch (Number(record.get('RecordStatus_Code'))) {
						case 0:
							grid.getStore().remove(record);
							break;

						case 1:
						case 2:
							record.set('RecordStatus_Code', 3);
							record.commit();

							grid.getStore().filterBy(function(rec) {
								if (Number(rec.get('RecordStatus_Code')) == 3) {
									return false;
								}
								else {
									return true;
								}
							});
							break;
					}
				}
				if (grid.getStore().getCount() > 0) {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_deystvitelno_hotite_udalit_etu_zapis'],
			title: lang['vopros']
		});
	},
	initComponent: function() {
		
		var thisWindow = this;
		this.CrazySpecificsForm = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			id: 'CrazySpecificsForm',
			frame: false,
			labelAlign: 'right',
			labelWidth: 300,
            url: '/?c=MorbusCrazySpecifics&m=Save',
			items: [
				new sw.Promed.Panel({
					autoHeight: true,
					style: 'margin-bottom: 0.5em;',
					bodyStyle:'background:#DFE8F6;padding:5px;',
					border: true,
					collapsible: true,
					region: 'north',
					layout: 'form',
					title: lang['1_patsient'],
					items: [{
							fieldLabel: lang['meditsinskaya_karta_statsionarnogo_bolnogo'],
							name: 'MorbusCrazySection_NumCard',
							xtype: 'textfield',
							autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
							width: 150
						}, {
							fieldLabel: lang['gospitalizirovan'],
							hiddenName: 'CrazyHospType_id',
							xtype: 'swcrazyhosptypecombo',
							width: 350
						}, {
							fieldLabel: lang['postuplenie'],
							hiddenName: 'CrazySupplyType_id',
							xtype: 'swcrazysupplytypecombo',
							width: 350
						}, {
							fieldLabel: lang['kem_napravlen'],
							hiddenName: 'CrazyDirectType_id',
							xtype: 'swcrazydirecttypecombo',
							width: 350
						}, {
							fieldLabel: lang['poryadok_postupleniya'],
							hiddenName: 'CrazySupplyOrderType_id',
							xtype: 'swcrazysupplyordertypecombo',
							width: 350,
							listeners: {
								'select': function (combo) {
										
									var field = Ext.getCmp('CrazySpecificsForm').getForm().findField('CrazyJudgeDecisionArt35Type_id');
										
									if ( !combo.getValue() || combo.getValue()!='2' ) {
										field.allowBlank = true;
										field.setValue('');
										field.disable();
										return false;
									} else {
										field.allowBlank = false;	
										field.enable();								
									}
								}
							}
						}, {
							fieldLabel: lang['reshenie_sudi_po_st_35'],
							hiddenName: 'CrazyJudgeDecisionArt35Type_id',
							xtype: 'swcrazyjudgedecisionart35typecombo',
							disabled: true,
							width: 350
						}, {
							fieldLabel: lang['otkuda_postupil'],
							hiddenName: 'CrazyDirectFromType_id',
							xtype: 'swcrazydirectfromtypecombo',
							width: 350
						}, {
							fieldLabel: lang['tsel_napravleniya'],
							hiddenName: 'CrazyPurposeDirectType_id',
							xtype: 'swcrazypurposedirecttypecombo',
							width: 350
						}, {
							fieldLabel: lang['invalidnost_pri_vyipiske_po_psih_zabolevaniyu'],
							hiddenName: 'CrazyLeaveInvalidType_id',
							xtype: 'swcrazyleaveinvalidtypecombo',
							width: 350
						}, {
							fieldLabel: lang['obsledovanie_bolnogo_na_vich'],
							hiddenName: 'CrazySurveyHIVType_id',
							xtype: 'swcrazysurveyhivtypecombo',
							width: 350
						}, {
							fieldLabel: lang['vyibyil'],
							hiddenName: 'CrazyLeaveType_id',
							xtype: 'swcrazyleavetypecombo',
							width: 350,
							listeners: {
								'select': function (combo) {
										
									var field = Ext.getCmp('CrazySpecificsForm').getForm().findField('CrazyDeathCauseType_id');
										
									if ( !combo.getValue() || combo.getValue()!='9' ) {
										field.allowBlank = true;
										field.setValue('');
										field.disable();
										return false;
									} else {
										field.allowBlank = false;	
										field.enable();								
									}
								}
							}
						}, {
							fieldLabel: lang['smert_nastupila'],
							hiddenName: 'CrazyDeathCauseType_id',
							xtype: 'swcrazydeathcausetypecombo',
							disabled: true,
							width: 350
						}]
				}),
				new sw.Promed.Panel({
					autoHeight: true,
					style: 'margin-bottom: 0.5em;',
					bodyStyle:'background:#DFE8F6;padding:5px;',
					border: true,
					collapsible: true,
					region: 'north',
					layout: 'form',
					title: lang['2_prinuditelnoe_lechenie'],
					items: [
						new sw.Promed.ViewFrame({
							actions: [
								{name: 'action_add', handler: function() {
									this.openMorbusCrazyForceTreatWindow('add');
								}.createDelegate(this)},
								{name: 'action_edit', handler: function() {
									this.openMorbusCrazyForceTreatWindow('edit');
								}.createDelegate(this)},
								{name: 'action_view', handler: function() {
									this.openMorbusCrazyForceTreatWindow('view');
								}.createDelegate(this)},
								{name: 'action_delete', handler: function() {
									this.deleteGridSelectedRecord('CSEW_MorbusCrazyForceTreat', 'MorbusCrazyForceTreat_id');
								}.createDelegate(this)},
								{name: 'action_print'}
							],
							autoExpandColumn: 'autoexpand',
							object: '',
							editformclassname: 'swMorbusCrazyForceTreatWindow',
							autoExpandMin: 150,
							autoLoadData: false,
							border: false,
							dataUrl: '',
							id: 'CSEW_MorbusCrazyForceTreat',
							paging: false,
							style: 'margin-bottom: 10px',
							stringfields: [
								{name: 'MorbusCrazyForceTreat_id', type: 'int', header: 'ID', key: true},
								{name: 'RecordStatus_Code', type: 'int', hidden: true},
								{name: 'MorbusCrazyForceTreat_setDT', type: 'date', header: lang['data_izmeneniya_prodleniya'], width: 180},
								{name: 'CrazyForceTreatJudgeDecisionType_id', type: 'int', hidden: true},
								{name: 'CrazyForceTreatJudgeDecisionType_Name', type: 'string', header: lang['reshenie_suda'], width: 180},
								{name: 'CrazyForceTreatType_id', type: 'string', hidden: true},
								{name: 'CrazyForceTreatType_Name', type: 'string', header: lang['vid'], width: 480}
							],
							title: lang['izmenenie_prodlenie_prinuditelnogo_lecheniya'],
							toolbar: true
						}),
						{
							fieldLabel: lang['v_sluchae_okonchaniya_prinuditelnogo_lecheniya'],
							hiddenName: 'CrazyForceTreatResultType_id',
							xtype: 'swcrazyforcetreatresulttypecombo',
							width: 300
						}, {
							fieldLabel: lang['data_okonchaniya_predyiduschego_prinuditelnogo_lecheniya'],
							name: 'MorbusCrazySection_LastForceDisDT',
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
						}]
				}),
				new sw.Promed.Panel({
					autoHeight: true,
					style: 'margin-bottom: 0.5em;',
					bodyStyle:'background:#DFE8F6;padding:5px;',
					border: true,
					collapsible: true,
					region: 'north',
					layout: 'form',
					labelWidth: 380,
					title: lang['3_dopolnitelnyie_svedeniya_o_bolnom'],
					items: [{
							fieldLabel: lang['data_obrascheniya_k_psihiatru_narkologu_vpervyie_v_jizni'],
							name: 'MorbusCrazyPerson_firstDT',
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
						}, {
							fieldLabel: lang['ranee_nahodilsya_na_prinuditelnom_dolechivanii_chislo_raz'],
							name: 'MorbusCrazyBase_EarlyCareCount',
							xtype: 'textfield',
							autoCreate: {tag: "input", maxLength: "2", autocomplete: "off"},
							maskRe: /[0-9]/
						}, {
							fieldLabel: lang['vid_ambulatornogo_nablyudeniya'],
							hiddenName: 'CrazyAmbulMonitoringType_id',
							xtype: 'swcrazyambulmonitoringtypecombo',
							anchor: '60%'
						}, {
							fieldLabel: lang['data_predyiduschey_vyipiski_iz_psihiatricheskogo_ili_narkologicheskogo_statsionara'],
							name: 'MorbusCrazySection_LastLeaveDisDT',
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
						}, {
							fieldLabel: lang['chislo_dney_rabotyi_v_ltm'],
							name: 'MorbusCrazySection_LTMDayCount',
							xtype: 'textfield',
							autoCreate: {tag: "input", maxLength: "3", autocomplete: "off"},
							maskRe: /[0-9]/
						}, {
							fieldLabel: lang['chislo_dney_lechebnyih_otpuskov_za_period_gospitalizatsii'],
							name: 'MorbusCrazySection_HolidayDayCount',
							xtype: 'textfield',
							autoCreate: {tag: "input", maxLength: "3", autocomplete: "off"},
							maskRe: /[0-9]/
						}, {
							fieldLabel: lang['chislo_lechebnyih_otpuskov_za_period_gospitalizatsii'],
							name: 'MorbusCrazySection_HolidayCount',
							xtype: 'textfield',
							autoCreate: {tag: "input", maxLength: "2", autocomplete: "off"},
							maskRe: /[0-9]/
						}, {
							hiddenName: 'Diag_nid',
							fieldLabel: lang['soputstvuyuschee_psihicheskoe_narkologicheskoe_zabolevanie'],
							onChange: function() {},
							tabIndex: thisWindow.tabindex + 2,
							enableNativeTabSupport: false,
							width: 400,
							additQueryFilter: "Diag_Code like 'F%'",
							additClauseFilter: 'record["Diag_Code"].search(new RegExp("^F", "i"))>=0',
							allQueryFilter: false,
							xtype: 'swdiagcombo',
							anchor: '80%'
						}, {
							hiddenName: 'Diag_sid',
							fieldLabel: lang['soputstvuyuschee_somaticheskoe_v_t_ch_nevrologicheskoe_zabolevanie'],
							onChange: function() {},
							tabIndex: thisWindow.tabindex + 2,
							enableNativeTabSupport: false,
							width: 400,
							additQueryFilter: "(Diag_Code like 'F%' OR Diag_Code like 'G%')",
							additClauseFilter: '(record["Diag_Code"].search(new RegExp("^F", "i"))>=0 || record["Diag_Code"].search(new RegExp("^G", "i"))>=0)',
							allQueryFilter: false,
							xtype: 'swdiagcombo',
							anchor: '80%'
						}, {
							fieldLabel: lang['invalidnost_po_obschemu_zabolevaniyu'],
							hiddenName: 'InvalidGroupType_id',
							xtype: 'swinvalidgrouptypecombo',
							anchor: '60%'
						}, {
							fieldLabel: lang['invalid_vov'],
							hiddenName: 'MorbusCrazyPerson_IsWowInvalid',
							xtype: 'swyesnocombo',
							anchor: '60%'
						}, {
							fieldLabel: lang['uchastnik_vov'],
							hiddenName: 'MorbusCrazyPerson_IsWowMember',
							xtype: 'swyesnocombo',
							anchor: '60%'
						}, {
							fieldLabel: lang['obrazovanie'],
							hiddenName: 'CrazyEducationType_id',
							xtype: 'swcrazyeducationtypecombo',
							anchor: '60%'
						}, {
							fieldLabel: lang['chislo_zakonchennyih_klassov_sredneobrazovatelnogo_uchrejdeniya'],
							name: 'MorbusCrazyPerson_CompleteClassCount',
							xtype: 'textfield',
							autoCreate: {tag: "input", maxLength: "2", autocomplete: "off"},
							maskRe: /[0-9]/
						}, {
							fieldLabel: lang['uchitsya'],
							hiddenName: 'MorbusCrazyPerson_IsEducation',
							xtype: 'swyesnocombo',
							anchor: '60%'
						}, {
							fieldLabel: lang['istochnik_sredstv_suschestvovaniya'],
							hiddenName: 'CrazySourceLivelihoodType_id',
							xtype: 'swcrazysourcelivelihoodtypecombo',
							anchor: '60%'
						}, {
							fieldLabel: lang['projivaet'],
							hiddenName: 'CrazyResideType_id',
							xtype: 'swcrazyresidetypecombo',
							anchor: '60%'
						}, {
							fieldLabel: lang['usloviya_projivaniya'],
							hiddenName: 'CrazyResideConditionsType_id',
							xtype: 'swcrazyresideconditionstypecombo',
							anchor: '60%'
						}, {
							fieldLabel: lang['ishod_zabolevaniya'],
							hiddenName: 'CrazyResultDeseaseType_id',
							xtype: 'swcrazyresultdeseasetypecombo',
							anchor: '60%'
						}]
				}),
				new sw.Promed.Panel({
					autoHeight: true,
					style: 'margin-bottom: 0.5em;',
					bodyStyle:'background:#DFE8F6;padding:5px;',
					border: true,
					collapsible: true,
					region: 'north',
					layout: 'form',
					title: lang['4_svedeniya_ob_upotreblenii_psihoaktivnyih_sredstv'],
					items: [
						new sw.Promed.ViewFrame({
							actions: [
								{name: 'action_add', handler: function() {
									this.openMorbusCrazyBaseDrugStartWindow('add');
								}.createDelegate(this)},
								{name: 'action_edit', handler: function() {
									this.openMorbusCrazyBaseDrugStartWindow('edit');
								}.createDelegate(this)},
								{name: 'action_view', handler: function() {
									this.openMorbusCrazyBaseDrugStartWindow('view');
								}.createDelegate(this)},
								{name: 'action_delete', handler: function() {
									this.deleteGridSelectedRecord('CSEW_MorbusCrazyBaseDrugStart', 'MorbusCrazyBaseDrugStart_id');
								}.createDelegate(this)},
								{name: 'action_print'}
							],
							autoExpandColumn: 'autoexpand',
							editformclassname: 'swMorbusCrazyBaseDrugStartWindow',
							autoExpandMin: 150,
							autoLoadData: false,
							border: false,
							dataUrl: '',
							id: 'CSEW_MorbusCrazyBaseDrugStart',
							paging: false,
							style: 'margin-bottom: 10px',
							stringfields: [
								{name: 'MorbusCrazyBaseDrugStart_id', type: 'int', header: 'ID', key: true},
								{name: 'RecordStatus_Code', type: 'int', hidden: true},
								{name: 'MorbusCrazyBaseDrugStart_Name', type: 'string', header: lang['naimenovanie'], width: 240},
								{name: 'CrazyDrugReceptType_id', type: 'string', hidden: true},
								{name: 'CrazyDrugReceptType_Name', type: 'string', header: lang['tip_priema'], width: 240},
								{name: 'MorbusCrazyBaseDrugStart_Age', type: 'int', header: lang['chislo_polnyih_let'], width: 120}
							],
							title: lang['vozrast_nachala_upotrebleniya_psihoaktivnyih_sredstv'],
							toolbar: true
						}),
						new sw.Promed.ViewFrame({
							actions: [
								{name: 'action_add', handler: function() {
									this.openMorbusCrazyDrugWindow('add');
								}.createDelegate(this)},
								{name: 'action_edit', handler: function() {
									this.openMorbusCrazyDrugWindow('edit');
								}.createDelegate(this)},
								{name: 'action_view', handler: function() {
									this.openMorbusCrazyDrugWindow('view');
								}.createDelegate(this)},
								{name: 'action_delete', handler: function() {
									this.deleteGridSelectedRecord('CSEW_MorbusCrazyDrug', 'MorbusCrazyDrug_id');
								}.createDelegate(this)},
								{name: 'action_print'}
							],
							autoExpandColumn: 'autoexpand',
							editformclassname: 'swMorbusCrazyDrugWindow',
							autoExpandMin: 150,
							autoLoadData: false,
							border: false,
							id: 'CSEW_MorbusCrazyDrug',
							paging: false,
							style: 'margin-bottom: 10px',
							stringfields: [
								{name: 'MorbusCrazyDrug_id', type: 'int', header: 'ID', key: true},
								{name: 'RecordStatus_Code', type: 'int', hidden: true},
								{name: 'CrazyDrugType_id', type: 'string', hidden: true},
								{name: 'CrazyDrugType_Name', type: 'string', header: lang['vid_veschestva'], width: 240},
								{name: 'MorbusCrazyDrug_Name', type: 'string', header: lang['naimenovanie'], width: 240},
								{name: 'CrazyDrugReceptType_id', type: 'string', hidden: true},
								{name: 'CrazyDrugReceptType_Name', type: 'string', header: lang['tip_priema'], width: 240}
							],
							title: lang['upotreblenie_psihoaktivnyih_veschestv_na_moment_gospitalizatsii'],
							toolbar: true
						}),
						{
							fieldLabel: lang['ispolzovanie_chujih_shpritsov_igl_prisposobleniy_v_techenie_poslednego_goda'],
							hiddenName: 'MorbusCrazySection_IsAnotherSyringe',
							xtype: 'swyesnocombo',
							anchor: '60%'
						}, {
							fieldLabel: lang['projivanie_s_potrebitelem_psihoaktivnyih_sredstv'],
							hiddenName: 'MorbusCrazySection_IsLiveWithJunkie',
							xtype: 'swyesnocombo',
							anchor: '60%'
						}, {
							fieldLabel: lang['poluchennyiy_obyem_narkologicheskoy_pomoschi_v_dannom_uchrejdenii'],
							hiddenName: 'CrazyDrugVolumeType_id',
							xtype: 'swcrazydrugvolumetypecombo',
							anchor: '60%'
						}]
				})
			]
		});
        this.form = this.CrazySpecificsForm;
		
		Ext.apply(this, {
			keys:[],
			buttons: [{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[
				this.CrazySpecificsForm
			]
		});
		sw.Promed.swCrazySpecificsTestWindow.superclass.initComponent.apply(this, arguments);
	},
	maximizable: true,
	maximized: true,
	modal: false,
	plain: true,
	resizable: false,
	show: function(params) {
		
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg: lang['zagruzka']});
		loadMask.show();
		
		if (params) {
            if (params.Morbus_id) {
                this.Morbus_id = params.Morbus_id;
            } else {
                //todo сделать нормальное сообщение
                alert(lang['ne_ukazan_obyazatelnyiy_parametr_-_identifikator_zabolevaniya']);
                return false;
            }
            if (params.Evn_id) {
                this.Evn_id = params.Evn_id;
            } else {
                //todo сделать нормальное сообщение
                alert(lang['ne_ukazan_obyazatelnyiy_parametr_-_identifikator_uchetnogo_dokumenta']);
                return false;
            }
        } else {
            //todo сделать нормальное сообщение
            alert(lang['ne_ukazanyi_vhodnyie_dannyie']);
            return false;
        }
		
		Ext.Ajax.request({
			failure: function(response, options) {
				sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
			},
			params: {
				Evn_id: this.Evn_id, 
				Morbus_id: this.Morbus_id
			},
			success: function(response, options) {
				var result = Ext.util.JSON.decode(response.responseText);
				var form = this.form.getForm();
				log(result[0]);
				form.setValues(result[0]);
				
				if (result[0]['MorbusCrazyForceTreat']) {
					Ext.getCmp('CSEW_MorbusCrazyForceTreat').getGrid().getStore().loadData(result[0]['MorbusCrazyForceTreat']);
				}
				
				if (result[0]['MorbusCrazyBaseDrugStart']) {
					Ext.getCmp('CSEW_MorbusCrazyBaseDrugStart').getGrid().getStore().loadData(result[0]['MorbusCrazyBaseDrugStart']);
				}
				
				if (result[0]['MorbusCrazyDrug']) {
					Ext.getCmp('CSEW_MorbusCrazyDrug').getGrid().getStore().loadData(result[0]['MorbusCrazyDrug']);
				}

				var diag_nid = form.findField('Diag_nid').getValue();
				if (diag_nid != null && diag_nid.toString().length > 0) {
					form.findField('Diag_nid').getStore().load({
						callback: function() {
							form.findField('Diag_nid').getStore().each(function(record) {
								if (record.get('Diag_id') == diag_nid) {
									form.findField('Diag_nid').fireEvent('select', form.findField('Diag_nid'), record, 0);
								}
							});
						},
						params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_nid}
					});
				}
				
				var diag_sid = form.findField('Diag_sid').getValue();
				if (diag_sid != null && diag_sid.toString().length > 0) {
					form.findField('Diag_sid').getStore().load({
						callback: function() {
							form.findField('Diag_sid').getStore().each(function(record) {
								if (record.get('Diag_id') == diag_sid) {
									form.findField('Diag_sid').fireEvent('select', form.findField('Diag_sid'), record, 0);
								}
							});
						},
						params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_sid}
					});
				}	
				
				loadMask.hide();			

			}.createDelegate(this),
			url: '/?c=MorbusCrazySpecifics&m=load'
		});
		sw.Promed.swCrazySpecificsTestWindow.superclass.show.apply(this, arguments);
		
	},
	title: lang['testovaya_forma_spetsifik'],
	width: 800
});