/**
* swReagentConsumptionCalculationWindow - окно учета расхода реактивов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Salakhov R.
* @version      11.2013
* @comment      
*/
sw.Promed.swReagentConsumptionCalculationWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['uchet_rashoda_reaktivov'],
	layout: 'border',
	id: 'ReagentConsumptionCalculationWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doRemove:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('ReagentConsumptionCalculationForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		//var loadMask = new Ext.LoadMask(this.getEl(), {msg:'Загрузка...'});
		//loadMask.show();

		var params = new Object();
		params.UslugaListJSON = wnd.DataGrid.getJSONData();
		params.LpuSection_id = getGlobalOptions().CurLpuSection_id;
		params.Date = wnd.form.findField('Date').getValue().dateFormat('Y-m-d');

		//Открываем окно выбора параметров
		getWnd('swReagentConsumptionParamsSelectWindow').show({
			onSelect: function(selected_params) {
				Ext.apply(params, selected_params);
				Ext.Ajax.request({
					url: '/?c=Farmacy&m=createDocumentForReagentConsumption',
					params: params,
					callback: function(options, success, response) {
						// loadMask.hide();
						if (success) {
							//открываем документ учета
							if (response && response.responseText) {
								var response_data = Ext.util.JSON.decode(response.responseText);
								if (response_data.DocumentUc_id > 0) {
									getWnd('swDokSpisEditWindow').show({
										DocumentUc_id: response_data.DocumentUc_id,
										action: 'edit'
									});
								}
							}
						} else {
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_sozdanii_dokumenta_ucheta'], function() {
								wnd.form.findField('Date').focus(true);
							});
						}
					}
				});
			}
		});
		return true;		
	},	
	show: function() {
        var wnd = this;
		sw.Promed.swReagentConsumptionCalculationWindow.superclass.show.apply(this, arguments);

		var curr_date = new Date();

		if (!arguments[0] || !arguments[0].MedService_id) {
			alert(lang['ne_ukazanyi_vhodnyie_dannyie']);
			return false;
		}

		this.MedService_id = arguments[0].MedService_id;

		this.form.reset();
		this.form.findField('Date').setValue(curr_date);

		var pay_combo = this.form.findField('PayType_id');
		pay_combo.getStore().each(function(record) {
			if (record.get('PayType_id') > 0) {
				pay_combo.setValue(record.get('PayType_id'));
				return false;
			}
		});

		this.buttons[0].disable();
		wnd.DataGrid.setReadOnly();
        /*var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		loadMask.hide();*/
	},
	initComponent: function() {
		var wnd = this;		
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			height: 105,
			border: false,			
			frame: true,
			region: 'north',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'ReagentConsumptionCalculationForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 80,
				collapsible: true,
				items: [{
					xtype: 'swdatefield',
					fieldLabel: lang['data'],
					name: 'Date',
					format: 'd.m.Y',
					allowBlank: false
				}, {
					xtype: 'swpaytypecombo',
					fieldLabel: lang['vid_oplatyi'],
					width: 240
				}, {
					xtype: 'button',
					text: lang['podschitat_kolichestvo_uslug'],
					handler: function() {
						var params = wnd.form.getValues();
						params.MedService_id = wnd.MedService_id;
						wnd.DataGrid.loadData({
							params: params,
							globalFilters: params,
							callback: function() {
								var button_disable = true;
								wnd.DataGrid.getGrid().getStore().each(function(record) {
									if (record.get('UslugaComplex_id') > 0) {
										button_disable = false;
										return false;
									}
								});
								if (button_disable) {
									wnd.buttons[0].disable();
								} else {
									wnd.buttons[0].enable();
								}
							}
						});
					}
				}]
			}]
		});

		wnd.DataGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=EvnUsluga&m=getReagentCountByDate',
			region: 'center',
			object: 'WhsDocumentOrderAllocation',
			id: 'WhsDocumentOrderAllocationDrugGrid',
			paging: false,
			saveAtOnce:false,
			style: 'margin-bottom: 0px',
			stringfields: [
				{name: 'UslugaComplex_id', header: 'ID', key: true},
				{name: 'PayType_id', type: 'int', hidden: true},
				{name: 'PayType_Name', type: 'string', header: lang['vid_oplatyi'], width: 200},
				{name: 'UslugaComplex_Name', type: 'string', header: lang['usluga'], id: 'autoexpand'},
				{name: 'Kolvo', type: 'string', header: lang['kolichestvo'], width: 100}
			],
			title: lang['spisok_okazannyih_uslug'],
			//toolbar: false,
            actions: [
                { name: 'action_add', hidden: true },
                { name: 'action_edit', hidden: true },
                { name: 'action_view', hidden: true },
                { name: 'action_delete', hidden: true },
                { name: 'action_refresh', hidden: true },
                { name: 'action_print'}
            ],
			contextmenu: false,
			getJSONData: function(){ //возвращает записи в виде закодированной JSON строки
				var data = new Array();
				this.getGrid().getStore().each(function(record) {
					if (record.get('UslugaComplex_id') > 0 && record.get('Kolvo') > 0) {
						data.push({
							UslugaComplex_id: record.get('UslugaComplex_id'),
							Kolvo: record.get('Kolvo')
						});
					}
				});
				return data.length > 0 ? Ext.util.JSON.encode(data) : "";
			}
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() {
					this.ownerCt.doRemove();
				},
				iconCls: 'ok16',
				text: lang['spisat']
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
			items:[
				form,
				wnd.DataGrid
			]
		});
		sw.Promed.swReagentConsumptionCalculationWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('ReagentConsumptionCalculationForm').getForm();
	}
});