/**
* swEvnDirectionListWindow - окно журнал направлений на госпитализацию.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009-2012 Swan Ltd.
* @author       Ivan Petukhov aka Lich (ethereallich@gmail.com)
* @version      13.06.2012
* @comment      Префикс для id компонентов EDLW (EvnDirectionListWindow)
**/
/*NO PARSE JSON*/
sw.Promed.swEvnDirectionListWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnDirectionListWindow',
	objectSrc: '/jscore/Forms/Hospital/swEvnDirectionListWindow.js',
	
	title: lang['jurnal_napravleniy'],
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	layout: 'border',
	maximized: true,
	minHeight: 400,
	minWidth: 700,
	modal: true,
	plain: true,
	id: 'EvnDirectionListWindow',
	
	show: function() {
		sw.Promed.swEvnDirectionListWindow.superclass.show.apply(this, arguments);
		
		this.getCurrentDateTime();
		//this.dateMenu.focus(true);
	},
	
	currentDay: function ()
	{
		var date1 = Date.parseDate(this.curDate, 'd.m.Y');
		var date2 = Date.parseDate(this.curDate, 'd.m.Y');
		this.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		// this.dateMenu.fireEvent("select", this.dateMenu);
	},
	getCurrentDateTime: function() 
	{
		var frm = this;
		frm.getLoadMask(LOAD_WAIT).show();
		Ext.Ajax.request(
		{
			url: C_LOAD_CURTIME,
			callback: function(opt, success, response) 
			{
				frm.getLoadMask().hide();
				if (success && response.responseText != '')
				{
					var result  = Ext.util.JSON.decode(response.responseText);
					frm.curDate = result.begDate;
					frm.curTime = result.begTime;
					frm.userName = result.pmUser_Name;
					frm.doReset();
					//frm.dateMenu.focus(true);
				}
			}
		});
	},
	doSearch: function() {
		this.loadGridWithFilter(false);
	},
	doReset: function() {
		this.findById('EDLW_Person_Fio').setValue('');
		this.findById('EDLW_Person_BirthDay').setValue('');
		this.findById('EDLW_DirType_id').setValue(null);
		this.findById('EDLW_LpuSectionProfile_id').setValue(null);
		this.findById('EDLW_PrehospStatus_id').setValue(null);
		this.currentDay();
		this.loadGridWithFilter(true);
	},
	loadGridWithFilter: function(clear) {
		var grid = this.EvnDirectionGrid;
		grid.removeAll();
		var beg_date = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		var end_date = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		if (clear)
		{
			grid.loadData({
				globalFilters: {
					limit: 100,
					start: 0,
					beg_date: beg_date,
					end_date: end_date,
					Person_Fio: null,
					Person_BirthDay: null,
					DirType_id: null,
					LpuSectionProfile_id: null
				}
			});
		}
		else
		{
			var Person_Fio = this.findById('EDLW_Person_Fio').getValue() || null;
			var Person_BirthDay = this.findById('EDLW_Person_BirthDay').getValue() || null;
			var DirType_id = this.findById('EDLW_DirType_id').getValue() || null;
			var LpuSectionProfile_id = this.findById('EDLW_LpuSectionProfile_id').getValue() || null;
			var PrehospStatus_id = this.findById('EDLW_PrehospStatus_id').getValue() || null;
			grid.loadData({
				globalFilters: {
					limit: 100,
					start: 0,
					beg_date: beg_date,
					end_date: end_date,
					Person_Fio: Person_Fio,
					Person_BirthDay: Person_BirthDay,
					DirType_id: DirType_id,
					LpuSectionProfile_id: LpuSectionProfile_id,
					PrehospStatus_id: PrehospStatus_id
				}
			});
		}
	},
	getSelectedRecord: function()
	{
		var record = this.EvnDirectionGrid.getGrid().getSelectionModel().getSelected();
		if (!record || !record.data.EvnDirection_id)
		{
			Ext.Msg.alert(lang['oshibka'], lang['oshibka_vyibora_zapisi']);
			return false;
		}
		return record;
	},
	openForm: function(name,title,params)
	{
		if (getWnd(name).isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['forma']+title+lang['v_dannyiy_moment_otkryita']);
			return false;
		}
		else
		{
			getWnd(name).show(params);
		}
	},
	openEvnDirectionEditWindow: function()
	{
		var record = this.getSelectedRecord();
		if (record == false) return false;
		var params = {
			action: 'view',
			formParams: new Object(),
			EvnDirection_id: record.data.EvnDirection_id,
			Person_id: record.data.Person_id
		};
		this.openForm('swEvnDirectionEditWindow',lang['prosmotr_elektronnogo_napravleniya'],params);
	},

	addEvnDirection: function()
	{
		var view_frame = this.EvnDirectionGrid;
		this.openForm('swPersonSearchWindow',lang['poisk_cheloveka'],{
			onSelect: function(person_data) {
				getWnd('swPersonSearchWindow').hide();
				person_data.action = 'add';
				person_data.formParams = person_data;
				person_data.formParams.timetable = 'TimetableStac';
				person_data.formParams.Lpu_did = getGlobalOptions().lpu_id;
				person_data.formParams.LpuSectionProfile_id = this.userMedStaffFact.LpuSectionProfile_id;
				person_data.formParams.MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;
				person_data.formParams.MedPersonal_id = getGlobalOptions().medpersonal_id;
				person_data.formParams.DirType_id = 5;
				person_data.callback = function(data){
					view_frame.ViewGridStore.reload({callback: function(l,o,s){
						var grid = view_frame.getGrid();
						for(var i=0;i<l.length;i++)
						{
							if(l[i].get('EvnDirection_id') == data.evnDirectionData.EvnDirection_id)
							{
								grid.getView().focusRow(i);
								grid.getSelectionModel().selectRow(i);
								break;
							}
						}
					}});
				}.createDelegate(this);
				this.openForm('swEvnDirectionEditWindow',lang['dobavlenie_elektronnogo_napravleniya'],person_data);
			}.createDelegate(this),
			searchMode: 'all'
		});
	},
	addEvnPS: function(record)
	{
		var params = new Object();
		params.action = 'add';
		params.LpuSection_id = this.userMedStaffFact.LpuSection_id;
		params.MedPersonal_id = getGlobalOptions().medpersonal_id;
		if (record)
		{
			//создает КВС, заполняет данными из направления. После сохранения КВС направлению присваивается признак «Создана КВС на основании направления  № …= да» > обновление списка.
			params.callback = function(data) {
				if ( !data || !data.evnPSData ) {
					return false;
				}
				record.data.IsHospitalized = true;
				record.commit();
			};
			params.Person_id = record.get('Person_id');
			params.PersonEvn_id = record.get('PersonEvn_id');
			params.Server_id = record.get('Server_id');
			params.EvnDirection = record;
			this.openForm('swEvnPSEditWindow',lang['dobavlenie_kvs'],params);
		}
		else
		{
			//открывает форму поиска человека > создает новую пустую карту выбывшего из стационара.
			this.openForm('swPersonSearchWindow',lang['poisk_cheloveka'],{
				onSelect: function(person_data) {
					params.Person_id = person_data.Person_id;
					params.PersonEvn_id = person_data.PersonEvn_id;
					params.Server_id = person_data.Server_id;
					this.openForm('swEvnPSEditWindow',lang['dobavlenie_kvs'],params);
				}.createDelegate(this),
				searchMode: 'all'
			});
		}
	},

	initComponent: function() {
		var current_window = this;

		this.dateMenu = new Ext.form.DateRangeField(
		{
			width: 170,
			fieldLabel: lang['datyi_napravleniy'],
			tabIndex: TABINDEX_DIRLIST+11,
			hideLabel: true,
			plugins: 
			[
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			],
			listeners: 
			{
				/*'keydown': function (inp, e) 
				{
					if (e.getKey() == Ext.EventObject.ENTER)
					{
						e.stopEvent();
						
						alert(Ext.util.Format.date(this.getValue1(), 'd.m.Y'));
						current_window.doSearch();
					}
				},*/
				'select': function () 
				{
					current_window.doSearch();
				}
			}
		});
		
		this.TopPanel = new Ext.Panel(
		{
			region: 'north',
			frame: true,
			border: false,
			height: 145,
			items: 
			[{
				xtype: 'form',
				labelAlign: 'right',
				items: 
				[{
					xtype: 'fieldset',
					height: 60,
					title: lang['napravlenie'],
					layout: 'column',
					items:[{ 
						layout: 'form',
						items: 
						[ 
							//Даты направлений
							current_window.dateMenu
						]
					}, 
					{
						layout: 'form',
						style: "padding-left: 5px",
						items: 
						[{
							
							xtype: 'button',
							id: 'EDLW_Today',
							tabIndex: TABINDEX_DIRLIST+12,
							text: lang['segodnya'],
							handler: function()
							{
								current_window.currentDay();
								current_window.doSearch();
							}
						}]
					},
					{
						layout: 'form',
						style: 'padding-left: 5px',
						items: 
						[{
							width: 250,
							hideLabel: true,
							lastQuery: '',
							emptyText: lang['tip_napravleniya'],
							id: 'EDLW_DirType_id',
							tabIndex: TABINDEX_DIRLIST+14,
							xtype: 'swdirtypecombo'
						}]
					},
					{
						layout: 'form',
						style: 'padding-left: 5px',
						items:
						[{
							width: 250,
							listWidth: 300,
							hideLabel: true,
							emptyText: lang['profil'],
							tabIndex: TABINDEX_DIRLIST+16,
							id: 'EDLW_LpuSectionProfile_id',
							xtype: 'swlpusectionprofilecombo'
						}]
					}]
				},
				{
					xtype: 'fieldset',
					height: 60,
					title: lang['patsient'],
					layout: 'column',
					items: 
					[{
						layout: 'form',
						items: 
						[{
							xtype: 'textfieldpmw',
							width: 300,
							id: 'EDLW_Person_Fio',
							tabIndex: TABINDEX_DIRLIST+18,
							hideLabel: true,
							emptyText: lang['familiya_imya_otchestvo'],
							listeners: 
							{
								'keydown': function (inp, e) 
								{
									if (e.getKey() == Ext.EventObject.ENTER)
									{
										current_window.doSearch();
									}
								}
							}
						}]
					}, 
					{
						layout: 'form',
						items:
						[{
							xtype: 'swdatefield',
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							id: 'EDLW_Person_BirthDay',
							tabIndex: TABINDEX_DIRLIST+20,
							fieldLabel: lang['data_rojdeniya'],
							listeners: 
							{
								'keydown': function (inp, e) 
								{
									if (e.getKey() == Ext.EventObject.ENTER)
									{
										current_window.doSearch();
									}
								}
							}
						}]
					},
					{
						layout: 'form',
						labelWidth: 140,
						style: 'padding-left: 5px',
						hidden: (this.userMedStaffFact.ARMType != 'stacpriem'),
						items:
						[{
							width: 200,
							hideLabel: true,
							emptyText: lang['prehosp_status'],
							tabIndex: TABINDEX_DIRLIST+22,
							id: 'EDLW_PrehospStatus_id',
							xtype: 'swprehospstatuscombo'
						}]
					}]
				},
				{
					layout: 'column',
					items: 
					[{
						layout: 'form',
						labelWidth: 180,
						items: 
						[
						]
					}, 
					{
						layout: 'form',
						labelWidth: 150,
						items: 
						[
						]
					}]
				}]
			}]
		});

		this.EvnDirectionGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', text: lang['sozdat'], tooltip: lang['sozdat_napravlenie'], handler: function() { this.addEvnDirection(); }.createDelegate(this) },
				{ name: 'action_view', text: lang['otkryit'], tooltip: lang['prosmotret_elektronnoe_napravlenie'], handler: function() { this.openEvnDirectionEditWindow(); }.createDelegate(this) },
				{ name: 'action_DirHospitalize', text: lang['gospitalizirovat_po_napravleniyu'], handler: function() { this.DirHospitalize(); }.createDelegate(this) },
				{ name: 'action_edit', hidden: true, disabled: true },
				{ name: 'action_delete', hidden: true, disabled: true },
				{ name: 'action_cancel', text: lang['otmenit'], hidden: true, disabled: true},
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			stringfields: [
				{ name: 'EvnDirection_id', type: 'int', header: 'ID', key: true },
				{ name: 'Lpu_id', type: 'int', hidden: true },
				{ name: 'Lpu_Name', type: 'string', header: lang['napravivshee_lpu'], width: 150 },
				{ name: 'Org_Nick', type: 'string', hidden: true },
				{ name: 'Org_Name', type: 'string', hidden: true },
				{ name: 'Org_id', type: 'int', hidden: true },
				{ name: 'EvnDirection_Num', type: 'string', header: lang['nomer'], width: 50 },
				{ name: 'DirType_id', type: 'int', hidden: true },
				{ name: 'DirType_Name', type: 'string', header: lang['tip_napravleniya'], width: 150 },
				{ name: 'DLpu_Nick', type: 'string', header: lang['lpu_napravleniya'], width: 100 },
				{ name: 'LpuSectionProfile_id', type: 'int', hidden: true },
				{ name: 'LpuSectionProfile_Name', type: 'string', header: lang['profil'], width: 100 },
				{ name: 'EvnDirection_setDateTime', type: 'date', header: lang['data_napr'], width: 70, renderer: Ext.util.Format.dateRenderer('d.m.Y')},
				{ name: 'Person_id', type: 'int', hidden: true},
				{ name: 'PersonEvn_id', type: 'int', hidden: true},
				{ name: 'Server_id', type: 'int', hidden: true},
				{ name: 'RecMP', type: 'string', header: lang['vrach_otdelenie_slujba'], autoExpandMin: 100 },
				{ name: 'RecDate', type: 'datetime', header: lang['vremya_zapisi'], autoExpandMin: 50 },
				{ name: 'PrehospStatus_id', type: 'int', hidden: true },
				{ name: 'PrehospStatus_Name', type: 'string', header: lang['prehosp_status'], width: 120, hidden: (this.userMedStaffFact.ARMType != 'stacpriem') },
				{ name: 'Person_Fio', type: 'string', header: lang['fio_patsienta'], autoexpand: true, autoExpandMin: 150 },
				{ name: 'Person_Birthday', type: 'date', header: lang['data_rojdeniya'], width: 70},
				{ name: 'Diag_id', type: 'int', hidden: true },
				{ name: 'Diag_Name', type: 'string', header: lang['diagnoz'], width: 120 },
				{ name: 'EvnDirection_Descr', type: 'string', header: lang['obosnovanie'], width: 120 },
				{ name: 'MedPersonal_id', type: 'int', hidden: true},
				{ name: 'MedPersonal_Fio', type: 'string', header: lang['vrach'], width: 120 }
			],
			autoLoadData: false,
			border: false,
			id: 'EDLW_EvnDirectionGrid',
			dataUrl: '/?c=EvnDirection&m=getEvnDirectionList',
			object: 'EvnDirection',
			layout: 'fit',
			root: 'data',
			totalProperty: 'totalCount',
			paging: true,
			region: 'center',
			toolbar: true,
			onLoadData: function() {
				
			},
			onDblClick: function() {
				this.openEvnDirectionEditWindow();
			}.createDelegate(this),
			onEnter: function() {
				this.openEvnDirectionEditWindow();
			}.createDelegate(this)
		});

		Ext.apply(this, {
			buttons: [
			{
				xtype: 'button',
				id: 'EDLW_BtnSearch',
				tabIndex: TABINDEX_DIRLIST+26,
				text: lang['nayti'],
				iconCls: 'search16',
				handler: function()
				{
					current_window.doSearch();
				}
			},
			{
				xtype: 'button',
				id: 'EDLW_BtnClear',
				tabIndex: TABINDEX_DIRLIST+28,
				text: lang['sbros'],
				iconCls: 'resetsearch16',
				handler: function()
				{
					current_window.doReset();
				}
			},
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'EDLW_CloseButton',
				tabIndex: TABINDEX_DIRLIST+50,
				onTabAction: function() {
					this.dateMenu.focus(true);
				}.createDelegate(this),
				text: BTN_FRMCLOSE
			}],
			items: [ 
				this.TopPanel,
				this.EvnDirectionGrid
			]
		});
		sw.Promed.swEvnDirectionListWindow.superclass.initComponent.apply(this, arguments);
	}
});
