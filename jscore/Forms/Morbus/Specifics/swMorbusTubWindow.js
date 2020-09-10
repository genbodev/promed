/**
* swMorbusTubWindow - Форма просмотра записи регистра с типом «Туберкулез»
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      24.05.2012
* @prefix       MHW
*/

sw.Promed.swMorbusTubWindow = Ext.extend(sw.Promed.BaseForm, 
{
	width : 400,
	height : 400,
	modal: true,
	resizable: false,
	autoHeight: false,
	closeAction :'hide',
	autoScroll: true,
	border : false,
	plain : false,
	action: null,
	maximized: true,
	title: lang['zapis_registra'],
	listeners: {
		'hide': function(win) {
			win.onHide(win.isChange);
		},
		'beforeShow': function(win) {
			if(!getWnd('swWorkPlaceMZSpecWindow').isVisible() && !isUserGroup('MIACSuperAdmin'))
			{
				if (String(getGlobalOptions().groups).indexOf('Tub', 0) < 0)
				{
					sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «Регистр по туберкулезным заболеваниям»');
					return false;
				}
			}
		}
	},
	createMorbusTubHtmlForm: function(name, el_data) {
		console.log("*********************");
		console.log(el_data + ' ' + el_data.object_id);
		if(this.allowSpecificEdit == false) {
			return false;
		}
		var morbus_id = el_data.object_id.split('_')[1];
		var params = this.rightPanel.getObjectData('MorbusTub',morbus_id);
		if(typeof params != 'object') {
			return false;
		}
		var form = this;
		var cmp, ct, elinputid, eloutputid, config;
		var empty_value = '<span style="color: #666;">Не указано</span>';
		var onChange = function(conf){
			var save_tb1 = Ext.get('MorbusTub_'+el_data.object_id+'_toolbarMorbusTub');
			var save_tb2 = Ext.get('MorbusTub_'+el_data.object_id+'_toolbarMorbusTub2');
			var save_tb3 = Ext.get('MorbusTub_'+el_data.object_id+'_toolbarMorbusTub3');
            var save_tb4 = Ext.get('MorbusTub_'+el_data.object_id+'_toolbarMorbusTub4');
            var save_tb5 = Ext.get('MorbusTub_'+el_data.object_id+'_toolbarMorbusTub5');
            var save_tb6 = Ext.get('MorbusTub_'+el_data.object_id+'_toolbarMorbusTub6');
			switch(conf.name){
				case 'Diag':
                case 'TubDiag':
                case 'PersonResidenceType':
				case 'PersonDecreedGroup':
				case 'TubPhase':
				case 'PersonLivingFacilies':
					save_tb1.setDisplayed('block');
					break;
				
				case 'TubSickGroupType':
				case 'MorbusTub_RegNumCard':
				case 'MorbusTub_begDT':
				case 'MorbusTub_FirstDT':
				case 'MorbusTub_DiagDT':
					save_tb2.setDisplayed('block');
					break;
				
				case 'TubResultChemClass':
				case 'TubResultChemType':
				case 'MorbusTub_ResultDT':
				case 'TubResultDeathType':
				case 'MorbusTub_deadDT':
				case 'MorbusTub_breakDT':
				case 'TubBreakChemType':
				case 'MorbusTub_disDT':
				case 'MorbusTub_ConvDT':
				case 'MorbusTub_SanatorDT':
				case 'MorbusTub_unsetDT':
				case 'MorbusTub_CountDay':
				case 'PersonDispGroup':
				case 'TubDisability':
					save_tb3.setDisplayed('block');
					break;

                case 'MorbusTubMDR_RegNumPerson':
                case 'MorbusTubMDR_RegNumCard':
                case 'MorbusTubMDR_regDT':
                case 'MorbusTubMDR_regdiagDT':
                case 'MorbusTubMDR_begDT':
                case 'MorbusTubMDR_GroupDisp':
                case 'MorbusTubMDR_TubDiag':
                case 'MorbusTubMDR_TubSickGroupType':
                case 'MorbusTubMDR_IsPathology':
                case 'MorbusTubMDR_IsART':
                case 'MorbusTubMDR_IsCotrim':
                case 'MorbusTubMDR_IsDrugFirst':
                case 'MorbusTubMDR_IsDrugSecond':
                case 'MorbusTubMDR_IsDrugResult':
                case 'MorbusTubMDR_IsEmpiric':
                    save_tb4.setDisplayed('block');
                    break;

                case 'SopDiag1':
                case 'SopDiag2':
                case 'SopDiag3':
                case 'SopDiag4':
                case 'SopDiag5':
                case 'SopDiag6':
                case 'SopDiag7':
                case 'SopDiag8':
                case 'SopDiag_Descr':
					save_tb5.setDisplayed('block');
					break;

				case 'RiskType1':
                case 'RiskType2':
                case 'RiskType3':
                case 'RiskType4':
                case 'RiskType5':
                case 'RiskType6':
                case 'RiskType8':
					save_tb6.setDisplayed('block');
					break;
			}

			if(!this.changed_fields) this.changed_fields = {};
			this.changed_fields[conf.field_name] = conf;
		}.createDelegate(this);

		var onCancel = function(conf){
			if(!this.changed_fields) this.changed_fields = {};
			if(!this.changed_fields[conf.field_name]) {
				conf.elOutput.setDisplayed('inline');
				conf.elInputWrap.setDisplayed('none');
				conf.elInput.destroy();
				this.input_cmp_list[conf.elOutputId] = false;
			}
		}.createDelegate(this);

		if(!this.input_cmp_list) this.input_cmp_list = {};

		var getBaseConfig = function(options){
			return {
				hideLabel: true
				,renderTo: options.elInputId
				,listeners:
				{
					blur: function(f) {
						options.elInput = f;
						onCancel(options);
					},
					render: function(f) {
						/*if(options.type == 'id') {
							if(!f.getStore() || f.getStore().getCount()==0) log('not store: ' + options.field_name);
							debugger;
							var dataid = options.elOutput.getAttribute('dataid');
							if(!Ext.isEmpty(dataid)) {
								f.setValue(parseInt(dataid));
							}
						} else*/ if(options.type == 'checkbox') {
							var dataid = options.elOutput.getAttribute('dataid');
							if(!Ext.isEmpty(dataid) && dataid == 1) {
								f.setValue(true);
							} else {
								f.setValue(false);
							}
						} else {
							f.setValue(params[options.field_name]);
						}
					},
					change: function(f,n,o) {
						if(options.type == 'date') {
							options.outputValue = (n)?n.format('d.m.Y'):empty_value;
							options.value = (n)?n.format('d.m.Y'):null;
						}
						if(options.type.inlist(['string','int'])) {
							options.outputValue = (n)?n:empty_value;
							options.value = n || null;
						}
						if(options.type == 'id') {
							var rec = (n)?f.getStore().getById(n):false;
							if(rec) {
								options.value = n;
								if(options.codeField) {
									options.outputValue = rec.get(options.codeField) + '. ' + rec.get(f.displayField);
								} else {
									options.outputValue = rec.get(f.displayField);
								}
							} else {
								options.value = 0;
								options.outputValue = empty_value;
							}
						}
						if(options.type.inlist(['checkbox'])) {
							options.outputValue = (n)?n:empty_value;
							options.value = (n)?2:1;
						}
						options.elInput = f;
						if (n!=o)
							onChange(options);
					}
				}
			};
		};
		eloutputid = 'MorbusTub_'+ el_data.object_id +'_input'+name;
		elinputid = 'MorbusTub_'+ el_data.object_id +'_inputarea'+name;
		eloutput = Ext.get(eloutputid);
		ct = Ext.get(elinputid);

		switch(name){
			// даты
			case 'MorbusTub_begDT'://дата начала заболевания
			case 'MorbusTub_FirstDT'://дата первого обращения
			case 'MorbusTub_DiagDT'://Дата установления диагноза
			case 'MorbusTub_ResultDT':
			case 'MorbusTub_deadDT':
			case 'MorbusTub_disDT':
			case 'MorbusTub_breakDT':
			case 'MorbusTub_unsetDT':
			case 'MorbusTub_ConvDT':
			case 'MorbusTub_SanatorDT':
            case 'MorbusTubMDR_regDT':
            case 'MorbusTubMDR_regdiagDT':
            case 'MorbusTubMDR_begDT':
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'date'
						,field_name: name
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: null
						,elInputWrap: ct
						,elInput: null
					});
					config.width = 90;
					cmp = new sw.Promed.SwDateField(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'MorbusTub_CountDay':
			case 'MorbusTub_RegNumCard':

				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'int'
						,field_name: name
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,elInputWrap: ct
						,elInput: null
					});
					config.width = 100;
					config.maskRe = new RegExp("^[0-9]*$");
					config.allowDecimals = false;
					config.allowNegative = false;
					cmp = new Ext.form.NumberField(config);
					cmp.focus(true, 200);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
            case 'MorbusTubMDR_RegNumPerson':
            case 'MorbusTubMDR_RegNumCard':
            case 'MorbusTubMDR_GroupDisp':
            case 'SopDiag_Descr':
                if(ct && !this.input_cmp_list[eloutputid]) {
                    ct.setDisplayed('block');
                    eloutput.setDisplayed('none');
                    config = getBaseConfig({
                        name: name
                        ,type: 'string'
                        ,field_name: name
                        ,elOutputId: eloutputid
                        ,elInputId: elinputid
                        ,elOutput: eloutput
                        ,outputValue: empty_value
                        ,elInputWrap: ct
                        ,elInput: null
                    });
                    config.hideLabel = true;
                    config.width = 200;
                    //config.maskRe = new RegExp("^[0-9]*$");
                    if ('MorbusTubMDR_GroupDisp' == name) {
                        config.maxLength = 20;
                        config.width = 100;
                    }
                    cmp = new Ext.form.TextField(config);
                    cmp.focus(true, 500);
                    this.input_cmp_list[eloutputid] = cmp;
                }
                break;
            case 'MorbusTubMDR_IsPathology':
            case 'MorbusTubMDR_IsART':
            case 'MorbusTubMDR_IsCotrim':
            case 'MorbusTubMDR_IsDrugFirst':
            case 'MorbusTubMDR_IsDrugSecond':
            case 'MorbusTubMDR_IsDrugResult':
            case 'MorbusTubMDR_IsEmpiric':
                if(ct && !this.input_cmp_list[eloutputid]) {
                    ct.setDisplayed('block');
                    eloutput.setDisplayed('none');
                    config = getBaseConfig({
                        name: name
                        ,type: 'id'
                        ,field_name: name
                        ,elOutputId: eloutputid
                        ,elInputId: elinputid
                        ,elOutput: eloutput
                        ,outputValue: empty_value
                        ,elInputWrap: ct
                        ,elInput: null
                    });
                    config.width = 70;
                    config.comboSubject = 'YesNo';
                    config.typeCode = 'int';
                    config.autoLoad = true;
                    cmp = new sw.Promed.SwCommonSprCombo(config);
                    cmp.focus(true, 500);
                    this.input_cmp_list[eloutputid] = cmp;
                }
                break;
            case 'SopDiag1':
            case 'SopDiag2':
            case 'SopDiag3':
            case 'SopDiag4':
            case 'SopDiag5':
            case 'SopDiag6':
            case 'SopDiag7':
            case 'SopDiag8':
            case 'RiskType1':
            case 'RiskType2':
            case 'RiskType3':
            case 'RiskType4':
            case 'RiskType5':
            case 'RiskType6':
            case 'RiskType8':
                if(ct && !this.input_cmp_list[eloutputid]) {log(11);
					ct.setDisplayed('none');
                    eloutput.setDisplayed('block');
                    config = getBaseConfig({
                        name: name
                        ,type: 'checkbox'
                        ,field_name: name
                        ,elOutputId: eloutputid
                        ,elInputId: elinputid
                        ,elOutput: eloutput
                        ,outputValue: empty_value
                        ,elInputWrap: ct
                        ,elInput: null
                    });
                    cmp = new Ext.form.Checkbox(config);
                    cmp.focus(true, 500);
                    cmp.fireEvent('change',cmp,cmp.getValue());
                    this.input_cmp_list[eloutputid] = cmp;
                }
                break;
			case 'Diag':
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'id'
						,field_name: 'Diag_id'
						,codeField: 'Diag_Code'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: null
						,elInputWrap: ct
						,elInput: null
					});
					config.width = 350;
					config.listWidth = 500;
					var dataid = eloutput.getAttribute('dataid');
					config.hiddenName = 'Diag_id';
					config.MorbusType_SysNick = 'tub';
					config.registryType = 'TubRegistry';
					config.filterDiag = [
						2377, 2378, 2379, 2380, 2381, 2382, 2383, 2384, 2385, 2386, 
						2387, 2388, 2389, 2390, 2391, 2392, 2393, 2394, 2395, 2396, 
						2397, 2398, 2399, 2400, 2401, 2402, 2403, 2404, 2405, 2406, 
						2407, 2408, 2409, 2410, 2411, 2412, 2413
					];
					var listeners = Ext.apply({}, config.listeners);
					delete config.listeners;
					cmp = new sw.Promed.SwDiagCombo(config);
					cmp.on('blur', listeners.blur);
					cmp.on('render', listeners.render);
					cmp.onChange = listeners.change;
					cmp.getStore().load({
						params: {
							where: 'where Diag_id = '+ dataid
							,clause: {where: 'record["Diag_id"] == "'+ dataid +'"' }
						},
						callback: function(){
							if(this.getStore().getCount() > 0) {
								this.setValue(dataid);
							}
						},
						scope: cmp
					});
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
            case 'MorbusTubMDR_TubSickGroupType':
                if(ct && !this.input_cmp_list[eloutputid]) {
                    ct.setDisplayed('block');
                    eloutput.setDisplayed('none');
                    config = getBaseConfig({
                        name: name
                        ,type: 'id'
                        ,field_name: name + '_id'
                        ,elOutputId: eloutputid
                        ,elInputId: elinputid
                        ,elOutput: eloutput
                        ,outputValue: empty_value
                        ,elInputWrap: ct
                        ,elInput: null
                    });
                    if ('MorbusTubMDR_TubSickGroupType' == name) {
                        config.hiddenName = 'MorbusTubMDR_TubSickGroupType_id';
                        config.isMDR = true;
                    }
                    config.onLoadStore = function() {
                        this.focus(true, 500);
                    };
                    config.comboSubject = 'TubSickGroupType';
                    config.typeCode = 'int';
                    config.width = 250;
                    config.listWidth = 500;
                    cmp = new sw.Promed.SwTubCommonSprCombo(config);
                    this.input_cmp_list[eloutputid] = cmp;
                }
                break;
			case 'TubSickGroupType':
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'id'
						,field_name: name + '_id'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,elInputWrap: ct
						,elInput: null
					});
					config.listWidth = 500;
					config.comboSubject = name;
					config.moreFields = [
						{name: 'TubSickGroupType_begDT', mapping: 'TubSickGroupType_begDT' },
						{name: 'TubSickGroupType_endDT', mapping: 'TubSickGroupType_endDT' }
					];
					config.typeCode = 'int';
					config.autoLoad = true;
					config.onLoadStore = function() {
						var store = this.getStore();
						store.clearFilter();
						this.lastQuery = '';
						var curDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
						var TubSickGroupType_begDT = null;
						var TubSickGroupType_endDT = null;
						store.filterBy(function (rec) {
							if(rec.get("TubSickGroupType_begDT") != null){
								TubSickGroupType_begDT = Date.parseDate(rec.get("TubSickGroupType_begDT"), 'd.m.Y');
							} else {
								TubSickGroupType_begDT = null;
							}
							if(rec.get("TubSickGroupType_endDT") != null){
								TubSickGroupType_endDT = Date.parseDate(rec.get("TubSickGroupType_endDT"), 'd.m.Y');
							} else {
								TubSickGroupType_endDT = null;
							}
							if(curDate >= TubSickGroupType_begDT && curDate < TubSickGroupType_endDT ||
								TubSickGroupType_begDT == null && curDate < TubSickGroupType_endDT ||
								curDate >= TubSickGroupType_begDT && TubSickGroupType_endDT == null ||
								TubSickGroupType_begDT == null && TubSickGroupType_endDT == null) {
								return true;
							} else {
								return false;
							}
						});
					};
					cmp = new sw.Promed.SwCommonSprCombo(config);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'TubResultChemClass':
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'id'
						,field_name: name + '_id'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,elInputWrap: ct
						,elInput: null
					});
					config.listWidth = 700;
					config.comboSubject = name;
					config.moreFields = [
						{name: 'TubResultChemClass_begDT', mapping: 'TubResultChemClass_begDT' },
						{name: 'TubResultChemClass_endDT', mapping: 'TubResultChemClass_endDT' }
					];
					config.typeCode = 'int';
					config.autoLoad = true;
					config.onLoadStore = function() {
						var store = this.getStore();
						store.clearFilter();
						this.lastQuery = '';
						var curDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
						var TubResultChemClass_begDT = null;
						var TubResultChemClass_endDT = null;
						store.filterBy(function (rec) {
							if(rec.get("TubResultChemClass_begDT") != null){
								TubResultChemClass_begDT = Date.parseDate(rec.get("TubResultChemClass_begDT"), 'd.m.Y');
							} else {
								TubResultChemClass_begDT = null;
							}
							if(rec.get("TubResultChemClass_endDT") != null){
								TubResultChemClass_endDT = Date.parseDate(rec.get("TubResultChemClass_endDT"), 'd.m.Y');
							} else {
								TubResultChemClass_endDT = null;
							}
							if(curDate >= TubResultChemClass_begDT && curDate < TubResultChemClass_endDT ||
								TubResultChemClass_begDT == null && curDate < TubResultChemClass_endDT ||
								curDate >= TubResultChemClass_begDT && TubResultChemClass_endDT == null ||
								TubResultChemClass_begDT == null && TubResultChemClass_endDT == null) {
								return true;
							} else {
								return false;
							}
						});
					};
					cmp = new sw.Promed.SwCommonSprCombo(config);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'TubDiag':
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'id'
						,field_name: name + '_id'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,elInputWrap: ct
						,elInput: null
					});
					config.listWidth = 800;
					config.comboSubject = name;
					config.moreFields = [
						{name: 'TubDiag_begDT', mapping: 'TubDiag_begDT' },
						{name: 'TubDiag_endDT', mapping: 'TubDiag_endDT' }
					];
					config.typeCode = 'int';
					config.autoLoad = true;
					config.onLoadStore = function() {
						var store = this.getStore();
						store.clearFilter();
						this.lastQuery = '';
						var curDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
						var TubDiag_begDT = null;
						var TubDiag_endDT = null;
							store.filterBy(function (rec) {
							if(rec.get("TubDiag_begDT") != null){
								TubDiag_begDT = Date.parseDate(rec.get("TubDiag_begDT"), 'd.m.Y');
							} else {
								TubDiag_begDT = null;
							}
							if(rec.get("TubDiag_endDT") != null){
								TubDiag_endDT = Date.parseDate(rec.get("TubDiag_endDT"), 'd.m.Y');
							} else {
								TubDiag_endDT = null;
							}
							if(curDate >= TubDiag_begDT && curDate < TubDiag_endDT ||
								TubDiag_begDT == null && curDate < TubDiag_endDT ||
								curDate >= TubDiag_begDT && TubDiag_endDT == null ||
								TubDiag_begDT == null && TubDiag_endDT == null) {
								return true;
							} else {
								return false;
							}
						});
					};
					cmp = new sw.Promed.SwCommonSprCombo(config);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'TubPhase':
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'id'
						,field_name: name + '_id'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,elInputWrap: ct
						,elInput: null
					});
					config.comboSubject = name;
					config.moreFields = [
						{name: 'TubPhase_begDT', mapping: 'TubPhase_begDT'},
						{name: 'TubPhase_endDT', mapping: 'TubPhase_endDT'}
					];
					config.typeCode = 'int';
					config.autoLoad = true;
					config.onLoadStore = function() {						
						var store = this.getStore();
						store.clearFilter();
						this.lastQuery = '';
						var curDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');						
						var TubPhase_begDT = null;
						var TubPhase_endDT = null;
						store.filterBy(function (rec) {							
							if(rec.get("TubPhase_begDT") != null){
								var TubPhase_begDT = Date.parseDate(rec.get("TubPhase_begDT"), 'd.m.Y');
							} else {
								TubPhase_begDT = null;
							}
							if(rec.get("TubPhase_endDT") != null){
								var TubPhase_endDT = Date.parseDate(rec.get("TubPhase_endDT"), 'd.m.Y');
							} else {
								TubPhase_endDT = null;
							}
							if(curDate >= TubPhase_begDT && curDate < TubPhase_endDT ||
								TubPhase_begDT == null && curDate < TubPhase_endDT ||
								curDate >= TubPhase_begDT && TubPhase_endDT == null ||
								TubPhase_begDT == null && TubPhase_endDT == null) {
								return true;
							} else {
								return false;
							}
						});
					};
					cmp = new sw.Promed.SwCommonSprCombo(config);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'TubResultChemType':						
            case 'MorbusTubMDR_TubDiag':
			case 'TubBreakChemType':
            case 'PersonResidenceType':
            case 'PersonDecreedGroup':						
            case 'PersonLivingFacilies':
            case 'PersonDispGroup':
			case 'TubDisability':
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'id'
						,field_name: name + '_id'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,elInputWrap: ct
						,elInput: null
					});
					config.width = 250;
					config.listWidth = 500;
					config.comboSubject = name;
                    if ('MorbusTubMDR_TubDiag' == name) {
                        config.comboSubject = 'TubDiag';
                    }
					config.typeCode = 'int';
					config.autoLoad = true;
					cmp = new sw.Promed.SwCommonSprCombo(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'TubResultDeathType':
				if(ct && !this.input_cmp_list[eloutputid]) {
					if ( (Ext.isEmpty(this.changed_fields['MorbusTub_deadDT'])
						|| Ext.isEmpty(this.changed_fields['MorbusTub_deadDT'].value))
						&& Ext.isEmpty(params['MorbusTub_deadDT'])
					) {
						break;
					}
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'id'
						,field_name: name + '_id'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: empty_value
						,elInputWrap: ct
						,elInput: null
					});
					config.width = 250;
					config.listWidth = 500;
					config.comboSubject = name;
					if ('MorbusTubMDR_TubDiag' == name) {
						config.comboSubject = 'TubDiag';
					}
					config.typeCode = 'int';
					config.autoLoad = true;
					cmp = new sw.Promed.SwCommonSprCombo(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
		}
	},
	openMorbusTubSpecificForm: function(options) {
		if(this.allowSpecificEdit == false) {
			return false;
		}
		if(!options.action || !options.object || !options.eldata) {
			return false;
		}

		var win_name,
			object_id,
			data,
			mhdata,
			params = {formParams: {}};

		/*
		 log('openMorbusTubSpecificForm');
		 log(options);
		 */
		if(options.action == 'add') {
			object_id = (options.eldata.object_id.split('_').length > 1)?options.eldata.object_id.split('_')[1]:options.eldata.object_id;
			mhdata = options.mhdata || this.rightPanel.getObjectData('MorbusTub',object_id);
			if(!mhdata) {
				return false;
			}
		} else {
			object_id = (options.eldata.object_id.split('_').length > 1)?options.eldata.object_id.split('_')[1]:options.eldata.object_id;
			data = this.rightPanel.getObjectData(options.object,object_id);
			if(!data) {
				return false;
			}
			mhdata = options.mhdata || this.rightPanel.getObjectData('MorbusTub',data.MorbusTub_id);

			if(!mhdata) {
				return false;
			}
		}
		params.callback = function() {
			var reload_params = {
				section_code: options.object,
				object_key: options.object +'_id',
				object_value: (object_id<0)?data.MorbusTub_id:object_id,
				parent_object_key: 'MorbusTub_id',
				parent_object_value: mhdata.MorbusTub_id,
				param_name: 'MorbusTub_pid',
				param_value: mhdata.MorbusTub_pid,
				section_id: options.object +'List_'+ mhdata.MorbusTub_pid +'_'+ mhdata.MorbusTub_id
			};

			if(options.object == 'MorbusTubDiagGeneralForm') {
				reload_params.object_key = 'TubDiagGeneralForm_id';
			}

			if(options.object == 'MorbusTubAdvice') {
				//reload_params.section_id = 'MorbusTubAdviceItems_'+ mhdata.MorbusTub_pid +'_'+ mhdata.MorbusTub_id;
				this.loadNodeViewForm();
			} else {
				this.rightPanel.reloadViewForm(reload_params);
			}
		}.createDelegate(this);

		switch(options.object) {
			case 'MorbusTubAdvice':
			case 'MorbusTubDiagSop':
			case 'MorbusTubDiagGeneralForm':
			case 'MorbusTubConditChem':
			case 'MorbusTubStudyResult':
			case 'MorbusTubPrescr':
			case 'EvnDirectionTub':
            case 'MorbusTubMDRPrescr':
            case 'MorbusTubMDRStudyResult':

				win_name = 'sw'+options.object+'Window';
				params.action = options.action;
				params[options.object+'_id'] = (params.action=='edit')?object_id:null;
				params.formParams = {
                    MorbusTub_id: mhdata.MorbusTub_id,
                    MorbusBase_id: mhdata.MorbusBase_id,
                    MorbusTubMDR_id: mhdata.MorbusTubMDR_id,
                    Person_id: this.Person_id,
                    Evn_id: null
                };
				break;
			default:
				return false;
		}
		if(options.object == 'MorbusTubDiagGeneralForm') {
			params['TubDiagGeneralForm_id'] = (params.action=='edit')?object_id:null;
		}
		getWnd(win_name).show(params);
	},
	/**
	 * Сохраняет данные по специфике
	 * @param btn_name
	 * @param el_data
	 * @return {Boolean}
	 */
	submitMorbusTubHtmlForm: function(btn_name, el_data) {
		if(this.allowSpecificEdit == false) {
			return false;
		}

		var save_tb1 = Ext.get('MorbusTub_'+el_data.object_id+'_toolbarMorbusTub');
		var save_tb2 = Ext.get('MorbusTub_'+el_data.object_id+'_toolbarMorbusTub2');
		var save_tb3 = Ext.get('MorbusTub_'+el_data.object_id+'_toolbarMorbusTub3');
        var save_tb4 = Ext.get('MorbusTub_'+el_data.object_id+'_toolbarMorbusTub4');
        var save_tb5 = Ext.get('MorbusTub_'+el_data.object_id+'_toolbarMorbusTub5');
        var save_tb6 = Ext.get('MorbusTub_'+el_data.object_id+'_toolbarMorbusTub6');

		var params = this.rightPanel.getObjectData('MorbusTub',el_data.object_id.split('_')[1]);
		if(!params) {
			return false;
		}
		var SopDiags = {};
		var RiskTypes = {};
		for(var field_name in this.changed_fields) {
			if(this.changed_fields[field_name].name.indexOf('SopDiag') == 0 && this.changed_fields[field_name].name != 'SopDiag_Descr'){
				SopDiags[field_name] = this.changed_fields[field_name].value || '';
			} else if(this.changed_fields[field_name].name.indexOf('RiskType') == 0){
				RiskTypes[field_name] = this.changed_fields[field_name].value || '';
			} else {
				params[field_name] = this.changed_fields[field_name].value || '';
			}
		}
		params.SopDiags = JSON.stringify(SopDiags);
		params.RiskTypes = JSON.stringify(RiskTypes);
		params['Evn_pid'] = this.EvnDiagPLStom_id || this.EvnVizitPL_id || this.EvnSection_id || null;
		if (this.EvnVizitPL_id) {
			params['Mode'] = 'evnvizitpl_viewform';
		} else {
			params['Mode'] = 'personregister_viewform';
		}
		var form = this;
        form.requestSaveWithShowInfoMsg('/?c=MorbusTub&m=saveMorbusTub',
            params,
            function(result) {
                if ( result.success ) {
                    save_tb1.setDisplayed('none');
                    save_tb2.setDisplayed('none');
                    save_tb3.setDisplayed('none');
                    if (save_tb4) {
                        save_tb4.setDisplayed('none');
                    }
                    save_tb5.setDisplayed('none');
                    save_tb6.setDisplayed('none');
                    var conf;
                    for(var field_name in form.changed_fields) {
                        conf = form.changed_fields[field_name];
                        conf.elOutput.setDisplayed('inline');
                        conf.elOutput.update(conf.outputValue);
                        if(conf.type == 'id') conf.elOutput.setAttribute('dataid',conf.value);
                        conf.elInputWrap.setDisplayed('none');
                        conf.elInput.destroy();
                        form.input_cmp_list[conf.elOutputId] = false;
                    }
                    form.changed_fields = {};
                    form.isChange = true;
                }
            }, form);
	},
	/**
	 * Открывает соответсвующую акшену форму 
	 * 
	 * @param {open_form} Название открываемой формы, такое же как название объекта формы
	 * @param {id} Наименование идентификатора таблицы, передаваемого в форму
	 */
	openForm: function (open_form, id, oparams, mode, title, callback)
	{
		// Проверка
		if (getWnd(open_form).isVisible())
		{
			sw.swMsg.alert(lang['soobschenie'], lang['forma']+ ((title)?title:open_form) +lang['v_dannyiy_moment_otkryita']);
			return false;
		}
		else
		{
			if (!mode)
				mode = 'edit';
			var params = {
				action: mode,
				Person_id: this.Person_id,
				UserMedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
				UserLpuSection_id: this.userMedStaffFact.LpuSection_id,
				userMedStaffFact: this.userMedStaffFact,
				from: this.id,
				ARMType: this.userMedStaffFact.ARMType
			};
			params = Ext.apply(params || {}, oparams || {});
			if(open_form == 'swPersonCardHistoryWindow')
                params.action = (this.editType=='onlyRegister')?'view':'edit';
            if(open_form == 'swPersonEditWindow')
                params.readOnly = (this.editType=='onlyRegister')?true:false;
			getWnd(open_form).show(params);
		}
	},
	deleteEvent: function(event, data) {
		if(this.allowSpecificEdit == false) {
			return false;
		}
		if ( !event.inlist(['MorbusTubDiagSop','MorbusTubDiagGeneralForm','MorbusTubConditChem','MorbusTubPrescr','EvnDirectionTub',
            'MorbusTubMDRPrescr','MorbusTubMDRStudyResult']) )
		{
			return false;
		}

		if ( event.inlist(['MorbusTubDiagSop','MorbusTubDiagGeneralForm','MorbusTubConditChem','MorbusTubPrescr','EvnDirectionTub',
            'MorbusTubMDRPrescr','MorbusTubMDRStudyResult']) )
		{
			data.object_id = data.object_id.split('_')[1];
		}

		var formParams = this.rightPanel.getObjectData(data.object,data.object_id);

		var error = '';
		var question = '';
		var params = new Object();
		var url = '';
		var onSuccess;

		switch ( event ) {
			case 'MorbusTubDiagSop':
			case 'MorbusTubDiagGeneralForm':
			case 'MorbusTubConditChem':
			case 'MorbusTubPrescr':
			case 'EvnDirectionTub':
            case 'MorbusTubMDRPrescr':
            case 'MorbusTubMDRStudyResult':
				error = lang['pri_udalenii_voznikli_oshibki'];
				question = lang['udalit'];
				onSuccess = function(){
					var reload_params = {
						section_code: data.object,
						object_key: data.object +'_id',
						object_value: data.object_id,
						parent_object_key: 'MorbusTub_id',
						parent_object_value: formParams.MorbusTub_id,
						accessType: (this.allowSpecificEdit == true)?1:0,
						param_name: 'MorbusTub_pid',
						param_value: formParams.MorbusTub_pid || null,
						section_id: data.object +'List_'+ formParams.MorbusTub_pid +'_'+ formParams.MorbusTub_id
					};
					this.rightPanel.reloadViewForm(reload_params);
				}.createDelegate(this);
				if ('MorbusTubMDRStudyResult' == data.object) {
					url = '/?c=MorbusTub&m=deleteMorbusTubMDRStudyResult';
					params['MorbusTubMDRStudyResult_id'] = data.object_id;
				} else {
					url = '/?c=Utils&m=ObjectRecordDelete';
					params['obj_isEvn'] = (event == 'EvnDirectionTub')?'true':'false';
					params['id'] = data.object_id;
					params['object'] = data.object;
					if ('MorbusTubMDRPrescr' == data.object) {
						params['object'] = 'MorbusTubPrescr';
					} else if ('MorbusTubDiagGeneralForm' == data.object) {
						params['object'] = 'TubDiagGeneralForm';
					}
				}
				break;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление записи..."});
					loadMask.show();

					Ext.Ajax.request({
						failure: function(response, options) {
							loadMask.hide();
							sw.swMsg.alert(lang['oshibka'], error);
						},
						params: params,
						success: function(response, options) {
							loadMask.hide();
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if ( response_obj.success == false ) {
								sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : error);
							} else {
								onSuccess(response_obj);
							}
						}.createDelegate(this),
						url: url
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: lang['vopros']
		});

		return true;

	},
	loadNodeViewForm: function()
	{
		if (this.MorbusTub_pid) {
			this.viewObject.attributes.object_id = 'MorbusTub_pid';
			this.viewObject.attributes.object_value = this.MorbusTub_pid;
		}
		if (this.PersonRegister_id) {
			this.rightPanel.loadNodeViewForm(this.viewObject, {
				param_name: 'PersonRegister_id',
				param_value: this.PersonRegister_id
			});
		} else if (this.Morbus_id) {
			this.rightPanel.loadNodeViewForm(this.viewObject, {
				param_name: 'Morbus_id',
				param_value: this.Morbus_id
			});
		} else if (this.EvnDiagPLStom_id) { // создание специфики
			this.rightPanel.loadNodeViewForm(this.viewObject, {
				param_name: 'EvnDiagPLStom_id',
				param_value: this.EvnDiagPLStom_id
			});
		} else if (this.EvnVizitPL_id) { // создание специфики
			this.rightPanel.loadNodeViewForm(this.viewObject, {
				param_name: 'EvnVizitPL_id',
				param_value: this.EvnVizitPL_id
			});
		} else if (this.EvnSection_id) { // создание специфики
			this.rightPanel.loadNodeViewForm(this.viewObject, {
				param_name: 'EvnSection_id',
				param_value: this.EvnSection_id
			});
		}
	},
	show: function() 
	{
		sw.Promed.swMorbusTubWindow.superclass.show.apply(this, arguments);
		
		//log(arguments[0]);
		if ( !arguments[0] || !arguments[0].Person_id ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		this.editType = 'all';
        if(arguments[0] && arguments[0].editType){
            this.editType = arguments[0].editType;
        }
		this.Person_id = arguments[0].Person_id;
		this.EvnDiagPLStom_id = arguments[0].EvnDiagPLStom_id;
		this.PersonRegister_id = arguments[0].PersonRegister_id || null;
		this.userMedStaffFact = arguments[0].userMedStaffFact || sw.Promed.MedStaffFactByUser.last;
		this.EvnVizitPL_id = arguments[0].EvnVizitPL_id;
		this.EvnSection_id = arguments[0].EvnSection_id;
		this.Morbus_id = arguments[0].Morbus_id || null;
		this.MorbusTub_pid = arguments[0].MorbusTub_pid;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.isChange = false;
		this.allowSpecificEdit = arguments[0].allowSpecificEdit || false;

		if(arguments[0] && arguments[0].action == 'view')
			this.allowSpecificEdit = false;

		if (this.MorbusTub_pid) {
			this.setTitle('Специфика');
		}

		this.viewObject = {
			id: 'PersonMorbusTub_'+ this.Person_id,
			attributes: {
				accessType: (this.allowSpecificEdit == true)?'edit':'view',
				text: 'test',
				object: 'PersonMorbusTub',
				object_id: 'Person_id',
				object_value: this.Person_id			
			}
		};
		this.loadNodeViewForm();
	},
	initComponent: function() 
	{

		this.rightPanel = new Ext.Panel(
		{
			animCollapse: false,
			autoScroll: true,
			bodyStyle: 'background-color: #e3e3e3',
			floatable: false,			
			minSize: 400,
			region: 'center',
			id: 'rightEmkPanel',
			split: true,
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false,
				style: 'border 0px'
			},
			items: 
			[{
				html: ''
			}]
		});
		
		Ext.apply(this.rightPanel,sw.Promed.viewHtmlForm);
		this.rightPanel.ownerWindow = this;
        Ext.apply(this, sw.Promed.ViewPanelMsgMixin);
        this.viewPanel = this.rightPanel;
		var win = this;
		this.rightPanel.configActions = {
			PersonMorbusTub: {
				print: {
					actionType: 'view',
					sectionCode: 'PersonMorbusTub',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				editPhoto: {
					actionType: 'edit',
					sectionCode: 'person_data',
					handler: function(e, c, d) {
						var params = {
							action: 'loadimage',
							saveUrl: '/?c=PMMediaData&m=uploadPersonPhoto',
							enableFileDescription: false,
							saveParams: {Person_id: d.object_id},
							callback: function(data){
								if (data && data.person_thumbs_src)
								{
									document[('photo_person_'+ d.object_id)].src=data.person_thumbs_src +'?'+ Math.random();
								}
							}
						};
						getWnd('swFileUploadWindow').show(params);
					}
				},
				editPers: {
					actionType: 'edit',
					dblClick: true,
					sectionCode: 'person_data',
					handler: function(e, c, d) {
						var params = {
							callback: function(data){
								if (data && data.Person_id)
								{
									win.loadNodeViewForm();
								}
							}
						};
						win.openForm('swPersonEditWindow','XXX_id',params,'edit',lang['redaktirovanie_personalnyih_dannyih_patsienta']);
					}
				},
				printMedCard: {
					actionType: 'view',
					sectionCode: 'person_data', 
					handler: function(e, c, d) {
						var data = win.rightPanel.getObjectData('PersonMorbusTub',d.object_id);
						if (getRegionNick() =='ufa'){
							printMedCard4Ufa(data.PersonCard_id);
							return;
						}
						if(getRegionNick().inlist([ 'buryatiya', 'astra', 'perm', 'ekb', 'pskov', 'krym', 'khak', 'kaluga' ])){
							var PersonCard = 0;
							if(!Ext.isEmpty(data.PersonCard_id)){
								var PersonCard = data.PersonCard_id;
							}
							printBirt({
		                        'Report_FileName': 'pan_PersonCard_f025u.rptdesign',
		                        'Report_Params': '&paramPerson=' + data.Person_id + '&paramPersonCard=' + PersonCard + '&paramLpu=' + getLpuIdForPrint(),
		                        'Report_Format': 'pdf'
		                    });
						} else {
							Ext.Ajax.request(
							{
								url : '/?c=PersonCard&m=printMedCard',
								params : 
								{
									PersonCard_id: data.PersonCard_id
								},
								callback: function(options, success, response)
								{
									if ( success ) {
								        var response_obj = Ext.util.JSON.decode(response.responseText);
										openNewWindow(response_obj.result);
									}
								}
							});
						}
					}.createDelegate(this)	
				},				
				editAttach: {
					actionType: 'edit',
					sectionCode: 'person_data',
					handler: function(e, c, d) {
						var params = {
							callback: Ext.emptyFn, // почему-то в форме swPersonCardHistoryWindow вызывается только при нажатии на кн. "помощь"
							onHide: function(data){
								// нужно обновить секцию person_data, пока будем перезагружать все
								win.loadNodeViewForm();
							}
						};
						win.openForm('swPersonCardHistoryWindow','XXX_id',params,'edit',lang['istoriya_prikrepleniya']);
					}
				}
			},
			MorbusTub: {
				toggleDisplayDiag: {actionType: 'view',sectionCode: 'MorbusTub', handler: function(e, c, d) {
					var id = 'MorbusTubDiag_'+ d.object_id;
					win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed()); }
				},
				inputMorbusTub_RegNumCard: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusTubHtmlForm('MorbusTub_RegNumCard', d);
					}
				},
				inputMorbusTub_begDT: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusTubHtmlForm('MorbusTub_begDT', d);
					}
				},
				inputMorbusTub_FirstDT: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusTubHtmlForm('MorbusTub_FirstDT', d);
					}
				},
				inputMorbusTub_DiagDT: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					dblClick: false,
					handler: function(e, c, d) {

						win.createMorbusTubHtmlForm('MorbusTub_DiagDT', d);
					}
				},
				inputTubSickGroupType: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					dblClick: false,
					handler: function(e, c, d) {

						win.createMorbusTubHtmlForm('TubSickGroupType', d);
					}
				},
				inputMorbusTub_ResultDT: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					dblClick: false,
					handler: function(e, c, d) {

						win.createMorbusTubHtmlForm('MorbusTub_ResultDT', d);
					}
				},
				inputMorbusTub_deadDT: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					dblClick: false,
					handler: function(e, c, d) {

						win.createMorbusTubHtmlForm('MorbusTub_deadDT', d);
					}
				},
				inputMorbusTub_breakDT: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					dblClick: false,
					handler: function(e, c, d) {

						win.createMorbusTubHtmlForm('MorbusTub_breakDT', d);
					}
				},
				inputMorbusTub_disDT: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					dblClick: false,
					handler: function(e, c, d) {

						win.createMorbusTubHtmlForm('MorbusTub_disDT', d);
					}
				},
				inputMorbusTub_unsetDT: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					dblClick: false,
					handler: function(e, c, d) {

						win.createMorbusTubHtmlForm('MorbusTub_unsetDT', d);
					}
				},
				inputMorbusTub_ConvDT: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					handler: function(e, c, d) {

						win.createMorbusTubHtmlForm('MorbusTub_ConvDT', d);
					}
				},
				inputMorbusTub_SanatorDT: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					handler: function(e, c, d) {

						win.createMorbusTubHtmlForm('MorbusTub_SanatorDT', d);
					}
				},
				inputTubResultChemClass: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					dblClick: false,
					handler: function(e, c, d) {

						win.createMorbusTubHtmlForm('TubResultChemClass', d);
					}
				},
				inputTubResultChemType: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusTubHtmlForm('TubResultChemType', d);
					}
				},
				inputTubResultDeathType: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusTubHtmlForm('TubResultDeathType', d);
					}
				},
                inputTubDiag: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    dblClick: false,
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('TubDiag', d);
                    }
                },
                inputPersonResidenceType: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    dblClick: false,
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('PersonResidenceType', d);
                    }
                },
				inputDiag: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					handler: function(e, c, d) {
						win.createMorbusTubHtmlForm('Diag', d);
					}
				},
				inputTubBreakChemType: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					handler: function(e, c, d) {
						win.createMorbusTubHtmlForm('TubBreakChemType', d);
					}
				},
				inputPersonDecreedGroup: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					handler: function(e, c, d) {
						win.createMorbusTubHtmlForm('PersonDecreedGroup', d);
					}
				},
				inputTubPhase: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					handler: function(e, c, d) {
						win.createMorbusTubHtmlForm('TubPhase', d);
					}
				},
				inputTubDisability: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					handler: function(e, c, d) {
						win.createMorbusTubHtmlForm('TubDisability', d);
					}
				},
				inputPersonLivingFacilies: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					handler: function(e, c, d) {
						win.createMorbusTubHtmlForm('PersonLivingFacilies', d);
					}
				},
				inputPersonDispGroup: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					handler: function(e, c, d) {
						win.createMorbusTubHtmlForm('PersonDispGroup', d);
					}
				},
				inputMorbusTub_CountDay: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					handler: function(e, c, d) {
						win.createMorbusTubHtmlForm('MorbusTub_CountDay', d);
					}
				},
				saveMorbusTub: {
					actionType: 'edit', sectionCode: 'MorbusTub', handler: function(e, c, d) { win.submitMorbusTubHtmlForm('saveMorbusTub',d); }
				},
				saveMorbusTub2: {
					actionType: 'edit', sectionCode: 'MorbusTub', handler: function(e, c, d) { win.submitMorbusTubHtmlForm('saveMorbusTub',d); }
				},
				saveMorbusTub3: {
					actionType: 'edit', sectionCode: 'MorbusTub', handler: function(e, c, d) { win.submitMorbusTubHtmlForm('saveMorbusTub',d); }
				},
				addMorbusTubAdvice: {
					actionType: 'edit',
					sectionCode: 'MorbusTub',
					handler: function(e, c, d) {
						win.openMorbusTubSpecificForm({action: 'add',object: 'MorbusTubAdvice', eldata: d});
					}
				},
				toggleDisplayMorbusTubAdviceList: {
					actionType: 'view',
					sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        var id = 'MorbusTubAdviceItems_'+ d.object_id;
                        win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
                    }
                },
				morbusPrint: {
					actionType: 'view',
					sectionCode: 'MorbusTub',
					handler: function(e, c, d) {
						var me = this;
						if (me.printMenu){
							me.printMenu.destroy();
							me.printMenu = null;
						}
						me.d = d;
						me.printMenu = new Ext.menu.Menu();
						
						var
							ids = d.object_id.split('_'),
							object_id,
							PersonRegister_id,
							specifics;
						
						if (typeof ids == 'object' && ids[1] && parseInt(ids[1]) > 0 ) {
							object_id = ids[1];
							specifics = win.rightPanel.viewFormDataStore.getById(me.d.object + '_' + object_id);
							
							if (typeof specifics == 'object' && !Ext.isEmpty(specifics.get('PersonRegister_id'))) {
								PersonRegister_id = specifics.get('PersonRegister_id');
							}
						} 
						
						if (!Ext.isEmpty(PersonRegister_id)){
							me.printMenu.add({
								text: 'Печать формы № 81/У',
								value: 'Печать формы № 81/У',
								handler: function(e, c, d) {
									printBirt({
										'Report_FileName': 'f81u.rptdesign',
										'Report_Params': '&paramRegistry=' + PersonRegister_id,
										'Report_Format': 'pdf'
									});
									return true;
								}.createDelegate(this)
							});
							me.printMenu.add({
								text: 'Печать формы № 01-ТБ/у',
								value: 'Печать формы № 01-ТБ/у',
								handler: function(e, c, d) {
									printBirt({
										'Report_FileName': 'F01_TB_u.rptdesign',
										'Report_Params': '&paramRegistry=' + PersonRegister_id,
										'Report_Format': 'pdf'
									});
									return true;
								}.createDelegate(this)
							});
							me.printMenu.add({
								text: 'Печать формы № 081-1/у',
								value: 'Печать формы № 081-1/у',
								handler: function(e, c, d) {
									printBirt({
										'Report_FileName': '081_1u_tub.rptdesign',
										'Report_Params': '&paramRegistry=' + PersonRegister_id,
										'Report_Format': 'pdf'
									});
									return true;
								}.createDelegate(this)
							});
						}
						var btnEl = Ext.get(d.object + '_' + d.object_id + '_morbusPrint');
						me.printMenu.show(btnEl);
					}
				},
                printLink: {
                    actionType: 'view',
                    sectionCode: 'MorbusTub',
					handler: function(e, c, d) {
                        var record = win.rightPanel.viewFormDataStore.getById('MorbusTub_'+ d.object_id.split('_')[1]);
                        if (record && record.get('Morbus_id')) {
                            window.open(((getGlobalOptions().birtpath) ? getGlobalOptions().birtpath : '')
                                + '/run?__report=report/f01mdrtubu.rptdesign&paramMorbus='
                                + record.get('Morbus_id') + '&__format=pdf');
                        }
					}
                },
                toggleDisplayMorbusTubMDR: {
                    actionType: 'view',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        var id = 'MorbusTubMDR_'+ d.object_id,
                            record = win.rightPanel.viewFormDataStore.getById('MorbusTub_'+ d.object_id.split('_')[1]),
                            isDisplayed = Ext.get(id).isDisplayed();
                        //log(d, win.rightPanel.viewFormDataStore);
                        if (!record) {
                            return false;
                        }
                        if (!record.get('MorbusTubMDR_id') && !isDisplayed) {
                            Ext.Msg.show({
                                title: lang['vopros'],
                                msg: lang['sozdat_meditsinskuyu_kartu_sluchaya_lecheniya_tuberkuleza_po_iv_rejimu_himioterapii_po_forme_№01-mlu-tb_u'],
                                buttons: Ext.Msg.OKCANCEL,// YESNO
                                fn: function(btn) {
                                    if (btn === 'ok') {
                                        win.requestSaveWithShowInfoMsg('/?c=MorbusTub&m=createMorbusTubMDR',
                                            {
                                                Morbus_id: record.get('Morbus_id')
                                            },
                                            function(result)
                                            {
                                                if ( result.success && result.MorbusTubMDR_id ) {
                                                    record.set('MorbusTubMDR_id', result.MorbusTubMDR_id);
                                                    record.commit(true);
                                                    win.rightPanel.viewFormDataStore.commitChanges();
                                                    win.rightPanel.toggleDisplay(id, isDisplayed);
                                                    var printWrap = Ext.get('MorbusTubMDR_'+ d.object_id + '_printWrap');
                                                    if (printWrap) {
                                                        printWrap.setStyle({display: 'block'});
                                                    }
                                                }
                                            }, win);
                                    }
                                },
                                icon: Ext.MessageBox.QUESTION
                            });
                            return false;
                        }
                        win.rightPanel.toggleDisplay(id, isDisplayed);
                        return true;
                    }
                },
                saveMorbusTub4: {
                    actionType: 'edit', sectionCode: 'MorbusTub', handler: function(e, c, d) {
                        win.submitMorbusTubHtmlForm('saveMorbusTub',d);
                    }
                },
                inputMorbusTubMDR_RegNumPerson: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('MorbusTubMDR_RegNumPerson', d);
                    }
                },
                inputMorbusTubMDR_RegNumCard: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('MorbusTubMDR_RegNumCard', d);
                    }
                },
                inputMorbusTubMDR_regDT: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('MorbusTubMDR_regDT', d);
                    }
                },
                inputMorbusTubMDR_regdiagDT: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('MorbusTubMDR_regdiagDT', d);
                    }
                },
                inputMorbusTubMDR_begDT: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('MorbusTubMDR_begDT', d);
                    }
                },
                inputMorbusTubMDR_GroupDisp: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('MorbusTubMDR_GroupDisp', d);
                    }
                },
                inputMorbusTubMDR_TubDiag: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('MorbusTubMDR_TubDiag', d);
                    }
                },
                inputMorbusTubMDR_TubSickGroupType: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('MorbusTubMDR_TubSickGroupType', d);
                    }
                },
                inputMorbusTubMDR_IsPathology: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('MorbusTubMDR_IsPathology', d);
                    }
                },
                inputMorbusTubMDR_IsART: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('MorbusTubMDR_IsART', d);
                    }
                },
                inputMorbusTubMDR_IsCotrim: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('MorbusTubMDR_IsCotrim', d);
                    }
                },
                inputMorbusTubMDR_IsDrugFirst: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('MorbusTubMDR_IsDrugFirst', d);
                    }
                },
                inputMorbusTubMDR_IsDrugSecond: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('MorbusTubMDR_IsDrugSecond', d);
                    }
                },
                inputMorbusTubMDR_IsDrugResult: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('MorbusTubMDR_IsDrugResult', d);
                    }
                },
                inputMorbusTubMDR_IsEmpiric: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('MorbusTubMDR_IsEmpiric', d);
                    }
				},
				saveMorbusTub5: {
					actionType: 'edit', sectionCode: 'MorbusTub', handler: function(e, c, d) { win.submitMorbusTubHtmlForm('saveMorbusTub',d); }
				},
				inputSopDiag1: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('SopDiag1', d);
                    }
				},
				inputSopDiag2: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('SopDiag2', d);
                    }
				},
				inputSopDiag3: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('SopDiag3', d);
                    }
				},
				inputSopDiag4: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('SopDiag4', d);
                    }
				},
				inputSopDiag5: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('SopDiag5', d);
                    }
				},
				inputSopDiag6: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('SopDiag6', d);
                    }
				},
				inputSopDiag7: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('SopDiag7', d);
                    }
				},
				inputSopDiag8: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('SopDiag8', d);
                    }
				},
				inputSopDiag_Descr: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('SopDiag_Descr', d);
                    }
				},
				saveMorbusTub6: {
					actionType: 'edit', sectionCode: 'MorbusTub', handler: function(e, c, d) { win.submitMorbusTubHtmlForm('saveMorbusTub',d); }
				},
				inputRiskType1: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('RiskType1', d);
                    }
				},
				inputRiskType2: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('RiskType2', d);
                    }
				},
				inputRiskType3: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('RiskType3', d);
                    }
				},
				inputRiskType4: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('RiskType4', d);
                    }
				},
				inputRiskType5: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('RiskType5', d);
                    }
				},
				inputRiskType6: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('RiskType6', d);
                    }
				},
				inputRiskType8: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTub',
                    handler: function(e, c, d) {
                        win.createMorbusTubHtmlForm('RiskType8', d);
                    }
				},
			},
			MorbusTubDiagSop: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusTubDiagSop',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusTubSpecificForm({action: 'edit',object: 'MorbusTubDiagSop', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusTubDiagSop',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusTubDiagSop',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusTubDiagSopList',
					handler: function(e, c, d) {
						win.openMorbusTubSpecificForm({action: 'add',object: 'MorbusTubDiagSop', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusTubDiagSopList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusTubDiagSopList',
					handler: function(e, c, d) {
						var id = 'MorbusTubDiagSopTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusTubDiagGeneralForm: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusTubDiagGeneralForm',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusTubSpecificForm({action: 'edit',object: 'MorbusTubDiagGeneralForm', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusTubDiagGeneralForm',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusTubDiagGeneralForm',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusTubDiagGeneralFormList',
					handler: function(e, c, d) {
						win.openMorbusTubSpecificForm({action: 'add',object: 'MorbusTubDiagGeneralForm', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusTubDiagGeneralFormList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusTubDiagGeneralFormList',
					handler: function(e, c, d) {
						var id = 'MorbusTubDiagGeneralFormTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusTubConditChem: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusTubConditChem',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusTubSpecificForm({action: 'edit',object: 'MorbusTubConditChem', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusTubConditChem',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusTubConditChem',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusTubConditChemList',
					handler: function(e, c, d) {
						win.openMorbusTubSpecificForm({action: 'add',object: 'MorbusTubConditChem', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusTubConditChemList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusTubConditChemList',
					handler: function(e, c, d) {
						var id = 'MorbusTubConditChemTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusTubAdvice: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusTubAdvice',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusTubSpecificForm({action: 'edit',object: 'MorbusTubAdvice', eldata: d});
					}
				}
			},
			MorbusTubStudyResult: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusTubStudyResult',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusTubSpecificForm({action: 'edit',object: 'MorbusTubStudyResult', eldata: d});
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusTubStudyResultList',
					handler: function(e, c, d) {
						win.openMorbusTubSpecificForm({action: 'add',object: 'MorbusTubStudyResult', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusTubStudyResultList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusTubStudyResultList',
					handler: function(e, c, d) {
						var id = 'MorbusTubStudyResultTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			EvnDirectionTub: {
				edit: {
					actionType: 'edit',
					sectionCode: 'EvnDirectionTub',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusTubSpecificForm({action: 'edit',object: 'EvnDirectionTub', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'EvnDirectionTub',
					handler: function(e, c, d) {
						win.deleteEvent('EvnDirectionTub',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'EvnDirectionTubList',
					handler: function(e, c, d) {
						win.openMorbusTubSpecificForm({action: 'add',object: 'EvnDirectionTub', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'EvnDirectionTubList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'EvnDirectionTubList',
					handler: function(e, c, d) {
						var id = 'EvnDirectionTubTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusTubPrescr: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusTubPrescr',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusTubSpecificForm({action: 'edit',object: 'MorbusTubPrescr', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusTubPrescr',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusTubPrescr',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusTubPrescrList',
					handler: function(e, c, d) {
						win.openMorbusTubSpecificForm({action: 'add',object: 'MorbusTubPrescr', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusTubPrescrList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusTubPrescrList',
					handler: function(e, c, d) {
						var id = 'MorbusTubPrescrTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
            MorbusTubMDRPrescr: {
                edit: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTubMDRPrescr',
                    dblClick: true,
                    handler: function(e, c, d) {
                        win.openMorbusTubSpecificForm({action: 'edit',object: 'MorbusTubMDRPrescr', eldata: d});
                    }
                },
                'delete': {
                    actionType: 'edit',
                    sectionCode: 'MorbusTubMDRPrescr',
                    handler: function(e, c, d) {
                        win.deleteEvent('MorbusTubMDRPrescr',d);
                    }
                },
                add: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTubMDRPrescrList',
                    handler: function(e, c, d) {
                        win.openMorbusTubSpecificForm({action: 'add',object: 'MorbusTubMDRPrescr', eldata: d});
                    }
                },
                print: {
                    actionType: 'view',
                    sectionCode: 'MorbusTubMDRPrescrList',
                    handler: function(e, c, d) {
                        win.rightPanel.printHtml(d.section_id);
                    }
                },
                toggleDisplay: {
                    actionType: 'view',
                    sectionCode: 'MorbusTubMDRPrescrList',
                    handler: function(e, c, d) {
                        var id = 'MorbusTubMDRPrescrTable_'+ d.object_id;
                        win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
                    }
                }
            },
            MorbusTubMDRStudyResult: {
                edit: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTubMDRStudyResult',
                    dblClick: true,
                    handler: function(e, c, d) {
                        win.openMorbusTubSpecificForm({action: 'edit',object: 'MorbusTubMDRStudyResult', eldata: d});
                    }
                },
                'delete': {
                    actionType: 'edit',
                    sectionCode: 'MorbusTubMDRStudyResult',
                    handler: function(e, c, d) {
                        win.deleteEvent('MorbusTubMDRStudyResult',d);
                    }
                },
                add: {
                    actionType: 'edit',
                    sectionCode: 'MorbusTubMDRStudyResultList',
                    handler: function(e, c, d) {
                        win.openMorbusTubSpecificForm({action: 'add',object: 'MorbusTubMDRStudyResult', eldata: d});
                    }
                },
                print: {
                    actionType: 'view',
                    sectionCode: 'MorbusTubMDRStudyResultList',
                    handler: function(e, c, d) {
                        win.rightPanel.printHtml(d.section_id);
                    }
                },
                toggleDisplay: {
                    actionType: 'view',
                    sectionCode: 'MorbusTubMDRStudyResultList',
                    handler: function(e, c, d) {
                        var id = 'MorbusTubMDRStudyResultTable_'+ d.object_id;
                        win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
                    }
                }
            },
			PersonPrivilege: {
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'PersonPrivilegeList',
					handler: function(e, c, d) {
						var id = 'PersonPrivilegeTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			PersonPrivilegeFed: {
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'PersonPrivilegeFedList',
					handler: function(e, c, d) {
						var id = 'PersonPrivilegeFedTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			/*DrugTub: {
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'DrugTubList',
					handler: function(e, c, d) {
						var id = 'DrugTubTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},*/
			PersonRegisterExport: {
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'PersonRegisterExportList',
					handler: function(e, c, d) {
						var id = 'PersonRegisterExportTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			}
		};
		
		Ext.apply(this, 
		{
			region: 'center',
			layout: 'border',
			buttons: 
			[{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
			items: {
				autoScroll: true,
				bodyBorder: false,
				frame: false,
				xtype: 'form',
				region: 'center',
				layout: 'border',
				border: false,
				items: [this.rightPanel]
			}
		});
		sw.Promed.swMorbusTubWindow.superclass.initComponent.apply(this, arguments);
	}
});
