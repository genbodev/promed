/**
* swMorbusOrphanWindow - Форма просмотра записи регистра с типом «Орфанное»
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

sw.Promed.swMorbusOrphanWindow = Ext.extend(sw.Promed.BaseForm, 
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
			if (String(getGlobalOptions().groups).indexOf('Orphan', 0) < 0)
			{
				sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «Регистр по орфанным заболеваниям»');
				return false;
			}
		}
	},
	createInputArea: function(name, el_data) {
		if(this.allowSpecificEdit == false) {
			return false;
		}
		var object_id = el_data.object_id.split('_')[1];
		var params = this.rightPanel.getObjectData('MorbusOrphan',object_id);
		if(typeof params != 'object') {
			return false;
		}
		var form = this;
		var cmp, ct, elinputid, eloutputid, config;
		var empty_value = '<span style="color: #666;">Не указано</span>';
		var onChange = function(conf){
			if(conf.elOutput) {
				if(conf.field_name == 'Diag_id' && !conf.disableMsg) {
					sw.swMsg.show(
					{
						buttons: Ext.Msg.YESNO,
						fn: function( buttonId ) 
						{
							if ( buttonId == 'yes' ) 
							{
								conf.disableMsg = true;
								onChange(conf);
							}
							else
							{
								conf.elOutput.setDisplayed('inline');
								conf.elInputWrap.setDisplayed('none');
								conf.elInput.destroy();
								form.input_cmp_list[conf.elOutputId] = false;
							}
						},
						msg: lang['izmenen_diagnoz_zapisi_registra_po_orfannyim_zabolevaniyam_izmenit_diagnoz_i_sozdat_napravlenie_na_vnesenie_izmeneniy_v_svedeniya_soderjaschiesya_regionalnom_segmente_federalnogo_registra_lits_stradayuschih_jizneugrojayuschimi_i_hronicheskimi_progressiruyuschimi_redkimi_orfannyimi_zabolevaniyami_privodyaschimi_k_sokrascheniyu_prodoljitelnosti_jizni_grajdan_ili_ih_invalidnosti'],
						title: lang['vopros']
					});
					return false;
				}
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
					,MorbusOrphan_id: params.MorbusOrphan_id
					,Diag_id: params.Diag_id
					,Person_id: params.Person_id
				};
				requestParams[conf.field_name] = conf.value;
				Ext.Ajax.request({
					url: '/?c=MorbusOrphan&m=set'+ conf.field_name,
					params: requestParams,
					callback: function(options, success, response) {
						this.isChange = true;
						if(typeof conf.requestCallback == 'function')
							conf.requestCallback(options, success, response);
					}.createDelegate(this)
				});
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
		
		eloutputid = 'MorbusOrphan_'+ el_data.object_id +'_input'+name;
		elinputid = 'MorbusOrphan_'+ el_data.object_id +'_inputarea'+name;
		eloutput = Ext.get(eloutputid);
		ct = Ext.get(elinputid);
		
		switch(name){
			case 'Diag':
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'id'
						,field_name: 'Diag_id'
						,elOutputId: eloutputid
						,elInputId: elinputid
						,elOutput: eloutput
						,outputValue: null
						,codeField: 'Diag_Code'
						,elInputWrap: ct
						,elInput: null
					});
					config.width = 350;
					config.listWidth = 500;
					config.hiddenName = 'Diag_id';
					config.MorbusType_SysNick = 'orphan';
					cmp = new sw.Promed.SwDiagCombo(config);
					var dataid = eloutput.getAttribute('dataid');
					if(dataid && dataid > 0) {
						cmp.getStore().load({
							params: {where: 'where Diag_id = '+ dataid, clause: {where: 'record["Diag_id"] == "'+ dataid +'"' }},
							callback: function(){
								if(this.getStore().getCount() > 0) {
									this.setValue(dataid);
								}
							},
							scope: cmp
						});
					}
					cmp.focus(true, 500);
					this.input_cmp_list[eloutputid] = cmp;
				}
				break;
			case 'LpuO':
				if(ct && !this.input_cmp_list[eloutputid]) {
					ct.setDisplayed('block');
					eloutput.setDisplayed('none');
					config = getBaseConfig({
						name: name
						,type: 'id'
						,field_name: 'Lpu_oid'
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
			getWnd(open_form).show(params);
		}
	},
	loadNodeViewForm: function() 
	{
		this.rightPanel.loadNodeViewForm(this.viewObject,{
			param_name: 'PersonRegister_id',
			param_value: this.PersonRegister_id
		});
	},
	
	show: function() 
	{
		sw.Promed.swMorbusOrphanWindow.superclass.show.apply(this, arguments);
		
		//log(arguments[0]);
		if ( !arguments[0] || !arguments[0].Person_id || !arguments[0].PersonRegister_id ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		
		this.Person_id = arguments[0].Person_id;
		this.PersonRegister_id = arguments[0].PersonRegister_id;
		this.userMedStaffFact = arguments[0].userMedStaffFact || sw.Promed.MedStaffFactByUser.last;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.isChange = false;
		this.allowSpecificEdit = arguments[0].allowSpecificEdit || false;
		this.viewObject = {
			id: 'PersonMorbusOrphan_'+ this.Person_id,
			attributes: {
				accessType: (this.allowSpecificEdit == true)?'edit':'view',
				text: 'test',
				object: 'PersonMorbusOrphan',
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
			PersonMorbusOrphan: {
				print: {
					actionType: 'view',
					sectionCode: 'PersonMorbusOrphan',
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
						var data = win.rightPanel.getObjectData('PersonMorbusOrphan',d.object_id);
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
			MorbusOrphan: {
				inputDiag: {
					actionType: 'edit',
					sectionCode: 'MorbusOrphan',
					dblClick: false,
					handler: function(e, c, d) {
						win.createInputArea('Diag', d);
					}
				},
				inputLpuO: {
					actionType: 'edit',
					sectionCode: 'MorbusOrphan',
					dblClick: false,
					handler: function(e, c, d) {
						win.createInputArea('LpuO', d);
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
			DrugOrphan: {
				toggleDisplay: {
					actionType: 'view',
					sectionCode: 'DrugOrphanList',
					handler: function(e, c, d) {
						var id = 'DrugOrphanTable_'+ d.object_id;
						win.rightPanel.toggleDisplay(id,Ext.get(id).isDisplayed());
					}
				}
			},
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
		sw.Promed.swMorbusOrphanWindow.superclass.initComponent.apply(this, arguments);
	}
});