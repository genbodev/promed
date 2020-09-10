/**
* swErmpExportSelectWindow - окно выбора типа выгрузки ФРМП
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Alexander Chebukin
* @version      05.2012
* @comment      
*/

/*NO PARSE JSON*/

sw.Promed.swErmpExportSelectWindow = Ext.extend(sw.Promed.BaseForm,
{
	autoHeight: true,
	objectName: 'swErmpExportSelectWindow',
	objectSrc: '/jscore/Forms/Common/swErmpExportSelectWindow.js',
	title:lang['parametryi_vyigruzki_frmp'],
	layout: 'border',
	id: 'EESW',
	modal: true,
	shim: false,
	resizable: false,
	maximizable: false,
	listeners:
	{
		hide: function()
		{
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	width: 390,
	show: function()
	{
		sw.Promed.swErmpExportSelectWindow.superclass.show.apply(this, arguments);
		var Lpu_all = new Ext.data.Record({
			Lpu_id: 100500,
			Lpu_Name: lang['vse'],
			Lpu_Nick: lang['vse']
		});
		this.findById('EESW_Lpu').getStore().removeAll();
		var that = this;
		this.findById('EESW_Lpu').getStore().load({
			params: {},
			callback: function() {
				that.findById('EESW_Lpu').getStore().insert(0,[Lpu_all]);
				that.findById('EESW_Lpu').getStore().commitChanges();
				that.findById('EESW_mainPanel').getForm().reset();
			}
		});
	},
	submit: function()
	{
		var form = this;
		var Lpu_id = form.findById('EESW_Lpu').getValue();
		var lpu_filter;
		if(Lpu_id == 100500){
			lpu_filter = '';
		}
		else{
			lpu_filter = '?lpuId='+Lpu_id;
		}
		var ExportPath = '';
		var ExportType = form.findById('ExportType_RdGroup').getValue();
		if(ExportType == 1) {
			ExportPath = 'ermp/servlets/ErmpExportServlet';
		}
		else {
			ExportPath = 'ermp/servlets/ErmpWsServlet';
		}
		if (form.findById('EESW_RdGroup').getValue()==1) {
			//window.open('ermp/servlets/ErmpExportServlet'+lpu_filter);
			window.open(ExportPath+lpu_filter);
			this.hide();
		} else {
			var date = form.findById('EESW_date').getValue();
			if (!date) {
				Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			} else {
				date = Ext.util.Format.date(date, "Ymd");
				var date_filter = '';
				if(Lpu_id == 100500){
					date_filter = '?fromDate=' + date;
				}
				else{
					date_filter = '&fromDate=' + date;
				}
				//window.open('ermp/servlets/ErmpExportServlet'+lpu_filter+date_filter);
				window.open(ExportPath+lpu_filter + date_filter);
				this.hide();
			}
		}
	},	
	initComponent: function()
	{
		var form = this;

		this.mainPanel = new sw.Promed.FormPanel(
		{
			region: 'center',
			layout: 'form',
			border: false,
			frame: true,
			style: 'padding: 10px;',
			labelWidth: 90,
			id: 'EESW_mainPanel',
			items: 
			[
				{
					id:'EESW_Lpu',
					width: 240,
					lastQuery: '',
					fieldLabel: lang['meditsinskaya_organizatsiya'],
					xtype: 'swlpucombo',
					value: '100500',
					disabled: false,
                    listeners: {
                        change: function(combo,newvalue){
                            if((newvalue)&&(newvalue!='100500')){
                                //Проверяем у выбранного ЛПУ параметр PasportMO_IsNoFRMP
                                Ext.Ajax.request({
                                    params: {
                                        Lpu_id: newvalue
                                    },
                                    success: function(response, options) {
                                        var response_obj = Ext.util.JSON.decode(response.responseText);
                                        if(response_obj.success == false){
                                            sw.swMsg.alert("Предупреждение",'Данные по выбранной МО не выгружаются, так как у данной организации в паспорте МО на вкладке "Справочная информация" установлен флаг "Не учитывать при выгрузке ФРМР"');
                                        }
                                    }.createDelegate(this),
                                    url: '/?c=LpuStructure&m=getIsNoFRMP'
                                });
                            }
                        }
                    }
				},
				{
				xtype: 'panel',
				layout: 'column',
				border: false,
				bodyStyle:'background:#DFE8F6;padding:5px;',
				items: [{// Левая часть 
					layout: 'form',
					border: false,
					width: 170,
					items: 
					[{
						id:'EESW_RdGroup',
						xtype: 'radiogroup',
						hideLabel: true,
						style : 'padding: 5px; padding-top:1px;',
						itemCls: 'x-radio-group-alt',
						columns: [150, 250],
						items: [
							{boxLabel: lang['vse_dannyie'], name: 'ErmpType', inputValue: 1, checked: true},
							{boxLabel: lang['izmenennyie_posle_datyi'], name: 'ErmpType', inputValue: 2}
						],
						listeners: {
							change: function(box, value) {
								form.findById('EESW_date').setDisabled((value.inputValue == 1) ? true : false);
								form.findById('EESW_date').focus((value.inputValue == 1) ? true : false);
							}
						},
						getValue: function() {
							var out = [];
							this.items.each(function(item){
								if(item.checked){
									out.push(item.inputValue);
								}
							});
							return out.join(',');
						}
					},
						{
							id:'ExportType_RdGroup',
							xtype: 'radiogroup',
							hideLabel: true,
							style : 'padding: 5px; padding-top:1px;',
							itemCls: 'x-radio-group-alt',
							columns: [250, 250],
							items: [
								{boxLabel: lang['vyigruzit_v_xml_fayl'], name: 'ExportType', inputValue: 1, checked: true},
								{boxLabel: lang['zapustit_servis'], name: 'ExportType', inputValue: 2}
							],
							getValue: function() {
								var out = [];
								this.items.each(function(item){
									if(item.checked){
										out.push(item.inputValue);
									}
								});
								return out.join(',');
							}
						}]
				}, {// Правая часть 
					layout: 'form',
					border: false,
					width: 100,
					bodyStyle:'padding-top: 29px;',
					items: 
					[{
						xtype: 'swdatefield',
						disabled: true,
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
						hideLabel: true,
						format: 'd.m.Y',
						id: 'EESW_date'
					}]
				}]
			}]
		});
		
		Ext.apply(this,
		{
			region: 'center',
			layout: 'form',
			buttons:
			[{
				text: lang['vyibrat'],
				id: 'lsqefOk',
				iconCls: 'ok16',
				handler: function() {
					this.ownerCt.submit();
				}
			},{
				text: '-'
			}, 
			//HelpButton(this, -1), 
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() {this.hide();}.createDelegate(this)
			}],
			items:
			[
				form.mainPanel
			]
			
		});
		sw.Promed.swErmpExportSelectWindow.superclass.initComponent.apply(this, arguments);
	}
});
