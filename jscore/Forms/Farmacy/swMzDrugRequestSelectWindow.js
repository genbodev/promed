/**
* swMzDrugRequestSelectWindow - окно установки фильтров для списка заявок врачей
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Salakhov R.
* @version      01.2013
* @comment      
*/
sw.Promed.swMzDrugRequestSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['vyibor_zayavki'],
	layout: 'border',
	id: 'MzDrugRequestSelectWindow',
	modal: true,
	shim: false,
	width: 800,
	height: 125,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSelect:  function() {
		var wnd = this;

		if (!wnd.form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.base_form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var store = null;
		var params = new Object();
		var msf = sw.Promed.MedStaffFactByUser.current;
		var groups = getGlobalOptions().groups.split('|');

		params.MoRequest_Data = new Object();

		params.Lpu_id = getGlobalOptions().lpu_id > 0 ? getGlobalOptions().lpu_id : null;

		if (this.ARMType == "leadermo") {
			params.LpuUnit_id = null;
		} else {
			params.LpuUnit_id = !Ext.isEmpty(msf.LpuUnit_id) ? msf.LpuUnit_id : null;
		}

		if (this.ARMType == "leadermo" || "100".inlist(groups)) { //100 - Руководитель подразделения МО.
			params.LpuSection_id = null;
		} else {
			params.LpuSection_id = !Ext.isEmpty(msf.LpuSection_id) ? msf.LpuSection_id : null;
		}

		if (this.ARMType == "leadermo" || "100".inlist(groups) || "101".inlist(groups)) { //100 - Руководитель подразделения МО; 101 - Руководитель отделения.
			params.MedPersonal_id = null;
		} else {
			params.MedPersonal_id = getGlobalOptions().medpersonal_id > 0 ? getGlobalOptions().medpersonal_id : null;
		}

		var combo = wnd.form.findField('RegionDrugRequest_id');
		var idx = combo.getStore().findBy(function(rec) { return rec.get('DrugRequest_id') == combo.getValue(); });
		if (idx > -1) {
			var record = combo.getStore().getAt(idx);
			params.RegionDrugRequest_id = record.get('DrugRequest_id');
			params.RegionDrugRequest_Name = record.get('DrugRequest_Name');
			params.DrugRequestPeriod_id = record.get('DrugRequestPeriod_id');
			params.DrugRequestPeriod_Name = record.get('DrugRequestPeriod_Name');
			params.PersonRegisterType_id = record.get('PersonRegisterType_id');
			params.PersonRegisterType_Name = record.get('PersonRegisterType_Name');
			params.DrugRequestKind_id = record.get('DrugRequestKind_id');
			params.DrugGroup_id = record.get('DrugGroup_id');
			params.DrugRequest_Version = record.get('DrugRequest_Version');
			params.FirstCopy_Inf = record.get('FirstCopy_Inf');
			params.ARMType = this.ARMType;
		} else {
			return;
		}

		Ext.Ajax.request({
			params:{
				DrugRequest_id: params.RegionDrugRequest_id
			},
			success: function (response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (!Ext.isEmpty(result)) {
                    if (result.DrugRequestStatus_Code == 8) { // 8 - Выполняется операция обработки
                        sw.swMsg.alert(langs('Ошибка'), langs('Доступ к заявке приостановлен на период обработки данных. Повторите попытку позднее.'));
					} else {
                        if (result.DrugRequestStatus_Code != 1) {
                            params.action = 'view';
                        }
                        getWnd('swMzDrugRequestViewWindow').show(params);
                        wnd.hide();
                    }
				}
			},
			failure:function () {
				sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные о статусе заявки'));
			},
			url:'/?c=MzDrugRequest&m=getDrugRequestStatus'
		});

		return true;		
	},	
	show: function() {
		sw.Promed.swMzDrugRequestSelectWindow.superclass.show.apply(this, arguments);

		this.ARMType = null;

		if (arguments[0] && !Ext.isEmpty(arguments[0].ARMType)) {
			this.ARMType = arguments[0].ARMType;
		}

		this.form.reset();
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		this.form.findField('RegionDrugRequest_id').getStore().load({
			params: {
				mode: 'with_user_mo',
                show_first_copy: 1
			},
			callback: function() {
				loadMask.hide();
			}
		});
	},
	initComponent: function() {
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			height: 110,
			border: false,			
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'MzDrugRequestSelectForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 50,
				collapsible: true,
				items: [{
					fieldLabel: lang['zayavka'],
					hiddenName: 'RegionDrugRequest_id',
					xtype: 'swbaselocalcombo',
					valueField: 'DrugRequest_id',
					displayField: 'DrugRequest_FullName',
					allowBlank: false,
					editable: false,
					lastQuery: '',
					validateOnBlur: true,
					anchor: '100%',
					store: new Ext.data.Store({
						autoLoad: false,
						reader: new Ext.data.JsonReader({
							id: 'DrugRequest_id'
						}, [
							{name: 'DrugRequest_id', mapping: 'DrugRequest_id'},
							{name: 'DrugRequest_Name', mapping: 'DrugRequest_Name'},
							{name: 'DrugRequest_FullName', mapping: 'DrugRequest_FullName'},
							{name: 'DrugRequestPeriod_id', mapping: 'DrugRequestPeriod_id'},
							{name: 'DrugRequestPeriod_Name', mapping: 'DrugRequestPeriod_Name'},
							{name: 'PersonRegisterType_id', mapping: 'PersonRegisterType_id'},
							{name: 'PersonRegisterType_Name', mapping: 'PersonRegisterType_Name'},
							{name: 'DrugRequestKind_id', mapping: 'DrugRequestKind_id'},
							{name: 'DrugGroup_id', mapping: 'DrugGroup_id'},
							{name: 'DrugRequest_Version', mapping: 'DrugRequest_Version'},
							{name: 'FirstCopy_Inf', mapping: 'FirstCopy_Inf'},
							{name: 'DrugRequestStatus_Code', mapping: 'DrugRequestStatus_Code'}
						]),
						url: '/?c=MzDrugRequest&m=loadRegionDrugRequestCombo'
					}),
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<table style="border: 0;"><tr><td>{DrugRequest_FullName}</td></tr></table>',
						'</div></tpl>'
					)
				}]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() 
				{
					this.ownerCt.doSelect();
				},
				iconCls: 'save16',
				text: lang['otkryit']
			}, 
			{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		sw.Promed.swMzDrugRequestSelectWindow.superclass.initComponent.apply(this, arguments);
		this.base_form = this.findById('MzDrugRequestSelectForm');
		this.form = this.base_form.getForm();
	}
});