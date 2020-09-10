/**
 * swPersonCardAttachListWindow - окно "Список заявлений о выборе МО"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			26.11.2015
 */
/*NO PARSE JSON*/

sw.Promed.swPersonCardAttachListWindow = Ext.extend(sw.Promed.BaseForm, {
	height: 500,
	width: 800,
	id: 'swPersonCardAttachListWindow',
	title: lang['spisok_zayavleniy_o_vyibore_mo'],
	maximizable: true,
	maximized: true,
	modal: false,
	plain: true,
	resizable: false,
	show: function()
	{
		sw.Promed.swPersonCardAttachListWindow.superclass.show.apply(this, arguments);

		var base_form = this.FiltersPanel.getForm();
		var grid = this.GridPanel.getGrid();

		base_form.reset();

		var date = new Date().format('d.m.Y');
		base_form.findField('PersonCardAttach_setDate_Range').setValue(date+' - '+date);
		base_form.findField('Lpu_aid').setValue(getGlobalOptions().lpu_id);

		if (arguments[0] && arguments[0].filterParams) {
			 base_form.setValues(arguments[0].filterParams);
		}

		if (!this.GridPanel.getAction('action_changestatus')) {
			this.GridPanel.addActions({
				disabled: false,
				name: 'action_changestatus',
				text: 'Изменить статус',
				handler: function() {
					this.changeStatus();
				}.createDelegate(this)
			});
		}
		if (!this.GridPanel.getAction('action_addPersonCard')) {
			this.GridPanel.addActions({
				disabled: false,
				name: 'action_addPersonCard',
				text: 'Создать прикрепление',
				handler: function() {
					this.addPersonCard();
				}.createDelegate(this)
			});
		}

		this.doSearch();
		this.PersonCardAttachChecked = new Array();
	},

	doSearch: function(reset, callback) {
		var grid = this.GridPanel.getGrid();
		var base_form = this.FiltersPanel.getForm();

		if (reset) {
			base_form.reset();

			var date = new Date().format('d.m.Y');
			base_form.findField('PersonCardAttach_setDate_Range').setValue(date+' - '+date);
		}

		grid.getStore().baseParams = base_form.getValues();
		grid.getStore().baseParams.Lpu_aid = base_form.findField('Lpu_aid').getValue();
		grid.getStore().load({callback: callback || Ext.emptyFn});
	},

	changeStatus: function(){
		var that = this;
		var grid_panel = this.GridPanel;
		if (that.PersonCardAttachChecked.length < 1) {
			sw.swMsg.alert('Ошибка', 'Необходимо выбрать заявления');
			return false;
		}
		var Person_ids_array = new Array();
		PersonCardAttach_ids_array = this.PersonCardAttachChecked;
		getWnd('swPersonCardAttachChangeStatusWindow').show({
            callback: function(answer) {
                var params = {
					PersonCardAttachStatusType_id: answer.PersonCardAttachStatusType_id,
                    PersonCardAttach_ids_array: Ext.util.JSON.encode(PersonCardAttach_ids_array)
                };
                Ext.Ajax.request({
                    params: params,
                    url: '?c=PersonCard&m=changePersonCardAttachStatus',
                    callback: function(options,success,response){
                        var response_obj = Ext.util.JSON.decode(response.responseText);
                        if(response_obj.length > 0){
                        	var result_string = '';
                        	for (var i=0; i< response_obj.length; i++){
                        		if(!Ext.isEmpty(response_obj[i]))
                        			result_string += response_obj[i] + '<br>';
                        	}
                        	if(result_string!='')
                        		sw.swMsg.alert('', result_string);
                        }
						grid_panel.getAction('action_refresh').execute();
                    }

                });
            }
        });
	},
	addPersonCard: function(){
		var that = this;
		var grid_panel = this.GridPanel;
		if (that.PersonCardAttachChecked.length < 1) {
			sw.swMsg.alert('Ошибка', 'Необходимо выбрать заявления');
			return false;
		}
		var Person_ids_array = new Array();
		PersonCardAttach_ids_array = this.PersonCardAttachChecked;
		var params = {
			PersonCardAttach_ids_array: Ext.util.JSON.encode(PersonCardAttach_ids_array)
		};
		Ext.Ajax.request({
			params: params,
			url: '?c=PersonCard&m=savePersonCardByAttach',
			callback: function (options,success,response){
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if(response_obj.length > 0){
                        	var result_string = '';
                        	for (var i=0; i< response_obj.length; i++){
                        		if(!Ext.isEmpty(response_obj[i]))
                        			result_string += response_obj[i] + '<br>';
                        	}
                        	sw.swMsg.alert('', result_string);
                        }
				grid_panel.getAction('action_refresh').execute();
			}
		});

	},
	openPersonCardAttachEditWindow: function(action) {
		if (!action.inlist(['add','edit','view'])) {
			return false;
		}

		var base_form = this.FiltersPanel.getForm();
		var grid_panel = this.GridPanel;
		var grid = grid_panel.getGrid();
		var that = this;
		var params = {};
		params.action = action;
		params.formParams = {};

		params.callback = function() {
			grid_panel.getAction('action_refresh').execute();
		};

		if (action == 'add') {
			if ( getWnd('swPersonSearchWindow').isVisible() ) {
				sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
				return false;
			}

			getWnd('swPersonSearchWindow').show({
				onClose: Ext.emptyFn,
				onSelect: function(person_data) {
					getWnd('swPersonSearchWindow').hide();

					params.formParams.Person_id = person_data.Person_id;
					var ajaxparams = {
						Person_id: person_data.Person_id,
						Lpu_id: getGlobalOptions().lpu_id
					};
					Ext.Ajax.request({
						params: ajaxparams,
						url: '?c=PersonCard&m=checkPersonCardActive',
						callback: function (options,success,response){
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if(!Ext.isEmpty(response_obj[0]) && !Ext.isEmpty(response_obj[0].PersonCard_id)){
								sw.swMsg.show({
									buttons:Ext.Msg.YESNO,
									fn:function (buttonId, text, obj) {
										if (buttonId == 'yes') {
											getWnd('swPersonCardAttachEditWindow').show(params);
										}
									}.createDelegate(that),
									icon:Ext.MessageBox.QUESTION,
									msg:'Внимание! Пациент ' + response_obj[0].Person_FIO + ' имеет основное прикрепление к участку ' + response_obj[0].LpuRegionType_Name + ' №' + response_obj[0].LpuRegion_Name + '. Продолжить добавление заявления?',
									title:lang['podtverjdenie']
								});
							}
							else
							{
								Ext.Ajax.request({
									params: ajaxparams,
									url: '?c=PersonCard&m=checkPersonCardDate',
									callback: function (options, success, response){
										var response_obj = Ext.util.JSON.decode(response.responseText);
										if(!Ext.isEmpty(response_obj[0]) && response_obj[0]==2)
										{
											sw.swMsg.show({
											buttons:Ext.Msg.YESNO,
											fn:function (buttonId, text, obj) {
												if (buttonId == 'yes') {
													getWnd('swPersonCardAttachEditWindow').show(params);
												}
											}.createDelegate(that),
											icon:Ext.MessageBox.QUESTION,
											msg:'Внимание! Пациент менял прикрепление в текущем году без смены адреса. Продолжить добавление заявления?',
											title:lang['podtverjdenie']
										});
										}
										else
											getWnd('swPersonCardAttachEditWindow').show(params);
									}
								});
							}
						}
					});
				},
				personFirname: base_form.findField('Person_FirName').getValue(),
				personSecname: base_form.findField('Person_SecName').getValue(),
				personSurname: base_form.findField('Person_SurName').getValue(),
				searchMode: 'all'
			});
		} else {
			var record = grid.getSelectionModel().getSelected();
			if (!record) {
				return false;
			}
			params.formParams.PersonCardAttach_id = record.get('PersonCardAttach_id');

			getWnd('swPersonCardAttachEditWindow').show(params);
		}
		return true;
	},

	deletePersonCardAttach: function() {
		var grid_panel = this.GridPanel;
		var grid = grid_panel.getGrid();

		var record = grid.getSelectionModel().getSelected();

		if (!record || !record.get('PersonCardAttach_id')) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var params = {PersonCardAttach_id: record.get('PersonCardAttach_id')};

					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (!response_obj.Error_Msg) {
								grid_panel.getAction('action_refresh').execute();
							}
						}.createDelegate(this),
						params: params,
						url: '/?c=PersonCard&m=deletePersonCardAttach'
					});
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		});
	},

	initComponent: function()
	{
		var wnd = this;
		this.FiltersPanel = new Ext.FormPanel({
			region: 'north',
			autoHeight: true,
			frame: true,
			keys: [{
				fn: function(inp, e) {
					var f = Ext.get(e.getTarget());
					this.doSearch(false, f.focus.createDelegate(f));
				},
				key: [ Ext.EventObject.ENTER ],
				scope: this,
				stopEvent: true
			}],
			items: [{
				xtype: 'fieldset',
				title: lang['filtr'],
				autoHeight: true,
				labelAlign: 'right',
				collapsible: true,
				listeners: {
					collapse: function(p) {
						p.doLayout();
						this.doLayout();
					}.createDelegate(this),
					expand: function(p) {
						p.doLayout();
						this.doLayout();
					}.createDelegate(this)
				},
				items: [{
					layout: 'column',
					items: [{
						layout: 'form',
						width: 300,
						labelWidth: 70,
						defaults: {
							anchor: '100%'
						},
						items: [{
							xtype: 'textfield',
							name: 'Person_SurName',
							fieldLabel: lang['familiya']
						}, {
							xtype: 'textfield',
							name: 'Person_FirName',
							fieldLabel: lang['imya']
						}, {
							xtype: 'textfield',
							name: 'Person_SecName',
							fieldLabel: lang['otchestvo']
						}]
					}, {
						layout: 'form',
						width: 400,
						labelWidth: 170,
						defaults: {
							anchor: '100%'
						},
						items: [{
							xtype: 'daterangefield',
							name: 'Person_BirthDay_Range',
							fieldLabel: lang['data_rojdeniya'],
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)]
						}, {
							xtype: 'daterangefield',
							name: 'PersonCardAttach_setDate_Range',
							fieldLabel: lang['period_podachi_zayavleniya'],
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)]
						}, {
							xtype: 'swlpucombo',
							hiddenName: 'Lpu_aid',
							disabled: !isSuperAdmin(),
							fieldLabel: lang['mo_prinyavshaya_zayavlenie'],
							listWidth: 400
						}]
					}, {
						layout: 'form',
						width: 400,
						labelWidth: 150,
						defaults: {
							anchor: '100%'
						},
						items: [{
							editable: false,
							xtype: 'swpersoncardattachstatustypecombo',
							hiddenName: 'PersonCardAttachStatusType_id',
							fieldLabel: lang['status_zayavleniya']
						}, {
							xtype: 'swrecmethodtypecombo',
							fieldLabel: langs('Источник записи')
						}]
					}]
				}]
			}]
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			region: 'center',
			id: this.id + '_GridPanel',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			pageSize: 100,
			border: false,
			actions: [
				{ name: 'action_add', handler: function() {this.openPersonCardAttachEditWindow('add')}.createDelegate(this)},
				{ name: 'action_edit', handler: function(){this.openPersonCardAttachEditWindow('edit')}.createDelegate(this)},
				{ name: 'action_view', handler: function(){this.openPersonCardAttachEditWindow('view')}.createDelegate(this)},
				{ name: 'action_delete', handler: function(){this.deletePersonCardAttach()}.createDelegate(this)},
				{ name: 'action_refresh'},
				{ name: 'action_print', menuConfig: {
					printPersonCardAttach: {text: lang['pechat_zayavleniya_o_vyibore_mo'], handler: function(){
						var record = this.GridPanel.getGrid().getSelectionModel().getSelected();

						if (!record || Ext.isEmpty(record.get('PersonCardAttach_id'))) {
							return false;
						}
						var Person_id = record.get('Person_id');
						var Lpu_id = record.get('Lpu_id');
						printBirt({
							'Report_FileName': 'ApplicationForAttachment.rptdesign',
							'Report_Params': '&paramPerson_id=' + Person_id + '&paramDeputy=2&paramLpu='+Lpu_id,
							'Report_Format': 'pdf'
						});

					}.createDelegate(this)} 
				}}
			],
			autoLoadData: false,
			stripeRows: true,
			root: 'data',
			onLoadData: function() {
				var base_form = Ext.getCmp('swPersonCardAttachListWindow');
				var records = new Array();
				wnd.GridPanel.getGrid().getStore().each(function (rec){
					if (!Ext.isEmpty(rec.get('PersonCardAttach_id'))) {
						var index = wnd.PersonCardAttachChecked.indexOf(rec.get('PersonCardAttach_id'));
						if (index > -1) {
							rec.set('Is_Checked', 1);
						}
					}
				});
			},
			stringfields: [
				{
					name: 'check', 
					sortable: false, 
					width: 40, 
					renderer: this.checkRenderer,
					header: '<input type="checkbox" id="PCALW_checkAll" onClick="getWnd(\'swPersonCardAttachListWindow\').checkAll(this.checked);">'
				},
				{name: 'Is_Checked',type: 'int', header: 'is_checked', hidden: true},
				{name: 'PersonCardAttach_id', type: 'int', hidden: true, key: true },
				{name: 'PersonCardAttachStatusType_id', type: 'int', hidden: true},
				{name: 'PersonCardAttachStatusType_Code', type: 'int', hidden: true},
				{name: 'PersonCardAttach_setDate', header: lang['data_zayavleniya'], width: 110, type:'date'},
				{name: 'Person_FIO', header: lang['fio_patsienta'], width: 300 },
				{name: 'Lpu_id', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'Lpu_Nick', type: 'string', header: lang['mo_prinyavshaya_zayavlenie'], width: 200,},
				{name: 'PersonCardAttachStatusType_Name', type: 'string', header: lang['status_zayavleniya'], width: 200},
				{name: 'LpuRegionType_Name', header: lang['tip_uchastka'], width: 100},
				{name: 'LpuRegion_Name', header: 'Участок', width: 100},
				{name: 'MSF_FIO', header: lang['vrach'], width: 300 },
				{name: 'HasPersonCard', header: 'Прикрепление', type: 'checkbox' },
				{name: 'RecMethodType_Name', header: langs('Источник записи'), width: 200 }
			],
			paging: true,
			dataUrl: '/?c=PersonCard&m=loadPersonCardAttachGrid',
			totalProperty: 'totalCount',
			onRowSelect: function(sm, index, record) {
				var status = record.get('PersonCardAttachStatusType_Code');
				var HasPersonCard = (record.get('HasPersonCard')=='true');

				this.setActionDisabled('action_edit',HasPersonCard);
				this.setActionDisabled('action_delete',HasPersonCard);
				/*if(!HasPersonCard)
				{
					this.setActionDisabled('action_addPersonCard',(!(status==2 || status==3)));
				}
				else {
					this.setActionDisabled('action_addPersonCard',true);
				}*/
			},
			onDblClick: function() {
				this.getAction('action_view').execute();
			},
			onEnter: function() {
				this.getAction('action_view').execute();
			}
		});

		Ext.apply(this, {
			layout: 'border',
			items: [this.FiltersPanel, this.GridPanel],
			buttons: [{
				handler: function() {
					this.doSearch(false);
				}.createDelegate(this),
				iconCls: 'search16',
				id: 'PCALW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.PersonCardAttachChecked = new Array();
					this.doSearch(true);
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				id: 'PCALW_ResetButton',
				text: BTN_FRMRESET
			},
			'-',
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function() {
					ShowHelp(this.title);
				}.createDelegate(this)
			}, {
				text: BTN_FRMCLOSE,
				tabIndex: -1,
				tooltip: lang['zakryit'],
				iconCls: 'cancel16',
				handler: this.hide.createDelegate(this, [])
			}]
		});
		sw.Promed.swPersonCardAttachListWindow.superclass.initComponent.apply(this, arguments);
	},
	checkRenderer: function(v, p, record) {
		var id = record.get('PersonCardAttach_id');
		var value = 'value="'+id+'"';
		var checked = record.get('Is_Checked')!=0 ? ' checked="checked"' : '';
		var onclick = 'onClick="getWnd(\'swPersonCardAttachListWindow\').checkOne(this.value);"';

		return '<input type="checkbox" '+value+' '+checked+' '+onclick+'>';

	},
	checkAll: function(check)
	{
		var form = this;
		var array_index = -1;
		if(check)
			this.GridPanel.getGrid().getStore().each(function(record){
				record.set('Is_Checked', 1);
				array_index = form.PersonCardAttachChecked.indexOf(record.get('PersonCardAttach_id'));
				if(array_index == -1){
					form.PersonCardAttachChecked.push(record.get('PersonCardAttach_id'));
				}
			});
		else
			this.GridPanel.getGrid().getStore().each(function(record){
				record.set('Is_Checked', 0);
				array_index = form.PersonCardAttachChecked.indexOf(record.get('PersonCardAttach_id'));
				if(array_index > -1){
					form.PersonCardAttachChecked.splice(array_index, 1); //Убираем из массива отмеченных людей
				}
			});
	},
	checkOne: function(id){

		var form = this;
		var PersonCardAttach_id = id;
		var array_index = form.PersonCardAttachChecked.indexOf(PersonCardAttach_id);
		this.GridPanel.getGrid().getStore().each(function(record){
			if(record.get('PersonCardAttach_id') == PersonCardAttach_id){
				if(record.get('Is_Checked') == 0) //Было 0, т.е. при нажатии устанавливаем галочку
				{
					record.set('Is_Checked',1);
					if(array_index == -1){
						form.PersonCardAttachChecked.push(PersonCardAttach_id);
					}
				}
				else{ //Было 1, т.е. при нажатии снимаем галочку
					record.set('Is_Checked',0);
					if(array_index > -1){
						form.PersonCardAttachChecked.splice(array_index, 1); //Убираем из массива отмеченных людей
					}
				}
			}
		});
		log(form.PersonCardAttachChecked);
	},
});