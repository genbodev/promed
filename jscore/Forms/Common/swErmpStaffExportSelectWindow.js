/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 09.07.13
 * Time: 16:20
 * To change this template use File | Settings | File Templates.
 */

sw.Promed.swErmpStaffExportSelectWindow = Ext.extend(sw.Promed.BaseForm,
	{
		autoHeight: true,
		objectName: 'swErmpStaffExportSelectWindow',
		objectSrc: '/jscore/Forms/Common/swErmpStaffExportSelectWindow.js',
		title:lang['parametryi_vyigruzki_shtatnogo_raspisaniya_frmp'],
		layout: 'border',
		id: 'ESESW',
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

        getLoadMask: function()
        {
            if (!this.loadMask)
            {
                this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: lang['podojdite_idet_formirovanie'] });
            }
            return this.loadMask;
        },

		show: function()
		{
			sw.Promed.swErmpStaffExportSelectWindow.superclass.show.apply(this, arguments);
			var Lpu_all = new Ext.data.Record({
				Lpu_id: 100500,
				Lpu_Name: lang['vse'],
				Lpu_Nick: lang['vse']
			});

            var that = this;
            this.findById('ESESW_Lpu').getStore().load({
                callback: function() {

                    that.findById('ESESW_Lpu').getStore().insert(0,[Lpu_all]);
                    that.findById('ESESW_Lpu').getStore().commitChanges();
                    that.findById('ESESW_mainPanel').getForm().reset();

                    that.findById('ESESW_Lpu').getStore().filterBy(function(rec){ //Фильтруем лпу - оставляем только незакрытые
                        if(Ext.isEmpty(rec.get('Lpu_EndDate'))){
                            return true;
                        }

                        else
                            return false;
                    });
                }
            });

		},
		submit: function()
		{
			var form = this;
			var Lpu_id = form.findById('ESESW_Lpu').getValue();
			if (form.findById('ESESW_RdGroup').getValue()==2)
			{
				var ESESW_date = Ext.util.Format.date(form.findById('ESESW_date').getValue(),'d.m.Y');// form.findById('ESESW_date').getValue();
				if (!ESESW_date) {
					Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
					return false;
				}
			}
			this.disable();
			var ExportType = form.findById('ExportType_RdGroup').getValue();
            form.getLoadMask().show();
			if(ExportType == 1)
			{
				Ext.Ajax.request({
					url: '/?c=LpuStructure&m=ExportErmpStaff',
					params: {
						Lpu_id: Lpu_id,
						ESESW_date: ESESW_date
					},
					callback: function(options, success, response) {
                        form.getLoadMask().hide();
						var result = Ext.util.JSON.decode(response.responseText);
						if(result['success'] == true){
						var file = '<a target="_blank" href="' + result['link'] + '">' + lang['skachat_rezultat_vyigruzki_shtatnogo_raspisaniya'] + '</a>';
						Ext.Msg.alert(lang['zagruzka_fayla'], file);
						}
						else{
							alert(lang['proizoshla_oshibka_pri_formirovanii_xml-fayla']);
						}
						this.enable();
						this.hide();
					}.createDelegate(this)
				});
			}
			else
			{
				var lpu_filter;
				if(Lpu_id == 100500){
					lpu_filter = '';
				}
				else{
					lpu_filter = '?lpuId='+Lpu_id;
				}
				if (form.findById('ESESW_RdGroup').getValue()==1) {
					window.open('ermp/servlets/ErmpStaffServlet'+lpu_filter);
					this.enable();
					this.hide();
				}
				else {
					var date = form.findById('ESESW_date').getValue();
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
						window.open('ermp/servlets/ErmpStaffServlet'+lpu_filter+date_filter);
						this.enable();
						this.hide();
					}
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
					id: 'ESESW_mainPanel',
					items:
						[
							{
								id:'ESESW_Lpu',
								width: 240,
								lastQuery: '',
								fieldLabel: lang['meditsinskaya_organizatsiya'],
								xtype: 'swlpucombo',
								value: '100500',
								disabled: false,
                                listeners: {
                                    change: function(combo,newvalue){
                                        if((newvalue)&&(newvalue!='100500')){
											var loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: lang['proverka_vozmojnosti_vyigruzki_shtatnogo_raspisaniya_po_vyibrannoy_mo'] });
											loadMask.show();

                                            //Проверяем у выбранного ЛПУ параметр PasportMO_IsNoFRMP
                                            Ext.Ajax.request({
                                                params: {
                                                    Lpu_id: newvalue
                                                },
												failure: function() {
													loadMask.hide();
													sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_proverke_vozmojnosti_vyigruzki_shtatnogo_raspisaniya_po_vyibrannoy_mo']);
												},
                                                success: function(response, options) {
													loadMask.hide();

                                                    var response_obj = Ext.util.JSON.decode(response.responseText);
                                                    if(response_obj.success == false){
                                                        sw.swMsg.alert("Предупреждение",'Данные по выбранной МО не выгружаются, так как у данной организации в паспорте МО на вкладке "Справочная информация" установлен флаг "Не учитывать при выгрузке ФРМР"');
                                                    }
                                                }.createDelegate(this),
                                                url: '/?c=LpuStructure&m=getIsNoFRMP'
                                            });
                                        }
                                    }.createDelegate(this)
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
											id:'ESESW_RdGroup',
											xtype: 'radiogroup',
											hideLabel: true,
											style : 'padding: 5px; padding-top:1px;',
											itemCls: 'x-radio-group-alt',
											columns: 1,
											items: [
												{boxLabel: lang['vse_dannyie'], name: 'ErmpType', inputValue: 1, checked: true},
												{boxLabel: lang['izmenennyie_posle_datyi'], name: 'ErmpType', inputValue: 2}
											],
											listeners: {
												change: function(box, value) {
													form.findById('ESESW_date').setDisabled((value.inputValue == 1) ? true : false);
													form.findById('ESESW_date').focus((value.inputValue == 1) ? true : false);
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
												columns: 1,
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
											id: 'ESESW_date'
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
			sw.Promed.swErmpStaffExportSelectWindow.superclass.initComponent.apply(this, arguments);
		}
	});