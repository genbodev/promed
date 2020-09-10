/**
* swConsolidatedDrugRequestAddWindow - окно добавления сводной заявки
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov R.
* @version      11.2012
* @comment      
*/
sw.Promed.swConsolidatedDrugRequestAddWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['svodnaya_zayavka_dobavlenie'],
	layout: 'border',
	id: 'ConsolidatedDrugRequestAddWindow',
	modal: true,
	shim: false,
	width: 750,
	resizable: false,
	maximizable: false,
	maximized: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	loadGrid: function() {
		var wnd = this;
		var params = new Object();
		params.limit = 100;
		params.start =  0;
		
		wnd.SearchGrid.removeAll();
		wnd.SearchGrid.loadData({
			globalFilters: params,
			callback: function() {
				wnd.ConsolidatedDrugRequest_begDate = params.ConsolidatedDrugRequest_begDate;
				wnd.ConsolidatedDrugRequest_endDate = params.ConsolidatedDrugRequest_endDate;
			}
		});
	},
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					//wnd.form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		if (wnd.SearchGrid.getSelected().length < 1) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {},
				icon: Ext.Msg.WARNING,
				msg: lang['dlya_sozdaniya_svodnoy_zayavki_neobohodimo_vyibrat_hotya_byi_odnu_zayavku_llo'],
				title: lang['oshibka']
			});
			return false;
		}
        this.submit();
		return true;		
	},
	submit: function() {
		var wnd = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = new Object();
		params.action = 'add';
		params.SelectedRequest_List = wnd.SearchGrid.getSelected().join(',');
		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				var id = 0;
				if (action.result && action.result.DrugRequestProperty_id > 0) {
					id = action.result.DrugRequestProperty_id;
				}
				loadMask.hide();
				wnd.callback(wnd.owner, id);
				wnd.hide();
			}
		});
	},	
	show: function() {
        var wnd = this;
		sw.Promed.swConsolidatedDrugRequestAddWindow.superclass.show.apply(this, arguments);		
		this.action = 'add';
		this.callback = Ext.emptyFn;
		this.ConsolidatedDrugRequest_id = null;
        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}	
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].ConsolidatedDrugRequest_id ) {
			this.ConsolidatedDrugRequest_id = arguments[0].ConsolidatedDrugRequest_id;
		}
		this.form.reset();
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		wnd.loadGrid();
		loadMask.hide();
		
	},
	initComponent: function() {
		var wnd = this;		
		//swmultifieldpanel
		this.DatePanel = {
			xtype: 'swmultifieldpanel',
			label: lang['data_postavki'],
			createField: function() {
				var field = new Ext.form.DateField();
				return field;
			}
		};

		
		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', hidden: true},
				{name: 'action_delete', hidden: true},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MzDrugRequest&m=loadConsolidatedDrugRequestSourceList',
			height: 180,
			region: 'center',
			object: 'WhsDocumentRightRecipient',
			editformclassname: null,
			id: wnd.id + 'Grid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'DrugRequest_id', type: 'int', header: 'ID', key: true},
				{name: 'FinYear', hidden: true},
				{name: 'PersonRegisterType_id', hidden: true},
				{name: 'DrugGroup_id', hidden: true},
				{name: 'checked', header: lang['vyibrana'], width: 65, renderer: sw.Promed.Format.checkColumn},
				{name: 'DrugRequestStatus_Name', type: 'string', header: lang['status'], width: 120},
				{name: 'DrugRequest_Name', type: 'string', header: lang['naimenovanie'], id: 'autoexpand'},
				//{name: 'DrugRequest_SummFed', type: 'string', header: 'Фед. тыс. руб.', width: 120},
				//{name: 'DrugRequest_SummReg', type: 'string', header: 'Рег. тыс. руб.', width: 120},
				{name: 'DrugRequest_Summa', type: 'money', header: lang['summa'], width: 120},
				{name: 'DrugFinance_List', hidden: true},
				{name: 'PersonRegisterType_Name', type: 'string', header: lang['tip_registra_patsientov'], width: 120},
				{name: 'DrugGroup_Name', type: 'string', header: lang['gruppa_medikamentov'], width: 120}
			],
			title: lang['vklyuchit_v_svodnuyu_zayavku_medikamentyi_iz_zayavochnyih_kampaniy'],
			toolbar: true,
			onDblClick: function(grid) {
				var record = grid.getSelectionModel().getSelected();
                if (!record.get('checked') && this.checkAllowSelect(record)) {
                    if (this.getSelected().length == 0) {
                        wnd.form.findField('FinYear').setValue(record.get('FinYear'));
                        wnd.form.findField('PersonRegisterType_id').setValue(record.get('PersonRegisterType_id'));
                        wnd.form.findField('DrugGroup_id').setValue(record.get('DrugGroup_id'));
                    }
                    record.set('checked', true);
                } else {
                    record.set('checked', false);
                }
				record.commit();
			},
			getSelected: function() {
				var res = new Array();
				this.getGrid().getStore().each(function(item){
					if (item.get('checked')) {
						res.push(item.get('DrugRequest_id'));
                    }
				});
				return res;
			},
            checkAllowSelect: function(record) { //проверка на возможность выбора сводной заявки
                if (this.getSelected().length > 0) {
                    var exsits = false;
                    var df_list = new Array();
                    if (!Ext.isEmpty(record.get('DrugFinance_List'))) {
                        df_list = record.get('DrugFinance_List').split(', ');
                    }

                    this.getGrid().getStore().each(function(item){
                        if (item.get('checked')) {
                            if(item.get('PersonRegisterType_id') > 0 && item.get('PersonRegisterType_id') == record.get('PersonRegisterType_id')) {
                                if (df_list.length > 0 && !Ext.isEmpty(item.get('DrugFinance_List'))) {
                                    var df_arr = item.get('DrugFinance_List').split(', ');
                                    for(var i = 0; i < df_arr.length; i++) {
                                        if (df_list.indexOf(df_arr[i]) >= 0) {
                                            exsits = true;
                                            break;
                                        }
                                    }
                                }
                                if (Ext.isEmpty(item.get('DrugFinance_List')) && Ext.isEmpty(record.get('DrugFinance_List'))) {
                                    exsits = true;
                                }
                            }
                            if (exsits) {
                                return false;
                            }
                        }

                    });

                    if (exsits) {
                        Ext.Msg.alert(lang['oshibka'], 'Заявки выбранной заявочной  кампании не могут быть включены в выбранную сводную заявку, т.к. уже выбрана заявка по ЛЛО, имеющая такой же регистр и финансирование.');
                        return false;
                    }
                }
                return true;
            }
		});
		
		var form = new sw.Promed.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0;',
			border: false,			
			frame: true,
			labelAlign: 'right',
			labelWidth: 160,
			url:'/?c=MzDrugRequest&m=saveConsolidatedDrugRequest',
			items: [{
				xtype: 'textfield',
				name: 'DrugRequest_Name',
				fieldLabel: lang['naimenovanie_zayavki'],
				anchor: '100%',
				allowBlank: false
			}, {
                xtype: 'numberfield',
                name: 'FinYear',
                fieldLabel: 'Финансовый год',
                minValue: 1980,
                maxValue: 2300,
                plugins: [ new Ext.ux.InputTextMask('9999', false) ],
                allowDecimal: false,
                allowNegative: false,
                allowBlank: false
            },{
            	layout: 'form',
            	hidden: true,
            	items:[{
	                xtype: 'swcommonsprcombo',
	                name: 'PersonRegisterType_id',
	                fieldLabel: lang['tip_registra_patsientov'],
	                comboSubject: 'PersonRegisterType',
	                anchor: '50%',
	                allowBlank: true
	            }, {
	                xtype: 'swcommonsprcombo',
	                name: 'DrugGroup_id',
	                fieldLabel: lang['gruppa_medikamentov'],
	                comboSubject: 'DrugGroup',
	                anchor: '50%',
	                allowBlank: true
	            }]
            }]
		});
		
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function()  {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[{
				autoHeight: true,
				region: 'north',
				layout: 'form',				
				items:[form]
			},
			this.SearchGrid]
		});
		sw.Promed.swConsolidatedDrugRequestAddWindow.superclass.initComponent.apply(this, arguments);
		this.form = form.getForm();
	}	
});