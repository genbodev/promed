/**
 * swMorbusOnkoEditWindow - модальное окно специфики по Онкологии
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      MorbusOnko
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 * @comment
 */

Ext6.define('common.MorbusOnko.swMorbusOnkoEditWindow', {
	/* свойства */
	requires: [
		'common.EMK.PersonInfoPanel',
		'common.MorbusOnko.OnkoDiagConfTypePanel',
	],
	alias: 'widget.swMorbusOnkoEditWindow',
    autoShow: false,
	closable: true,
	cls: 'arm-window-new emkd',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
    header: true,
	modal: true,
	layout: 'form',
	refId: 'MorbusOnkoeditsw',
	swMaximized: true,
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Специфика / Онкология',
	width: 1150,
	maxHeight: 800,
	noTaskBarButton: true,
	listeners: {
		resize: function (el, width, height, oldWidth, oldHeight) {
			el.LeftPanel.setHeight(height-170); // где 170 - это высота title + personInfo + tbar + bbar
		}
	},

	/* методы */
	save: function (opt) {
		if (!opt) opt = {};
		var
			base_form = this.FormPanel.getForm(),
			win = this;

		if ( !base_form.isValid() ) {
			sw.swMsg.alert('Ошибка', 'Не все поля формы заполнены корректно');
			return false;
		}

		win.mask(LOAD_WAIT_SAVE);
		
        var params = {};
        var OnkoDiagConfTypes = this.OnkoDiagConfTypePanel.getValues();
        params.OnkoDiagConfTypes = (OnkoDiagConfTypes.length > 1 ? OnkoDiagConfTypes.join(',') : OnkoDiagConfTypes);

		base_form.submit({
			params: params,
			success: function(form, action) {
				win.unmask();

				if ( !Ext6.isEmpty(action.result.Error_Msg) ) {
					sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
					return false;
				}

				win.callback();
				if (!opt.doNotHide) {
					win.hide();
				} else {
					win.reloadOnkoDiagConfTypes();
				}
			},
			failure: function(form, action) {
				win.unmask();
			}
		});
	},
	
    reloadOnkoDiagConfTypes: function() {
		if (getRegionNick() != 'perm') return false;
        var OnkoDiagConfTypes = this.OnkoDiagConfTypePanel.getValues();
		this.OnkoDiagConfTypePanel.setValues(OnkoDiagConfTypes);
	},
	
    checkDiagAttribPanel: function() {
		var me = this;
		var base_form = this.FormPanel.getForm();
		var vals = [];
		var n = '';
		for(var i=1;i<8;i++){
			if(i>1){
				n = i;
			}
			el = base_form.findField('OnkoDiagConfType_id'+n);
			if(el){
				var val = el.getValue();
				if(val) vals.push(val);
			}
		}
		var diagAttribPanel = this.DiagPanel.getComponent(this.id + 'diagAttribPanel');
		if ((1).inlist(vals) || ((2).inlist(vals) && getRegionNick() == 'perm')) {
			diagAttribPanel.show();
		} else {
			diagAttribPanel.hide();
		}
	},
	
    setDiagAttribType: function(value) {
		var me = this;
		var base_form = this.FormPanel.getForm();
		var DiagAttribTypeCombo = base_form.findField('DiagAttribType_id');

		if (value == 2 && getRegionNick() == 'perm' && DiagAttribTypeCombo.getValue() != 3) {
			DiagAttribTypeCombo.setValue(3);
			DiagAttribTypeCombo.fireEvent('change', DiagAttribTypeCombo, DiagAttribTypeCombo.getValue());
		}
	},
	
    filterDiagnosisResultDiagLinkStore: function() {
		var me = this;
		var base_form = this.FormPanel.getForm();
		var Diag_id = base_form.findField('Diag_id').getValue();
		
		me.arrDiagResult = [];
		
		me.DiagnosisResultDiagLinkStore.clearFilter();
		me.DiagnosisResultDiagLinkStore.filterBy(function(rec) {
			return (
				(Ext.isEmpty(rec.get('DiagnosisResultDiagLink_endDate')) || rec.get('DiagnosisResultDiagLink_endDate') >= me.checkDate) &&
				(Ext.isEmpty(rec.get('DiagnosisResultDiagLink_begDate')) || rec.get('DiagnosisResultDiagLink_begDate') <= me.checkDate)
			);
		});

		if ( !Ext.isEmpty(Diag_id) ) {
			me.DiagnosisResultDiagLinkStore.each(function(rec) {
				if ( rec.get('Diag_id') == Diag_id && !Ext.isEmpty(rec.get('DiagResult_id')) && !rec.get('DiagResult_id').inlist(me.arrDiagResult) ) {
					me.arrDiagResult.push(rec.get('DiagResult_id'));
				}
			});
		}
		if (me.arrDiagResult.length == 0) {
			me.DiagnosisResultDiagLinkStore.each(function(rec) {
				if ( !Ext.isEmpty(rec.get('Diag_id')) && !Ext.isEmpty(rec.get('DiagResult_id')) && !rec.get('DiagResult_id').inlist(me.arrDiagResult) ) {
					me.arrDiagResult.push(rec.get('DiagResult_id'));
				}
			});
		}
	},
	
	checkEvnOnkoNotifyExists: function() {
		var me = this;
		var base_form = this.FormPanel.getForm();
		
		Ext6.Ajax.request({
			url: '/?c=EvnOnkoNotify&m=getEvnOnkoNotifyList',
			params: {
				Person_id: me.Person_id
			},
			success: function(response) {
				var data = JSON.parse(response.responseText),
					newNotifyDiagId = base_form.findField('Diag_id').getValue(),
					addNotifyButton = me.queryById(me.id + 'addEvnNotify'),
					exist = false;
					
				for(var key in data) {
					if(newNotifyDiagId == data[key].Diag_id) {
						exist = true;
					}
				}
				if(exist || me.accessType == 'view') {
					addNotifyButton.disable();
				} else {
					addNotifyButton.enable();
				}
			}
		});
	},
	onSprLoad: function(arguments) {

		var me = this;
		var base_form = this.FormPanel.getForm();
		
		base_form.reset();
		me.isLoading = true;
		
		me.userMedStaffFact = arguments[0].userMedStaffFact || {};
		me.Person_id = arguments[0].Person_id;
		me.Server_id = arguments[0].Server_id;
		me.PersonEvn_id = arguments[0].PersonEvn_id;
		me.Morbus_id = arguments[0].Morbus_id || null;
		me.MorbusOnko_pid = arguments[0].MorbusOnko_pid || null;
		me.MorbusOnkoVizitPLDop_id = arguments[0].MorbusOnkoVizitPLDop_id || null;
		me.MorbusOnkoLeave_id = arguments[0].MorbusOnkoLeave_id || null;
		me.EvnDiagPLSop_id = arguments[0].EvnDiag_id || null;
		me.callback = arguments[0].callback || Ext6.emptyFn;
		me.Evn_id = me.MorbusOnko_pid;
		me.linkStore = [];
		
		this.PersonInfoPanel.load({
			Person_id: me.Person_id,
			Server_id: me.Server_id,
			PersonEvn_id: me.PersonEvn_id,
			noToolbar: true
		});
		
		if ( this.OnkoMLinkStore.getCount() == 0 ) {
			this.OnkoMLinkStore.load();
		}

		if ( this.OnkoNLinkStore.getCount() == 0 ) {
			this.OnkoNLinkStore.load();
		}

		if ( this.OnkoTLinkStore.getCount() == 0 ) {
			this.OnkoTLinkStore.load();
		}

		if ( this.TumorStageLinkStore.getCount() == 0 ) {
			this.TumorStageLinkStore.load();
		}

		if ( this.DiagnosisResultDiagLinkStore.getCount() == 0 ) {
			this.DiagnosisResultDiagLinkStore.load();
		}
		
		this.LeftPanel.setActiveTab(0);
		
		var loadMask = new Ext6.LoadMask(me, {msg: LOADING_MSG});
		loadMask.show();
		base_form.load({
			url: '/?c=MorbusOnkoSpecifics&m=loadMorbusSpecific',
			params: {
				Morbus_id: me.Morbus_id,
				MorbusOnko_pid: me.MorbusOnko_pid,
				EvnDiagPLSop_id: me.EvnDiagPLSop_id
			},
			success: function (form, action) {
				loadMask.hide();
				if (action.response && action.response.responseText) {
					var data = Ext6.JSON.decode(action.response.responseText);
					
					me.Morbus_id = data[0].Morbus_id;
					me.MorbusOnko_id = data[0].MorbusOnko_id;
					me.MorbusOnkoBase_id = data[0].MorbusOnkoBase_id;
					me.MorbusOnkoVizitPLDop_id = data[0].MorbusOnkoVizitPLDop_id;
					me.MorbusOnkoLeave_id = data[0].MorbusOnkoLeave_id;
					me.Evn_disDate = data[0].Evn_disDate;
					me.checkDate = Date.parseDate(!Ext6.isEmpty(me.Evn_disDate) ? me.Evn_disDate : getGlobalOptions().date, 'd.m.Y');
					me.accessType = data[0].accessType;
					
					//me.filterDiagnosisResultDiagLinkStore();
					me.checkEvnOnkoNotifyExists();
					/*
					if(data[0].OnkoDiagConfTypes){
						data[0].OnkoDiagConfTypes = data[0].OnkoDiagConfTypes.split(',');
						me.OnkoDiagConfTypePanel.setValues(data[0].OnkoDiagConfTypes);
					} else {
						me.OnkoDiagConfTypePanel.setValues([null]);
					}
					
					base_form.findField('DiagAttribType_id').fireEvent('change', base_form.findField('DiagAttribType_id'), base_form.findField('DiagAttribType_id').getValue());
					*/
					
					base_form.findField('OnkoTreatment_id').getStore().clearFilter();
					base_form.findField('OnkoTreatment_id').getStore().filterBy(function(rec) {
						return (
							(Ext.isEmpty(rec.get('OnkoTreatment_begDate')) || rec.get('OnkoTreatment_begDate') <= me.checkDate) && 
							(Ext.isEmpty(rec.get('OnkoTreatment_endDate')) || rec.get('OnkoTreatment_endDate') >= me.checkDate)
						);
					});

					me.setFieldsAllowBlank();
					me.isLoading = false;
					base_form.isValid();

					if (me.accessType == 'view') {
						me.setAccessType(true);
					} else {
						me.setAccessType(false);
					}
				}
			}
		});
		
	},

	show: function() {
		this.callParent(arguments);
	},
	setAccessType: function(accessType) {
		this.DiagPanel.query('field, button').forEach(function(c){
			if (c.name != "MorbusOnko_NumTumor") {
				c.setDisabled(accessType);
			}
		});
		this.OnkoDiagConfTypePanel.query('field, button').forEach(function(c){c.setDisabled(accessType);});
		this.query('[cls=buttonAccept]').forEach(function(c){c.setDisabled(accessType);});
		this.ToolPanel.down('[itemId='+ this.id + 'buttonAdd]').setDisabled(accessType);
	},
	addEvnNotify: function() {
		var me = this;
		checkEvnNotify({
			Evn_id: me.MorbusOnko_pid
			,EvnDiagPLSop_id: me.EvnDiagPLSop_id
			,MorbusType_SysNick: 'onko'
			,callback: function(success) {
				me.LeftPanel.setActiveItem('EvnNotify');
				me.loadTabGrig('EvnNotify');
				me.checkEvnOnkoNotifyExists();
			}
		});
	},
	
	deleteEvent: function(object) {
		var win = this;
		var object_id = null;
		var error = langs('При удалении возникли ошибки');
		var question = langs('Удалить?');
		var params = {};
		var url = '';
		var onSuccess = function() {
			win.loadTabGrig(object);
		};
		url = '/?c=Utils&m=ObjectRecordDelete';
		params['obj_isEvn'] = 'false';
		
		var grid = win[object+'Grid'];
		if (!grid) return false;
		var record = grid.getStore().getAt(grid.recordMenu.rowIndex);
		if (!record) return false;
		
		switch(object) {
			case 'OnkoConsult':
			case 'MorbusOnkoDrug':
			case 'MorbusOnkoSpecTreat':
			case 'MorbusOnkoLink':
			case 'MorbusOnkoRefusal':
			case 'MorbusOnkoBasePersonState':
			case 'MorbusOnkoBasePS':
				object_id = record.get(object + '_id');
				break;
			default:
				object_id = record.get('EvnUsluga_id');
				question = langs('Удалить выбранное лечение?');
				break;
		}
		
		if (!object_id) return false;
		
		switch(object) {
			case 'MorbusOnkoBasePersonState':
				params['MorbusOnkoBasePersonState_id'] = object_id;
				url = '/?c=MorbusOnkoBasePersonState&m=destroy';
				break;
			case 'MorbusOnkoSopDiag':
				url = '/?c=MorbusOnkoSpecifics&m=deleteMorbusOnkoSopDiag';
				params['object'] = 'MorbusOnkoSopDiag'; 
				break;
			case 'MorbusOnkoDrug':
				url = '/?c=MorbusOnkoDrug&m=destroy';
				params['MorbusOnkoDrug_id'] = object_id;
				question = langs('Удалить препарат?');
				break;
			case 'MorbusOnkoSpecTreat':
				url = '/?c=MorbusOnkoSpecifics&m=deleteMorbusOnkoSpecTreat';
				params['object'] = 'MorbusOnkoSpecTreat'; 
				break;
			case 'MorbusOnkoLink':
				params['object'] = 'MorbusOnkoLink';
				break;
			case 'MorbusOnkoRefusal':
				url = '/?c=MorbusOnkoSpecifics&m=deleteMorbusOnkoRefusal';
				params['object'] = 'MorbusOnkoRefusal'; 
				break;
			case 'MorbusOnkoBasePS': params['object'] = 'MorbusOnkoBasePS'; break;
			case 'MorbusOnkoRadTer': params['object'] = 'EvnUslugaOnkoBeam'; params['obj_isEvn'] = 'true'; break;
			case 'MorbusOnkoHirTer': params['object'] = 'EvnUslugaOnkoSurg'; params['obj_isEvn'] = 'true'; break;
			case 'MorbusOnkoChemTer': params['object'] = 'EvnUslugaOnkoChem'; params['obj_isEvn'] = 'true'; break;
			case 'MorbusOnkoGormTer': params['object'] = 'EvnUslugaOnkoGormun'; params['obj_isEvn'] = 'true'; break;
			case 'MorbusOnkoNonSpecTer': params['object'] = 'EvnUslugaOnkoNonSpec'; params['obj_isEvn'] = 'true'; break;
			case 'OnkoConsult': params['object'] = 'OnkoConsult'; question = langs('Удалить выбранные сведения?'); break;
		}
		params['id'] = object_id;
		
		sw.swMsg.show({
			buttons: sw.swMsg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					win.mask(LOAD_WAIT_DELETE);
					Ext6.Ajax.request({
						failure: function(response, options) {
							win.unmask();
							sw.swMsg.alert(langs('Ошибка'), error);
						},
						params: params,
						success: function(response, options) {
							win.unmask();
							var response_obj = Ext6.util.JSON.decode(response.responseText);
							if ( response_obj.success == false ) {
								sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : error);
							} else {
								onSuccess({});
							}
						}.createDelegate(this),
						url: url
					});
				}
			}.createDelegate(this),
			icon: Ext6.MessageBox.QUESTION,
			msg: question,
			title: langs('Вопрос')
		});
        return true;
	},
	
	openMorbusOnkoSpecificForm: function(type, options, action) {
		var params = {},
			me = this,
			wnd = false,
			id = null,
			tabId = null,
			base_form = this.FormPanel.getForm(),
			Diag_id = base_form.findField('Diag_id').getValue();
			
		var grid = me[type+'Grid'];
		if (!grid) return false;
		
		params.Evn_id = me.MorbusOnko_pid;
		params.Morbus_id = me.Morbus_id;
		params.MorbusOnko_id = me.MorbusOnko_id;
		params.MorbusOnkoVizitPLDop_id = me.MorbusOnkoVizitPLDop_id;
		params.MorbusOnkoLeave_id = me.MorbusOnkoLeave_id;
		params.EvnVizitPL_id = me.MorbusOnko_pid;
        params.action = action;
        params.formParams = {
			Person_id: me.Person_id,
			Server_id: me.Server_id,
			PersonEvn_id: me.PersonEvn_id,
            Morbus_id: me.Morbus_id,
			MorbusOnko_id: me.MorbusOnko_id,
            MorbusOnkoBase_id: me.MorbusOnkoBase_id,
			MorbusOnkoVizitPLDop_id: me.MorbusOnkoVizitPLDop_id,
			MorbusOnkoLeave_id: me.MorbusOnkoLeave_id,
			MedPersonal_id: me.userMedStaffFact.MedPersonal_id || getGlobalOptions().CurMedPersonal_id,
			MedStaffFact_id: me.userMedStaffFact.MedStaffFact_id || getGlobalOptions().CurMedStaffFact_id,
            Lpu_id: getGlobalOptions().lpu_id,
            Lpu_uid: getGlobalOptions().lpu_id
		};
		
		if (action != 'add') {
			var record = grid.getStore().getAt(grid.recordMenu.rowIndex);
			if (!record) return false;
			switch(type) {
				case 'OnkoConsult':
				case 'MorbusOnkoDrug':
				case 'MorbusOnkoSpecTreat':
				case 'MorbusOnkoLink':
				case 'MorbusOnkoRefusal':
				case 'MorbusOnkoBasePersonState':
				case 'MorbusOnkoBasePS':
					id = record.get(type + '_id');
					break;
				default:
					id = record.get('EvnUsluga_id');
					break;
			}
			if (!id) return false;
		}
		
		switch(type) {
			case 'OnkoConsult':
				wnd = 'swOnkoConsultEditWindowExt6';
				params.OnkoConsult_id = id;
				params.formParams.OnkoConsult_id = id;
				break;
			case 'MorbusOnkoDrug':
				wnd = 'swMorbusOnkoDrugWindowExt6';
				params.MorbusOnkoDrug_id = id;
				params.formParams.MorbusOnkoDrug_id = id;
				break;
			case 'MorbusOnkoSpecTreat':
				wnd = 'swMorbusOnkoSpecTreatEditWindowExt6';
				params.MorbusOnkoSpecTreat_id = id;
				params.formParams.MorbusOnkoSpecTreat_id = id;
				break;
			case 'MorbusOnkoLink':
				wnd = 'swMorbusOnkoLinkDiagnosticsWindow';
				params.MorbusOnkoLink_id = id;
				params.formParams.MorbusOnkoLink_id = id;
				params.formParams.Diag_id = Diag_id;
				params.formParams.HistologicReasonType_id = base_form.findField('HistologicReasonType_id').getValue();
				params.formParams.Evn_disDate = me.Evn_disDate ? me.Evn_disDate : getGlobalOptions().date;
				break;
			case 'MorbusOnkoRefusal':
				wnd = 'swMorbusOnkoRefusalWindowExt6';
				params.isRefusal = options.isRefusal;
				params.MorbusOnkoRefusal_id = id;
				params.formParams.MorbusOnkoRefusal_id = id;
				break;
			case 'MorbusOnkoChemTer':
				wnd = 'swEvnUslugaOnkoChemEditWindowExt6';
				params.EvnUslugaOnkoChem_id = id;
				params.formParams.EvnUslugaOnkoChem_id = id;
                params.formParams.EvnUslugaOnkoChem_pid = me.MorbusOnko_pid;
				break;
			case 'MorbusOnkoRadTer':
				wnd = 'swEvnUslugaOnkoBeamEditWindowExt6';
				params.EvnUslugaOnkoBeam_id = id;
				params.formParams.EvnUslugaOnkoBeam_id = id;
                params.formParams.EvnUslugaOnkoBeam_pid = me.MorbusOnko_pid;
				break;
			case 'MorbusOnkoGormTer':
				wnd = 'swEvnUslugaOnkoGormunEditWindowExt6';
				params.EvnUslugaOnkoGormun_id = id;
				params.formParams.EvnUslugaOnkoGormun_id = id;
                params.formParams.EvnUslugaOnkoGormun_pid = me.MorbusOnko_pid;
				break;
			case 'MorbusOnkoHirTer':
				wnd = 'swEvnUslugaOnkoSurgEditWindowExt6';
				params.EvnUslugaOnkoSurg_id = id;
				params.formParams.EvnUslugaOnkoSurg_id = id;
                params.formParams.EvnUslugaOnkoSurg_pid = me.MorbusOnko_pid;
				break;
			case 'MorbusOnkoNonSpecTer':
				wnd = 'swEvnUslugaOnkoNonSpecEditWindowExt6';
				params.EvnUslugaOnkoNonSpec_id = id;
				params.formParams.EvnUslugaOnkoNonSpec_id = id;
                params.formParams.EvnUslugaOnkoNonSpec_pid = me.MorbusOnko_pid;
				break;
			case 'MorbusOnkoBasePersonState':
				wnd = 'swMorbusOnkoBasePersonStateWindowExt6';
                params.formParams.Evn_id = null;
				params.formParams.MorbusOnkoBasePersonState_id = id;
				break;
			case 'MorbusOnkoBasePS':
				wnd = 'swMorbusOnkoBasePSWindowExt6';
                params.formParams.Evn_id = null;
				params.formParams.MorbusOnkoBasePS_id = id;
				break;
		}
		
		params.callback = function(success) {
			me.LeftPanel.setActiveItem(type);
			var tab = me.LeftPanel.getActiveTab();
			me.loadTabGrig(tab.itemId);
		};
		
		if (!wnd) {
			return false;
		}
			
		getWnd(wnd).show(params);
	},
	
	loadTabGrig: function(tabid) {
		var me = this;
		var eu_class = null;
		switch(tabid) {
			case 'OnkoConsult':
				me.OnkoConsultGrid.getStore().load({
					params: {
						'Lpu_id': null,
						'Morbus_id': me.Morbus_id,
						'MorbusOnkoVizitPLDop_id': me.MorbusOnkoVizitPLDop_id,
						'MorbusOnkoLeave_id': me.MorbusOnkoLeave_id
					}
				});
				break;
			case 'MorbusOnkoDrug':
				me.MorbusOnkoDrugGrid.getStore().load({
					params: {
						'MorbusOnko_pid': me.Evn_id,
						'Morbus_id': me.Morbus_id,
						'MorbusOnkoVizitPLDop_id': me.MorbusOnkoVizitPLDop_id,
						'MorbusOnkoLeave_id': me.MorbusOnkoLeave_id
					}
				});
				break;
			case 'MorbusOnkoSpecTreat':
				me.MorbusOnkoSpecTreatGrid.getStore().load({
					params: {
						'Lpu_id': null,
						'Evn_id': me.Evn_id,
						'Morbus_id': me.Morbus_id,
						'MorbusOnkoVizitPLDop_id': me.MorbusOnkoVizitPLDop_id,
						'MorbusOnkoLeave_id': me.MorbusOnkoLeave_id
					}
				});
				break;
			case 'MorbusOnkoLink':
				me.MorbusOnkoLinkGrid.getStore().load({
					params: {
						'Lpu_id': null,
						'Evn_id': me.Evn_id,
						'Morbus_id': me.Morbus_id,
						'MorbusOnkoVizitPLDop_id': me.MorbusOnkoVizitPLDop_id,
						'MorbusOnkoLeave_id': me.MorbusOnkoLeave_id,
						'MorbusOnkoDiagPLStom_id': me.MorbusOnkoDiagPLStom_id
					}
				});
				break;
			case 'MorbusOnkoRefusal':
				me.MorbusOnkoRefusalGrid.getStore().load({
					params: {
						'Lpu_id': null,
						'Evn_id': me.Evn_id,
						'Morbus_id': me.Morbus_id,
						'MorbusOnkoVizitPLDop_id': me.MorbusOnkoVizitPLDop_id,
						'MorbusOnkoLeave_id': me.MorbusOnkoLeave_id
					}
				});
				break;
			case 'DrugTherapyScheme':
				me.DrugTherapySchemeGrid.getStore().load({
					params: {
						'EvnSection_id': me.Evn_id,
						'isForEMK': true
					}
				});
				break;
			case 'MorbusOnkoChemTer':
				eu_class = 'EvnUslugaOnkoChem';
				break;
			case 'MorbusOnkoRadTer':
				eu_class = 'EvnUslugaOnkoBeam';
				break;
			case 'MorbusOnkoGormTer':
				eu_class = 'EvnUslugaOnkoGormun';
				break;
			case 'MorbusOnkoHirTer':
				eu_class = 'EvnUslugaOnkoSurg';
				break;
			case 'MorbusOnkoNonSpecTer':
				eu_class = 'EvnUslugaOnkoNonSpec';
				break;
			case 'EvnNotify':
				me.EvnNotifyGrid.getStore().load({
					params: {
						'Morbus_id': me.Morbus_id,
						'Evn_id': me.Evn_id
					}
				});
				break;
			case 'MorbusOnkoBasePersonState':
				me.MorbusOnkoBasePersonStateGrid.getStore().load({
					params: {
						'Morbus_id': me.Morbus_id,
						'Evn_id': me.Evn_id
					}
				});
				break;
			case 'MorbusOnkoBasePS':
				me.MorbusOnkoBasePSGrid.getStore().load({
					params: {
						'Morbus_id': me.Morbus_id,
						'Evn_id': me.Evn_id
					}
				});
				break;
		}
		if (!!eu_class) {
			me[tabid+'Grid'].getStore().load({
				params: {
					'class': eu_class,
					'Lpu_id': null,
					'byMorbus': 1,
					'Morbus_id': me.Morbus_id,
					'EvnEdit_id': me.Evn_id
				}
			});
		}
	},

	setFieldsAllowBlank: function() {
		var
			base_form = this.FormPanel.getForm(),
			me = this;

		var
			field,
			fieldsList = ['OnkoM', 'OnkoN', 'OnkoT', 'TumorStage'],
			linkStore,
			linkStoreWithDiagAndSpr = {
				'OnkoM': [],
				'OnkoN': [],
				'OnkoT': [],
				'TumorStage': []
			},
			linkStoreWithoutDiag = {
				'OnkoM': [],
				'OnkoN': [],
				'OnkoT': [],
				'TumorStage': []
			},
			linkStoreWithoutSpr = {
				'OnkoM': [],
				'OnkoN': [],
				'OnkoT': [],
				'TumorStage': []
			};

		//var DiagAttribTypeId = base_form.findField('DiagAttribType_id').getValue();
		var Diag_id = base_form.findField('Diag_id').getValue();
		var OnkoTreatment_id = base_form.findField('OnkoTreatment_id').getValue();
		var OnkoTreatment_Code = base_form.findField('OnkoTreatment_id').getFieldValue('OnkoTreatment_Code');
		var Person_Age = me.PersonInfoPanel.getFieldValue('Person_Age');

		if ( getRegionNick() != 'kz' ) {
			
			for ( var i in fieldsList ) {
				field = fieldsList[i];

				if ( typeof field != 'string' ) {
					continue;
				}

				linkStore = me[field + 'LinkStore'];

				if ( !linkStore ) {
					continue;
				}

				linkStore.each(function(rec) {
					if (
						(Ext6.isEmpty(rec.get(field + 'Link_begDate')) || rec.get(field + 'Link_begDate') <= me.checkDate)
						&& (Ext6.isEmpty(rec.get(field + 'Link_endDate')) || rec.get(field + 'Link_endDate') >= me.checkDate)
					) {
						if ( !Ext6.isEmpty(rec.get('Diag_id')) && rec.get('Diag_id') == Diag_id ) {
							if ( !Ext6.isEmpty(rec.get(field + '_fid')) ) {
								linkStoreWithDiagAndSpr[field].push(rec.get(field + 'Link_id'));
							}
							else {
								linkStoreWithoutSpr[field].push(rec.get('Diag_id'));
							}
						}
						else if ( Ext6.isEmpty(rec.get('Diag_id')) && !Ext6.isEmpty(rec.get(field + '_fid')) ) {
							linkStoreWithoutDiag[field].push(rec.get(field + 'Link_id'));
						}
					}
				});
				
				base_form.findField(field + '_fid').getStore().clearFilter();
				if (linkStoreWithDiagAndSpr[field].length) {
					me.linkStore[field] = linkStoreWithDiagAndSpr[field];
					base_form.findField(field + '_fid').getStore().filterBy(function(rec) {
						return rec.get(field + '_id') && rec.get(field + 'Link_id').inlist(linkStoreWithDiagAndSpr[field]);
					});
				} else if (linkStoreWithoutDiag[field].length > 0)  {
					me.linkStore[field] = linkStoreWithoutDiag[field];
					base_form.findField(field + '_fid').getStore().filterBy(function(rec) {
						return rec.get(field + '_id') && rec.get(field + 'Link_id').inlist(linkStoreWithoutDiag[field]);
					});
				}
				base_form.findField(field + '_fid').setValue(base_form.findField(field + '_fid').getValue());
				base_form.findField(field + '_fid').fireEvent('change', base_form.findField(field + '_fid'), base_form.findField(field + '_fid').getValue());
			}
		
			if ( (linkStoreWithDiagAndSpr.OnkoT.length > 0 || (linkStoreWithoutDiag.OnkoT.length > 0 && linkStoreWithoutSpr.OnkoT.length == 0)) && OnkoTreatment_Code === '0' && Person_Age >= 18 ) {
				base_form.findField('OnkoT_fid').setAllowBlank(false);
			}
			else {
				base_form.findField('OnkoT_fid').setAllowBlank(true);
			}

			if ( (linkStoreWithDiagAndSpr.OnkoN.length > 0 || (linkStoreWithoutDiag.OnkoN.length > 0 && linkStoreWithoutSpr.OnkoN.length == 0)) && OnkoTreatment_Code === '0' && Person_Age >= 18 ) {
				base_form.findField('OnkoN_fid').setAllowBlank(false);
			}
			else {
				base_form.findField('OnkoN_fid').setAllowBlank(true);
			}

			if ( (linkStoreWithDiagAndSpr.OnkoM.length > 0 || (linkStoreWithoutDiag.OnkoM.length > 0 && linkStoreWithoutSpr.OnkoM.length == 0)) && OnkoTreatment_Code === '0' && Person_Age >= 18 ) {
				base_form.findField('OnkoM_fid').setAllowBlank(false);
			}
			else {
				base_form.findField('OnkoM_fid').setAllowBlank(true);
			}

			if ( !Ext.isEmpty(OnkoTreatment_id) && OnkoTreatment_Code != 5 && OnkoTreatment_Code != 6 ) {
				base_form.findField('TumorStage_fid').setAllowBlank(false);
			}
			else {
				base_form.findField('TumorStage_fid').setAllowBlank(true);
			}
		}

		/*
		var OnkoDiagConfTypes = [], n = '';
		for(var i=1;i<8;i++){
			if(i>1){
				n = i;
			}
			el = base_form.findField('OnkoDiagConfType_id'+n);
			if(el){
				var val = el.getValue();
				if(val) OnkoDiagConfTypes.push(val);
			}
		}


		var allow = !(
			(1).inlist(OnkoDiagConfTypes) &&
			!base_form.findField('HistologicReasonType_id').getValue()
		);
		
		base_form.findField('MorbusOnko_takeDT').setAllowBlank(allow || getRegionNick() == 'kareliya');
		base_form.findField('DiagAttribType_id').setAllowBlank(allow);
		base_form.findField('DiagResult_id').setAllowBlank(allow);
		base_form.findField('DiagAttribDict_id').setAllowBlank(allow);
		*/
		
		base_form.findField('MorbusOnko_histDT').setAllowBlank(!base_form.findField('HistologicReasonType_id').getValue());
	},

	DiagnosisResultDiagLinkStore: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'DiagnosisResultDiagLink_id', type: 'int'},
			{name: 'Diag_id', type: 'int'},
			{name: 'DiagResult_id', type: 'int'},
			{name: 'DiagnosisResultDiagLink_begDate', type: 'date', mapping: 'DiagnosisResultDiagLink_begDate', dateFormat: 'd.m.Y'},
			{name: 'DiagnosisResultDiagLink_endDate', type: 'date', mapping: 'DiagnosisResultDiagLink_endDate', dateFormat: 'd.m.Y'},
			{name: 'DiagAttribDict_id', type: 'int'},
			{name: 'DiagAttribType_id', type: 'int'}
		],
		key: 'DiagnosisResultDiagLink_id',
		url: '/?c=MorbusOnkoSpecifics&m=loadDiagnosisResultDiagLinkStore'
	}),
	OnkoMLinkStore: new Ext.db.AdapterStore({
		autoLoad: false,
		dbFile: 'Promed.db',
		fields: [
			{ name: 'OnkoMLink_id', type: 'int', mapping: 'OnkoMLink_id' },
			{ name: 'OnkoM_id', type: 'int', mapping: 'OnkoM_id' },
			{ name: 'Diag_id', type: 'int', mapping: 'Diag_id' },
			{ name: 'OnkoM_fid', type: 'int', mapping: 'OnkoM_fid' },
			{ name: 'OnkoMLink_begDate', type: 'date', mapping: 'OnkoMLink_begDate', dateFormat: 'd.m.Y' },
			{ name: 'OnkoMLink_endDate', type: 'date', mapping: 'OnkoMLink_endDate', dateFormat: 'd.m.Y' }
		],
		key: 'OnkoMLink_id',
		tableName: 'OnkoMLink'
	}),
	OnkoNLinkStore: new Ext.db.AdapterStore({
		autoLoad: false,
		dbFile: 'Promed.db',
		fields: [
			{ name: 'OnkoNLink_id', type: 'int', mapping: 'OnkoNLink_id' },
			{ name: 'OnkoN_id', type: 'int', mapping: 'OnkoN_id' },
			{ name: 'Diag_id', type: 'int', mapping: 'Diag_id' },
			{ name: 'OnkoN_fid', type: 'int', mapping: 'OnkoN_fid' },
			{ name: 'OnkoNLink_begDate', type: 'date', mapping: 'OnkoNLink_begDate', dateFormat: 'd.m.Y' },
			{ name: 'OnkoNLink_endDate', type: 'date', mapping: 'OnkoNLink_endDate', dateFormat: 'd.m.Y' }
		],
		key: 'OnkoNLink_id',
		tableName: 'OnkoNLink'
	}),
	OnkoTLinkStore: new Ext.db.AdapterStore({
		autoLoad: false,
		dbFile: 'Promed.db',
		fields: [
			{ name: 'OnkoTLink_id', type: 'int', mapping: 'OnkoTLink_id' },
			{ name: 'OnkoT_id', type: 'int', mapping: 'OnkoT_id' },
			{ name: 'Diag_id', type: 'int', mapping: 'Diag_id' },
			{ name: 'OnkoT_fid', type: 'int', mapping: 'OnkoT_fid' },
			{ name: 'OnkoTLink_begDate', type: 'date', mapping: 'OnkoTLink_begDate', dateFormat: 'd.m.Y' },
			{ name: 'OnkoTLink_endDate', type: 'date', mapping: 'OnkoTLink_endDate', dateFormat: 'd.m.Y' }
		],
		key: 'OnkoTLink_id',
		tableName: 'OnkoTLink'
	}),
	TumorStageLinkStore: new Ext.db.AdapterStore({
		autoLoad: false,
		dbFile: 'Promed.db',
		fields: [
			{ name: 'TumorStageLink_id', type: 'int', mapping: 'TumorStageLink_id' },
			{ name: 'TumorStage_id', type: 'int', mapping: 'TumorStage_id' },
			{ name: 'Diag_id', type: 'int', mapping: 'Diag_id' },
			{ name: 'TumorStage_fid', type: 'int', mapping: 'TumorStage_fid' },
			{ name: 'TumorStageLink_begDate', type: 'date', mapping: 'TumorStageLink_begDate', dateFormat: 'd.m.Y' },
			{ name: 'TumorStageLink_endDate', type: 'date', mapping: 'TumorStageLink_endDate', dateFormat: 'd.m.Y' }
		],
		key: 'TumorStageLink_id',
		tableName: 'TumorStageLink'
	}),

	/* конструктор */
    initComponent: function() {
		var me = this;

		this.PersonInfoPanel = Ext6.create('common.EMK.PersonInfoPanel', {
			region: 'north',
			buttonPanel: false,
			border: true,
			bodyStyle: 'border-width: 0 0 1px 0;',
			userMedStaffFact: this.userMedStaffFact,
			ownerWin: this,
			listeners: {
				resize: function (el, width, height, oldWidth, oldHeight) {
					el.ownerCt.LeftPanel.setHeight(el.ownerCt.getHeight() - height - 125); // где 125 - это высота title + tbar + bbar
				}
			}
		});
		
		this.OnkoDiagConfTypePanel = Ext6.create('common.MorbusOnko.OnkoDiagConfTypePanel', {
			objectName: 'OnkoDiagConfType',
			fieldLabelTitle: 'Метод подтверждения диагноза',
			win: this,
			width: 740,
			buttonAlign: 'left',
			buttonLeftMargin: 150,
			fieldWidth: 690,
			labelWidth: 200,
			onChange: function(newValue) {
				me.checkDiagAttribPanel();
				me.setDiagAttribType(newValue);
				me.setFieldsAllowBlank();
			},
			onLoad: function() {
				me.checkDiagAttribPanel();
				me.setFieldsAllowBlank();
			}
		});
		
		this.ToolPanel = Ext6.create('Ext6.Toolbar', {
			height: 45,
			region: 'north',
			border: false,
			userCls: 'packet-fast-exec-toolbar',
			padding: '6 10',
			style: {
				background: '#f5f5f5'
			},
			noWrap: true,
			right: 0,
			items: [
				'->',
				{
					text: 'Добавить',
					iconCls: 'panicon-add',
					itemId: me.id + 'buttonAdd',
					menu: [{
						text: 'Сведения о проведении консилиума',
						handler: function() {
							me.openMorbusOnkoSpecificForm('OnkoConsult', false, 'add');
						}
					}, {
						text: 'Данные о препаратах',
						handler: function() {
							me.openMorbusOnkoSpecificForm('MorbusOnkoDrug', false, 'add');
						}
					}, {
						text: 'Специальное лечение',
						handler: function() {
							me.openMorbusOnkoSpecificForm('MorbusOnkoSpecTreat', false, 'add');
						}
					}, {
						text: 'Диагностика',
						handler: function() {
							me.openMorbusOnkoSpecificForm('MorbusOnkoLink', false, 'add');
						}
					}, {
						text: 'Отказ от лечения',
						handler: function() {
							me.openMorbusOnkoSpecificForm('MorbusOnkoRefusal', {isRefusal: true}, 'add');
						}
					}, {
						text: 'Противопоказание к лечению',
						handler: function() {
							me.openMorbusOnkoSpecificForm('MorbusOnkoRefusal', {isRefusal: false}, 'add');
						}
					}, {
						text: 'Химиотерапевтическое лечение',
						handler: function() {
							me.openMorbusOnkoSpecificForm('MorbusOnkoChemTer', false, 'add');
						}
					}, {
						text: 'Лучевое лечение',
						handler: function() {
							me.openMorbusOnkoSpecificForm('MorbusOnkoRadTer', false, 'add');
						}
					}, {
						text: 'Гормоноиммунотерапевтическое лечение',
						handler: function() {
							me.openMorbusOnkoSpecificForm('MorbusOnkoGormTer', false, 'add');
						}
					}, {
						text: 'Хирургическое лечение',
						handler: function() {
							me.openMorbusOnkoSpecificForm('MorbusOnkoHirTer', false, 'add');
						}
					}, {
						text: 'Неспецифическое лечение',
						handler: function() {
							me.openMorbusOnkoSpecificForm('MorbusOnkoNonSpecTer', false, 'add');
						}
					}, {
						text: 'Контроль состояния',
						handler: function() {
							me.openMorbusOnkoSpecificForm('MorbusOnkoBasePersonState', false, 'add');
						}
					}, {
						text: 'Госпитализация',
						handler: function() {
							me.openMorbusOnkoSpecificForm('MorbusOnkoBasePS', false, 'add');
						}
					}]
				}, {
					text: 'Создать извещение',
					iconCls: 'notify16-2017-panel',
					id: me.id + 'addEvnNotify',
					handler: function () {
						me.addEvnNotify();
					}
				}, {
					text: 'Печать',
					iconCls: 'panicon-print',
					menu: [{
						text: 'Печать КЛУ при ЗНО',
						handler: function() {
							printControlCardZno(me.Evn_id, me.EvnDiagPLSop_id);
						}
					}, {
						text: 'Печатная форма по специфике',
						handler: function() {
							printBirt({
								'Report_FileName': 'SpecificsOnko_Print.rptdesign',
								'Report_Params': '&paramEvn=' + me.Evn_id,
								'Report_Format': 'pdf'
							});
						}
					}]
				}
			]
		});
		
		if (getRegionNick() != 'kz') {
			this.stagePanel = {
				border: false,
				items: [{
					xtype: 'fieldset',
					title: 'Стадия опухолевого процесса по системе TNM',
					items: [{
						layout: 'column',
						border: false,
						style: 'margin-bottom: 5px;',
						anchor: '100%',
						defaults: {
							border: false,
							labelWidth: 200,
							width: 140
						},
						items: [{
							xtype: 'label',
							width: 200,
							anchor: null,
							padding: '7 10 0 0',
							html: 'ФОМС:',
						}, {
							xtype: 'commonSprCombo',
							comboSubject: 'OnkoT',
							prefix: 'fed_',
							moreFields: [
								{ name: 'OnkoTLink_id', mapping: 'OnkoTLink_id' },
								{ name: 'OnkoT_did', mapping: 'OnkoT_did' },
								{ name: 'OnkoTLink_CodeStage', mapping: 'OnkoTLink_CodeStage' }
							],
							labelWidth: 15,
							style: 'margin-right: 20px;',
							displayCode: true,
							codeField: 'OnkoTLink_CodeStage',
							allowBlank: false,
							typeCode: 'int',
							fieldLabel: 'T',
							name: 'OnkoT_fid',
							tpl: '<tpl for=".">' +
								'<li role="option" class="x6-boundlist-item">' +
								'<span style="color: red;">{OnkoTLink_CodeStage}.</span> {OnkoT_Name}' +
								'</li></tpl>',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if (!combo.getStore().getCount() && me.linkStore[combo.comboSubject]) {
										combo.getStore().clearFilter();
										combo.getStore().filterBy(function(rec) {
											return rec.get(combo.comboSubject + '_id') && rec.get(combo.comboSubject + 'Link_id').inlist(me.linkStore[combo.comboSubject]);
										});
									}
									var base_form = me.FormPanel.getForm();
									if (newValue) {
										base_form.findField('OnkoT_id').setValue(combo.getFieldValue('OnkoT_did'));
									}
								}
							}
						}, {
							xtype: 'commonSprCombo',
							comboSubject: 'OnkoN',
							prefix: 'fed_',
							moreFields: [
								{ name: 'OnkoNLink_id', mapping: 'OnkoNLink_id' },
								{ name: 'OnkoN_did', mapping: 'OnkoN_did' },
								{ name: 'OnkoNLink_CodeStage', mapping: 'OnkoNLink_CodeStage' }
							],
							labelWidth: 15,
							style: 'margin-right: 20px;',
							displayCode: true,
							codeField: 'OnkoNLink_CodeStage',
							allowBlank: false,
							typeCode: 'int',
							fieldLabel: 'N',
							name: 'OnkoN_fid',
							tpl: '<tpl for=".">' +
								'<li role="option" class="x6-boundlist-item">' +
								'<span style="color: red;">{OnkoNLink_CodeStage}.</span> {OnkoN_Name}' +
								'</li></tpl>',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if (!combo.getStore().getCount() && me.linkStore[combo.comboSubject]) {
										combo.getStore().clearFilter();
										combo.getStore().filterBy(function(rec) {
											return rec.get(combo.comboSubject + '_id') && rec.get(combo.comboSubject + 'Link_id').inlist(me.linkStore[combo.comboSubject]);
										});
									}
									var base_form = me.FormPanel.getForm();
									if (newValue) {
										base_form.findField('OnkoN_id').setValue(combo.getFieldValue('OnkoN_did'));
									}
								}
							}
						}, {
							xtype: 'commonSprCombo',
							comboSubject: 'OnkoM',
							prefix: 'fed_',
							moreFields: [
								{ name: 'OnkoMLink_id', mapping: 'OnkoMLink_id' },
								{ name: 'OnkoM_did', mapping: 'OnkoM_did' },
								{ name: 'OnkoMLink_CodeStage', mapping: 'OnkoMLink_CodeStage' }
							],
							labelWidth: 15,
							style: 'margin-right: 20px;',
							displayCode: true,
							codeField: 'OnkoMLink_CodeStage',
							allowBlank: false,
							typeCode: 'int',
							fieldLabel: 'M',
							name: 'OnkoM_fid',
							tpl: '<tpl for=".">' +
								'<li role="option" class="x6-boundlist-item">' +
								'<span style="color: red;">{OnkoMLink_CodeStage}.</span> {OnkoM_Name}' +
								'</li></tpl>',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if (!combo.getStore().getCount() && me.linkStore[combo.comboSubject]) {
										combo.getStore().clearFilter();
										combo.getStore().filterBy(function(rec) {
											return rec.get(combo.comboSubject + '_id') && rec.get(combo.comboSubject + 'Link_id').inlist(me.linkStore[combo.comboSubject]);
										});
									}
									var base_form = me.FormPanel.getForm();
									if (newValue) {
										base_form.findField('OnkoM_id').setValue(combo.getFieldValue('OnkoM_did'));
									}
								}
							}
						}]
					}, {
						layout: 'column',
						border: false,
						style: 'margin-bottom: 5px;',
						anchor: '100%',
						defaults: {
							border: false,
							labelWidth: 200,
							width: 140
						},
						items: [{
							xtype: 'label',
							width: 200,
							anchor: null,
							padding: '7 10 0 0',
							html: 'Канцер регистр:',
						}, {
							xtype: 'commonSprCombo',
							comboSubject: 'OnkoT',
							labelWidth: 15,
							style: 'margin-right: 20px;',
							displayCode: false,
							allowBlank: false,
							typeCode: 'int',
							fieldLabel: 'T',
							name: 'OnkoT_id'
						}, {
							xtype: 'commonSprCombo',
							comboSubject: 'OnkoN',
							labelWidth: 15,
							style: 'margin-right: 20px;',
							displayCode: false,
							allowBlank: false,
							typeCode: 'int',
							fieldLabel: 'N',
							name: 'OnkoN_id'
						}, {
							xtype: 'commonSprCombo',
							comboSubject: 'OnkoM',
							labelWidth: 15,
							style: 'margin-right: 20px;',
							displayCode: false,
							allowBlank: false,
							typeCode: 'int',
							fieldLabel: 'M',
							name: 'OnkoM_id'
						}]
					}]
				}, {
					xtype: 'fieldset',
					title: 'Стадия опухолевого процесса',
					defaults: {
						labelWidth: 190
					},
					items: [{
						xtype: 'commonSprCombo',
						comboSubject: 'TumorStage',
						prefix: 'fed_',
						moreFields: [
							{ name: 'TumorStageLink_id', mapping: 'TumorStageLink_id' },
							{ name: 'TumorStage_did', mapping: 'TumorStage_did' },
							{ name: 'TumorStageLink_CodeStage', mapping: 'TumorStageLink_CodeStage' }
						],
						fieldLabel: 'ФОМС',
							displayCode: true,
							codeField: 'TumorStageLink_CodeStage',
						allowBlank: false,
						tpl: '<tpl for=".">' +
							'<li role="option" class="x6-boundlist-item">' +
							'<span style="color: red;">{TumorStageLink_CodeStage}.</span> {TumorStage_Name}' +
							'</li></tpl>',
						anchor:'100%',
						typeCode: 'int',
						name: 'TumorStage_fid',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								if (!combo.getStore().getCount() && me.linkStore[combo.comboSubject]) {
									combo.getStore().clearFilter();
									combo.getStore().filterBy(function(rec) {
										return rec.get(combo.comboSubject + '_id') && rec.get(combo.comboSubject + 'Link_id').inlist(me.linkStore[combo.comboSubject]);
									});
								}
								var base_form = me.FormPanel.getForm();
								if (newValue && typeof(newValue) != "object" ) {
									base_form.findField('TumorStage_id').setValue(combo.getFieldValue('TumorStage_did'));
								}
							}
						}
					}, {
						xtype: 'commonSprCombo',
						comboSubject: 'TumorStage',
						fieldLabel: 'Канцер регистр',
						displayCode: false,
						allowBlank: false,
						anchor: '100%',
						typeCode: 'int',
						name: 'TumorStage_id'
					}]
				}]
			};
		} else {
			this.stagePanel = {
				border: false,
				items: [{
					layout: 'column',
					border: false,
					style: 'margin-bottom: 5px;',
					width: 730,
					defaults: {
						border: false,
						labelWidth: 200,
						width: 140
					},
					items: [{
						xtype: 'label',
						width: 210,
						anchor: null,
						padding: '7 10 0 0',
						html: 'Стадия опухолевого процесса по системе TNM.',
					}, {
						xtype: 'commonSprCombo',
						comboSubject: 'OnkoT',
						labelWidth: 15,
						style: 'margin-right: 20px;',
						displayCode: false,
						allowBlank: false,
						typeCode: 'int',
						fieldLabel: 'T',
						name: 'OnkoT_id'
					}, {
						xtype: 'commonSprCombo',
						comboSubject: 'OnkoN',
						labelWidth: 15,
						style: 'margin-right: 20px;',
						displayCode: false,
						allowBlank: false,
						typeCode: 'int',
						fieldLabel: 'N',
						name: 'OnkoN_id'
					}, {
						xtype: 'commonSprCombo',
						comboSubject: 'OnkoM',
						labelWidth: 15,
						style: 'margin-right: 20px;',
						displayCode: false,
						allowBlank: false,
						typeCode: 'int',
						fieldLabel: 'M',
						name: 'OnkoM_id'
					}]
				}, {
					xtype: 'commonSprCombo',
					comboSubject: 'TumorStage',
					fieldLabel: 'Стадия опухолевого процесса',
					displayCode: false,
					allowBlank: false,
					width: 730,
					labelWidth: 200,
					typeCode: 'int',
					name: 'TumorStage_id'
				}]
			};
		}
		
		this.DiagPanel = Ext6.create('Ext6.panel.Panel', {
			userCls: 'vizitPanelEmk',
			autoScroll: true,
			layout: 'anchor',
			bodyPadding: '15 25',
			border: false,
			defaults: {
				anchor: '100%',
				width: 615,
				maxWidth: 615 + 145,
				labelWidth: 200
			},
			items: [{
				name: 'Morbus_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusOnko_id',
				xtype: 'hidden'
			}, {
				name: 'EvnDiagPLSop_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusOnkoBase_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusBase_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusOnkoVizitPLDop_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusOnkoLeave_id',
				xtype: 'hidden'
			}, {
				name: 'Evn_pid',
				xtype: 'hidden'
			}, {
				name: 'Mode',
				xtype: 'hidden',
				value: 'evnvizitpl_viewform' // todo: надо ставить evnsection_viewform или evnvizitpl_viewform в зависимости от типа события
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'OnkoTreatment',
				fieldLabel: 'Повод обращения',
				allowBlank: false,
				name: 'OnkoTreatment_id', 
				moreFields: [
					{name: 'OnkoTreatment_begDate', type: 'date', dateFormat: 'd.m.Y' },
					{name: 'OnkoTreatment_endDate', type: 'date', dateFormat: 'd.m.Y' }
				],
				listeners: {
					'change': function() {
						me.setFieldsAllowBlank();
					}
				}
			}, {
				xtype: 'datefield',
				anchor: null,
				height:27,
				width: 325,
				allowBlank: true,
				format: 'd.m.Y',
				maxValue: getGlobalOptions().date,
				fieldLabel: 'Дата появления первых признаков заболевания',
				style: 'margin-right: 10px;',
				name: 'MorbusOnko_firstSignDT'
			}, {
				layout: 'column',
				border: false,
				style: 'margin-bottom: 5px;',
				anchor: '100%',
				defaults: {
					border: false,
					labelWidth: 200
				},
				items: [{
					xtype: 'datefield',
					height:27,
					width: 325,
					anchor:'100%',
					format: 'd.m.Y',
					maxValue: getGlobalOptions().date,
					fieldLabel: 'Дата первого обращения в МО по&nbsp;поводу данного заболевания',
					style: 'margin-right: 20px;',
					name: 'MorbusOnko_firstVizitDT'
				}, {
					xtype: 'swLpuCombo',
					fieldLabel: 'МО',
					labelStyle: 'vertical-align: middle;',
					anchor:'100%',
					labelWidth: 30,
					labelAlign: 'middle',
					height:60,
					width: 315,
					name: 'Lpu_foid'
				}]
			}, {
				xtype: 'datefield',
				height:27,
				width: 325,
				anchor: null,
				allowBlank: getRegionNick() != 'perm',
				format: 'd.m.Y',
				maxValue: getGlobalOptions().date,
				fieldLabel: 'Дата установления диагноза',
				style: 'margin-right: 10px;',
				name: 'MorbusOnko_setDiagDT'
			}, {
				xtype: 'textfield',
				height:27,
				width: 325,
				anchor: null,
				format: 'd.m.Y',
				fieldLabel: 'Регистрационный номер',
				style: 'margin-right: 10px;',
				name: 'MorbusOnkoBase_NumCard'
			}, {
				layout: 'column',
				border: false,
				style: 'margin-bottom: 5px;',
				anchor:'100%',
				defaults: {
					border: false,
					labelWidth: 200
				},
				items: [{
					xtype: 'datefield',
					height:27,
					width: 325,
					anchor: null,
					allowBlank: false,
					format: 'd.m.Y',
					maxValue: getGlobalOptions().date,
					fieldLabel: 'Дата взятия на учет в ОД',
					style: 'margin-right: 20px;',
					name: 'MorbusBase_setDT'
				}, {
					xtype: 'commonSprCombo',
					comboSubject: 'OnkoRegType',
					labelWidth: 150,
					width: 315,
					fieldLabel: 'Взят на учет в ОД',
					name: 'OnkoRegType_id'
				}]
			}, {
				layout: 'column',
				border: false,
				style: 'margin-bottom: 5px;',
				anchor:'100%',
				defaults: {
					border: false,
					labelWidth: 200
				},
				items: [{
					xtype: 'datefield',
					height:27,
					width: 325,
					anchor: null,
					format: 'd.m.Y',
					maxValue: getGlobalOptions().date,
					fieldLabel: 'Дата снятия с учета в ОД',
					style: 'margin-right: 20px;',
					name: 'MorbusBase_disDT'
				}, {
					xtype: 'commonSprCombo',
					comboSubject: 'OnkoRegOutType',
					labelWidth: 150,
					width: 315,
					fieldLabel: 'Причина снятия с учета',
					name: 'OnkoRegOutType_id'
				}]
			}, {
				xtype: 'textfield',
				height: 27,
				width: 325,
				anchor: null,
				readOnly: true,
				fieldStyle: 'background: #f5f5f5; color: #666666; border-color: #d0d0d0;',
				fieldLabel: 'Порядковый номер данной опухоли у данного больного',
				style: 'margin-right: 10px;',
				name: 'MorbusOnko_NumTumor'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'TumorPrimaryMultipleType',
				fieldLabel: 'Первично-множественная опухоль',
				name: 'TumorPrimaryMultipleType_id'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'YesNo',
				fieldLabel: 'Признак основной опухоли',
				name: 'MorbusOnko_IsMainTumor'
			}, {
				xtype: 'swDiagCombo',
				disabled: true,
				fieldLabel: 'Топография (локализация) опухоли',
				name: 'Diag_id'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'OnkoLesionSide',
				fieldLabel: 'Сторона поражения',
				name: 'OnkoLesionSide_id'
			}, {
				xtype: 'fieldset',
				collapsible: true,
				collapsed: true,
				title: 'Подтверждение диагноза',
				defaults: {
					border: false,
					labelWidth: 190
				},
				items: [{
					xtype: 'commonSprCombo',
					comboSubject: 'HistologicReasonType',
					anchor:'100%',
					fieldLabel: 'Отказ / противопоказание',
					name: 'HistologicReasonType_id',
					listeners: {
						'change': function() {
							me.setFieldsAllowBlank();
						}
					}
				}, {
					xtype: 'datefield',
					height: 27,
					width: 325,
					anchor: null,
					format: 'd.m.Y',
					maxValue: getGlobalOptions().date,
					fieldLabel: 'Дата регистрации отказа / противопоказания',
					name: 'MorbusOnko_histDT'
				}]
			},
			/* {
				xtype: 'datefield',
				height: 27,
				width: 325,
				anchor: null,
				format: 'd.m.Y',
				maxValue: getGlobalOptions().date,
				hidden: getRegionNick() == 'kz',
				fieldLabel: 'Дата взятия материала',
				name: 'MorbusOnko_takeDT'
			},
			me.OnkoDiagConfTypePanel, 
			{
				xtype: 'panel',
				border: false,
				layout: 'anchor',
				id: this.id + 'diagAttribPanel',
				padding: 0,
				defaults: {
					labelWidth: 200
				},
				items: [{
					xtype: 'commonSprCombo',
					comboSubject: 'DiagAttribType',
					anchor:'100%',
					fieldLabel: 'Тип диагностического показателя',
					name: 'DiagAttribType_id',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = me.FormPanel.getForm();
							var DiagResultCombo = base_form.findField('DiagResult_id');
							var DiagResultComboId = DiagResultCombo.getValue();
							var DiagAttribDictCombo = base_form.findField('DiagAttribDict_id');
							var isLoading = me.isLoading;
							if (!isLoading) {
								DiagResultCombo.clearValue();
								DiagAttribDictCombo.clearValue();
							} 
							DiagResultCombo.getStore().clearFilter();
							DiagResultCombo.getStore().proxy.extraParams.object = !getRegionNick().inlist(['ekb', 'perm']) 
								? 'fed_DiagResult' 
								: (getRegionNick() != 'perm' || newValue != 3 ? 'fed_DiagResult' : 'DiagResult');
							DiagResultCombo.getStore().load({
								callback: function() {
									if (getRegionNick() != 'perm' || newValue != 3) {
										if ( !Ext.isEmpty(newValue) ) {
											DiagResultCombo.getStore().filterBy(function(rec) {
												return (rec.get('DiagAttribType_id') == newValue && rec.get('DiagResult_id').inlist(me.arrDiagResult));
											});
										}
									}
									if (isLoading) DiagResultCombo.setValue(DiagResultComboId);
									DiagResultCombo.fireEvent('change', DiagResultCombo, DiagResultCombo.getValue());
								}
							});
						}
					}
				}, {
					xtype: 'commonSprCombo',
					comboSubject: 'DiagResult',
					anchor:'100%',
					fieldLabel: 'Результат диагностики',
					name: 'DiagResult_id', 
					moreFields: [
						{name: 'DiagAttribType_id', mapping: 'DiagAttribType_id'},
						{name: 'DiagAttribDict_id', mapping: 'DiagAttribDict_id'}
					],
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = me.FormPanel.getForm();
							var DiagAttribTypeCombo = base_form.findField('DiagAttribType_id');
							var DiagAttribDictCombo = base_form.findField('DiagAttribDict_id');
							var DiagAttribDictId = combo.getFieldValue('DiagAttribDict_id');
							if (getRegionNick() == 'perm') {
								DiagAttribDictCombo.getStore().proxy.extraParams.object = DiagAttribTypeCombo.getValue() != 3 ? 'fed_DiagAttribDict' : 'DiagAttribDict';
								DiagAttribDictCombo.getStore().load({
									callback: function() {
										DiagAttribDictCombo.setValue(DiagAttribDictId);
									}
								});
							} else {
								DiagAttribDictCombo.setValue(DiagAttribDictId);
							}
						}
					}
				}, {
					xtype: 'commonSprCombo',
					comboSubject: 'DiagAttribDict',
					prefix: getRegionNick() != 'ekb' ? 'fed_' : '',
					anchor:'100%',
					fieldLabel: 'Диагностический показатель',
					disabled: true,
					name: 'DiagAttribDict_id'
				}]
			},*/
			{
				xtype: 'fieldset',
				collapsible: true,
				title: 'Морфологический тип опухоли',
				defaults: {
					border: false,
					labelWidth: 190
				},
				items: [{
					xtype: 'commonSprCombo',
					comboSubject: 'OnkoDiag',
					anchor:'100%',
					fieldLabel: 'Морфологический тип опухоли. (Гистология опухоли)',
					name: 'OnkoDiag_mid'
				}, {
					xtype: 'textfield',
					width: 325,
					fieldLabel: 'Номер гистологического исследования',
					name: 'MorbusOnko_NumHisto'
				}]
			}, 
			this.stagePanel, 
			{
				xtype: 'fieldset',
				collapsible: true,
				collapsed: true,
				title: 'Локализация отдаленных метастазов',
				defaults: {
					border: false,
					labelWidth: 190
				},
				items: [{
					xtype: 'commonSprCombo',
					comboSubject: 'YesNo',
					anchor:'100%',
					fieldLabel: 'Неизвестна',
					name: 'MorbusOnko_IsTumorDepoUnknown'
				}, {
					xtype: 'commonSprCombo',
					comboSubject: 'YesNo',
					anchor:'100%',
					fieldLabel: 'Отдаленные лимфатические узлы',
					name: 'MorbusOnko_IsTumorDepoLympha'
				}, {
					xtype: 'commonSprCombo',
					comboSubject: 'YesNo',
					anchor:'100%',
					fieldLabel: 'Кости',
					name: 'MorbusOnko_IsTumorDepoBones'
				}, {
					xtype: 'commonSprCombo',
					comboSubject: 'YesNo',
					anchor:'100%',
					fieldLabel: 'Печень',
					name: 'MorbusOnko_IsTumorDepoLiver'
				}, {
					xtype: 'commonSprCombo',
					comboSubject: 'YesNo',
					anchor:'100%',
					fieldLabel: 'Легкие и/или плевра',
					name: 'MorbusOnko_IsTumorDepoLungs'
				}, {
					xtype: 'commonSprCombo',
					comboSubject: 'YesNo',
					anchor:'100%',
					fieldLabel: 'Головной мозг',
					name: 'MorbusOnko_IsTumorDepoBrain'
				}, {
					xtype: 'commonSprCombo',
					comboSubject: 'YesNo',
					anchor:'100%',
					fieldLabel: 'Кожа',
					name: 'MorbusOnko_IsTumorDepoSkin'
				}, {
					xtype: 'commonSprCombo',
					comboSubject: 'YesNo',
					anchor:'100%',
					fieldLabel: 'Почки',
					name: 'MorbusOnko_IsTumorDepoKidney'
				}, {
					xtype: 'commonSprCombo',
					comboSubject: 'YesNo',
					anchor:'100%',
					fieldLabel: 'Яичники',
					name: 'MorbusOnko_IsTumorDepoOvary'
				}, {
					xtype: 'commonSprCombo',
					comboSubject: 'YesNo',
					anchor:'100%',
					fieldLabel: 'Брюшина',
					name: 'MorbusOnko_IsTumorDepoPerito'
				}, {
					xtype: 'commonSprCombo',
					comboSubject: 'YesNo',
					anchor:'100%',
					fieldLabel: 'Костный мозг',
					name: 'MorbusOnko_IsTumorDepoMarrow'
				}, {
					xtype: 'commonSprCombo',
					comboSubject: 'YesNo',
					anchor:'100%',
					fieldLabel: 'Другие органы',
					name: 'MorbusOnko_IsTumorDepoOther'
				}, {
					xtype: 'commonSprCombo',
					comboSubject: 'YesNo',
					anchor:'100%',
					fieldLabel: 'Множественные',
					name: 'MorbusOnko_IsTumorDepoMulti'
				}]
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'OnkoPostType',
				fieldLabel: 'Выявлен врачом',
				displayCode: false,
				typeCode: 'int',
				name: 'OnkoPostType_id'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'TumorCircumIdentType',
				fieldLabel: 'Обстоятельства выявления опухоли',
				displayCode: false,
				typeCode: 'int',
				name: 'TumorCircumIdentType_id'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'OnkoLateDiagCause',
				fieldLabel: 'Причины поздней диагностики',
				displayCode: false,
				typeCode: 'int',
				name: 'OnkoLateDiagCause_id'
			}, {
				layout: 'column',
				border: false,
				style: 'margin-bottom: 5px;',
				anchor:'100%',
				defaults: {
					border: false,
					labelWidth: 200
				},
				items: [{
					xtype: 'datefield',
					height:27,
					width: 325,
					anchor: null,
					format: 'd.m.Y',
					maxValue: getGlobalOptions().date,
					fieldLabel: 'Дата смерти',
					style: 'margin-right: 20px;',
					name: 'MorbusBase_DeadDT'
				}, {
					xtype: 'swDiagCombo',
					labelWidth: 120,
					width: 315,
					fieldLabel: 'Причина смерти',
					name: 'DiagDead_id'
				}]
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'AutopsyPerformType',
				fieldLabel: 'Аутопсия',
				name: 'AutopsyPerformType_id'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'TumorAutopsyResultType',
				fieldLabel: 'Результат аутопсии применительно к данной опухоли',
				name: 'TumorAutopsyResultType_id'
			}]
		});
		
		this.OnkoConsultGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			region: 'center',
			height: 580,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					iconCls:'menu_dispedit',
					handler: function() {
						me.openMorbusOnkoSpecificForm('OnkoConsult', false, 'edit');
					}
				}, {
					text: 'Удалить',
					iconCls:'menu_dispdel',
					handler: function() {
						me.deleteEvent('OnkoConsult');
					}
				}]
			}),
			showRecordMenu: function(el, rowIndx) {
				this.recordMenu.rowIndex = rowIndx;
				this.recordMenu.showBy(el);
			},
			columns: [{
				text: 'Дата проведения',
				dataIndex: 'OnkoConsult_consDate',
				width: 140,
				renderer: function (value) {
					return value ? value.format('d.m.Y') : '';
				}
			}, {
				text: 'Тип лечения',
				dataIndex: 'OnkoHealType_Name',
				width: 140
			}, {
				text: 'Результат проведения',
				dataIndex: 'OnkoConsultResult_Name',
				flex: 1
			}, {
				width: 40,
				dataIndex: 'PersonDispHist_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.OnkoConsultGrid.id + "\").showRecordMenu(this, " + metaData.rowIndex + ");'></div>";
				}
			}, {
				hidden: true,
				text: 'OnkoConsult_id',
				dataIndex: 'OnkoConsult_id'
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'OnkoConsult_id', type: 'int' },
					{ name: 'OnkoConsult_consDate', type: 'date', dateFormat: 'd.m.Y'},
					{ name: 'OnkoHealType_Name', type: 'string' },
					{ name: 'OnkoConsultResult_Name', type: 'string' },
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=OnkoConsult&m=loadList',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: {
					property: 'OnkoConsult_consDate',
					direction: 'DESC'
				}
			})
		});
			
		this.MorbusOnkoDrugGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			region: 'center',
			height: 580,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					iconCls:'menu_dispedit',
					handler: function() {
						me.openMorbusOnkoSpecificForm('MorbusOnkoDrug', false, 'edit');
					}
				}, {
					text: 'Удалить',
					iconCls:'menu_dispdel',
					handler: function() {
						me.deleteEvent('MorbusOnkoDrug');
					}
				}]
			}),
			showRecordMenu: function(el, rowIndx) {
				this.recordMenu.rowIndex = rowIndx;
				this.recordMenu.showBy(el);
			},
			columns: [{
				text: 'Дата начала',
				dataIndex: 'MorbusOnkoDrug_begDate',
				width: 140,
				renderer: function (value) {
					return value ? value.format('d.m.Y') : '';
				}
			}, {
				text: 'Дата окончания',
				dataIndex: 'MorbusOnkoDrug_endDate',
				width: 140,
				renderer: function (value) {
					return value ? value.format('d.m.Y') : '';
				}
			}, {
				text: 'Препарат',
				dataIndex: 'Prep_Name',
				width: 160
			}, {
				text: 'Медикамент',
				dataIndex: 'OnkoDrug_Name',
				flex: 1
			}, {
				width: 40,
				dataIndex: 'PersonDispHist_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.MorbusOnkoDrugGrid.id + "\").showRecordMenu(this, " + metaData.rowIndex + ");'></div>";
				}
			}, {
				hidden: true,
				text: 'MorbusOnkoDrug_id',
				dataIndex: 'MorbusOnkoDrug_id'
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'MorbusOnkoDrug_id', type: 'int' },
					{ name: 'MorbusOnkoDrug_begDate', type: 'date', dateFormat: 'd.m.Y'},
					{ name: 'MorbusOnkoDrug_endDate', type: 'date', dateFormat: 'd.m.Y'},
					{ name: 'Prep_Name', type: 'string' },
					{ name: 'OnkoDrug_Name', type: 'string' }
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=MorbusOnkoDrug&m=loadMorbusOnkoDrugList',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: {
					property: 'MorbusOnkoDrug_specSetDT',
					direction: 'DESC'
				}
			})
		});
			
		this.MorbusOnkoSpecTreatGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			region: 'center',
			height: 580,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					iconCls:'menu_dispedit',
					handler: function() {
						me.openMorbusOnkoSpecificForm('MorbusOnkoSpecTreat', false, 'edit');
					}
				}, {
					text: 'Удалить',
					iconCls:'menu_dispdel',
					handler: function() {
						me.deleteEvent('MorbusOnkoSpecTreat');
					}
				}]
			}),
			showRecordMenu: function(el, rowIndx) {
				this.recordMenu.rowIndex = rowIndx;
				this.recordMenu.showBy(el);
			},
			columns: [{
				text: 'Дата начала',
				dataIndex: 'MorbusOnkoSpecTreat_specSetDT',
				width: 140,
				renderer: function (value) {
					return value ? value.format('d.m.Y') : '';
				}
			}, {
				text: 'Дата окончания',
				dataIndex: 'MorbusOnkoSpecTreat_specDisDT',
				width: 140,
				renderer: function (value) {
					return value ? value.format('d.m.Y') : '';
				}
			}, {
				text: 'Тип',
				dataIndex: 'TumorPrimaryTreatType_id_Name',
				width: 160
			}, {
				text: 'Сочетание видов лечения',
				dataIndex: 'OnkoCombiTreatType_id_Name',
				flex: 1
			}, {
				width: 40,
				dataIndex: 'PersonDispHist_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.MorbusOnkoSpecTreatGrid.id + "\").showRecordMenu(this, " + metaData.rowIndex + ");'></div>";
				}
			}, {
				hidden: true,
				text: 'MorbusOnkoSpecTreat_id',
				dataIndex: 'MorbusOnkoSpecTreat_id'
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'MorbusOnkoSpecTreat_id', type: 'int' },
					{ name: 'MorbusOnkoSpecTreat_specSetDT', type: 'date', dateFormat: 'd.m.Y'},
					{ name: 'MorbusOnkoSpecTreat_specDisDT', type: 'date', dateFormat: 'd.m.Y'},
					{ name: 'TumorPrimaryTreatType_id_Name', type: 'string' },
					{ name: 'OnkoCombiTreatType_id_Name', type: 'string' }
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=MorbusOnkoSpecifics&m=loadMorbusOnkoSpecTreatList',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: {
					property: 'MorbusOnkoSpecTreat_specSetDT',
					direction: 'DESC'
				}
			})
		});

		this.MorbusOnkoLinkGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			region: 'center',
			height: 580,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					iconCls:'menu_dispedit',
					handler: function() {
						me.openMorbusOnkoSpecificForm('MorbusOnkoLink', false, 'edit');
					}
				}, {
					text: 'Удалить',
					iconCls:'menu_dispdel',
					handler: function() {
						me.deleteEvent('MorbusOnkoLink');
					}
				}]
			}),
			showRecordMenu: function(el, rowIndx) {
				this.recordMenu.rowIndex = rowIndx;
				this.recordMenu.showBy(el);
			},
			columns: [{
				text: 'Дата взятия материала',
				dataIndex: 'MorbusOnkoLink_takeDT',
				width: 140,
				renderer: function (value) {
					return value ? value.format('d.m.Y') : '';
				}
			}, {
				text: 'Метод подтверждения диагноза',
				dataIndex: 'OnkoDiagConfType_id_Name',
				width: 160
			}, {
				text: 'Тип диагностического показателя',
				dataIndex: 'DiagAttribType_id_Name',
				flex: 1
			}, {
				text: 'Результат диагностики',
				dataIndex: 'DiagResult_id_Name',
				flex: 1
			}, {
				text: 'Диагностический показатель',
				dataIndex: 'DiagAttribDict_id_Name',
				flex: 1
			},{
				width: 40,
				dataIndex: 'PersonDispHist_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.MorbusOnkoLinkGrid.id + "\").showRecordMenu(this, " + metaData.rowIndex + ");'></div>";
				}
			}, {
				hidden: true,
				text: 'MorbusOnkoLink_id',
				dataIndex: 'MorbusOnkoLink_id'
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'MorbusOnkoLink_id', type: 'int' },
					{ name: 'MorbusOnkoLink_takeDT', type: 'date', dateFormat: 'd.m.Y'},
					{ name: 'OnkoDiagConfType_id_Name', type: 'string' },
					{ name: 'DiagAttribType_id_Name', type: 'string' },
					{ name: 'DiagResult_id_Name', type: 'string' },
					{ name: 'DiagAttribDict_id_Name', type: 'string' }
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=MorbusOnkoSpecifics&m=loadMorbusOnkoLinkList',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: {
					property: 'MorbusOnkoLink_takeDT',
					direction: 'DESC'
				}
			})
		});
		
		this.MorbusOnkoRefusalGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			region: 'center',
			height: 580,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					iconCls:'menu_dispedit',
					handler: function() {
						me.openMorbusOnkoSpecificForm('MorbusOnkoRefusal', false, 'edit');
					}
				}, {
					text: 'Удалить',
					iconCls:'menu_dispdel',
					handler: function() {
						me.deleteEvent('MorbusOnkoRefusal');
					}
				}]
			}),
			showRecordMenu: function(el, rowIndx) {
				this.recordMenu.rowIndex = rowIndx;
				this.recordMenu.showBy(el);
			},
			columns: [{
				text: 'Дата регистрации отказа / противопоказания',
				dataIndex: 'MorbusOnkoRefusal_setDT',
				width: 240,
				renderer: function (value) {
					return value ? value.format('d.m.Y') : '';
				}
			}, {
				text: 'Тип лечения',
				dataIndex: 'MorbusOnkoRefusalType_id_Name',
				flex: 1
			}, {
				width: 40,
				dataIndex: 'PersonDispHist_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.MorbusOnkoRefusalGrid.id + "\").showRecordMenu(this, " + metaData.rowIndex + ");'></div>";
				}
			}, {
				hidden: true,
				text: 'MorbusOnkoRefusal_id',
				dataIndex: 'MorbusOnkoRefusal_id'
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'MorbusOnkoRefusal_id', type: 'int' },
					{ name: 'MorbusOnkoRefusal_setDT', type: 'date', dateFormat: 'd.m.Y'},
					{ name: 'MorbusOnkoRefusalType_id_Name', type: 'string' }
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=MorbusOnkoSpecifics&m=loadMorbusOnkoRefusalList',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: {
					property: 'MorbusOnkoRefusal_setDT',
					direction: 'DESC'
				}
			})
		});

		var gdsAddTip = function(val, metaData, record) {
			if (val && record.get('DrugTherapyScheme_IsMes') == 1) {
				metaData.tdAttr = 'data-qtip="Используется в расчёте КСГ"';
			}
			return val;
		};
			
		this.DrugTherapySchemeGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			region: 'center',
			height: 580,
			columns: [{
				text: 'Код',
				dataIndex: 'DrugTherapyScheme_Code',
				width: 80,
				renderer: gdsAddTip
			}, {
				text: 'Схема',
				dataIndex: 'DrugTherapyScheme_Name',
				flex: 1,
				renderer: gdsAddTip
			}, {
				text: 'Кол-во дней введения (норматив)',
				dataIndex: 'DrugTherapyScheme_Days',
				width: 200,
				renderer: gdsAddTip
			}, {
				text: 'Кол-во дней введения (факт)',
				dataIndex: 'DrugTherapyScheme_DaysFact',
				width: 200,
				renderer: gdsAddTip
			}, {
				hidden: true,
				text: 'DrugTherapyScheme_id',
				dataIndex: 'DrugTherapyScheme_id'
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'DrugTherapyScheme_id', type: 'int' },
					{ name: 'DrugTherapyScheme_Code', type: 'string' },
					{ name: 'DrugTherapyScheme_Name', type: 'string' },
					{ name: 'DrugTherapyScheme_Days', type: 'string' },
					{ name: 'DrugTherapyScheme_DaysFact', type: 'string' },
					{ name: 'DrugTherapyScheme_IsMes', type: 'int' }
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnSection&m=loadDrugTherapySchemeList',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
			}),
			viewConfig: {
				getRowClass: function(record, rowIndex, rowParams, store) {
					var cls = '';
					if (record.get('DrugTherapyScheme_IsMes') == 1) {
						cls = cls + 'x-grid-rowbold ';
					}
					return cls;
				}
			},
		});
	
		this.MorbusOnkoChemTerGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			region: 'center',
			height: 580,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					iconCls:'menu_dispedit',
					handler: function() {
						me.openMorbusOnkoSpecificForm('MorbusOnkoChemTer', false, 'edit');
					}
				}, {
					text: 'Удалить',
					iconCls:'menu_dispdel',
					handler: function() {
						me.deleteEvent('MorbusOnkoChemTer');
					}
				}]
			}),
			showRecordMenu: function(el, rowIndx) {
				this.recordMenu.rowIndex = rowIndx;
				this.recordMenu.showBy(el);
			},
			columns: [{
				text: 'Продолжительность курса',
				dataIndex: 'EvnUsluga_setDate',
				width: 200,
				renderer: function (value, el, record) {
					return value ? ('с ' + value.format('d.m.Y') + ' по ' + (record.get('EvnUsluga_disDate') ? record.get('EvnUsluga_disDate').format('d.m.Y') : 'н.в.')) : '';
				}
			}, {
				text: 'МО',
				dataIndex: 'Lpu_Name',
				width: 100
			}, {
				text: 'Вид химиотерапии',
				dataIndex: 'OnkoUslugaChemKindType_Name',
				width: 140
			}, {
				text: 'Преимущественная направленность',
				dataIndex: 'FocusType_Name',
				flex: 1
			}, {
				width: 40,
				dataIndex: 'PersonDispHist_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.MorbusOnkoChemTerGrid.id + "\").showRecordMenu(this, " + metaData.rowIndex + ");'></div>";
				}
			}, {
				hidden: true,
				text: 'EvnUsluga_id',
				dataIndex: 'EvnUsluga_id'
			}, {
				hidden: true,
				text: 'EvnUsluga_disDate',
				dataIndex: 'EvnUsluga_disDate'
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'EvnUsluga_id', type: 'int' },
					{ name: 'EvnUsluga_setDate', type: 'date', dateFormat: 'd.m.Y'},
					{ name: 'EvnUsluga_disDate', type: 'date', dateFormat: 'd.m.Y'},
					{ name: 'Lpu_Name', type: 'string' },
					{ name: 'OnkoUslugaChemKindType_Name', type: 'string' },
					{ name: 'FocusType_Name', type: 'string' }
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnUsluga&m=loadEvnUslugaGrid',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: {
					property: 'EvnUsluga_setDate',
					direction: 'DESC'
				}
			})
		});
		
		this.MorbusOnkoRadTerGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			region: 'center',
			height: 580,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					iconCls:'menu_dispedit',
					handler: function() {
						me.openMorbusOnkoSpecificForm('MorbusOnkoRadTer', false, 'edit');
					}
				}, {
					text: 'Удалить',
					iconCls:'menu_dispdel',
					handler: function() {
						me.deleteEvent('MorbusOnkoRadTer');
					}
				}]
			}),
			showRecordMenu: function(el, rowIndx) {
				this.recordMenu.rowIndex = rowIndx;
				this.recordMenu.showBy(el);
			},
			columns: [{
				text: 'Дата начала курса',
				dataIndex: 'EvnUsluga_setDate',
				width: 100,
				renderer: function (value) {
					return value ? value.format('d.m.Y') : '';
				}
			}, {
				text: 'Дата окончания курса',
				dataIndex: 'EvnUsluga_disDate',
				width: 120,
				renderer: function (value) {
					return value ? value.format('d.m.Y') : '';
				}
			}, {
				text: 'Способ облучения',
				dataIndex: 'OnkoUslugaBeamIrradiationType_Name',
				width: 140
			}, {
				text: 'Вид радиотерапии',
				dataIndex: 'OnkoUslugaBeamKindType_Name',
				width: 140
			}, {
				text: 'Метод',
				dataIndex: 'OnkoUslugaBeamMethodType_Name',
				width: 80
			}, {
				text: 'Преимущественная направленность',
				dataIndex: 'FocusType_Name',
				flex: 1
			}, {
				width: 40,
				dataIndex: 'PersonDispHist_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.MorbusOnkoRadTerGrid.id + "\").showRecordMenu(this, " + metaData.rowIndex + ");'></div>";
				}
			}, {
				hidden: true,
				text: 'EvnUsluga_id',
				dataIndex: 'EvnUsluga_id'
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'EvnUsluga_id', type: 'int' },
					{ name: 'EvnUsluga_setDate', type: 'date', dateFormat: 'd.m.Y'},
					{ name: 'EvnUsluga_disDate', type: 'date', dateFormat: 'd.m.Y'},
					{ name: 'OnkoUslugaBeamIrradiationType_Name', type: 'string' },
					{ name: 'OnkoUslugaBeamMethodType_Name', type: 'string' },
					{ name: 'FocusType_Name', type: 'string' }
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnUsluga&m=loadEvnUslugaGrid',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: {
					property: 'EvnUsluga_setDate',
					direction: 'DESC'
				}
			})
		});
		
		this.MorbusOnkoGormTerGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			region: 'center',
			height: 580,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					iconCls:'menu_dispedit',
					handler: function() {
						me.openMorbusOnkoSpecificForm('MorbusOnkoGormTer', false, 'edit');
					}
				}, {
					text: 'Удалить',
					iconCls:'menu_dispdel',
					handler: function() {
						me.deleteEvent('MorbusOnkoGormTer');
					}
				}]
			}),
			showRecordMenu: function(el, rowIndx) {
				this.recordMenu.rowIndex = rowIndx;
				this.recordMenu.showBy(el);
			},
			columns: [{
				text: 'Продолжительность курса',
				dataIndex: 'EvnUsluga_setDate',
				width: 200,
				renderer: function (value, el, record) {
					return value ? ('с ' + value.format('d.m.Y') + ' по ' + (record.get('EvnUsluga_disDate') ? record.get('EvnUsluga_disDate').format('d.m.Y') : 'н.в.')) : '';
				}
			}, {
				text: 'МО',
				dataIndex: 'Lpu_Name',
				width: 100
			}, {
				text: 'Вид терапии',
				dataIndex: 'types',
				width: 140
			}, {
				text: 'Преимущественная направленность',
				dataIndex: 'FocusType_Name',
				flex: 1
			}, {
				width: 40,
				dataIndex: 'PersonDispHist_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.MorbusOnkoGormTerGrid.id + "\").showRecordMenu(this, " + metaData.rowIndex + ");'></div>";
				}
			}, {
				hidden: true,
				text: 'EvnUsluga_id',
				dataIndex: 'EvnUsluga_id'
			}, {
				hidden: true,
				text: 'EvnUsluga_disDate',
				dataIndex: 'EvnUsluga_disDate'
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'EvnUsluga_id', type: 'int' },
					{ name: 'EvnUsluga_setDate', type: 'date', dateFormat: 'd.m.Y'},
					{ name: 'EvnUsluga_disDate', type: 'date', dateFormat: 'd.m.Y'},
					{ name: 'Lpu_Name', type: 'string' },
					{ name: 'types', type: 'string' },
					{ name: 'FocusType_Name', type: 'string' }
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnUsluga&m=loadEvnUslugaGrid',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: {
					property: 'EvnUsluga_setDate',
					direction: 'DESC'
				}
			})
		});
		
		this.MorbusOnkoHirTerGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			region: 'center',
			height: 580,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					iconCls:'menu_dispedit',
					handler: function() {
						me.openMorbusOnkoSpecificForm('MorbusOnkoHirTer', false, 'edit');
					}
				}, {
					text: 'Удалить',
					iconCls:'menu_dispdel',
					handler: function() {
						me.deleteEvent('MorbusOnkoHirTer');
					}
				}]
			}),
			showRecordMenu: function(el, rowIndx) {
				this.recordMenu.rowIndex = rowIndx;
				this.recordMenu.showBy(el);
			},
			columns: [{
				text: 'Дата проведения',
				dataIndex: 'EvnUsluga_setDate',
				width: 140,
				renderer: function (value) {
					return value ? value.format('d.m.Y') : '';
				}
			}, {
				text: 'МО',
				dataIndex: 'Lpu_Name',
				width: 160
			}, {
				text: 'Кто проводил',
				dataIndex: 'MedPersonal_Name',
				width: 160
			}, {
				text: 'Название операции',
				dataIndex: 'Usluga_Name',
				flex: 1
			}, {
				width: 40,
				dataIndex: 'PersonDispHist_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.MorbusOnkoHirTerGrid.id + "\").showRecordMenu(this, " + metaData.rowIndex + ");'></div>";
				}
			}, {
				hidden: true,
				text: 'EvnUsluga_id',
				dataIndex: 'EvnUsluga_id'
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'EvnUsluga_id', type: 'int' },
					{ name: 'EvnUsluga_setDate', type: 'date', dateFormat: 'd.m.Y'},
					{ name: 'Lpu_Name', type: 'string' },
					{ name: 'MedPersonal_Name', type: 'string' },
					{ name: 'Usluga_Name', type: 'string' }
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnUsluga&m=loadEvnUslugaGrid',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: {
					property: 'EvnUsluga_setDate',
					direction: 'DESC'
				}
			})
		});
		
		this.MorbusOnkoNonSpecTerGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			region: 'center',
			height: 580,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					iconCls:'menu_dispedit',
					handler: function() {
						me.openMorbusOnkoSpecificForm('MorbusOnkoNonSpecTer', false, 'edit');
					}
				}, {
					text: 'Удалить',
					iconCls:'menu_dispdel',
					handler: function() {
						me.deleteEvent('MorbusOnkoNonSpecTer');
					}
				}]
			}),
			showRecordMenu: function(el, rowIndx) {
				this.recordMenu.rowIndex = rowIndx;
				this.recordMenu.showBy(el);
			},
			columns: [{
				text: 'Дата',
				dataIndex: 'EvnUsluga_setDate',
				width: 140,
				renderer: function (value) {
					return value ? value.format('d.m.Y') : '';
				}
			}, {
				text: 'МО',
				dataIndex: 'Lpu_Name',
				width: 140
			}, {
				text: 'Услуга',
				dataIndex: 'Usluga_Name',
				flex: 1,
				renderer: function (value, el, record) {
					return value ? record.get('Usluga_Code') + ' ' + value : '';
				}
			}, {
				width: 40,
				dataIndex: 'PersonDispHist_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.MorbusOnkoNonSpecTerGrid.id + "\").showRecordMenu(this, " + metaData.rowIndex + ");'></div>";
				}
			}, {
				hidden: true,
				text: 'EvnUsluga_id',
				dataIndex: 'EvnUsluga_id'
			}, {
				hidden: true,
				text: 'Usluga_Code',
				dataIndex: 'Usluga_Code'
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'EvnUsluga_id', type: 'int' },
					{ name: 'EvnUsluga_setDate', type: 'date', dateFormat: 'd.m.Y'},
					{ name: 'Lpu_Name', type: 'string' },
					{ name: 'Usluga_Code', type: 'string' },
					{ name: 'Usluga_Name', type: 'string' }
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnUsluga&m=loadEvnUslugaGrid',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: {
					property: 'EvnUsluga_setDate',
					direction: 'DESC'
				}
			})
		});
		
		this.EvnNotifyGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			region: 'center',
			height: 580,
			columns: [{
				text: 'Дата',
				dataIndex: 'EvnOnkoNotify_setDate',
				width: 100,
				renderer: function (value) {
					return value ? value.format('d.m.Y') : '';
				}
			}, {
				text: 'Статус',
				dataIndex: 'EvnNotifyStatus_Name',
				width: 100
			}, {
				text: 'Причина отклонения',
				dataIndex: 'EvnNotifyRejectStatus_Name',
				width: 240
			}, {
				text: 'Комментарий',
				dataIndex: 'EvnOnkoNotify_Comment',
				flex: 1
			}, {
				hidden: true,
				text: 'MorbusOnkoEvnNotify_id',
				dataIndex: 'MorbusOnkoEvnNotify_id'
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'MorbusOnkoEvnNotify_id', type: 'int' },
					{ name: 'EvnOnkoNotify_setDate', type: 'date', dateFormat: 'd.m.Y'},
					{ name: 'EvnNotifyStatus_Name', type: 'string' },
					{ name: 'EvnNotifyRejectStatus_Name', type: 'string' },
					{ name: 'EvnOnkoNotify_Comment', type: 'string' }
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnOnkoNotify&m=getDataForSpecific',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: {
					property: 'EvnOnkoNotify_setDate',
					direction: 'ASC'
				}
			})
		});
		
		this.MorbusOnkoBasePersonStateGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			region: 'center',
			height: 580,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					iconCls:'menu_dispedit',
					handler: function() {
						me.openMorbusOnkoSpecificForm('MorbusOnkoBasePersonState', false, 'edit');
					}
				}, {
					text: 'Удалить',
					iconCls:'menu_dispdel',
					handler: function() {
						me.deleteEvent('MorbusOnkoBasePersonState');
					}
				}]
			}),
			showRecordMenu: function(el, rowIndx) {
				this.recordMenu.rowIndex = rowIndx;
				this.recordMenu.showBy(el);
			},
			columns: [{
				text: 'Дата наблюдения',
				dataIndex: 'MorbusOnkoBasePersonState_setDT',
				width: 140,
				renderer: function (value) {
					return value ? value.format('d.m.Y') : '';
				}
			}, {
				text: 'Общее состояние пациента',
				dataIndex: 'OnkoPersonStateType_id_Name',
				flex: 1
			}, {
				width: 40,
				dataIndex: 'PersonDispHist_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.MorbusOnkoBasePersonStateGrid.id + "\").showRecordMenu(this, " + metaData.rowIndex + ");'></div>";
				}
			}, {
				hidden: true,
				text: 'MorbusOnkoBasePersonState_id',
				dataIndex: 'MorbusOnkoBasePersonState_id'
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'MorbusOnkoBasePersonState_id', type: 'int' },
					{ name: 'MorbusOnkoBasePersonState_setDT', type: 'date', dateFormat: 'd.m.Y'},
					{ name: 'OnkoPersonStateType_id_Name', type: 'string' },
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=MorbusOnkoBasePersonState&m=loadList',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: {
					property: 'MorbusOnkoBasePersonState_setDT',
					direction: 'DESC'
				}
			})
		});
		
		this.MorbusOnkoBasePSGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			region: 'center',
			height: 580,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					iconCls:'menu_dispedit',
					handler: function() {
						me.openMorbusOnkoSpecificForm('MorbusOnkoBasePS', false, 'edit');
					}
				}, {
					text: 'Удалить',
					iconCls:'menu_dispdel',
					handler: function() {
						me.deleteEvent('MorbusOnkoBasePS');
					}
				}]
			}),
			showRecordMenu: function(el, rowIndx) {
				this.recordMenu.rowIndex = rowIndx;
				this.recordMenu.showBy(el);
			},
			columns: [{
				text: 'Поступил',
				dataIndex: 'MorbusOnkoBasePS_setDT',
				width: 140,
				renderer: function (value) {
					return value ? value.format('d.m.Y') : '';
				}
			}, {
				text: 'Выписан',
				dataIndex: 'MorbusOnkoBasePS_disDT',
				width: 140,
				renderer: function (value) {
					return value ? value.format('d.m.Y') : '';
				}
			}, {
				text: 'Цель госпитализации',
				dataIndex: 'OnkoPurposeHospType_id_Name',
				flex: 1
			}, {
				width: 40,
				dataIndex: 'PersonDispHist_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.MorbusOnkoBasePSGrid.id + "\").showRecordMenu(this, " + metaData.rowIndex + ");'></div>";
				}
			}, {
				hidden: true,
				text: 'MorbusOnkoBasePS_id',
				dataIndex: 'MorbusOnkoBasePS_id'
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'MorbusOnkoBasePS_id', type: 'int' },
					{ name: 'MorbusOnkoBasePS_setDT', type: 'date', dateFormat: 'd.m.Y'},
					{ name: 'MorbusOnkoBasePS_disDT', type: 'date', dateFormat: 'd.m.Y'},
					{ name: 'OnkoPurposeHospType_id_Name', type: 'string' }
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=MorbusOnkoBasePS&m=loadList',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: {
					property: 'MorbusOnkoBasePS_setDT',
					direction: 'DESC'
				}
			})
		});
		
		this.LeftPanel = new Ext6.tab.Panel({
			xtype: 'side-navigation-tabs',
			border: false,
			region: 'center',
			tabPosition: 'left',
			tabRotation: 0,
			width: 1150,
			tabBar: {
				border: false,
				cls: 'left-tab-bar narrow-scroll onko-custome',
				style:{
					boxShadow: 'none'
				},
				defaults: {
					width: 300,
					cls: 'blue-tab',
					padding: '6 0 6 25',
					textAlign: 'left'
				}
			},
			activeItem: 0,
			defaults: {
				padding: '20 25',
				border: false
			},
			items: [
				{
					title: 'Диагноз',
					scrollable: true,
					cls: 'subFieldPanel',
					padding: '0',
					itemId: 'DiagPanel',
					items: [
						me.DiagPanel
					]
				}, {
					title: 'Сведения о проведении консилиума',
					itemId: 'OnkoConsult',
					items: [
						me.OnkoConsultGrid
					]
				}, {
					title: 'Схема лекарственной терапии',
					itemId: 'DrugTherapyScheme',
					hidden: getRegionNick() == 'kz',
					items: [
						me.DrugTherapySchemeGrid
					]
				}, {
					title: 'Данные о препаратах',
					itemId: 'MorbusOnkoDrug',
					items: [
						me.MorbusOnkoDrugGrid
					]
				}, {
					title: 'Специальное лечение',
					itemId: 'MorbusOnkoSpecTreat',
					items: [
						me.MorbusOnkoSpecTreatGrid
					]
				}, {
					title: 'Диагностика',
					itemId: 'MorbusOnkoLink',
					items: [
						me.MorbusOnkoLinkGrid
					]
				}, {
					title: 'Данные об отказах / противопоказаниях',
					itemId: 'MorbusOnkoRefusal',
					items: [
						me.MorbusOnkoRefusalGrid
					]
				}, {
					title: 'Химиотерапевтическое лечение',
					itemId: 'MorbusOnkoChemTer',
					items: [
						me.MorbusOnkoChemTerGrid
					]
				}, {
					title: 'Лучевое лечение',
					itemId: 'MorbusOnkoRadTer',
					items: [
						me.MorbusOnkoRadTerGrid
					]
				}, {
					title: 'Гормоноиммунотерапевтическое лечение',
					itemId: 'MorbusOnkoGormTer',
					items: [
						me.MorbusOnkoGormTerGrid
					]
				}, {
					title: 'Хирургическое лечение',
					itemId: 'MorbusOnkoHirTer',
					items: [
						me.MorbusOnkoHirTerGrid
					]
				}, {
					title: 'Неспецифическое лечение',
					itemId: 'MorbusOnkoNonSpecTer',
					items: [
						me.MorbusOnkoNonSpecTerGrid
					]
				}, {
					title: 'Извещения',
					itemId: 'EvnNotify',
					items: [
						me.EvnNotifyGrid
					]
				}, {
					title: 'Контроль состояния',
					layout: 'border',
					itemId: 'MorbusOnkoBasePersonState',
					items: [
						{
							region: 'north',
							border: false,
							items: [{
								labelWidth: 200,
								xtype: 'commonSprCombo',
								comboSubject: 'OnkoStatusYearEndType',
								width: 500,
								fieldLabel: 'Клиническая группа',
								name: 'OnkoStatusYearEndType_id'
							}]
						},
						me.MorbusOnkoBasePersonStateGrid
					]
				}, {
					title: 'Госпитализация',
					itemId: 'MorbusOnkoBasePS',
					items: [
						me.MorbusOnkoBasePSGrid
					]
				}
			],
			listeners: {
				tabchange: function () {
					var tab = this.getActiveTab();
					me.loadTabGrig(tab.itemId);
				}
			}
		});

		this.FormPanel = Ext6.create('Ext6.form.Panel', {
			border: false,
			url: '/?c=MorbusOnkoSpecifics&m=saveMorbusSpecific',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {})
			}),
			items: [
				me.LeftPanel
			]
		});

		Ext6.apply(this, {
			items: [
				this.PersonInfoPanel,
				this.ToolPanel,
				this.FormPanel
			],
			buttons: [{
				xtype: 'SimpleButton',
				handler:function () {
					me.hide();
				}
			}, {
				xtype: 'SubmitButton',
				handler:function () {
					me.save({doNotHide: true});
				}
			}, {
				xtype: 'SubmitButton',
				text: 'Сохранить и закрыть',
				handler:function () {
					me.save();
				}
			}]
		});

		this.callParent(arguments);
    }
});