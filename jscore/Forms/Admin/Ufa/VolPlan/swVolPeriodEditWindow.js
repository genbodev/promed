/**
* swDrugRequestPeriodEditWindow - окно редактирования "Справочник медикаментов: период заявки"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Dlo
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Rustam Salakhov
* @version      07.2012
* @comment      
*/
sw.Promed.swVolPeriodEditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Период фактических объемов: Добавление',
	layout: 'fit',
	id: 'PlanPeriodEditWindow',
	modal: true,
	shim: false,
	width: 550,
	height: 200,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	setDefaultValues: function() {
		var wnd = this;		
		Ext.Ajax.request({
			url: '/?c=VolPeriods&m=getVolPeriodMaxDate',
			callback: function(options, success, response) {
				if (success) {
					var result = Ext.util.JSON.decode(response.responseText);					
					if (result[0].max_date && result[0].max_date.split != '') {
						var date_arr = result[0].max_date.split('.');
						var start_date = new Date();
						start_date.setDate(date_arr[0]);
						start_date.setMonth(date_arr[1]-1);
						start_date.setYear(date_arr[2]);
						start_date = start_date.add(Date.DAY, 1).clearTime();
						
						var month = start_date.getMonth();
						month = month - (month%3);
                                                console.log('%', result);
						var kv = (month/3)+1;
						
						start_date.setDate(1);
						start_date.setMonth(month);
						
						wnd.form.setValues({
                                                        'VolPeriod_Name': 0,
							'VolPeriod_begDate': Ext.util.Format.date(start_date.clearTime(), 'd.m.Y'),
							'VolPeriod_endDate': Ext.util.Format.date(start_date.add(Date.MONTH, 3).add(Date.DAY, -1).clearTime(), 'd.m.Y'), 
							'VolPeriod_Name': kv+'-'+(kv == 1 ? 'ы' : '')+'й квартал '+start_date.getFullYear()+' года'
						});
					}
				}
			}
		});
//                Ext.Ajax.request({
//                        url: '/?c=VolPeriods&m=getNewId',
//                        callback: function(options, success, response) {
//                            var result = Ext.util.JSON.decode(response.responseText);
//                            var id = result[0].VolPeriod_id;
//                            console.log(id);
//                            wnd.form.setValues({
//							'VolPeriod_id': id
//						});
//                        }
//                       });
	},
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('drpeVolPeriodEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
                //savePeriod: function() {
                                var data = this.getData();
                                //console.log(data);
                                Ext.Ajax.request({
                                        url: '/?c=VolPeriods&m=saveVolPeriod',
                                        params: {
                                                VolPeriod_id: data.VolPeriod_id,
                                                VolPeriod_begDate: data.VolPeriod_begDate,
                                                VolPeriod_endDate: data.VolPeriod_endDate,
                                                VolPeriod_Name: data.VolPeriod_Name,
                                                Plan_year: data.Plan_year
                                        }
                                });   
                //};
                
                //this.submit();
                var mainWnd = getWnd('swVolPeriodViewWindow');
                mainWnd.reloadGrid();
                wnd.hide();
		return true;		
	},
        getData: function(){
				var dataObj = new Object();
//                                var id = new Object();
//                                Ext.Ajax.request({
//                                        url: '/?c=VolPeriods&m=getNewId',
//                                        callback: function(options, success, response) {
//                                            var result = Ext.util.JSON.decode(response.responseText);
//                                            id = result[0].VolPeriod_id;
//                                            console.log('show ' + id);
//                                            
//                                        }
//                                });
//                                dataObj.VolPeriod_id = id;
                                dataObj.VolPeriod_id = this.form.findField('VolPeriod_id').getValue();  
                                dataObj.VolPeriod_begDate = Ext.util.Format.date(this.form.findField('VolPeriod_begDate').getValue(), 'd.m.Y');
                                dataObj.VolPeriod_endDate = Ext.util.Format.date(this.form.findField('VolPeriod_endDate').getValue(), 'd.m.Y');
                                dataObj.VolPeriod_Name = this.form.findField('VolPeriod_Name').getValue();
                                dataObj.Plan_year = this.form.findField('plan_year').getValue();
                                //console.log(dataObj);
				return dataObj;
	},
	submit: function() {
		var wnd = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = new Object();
		params.action = wnd.action;
		params.VolPeriodJSON = wnd.getJSON();

		this.form.submit({
			params: params,
			failure: function(result_form, action)  {
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				loadMask.hide();
				wnd.callback(wnd.owner, action.result.VolPeriod_id);
				wnd.hide();
			}
		});
	},
	show: function() {
                var wnd = this;
		sw.Promed.swVolPeriodEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
                
		this.VolPeriod_id = 0;
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
		if ( arguments[0].VolPeriod_id ) {
			this.VolPeriod_id = arguments[0].VolPeriod_id;
		}

		this.form.reset();
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		switch (arguments[0].action) {
			case 'add':
				wnd.setTitle('Период фактических объемов: Добавление');
				wnd.setDefaultValues();
				loadMask.hide();
			break;
			case 'edit':
			case 'view':
				wnd.setTitle(wnd.action == 'edit' ? 'Период фактических объемов: Редактирование' : 'Период фактических объемов: просмотр');
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						wnd.hide();
					},
					params:{
						VolPeriod_id: wnd.VolPeriod_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) { return false}
						wnd.form.setValues(result[0]);

						loadMask.hide();
					},
					url:'/?c=VolPeriods&m=loadVolPeriodList'
				});
			break;	
		}
	},
	initComponent: function() {
		var wnd = this;
                
                this.yearMenu = new Ext.form.TextField({
			width: 50,
			fieldLabel: 'Год планирования',
			id: 'idplanyear',
                        name: 'plan_year',
			plugins: 
			[
                            new Ext.ux.InputTextMask('9999', false)
			],
                        value: (new Date().getFullYear() + 1)
		});

		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 0',
			border: false,
			frame: true,
			region: 'center',
			height: 150,
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				id: 'drpeVolPeriodEditForm',
				style: '',
				bodyStyle:'background:#DFE8F6;padding:4px;',
				border: true,
				labelWidth: 120,
				collapsible: true,
				url:'/?c=VolPeriods&m=saveVolPeriod',
				items: [{
					name: 'VolPeriod_id',
					xtype: 'hidden',
					value: 0
				}, 
                                this.yearMenu,
                                {					
					allowBlank: false,
					fieldLabel: lang['nachalo_perioda'],
					id: 'drpeVolPeriod_begDate',
					name: 'VolPeriod_begDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					width: 100,
					xtype: 'swdatefield'					
				}, {					
					allowBlank: false,
					fieldLabel: lang['konets_perioda'],
					id: 'drpeVolPeriod_endDate',
					name: 'VolPeriod_endDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					width: 100,
					xtype: 'swdatefield'					
				}, {
					fieldLabel: lang['naimenovanie'],
					name: 'VolPeriod_Name',
					allowBlank:false,
					maxLength: 30,
					xtype: 'textfield',
					anchor: '100%'
				}]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'VolPeriod_id'}, 
				{name: 'VolPeriod_begDate'}, 
				{name: 'VolPeriod_endDate'}, 
				{name: 'VolPeriod_Name'}
			]),
			url: '/?c=VolPeriods&m=saveVolPeriod'
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
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
				form
			]
		});
		sw.Promed.swVolPeriodEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('drpeVolPeriodEditForm').getForm();
	}	
});