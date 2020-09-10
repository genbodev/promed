/**
* swMorbusVenerWindow - Форма просмотра записи регистра с типом «Венерические заболевания»
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

sw.Promed.swMorbusVenerWindow = Ext.extend(sw.Promed.BaseForm, 
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
				if (String(getGlobalOptions().groups).indexOf('Vener', 0) < 0)
				{
					sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «Регистр по венерическим заболеваниям»');
					return false;
				}
			}
		}
	},
	createMorbusVenerHtmlForm: function(name, el_data) {
		if(this.allowSpecificEdit == false) {
			return false;
		}
		var morbus_id = el_data.object_id.split('_')[1];
		var params = this.rightPanel.getObjectData('MorbusVener',morbus_id);
		if(typeof params != 'object') {
			return false;
		}
		var form = this;
		var cmp, ct, elinputid, eloutputid, config;
		var empty_value = '<span style="color: #666;">Не указано</span>';
		var onChange = function(conf){

			var save_tb = Ext.get('MorbusVener_'+el_data.object_id+'_toolbarMorbusVener');
			save_tb.setDisplayed('block');

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
						f.setValue(params[options.field_name]);
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
						options.elInput = f;
						if (n!=o)
							onChange(options);
					}
				}
			};
		};

		eloutputid = 'MorbusVener_'+ el_data.object_id +'_input'+name;
		elinputid = 'MorbusVener_'+ el_data.object_id +'_inputarea'+name;
		eloutput = Ext.get(eloutputid);
		ct = Ext.get(elinputid);

		switch(name){
			// даты
			case 'MorbusVener_DiagDT':
			case 'MorbusVener_updDiagDT':
			case 'MorbusVener_HospDT':
			case 'MorbusVener_BegTretDT':
			case 'MorbusVener_EndTretDT':
			case 'MorbusVener_DeRegDT':
			case 'MorbusVener_MensLastDT':
			
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
			case 'MorbusVener_MensBeg':
			case 'MorbusVener_MensEnd':
			case 'MorbusVener_MensOver':
			case 'MorbusVener_SexualInit':
			case 'MorbusVener_CountPregnancy':
			case 'MorbusVener_CountBirth':
			case 'MorbusVener_CountAbort':
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
					//config.maxValue = 99;
					config.maxLength = '2';
					config.autoCreate = {tag: "input", size:14, maxLength: config.maxLength, autocomplete: "off"};
					cmp = new Ext.form.NumberField(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'MorbusVener_LiveCondit':
			case 'MorbusVener_WorkCondit':
			case 'MorbusVener_Heredity':
			case 'MorbusVener_UseAlcoNarc':
			case 'MorbusVener_PlaceInfect':
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
						,outputValue: null
						,elInputWrap: ct
						,elInput: null
					});
					config.hideLabel = true;
					config.width = 360;
					cmp = new Ext.form.TextField(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'MorbusVener_IsVizitProf':
			case 'MorbusVener_IsPrevent':
			case 'MorbusVener_IsAlco':
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
			case 'VenerDetectType':
			case 'VenerDeRegCauseType':
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
					//config.listWidth = 500;
					config.comboSubject = name;
					config.typeCode = 'int';
					config.autoLoad = true;
					cmp = new sw.Promed.SwCommonSprCombo(config);
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'Lpu_bid': //
			case 'Lpu_eid': //
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
					,outputValue: null
					,elInputWrap: ct
					,elInput: null
				});
				config.width = 200;
				config.listWidth = 300;
				cmp = new sw.Promed.SwLpuLocalCombo(config);
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
		}
	},
	openMorbusVenerSpecificForm: function(options) {
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
			evndata,
			evnsysnick = null, //(this.data.Code.inlist(['EvnPL','EvnVizitPL']))?'EvnVizitPL':'EvnSection',
			params = {formParams: {}};

		/*
		 log('openMorbusVenerSpecificForm');
		 log(options);
		 */

		if(options.action == 'add') {
			object_id = (options.eldata.object_id.split('_').length > 1)?options.eldata.object_id.split('_')[1]:options.eldata.object_id;
			mhdata = options.mhdata || this.rightPanel.getObjectData('MorbusVener',object_id);
			if(!mhdata) {
				return false;
			}
		} else {
			object_id = (options.eldata.object_id.split('_').length > 1)?options.eldata.object_id.split('_')[1]:options.eldata.object_id;
			data = this.rightPanel.getObjectData(options.object,object_id);
			if(!data) {
				return false;
			}
			mhdata = options.mhdata || this.rightPanel.getObjectData('MorbusVener',data.MorbusVener_id);

			if(!mhdata) {
				return false;
			}
		}
		params.callback = function() {
			var reload_params = {
				section_code: options.object,
				object_key: options.object +'_id',
				object_value: object_id,
				parent_object_key: 'MorbusVener_id',
				parent_object_value: mhdata.MorbusVener_id,
				param_name: 'MorbusVener_pid',
				param_value: mhdata.MorbusVener_pid,
				section_id: options.object +'List_'+ mhdata.MorbusVener_pid +'_'+ mhdata.MorbusVener_id
			};
			this.rightPanel.reloadViewForm(reload_params);
		}.createDelegate(this);


		switch(options.object) {
			case 'MorbusVenerContact':
			case 'MorbusVenerTreatSyph':
			case 'MorbusVenerAccurTreat':
			case 'MorbusVenerEndTreat':
				win_name = 'sw'+options.object+'Window';
				params.action = options.action;
				params[options.object+'_id'] = (params.action=='edit')?object_id:null;
				params.formParams = {MorbusVener_id: mhdata.MorbusVener_id, MorbusVenerBase_id: mhdata.MorbusVenerBase_id, MorbusVenerPerson_id: mhdata.MorbusVenerPerson_id, Person_id: this.Person_id, Evn_id: null};
				break;
			default:
				return false;
		}
		getWnd(win_name).show(params);
	},
	/**
	 * Сохраняет данные по специфике
	 * @param btn_name
	 * @param el_data
	 * @return {Boolean}
	 */
	submitMorbusVenerHtmlForm: function(btn_name, el_data) {
		if(this.allowSpecificEdit == false) {
			return false;
		}

		var save_tb = Ext.get('MorbusVener_'+el_data.object_id+'_toolbarMorbusVener');

		var params = this.rightPanel.getObjectData('MorbusVener',el_data.object_id.split('_')[1]);
		if(!params) {
			return false;
		}
		for(var field_name in this.changed_fields) {
			params[field_name] = this.changed_fields[field_name].value || '';
		}
		params['Evn_pid'] = this.EvnDiagPLStom_id || this.EvnVizitPL_id || this.EvnSection_id || null;
		if (this.EvnVizitPL_id) {
			params['Mode'] = 'evnvizitpl_viewform';
		} else {
			params['Mode'] = 'personregister_viewform';
		}
		var url = '/?c=MorbusVener&m=saveMorbusVener';
		var form = this;
		form.loadMask = form.getLoadMask(LOAD_WAIT);
		form.loadMask.show();
		Ext.Ajax.request({
			url: url,
			params: params,
			callback: function(options, success, response) {
				form.loadMask.hide();
				var result = Ext.util.JSON.decode(response.responseText);
				if (result.success)
				{
					save_tb.setDisplayed('none');
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
			}
		});
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
		if ( !event.inlist(['MorbusVenerContact','MorbusVenerTreatSyph','MorbusVenerAccurTreat','MorbusVenerEndTreat']) )
		{
			return false;
		}

		if ( event.inlist(['MorbusVenerContact','MorbusVenerTreatSyph','MorbusVenerAccurTreat','MorbusVenerEndTreat']) )
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
			case 'MorbusVenerContact':
			case 'MorbusVenerTreatSyph':
			case 'MorbusVenerAccurTreat':
			case 'MorbusVenerEndTreat':
				error = lang['pri_udalenii_voznikli_oshibki'];
				question = lang['udalit'];
				onSuccess = function(){
					var reload_params = {
						section_code: data.object,
						object_key: data.object +'_id',
						object_value: data.object_id,
						parent_object_key: 'MorbusVener_id',
						parent_object_value: formParams.MorbusVener_id,
						accessType: (this.allowSpecificEdit == true)?1:0,
						param_name: 'MorbusVener_pid',
						param_value: formParams.MorbusVener_pid || null,
						section_id: data.object +'List_'+ formParams.MorbusVener_pid +'_'+ formParams.MorbusVener_id
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
		if (this.MorbusVener_pid) {
			this.viewObject.attributes.object_id = 'MorbusVener_pid';
			this.viewObject.attributes.object_value = this.MorbusVener_pid;
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
		sw.Promed.swMorbusVenerWindow.superclass.show.apply(this, arguments);
		
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
		this.MorbusVener_pid = arguments[0].MorbusVener_pid;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.isChange = false;
		this.allowSpecificEdit = arguments[0].allowSpecificEdit || false;
		
		if(arguments[0] && arguments[0].action == 'view')
			this.allowSpecificEdit = false;

		if (this.MorbusVener_pid) {
			this.setTitle('Специфика');
		}

		this.viewObject = {
			id: 'PersonMorbusVener_'+ this.Person_id,
			attributes: {
				accessType: (this.allowSpecificEdit == true)?'edit':'view',
				text: 'test',
				object: 'PersonMorbusVener',
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
		var win = this;
		this.rightPanel.configActions = {
			PersonMorbusVener: {
				print: {
					actionType: 'view',
					sectionCode: 'PersonMorbusVener',
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
						var data = win.rightPanel.getObjectData('PersonMorbusVener',d.object_id);
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
								// нужно обновить секцию person_data, пока будем перезагружать все
								win.loadNodeViewForm();
							}
						};
						win.openForm('swPersonCardHistoryWindow','XXX_id',params,'edit',lang['istoriya_prikrepleniya']);
					}
				}
			},
			MorbusVener: {
				inputMorbusVener_DiagDT: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusVenerHtmlForm('MorbusVener_DiagDT', d);
					}
				},
				inputVenerDetectType: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusVenerHtmlForm('VenerDetectType', d);
					}
				},
				inputMorbusVener_IsVizitProf: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusVenerHtmlForm('MorbusVener_IsVizitProf', d);
					}
				},
				inputMorbusVener_IsPrevent: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusVenerHtmlForm('MorbusVener_IsPrevent', d);
					}
				},
				inputMorbusVener_IsAlco: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusVenerHtmlForm('MorbusVener_IsAlco', d);
					}
				},
				inputMorbusVener_updDiagDT: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {

						win.createMorbusVenerHtmlForm('MorbusVener_updDiagDT', d);
					}
				},
				inputMorbusVener_HospDT: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {

						win.createMorbusVenerHtmlForm('MorbusVener_HospDT', d);
					}
				},
				inputMorbusVener_BegTretDT: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {

						win.createMorbusVenerHtmlForm('MorbusVener_BegTretDT', d);
					}
				},
				inputLpu_bid: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {

						win.createMorbusVenerHtmlForm('Lpu_bid', d);
					}
				},
				inputMorbusVener_EndTretDT: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusVenerHtmlForm('MorbusVener_EndTretDT', d);
					}
				},
				inputLpu_eid: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusVenerHtmlForm('Lpu_eid', d);
					}
				},
				inputMorbusVener_DeRegDT: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusVenerHtmlForm('MorbusVener_DeRegDT', d);
					}
				},
				inputVenerDeRegCauseType: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusVenerHtmlForm('VenerDeRegCauseType', d);
					}
				},
				inputMorbusVener_MensLastDT: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusVenerHtmlForm('MorbusVener_MensLastDT', d);
					}
				},
				inputMorbusVener_LiveCondit: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusVenerHtmlForm('MorbusVener_LiveCondit', d);
					}
				},
				inputMorbusVener_WorkCondit: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusVenerHtmlForm('MorbusVener_WorkCondit', d);
					}
				},
				inputMorbusVener_Heredity: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusVenerHtmlForm('MorbusVener_Heredity', d);
					}
				},
				inputMorbusVener_UseAlcoNarc: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusVenerHtmlForm('MorbusVener_UseAlcoNarc', d);
					}
				},
				inputMorbusVener_PlaceInfect: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusVenerHtmlForm('MorbusVener_PlaceInfect', d);
					}
				},
				inputMorbusVener_MensBeg: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusVenerHtmlForm('MorbusVener_MensBeg', d);
					}
				},
				inputMorbusVener_MensEnd: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusVenerHtmlForm('MorbusVener_MensEnd', d);
					}
				},
				inputMorbusVener_MensOver: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusVenerHtmlForm('MorbusVener_MensOver', d);
					}
				},
				inputMorbusVener_SexualInit: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusVenerHtmlForm('MorbusVener_SexualInit', d);
					}
				},
				inputMorbusVener_CountPregnancy: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusVenerHtmlForm('MorbusVener_CountPregnancy', d);
					}
				},
				inputMorbusVener_CountBirth: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusVenerHtmlForm('MorbusVener_CountBirth', d);
					}
				},
				inputMorbusVener_CountAbort: {
					actionType: 'edit',
					sectionCode: 'MorbusVener',
					dblClick: false,
					handler: function(e, c, d) {
						win.createMorbusVenerHtmlForm('MorbusVener_CountAbort', d);
					}
				},
				saveMorbusVener: { actionType: 'edit', sectionCode: 'MorbusVener', handler: function(e, c, d) { win.submitMorbusVenerHtmlForm('saveMorbusVener',d); } }
			},
			MorbusVenerContact: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusVenerContact',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusVenerSpecificForm({action: 'edit',object: 'MorbusVenerContact', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusVenerContact',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusVenerContact',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusVenerContactList',
					handler: function(e, c, d) {
						win.openMorbusVenerSpecificForm({action: 'add',object: 'MorbusVenerContact', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusVenerContactList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusVenerContactList',
					handler: function(e, c, d) {
						var id = 'MorbusVenerContactTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusVenerTreatSyph: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusVenerTreatSyph',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusVenerSpecificForm({action: 'edit',object: 'MorbusVenerTreatSyph', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusVenerTreatSyph',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusVenerTreatSyph',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusVenerTreatSyphList',
					handler: function(e, c, d) {
						win.openMorbusVenerSpecificForm({action: 'add',object: 'MorbusVenerTreatSyph', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusVenerTreatSyphList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusVenerTreatSyphList',
					handler: function(e, c, d) {
						var id = 'MorbusVenerTreatSyphTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusVenerAccurTreat: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusVenerAccurTreat',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusVenerSpecificForm({action: 'edit',object: 'MorbusVenerAccurTreat', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusVenerAccurTreat',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusVenerAccurTreat',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusVenerAccurTreatList',
					handler: function(e, c, d) {
						win.openMorbusVenerSpecificForm({action: 'add',object: 'MorbusVenerAccurTreat', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusVenerAccurTreatList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusVenerAccurTreatList',
					handler: function(e, c, d) {
						var id = 'MorbusVenerAccurTreatTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
			MorbusVenerEndTreat: {
				edit: {
					actionType: 'edit',
					sectionCode: 'MorbusVenerEndTreat',
					dblClick: true,
					handler: function(e, c, d) {
						win.openMorbusVenerSpecificForm({action: 'edit',object: 'MorbusVenerEndTreat', eldata: d});
					}
				},
				'delete': {
					actionType: 'edit',
					sectionCode: 'MorbusVenerEndTreat',
					handler: function(e, c, d) {
						win.deleteEvent('MorbusVenerEndTreat',d);
					}
				},
				add: {
					actionType: 'edit',
					sectionCode: 'MorbusVenerEndTreatList',
					handler: function(e, c, d) {
						win.openMorbusVenerSpecificForm({action: 'add',object: 'MorbusVenerEndTreat', eldata: d});
					}
				},
				print: {
					actionType: 'view',
					sectionCode: 'MorbusVenerEndTreatList',
					handler: function(e, c, d) {
						win.rightPanel.printHtml(d.section_id);
					}
				},
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'MorbusVenerEndTreatList',
					handler: function(e, c, d) {
						var id = 'MorbusVenerEndTreatTable_'+ d.object_id;
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
			/*DrugVener: {
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'DrugVenerList',
					handler: function(e, c, d) {
						var id = 'DrugVenerTable_'+ d.object_id;
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
		sw.Promed.swMorbusVenerWindow.superclass.initComponent.apply(this, arguments);
	}
});
