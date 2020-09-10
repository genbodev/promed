/**
* swMorbusHepatitisWindow - окно простого заболевания.
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

sw.Promed.swMorbusHepatitisWindow = Ext.extend(sw.Promed.BaseForm, 
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
	title: langs('Запись регистра'),
	listeners: {
		'hide': function(win) {
			win.onHide(win.isChange);
		},
		'beforeShow': function(win) {
			if(!getWnd('swWorkPlaceMZSpecWindow').isVisible() && !isUserGroup('MIACSuperAdmin'))
			{
				if (String(getGlobalOptions().groups).indexOf('HepatitisRegistry', 0) < 0)
				{
					sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «Регистр по гепатиту»»');
					return false;
				}
			}
		}
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		this.FormPanel.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	checkAccessEdit: function(enable_msg) {
		if (this.allowSpecificEdit)
		{
			return true;
		}
		else
		{
			if(enable_msg) sw.swMsg.alert(lang['soobschenie'], lang['funktsiya_redaktirovaniya_zapreschena']);
			return false;
		}
	},
	openMorbusHepatitisSpecificForm: function(options) {
		if(!options.action || !options.object || !options.eldata) {
			return false;
		}
		
		var win_name,
			object_id,
			data,
			mhdata,
			evndata,
			//evnsysnick = (this.data.Code == 'EvnPL')?'EvnVizitPL':'EvnSection',
			params = {formParams: {}};
			
		/*
		log('openMorbusHepatitisSpecificForm');
		log(options);
		*/
		if(options.action == 'add') {
			object_id = (options.eldata.object_id.split('_').length > 1)?options.eldata.object_id.split('_')[1]:options.eldata.object_id;
			mhdata = options.mhdata || this.rightPanel.getObjectData('MorbusHepatitis',object_id);
			if(!mhdata) {
				return false;
			}
			if(this.checkAccessEdit() == false) {
				return false;
			}
		} else {
			object_id = (options.eldata.object_id.split('_').length > 1)?options.eldata.object_id.split('_')[1]:options.eldata.object_id;
			data = this.rightPanel.getObjectData(options.object,object_id);
			if(!data) {
				return false;
			}
			mhdata = options.mhdata || this.rightPanel.getObjectData('MorbusHepatitis',data.MorbusHepatitis_id);
			if(!mhdata) {
				return false;
			}
		}
		/*
		evndata = this.rightPanel.getObjectData(evnsysnick,mhdata.MorbusHepatitis_pid);
		if(!evndata) {
			return false;
		}
		*/
		if (options.action == 'add' && object_id == -1) {
			Ext.Ajax.request({
				url: '/?c=MorbusHepatitis&m=saveMorbusHepatitis',
				params: {
					From: 'personregister_viewform'
					,Person_id: mhdata.MorbusHepatitis_pid
					,MorbusHepatitis_id: mhdata.MorbusHepatitis_id
					,Morbus_id: mhdata.Morbus_id
				},
				callback: function(o, s, response) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if ( response_obj.success == true ) {
						var reload_params = {
							section_code: 'MorbusHepatitis',
							object_key: 'MorbusHepatitis_id',
							object_value: 0,
							parent_object_key: 'MorbusHepatitis_pid',
							parent_object_value: mhdata.MorbusHepatitis_pid,
							section_id: 'MorbusHepatitis_'+ mhdata.MorbusHepatitis_pid +'_-1',
							callback: function(){
							}.createDelegate(this)
						};
						this.rightPanel.reloadViewForm(reload_params);
						
						options.eldata.object_id = mhdata.MorbusHepatitis_pid +'_'+ response_obj.MorbusHepatitis_id;
						options.eldata.section_id = options.eldata.object +'_'+ mhdata.MorbusHepatitis_pid +'_'+ response_obj.MorbusHepatitis_id;
						mhdata.MorbusHepatitis_id = response_obj.MorbusHepatitis_id;
						options.mhdata = mhdata;
						this.openMorbusHepatitisSpecificForm(options);
					}
				}.createDelegate(this)
			});
			return true;
		}

		params.callback = function() {
			var reload_params = {
				section_code: options.object,
				object_key: options.object +'_id',
				object_value: object_id,
				parent_object_key: 'MorbusHepatitis_id',
				parent_object_value: mhdata.MorbusHepatitis_id,
				param_name: 'MorbusHepatitis_pid',
				param_value: mhdata.MorbusHepatitis_pid,
				section_id: options.object +'List_'+ mhdata.MorbusHepatitis_pid +'_'+ mhdata.MorbusHepatitis_id
			};
			this.rightPanel.reloadViewForm(reload_params);
		}.createDelegate(this);
		
		switch(options.object) {
			case 'MorbusHepatitisEvn':
				if(!data.EvnClass_sysNick || !data.EvnClass_sysNick.inlist(['EvnVizitPL','EvnSection']) || !data.Evn_id || !data.Evn_pid || options.action != 'openEvn') {
					return false;
				}
				if(data.EvnClass_sysNick == 'EvnVizitPL') {
					params.onHide = Ext.emptyFn;
					params.callback = Ext.emptyFn;
					params.formParams.Person_id = this.Person_id;
					params.formParams.PersonEvn_id = this.PersonEvn_id;
					params.formParams.Server_id = this.Server_id;
					params.formParams.EvnVizitPL_id = data.Evn_id;
					params.formParams.EvnPL_id = data.Evn_pid;
					this.openForm('swEmkEvnPLEditWindow','XXX_id',params,'viewEvnVizitPL',lang['prosmotr_posescheniya']);
					return true;
				}
				if(data.EvnClass_sysNick == 'EvnSection') {
					params.onHide = Ext.emptyFn;
					params.callback = Ext.emptyFn;
					params.formParams.Person_id = this.Person_id;
					params.formParams.PersonEvn_id = this.PersonEvn_id;
					params.formParams.Server_id = this.Server_id;
					params.formParams.EvnSection_id = data.Evn_id;
					params.formParams.EvnSection_pid = data.Evn_pid;
					this.openForm('swEvnSectionEditWindow','XXX_id',params,'view',lang['prosmotr_dvijeniya']);
					return true;
				}
				break;
			case 'MorbusHepatitisVaccination':
				win_name = 'swMorbusHepatitisVaccinationWindow';
				if(options.action == 'add') {
					params.MorbusHepatitisVaccination_id = null;
					params.formParams = {MorbusHepatitis_id: mhdata.MorbusHepatitis_id, EvnSection_id: null};
				}
				if(options.action == 'edit') {
					if(this.checkAccessEdit() == false) {
						return false;
					}
					params.MorbusHepatitisVaccination_id = object_id;
					params.formParams = {MorbusHepatitis_id: mhdata.MorbusHepatitis_id};
				}
				break;
			case 'MorbusHepatitisCure':
				win_name = 'swMorbusHepatitisCureWindow';
				if(options.action == 'add') {
					params.MorbusHepatitisCure_id = null;
					params.formParams = {MorbusHepatitis_id: mhdata.MorbusHepatitis_id, EvnSection_id: null};
				}
				if(options.action == 'edit') {
					if(this.checkAccessEdit() == false) {
						return false;
					}
					params.MorbusHepatitisCure_id = object_id;
					params.formParams = {MorbusHepatitis_id: mhdata.MorbusHepatitis_id};
				}
				if(options.action == 'openEffMonitoring') {
					getWnd('swMorbusHepatitisCureEffMonitoringList').show({MorbusHepatitisCure_id: data.MorbusHepatitisCure_id, accessType: this.checkAccessEdit(true)?'edit':'view'});
					return true;
				}
				break;
			case 'MorbusHepatitisDiag':
				win_name = 'swMorbusHepatitisDiagWindow';
				if(options.action == 'add') {
					params.MorbusHepatitisDiag_id = null;
					params.formParams = {MorbusHepatitis_id: mhdata.MorbusHepatitis_id, EvnSection_id: null};
					//params.formParams.MorbusHepatitisDiag_setDT = (evnsysnick == 'EvnVizitPL')?evndata.EvnVizitPL_setDate:evndata.EvnSection_setDate;
				}
				if(options.action == 'edit') {
					if(this.checkAccessEdit() == false) {
						return false;
					}
					params.MorbusHepatitisDiag_id = object_id;
					params.formParams = {MorbusHepatitis_id: mhdata.MorbusHepatitis_id};
				}
				break;
			case 'MorbusHepatitisQueue':
				win_name = 'swMorbusHepatitisQueueWindow';
				if(options.action == 'add') {
					params.MorbusHepatitisQueue_id = null;
					params.formParams = {MorbusHepatitis_id: mhdata.MorbusHepatitis_id};
				}
				if(options.action == 'edit') {
					if(this.checkAccessEdit() == false) {
						return false;
					}
					params.MorbusHepatitisQueue_id = object_id;
					params.formParams = {MorbusHepatitis_id: mhdata.MorbusHepatitis_id};
				}
				break;
			case 'MorbusHepatitisPlan':
				win_name = 'swMorbusHepatitisPlanWindow';
				if(options.action == 'add') {
					params.MorbusHepatitisPlan_id = null;
					params.formParams = {MorbusHepatitis_id: mhdata.MorbusHepatitis_id};
				}
				if(options.action == 'edit') {
					if(this.checkAccessEdit() == false) {
						return false;
					}
					params.MorbusHepatitisPlan_id = object_id;
					params.formParams = {MorbusHepatitis_id: mhdata.MorbusHepatitis_id};
				}
				break;
			case 'MorbusHepatitisFuncConfirm':
			case 'MorbusHepatitisLabConfirm':
				win_name = 'swEvnUslugaCommonEditWindow';
				params.Person_id = this.Person_id;
				/*
				params.Person_Firname = this.findById('PEMK_PersonInfoFrame').getFieldValue('Person_Firname');
				params.Person_Surname = this.findById('PEMK_PersonInfoFrame').getFieldValue('Person_Surname');
				params.Person_Secname = this.findById('PEMK_PersonInfoFrame').getFieldValue('Person_Secname');
				params.Person_Birthday = this.findById('PEMK_PersonInfoFrame').getFieldValue('Person_Birthday');
				params.parentClass = (evnsysnick == 'EvnVizitPL')?'EvnVizit':'EvnSection';
				params.parentEvnComboData = [{
					Evn_id: (evnsysnick == 'EvnVizitPL')?evndata.EvnVizitPL_id:evndata.EvnSection_id,
					LpuSection_id: evndata.LpuSection_id,
					MedPersonal_id: evndata.MedPersonal_id,
					Evn_Name: (evnsysnick == 'EvnVizitPL')?lang['poseschenie_patsientom_polikliniki']:lang['dvijenie'],
					Evn_setDate: Date.parseDate((evnsysnick == 'EvnVizitPL')?evndata.EvnVizitPL_setDate:evndata.EvnSection_setDate, 'd.m.Y')
				}];
				*/
				params.action = options.action;
				params.UslugaConfirm = (options.object == 'MorbusHepatitisFuncConfirm')?'FuncConfirm':'LabConfirm';
				params.formParams = {
					//Morbus_id: null
					EvnUslugaCommon_rid: null, //(evnsysnick == 'EvnVizitPL')?evndata.EvnVizitPL_id:evndata.EvnSection_id,
					//PersonEvn_id: this.PersonEvn_id,
					//Server_id: this.Server_id,
					Person_id: this.Person_id
				};
				
				if(options.action == 'add') {
					params.formParams.EvnUslugaCommon_id = 0;
				}
				if(options.action == 'edit' || options.action == 'openUsluga') {
					params.formParams.EvnUslugaCommon_id = data.EvnUsluga_id;
					params.action = this.checkAccessEdit()?'edit':'view';
					params.accessType = this.checkAccessEdit()?'edit':'view';
					if(data.EvnClass_SysNick == 'EvnUslugaPar') {
						win_name = 'swEvnUslugaParEditWindow';
						params.EvnUslugaPar_id = data.EvnUsluga_id;
						params.formParams.EvnUslugaPar_id = data.EvnUsluga_id;
						params.onSaveUsluga = params.callback;
					}
					if(options.action == 'openUsluga') {
						params.action = 'view';
						params.accessType = 'view';
					}
				}
				break;
			default:
				return false;
		}
		getWnd(win_name).show(params);
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
				mode = 'view';

			var params = {
				action: mode,
				Person_id: this.Person_id,
				/*
				PersonEvn_id: this.PersonEvn_id,
				Server_id: this.Server_id,
				Person_Firname: this.findById('PEMK_PersonInfoFrame').getFieldValue('Person_Firname'),
				Person_Surname: this.findById('PEMK_PersonInfoFrame').getFieldValue('Person_Surname'),
				Person_Secname: this.findById('PEMK_PersonInfoFrame').getFieldValue('Person_Secname'),
				Person_Birthday: this.findById('PEMK_PersonInfoFrame').getFieldValue('Person_Birthday'),
				onHide: function() { if(callback){callback();} else {this.reloadTree();} }.createDelegate(this),
				*/
				UserMedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
				UserLpuSection_id: this.userMedStaffFact.LpuSection_id,
				userMedStaffFact: this.userMedStaffFact,
				from: this.id,
				ARMType: this.userMedStaffFact.ARMType
			};
			params = Ext.apply(params || {}, oparams || {});
			/*
			if (IS_DEBUG)
			{
				console.debug('openForm | Форма %s с параметрами: %o', open_form, params);
			}
			*/
			if(open_form == 'swPersonCardHistoryWindow')
                params.action = (this.editType=='onlyRegister')?'view':'edit';
            if(open_form == 'swPersonEditWindow')
                params.readOnly = (this.editType=='onlyRegister')?true:false;
			getWnd(open_form).show(params);
		}
	},
	createMorbusHepatitisInputCmp: function(name, el_data) {
		if(this.allowSpecificEdit == false) {
			return false;
		}
		var object_id = el_data.object_id.split('_')[1];
		var obj, params, s;
		if(name == 'LabConfirmResult' || name == 'FuncConfirmResult') {
			s = (name == 'LabConfirmResult')?'Lab':'Func';
			obj = 'MorbusHepatitis'+s+'Confirm';
			params = this.rightPanel.getObjectData(obj,object_id);
			if(typeof params != 'object' || !params.MorbusHepatitis_id) {
				return false;
			}
			params = this.rightPanel.getObjectData('MorbusHepatitis',params.MorbusHepatitis_id);
			if(typeof params != 'object') {
				return false;
			}
			params['MorbusHepatitis'+s+'Confirm_id'] = object_id;
		} else { 
			obj = 'MorbusHepatitis';
			params = this.rightPanel.getObjectData(obj,object_id);
			if(typeof params != 'object') {
				return false;
			}
		}
		var form = this;
		var cmp, ct, elinputid, eloutputid, config;
		var empty_value = '<span style="color: #666;">Не указано</span>';
		var onChange = function(conf){
			if(conf.elOutput) {
				conf.elOutput.setDisplayed('inline');
				conf.elOutput.update(conf.outputValue);
				if(conf.type == 'id') conf.elOutput.setAttribute('dataid',conf.value);
				conf.elInputWrap.setDisplayed('none');
				conf.elInput.destroy();
				this.input_cmp_list[conf.elOutputId] = false;
				var requestParams = {
					Mode: 'personregister_viewform'
					,Morbus_id: params.Morbus_id
					,MorbusBase_id: params.MorbusBase_id
					,MorbusHepatitis_id: params.MorbusHepatitis_id
                    ,Person_id: params.Person_id
                    ,Diag_id: params.Diag_id
				};
				requestParams[conf.field_name] = conf.value;
				if(conf.child_obj) {
					requestParams[conf.child_obj] = params[conf.child_obj +'_id'];
				}
                this.requestSaveWithShowInfoMsg('/?c=MorbusHepatitis&m=set'+ conf.field_name,
                    requestParams,
                    function(result) {
                        if ( result.success ) {
                            this.isChange = true;
                        }
                        if (typeof conf.requestCallback == 'function') {
                            conf.requestCallback(result);
                        }
                    }, this);
			}
		}.createDelegate(this);
		
		var onCancel = function(conf){
			conf.elOutput.setDisplayed('inline');
			conf.elInputWrap.setDisplayed('none');
			conf.elInput.destroy();
			this.input_cmp_list[conf.elOutputId] = false;
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
						if(options.type == 'id') {
							//if(!f.getStore() || f.getStore().getCount()==0) log('not store: ' + options.field_name);
							var dataid = options.elOutput.getAttribute('dataid');
							if(!Ext.isEmpty(dataid)) {
								f.setValue(parseInt(dataid));
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
							options.outputValue = (n)?f.getStore().getById(n).get(f.displayField):empty_value;
							options.value = n || null;
						}
						options.elInput = f;
						onChange(options);
					}
				}
			};
		};
		
		eloutputid = obj+ '_'+ el_data.object_id +'_input'+name;
		elinputid = obj+ '_'+ el_data.object_id +'_inputarea'+name;
		eloutput = Ext.get(eloutputid);
		ct = Ext.get(elinputid);
		
		switch(name){
			case 'EpidAns':
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'id'
						,field_name: 'HepatitisEpidemicMedHistoryType_id'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: null
						,elInputWrap: ct
						,elInput: null
					});
					config.autoLoad = false;
					config.comboSubject = 'HepatitisEpidemicMedHistoryType';
					config.typeCode = 'int';
					config.width = 340;
					config.listWidth = 400;
					cmp = new sw.Promed.SwCommonSprCombo(config);
					var dataid = eloutput.getAttribute('dataid');
					cmp.getStore().load({
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
			case 'EpidNum':
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'int'
						,field_name: 'MorbusHepatitis_EpidNum'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: null
						,elInputWrap: ct
						,elInput: null
					});
					config.hideLabel = true;
					config.allowDecimals = false;
					config.allowNegative = false;
					config.maskRe =  new RegExp("^[0-9]*$");
					config.width = 60;
					cmp = new Ext.form.NumberField(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'LabConfirmResult':
			case 'FuncConfirmResult':
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'string'
						,field_name: 'MorbusHepatitis'+s+'Confirm_Result'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: null
						,elInputWrap: ct
						,elInput: null
						,child_obj: obj
					});
					config.hideLabel = true;
					config.width = 120;
					cmp = new Ext.form.TextField(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
		}
	},
	deleteEvent: function(event, data) {
		if(this.checkAccessEdit() == false) {
			return false;
		}
		if ( !event.inlist(['MorbusHepatitisPlan','MorbusHepatitisQueue','MorbusHepatitisVaccination','MorbusHepatitisCure','MorbusHepatitisFuncConfirm','MorbusHepatitisLabConfirm','MorbusHepatitisDiag']) )
		{
			return false;
		}

		if ( event.inlist(['MorbusHepatitisPlan','MorbusHepatitisQueue','MorbusHepatitisVaccination','MorbusHepatitisCure','MorbusHepatitisFuncConfirm','MorbusHepatitisLabConfirm','MorbusHepatitisDiag']) )
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
			case 'MorbusHepatitisPlan':
			case 'MorbusHepatitisQueue':
			case 'MorbusHepatitisVaccination':
			case 'MorbusHepatitisCure':
			case 'MorbusHepatitisFuncConfirm':
			case 'MorbusHepatitisLabConfirm':
			case 'MorbusHepatitisDiag':
				error = lang['pri_udalenii_voznikli_oshibki'];
				question = lang['udalit'];
				onSuccess = function(){
					var reload_params = {
						section_code: data.object,
						object_key: data.object +'_id',
						object_value: data.object_id,
						parent_object_key: 'MorbusHepatitis_id',
						parent_object_value: formParams.MorbusHepatitis_id,
						param_name: 'MorbusHepatitis_pid',
						param_value: formParams.MorbusHepatitis_pid || null,
						section_id: data.object +'List_'+ formParams.MorbusHepatitis_pid +'_'+ formParams.MorbusHepatitis_id
					};
					this.rightPanel.reloadViewForm(reload_params);
				}.createDelegate(this);
				url = '/?c=Utils&m=ObjectRecordDelete';
				params['object'] = data.object;
				params['obj_isEvn'] = 'false';
				params['id'] = data.object_id;
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
								onSuccess(callback_data);
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
		if (this.MorbusHepatitis_pid) {
			this.viewObject.attributes.object_id = 'MorbusHepatitis_pid';
			this.viewObject.attributes.object_value = this.MorbusHepatitis_pid;
		}
		if (this.PersonRegister_id) {
		this.rightPanel.loadNodeViewForm(this.viewObject,{
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
		sw.Promed.swMorbusHepatitisWindow.superclass.show.apply(this, arguments);
		
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
		this.MorbusHepatitis_pid = arguments[0].MorbusHepatitis_pid;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.isChange = false;
		this.allowSpecificEdit = arguments[0].allowSpecificEdit || false;
		
		if(arguments[0] && arguments[0].action == 'view')
			this.allowSpecificEdit = false;

		if (this.MorbusHepatitis_pid) {
			this.setTitle('Специфика');
		}

		this.viewObject = {
			id: 'PersonMorbusHepatitis_'+ this.Person_id,
			attributes: {
				accessType: (this.allowSpecificEdit == true)?'edit':'view',
				text: 'test',
				object: 'PersonMorbusHepatitis',
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
			PersonMorbusHepatitis: {
				print: {
					actionType: 'view',
					sectionCode: 'PersonMorbusHepatitis',
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
							//saveUrl: '/?c=PersonMediaData&m=uploadPersonPhoto',
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
									/*
									нужно обновить секцию person_data, но пока этого не сделать
									var reload_params = {
										section_code: d.object,
										view_section: 'subsection',
										object_key: 'Person_id',
										object_value: data.Person_id,
										parent_object_key: 'Person_id',
										parent_object_value: d.object_id,
										section_id: d.section_id
									};
									form.reloadViewForm(reload_params);
									*/
									// пока будем перезагружать всю сигн.информацию
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
						var data = win.rightPanel.getObjectData('PersonMorbusHepatitis',d.object_id);
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
									PersonCard_id: data.PersonCard_id,
	                                Person_id: data.Person_id
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
								// нужно обновить секцию person_data, пока будем перезагружать всю сигн.информацию
								win.loadNodeViewForm();
							}
						};
						win.openForm('swPersonCardHistoryWindow','XXX_id',params,'edit',lang['istoriya_prikrepleniya']);
					}
				/*},
				addAttach: {
					actionType: 'add',
					sectionCode: 'person_data',
					handler: function(e, c, d) {
						log(d);
						//форма для добавления прикрепления - какого прикрепления? Пусть юзер сам выберет в форме swPersonCardHistoryWindow
					}*/
				}
			},
			MorbusHepatitis: {
				inputEpidAns: {
					actionType: 'view',
					sectionCode: 'MorbusHepatitis',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusHepatitisInputCmp('EpidAns', d);
					}
				},
				inputEpidNum: {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitis',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusHepatitisInputCmp('EpidNum', d);
					}
				}
			},
			MorbusHepatitisEvn: {
				openEvn: {
					actionType: 'view',
					sectionCode: 'MorbusHepatitisEvn',
					handler: function(e, c, d) {
						win.openMorbusHepatitisSpecificForm({action: 'openEvn',object: 'MorbusHepatitisEvn', eldata: d, mhdata: {MorbusHepatitis_id: null}});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusHepatitisEvnList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusHepatitisEvnList',
					handler: function(e, c, d) {
						var id = 'MorbusHepatitisEvnTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusHepatitisSopDiag: {
				print: {
					actionType: 'view',
					sectionCode: 'MorbusHepatitisSopDiagList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusHepatitisSopDiagList',
					handler: function(e, c, d) {
						var id = 'MorbusHepatitisSopDiagTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusHepatitisQueue: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitisQueue',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusHepatitisSpecificForm({action: 'edit',object: 'MorbusHepatitisQueue', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitisQueue',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusHepatitisQueue',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitisQueueList',
					handler: function(e, c, d) {
						win.openMorbusHepatitisSpecificForm({action: 'add',object: 'MorbusHepatitisQueue', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusHepatitisQueueList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusHepatitisQueueList',
					handler: function(e, c, d) {
						var id = 'MorbusHepatitisQueueTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusHepatitisPlan: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitisPlan',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusHepatitisSpecificForm({action: 'edit',object: 'MorbusHepatitisPlan', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitisPlan',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusHepatitisPlan',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitisPlanList',
					handler: function(e, c, d) {
						win.openMorbusHepatitisSpecificForm({action: 'add',object: 'MorbusHepatitisPlan', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusHepatitisPlanList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusHepatitisPlanList',
					handler: function(e, c, d) {
						var id = 'MorbusHepatitisPlanTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusHepatitisVaccination: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitisVaccination',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusHepatitisSpecificForm({action: 'edit',object: 'MorbusHepatitisVaccination', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitisVaccination',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusHepatitisVaccination',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitisVaccinationList',
					handler: function(e, c, d) {
						win.openMorbusHepatitisSpecificForm({action: 'add',object: 'MorbusHepatitisVaccination', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusHepatitisVaccinationList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusHepatitisVaccinationList',
					handler: function(e, c, d) {
						var id = 'MorbusHepatitisVaccinationTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusHepatitisCure: {
				openEffMonitoring: {
					actionType: 'view',
					sectionCode: 'MorbusHepatitisCure',
					handler: function(e, c, d) {
						win.openMorbusHepatitisSpecificForm({action: 'openEffMonitoring',object: 'MorbusHepatitisCure', eldata: d});
					}
				},
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitisCure',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusHepatitisSpecificForm({action: 'edit',object: 'MorbusHepatitisCure', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitisCure',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusHepatitisCure',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitisCureList',
					handler: function(e, c, d) {
						win.openMorbusHepatitisSpecificForm({action: 'add',object: 'MorbusHepatitisCure', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusHepatitisCureList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusHepatitisCureList',
					handler: function(e, c, d) {
						var id = 'MorbusHepatitisCureTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusHepatitisFuncConfirm: {
				openUsluga: {
					actionType: 'view',
					sectionCode: 'MorbusHepatitisFuncConfirm',
					handler: function(e, c, d) {
						win.openMorbusHepatitisSpecificForm({action: 'openUsluga',object: 'MorbusHepatitisFuncConfirm', eldata: d});
					}
				},
				inputFuncConfirmResult: {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitisFuncConfirm',
					handler: function(e, c, d) {
						win.createMorbusHepatitisInputCmp('FuncConfirmResult', d);
					}
				},
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitisFuncConfirm',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusHepatitisSpecificForm({action: 'edit',object: 'MorbusHepatitisFuncConfirm', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitisFuncConfirm',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusHepatitisFuncConfirm',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitisFuncConfirmList',
					handler: function(e, c, d) {
						win.openMorbusHepatitisSpecificForm({action: 'add',object: 'MorbusHepatitisFuncConfirm', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusHepatitisFuncConfirmList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusHepatitisFuncConfirmList',
					handler: function(e, c, d) {
						var id = 'MorbusHepatitisFuncConfirmTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusHepatitisLabConfirm: {
				openUsluga: {
					actionType: 'view',
					sectionCode: 'MorbusHepatitisLabConfirm',
					handler: function(e, c, d) {
						win.openMorbusHepatitisSpecificForm({action: 'openUsluga',object: 'MorbusHepatitisLabConfirm', eldata: d});
					}
				},
				inputLabConfirmResult: {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitisLabConfirm',
					handler: function(e, c, d) {
						win.createMorbusHepatitisInputCmp('LabConfirmResult', d);
					}
				},
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitisLabConfirm',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusHepatitisSpecificForm({action: 'edit',object: 'MorbusHepatitisLabConfirm', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitisLabConfirm',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusHepatitisLabConfirm',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitisLabConfirmList',
					handler: function(e, c, d) {
						win.openMorbusHepatitisSpecificForm({action: 'add',object: 'MorbusHepatitisLabConfirm', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusHepatitisLabConfirmList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusHepatitisLabConfirmList',
					handler: function(e, c, d) {
						var id = 'MorbusHepatitisLabConfirmTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusHepatitisDiag: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitisDiag',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusHepatitisSpecificForm({action: 'edit',object: 'MorbusHepatitisDiag', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitisDiag',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusHepatitisDiag',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusHepatitisDiagList',
					handler: function(e, c, d) {
						win.openMorbusHepatitisSpecificForm({action: 'add',object: 'MorbusHepatitisDiag', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusHepatitisDiagList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusHepatitisDiagList',
					handler: function(e, c, d) {
						var id = 'MorbusHepatitisDiagTable_'+ d.object_id;
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
		sw.Promed.swMorbusHepatitisWindow.superclass.initComponent.apply(this, arguments);
	}
});