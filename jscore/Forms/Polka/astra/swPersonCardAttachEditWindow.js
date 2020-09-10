/**
 * swPersonCardAttachEditWindow - окно "Заявлений о выборе МО"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			18.12.2015
 */
/*NO PARSE JSON*/

sw.Promed.swPersonCardAttachEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	//height: 500,
	width: 680,
	id: 'swPersonCardAttachEditWindow',
	title: lang['zayavlenie_o_vyibore_mo'],
	maximizable: false,
	modal: true,
	resizable: true,

	doPrint: function() {
		var base_form = this.FormPanel.getForm();

		var PersonCardAttach_id = base_form.findField('PersonCardAttach_id').getValue();

		if (this.action == 'add' && Ext.isEmpty(PersonCardAttach_id)) {
			this.doSave({doPrint: true});
			this.hide();
			return;
		}

		if (Ext.isEmpty(PersonCardAttach_id)) {
			return false;
		}

		/*printBirt({
			'Report_FileName': 'han_EvnPrint_PersonCardAttach.rptdesign',
			'Report_Params': '&paramPersonCardAttach_id=' + PersonCardAttach_id,
			'Report_Format': 'pdf'
		});*/
		var Lpu_id = base_form.findField('Lpu_aid').getValue();
		var Person_id = base_form.findField('Person_id').getValue();
		printBirt({
			'Report_FileName': 'ApplicationForAttachment.rptdesign',
			'Report_Params': '&paramPerson_id=' + Person_id + '&paramDeputy=2&paramLpu='+Lpu_id,
			'Report_Format': 'pdf'
		});
	},

	showUploadDialog: function() {
		if(this.action != 'view') {
			this.UploadDialog.show();
		} else {
			alert(lang['okno_prikrepleniya_dokumenta_nedostupno_v_rejime_prosmotra']);
		}
	},

	uploadSuccess: function(dialog, data) {
		this.addFileToFilesPanel(data);
	},

	getCountFiles: function() {
		return this.FilesPanel.items.items.length;
	},

	setTitleFilesPanel: function() {
		var c = this.getCountFiles();
		if (c == 0) {
			var title = '<span style="color: gray;">нет приложенных документов</span>';
		} else {
			var tc = c.toString(), l = tc.length;
			var title = tc + ((tc.substring(l-1,1)=='1')?' документ':((tc.substring(l-1,1).inlist(['2','3','4']))?' документа': ' документов'));
		}
		this.FilesPanel.setTitle(lang['spisok_prilojennyih_dokumentov']+title);
	},

	addFileToFilesPanel: function(file) {
		if (file && file.name && file.size) {
			file.id = file.name.replace(/\./ig, '_');
			var html = '<div style="float:left;height:18px;">';
			// вот эта часть должна добавляться только к создаваемому письму
			var base_form = this.FormPanel.getForm();
			if(this.action.inlist(['edit','view']) && !Ext.isEmpty(file.url)) {
				html += '<a target="_blank" style="color: black; font-weight: bold;" href="'+file.url+'">'+file.name+'</a> ['+(file.size/1024).toFixed(2)+'Кб]';
			} else {
				html += '<b>'+file.name+'</b> ['+(file.size/1024).toFixed(2)+'Кб]';
			}
			if(this.action.inlist(['add','edit'])) {
				html = html + ' <a href="#" onClick="Ext.getCmp(\''+this.id+'\').deleteFileToFilesPanel(\''+file.id+'\');">'+
					'<img title="Удалить" style="height: 12px; width: 12px; vertical-align: bottom;" src="/img/icons/delete16.png" /></a>';
			}
			html = html + '</div>';
			if(this.FilesPanel.findById(file.id) != null) // Проверяем существует ли элемент с таким ид=)
				return false;
			this.FilesPanel.add({id: ''+file.id, border: false, html: html, settings: file});
			if(this.FilesPanel.collapsed)
				this.FilesPanel.expand();
			this.setTitleFilesPanel();
			this.FilesPanel.syncSize();
			this.FilesPanel.ownerCt.syncSize();
			this.doLayout();
			this.syncShadow();
		}
	},

	resetFilesPanel: function() {
		this.FilesPanel.removeAll();
		this.setTitleFilesPanel();
		this.doLayout();
		this.syncShadow();
	},

	loadLpuRegionList: function(callback) {
		callback = callback || Ext.emptyFn;
		var base_form = this.FormPanel.getForm();

		base_form.findField('LpuRegion_id').getStore().load({
			params: {
				Lpu_id: base_form.findField('Lpu_aid').getValue(),
				LpuRegionType_id: base_form.findField('LpuRegionType_id').getValue(),
				showOpenerOnlyLpuRegions: 1
			},
			callback: function(){
				if(!Ext.isEmpty(base_form.findField('MedStaffFact_id').getValue())){
					var msf_id = base_form.findField('MedStaffFact_id').getValue();
					base_form.findField('MedStaffFact_id').clearValue();
					base_form.findField('MedStaffFact_id').getStore().removeAll();
					var msf_combo = base_form.findField('MedStaffFact_id');
	                msf_combo.getStore().load({
						params: {
							Lpu_id: getGlobalOptions().lpu_id,
							LpuRegion_id: base_form.findField('LpuRegion_id').getValue(),
							showClosed: 1
						},
						callback: function(){
							base_form.findField('MedStaffFact_id').setValue(msf_id);
						}
					});
				}
			},
			success: function() {
				callback();
			}
		});
	},

	deleteFileToFilesPanel: function(id) {
		var win = this;
		var extItem = this.findById(''+id);
		var base_form = this.FormPanel.getForm();

		if (extItem) {
			sw.swMsg.show({
				title: '',
				msg: lang['vyi_deystvitelno_hotite_udalit_dokument'],
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId) {
					if (buttonId == 'yes') {
						// фактическое удаление с диска (на стороне вебсервера надо проверять, может ли пользователь удалять эти файлы)
						/*if(win.action == 'edit') {
							if(!Ext.isEmpty(extItem.settings.url)) {
								Ext.Ajax.request({
									url: '/?c=PersonCard&m=deleteFileFromPersonCard',
									params: extItem.settings,
									success: function(response, opts) {
										var obj = Ext.util.JSON.decode(response.responseText);
										if(!obj.success)
											return false;
										win.FilesPanel.remove(extItem, true);
										if (win.getCountFiles()==0) {
											//win.FilesPanel.collapse();
										}
										win.setTitleFilesPanel();
										win.FilesPanel.syncSize();
										win.FilesPanel.ownerCt.syncSize();
										win.syncShadow();
										win.doLayout();
									}
								});
							} else {
								win.FilesPanel.remove(extItem, true);
							}
						}*/

						// а потом уже удаление из панели
						win.FilesPanel.remove(extItem, true);
						win.setTitleFilesPanel();
						win.FilesPanel.syncSize();
						win.FilesPanel.ownerCt.syncSize();
						win.syncShadow();
					}
				}
			});
		}
	},

	doSave: function(options) {
		options = options || {};
		if ( this.action == 'view'  || this.formStatus == 'save')
			return false;

		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			this.formStatus = 'edit';
			return false;
		}

		var params = {};

		base_form.items.each(function(field) {
			if (field.disabled) {
				params[field.getName()] = field.value;
			}
		});

		// Собираем атрибуты прикрепленных файлов (если есть)
		var files = [];
		this.FilesPanel.findBy(function(file) {
			files.push(file.settings.name+'::'+file.settings.tmp_name);
		}, this.FilesPanel);
		if(files.length > 0) {
			params['files'] = files.join('|');
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		params.PersonAmbulatCard_id = base_form.findField('PersonAmbulatCard_id').getValue();
		base_form.submit({
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();
				var response_obj = Ext.util.JSON.decode(action.response.responseText);

				if (response_obj.Error_Msg) {
					//Ext.Msg.alert('Ошибка', response_obj.Error_Msg);
				} else {
					base_form.findField('PersonCardAttach_id').setValue(response_obj.PersonCardAttach_id);
					this.action = 'edit';
					Ext.getCmp('PCAEW_SaveButton').setText(BTN_FRMSAVE);
					Ext.getCmp('PCAEW_CancelButton').setText(BTN_FRMCANCEL);

					if (options.doPrint) {
						this.doPrint();
						this.callback();
					} else {
						this.callback();
						this.hide();
					}
				}
			}.createDelegate(this),
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();
				var response_obj = Ext.util.JSON.decode(action.response.responseText);

				var buttons = Ext.Msg.YESNO;
				if (response_obj.Error_Code == 101) {
					buttons = {yes: lang['soglesen'], no: lang['otmena']};
				}

				if (response_obj.Error_Msg && response_obj.Error_Msg == 'YesNo') {
					var msg = response_obj.Alert_Msg;
					sw.swMsg.show({
						buttons: buttons,
						fn: function(buttonId, text, obj) {
							if ( buttonId == 'yes' ) {
								switch (response_obj.Error_Code) {
									case '101':
										options.ignorePersonCardExists = 1;
										break;
								}
								this.doSave(options);
								return;
							}
						}.createDelegate(this),
						icon: Ext.MessageBox.QUESTION,
						msg: msg,
						title: lang['vopros']
					});
				}
			}.createDelegate(this)
		});

		return true;
	},

	getPersonCardCode: function() {
		var wnd = this;
		var base_form = this.FormPanel.getForm();
		base_form.findField('PersonCard_Code').setValue('');
		base_form.findField('PersonAmbulatCard_id').setValue('');
		var params = {
			Person_id: base_form.findField('Person_id').getValue(),
			Lpu_id : getGlobalOptions().lpu_id,
			AmbulatCardType_id : 1
		};
		
		
		if ( wnd.action == 'add')
		{
			params.CheckFond = 1;
		}
		base_form.findField('PersonCard_Code').getStore().load({params:params,baseParams:params});
	},

	show: function() {
		sw.Promed.swPersonCardAttachEditWindow.superclass.show.apply(this, arguments);

		this.callback = Ext.emptyFn;
		this.action = 'view';
		this.formStatus = 'edit';

		var base_form = this.FormPanel.getForm();

		base_form.reset();
		this.resetFilesPanel();

		if (arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		base_form.setValues(arguments[0].formParams);

		base_form.items.each(function(f){f.validate();});

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет загрузка..." });
		loadMask.show();

		switch(this.action) {
			case 'add':
				this.setTitle(lang['zayavlenie_o_vyibore_mo_dobavlenie']);
				this.enableEdit(true);
				Ext.getCmp('PCAEW_SaveButton').setText(lang['soglasen']);
				Ext.getCmp('PCAEW_CancelButton').setText(lang['otkaz']);

				base_form.findField('Lpu_aid').setValue(getGlobalOptions().lpu_id);

				getCurrentDateTime({
					callback: function(result) {
						if (result.date) {
							base_form.findField('PersonCardAttach_setDate').setValue(result.date);
						} else {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: this.hide.createDelegate(this),
								icon: Ext.Msg.ERROR,
								msg: lang['ne_udalos_poluchit_tekuschuyuyu_datu_s_servera'],
								title: lang['oshibka']
							});
						}
					}.createDelegate(this)
				});
				if(Ext.isEmpty(base_form.findField('PersonCard_Code').getValue())||Ext.isEmpty(base_form.findField('PersonAmbulatCard_id').getValue())){
					this.getPersonCardCode();
				}else{
					var params = {
						Person_id: base_form.findField('Person_id').getValue(),
						Lpu_id : getGlobalOptions().lpu_id,
						AmbulatCardType_id : 1
					};
					params.CheckFond = 1;
					base_form.findField('PersonCard_Code').getStore().load({params:params,baseParams:params})
				}
				this.PersonInfoPanel.load({
					Person_id: base_form.findField('Person_id').getValue()
				});

				this.loadLpuRegionList();

				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				if (this.action=='edit') {
					this.enableEdit(true);
					this.setTitle(lang['zayavlenie_o_vyibore_mo_redaktirovanie']);
				} else {
					this.enableEdit(false);
					this.setTitle(lang['zayavlenie_o_vyibore_mo_prosmotr']);
				}
				Ext.getCmp('PCAEW_SaveButton').setText(BTN_FRMSAVE);
				Ext.getCmp('PCAEW_CancelButton').setText(BTN_FRMCANCEL);

				base_form.load({
					url: '/?c=PersonCard&m=loadPersonCardAttachForm',
					params: {PersonCardAttach_id: base_form.findField('PersonCardAttach_id').getValue()},
					success: function (f, a)
					{
						var obj = Ext.util.JSON.decode(a.response.responseText)[0],
							files = obj.files;

						this.PersonInfoPanel.load({
							Person_id: obj.Person_id
						});

						this.loadLpuRegionList();

						for(var j=0; j<files.length; j++) {
							var ms = files[j].sizeinfo.match(/(\d+)+/g);
							files[j].size = ms ? ms[0] : 0;
							this.addFileToFilesPanel(files[j]);
						}

						loadMask.hide();
					}.createDelegate(this),
					failure: function (form,action)
					{
						loadMask.hide();
						//Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
					}.createDelegate(this)
				});
				break;
		}
	},

	initComponent: function()
	{
		this.UploadDialog = new Ext.ux.UploadDialog.Dialog({
			modal: true,
			title: lang['prikreplenie_faylov'],
			url: '/?c=PersonCard&m=uploadFiles',
			reset_on_hide: true,
			allow_close_on_upload: true,
			listeners: {
				uploadsuccess: function(dialog, filename, data) {
					this.uploadSuccess(dialog, data);
				}.createDelegate(this)
			},
			upload_autostart: false
		});

		this.PersonInfoPanel = new sw.Promed.PersonInformationPanel({
			id: 'PCAEW_PersonInformationFrame',
			button2Callback: function(callback_data) {
				var current_window = Ext.getCmp('swPersonCardAttachEditWindow');

				current_window.findById('PCAEW_PersonInformationFrame').load({Person_id: callback_data.Person_id, Server_id: callback_data.Server_id});
			},
			button1OnHide: function() {
				var current_window = Ext.getCmp('swPersonCardAttachEditWindow');
				if (current_window.action == 'view')
				{

				}
			},
			button2OnHide: function() {
				var current_window = Ext.getCmp('swPersonCardAttachEditWindow');
				if (current_window.action == 'view')
				{

				}
			},
			button3OnHide: function() {
				var current_window = Ext.getCmp('swPersonCardAttachEditWindow');
				if (current_window.action == 'view')
				{

				}
			},
			button4OnHide: function() {
				var current_window = Ext.getCmp('swPersonCardAttachEditWindow');
				if (current_window.action == 'view')
				{

				}
			},
			button5OnHide: function() {
				var current_window = Ext.getCmp('swPersonCardAttachEditWindow');
				if (current_window.action == 'view')
				{

				}
			}
		});

		this.FilesPanel = new Ext.Panel({
			layout: 'form',
			title: lang['spisok_prilojennyih_dokumentov_net_prilojennyih_dokumentov'],
			autoHeight: true,
			buttons: [
				{
					handler: function() {
						this.showUploadDialog();
					}.createDelegate(this),
					iconCls: 'add16',
					id: 'uploadbutton',
					//tabIndex: 2107,
					text: lang['prikrepit_dokumentyi'],
					align: 'left'
				},
				'-'
			],
			animCollapse: false,
			listeners: {
				beforeexpand: function() {
					return this.getCountFiles() > 0;
				}.createDelegate(this),
				collapse: function() {
					this.syncSize();
				}.createDelegate(this),
				expand: function() {
					this.syncSize();
				}.createDelegate(this)
			},
			floatable: false,
			style: 'margin: 3px;',
			bodyStyle: 'padding: 5px;',
			titleCollapse: true,
			items: []
		});

		this.FormPanel = new Ext.FormPanel({
			id: 'PCAEW_FormPanel',
			labelAlign: 'right',
			labelWidth: 100,
			autoHeight: true,
			frame: false,
			bodyStyle: 'padding: 5px;',
			keys: [{
				fn: function(inp, e) {
					var f = Ext.get(e.getTarget());
					this.doSearch(f.focus.createDelegate(f));
				},
				key: [ Ext.EventObject.ENTER ],
				scope: this,
				stopEvent: true
			}],
			items: [{
				xtype: 'hidden',
				name: 'PersonCardAttach_id'
			}, {
				xtype: 'hidden',
				name: 'Person_id'
			}, {
				xtype: 'hidden',
				name: 'Lpu_aid'
			},
			{
				name: 'PersonAmbulatCard_id',
				xtype: 'hidden'
			}, 
			{
				layout: 'column',
				border: false,
				items: [{
					border: false,
					layout: 'form',
					items: [{
						disabled: true,
						xtype: 'swdatefield',
						name: 'PersonCardAttach_setDate',
						fieldLabel: lang['data_zayavleniya'],
						width: 120
					}]
				}]
			}, {
				allowBlank: false,
				//enableKeyEvents: true,
				xtype: 'swlpuregiontypecombo',
				hiddenName : 'LpuRegionType_id',
				fieldLabel: lang['tip_uchastka'],
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();

						base_form.findField('LpuRegion_id').setValue(null);

						this.loadLpuRegionList();
					}.createDelegate(this)
				},
				width: 240
			}, {
				allowBlank: false,
				xtype: 'swlpuregioncombo',
				hiddenName: 'LpuRegion_id',
				fieldLabel: lang['uchastok'],
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();
						var rec = combo.getStore().getById(newValue);
						var lpu_region_type_id = base_form.findField('LpuRegionType_id').getValue();

						if (rec && !Ext.isEmpty(rec.get('LpuRegionType_id')) && lpu_region_type_id != rec.get('LpuRegionType_id')) {
							base_form.findField('LpuRegionType_id').setValue(rec.get('LpuRegionType_id'));
							this.loadLpuRegionList();
						}

						var msf_combo = base_form.findField('MedStaffFact_id');
						msf_combo.getStore().load({
							params: {
								Lpu_id: getGlobalOptions().lpu_id,
								LpuRegion_id: newValue,
								showClosed: 1
							},
							callback: function(res,par) {
								var index = msf_combo.getStore().findBy(function(rec) {
									return (rec.get('MedStaffRegion_isMain') == '2');
								});
								if(index >= 0)
								{
									var msf_rec = msf_combo.getStore().getAt(index);
									msf_combo.setValue(msf_rec.get('MedStaffFact_id'));
								}
							}
						});
					}.createDelegate(this)
				},
				width: 240
			},
			{
				allowBlank: false,
				width: 400,
				hidden: false,
				displayField: 'MedPersonal_FIO',
				fieldLabel: lang['vrach'],
				hiddenName: 'MedStaffFact_id',
				id: 'PCAMedStaffFact_id',
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{ name: 'MedStaffFact_id', type: 'int' },
						{ name: 'MedPersonal_FIO', type: 'string' },
						{ name: 'msr_descr', type: 'string'},
						{ name: 'MedStaffRegion_isMain', type: 'string'}
					],
					key: 'MedStaffFact_id',
					sortInfo: {
						field: 'MedStaffFact_id'
					},
					url: '/?c=LpuStructure&m=getMedStaffRegion'
				}),
				tabIndex: 2107,
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'{MedPersonal_FIO}&nbsp;<font color="red">{msr_descr}</font>',
					'</div></tpl>'
				),
				valueField: 'MedStaffFact_id',
				xtype: 'swbaselocalcombo'
			},
			{
				allowBlank: true,
				fieldLabel: lang['№_amb_kartyi'],
				name: 'PersonCard_Code',
				listeners: {
					'change':function(c,n,o){
						var base_form = this.FormPanel.getForm();
						base_form.findField('PersonAmbulatCard_id').setValue(n);
					}.createDelegate(this)
				},
				hiddenName: 'PersonCard_Code',
				tabIndex: 2110,
				width: 200,
				xtype: 'swpersoncardcombo'
			},
			{
				style: 'padding: 0px; margin-top: 10px;',
				xtype: 'fieldset',
				id: 'PCAEW_PersonCardAttachPanel',
				collapsed: false,
				autoHeight: true,
				layout: 'form',
				items: [this.FilesPanel]
			}],
			reader: new Ext.data.JsonReader({
				success: function() { }
			}, [
				{name: 'PersonCardAttach_id'},
				{name: 'Person_id'},
				{name: 'Lpu_aid'},
				{name: 'PersonCardAttach_setDate'},
				{name: 'LpuRegionType_id'},
				{name: 'LpuRegion_id'},
				{name: 'PersonCard_Code'},
				{name: 'MedStaffFact_id'}
			]),
			url: '/?c=PersonCard&m=savePersonCardAttachForm'
		});

		Ext.apply(this, {
			items: [
				this.PersonInfoPanel,
				this.FormPanel
			],
			buttons: [{
				handler: function () {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'PCAEW_SaveButton',
				text: lang['soglasen'],
				minWidth: 100
			}, {
				handler: function () {
					this.doPrint();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'PCAEW_PrintButton',
				text: lang['pechat_zayavleniya']
			}, {
				text: '-'
			}, /*{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function() {
					ShowHelp(this.title);
				}.createDelegate(this)
			},*/ {
				text: lang['otkaz'],
				iconCls: 'cancel16',
				id: 'PCAEW_CancelButton',
				handler: this.hide.createDelegate(this, []),
				minWidth: 100
			}]
		});
		sw.Promed.swPersonCardAttachEditWindow.superclass.initComponent.apply(this, arguments);
	}
});