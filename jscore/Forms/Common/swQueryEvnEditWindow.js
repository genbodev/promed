/**
* swQueryEvnEditWindow - Запрос данных
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       Common
* @copyright    Copyright (c) 2018 Swan Ltd.
* @comment      
*/
sw.Promed.swQueryEvnEditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Запрос данных',
	id: 'swQueryEvnEditWindow',
	modal: true,
	shim: false,
	width: 700,
	height: 250,
	layout: 'border',
	resizable: false,
	maximizable: false,
	maximized: false,
	
	doSave:  function(options) {
		var win = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.findById('QueryEvnEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
        this.submit(options);
		return true;		
	},
	
	openEMK: function() {
		var win = this,
			evn_id = this.form.findField('Evn_id').getValue();
			
		if (!evn_id) return false;
		
		var params = {
			Person_id: this.Person_id,
			Server_id: this.Server_id,
			PersonEvn_id: this.PersonEvn_id,
			userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
			MedStaffFact_id: sw.Promed.MedStaffFactByUser.last.MedStaffFact_id,
			LpuSection_id: sw.Promed.MedStaffFactByUser.last.LpuSection_id,
			readOnly: false,
			fromQueryEvn: true,
			ARMType: 'common',
			owner: this,
			callback: function(data){
				if (data && data.loadedFiles && data.loadedFiles.length) {
					this.findById(win.id + 'AnswerPanel').findBy(function(ans) {
						if (ans.QueryEvnMessage_id && (ans.pmUser_id == getGlobalOptions().pmuser_id || ans.MedPersonal_id == getGlobalOptions().medpersonal_id)) {
							Ext.each(data.loadedFiles, function(el, idx) {
								el.id = -el.EvnMediaData_id;
								el.name = el.orig_name;
								el.url = 'uploads/evnmedia/' + el.file_name;
								win.addFileToFilesPanel(el, win.findById(win.id + 'filesPanel'+ans.QueryEvnMessage_id));
							});
						}
					});
				}
			}.createDelegate(this)
		};

		params.searchNodeObj = {
			parentNodeId: 'root',
			last_child: false,
			disableLoadViewForm: false,
			EvnClass_SysNick: this.form.findField('Evn_id').getFieldValue('EvnClass_SysNick'),
			Evn_id: evn_id
		};
		
		getWnd('swPersonEmkWindow').show(params);
	},
	
	submit: function(options) {
		var win = this;
		var params = {};
		var options = options || {};
		
		
		if (this.form.findField('Evn_id').disabled) params.Evn_id = this.form.findField('Evn_id').getValue();
		if (this.form.findField('QueryEvnType_id').disabled) params.QueryEvnType_id = this.form.findField('QueryEvnType_id').getValue();
		
		var answers = [];			
				
		this.findById(win.id + 'AnswerPanel').findBy(function(ans) {
			if (ans.QueryEvnMessage_id) {
				var files = [];
				ans.items.items[1].findBy(function(file) {
					files.push(file.settings.id);
				});
				answers.push({
					QueryEvnMessage_id: ans.QueryEvnMessage_id,
					pmUser_id: ans.pmUser_id,
					message: ans.items.items[0].getValue(),
					files: files
				});
			}
		}, this.findById(win.id + 'filesPanel'));
		
		if (options.doSend) {
			params.QueryEvnStatus_id = Math.min(2, Number(this.form.findField('QueryEvnStatus_id').getValue()) + 1); // пока упрощенный вариант, поскольку статусы идут по порядку
			this.form.findField('QueryEvnStatus_id').setValue(params.QueryEvnStatus_id);
		}
		
		params.QueryEvnMessageAnswers = Ext.util.JSON.encode(answers);
		params.scenario = this.scenario;

		win.getLoadMask('Подождите, идет сохранение...').show();
		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				win.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				win.getLoadMask().hide();
				if (action.result && action.result.QueryEvn_id > 0) {
					var id = action.result.QueryEvn_id;
					win.form.findField('QueryEvn_id').setValue(id);
					win.callback(win.owner, id);

                    var data = win.form.getValues();
                    win.onSave(data);
					win.hide();
				}
			}
		});
	},
	
	// ---------- блок работы с файлами -------------
	
	addFileEmk: function(QueryEvnMessage_id) {
		
		var win = this;
		var evn_id = this.form.findField('Evn_id').getValue();
		if (!evn_id) return false;
		
		getWnd('swQueryEvnFileSelectWindow').show({
			Evn_id: evn_id,
			onSelect: function(data) {
				Ext.each(data.EvnXmlList, function(el) {
					el.id = el[0];
					el.name = el[1];
					el.url = Ext.isEmpty(el[2]) ? null : 'uploads/evnmedia/' + el[2];
					win.addFileToFilesPanel(el, win.findById(win.id + 'filesPanel'+QueryEvnMessage_id));
				});
			}
		});
	},
	
	addFileComp: function(QueryEvnMessage_id) {
		var input = this.findById('uploadFilesInput');
		this.findById('QueryEvnMessage_id_active').setValue(QueryEvnMessage_id);
		input.getEl().dom.click();
	},

	setFilePanelHeight: function(panel) {
		var file_count = this.getCountFiles(panel);
		file_count = Math.min(file_count,5);
		panel.setHeight(file_count * 18 + 22);
	},

	getCountFiles: function(panel) {
		return panel.items.length;
	},

	uploadFiles: function() {
		var win = this;
		var QueryEvnMessage_id = this.findById('QueryEvnMessage_id_active').getValue();
		
		sw.swMsg.show({
			msg: langs('Выбранные файлы будут добавлены к случаю лечения пациента. Продолжить?'),
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId) {
				if (buttonId == 'yes') {
					win.doUploadFiles(true, QueryEvnMessage_id);
				} else {
					win.doUploadFiles(false, QueryEvnMessage_id);
				}
			}
		});
	},

	doUploadFiles: function(addEvn, QueryEvnMessage_id) {
		var win = this;
		win.getLoadMask('Загрузка файлов...').show();
		var params = {
			Evn_id: null
		};
		if (addEvn) params.Evn_id = this.form.findField('Evn_id').getValue();
		this.uploadForm.submit({
			params: params,
			timeout: 0,
			success: function (form, action) {
				win.getLoadMask().hide();
				if (action.result && action.result.data) {
					data = Ext.util.JSON.decode(action.result.data);
					Ext.each(data, function(el) {
						el.url = el.EvnMediaData_FilePath;
						el.name = el.EvnMediaData_FileName;
						el.id = -el.EvnMediaData_id;
						win.addFileToFilesPanel(el, win.findById(win.id + 'filesPanel' + QueryEvnMessage_id));
					});
				}
			},
			failure: function (form, action){
				win.getLoadMask().hide();
				Ext.Msg.alert('При загрузке файлов произошла ошибка');
			}
		});
	},

	addFileToFilesPanel: function(file, panel) {
		if (file && file.name) {
			var html = '<div style="float:left; height:18px;">';
			if(!Ext.isEmpty(file.url)) {
				html += '<a target="_blank" style="color: black; font-weight: bold;" href="'+file.url+'">'+file.name+'</a>';
			} else {
				html += '<a href="#" style="color: black; font-weight: bold;" onClick="getWnd(\'swEvnXmlViewWindow\').show({ EvnXml_id: ' + file.id + ' });">'+file.name+'</a>';
			}
			if(this.scenario == 3) {
				html = html + ' <a href="#" onClick="Ext.getCmp(\''+this.id+'\').deleteFileToFilesPanel(\''+file.id+'\', \''+panel.id+'\');">'+
					'<img title="Удалить" style="height: 12px; width: 12px; vertical-align: bottom;" src="/img/icons/delete16.png" /></a>';
			}
			html = html + '</div>';

			if(file.url && !file.tmp_name){
				//при загрузке формы существующие файлы имеют url (tmp_name при загрузке новых файлов)
				file.tmp_name = file.url;
			}
			panel.add({id: ''+file.id, border: false, html: html, settings: file});
			this.setFilePanelHeight(panel);
		
			this.height = this.height + 18;
			this.setHeight(this.height);
			this.doLayout();
			this.findById(this.id + 'userPanel').body.setHeight(257 + this.height);
		
			this.doLayout();
			this.syncShadow();
		}
	},

	resetAnswerPanel: function() {
		this.findById(this.id + 'AnswerPanel').removeAll();
		this.doLayout();
		this.syncShadow();
	},

	deleteFileToFilesPanel: function(id, panelId) {
		var win = this;
		var extItem = this.findById(''+id);
		var panel = win.findById(panelId);

		if (extItem) {
			sw.swMsg.show({
				title: '',
				msg: langs('Вы действительно хотите удалить документ?'),
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId) {
					if (buttonId == 'yes') {
						panel.remove(extItem, true);
						win.setFilePanelHeight(panel);
						win.syncShadow();
					}
				}
			});
		}
	},
	
	// ---------- end of блок работы с файлами -------------
	
	loadEvnList: function() {
		var win = this,
			combo = win.form.findField('Evn_id'),
			value = combo.getValue(),
			params = {
				Person_id: win.form.findField('Person_id').getValue()
			};
			
		// если задисаблено, грузим только одно значение, чтобы было быстрее
		if (combo.disabled && value) { 
			params.Evn_id = value
		}
		
		var lm = this.getLoadMask('Загрузка списка случаев...');
		lm.show();
		win.form.findField('Evn_id').getStore().load({
			callback: function() {
				lm.hide();
				if (value) {
					combo.setValue(value);
				}
			},
			params: params
		});
	},
	
	doClearAnswer: function(QueryEvnMessage_id) {
		var win = this;
		win.findById(win.id + 'TextResponse'+QueryEvnMessage_id).setValue('');
		win.findById(win.id + 'filesPanel'+QueryEvnMessage_id).removeAll();
		win.setFilePanelHeight(win.findById(win.id + 'filesPanel'+QueryEvnMessage_id));
	},
	
	addAnswerPanel: function(data, resp) {
		var win = this,
			panel = win.findById(win.id + 'AnswerPanel');
			
		var element = {
			xtype: 'fieldset',
			id: win.id+'Answer'+data.QueryEvnMessage_id,
			QueryEvnMessage_id: data.QueryEvnMessage_id,
			pmUser_id: data.pmUser_id,
			MedPersonal_id: data.MedPersonal_id,
			style: 'margin: -10px 0;',
			border: false,
			autoHeight: true,
            labelWidth: 100,
			items: [{
                xtype: 'textarea',
            	width: 500,
                id: this.id + 'TextResponse'+data.QueryEvnMessage_id,
				value: data.QueryEvnMessage_TextResponse,
                name: 'QueryEvnMessage_TextResponse',
				emptyText: 'Введите описание результата или отказа',
				disabled: (win.scenario != 3 || !(data.pmUser_id == getGlobalOptions().pmuser_id || data.MedPersonal_id == getGlobalOptions().medpersonal_id)),
                fieldLabel: data.pmUser_Name
            }, {
                xtype: 'fieldset',
                id: this.id + 'filesPanel'+data.QueryEvnMessage_id,
				width: 500,
				style: 'margin: -5px 0 0 105px; background: #fff;',
				autoScroll: true,
				items: []
			}, {
                xtype: 'button',
				style: 'margin: 10px 0px 5px 400px;',
				text: 'Очистить',
				handler: function() {win.doClearAnswer(data.QueryEvnMessage_id)},
				hidden: win.scenario != 3,
				disabled: (win.scenario != 3 || (data.pmUser_id != getGlobalOptions().pmuser_id && !isUserGroup('QueryEvnResp'))),
                id: this.id + 'deleteAnswerButton'+data.QueryEvnMessage_id
			}, {
                xtype: 'button',
				style: 'margin: -26px 0 5px 475px;',
				hidden: win.scenario != 3,
				disabled: (win.scenario != 3 || !(data.pmUser_id == getGlobalOptions().pmuser_id || data.MedPersonal_id == getGlobalOptions().medpersonal_id)),
				text: 'Прикрепить файлы',
                id: this.id + 'addFilesButton'+data.QueryEvnMessage_id,
				menu: [
					new Ext.Action({text: 'ЭМК', handler: function() {this.addFileEmk(data.QueryEvnMessage_id)}.createDelegate(this)}),
					new Ext.Action({text: 'Мой компьютер', handler: function() {this.addFileComp(data.QueryEvnMessage_id)}.createDelegate(this)}),
				]
				
			}]
		};
		
		panel.add(element);
		panel.doLayout();
		
		this.height = this.height + 130;
		this.setHeight(this.height);
		this.doLayout();
		this.findById(this.id + 'userPanel').body.setHeight(257 + this.height);
		
		if(data.files && data.files.length) {
			Ext.each(data.files, function(el, idx) {
				el.id = el.EvnXml_id;
				el.name = el.EvnXml_Name;
				el.url = Ext.isEmpty(el.FilePath) ? null : 'uploads/evnmedia/' + el.FilePath;
				win.addFileToFilesPanel(el, win.findById(win.id + 'filesPanel'+data.QueryEvnMessage_id));
			});
		}
	},
	
	loadUserList: function(resp, addact) {
		var win = this,
			combo1 = win.findById(win.id + 'pmUser_NameResp'),
			combo2 = win.findById(win.id + 'pmUser_NameExec'),
			evn_id = win.form.findField('Evn_id').getValue();
			
		if (!evn_id) return false;
		
		combo1.getStore().load({
			callback: function() {
				if (addact == 'changeResp') {
					Ext.get(win.id + 'userPanel-xcollapsed').dom.click();
					combo1.focus(true);
				} else if (resp.pmUser_idResp) {
					var index = combo1.getStore().findBy(function(rec) { 
						return rec.get('PMUser_id') == resp.pmUser_idResp; 
					});
					if (index > -1) {
						var record = combo1.getStore().getAt(index);
						combo1.setValue(record.get('PMUser_id'));
					}
				}
			},
			params: {
				QueryEvnUserType_id: 3,
				Evn_id: evn_id
			}
		});
		
		combo2.getStore().load({
			callback: function() {
				if (addact == 'changeExec') {
					Ext.get(win.id + 'userPanel-xcollapsed').dom.click();
					combo2.focus(true);
				} else if (resp.pmUser_idExec) {
					var index = combo2.getStore().findBy(function(rec) { 
						return (rec.get('MedPersonal_id') == resp.MedPersonal_id && rec.get('MedStaffFact_id') == resp.MedStaffFact_id); 
					});
					if (index > -1) {
						var record = combo2.getStore().getAt(index);
						combo2.setValue(record.get('uid'));
					}
				}
			},
			params: {
				QueryEvnUserType_id: 2,
				Evn_id: evn_id
			}
		});
	},
	
	setScenario: function(scn) {
		var win = this;
		this.scenario = scn;
		
		this.form.findField('Person_id').disable();
		this.form.findField('Evn_id').disable();
		this.form.findField('QueryEvnType_id').disable();
		this.form.findField('QueryEvnMessage_TextRequest').disable();
		//this.form.findField('QueryEvnMessage_TextResponse').disable();
		//this.form.findField('QueryEvnMessage_TextResponse').setAllowBlank(true);
		//this.findById(this.id + 'addFilesButton').disable();
		this.findById(this.id + 'openEmkButton').show();
		Ext.getCmp(this.id + 'saveButton').show();
		
		switch (scn) {
			case 1: // новый запрос
				this.form.findField('Person_id').enable();
				this.form.findField('Evn_id').enable();
				this.form.findField('QueryEvnType_id').enable();
				this.form.findField('QueryEvnMessage_TextRequest').enable();
				//this.form.findField('QueryEvnMessage_TextResponse').hideContainer();
				//this.findById(this.id + 'filesPanel').hide();
				//this.findById(this.id + 'addFilesButton').hide();
				this.findById(this.id + 'openEmkButton').hide();
				Ext.getCmp(this.id + 'sendButton').show();
				this.setHeight(250);
				break;
				
			case 2: // редактирование отправленного запроса автором
				this.form.findField('QueryEvnMessage_TextRequest').enable();
				//this.form.findField('QueryEvnMessage_TextResponse').hideContainer();
				//this.findById(this.id + 'filesPanel').hide();
				//this.findById(this.id + 'addFilesButton').hide();
				Ext.getCmp(this.id + 'sendButton').hide();
				this.setHeight(250);
				break;
				
			case 3: // ответ на запрос
				//this.form.findField('QueryEvnMessage_TextResponse').showContainer();
				//this.form.findField('QueryEvnMessage_TextResponse').enable();
				//this.form.findField('QueryEvnMessage_TextResponse').setAllowBlank(false);
				//this.findById(this.id + 'filesPanel').show();
				//this.findById(this.id + 'addFilesButton').show();
				//this.findById(this.id + 'addFilesButton').enable();
				Ext.getCmp(this.id + 'sendButton').show();
				this.setHeight(250);
				//this.setHeight(450);
				break;
				
			case 4: // просмотр ответа
				//this.form.findField('QueryEvnMessage_TextResponse').showContainer();
				//this.findById(this.id + 'filesPanel').show();
				//this.findById(this.id + 'addFilesButton').hide();
				Ext.getCmp(this.id + 'saveButton').hide();
				Ext.getCmp(this.id + 'sendButton').hide();
				this.setHeight(250);
				break;
		}
	},
	
	show: function() {
        var win = this;
		sw.Promed.swQueryEvnEditWindow.superclass.show.apply(this, arguments);	
		
		this.action = '';
		this.height = 250;
		
		this.form.reset();
		Ext.get('QueryEvnStatus').dom.innerHTML = '';
		
        if ( !arguments[0] ) {
            arguments = [{}];
        }
		
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onSave = Ext.emptyFn;
		
		this.QueryEvn_id = arguments[0].QueryEvn_id || null;
		this.Person_id = arguments[0].Person_id || null;
		this.Person_Fio = arguments[0].Person_Fio || null;
		this.Evn_id = arguments[0].Evn_id || null;
		this.addact = arguments[0].addact || null;
		this.scenario = null;
		this.resetAnswerPanel();		
		this.Person_id = null;
		this.Server_id = null;
		this.PersonEvn_id = null;
					
		this.findById(win.id+'pmUser_NameExec').clearValue();
		this.findById(win.id+'pmUser_NameResp').clearValue();
		
		// Добавление 
		if (!this.QueryEvn_id) {
			this.setScenario(1);
			
		// редактирование
		} else {
			
			this.getLoadMask().show();
			this.form.load({
				params: {
					QueryEvn_id: win.QueryEvn_id
				},
				success: function(f, r) {
					win.getLoadMask().hide();
					var resp = Ext.util.JSON.decode(r.response.responseText)[0];
					
					win.Person_id = resp.Person_id;
					win.Server_id = resp.Server_id;
					win.PersonEvn_id = resp.PersonEvn_id;

					if (resp.QueryEvnStatus_id == 2) {
						win.setScenario(4);
					} else if (resp.QueryEvnStatus_id == 1) {
						if (resp.QueryEvnUserType_id == 1) { 
							win.setScenario(2); // автор - редактирует запрос
						} else if (resp.QueryEvnUserType_id && resp.QueryEvnUserType_id.inlist([2,3])) {
							win.setScenario(3); // исполнитель - редактирует ответ
						} else {
							win.setScenario(4); // остальные смотрят
						}
					} else {
						win.setScenario(1);
					}
					
					win.form.findField('Person_id').setValue(resp.Person_id);
					win.form.findField('Person_id').setRawValue(resp.Person_Fio);
					win.loadEvnList();
					
					if (win.scenario >= 3) {
						var isMeAnswered = false;
						if(resp.messages.length) {
							Ext.each(resp.messages, function(el, idx) {
								if (el.pmUser_id == getGlobalOptions().pmuser_id || el.MedPersonal_id == getGlobalOptions().medpersonal_id) {
									isMeAnswered = true;
								}
								win.addAnswerPanel(el, resp);
							});
						}
						
						// если ещё не отвечал - добавляем пустое поле
						if (win.scenario == 3 && !isMeAnswered) {
							win.addAnswerPanel({
								QueryEvnMessage_id: -Math.floor(Math.random() * 1000000),
								QueryEvnMessage_TextResponse: '',
								MedPersonal_id: getGlobalOptions().medpersonal_id,
								pmUser_id: getGlobalOptions().pmuser_id,
								pmUser_Name: getGlobalOptions().pmuser_name
							}, resp);
						}
					}
					
					win.findById(win.id+'pmUser_NameCreat').setValue(resp.pmUser_NameCreat);
					
					win.findById(win.id+'pmUser_NameExec').setDisabled(!(resp.QueryEvnStatus_id == 1 && win.scenario >= 3));
					win.findById(win.id+'pmUser_NameResp').setDisabled(!(resp.QueryEvnStatus_id == 1 && win.scenario >= 3 && isUserGroup('QueryEvnResp')));
					
					//win.findById(win.id+'pmUser_NameExec').setDisabled(false);
					//win.findById(win.id+'pmUser_NameResp').setDisabled(false);
					
					Ext.get('QueryEvnStatus').dom.innerHTML = resp.QueryEvnStatus_Name ? 'Статус запроса: ' + resp.QueryEvnStatus_Name : '';
					
					win.loadUserList(resp, win.addact);
				},
				url: '/?c=QueryEvn&m=load'
			});
		}
	},
	initComponent: function() {
		var win = this;

        var form = new Ext.form.FormPanel({
            url:'/?c=QueryEvn&m=save',
			id: 'QueryEvnEditForm',
            frame: true,
			border: false,
			region: 'center',
            labelAlign: 'right',
            labelWidth: 120,
            bodyStyle: 'padding: 5px 5px 0',
			defaults: {
            	width: 350
			},
            items: [{
                xtype: 'hidden',
                name: 'QueryEvn_id'
            }, {
                xtype: 'hidden',
                name: 'QueryEvnStatus_id'
            }, {
                xtype: 'swpersoncomboex',
                hiddenName: 'Person_id',
				fieldLabel: 'Пациент',
				allowBlank: false,
				onSelectPerson: function(data) {
					this.findById('QueryEvnEditForm').getForm().findField('Evn_id').setValue('');
					this.loadEvnList();
				}.createDelegate(this)
			}, {
				border: false,
				layout: 'column',
				anchor: '100%',
				items: [{
					layout: 'form',
					border: false,
					labelAlign: 'right',
					labelWidth: 120,
					width: 480,
					items: [{
						xtype: 'swbaselocalcombo',
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'Evn_id', mapping: 'Evn_id' },
								{ name: 'EvnClass_SysNick', mapping: 'EvnClass_SysNick' },
								{ name: 'Evn_Name', mapping: 'Evn_Name' },
							],
							key: 'Evn_id',
							sortInfo: { field: 'Evn_Name' },
							url:'/?c=QueryEvn&m=loadEvnList'
						}),
						displayField: 'Evn_Name',
						valueField: 'Evn_id',
						hiddenName: 'Evn_id',
						width: 350,
						listWidth: 600,
						fieldLabel: 'По случаю',
						allowBlank: false
					}]
				}, {
					xtype: 'button',
					id: this.id + 'openEmkButton',
					iconCls: 'idcard16',
					tooltip: 'Открыть ЭМК',
					handler: function(){
						win.openEMK();
					}
				}]
			}, {
                xtype: 'swcommonsprcombo',
                comboSubject: 'QueryEvnType',
                fieldLabel: 'Тип запроса',
				value: 1, // он пока один
                allowBlank: false
            }, {
                xtype: 'textarea',
            	width: 500,
                name: 'QueryEvnMessage_TextRequest',
				emptyText: 'Введите описание данных, которые требуется предоставить',
                fieldLabel: 'Запрос',
                allowBlank: false
            }, {
                xtype: 'fieldset',
                border: false,
            	width: 800,
				autoHeight: true,
                id: win.id + 'AnswerPanel',
				items:[]
			}],
			reader: new Ext.data.JsonReader({
				success: function() {}
			}, [
				{name: 'QueryEvn_id'},
				{name: 'QueryEvnStatus_id'},
				{name: 'Evn_id'},
				{name: 'QueryEvnType_id'},
				{name: 'QueryEvnMessage_TextRequest'}
			])
        });
		
		var uploadFilesPanel = new Ext.form.FormPanel({
			region: 'south',
			autoHeight: true,
			hidden: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'uploadFilesForm',
			labelAlign: 'right',
			labelWidth: 200,
			fileUpload: true,
			items: [{
				xtype: 'hidden',
				id: 'QueryEvnMessage_id_active'
			}, {
				xtype: 'textfield',
				inputType: 'file',
				id: 'uploadFilesInput',
				fieldLabel: langs('Выберите файлы для загрузки'),
				autoCreate: { tag: 'input', name: 'loadfiles[]', type: 'text', size: '20', autocomplete: 'off', multiple: 'multiple' },
				listeners: {
					'render': function() {
						// тупо ченж тут не работает, надо на дом-элемент вешать
						this.getEl().on('change', function() {
							win.uploadFiles();
						});
					}
				}
			}],
			url: '/?c=QueryEvn&m=uploadFiles'
		});

        var userPanel = new sw.Promed.Panel({
			region: 'west',
			border: true,
			id: this.id + 'userPanel',
			layout: 'form',
			bodyStyle: 'padding: 10px',
			collapsible: false,
			collapsed: true,
			title: 'Пользователи',
			width: 400,
			listeners: {
				'render': function() {
					setTimeout(function() {
						Ext.get(win.id+'userPanel-xcollapsed')
							.setStyle('background-image', 'url(/img/icons/users16.png)')
							.setStyle('background-position', '3px 5px')
							.setStyle('background-repeat', 'no-repeat');
					}, 50);
				}
			},
			items: [{
				xtype: 'textfield',
				fieldLabel: 'Автор',
				name: 'pmUser_NameCreat',
				id: win.id + 'pmUser_NameCreat',
				width: 240,
				disabled: true
			}, {
				xtype: 'swbaselocalcombo',
				hiddenName: 'pmUser_NameResp',
				id: win.id + 'pmUser_NameResp',
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{ name: 'MedStaffFact_id', mapping: 'MedStaffFact_id' },
						{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
						{ name: 'PMUser_id', mapping: 'PMUser_id' },
						{ name: 'Person_Fin', mapping: 'Person_Fin' },
					],
					key: 'MedStaffFact_id',
					sortInfo: { field: 'Person_Fin' },
					url:'/?c=QueryEvn&m=loadUsersList'
				}),
				width: 240,
				disabled: true,
				fieldLabel: 'Ответственный',
				displayField: 'Person_Fin',
				valueField: 'PMUser_id',
				listWidth: 300,
				allowBlank: false,
				listeners: {
					'select': function(combo, record, index){
						var params = {
							QueryEvn_id: win.form.findField('QueryEvn_id').getValue(),
							QueryEvnUserType_id: 3,
							MedStaffFact_id: combo.getFieldValue('MedStaffFact_id'),
							MedPersonal_id: combo.getFieldValue('MedPersonal_id'),
							pmUser_rid: combo.getFieldValue('PMUser_id'),
						}
						
						if (!params.pmUser_rid) return false;
						
						var lm = win.getLoadMask('Сохранение пользователя...');
						lm.show();

						Ext.Ajax.request({
							callback: function(options, success, response) {
								lm.hide();
							},
							params: params,
							url: '/?c=QueryEvn&m=saveUser'
						});
					}
				}
			}, {
				xtype: 'swbaselocalcombo',
				hiddenName: 'pmUser_NameExec',
				id: win.id + 'pmUser_NameExec',
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{ name: 'uid', mapping: 'uid' },
						{ name: 'MedStaffFact_id', mapping: 'MedStaffFact_id' },
						{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
						{ name: 'PMUser_id', mapping: 'PMUser_id' },
						{ name: 'Person_Fin', mapping: 'Person_Fin' },
					],
					key: 'uid',
					sortInfo: { field: 'Person_Fin' },
					url:'/?c=QueryEvn&m=loadUsersList'
				}),
				width: 240,
				disabled: true,
				fieldLabel: 'Исполнитель',
				displayField: 'Person_Fin',
				valueField: 'uid',
				listWidth: 300,
				allowBlank: false,
				listeners: {
					'select': function(combo, record, index){
						var params = {
							QueryEvn_id: win.form.findField('QueryEvn_id').getValue(),
							QueryEvnUserType_id: 2,
							MedStaffFact_id: record.get('MedStaffFact_id'),
							MedPersonal_id: record.get('MedPersonal_id'),
							pmUser_rid: record.get('PMUser_id'),
						}
						
						if (!params.MedPersonal_id || !params.pmUser_rid) return false;
						
						var lm = win.getLoadMask('Сохранение пользователя...');
						lm.show();
		
						Ext.Ajax.request({
							callback: function(options, success, response) {
								lm.hide();
							},
							params: params,
							url: '/?c=QueryEvn&m=saveUser'
						});
					}
				}
			}]
		});

		Ext.apply(this, {
			buttons:
			[{
				text: '-'
			}, {
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}, {
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				id: this.id + 'saveButton',
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				handler: function() 
				{
					this.ownerCt.doSave({doSend: true});
				},
				id: this.id + 'sendButton',
				iconCls: 'sent16',
				text: 'Отправить'
			},
			HelpButton(this, 0)],
			items:[userPanel,form,uploadFilesPanel]
		});
		sw.Promed.swQueryEvnEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = form.getForm();
		this.userPanel = userPanel;
		this.uploadForm = uploadFilesPanel.getForm();
		
        this.on('render', function(vt){
			var tr = this.footer.child('tr');
			var tdf = tr.child('td');
			var td = document.createElement('td');
			td.id = 'QueryEvnStatus';
			td.style = 'width: 300px; min-width: 300px; padding: 3px 0px 0 5px;';
			tr.dom.insertBefore(td,tdf.dom);
		}.createDelegate(this));
	}	
});