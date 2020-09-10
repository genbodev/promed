/**
 * swElectronicTreatmentEditWindow - повод обращения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Admin
 * @access      public
 * @copyright	Copyright (c) 2017 Swan Ltd.
 * @author      Bykov Stanislav
 * @version     08.2017
 */
sw.Promed.swElectronicTreatmentEditWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	height: 600,
	id: 'swElectronicTreatmentEditWindow',
	layout: 'border',
	maximizable: false,
	maximized: true,
	resizable: true,
	title: 'Повод обращения',
	width: 900,

	/* методы */
	clearGridFilter: function(grid) { //очищаем фильтры (необходимо делать всегда перед редактированием store)
		grid.getGrid().getStore().clearFilter();
	},
	deleteGridRecord: function(){
		var wnd = this,
			view_frame = this.ElectronicTreatmentLinkGrid,
			grid = view_frame.getGrid(),
			selected_record = grid.getSelectionModel().getSelected();

		sw.swMsg.show({
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_hotite_udalit_zapis'],
			title: lang['podtverjdenie'],
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {

				if ('yes' == buttonId) {
					if (selected_record.get('state') == 'add') {
						grid.getStore().remove(selected_record);
					} else {
						selected_record.set('state', 'delete');
						selected_record.commit();
						wnd.setGridFilter(view_frame);
					}
				} else {
					if (grid.getStore().getCount()>0) {
						grid.getView().focusRow(0);
					}
				}
			}
		});
	},
	doSave: function(options) {
		if (typeof options != 'object') {
			options = new Object();
		}

		var wnd = this,
			base_form = this.FormPanel.getForm(),
			loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});

		if ( !base_form.isValid() ) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.FormPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = {
			ElectronicTreatment_pid: base_form.findField('ElectronicTreatment_pid').getValue(),
			queueData: wnd.ElectronicTreatmentLinkGrid.getJSONChangedData()
		};

		base_form.findField('ElectronicTreatment_isConfirmPage').setValue(base_form.findField('ElectronicTreatment_isConfirmPageCheckbox').getValue() == true ? 2 : 1);
		base_form.findField('ElectronicTreatment_isFIOShown').setValue(base_form.findField('ElectronicTreatment_isFIOShownCheckbox').getValue() == true ? 2 : 1);

		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				loadMask.hide();
			},
			params: params,
			success: function(result_form, action) {
				loadMask.hide();

				if ( action.result ) {
					wnd.hide();
					wnd.callback();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], 'При сохранении возникли ошибки');
				}
			}.createDelegate(this)
		});

		return true;
	},
	onGridRowSelect: function(grid) {
		grid.setActionDisabled('action_delete', this.action == 'view');
	},
	openElectronicTreatmentLinkEditWindow: function(action) {
		var wnd = this,
			grid = this.ElectronicTreatmentLinkGrid.getGrid(),
			base_form = this.FormPanel.getForm();

		var params = {
			action: action,
			Lpu_id: base_form.findField('Lpu_id').getValue()
		};

		if ( !params.Lpu_id ) {
			log('Не указан Lpu_id');
			return false;
		}

		var view_frame = wnd.ElectronicTreatmentLinkGrid,
			store = view_frame.getGrid().getStore();

		if ( action == 'add' ) {
			params.callback = function(data) {
				var record_count = store.getCount();

				if ( record_count == 1 && !store.getAt(0).get('ElectronicTreatmentLink_id') ) {
					view_frame.removeAll({
						addEmptyRecord: false
					});
				}

				var index = store.findBy(function(rec) {
					return (rec.get('ElectronicQueueInfo_id') == data.ElectronicQueueInfo_id);
				});

				if ( index >= 0 ) {
					sw.swMsg.alert(lang['oshibka'], 'На один повод обращения нельзя назначить одну и ту же Электронную очередь несколько раз');
					return false;
				}
				
				var record = new Ext.data.Record.create(view_frame.jsonData['store']);
				wnd.clearGridFilter(view_frame);

				data.ElectronicTreatmentLink_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
				data.state = 'add';

				store.insert(record_count, new record(data));
				wnd.setGridFilter(view_frame);

				return true;
			};

			params.LpuBuilding_id = wnd.TreatmentGroup_LpuBuilding_id;

			getWnd('swElectronicTreatmentLinkEditWindow').show(params);
		}
	},
	setGridFilter: function(grid) { //скрывает удаленные записи
		grid.getGrid().getStore().filterBy(function(record){
			return (record.get('state') != 'delete');
		});
	},
	show: function() {
		sw.Promed.swElectronicTreatmentEditWindow.superclass.show.apply(this, arguments);

		var wnd = this,
			base_form = this.FormPanel.getForm(),
			grid = this.ElectronicTreatmentLinkGrid,
			loadMask = new Ext.LoadMask(
				wnd.getEl(),{
					msg: LOAD_WAIT
				}
			);

		wnd.action = null;
		this.TreatmentGroup_LpuBuilding_id = null;
		this.LpuBuilding_id = null;
		
		wnd.callback = Ext.emptyFn;
		wnd.mode = 'SuperAdmin';

		if ( !arguments[0] || typeof arguments[0].formParams != 'object' ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				title: lang['oshibka'],
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				fn: function() {
					wnd.hide();
				}
			});
		}

		var args = arguments[0];
		base_form.reset();

		if(args.formParams.LpuBuilding_id) {
			this.TreatmentGroup_LpuBuilding_id = args.formParams.LpuBuilding_id;
		}

		grid.getGrid().getStore().baseParams = {};
		grid.getGrid().getStore().removeAll();

		wnd.setTitle("Повод обращения");

		for ( var field_name in args ) {
			log(field_name +':'+ args[field_name]);
			wnd[field_name] = args[field_name];
		}

		grid.setActionDisabled('action_add', wnd.action == 'view');

		base_form.setValues(args.formParams);

		loadMask.show();

		base_form.findField('ElectronicTreatment_pid').getStore().load({
			callback: function() {
				loadMask.hide();

				switch ( wnd.action ) {
					case 'add':
						wnd.setTitle(wnd.title + ": Добавление");
						wnd.enableEdit(true);
						break;

					case 'edit':
					case 'view':
						wnd.setTitle(wnd.title + (wnd.action == "edit" ? ": Редактирование" : ": Просмотр"));
						wnd.enableEdit(wnd.action == "edit");

						base_form.findField('ElectronicTreatment_pid').setValue(base_form.findField('ElectronicTreatment_pid').getValue());

						base_form.findField('ElectronicTreatment_isConfirmPageCheckbox').setValue(base_form.findField('ElectronicTreatment_isConfirmPage').getValue() == 2);
						base_form.findField('ElectronicTreatment_isFIOShownCheckbox').setValue(base_form.findField('ElectronicTreatment_isFIOShown').getValue() == 2);

						grid.loadData({
							globalFilters: {
								ElectronicTreatment_id: base_form.findField('ElectronicTreatment_id').getValue(),
								start: 0,
								limit: 100
							}
						});
						break;
				}

				base_form.findField('ElectronicTreatment_pid').disable();
				base_form.findField('ElectronicTreatment_Code').focus(true, 100);
			},
			params: {
				Lpu_id: base_form.findField('Lpu_id').getValue()
			}
		})
	},

	/* конструктор */
	initComponent: function() {
		var wnd = this;

		this.FormPanel = new Ext.FormPanel({
			autoHeight: true,
			border: false,
			frame: true,
			items: [{
				name: 'ElectronicTreatment_id',
				xtype: 'hidden'
			}, {
				name: 'ElectronicTreatmentLevel_id',
				value: 2,
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				name: 'ElectronicTreatment_isConfirmPage',
				xtype: 'hidden'
			}, {
				name: 'ElectronicTreatment_isFIOShown',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				displayField: 'ElectronicTreatment_Name',
				fieldLabel: 'Группа повода обращения',
				hiddenName: 'ElectronicTreatment_pid',
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{ name: 'ElectronicTreatment_id', mapping: 'ElectronicTreatment_id' },
						{ name: 'ElectronicTreatment_Name', mapping: 'ElectronicTreatment_Name' },
						{ name: 'ElectronicTreatment_Code', mapping: 'ElectronicTreatment_Code' }
					],
					key: 'ElectronicTreatment_id',
					sortInfo: { field: 'ElectronicTreatment_Name' },
					url:'/?c=ElectronicTreatment&m=loadElectronicTreatmentGroupCombo'
				}),
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<font color="red">{ElectronicTreatment_Code}</font>&nbsp;{ElectronicTreatment_Name}',
					'</div></tpl>'
				),
				valueField: 'ElectronicTreatment_id',
				width: 350,
				xtype: 'swbaselocalcombo'
			}, {
				allowBlank: false,
				autoCreate: {
					autocomplete: "off",
					maxLength: 70,
					tag: "input"
				},
				fieldLabel: 'Код',
				name: 'ElectronicTreatment_Code',
				width: 350,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				autoCreate: {
					autocomplete: "off",
					maxLength: 70,
					tag: "input"
				},
				fieldLabel: 'Наименование',
				name: 'ElectronicTreatment_Name',
				width: 350,
				xtype: 'textfield'
			}, {
				autoCreate: {
					autocomplete: "off",
					maxLength: 70,
					tag: "input"
				},
				fieldLabel: 'Примечание',
				name: 'ElectronicTreatment_Descr',
				width: 350,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				fieldLabel: 'Дата начала',
				format: 'd.m.Y',
				name: 'ElectronicTreatment_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				width: 100,
				xtype: 'swdatefield'
			}, {
				fieldLabel: 'Дата окончания',
				format: 'd.m.Y',
				name: 'ElectronicTreatment_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				width: 100,
				xtype: 'swdatefield'
			}, {
				fieldLabel: 'Пропускать страницу подтверждения',
				name: 'ElectronicTreatment_isConfirmPageCheckbox',
				xtype: 'checkbox'
			}, {
				fieldLabel: 'Отображать ФИО врача в талоне',
				name: 'ElectronicTreatment_isFIOShownCheckbox',
				xtype: 'checkbox'
			}],
			labelAlign: 'right',
			layout: 'form',
			labelWidth: 250,
			region: 'north',
			reader: new Ext.data.JsonReader( {
				success: Ext.emptyFn
			}, [
				{ name: 'ElectronicTreatment_id' },
				{ name: 'ElectronicTreatment_pid' },
				{ name: 'ElectronicTreatmentLevel_id' },
				{ name: 'Lpu_id' },
				{ name: 'ElectronicTreatment_Code' },
				{ name: 'ElectronicTreatment_Name' },
				{ name: 'ElectronicTreatment_Descr' },
				{ name: 'ElectronicTreatment_begDate' },
				{ name: 'ElectronicTreatment_endDate' },
				{ name: 'ElectronicTreatment_isConfirmPage' },
				{ name: 'ElectronicTreatment_isFIOShown' }
			]),
			url: '/?c=ElectronicTreatment&m=save'
		});

		this.ElectronicTreatmentLinkGrid = new sw.Promed.ViewFrame({
			id: 'ElectronicTreatmentLinkGrid',
			title: 'Электронные очереди',
			object: 'ElectronicTreatmentLink',
			dataUrl: '/?c=ElectronicTreatment&m=loadElectronicTreatmentQueues',
			autoLoadData: false,
			paging: true,
			totalProperty: 'totalCount',
			region: 'center',
			toolbar: true,
			useEmptyRecord: false,
			stringfields: [
				{name: 'ElectronicTreatmentLink_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'ElectronicQueueInfo_Code', header: 'Код', width: 100},
				{name: 'ElectronicQueueInfo_Name', header: 'Наименование', width: 150, id: 'autoexpand'},
				{name: 'ElectronicQueueInfo_id', header: 'Идентификатор очереди', hidden: true},
				{name: 'LpuBuilding_Name', header: 'Подразделение', width: 200, },
				{name: 'LpuSection_Name', header: 'Отделение', width: 200,},
				{name: 'MedService_Name', header: 'Служба', width: 200},
				{name: 'ElectronicQueueInfo_begDate', header: 'Дата начала', type: 'date', width: 120},
				{name: 'ElectronicQueueInfo_endDate', header: 'Дата окончания', type: 'date', width: 120}
			],
			actions: [
				{name:'action_add', handler: function() { wnd.openElectronicTreatmentLinkEditWindow('add'); }},
				{name:'action_edit',  disabled: true, hidden: true},
				{name:'action_view',  disabled: true, hidden: true},
				{name:'action_delete', handler: function() { wnd.deleteGridRecord() }},
				{name:'action_print', disabled: true, hidden: true},
				{name:'action_refresh', disabled: true, hidden: true}
			],
			onRowSelect: function(sm, rowIdx, record) {
				wnd.onGridRowSelect(this);
			},
			getChangedData: function(){ //возвращает новые и измненные показатели
				var data = new Array();
				wnd.clearGridFilter(this);
				this.getGrid().getStore().each(function(record) {
					if ( record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete' ) {
						data.push(record.data);
					}
				});
				wnd.setGridFilter(this);
				return data;
			},
			getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
				var dataObj = this.getChangedData();
				return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
			},
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					wnd.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'close16',
				handler: function() {
					wnd.hide();
				},
				text: BTN_FRMCLOSE
			}],
			items: [
				wnd.FormPanel,
				wnd.ElectronicTreatmentLinkGrid
			]
		});

		sw.Promed.swElectronicTreatmentEditWindow.superclass.initComponent.apply(this, arguments);
	}
});