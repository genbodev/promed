/**
* swUslugaComplexMedServiceEditWindow - редактирование услуги на службе
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Usluga
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Vlasenko aka DimICE (work@dimice.ru)
* @version      30.08.2012
* @comment      Префикс для id компонентов UCMSEW (UslugaComplexMedServiceEditWindow)
*
*
* Использует: -
*/
/*NO PARSE JSON*/

sw.Promed.swUslugaComplexMedServiceEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swUslugaComplexMedServiceEditWindow',
	objectSrc: '/jscore/Forms/Usluga/swUslugaComplexMedServiceEditWindow.js',

	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
		// options @Object

        if ( typeof options != 'object' ) {
            options = new Object();
        }
		
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		var data = new Object();
		var params = {};
		
		var UslugaCategory_Name = base_form.findField('UslugaCategory_id').getFieldValue('UslugaCategory_Name');
		
		var UslugaCategory_SysNick = base_form.findField('UslugaCategory_id').getFieldValue('UslugaCategory_SysNick');
		if (UslugaCategory_SysNick == 'lpu' && base_form.findField('UslugaComplex_id').getFieldValue('Lpu_Nick')) {
			UslugaCategory_Name = UslugaCategory_Name + ' - ' + base_form.findField('UslugaComplex_id').getFieldValue('Lpu_Nick');
		}
		
		if ( base_form.findField('UslugaComplex_id').disabled ) {
			params.UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
		}
		
		data.UslugaComplexMedServiceData = {
			'UslugaComplex_id': base_form.findField('UslugaComplex_id').getValue(),
			'UslugaComplexMedService_pid': base_form.findField('UslugaComplexMedService_pid').getValue(),
			'UslugaComplex_Code': base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Code'),
			'UslugaComplex_Name': base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Name'),
			'UslugaCategory_id': base_form.findField('UslugaCategory_id').getValue(),
			'UslugaCategory_Name': UslugaCategory_Name,
			'RecordStatus_Code': base_form.findField('RecordStatus_Code').getValue(),
			'pmUser_Name': getGlobalOptions().pmuser_name
		};
		
		// Получаем данные из грида связей для сохранения 
		params.ucrData = [];
		var rgrid = this.ResourceGrid.getGrid();
		if ( rgrid.getStore().getCount() > 0 ) {
			// todo: Здесь конечно можно выбрать только те которые изменились
			params.ucrData = Ext.util.JSON.encode(getStoreRecords(rgrid.getStore()));
		}
		
		log(data);
		
		switch ( this.formMode ) {
			case 'local':
				this.formStatus = 'edit';
				loadMask.hide();
				
				this.callback(data);
				this.hide();
			break;

			case 'remote':
				base_form.submit({
					failure: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result ) {
							if ( action.result.Error_Msg ) {
								sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
							}
						}
					}.createDelegate(this),
					params: params,
					success: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result && action.result.success ) {
							this.callback(data);
							this.hide();
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
						}
					}.createDelegate(this)
				});
			break;
		}
	},
	draggable: true,
	formStatus: 'edit',
	_filterUslugaComplexCombo(begDT, endDT){// #145267 фильтрует элементы в комбобоксе по дате
		var form = this;
		var base_form = form.FormPanel.getForm();
		var usluga_combo = base_form.findField('UslugaComplex_id');

		if(Ext.isEmpty(begDT)){
			begDT = null;
		}else if((typeof begDT) == 'string' && ! Ext.isEmpty(begDT)){
			begDT = Date.parseDate(Ext.util.Format.date(begDT, 'd.m.Y'), 'd.m.Y');
		}

		if(Ext.isEmpty(endDT)){
			endDT = null;
		}else if((typeof endDT) == 'string' && ! Ext.isEmpty(endDT)){
			endDT = Date.parseDate(Ext.util.Format.date(endDT, 'd.m.Y'), 'd.m.Y');
		}

		if(! begDT && ! endDT){
			usluga_combo.getStore().clearFilter();
			return;
		}

		usluga_combo.getStore().clearFilter(true);// suppressEvent = true
		usluga_combo.getStore().filterBy(function(record){
			var recordEndDate = Date.parseDate(record.get('UslugaComplex_endDT'), 'd.m.Y');
			if(recordEndDate && begDT && recordEndDate < begDT){
				return false
			}
			var recordBegDate = Date.parseDate(record.get('UslugaComplex_begDT'), 'd.m.Y');
			if(recordBegDate && endDT && recordBegDate > endDT){
				return false
			}
			return true;
		});
	},
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		}

		return this.loadMask;
	},
	id: 'UslugaComplexMedServiceEditWindow',
	initComponent: function() {
		var form = this;
		
		this.uslugaTree = new Ext.tree.TreePanel({
			title: lang['sostav_uslugi'],
			height: 150,
			autoWidth: true,
			autoScroll:true,
			animate:true,
			enableDD:true,
			containerScroll: true,
			rootVisible: false,
			autoLoad:false,
			frame: true,
			root: {
				nodeType: 'async'
			},
			cls: 'x-tree-noicon',
			loader: new Ext.tree.TreeLoader(
			{
				dataUrl:'/?c=UslugaComplex&m=loadUslugaComplexMedServiceTree',
				uiProviders: {'default': Ext.tree.TreeNodeUI, tristate: Ext.tree.TreeNodeTriStateUI},
				listeners:
				{
					load: function(p, node)
					{
						callback:
						{
							if (node.firstChild) {
								var firstChild = node.firstChild;
								node.getUI().toggleCheck(true);
								node.fireEvent('checkchange', firstChild, true);
							}
						}
					},
					beforeload: function (tl, node)
					{
						var base_form = form.FormPanel.getForm();
						
						tl.baseParams.level = node.getDepth();
						
						if (Ext.isEmpty(base_form.findField('UslugaComplexMedService_id').getValue()))
						{
							// отображаем общий состав
							tl.dataUrl = '/?c=UslugaComplex&m=loadUslugaContentsTree';
							tl.baseParams.UslugaComplex_pid = (base_form.findField('UslugaComplex_id').getValue()>0)?base_form.findField('UslugaComplex_id').getValue():'0';
													
							if (node.getDepth()!=0)
							{
								tl.baseParams.UslugaComplex_pid = node.attributes.object_value;
							}
						}
						else
						{
							// отображаем состав со службы
							tl.dataUrl = '/?c=UslugaComplex&m=loadUslugaComplexMedServiceTree';
							tl.baseParams.UslugaComplexMedService_pid = (base_form.findField('UslugaComplexMedService_id').getValue()>0)?base_form.findField('UslugaComplexMedService_id').getValue():'0';
													
							if (node.getDepth()!=0)
							{
								tl.baseParams.UslugaComplexMedService_pid = node.attributes.object_value;
							}
						}
					}.createDelegate(this)
				}
			}),
			changing: false
		});
		
		this.ResourceGrid = new sw.Promed.ViewFrame({
			id: 'ResourceGrid',
			region: 'center',
			object: 'Resource',
			height:200,
			dataUrl: '/?c=MedService&m=loadUslugaComplexResourceGrid',
			editformclassname: '',
			autoLoadData: false,
			saveAtOnce: false,
			stringfields: [
			{ name: 'Resource_id', key: true,type:'int',hidden:true, width: 100, isparams: true },
			{ name: 'isActive',header: lang['svyaz_da_net'], sortable: false, type: 'checkcolumnedit', isparams: true },
			{ name: 'UslugaComplexMedService_id', type:'int' ,hidden:true, isparams: true },
			{ name: 'UslugaComplexResource_id', hidden:true, type:'int', isparams: true},
			{ name: 'Resource_Name',  header: lang['resurs'], autoexpand: true },
			{ name: 'UslugaComplexResource_Time',  header: lang['planovaya_dlitelnost'],width: 100, editor: new Ext.form.NumberField(), isparams: true }/*,
			{ name: 'UslugaComplexResource_begDT', type: 'date', editor: new Ext.form.DateField(), header: lang['data_nachala'], width: 100},
			{ name: 'UslugaComplexResource_endDT',header: lang['data_okonchaniya'],type: 'date', width: 100 }*/
			
			],
			actions: [
				{ name:'action_add', hidden: true },
				{ name:'action_edit', hidden: true },
				{ name:'action_view', hidden: true },
				{name: 'action_delete', hidden: true, url: ''},
				{name: 'action_save', hidden: true, url: '/?c=MedService&m=saveResourceLink'}
			],
			onBeforeEdit: function(o) {
				return o;
			},
			onAfterEdit: function(o) {
				if (!o.record.get('isActive')) {
					// Если сняли галку, то и убираем время
					o.record.set('UslugaComplexResource_Time',null);
					// todo: по идее если сохранять сразу при изменении здесь еще надо сбрасывать UslugaComplexResource_id сразу после сохранения, но это пока убрали
				}
				o.grid.stopEditing(true);
				
			},
			onLoadData: function() {
			//
			}
		});
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'UslugaComplexMedServiceEditForm',
			labelAlign: 'right',
			labelWidth: 120,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'UslugaComplex_id' },
				{ name: 'UslugaComplexMedService_pid' },
				{ name: 'MedService_id' },
				{ name: 'UslugaCategory_id' },
				{ name: 'UslugaComplexMedService_id' }
			]),
			url: '/?c=UslugaComplex&m=saveUslugaComplexMedService',
			items: [{
				name: 'UslugaComplexMedService_id',
				xtype: 'hidden'
			}, {
				name: 'UslugaComplexMedService_pid',
				xtype: 'hidden'
			}, {
				name: 'MedService_id',
				xtype: 'hidden'
			}, {
				name: 'RecordStatus_Code',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'UslugaCategory_id',
                comboSubject: 'UslugaCategory',
				fieldLabel: lang['kategoriya'],
				allowBlank: false,
				tabIndex: TABINDEX_UCMSEW,
				width: 400,
				moreFields: [
					{name: 'UslugaCategory_SysNick', mapping: 'UslugaCategory_SysNick'}
				],
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();
						
						var uslugaCombo = base_form.findField('UslugaComplex_id');
						
						if (uslugaCombo.getFieldValue('UslugaCategory_id') != newValue) {
							uslugaCombo.clearValue();
						}

						if (!Ext.isEmpty(newValue)) {
							uslugaCombo.getStore().filterBy(function(record) {
								if (record.get('UslugaCategory_id') == newValue) {
									return true;
								} else {
									return false;
								}
							});

							uslugaCombo.getStore().baseParams.UslugaCategory_id = newValue;
							uslugaCombo.lastQuery = 'This query sample that is not will never appear';
						} else {
							uslugaCombo.getStore().clearFilter();
							delete uslugaCombo.getStore().baseParams.UslugaCategory_id;
							uslugaCombo.lastQuery = 'This query sample that is not will never appear';
						}
					}.createDelegate(this)
				},
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: lang['usluga'],
				hiddenName: 'UslugaComplex_id',
				allowBlank: false,
				listWidth: 600,
				listeners: {
					'storeload': function(store){
						var base_form = this.FormPanel.getForm();
						var begDTField = base_form.findField('UslugaComplexMedService_begDT');
						var endDTField = base_form.findField('UslugaComplexMedService_endDT');
						this._filterUslugaComplexCombo(begDTField.getValue(), endDTField.getValue());
					}.createDelegate(this),
					'select': function(combo, record, index){
						var base_form = this.FormPanel.getForm();

						var begDTField = base_form.findField('UslugaComplexMedService_begDT');
						var endDTField = base_form.findField('UslugaComplexMedService_endDT');

						if (record && !Ext.isEmpty(record.get('UslugaComplex_id'))) {
							begDTField.setMinValue(combo.getFieldValue('UslugaComplex_begDT'));
							begDTField.setMaxValue(combo.getFieldValue('UslugaComplex_endDT'));

							if (Ext.isEmpty(begDTField.getValue())) {
								endDTField.setMinValue(combo.getFieldValue('UslugaComplex_begDT'));
							}
							endDTField.setMaxValue(combo.getFieldValue('UslugaComplex_endDT'));
						}
						else {
							begDTField.setMinValue(null);
							begDTField.setMaxValue(null);
							endDTField.setMinValue(null);
							endDTField.setMaxValue(null);
						}
						begDTField.validate();
						endDTField.validate();
					}.createDelegate(this),
					'change': function(combo, newValue, oldValue){
						if (!Ext.isEmpty(newValue)) {
							this.uslugaTree.getLoader().load(
								this.uslugaTree.getRootNode(), 
								function () {
									// this.uslugaTree.getRootNode().expand(true);
								}.createDelegate(this)
							);
						}
						else {
							var base_form = this.FormPanel.getForm();

							var begDTField = base_form.findField('UslugaComplexMedService_begDT');
							var endDTField = base_form.findField('UslugaComplexMedService_endDT');
							begDTField.setMinValue(null);// #145267 select выше не обрабатывается, если просто очистить поле
							begDTField.setMaxValue(null);
							endDTField.setMinValue(null);
							endDTField.setMaxValue(null);
							begDTField.validate();
							endDTField.validate();

							this.uslugaTree.getRootNode().reload();
						}
					}.createDelegate(this),
				},
				tabIndex: TABINDEX_UCMSEW + 1,
				width: 400,
				xtype: 'swuslugacomplexallcombo'
			}, {
					layout: 'form',
					border: false,
					items: [{
						fieldLabel: 'Длительность, мин',
						name: 'UslugaComplexMedService_Time',
						xtype: 'textfield'
					}]
				},{
				layout: 'column',
				border: false,
				items: [{
					layout: 'form',
					border: false,
					labelWidth: 160,
					columnWidth: .5,
					items: [{
						fieldLabel: 'Период оказания услуги с',
						name: 'UslugaComplexMedService_begDT',
						allowBlank: false,
						format: 'd.m.Y',
						listeners: {
							'change': function(field, newValue, oldValue) {
								var base_form = this.FormPanel.getForm();

								var usluga_combo = base_form.findField('UslugaComplex_id');
								var UslugaComplex_begDT = Date.parseDate(usluga_combo.getFieldValue('UslugaComplex_begDT'), 'd.m.Y');
								var UslugaComplex_endDT = Date.parseDate(usluga_combo.getFieldValue('UslugaComplex_endDT'), 'd.m.Y');

								if (!Ext.isEmpty(newValue)) {
									base_form.findField('UslugaComplexMedService_endDT').setMinValue(newValue);
								} else {
									base_form.findField('UslugaComplexMedService_endDT').setMinValue(UslugaComplex_begDT);
								}
								if (!Ext.isEmpty(base_form.findField('UslugaComplexMedService_endDT').getValue())) {
									base_form.findField('UslugaComplexMedService_endDT').validate();
								}

								this._filterUslugaComplexCombo(newValue, base_form.findField('UslugaComplexMedService_endDT').getValue());

								//usluga_combo.getStore().baseParams.UslugaComplex_begDT = Ext.util.Format.date(newValue, 'd.m.Y');// ? услуга ещё может действовать
								usluga_combo.lastQuery = 'This query sample that is not will never appear';
							}.createDelegate(this)
						},
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: TABINDEX_UCMSEW + 2,
						xtype: 'swdatefield'
					}]
				},{
					layout: 'form',
					border: false,
					labelWidth: 20,
					columnWidth: .5,
					items: [{
						fieldLabel: 'по',
						name: 'UslugaComplexMedService_endDT',
						allowBlank: true,
						format: 'd.m.Y',
						listeners: {
							'change': function(field, newValue, oldValue) {
								var base_form = this.FormPanel.getForm();

								var usluga_combo = base_form.findField('UslugaComplex_id');
								//var UslugaComplex_begDT = Date.parseDate(usluga_combo.getFieldValue('UslugaComplex_begDT'), 'd.m.Y');
								//var UslugaComplex_endDT = Date.parseDate(usluga_combo.getFieldValue('UslugaComplex_endDT'), 'd.m.Y');

								this._filterUslugaComplexCombo(base_form.findField('UslugaComplexMedService_begDT').getValue(), newValue);

								//usluga_combo.getStore().clearFilter();
								//usluga_combo.getStore().baseParams.UslugaComplex_endDT = Ext.util.Format.date(newValue, 'd.m.Y');
								usluga_combo.lastQuery = 'This query sample that is not will never appear';
							}.createDelegate(this)
						},
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: TABINDEX_UCMSEW + 3,
						xtype: 'swdatefield'
					}]
				}]
			}, {
				border: false,
				layout: 'form',
				bodyStyle: 'margin-left: 6px;',
				items: [{
					xtype: 'swcheckbox',
					name: 'UslugaComplexMedService_IsPortalRec',
					boxLabel: 'Разрешить запись на Портале и в Мобильном приложении',
					hideLabel: true
				}, {
					xtype: 'swcheckbox',
					name: 'UslugaComplexMedService_IsPay',
					boxLabel: 'Платная услуга',
					hideLabel: true
				}, {
					xtype: 'swcheckbox',
					name: 'UslugaComplexMedService_IsElectronicQueue',
					boxLabel: 'Участвует в электронной очереди',
					hideLabel: true
				}]
			},
			this.uslugaTree,
			this.ResourceGrid
			]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = this.FormPanel.getForm();

					if ( !base_form.findField('UslugaComplex_id').disabled ) {
						base_form.findField('UslugaComplex_id').focus();
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_UCMSEW + 4,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					var base_form = this.FormPanel.getForm();
					if ( !base_form.findField('UslugaCategory_id').disabled ) {
						base_form.findField('UslugaCategory_id').focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_UCMSEW + 5,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			]
		});

		sw.Promed.swUslugaComplexMedServiceEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('UslugaComplexMedServiceEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		}.createDelegate(this),
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'form',
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	parentClass: null,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swUslugaComplexMedServiceEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.doLayout();
		this.center();
		
		this.uslugaTree.getRootNode().reload();
		
		this.action = 'add';
		this.callback = Ext.emptyFn;
		this.formMode = 'local';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		
		if ( getRegionNick() == 'kz' ) {
			// Добавил MedOp - "15. Перечень медицинских манипуляции и операций"
			// @task https://redmine.swan.perm.ru/issues/98178
			var allowedCategoryList = ['classmedus','MedOp'];
		}
		else {
			var allowedCategoryList = ['tfoms','gost2011','syslabprofile','lpulabprofile','lpu', 'pskov_foms']; // эталонные и услуги ЛПУ.
		}

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert('Сообщение', 'Неверные параметры', function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		
		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		
		if (this.action == 'add') {
			base_form.findField('UslugaCategory_id').getStore().clearFilter();
			base_form.findField('UslugaCategory_id').lastQuery = '';
			
			base_form.findField('UslugaCategory_id').getStore().filterBy(function(record) {
				return (record.get('UslugaCategory_SysNick') 
					&& record.get('UslugaCategory_SysNick').inlist(allowedCategoryList)
				);
			});
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].formMode && arguments[0].formMode == 'remote' ) {
			this.formMode = 'remote';
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		this.MedServiceType_SysNick = (arguments[0].formParams.MedServiceType_SysNick) ? arguments[0].formParams.MedServiceType_SysNick : null;

		this.getLoadMask().show();

		var begDTField = base_form.findField('UslugaComplexMedService_begDT');
		var endDTField = base_form.findField('UslugaComplexMedService_endDT');

		begDTField.setMinValue(null);
		begDTField.setMaxValue(null);
		endDTField.setMinValue(null);
		endDTField.setMaxValue(null);

		var uslugaCombo = base_form.findField('UslugaComplex_id');
		var uslugaCategoryCombo = base_form.findField('UslugaCategory_id');
		this.ResourceGrid.removeAll({clearAll:true});

		uslugaCombo.lastQuery = 'This query sample that is not will never appear';
		uslugaCombo.getStore().baseParams = {};
		uslugaCombo.getStore().baseParams.MedService_id = base_form.findField('MedService_id').getValue();
		this.ResourceGrid.getGrid().getStore().baseParams.UslugaComplexMedService_id = base_form.findField('UslugaComplexMedService_id').getValue();
		this.ResourceGrid.getGrid().getStore().baseParams.MedService_id = base_form.findField('MedService_id').getValue();
		this.ResourceGrid.getGrid().getStore().load();
		
		switch ( this.action ) {
			case 'add':
				if(getRegionNick() != 'kz' && this.MedServiceType_SysNick == 'profosmotrvz'){
					uslugaCategoryCombo.setFieldValue('UslugaCategory_Code', 4);
				}else{
					uslugaCategoryCombo.setFieldValue('UslugaCategory_SysNick', 'lpu');
				}
				uslugaCategoryCombo.fireEvent('change', uslugaCategoryCombo, uslugaCategoryCombo.getValue());
				this.setTitle(WND_USLUGA_MEDSERVICE_ADD);
				this.enableEdit(true);
				this.getLoadMask().hide();
				if(getRegionNick() != 'kz' && this.MedServiceType_SysNick == 'profosmotrvz') uslugaCategoryCombo.disable();
			break;

			case 'edit':
			case 'view':
				uslugaCombo.getStore().load({
					params: {
						UslugaComplex_id: uslugaCombo.getValue()
					},
					callback: function() {
						uslugaCombo.setValue(uslugaCombo.getValue());
						uslugaCombo.fireEvent('change', uslugaCombo, uslugaCombo.getValue());
						
						uslugaCategoryCombo.setValue(uslugaCombo.getFieldValue('UslugaCategory_id'));
						uslugaCategoryCombo.fireEvent('change', uslugaCategoryCombo, uslugaCategoryCombo.getValue());
					}
				});
				if ( this.action == 'edit' ) {
					this.setTitle(WND_USLUGA_MEDSERVICE_EDIT);
					this.enableEdit(true);
				}
				else {
					this.setTitle(WND_USLUGA_MEDSERVICE_VIEW);
					this.enableEdit(false);
				}

				uslugaCategoryCombo.disable();
				uslugaCombo.disable();
				
				this.getLoadMask().hide();
				base_form.clearInvalid();
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
		
		if ( !base_form.findField('UslugaCategory_id').disabled ) {
			base_form.findField('UslugaCategory_id').focus(true, 250);
		}
		else {
			this.buttons[this.buttons.length - 1].focus();
		}
	},
	width: 600
});
