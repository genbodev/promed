/**
* Форма "Направление на ВК"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      03.11.2011
*/

sw.Promed.swEvnPrescrVKWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: lang['napravlenie_na_vk'],
	maximizable: false,
	modal: true,
	resizable: false,
	shim: false,
	plain: true,
	autoHeight: true,
	width: 650,
    onClose: Ext.emptyFn,
    onSave: Ext.emptyFn,
	layout: 'border',
	buttonAlign: "right",
	objectName: 'swEvnPrescrVKWindow',
	closeAction: 'hide',
	id: 'swEvnPrescrVKWindow',
	objectSrc: '/jscore/Forms/Mse/swEvnPrescrVKWindow.js',
	buttons: [
		{
			handler: function()
			{
				this.ownerCt.doSave();
			},
			iconCls: 'save16',
			text: lang['sohranit']
		},
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : lang['otmena'],
			tabIndex  : -1,
			tooltip   : lang['otmena'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	
	listeners: {
		hide: function(w){
			w.CommonForm.getForm().reset();
            w.onClose();
		}
	},
	editEvnPrescrMse: function() {
		var base_form = this.CommonForm.getForm();
		var EvnStatus_SysNick = base_form.findField('EvnPrescrMse_id').getFieldValue('EvnStatus_SysNick');
		if (EvnStatus_SysNick && EvnStatus_SysNick == 'New') {
			getWnd('swDirectionOnMseEditForm').show({
				Person_id: base_form.findField('Person_id').getValue(),
				EvnPrescrMse_id: base_form.findField('EvnPrescrMse_id').getValue(),
				action: 'edit',
				onClose: function(){

				}
			});
		}
	},
	editEvnDirectionHTM: function() {
		var base_form = this.CommonForm.getForm();
		var EvnStatus_SysNick = base_form.findField('EvnDirectionHTM_id').getFieldValue('EvnStatus_SysNick');
		if (EvnStatus_SysNick && EvnStatus_SysNick == 'New') {
			getWnd('swDirectionOnHTMEditForm').show({
				Person_id: base_form.findField('Person_id').getValue(),
				EvnDirectionHTM_id: base_form.findField('EvnDirectionHTM_id').getValue(),
				action: 'edit',
				onClose: function(){

				}
			});
		}
	},
	show: function()
	{
		sw.Promed.swEvnPrescrVKWindow.superclass.show.apply(this, arguments);
		
		/** Параметры с которыми вызывается форма:
		*	
		*	Person_id - обязательный
		*	PersonEvn_id - обязательный
		*	Server_id - обязательный
		*/
        this.action = (arguments[0] && arguments[0].action) || 'add';
        this.onClose = (arguments[0] && arguments[0].onClose) || Ext.emptyFn;
        this.onSave = (arguments[0] && arguments[0].onSave) || Ext.emptyFn;
        this.EvnPrescrVK_id = (arguments[0] && arguments[0].EvnPrescrVK_id) || null;
		if(this.onSave==Ext.emptyFn){
			this.onSave = (arguments[0] && arguments[0].onHide) || Ext.emptyFn;
		}
		
		
		if(!arguments[0]){
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			this.hide();
			return false;
		}

		if (this.action == 'add' && (!arguments[0].Person_id || arguments[0].Person_id == null)) {
			sw.swMsg.alert(lang['oshibka'], lang['neopredelen_patsient']);
			this.hide();
			return false;
		}
        /*
        this.onClose = arguments[0].onClose || Ext.emptyFn;
        this.onSave = arguments[0].onSave || Ext.emptyFn;
		*/
		var win = this;
		var b_f = win.CommonForm.getForm();
		win.EvnPrescrMsePanel.hide();
		win.EvnDirectionHTMPanel.hide();
		b_f.findField('EvnXml_id').hideContainer();
		b_f.findField('PalliatQuestion_id').hideContainer();

		this.FileUploadPanel.reset();
		this.FileUploadPanel.setDisabled(false);
		this.EvnStatusHistoryGrid.removeAll({clearAll: true});
		this.EvnStatusHistoryGrid.hide();
		this.syncShadow();

		var
			diag_combo = b_f.findField('Diag_id'),
			evn_combo = b_f.findField('EvnPrescrVK_pid'),
			stick_combo = b_f.findField('EvnStick_id');

		evn_combo.getStore().removeAll();
		stick_combo.getStore().removeAll();

		b_f.setValues(arguments[0]);
		b_f.findField('Lpu_gid').setValue(getGlobalOptions().lpu_id);

		if ( arguments[0].EvnDirection_pid ) {
			evn_combo.getStore().baseParams.EvnDirection_pid = arguments[0].EvnDirection_pid;
		}
		else {
			evn_combo.getStore().baseParams.EvnDirection_pid = null;
		}

		switch(this.action) {
			case 'add':
				win.enableEdit(true);
				this.setTitle(langs('Направление на ВК: Добавление'));
				evn_combo.getStore().baseParams.Person_id = b_f.findField('Person_id').getValue();

				var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Загрузка списка событий..." });
				loadMask.show();

				evn_combo.getStore().load({
					callback: function() {
						loadMask.hide();
						if (evn_combo.getStore().getCount() == 1) {
							evn_combo.setValue(evn_combo.getStore().getAt(0).get('Evn_id'));
							evn_combo.fireEvent('change', evn_combo, evn_combo.getValue());
						}
					}
				});

				if ( !Ext.isEmpty(diag_combo.getValue()) ) {
					diag_combo.getStore().load({
						params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_combo.getValue() },
						callback: function () {
							diag_combo.getStore().each(function(r) {
								if( r.get('Diag_id') == diag_combo.getValue() ) {
									diag_combo.fireEvent('select', diag_combo, r, 0);
								}
							});
						}
					});
				}

				b_f.items.each(function(f) {f.validate();});

				this.center();
				b_f.findField('CauseTreatmentType_id').focus(true, 100);

				break;
			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle(langs('Направление на ВК: Редактирование'));
					win.enableEdit(true);
				} else {
					this.setTitle(langs('Направление на ВК: Просмотр'));
					win.enableEdit(false);
				}
				win.getLoadMask(LOAD_WAIT).show();
				b_f.load({
					url: '/?c=Mse&m=loadEvnPrescrVKWindow',
					params: {
						EvnPrescrVK_id: win.EvnPrescrVK_id
					},
					success: function(f, r) {
						win.getLoadMask().hide();

						//загружаем файлы
						win.FileUploadPanel.listParams = {
							Evn_id: win.EvnPrescrVK_id
						};
						win.FileUploadPanel.loadData({
							Evn_id: win.EvnPrescrVK_id,
							callback: function() {
								win.FileUploadPanel.setDisabled('view' == win.action);
							}
						});
						win.FileUploadPanel.setDisabled('view' == win.action);
						win.EvnStatusHistoryGrid.loadData({globalFilters: {EvnPrescrVK_id: win.EvnPrescrVK_id}});

						var diagField = b_f.findField('Diag_id');
						var Diag_id = diagField.getValue();
						if (!Ext.isEmpty(Diag_id)) {
							diagField.getStore().load({
								params: {
									Diag_id: Diag_id
								},
								callback: function() {
									diagField.setValue(Diag_id);
								}
							});
						}

						var evnStickField = b_f.findField('EvnStick_id');
						var EvnStick_id = evnStickField.getValue();
						if (!Ext.isEmpty(EvnStick_id)) {
							evnStickField.getStore().load({
								params: {
									EvnStickBase_id: EvnStick_id
								},
								callback: function() {
									evnStickField.setValue(EvnStick_id);
								}
							});
						}

						var pidField = b_f.findField('EvnPrescrVK_pid');
						var EvnPrescrVK_pid = pidField.getValue();
						if (!Ext.isEmpty(EvnPrescrVK_pid)) {
							pidField.getStore().load({
								params: {
									Person_id: b_f.findField('Person_id').getValue(),
									Evn_id: EvnPrescrVK_pid
								},
								callback: function() {
									pidField.setValue(EvnPrescrVK_pid);
								}
							});
						}

						b_f.findField('CauseTreatmentType_id').fireEvent('change', b_f.findField('CauseTreatmentType_id'), b_f.findField('CauseTreatmentType_id').getValue());

						win.syncShadow();
					},
					failure: function() {
						win.getLoadMask().hide();
						sw.swMsg.alert(langs('Ошибка'), langs('Не удалось загрузить данные для формы!'));
						win.hide();
						return false;
					}
				});
				break;
		}
	},
	
	doSave: function()
	{
		var win = this;
		var frm = this.CommonForm.getForm();
		if(!frm.isValid()){
			sw.swMsg.alert(lang['oshibka'], lang['zapolnenyi_ne_vse_obyazatelnyie_polya_obyazatelnyie_k_zapolneniyu_polya_vyidelenyi_osobo']);
			return false;
		}

		var CauseTreatmentType_id = frm.findField('CauseTreatmentType_id').getValue();

		var index = frm.findField('CauseTreatmentType_id').getStore().findBy(function(rec) {
			return (rec.get('CauseTreatmentType_id') == CauseTreatmentType_id);
		});

		if ( index >= 0
			&& frm.findField('CauseTreatmentType_id').getStore().getAt(index).get('CauseTreatmentType_Code').inlist([ 1, 4 ])
			&& Ext.isEmpty(frm.findField('EvnStick_id').getValue())
			&& Ext.isEmpty(frm.findField('EvnPrescrVK_LVN').getValue())
		) {
			sw.swMsg.alert(lang['oshibka'], lang['dlya_vyibrannoy_prichinyi_napravleniya_neobhodimo_ukazat_lvn']);
			return false;
		}

		var params = {};
		params.LpuSection_sid = getGlobalOptions().CurLpuSection_id;
		params.MedPersonal_sid = getGlobalOptions().medpersonal_id;
		params.Diag_id = frm.findField('Diag_id').getValue();
		
		win.getLoadMask(lang['sohranenie_dannyih']).show();
		var data = frm.getValues(false);
		frm.submit({
			params: params,
			success: function(f,action){
				win.getLoadMask().hide();
				if(action.result.success) {
					data.Evn_id = action.result.EvnPrescrVK_id;
					data.EvnPrescrVK_id = action.result.EvnPrescrVK_id;

					win.FileUploadPanel.listParams = {Evn_id: action.result.EvnPrescrVK_id};
					win.FileUploadPanel.saveChanges();

					win.onSave(data);
                    win.hide();
				} else {
					sw.swMsg.alert(lang['oshibka'], (action.result.Error_Msg)?action.result.Error_Msg:lang['ne_udalos_sohranit_dannyie']);
				}
			},
			failure: function(){
				win.getLoadMask().hide();
				//sw.swMsg.alert('Ошибка', 'Не удалось сохранить данные!');
			}
		});
	},

	initComponent: function()
	{
		var cur_win = this;
		
		this.inT = function(){
			var ts = this.trigger.select('.x-form-trigger', true);
			this.wrap.setStyle('overflow', 'hidden');
			var triggerField = this;
			ts.each(function(t, all, index){
				t.hide = function(){
					var w = triggerField.wrap.getWidth();
					this.dom.style.display = 'none';
					triggerField.el.setWidth(w-triggerField.trigger.getWidth());
				};
				t.show = function(){
					var w = triggerField.wrap.getWidth();
					this.dom.style.display = '';
					triggerField.el.setWidth(w-triggerField.trigger.getWidth());
				};
				var triggerIndex = 'Trigger'+(index+1);
				if(this['hide'+triggerIndex]){
					t.dom.style.display = 'none';
				}
				t.on("click", this['on'+triggerIndex+'Click'], this, {preventDefault:true});
				t.addClassOnOver('x-form-trigger-over');
				t.addClassOnClick('x-form-trigger-click');
			}, this);
			this.triggers = ts.elements;
		};

		this.FileUploadPanel = new sw.Promed.FileUploadPanel({
			win: this,
			commentTextfieldWidth: 120,
			uploadFieldColumnWidth: .6,
			commentTextColumnWidth: .35,
			width: 600,
			buttonAlign: 'left',
			buttonLeftMargin: 100,
			labelWidth: 150,
			folder: 'evnmedia/',
			style: 'background: transparent',
			dataUrl: '/?c=EvnMediaFiles&m=loadEvnMediaFilesListGrid',
			saveUrl: '/?c=EvnMediaFiles&m=uploadFile',
			saveChangesUrl: '/?c=EvnMediaFiles&m=saveChanges',
			deleteUrl: '/?c=EvnMediaFiles&m=deleteFile'
		});
		
		this.CommonForm = new Ext.form.FormPanel({
			autoHeight: true,
			frame: true,
			url: '/?c=Mse&m=saveEvnPrescrVK',
			bodyStyle: 'padding: 5px;',
			defaults: {
				border: false
			},
			items: [{
				layout: 'form',
				width: 560,
				items: [{
					xtype: 'hidden',
					name: 'EvnPrescrVK_id'
				}, {
					xtype: 'hidden',
					name: 'Person_id'
				}, {
					xtype: 'hidden',
					name: 'PersonEvn_id'
				}, {
					xtype: 'hidden',
					name: 'Server_id'
				}, {
					xtype: 'hidden',
					name: 'MedService_id'
				}, {
					xtype: 'hidden',
					name: 'Lpu_gid'
				}, {
					xtype: 'hidden',
					name: 'TimetableMedService_id'
				}, {
					layout: 'form',
					labelAlign: 'right',
					labelWidth: 140,
					border: false,
					items: [{
						allowBlank: false,
						anchor: '100%',
						comboSubject: 'CauseTreatmentType',
						fieldLabel: lang['prichina_napravleniya'],
						hiddenName: 'CauseTreatmentType_id',
						listWidth: 400,
						typeCode: 'int',
						xtype: 'swcommonsprcombo',
						listeners: {
							'select': function(combo, record, index) {
								combo.fireEvent('change', combo, combo.getValue());
							},
							'change': function(combo, newValue, oldValue) {
								var base_form = this.CommonForm.getForm(),
									epm_combo = base_form.findField('EvnPrescrMse_id'),
									edh_combo = base_form.findField('EvnDirectionHTM_id'),
									ex_combo = base_form.findField('EvnXml_id'),
									pq_combo = base_form.findField('PalliatQuestion_id'),
									EvnPrescrMse_id = epm_combo.getValue(),
									EvnDirectionHTM_id = edh_combo.getValue();

								base_form.findField('Diag_id').setDisabled(false);
								if (newValue == 5) {
									cur_win.EvnPrescrMsePanel.show();
									epm_combo.setAllowBlank(false);
									epm_combo.getStore().removeAll();
									epm_combo.getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();
									epm_combo.getStore().load({
										params: {
											EvnPrescrMse_id: EvnPrescrMse_id
										},
										callback: function() {
											var index = -1;
											if (!Ext.isEmpty(EvnPrescrMse_id)) {
												index = epm_combo.getStore().findBy(function(rec) {
													return (rec.get('EvnPrescrMse_id') == EvnPrescrMse_id);
												});
											}
											if (index < 0 && epm_combo.getStore().getCount() == 1) {
												index = 0;
											}
											if (index >= 0) {
												epm_combo.setValue(epm_combo.getStore().getAt(index).get('EvnPrescrMse_id'));
												epm_combo.fireEvent('change', epm_combo, epm_combo.getValue());
											}
										}
									});
									base_form.findField('Diag_id').setDisabled(true);
								} else {
									cur_win.EvnPrescrMsePanel.hide();
									epm_combo.setAllowBlank(true);
								}
								if (newValue == 12 && getRegionNick().inlist(['perm', 'vologda'])) {
									cur_win.EvnDirectionHTMPanel.show();
									edh_combo.setAllowBlank(false);
									edh_combo.getStore().removeAll();
									edh_combo.getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();
									edh_combo.getStore().load({
										params: {
											EvnDirectionHTM_id: EvnDirectionHTM_id
										},
										callback: function() {
											var index = -1;
											if (!Ext.isEmpty(EvnDirectionHTM_id)) {
												index = edh_combo.getStore().findBy(function(rec) {
													return (rec.get('EvnDirectionHTM_id') == EvnDirectionHTM_id);
												});
											}
											if (index < 0 && edh_combo.getStore().getCount() == 1) {
												index = 0;
											}
											if (index >= 0) {
												edh_combo.setValue(edh_combo.getStore().getAt(index).get('EvnDirectionHTM_id'));
												edh_combo.fireEvent('change', edh_combo, edh_combo.getValue());
											}
										}
									});
									base_form.findField('Diag_id').setDisabled(true);
								} else {
									cur_win.EvnDirectionHTMPanel.hide();
									edh_combo.setAllowBlank(true);
								}
								if (newValue == 21) {
									ex_combo.showContainer();
									ex_combo.getStore().baseParams.Evn_id = base_form.findField('EvnPrescrVK_pid').getValue();
									ex_combo.getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();
									ex_combo.getStore().load({
										callback: function() {
											if (ex_combo.getStore().getCount()) {
												ex_combo.setValue(ex_combo.getStore().getAt(0).get('EvnXml_id'));
												ex_combo.fireEvent('change', ex_combo, ex_combo.getValue());
											}
										}
									});
									pq_combo.showContainer();
									pq_combo.getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();
									pq_combo.getStore().load({
										callback: function() {
											if (pq_combo.getStore().getCount()) {
												pq_combo.setValue(pq_combo.getStore().getAt(0).get('PalliatQuestion_id'));
												pq_combo.fireEvent('change', pq_combo, pq_combo.getValue());
											}
										}
									});
									pq_combo.setAllowBlank(false);
								} else {
									ex_combo.hideContainer();
									pq_combo.hideContainer();
									pq_combo.setAllowBlank(true);
								}
								this.doLayout();
								this.syncShadow();
							}.createDelegate(this)
						}
					}, {
						xtype: 'swbaselocalcombo',
						triggerConfig: {
							tag:'span', cls:'x-form-twin-triggers', cn:[
							{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-select-trigger"},
							{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-search-trigger"}
						]},
						initTrigger: cur_win.inT,
						editable: false,
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.CommonForm.getForm();

								base_form.findField('EvnStick_id').clearValue();
								base_form.findField('EvnStick_id').getStore().baseParams.Evn_id = null;
								base_form.findField('EvnStick_id').getStore().removeAll();

								base_form.findField('Diag_id').clearValue();
								base_form.findField('Diag_id').getStore().removeAll();

								if ( !Ext.isEmpty(newValue) ) {
									var evn_stick_combo = base_form.findField('EvnStick_id');
									evn_stick_combo.getStore().baseParams.Evn_id = newValue;
									evn_stick_combo.getStore().load({
										callback: function() {
											if (evn_stick_combo.getStore().getCount() > 0) {
												evn_stick_combo.setValue(evn_stick_combo.getStore().getAt(0).get('EvnStickBase_id'));
											}
										}
									});

									if (!Ext.isEmpty(combo.getFieldValue('Diag_id'))) {
										var diag_combo = base_form.findField('Diag_id');
										diag_combo.getStore().load({
											callback: function() {
												diag_combo.getStore().each(function(r) {
													if( r.get('Diag_id') == combo.getFieldValue('Diag_id') ) {
														diag_combo.setValue(combo.getFieldValue('Diag_id'));
														diag_combo.fireEvent('select', diag_combo, r, 0);
													}
												});
											},
											params: { where: "where DiagLevel_id = 4 and Diag_id = " + combo.getFieldValue('Diag_id') }
										});
									}
								}
								else {
									
								}
							}.createDelegate(this)
						},
						store: new Ext.data.Store({
							autoLoad: false,
							reader: new Ext.data.JsonReader({
								id: 'Evn_id'
							}, [
								{ mapping: 'Evn_id', name: 'Evn_id', type: 'int' },
								{ mapping: 'Evn_NumCard', name: 'Evn_NumCard', type: 'string' },
								{ mapping: 'Diag_id', name: 'Diag_id', type: 'int' }
							]),
							url: '/?c=ClinExWork&m=getEvnNumCardList',
							listeners: {
								load: function (s, recs, idx) {
									if(recs[0]) {
										//var c = this.CommonForm.getForm().findField('EvnPrescrVK_pid');
										//c.setValue(recs[0].get('Evn_id'));
									}
								}.createDelegate(this)
							}
						}),
						allowBlank: false,
						readOnly: true,
						enableKeyEvents: true,
						mode: 'local',
						anchor: '100%',
						triggerAction: 'all',
						fieldLabel: lang['tap_kvs'],
						hiddenName: 'EvnPrescrVK_pid',
						valueField: 'Evn_id',
						displayField: 'Evn_NumCard',
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{Evn_NumCard}&nbsp;',
							'</div></tpl>'
						),
						onTrigger1Click: function() {
							this.focus(true);
							if(this.getStore().getCount()>0) {
								( this.isExpanded() ) ? this.collapse() : this.expand();
							}
						},
						onTrigger2Click: function() {
							var b_f = this.CommonForm.getForm(),
								f = b_f.findField('EvnPrescrVK_pid'),
								stickField = b_f.findField('EvnStick_id'),
								personfield = b_f.findField('Person_id');
							getWnd('swEvnPLEvnPSSearchWindow').show({
								Person_id: personfield.getValue(),
								onHide: function() {
									f.focus(false);
								},
								onSelect: function(persData) {
									//persData.EvnVK_NumCard = persData.Evn_NumCard;
									f.getStore().baseParams = {
										Evn_id: persData.Evn_id,
										Person_id: personfield.getValue()
									};
									f.setValue(persData.Evn_NumCard);
									f.getStore().load({
										callback: function() {
											if (f.getStore().getCount() > 0) {
												f.setValue(f.getStore().getAt(0).get('Evn_id'));
												f.fireEvent('change', f, f.getValue());
											}
										}
									});
									getWnd('swEvnPLEvnPSSearchWindow').hide();
								}
							});
						}.createDelegate(this)
					}, cur_win.EvnPrescrMsePanel = new Ext.Panel({
						border: false,
						defaults: {
							border: false
						},
						layout: 'column',
						items: [{
							columnWidth: 1,
							layout: 'form',
							labelAlign: 'right',
							labelWidth: 140,
							items: [{
								xtype: 'swbaselocalcombo',
								triggerConfig: {
									tag:'span', cls:'x-form-twin-triggers', cn:[
										{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-select-trigger"},
										{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-plus-trigger"}
									]},
								initTrigger: cur_win.inT,
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var base_form = this.CommonForm.getForm();
										base_form.findField('Diag_id').clearValue();
										base_form.findField('Diag_id').getStore().removeAll();

										if ( !Ext.isEmpty(newValue) ) {
											if (!Ext.isEmpty(combo.getFieldValue('Diag_id'))) {
												var diag_combo = base_form.findField('Diag_id');
												diag_combo.getStore().load({
													callback: function() {
														diag_combo.getStore().each(function(r) {
															if( r.get('Diag_id') == combo.getFieldValue('Diag_id') ) {
																diag_combo.setValue(combo.getFieldValue('Diag_id'));
																diag_combo.fireEvent('select', diag_combo, r, 0);
															}
														});
													},
													params: { where: "where DiagLevel_id = 4 and Diag_id = " + combo.getFieldValue('Diag_id') }
												});
											}
										}
									}.createDelegate(this)
								},
								store: new Ext.data.Store({
									autoLoad: false,
									reader: new Ext.data.JsonReader({
										id: 'EvnPrescrMse_id'
									}, [
										{ mapping: 'EvnPrescrMse_id', name: 'EvnPrescrMse_id', type: 'int' },
										{ mapping: 'EvnPrescrMse_Name', name: 'EvnPrescrMse_Name', type: 'string' },
										{ mapping: 'Diag_id', name: 'Diag_id', type: 'int' },
										{ mapping: 'EvnStatus_SysNick', name: 'EvnStatus_SysNick', type: 'string' }
									]),
									url: '/?c=Mse&m=getEvnPrescrMseList'
								}),
								allowBlank: true,
								enableKeyEvents: true,
								mode: 'local',
								anchor: '100%',
								fieldLabel: 'Направление на МСЭ',
								hiddenName: 'EvnPrescrMse_id',
								valueField: 'EvnPrescrMse_id',
								displayField: 'EvnPrescrMse_Name',
								triggerAction: 'all',
								onTrigger1Click: function() {
									this.focus(true);
									if(this.getStore().getCount()>0) {
										( this.isExpanded() ) ? this.collapse() : this.expand();
									}
								},
								onTrigger2Click: function() {
									var b_f = this.CommonForm.getForm(),
										f = b_f.findField('EvnPrescrMse_id'),
										Person_id = b_f.findField('Person_id').getValue(),
										Server_id = b_f.findField('Server_id').getValue(),
										EvnPrescrVK_pid = b_f.findField('EvnPrescrVK_pid').getValue(),
										EvnDirection_pid = b_f.findField('EvnPrescrVK_pid').getStore().baseParams.EvnDirection_pid;
									checkEvnPrescrMseExists({
										Person_id: Person_id,
										callback: function() {
											createEvnPrescrMse({
												personData: {
													Person_id: Person_id,
													Server_id: Server_id
												},
												userMedStaffFact: {},
												directionData: {
													//EvnDirection_pid: EvnPrescrVK_pid
													EvnDirection_pid: ( EvnDirection_pid ) ? EvnDirection_pid : EvnPrescrVK_pid
												},
												callback: function(data) {
													f.getStore().load({
														callback: function() {
															if (f.getStore().getCount() > 0) {
																f.setValue(data.Evn_id);
																f.fireEvent('change', f, f.getValue());
															}
														}
													});
												}
											})
										}.createDelegate(this)
									});
								}.createDelegate(this)
							}]
						}, {
							width: 25,
							hidden: !getRegionNick().inlist(['perm', 'vologda']),
							items: [{
								iconCls: 'edit16',
								handler: function() {
									cur_win.editEvnPrescrMse();
								},
								xtype: 'button'
							}]
						}]
					}), cur_win.EvnDirectionHTMPanel = new Ext.Panel({
						border: false,
						defaults: {
							border: false
						},
						layout: 'column',
						items: [{
							columnWidth: 1,
							layout: 'form',
							labelAlign: 'right',
							labelWidth: 140,
							items: [{
								xtype: 'swbaselocalcombo',
								triggerConfig: {
									tag:'span', cls:'x-form-twin-triggers', cn:[
										{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-select-trigger"},
										{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-plus-trigger"}
									]},
								initTrigger: cur_win.inT,
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var base_form = this.CommonForm.getForm();
										base_form.findField('Diag_id').clearValue();
										base_form.findField('Diag_id').getStore().removeAll();

										if ( !Ext.isEmpty(newValue) ) {
											if (!Ext.isEmpty(combo.getFieldValue('Diag_id'))) {
												var diag_combo = base_form.findField('Diag_id');
												diag_combo.getStore().load({
													callback: function() {
														diag_combo.getStore().each(function(r) {
															if( r.get('Diag_id') == combo.getFieldValue('Diag_id') ) {
																diag_combo.setValue(combo.getFieldValue('Diag_id'));
																diag_combo.fireEvent('select', diag_combo, r, 0);
															}
														});
													},
													params: { where: "where DiagLevel_id = 4 and Diag_id = " + combo.getFieldValue('Diag_id') }
												});
											}
										}
									}.createDelegate(this)
								},
								store: new Ext.data.Store({
									autoLoad: false,
									reader: new Ext.data.JsonReader({
										id: 'EvnDirectionHTM_id'
									}, [
										{ mapping: 'EvnDirectionHTM_id', name: 'EvnDirectionHTM_id', type: 'int' },
										{ mapping: 'EvnDirectionHTM_Name', name: 'EvnDirectionHTM_Name', type: 'string' },
										{ mapping: 'Diag_id', name: 'Diag_id', type: 'int' },
										{ mapping: 'EvnStatus_SysNick', name: 'EvnStatus_SysNick', type: 'string' }
									]),
									url: '/?c=Mse&m=getEvnDirectionHTMList'
								}),
								allowBlank: true,
								enableKeyEvents: true,
								mode: 'local',
								anchor: '100%',
								fieldLabel: 'Направление на ВМП',
								hiddenName: 'EvnDirectionHTM_id',
								valueField: 'EvnDirectionHTM_id',
								displayField: 'EvnDirectionHTM_Name',
								triggerAction: 'all',
								onTrigger1Click: function() {
									this.focus(true);
									if(this.getStore().getCount()>0) {
										( this.isExpanded() ) ? this.collapse() : this.expand();
									}
								},
								onTrigger2Click: function() {
									var b_f = this.CommonForm.getForm(),
										f = b_f.findField('EvnDirectionHTM_id'),
										Person_id = b_f.findField('Person_id').getValue(),
										Server_id = b_f.findField('Server_id').getValue(),
										PersonEvn_id = b_f.findField('PersonEvn_id').getValue(),
										EvnPrescrVK_pid = b_f.findField('EvnPrescrVK_pid').getValue(),
										EvnDirection_pid = b_f.findField('EvnPrescrVK_pid').getStore().baseParams.EvnDirection_pid;

									getWnd('swDirectionOnHTMEditForm').show({
										action: 'add',
										Person_id: Person_id,
										PersonEvn_id: PersonEvn_id,
										Server_id: Server_id,
										LpuSection_id: getGlobalOptions().CurLpuSection_id,
										LpuSection_did: getGlobalOptions().CurLpuSection_id,
										EvnDirectionHTM_pid: ( EvnDirection_pid ) ? EvnDirection_pid : EvnPrescrVK_pid,
										onSave: function(data) {
											f.getStore().load({
												callback: function() {
													if (f.getStore().getCount() > 0) {
														f.setValue(data.Evn_id);
														f.fireEvent('change', f, f.getValue());
													}
												}
											});
										}
									});
								}.createDelegate(this)
							}]
						}, {
							width: 25,
							hidden: !getRegionNick().inlist(['perm', 'vologda']),
							items: [{
								iconCls: 'edit16',
								handler: function() {
									cur_win.editEvnDirectionHTM();
								},
								xtype: 'button'
							}]
						}]
					}), {
						xtype: 'swdiagcombo',
						anchor: '100%',
						allowBlank: false,
						hiddenName: 'Diag_id',
						fieldLabel: lang['osnovnoy_diagnoz']
					}, {
						xtype: 'swbaselocalcombo',
						fieldLabel: lang['lvn'],
						editable: false,
						anchor: '100%',
						readOnly: true,
						mode: 'local',
						hiddenName: 'EvnStick_id',
						valueField: 'EvnStickBase_id',
						displayField: 'EvnStick_all',
						enableKeyEvents: true,
						store: new Ext.data.Store({
							autoLoad: false,
							listeners: {
								'load': function(store, records, index) {
									var combo = this.CommonForm.getForm().findField('EvnStick_id');

									if ( typeof records == 'object' && records.length > 0 ) {
										combo.setValue(records[0].get('EvnStickBase_id'));
									}
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'EvnStickBase_id'
							}, [
								{ mapping: 'EvnStickBase_id', name: 'EvnStickBase_id', type: 'int' },
								{ mapping: 'EvnStick_all', name: 'EvnStick_all', type: 'string' }
							]),
							url: '/?c=ClinExWork&m=getEvnVKStick'
						})
					}, {
						allowBlank: true,
						anchor: '100%',
						fieldLabel: lang['lvn_ruchnoy_vvod'],
						name: 'EvnPrescrVK_LVN',
						xtype: 'textfield'
					}, {
						anchor: '100%',
						fieldLabel: lang['primechanie'],
						name: 'EvnPrescrVK_Note',
						maxLength: 100,
						xtype: 'textfield'
					}, {
						xtype: 'swbaselocalcombo',
						fieldLabel: langs('Эпикриз'),
						editable: false,
						anchor: '100%',
						mode: 'local',
						hiddenName: 'EvnXml_id',
						valueField: 'EvnXml_id',
						displayField: 'EvnXml_Name',
						enableKeyEvents: true,
						store: new Ext.data.Store({
							autoLoad: false,
							listeners: {
								'load': function(store, records, index) {
									var combo = this.CommonForm.getForm().findField('EvnXml_id');

									if ( typeof records == 'object' && records.length > 0 ) {
										combo.setValue(records[0].get('EvnXml_id'));
									}
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'EvnXml_id'
							}, [
								{ mapping: 'EvnXml_id', name: 'EvnXml_id', type: 'int' },
								{ mapping: 'EvnXml_Name', name: 'EvnXml_Name', type: 'string' }
							]),
							url: '/?c=ClinExWork&m=getEvnXmlList'
						})
					}, {
						xtype: 'swbaselocalcombo',
						triggerConfig: {
							tag:'span', cls:'x-form-twin-triggers', cn:[
							{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-select-trigger"},
							{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-plus-trigger"}
						]},
						initTrigger: cur_win.inT,
						store: new Ext.data.Store({
							autoLoad: false,
							reader: new Ext.data.JsonReader({
								id: 'PalliatQuestion_id'
							}, [
								{ mapping: 'PalliatQuestion_id', name: 'PalliatQuestion_id', type: 'int' },
								{ mapping: 'PalliatQuestion_Name', name: 'PalliatQuestion_Name', type: 'string' }
							]),
							url: '/?c=ClinExWork&m=getPalliatQuestionList'
						}),
						allowBlank: true,
						enableKeyEvents: true,
						mode: 'local',
						anchor: '100%',
						fieldLabel: 'Анкета',
						hiddenName: 'PalliatQuestion_id',
						valueField: 'PalliatQuestion_id',
						displayField: 'PalliatQuestion_Name',
						triggerAction: 'all',
						onTrigger1Click: function() {
							this.focus(true);
							if(this.getStore().getCount()>0) {
								( this.isExpanded() ) ? this.collapse() : this.expand();
							}
						},
						onTrigger2Click: function() {
							var b_f = this.CommonForm.getForm(),
								f = b_f.findField('PalliatQuestion_id'),
								Person_id = b_f.findField('Person_id').getValue();
							sw.Promed.PersonOnkoProfile.openEditWindow('add', {
								Person_id: Person_id,
								ReportType: 'palliat'
							}, function(c, PalliatQuestion_id) {
								f.getStore().load({
									callback: function() {
										if (f.getStore().getCount() > 0 && PalliatQuestion_id) {
											f.setValue(PalliatQuestion_id);
											f.fireEvent('change', f, f.getValue());
										}
									}
								});
							});
						}.createDelegate(this)
					}]
				}]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'EvnPrescrVK_id'},
				{name: 'EvnPrescrVK_pid'},
				{name: 'Person_id'},
				{name: 'PersonEvn_id'},
				{name: 'Server_id'},
				{name: 'MedService_id'},
				{name: 'Lpu_id'},
				{name: 'Lpu_gid'},
				{name: 'TimetableMedService_id'},
				{name: 'CauseTreatmentType_id'},
				{name: 'Diag_id'},
				{name: 'EvnPrescrMse_id'},
				{name: 'EvnDirectionHTM_id'},
				{name: 'EvnStick_id'},
				{name: 'EvnPrescrVK_LVN'},
				{name: 'EvnPrescrVK_Note'},
				{name: 'EvnXml_id'},
				{name: 'Person_id'},
				{name: 'PalliatQuestion_id'}
			])
		});
	
		Ext.apply(this,	{
			layout: 'fit',
			items: [this.CommonForm, cur_win.FilePanel = new Ext.Panel({
				hidden: !getRegionNick().inlist(['perm', 'vologda']),
				border: true,
				collapsible: true,
				autoHeight: true,
				titleCollapse: true,
				animCollapse: false,
				title: 'Электронные документы',
				items: [
					cur_win.FileUploadPanel
				]
			}), cur_win.EvnStatusHistoryGrid = new sw.Promed.ViewFrame({
				title: 'Причины возврата врачу',
				hidden: !getRegionNick().inlist(['perm', 'vologda']),
				dataUrl: '/?c=Mse&m=loadEvnPrescrVKStatusGrid',
				uniqueId: true,
				border: false,
				height: 200,
				autoLoadData: false,
				useEmptyRecord: false,
				onLoadData: function() {
					if (cur_win.EvnStatusHistoryGrid.getGrid().getStore().getCount() > 0) {
						cur_win.EvnStatusHistoryGrid.show();
						cur_win.syncShadow();
					}
				},
				stringfields: [
					{name: 'EvnStatusHistory_id', type: 'int', header: 'ID', key: true},
					{name: 'EvnStatusHistory_begDate', type: 'date', header: langs('Дата'), width: 100},
					{name: 'EvnStatus_Name', type: 'string', header: langs('Статус'), width: 100},
					{name: 'EvnStatusHistory_Cause', type: 'string', header: langs('Причина'), id: 'autoexpand'},
					{name: 'pmUser_Name', type: 'string', header: langs('Зав. отделением'), width: 200}
				],
				actions: [
					{name:'action_add', hidden: true, disabled: true},
					{name:'action_edit', hidden: true, disabled: true},
					{name:'action_view', hidden: true, disabled: true},
					{name:'action_delete', hidden: true, disabled: true},
					{name:'action_refresh', hidden: true, disabled: true},
					{name:'action_print'}
				]
			})]
		});
		sw.Promed.swEvnPrescrVKWindow.superclass.initComponent.apply(this, arguments);
	}
});