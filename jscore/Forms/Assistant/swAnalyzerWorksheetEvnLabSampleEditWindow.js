/**
 * swAnalyzerWorksheetEvnLabSampleEditWindow - окно редактирования "Список проб рабочего списка"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       gabdushev
 * @version      06.2012
 * @comment
 */
sw.Promed.swAnalyzerWorksheetEvnLabSampleEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['spisok_prob-kandidatov_dlya_rabochego_spiska'],
	layout: 'border',
	id: 'AnalyzerWorksheetEvnLabSampleEditWindow',
	modal: true,
	shim: false,
	width: 900,
	height: 610,
	resizable: false,
	maximizable: false,
	maximized: false,
    rowArray: [],
    listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSave:  function() {
		var that = this;
		if ( !this.form.isValid() )
		{
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						that.findById('AnalyzerWorksheetEvnLabSampleEditForm').getFirstInvalidEl().focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}
		this.submit();
		return true;
	},
	submit: function() {
		var that = this;
		var loadMask = new Ext.LoadMask(this.body, {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = {};
		params.action = that.action;
		var picked = [];
		that.grid.getSelectionModel().getSelections().forEach(function (el){
			if (!Ext.isEmpty(el.get('EvnLabSample_id'))) {
				picked.push(el.get('EvnLabSample_id'));
			}
		});
		if (picked.length < 1) {
			loadMask.hide();
			sw.swMsg.alert(lang['oshibka'], lang['vyiberite_probyi_dlya_dobavleniya_v_rabochiy_spisok']);
			return false;
		}
		var pickedEncoded = Ext.util.JSON.encode(picked);
		this.form.findField('PickedEvnLabSamples').setValue(pickedEncoded);
		this.form.submit({
			params: params,
			failure: function(result_form, action)
			{
				loadMask.hide();
				if (action.result)
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action)
			{
				loadMask.hide();
				that.callback(that.owner, action.result.AnalyzerWorksheetEvnLabSample_id);
				that.hide();
			}
		});
	},
	show: function() {
		var that = this;
		sw.Promed.swAnalyzerWorksheetEvnLabSampleEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.AnalyzerWorksheetEvnLabSample_id = null;
		if ( !arguments[0] || !arguments[0].MedService_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { that.hide(); });
			return false;
		}
		
		this.MedService_id = arguments[0].MedService_id;
		
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].ARMType ) {
			this.ARMType = arguments[0].ARMType;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].AnalyzerWorksheetEvnLabSample_id ) {
			this.AnalyzerWorksheetEvnLabSample_id = arguments[0].AnalyzerWorksheetEvnLabSample_id;
		}
		this.form.reset();
		this.doReset();
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		switch (arguments[0].action) {
			case 'add':
				if ( arguments[0].AnalyzerWorksheet_id ) {
					this.form.findField('AnalyzerWorksheet_id').setValue(arguments[0].AnalyzerWorksheet_id);
					this.findById('AnalyzerWorksheetEvnLabSamplePickerGrid').getGrid().getStore().load({params: {AnalyzerWorksheet_id: arguments[0].AnalyzerWorksheet_id, MedService_id: that.MedService_id}})
					loadMask.hide();
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie_identifikator_rabochego_spiska'], function() { that.hide(); });
					return false;
				}
				break;
			case 'edit':
			case 'view':
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						that.hide();
					},
					params:{
						AnalyzerWorksheetEvnLabSample_id: that.AnalyzerWorksheetEvnLabSample_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) {
							return false;
						}
						that.form.setValues(result[0]);
						loadMask.hide();
						return true;
					},
					url:'/?c=AnalyzerWorksheetEvnLabSample&m=load'
				});
				break;
		}

        that.rowArray.push(that.GridPanel.getGrid().getSelectionModel().getSelections('EvnLabSample_ShortNum'));
		return true;
	},
	doSearch: function(mode) {
		var params = this.filterForm.getForm().getValues();
		this.GridPanel.removeAll();
		this.GridPanel.loadData({globalFilters: params});
		return true;
	},
	doReset: function()	{
		this.filterForm.getForm().reset();
        this.doSearch();
	},
	initComponent: function() {
		var that = this;
		var filterForm = new Ext.form.FormPanel({
			floatable: false,
			autoHeight: true,
			animCollapse: false,
			labelAlign: 'right',
			defaults: {
				bodyStyle: 'background: #DFE8F6;'
			},
			region: 'north',
			frame: true,
			buttonAlign: 'left',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					that.doSearch();
				},
				stopEvent: true
			}],
			items: [{
				xtype: 'fieldset',
				style:'padding: 0px 3px 3px 6px;',
				autoHeight: true,
				listeners: {
					expand: function() {
						this.ownerCt.doLayout();
						that.syncSize();
					},
					collapse: function() {
						that.syncSize();
					}
				},
				collapsible: true,
				collapsed: false,
				title: lang['filtr'],
				bodyStyle: 'background: #DFE8F6;',
				items: [{
					layout: 'column',
					items: [
						{ //поле убрано по итогам обсуждения задачи http://redmine.swan.perm.ru/issues/24594
							layout:'form',
							bodyStyle:'background: #DFE8F6;',
							labelWidth:70,
							border:false,
							items:[
								{
                                    autoCreate: {tag: "input", type: "text", size: "12", maxLength: "12", autocomplete: "off"},
									fieldLabel:lang['shtrih-kod'],
                                    name:'EvnLabRequest_BarCode',
                                    maskRe: /\d/,
                                    maxLength:12,
                                    minLength:12,
                                    xtype:'textfield',
									width:100,
									listeners:{}
								}
							]
						},
						{
							layout:'form',
							bodyStyle:'background: #DFE8F6;',
							labelWidth:90,
							border:false,
							items:[
								{
									fieldLabel:lang['nomer_probyi_shtrih-kod'],
									name:'EvnLabSample_Num',
                                    maxLength:4,
                                    autoCreate: {tag: "input", type: "text", size: "4", maxLength: "4", autocomplete: "off"},
                                    maskRe: /\d/,
									xtype:'textfield',
									width:150
								}
							]
						},
						{
							layout: 'form',
							style: 'margin-left: 10px;',
							items: [
								{
									xtype: 'button',
									handler: function()
									{
										that.doSearch();
									},
									iconCls: 'search16',
									text: BTN_FRMSEARCH
								}
							]
						},
						{
							layout: 'form',
							style: 'margin-left: 10px;',
							items: [
								{
									xtype: 'button',
									handler: function()
									{
										this.doReset();
									}.createDelegate(this),
									iconCls: 'resetsearch16',
									text: BTN_FRMRESET
								}
							]
						}
					]
				}
				]
			}
			]
		});
		var form = new Ext.Panel({
			bodyBorder: false,
			border: false,
			frame: false,
			labelAlign: 'right',
			region: 'south',
			height: 0,
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'AnalyzerWorksheetEvnLabSampleEditForm',
				border: true,
				labelWidth: 200,
				collapsible: true,
				region: 'north',
				url:'/?c=AnalyzerWorksheetEvnLabSample&m=saveBulk',
				items: [
					{
						name: 'AnalyzerWorksheetEvnLabSample_id',
						xtype: 'hidden',
						value: 0
					},
					{
						name: 'AnalyzerWorksheet_id',
						xtype: 'hidden'
					},
					{
						name: 'PickedEvnLabSamples',
						xtype: 'hidden',
						value: ''
					}
				]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'AnalyzerWorksheetEvnLabSample_id'},
				{name: 'AnalyzerWorksheet_id'},
				{name: 'EvnLabSample_id'},
				{name: 'AnalyzerWorksheetEvnLabSample_X'},
				{name: 'AnalyzerWorksheetEvnLabSample_Y'}
			]),
			url: '/?c=AnalyzerWorksheetEvnLabSample&m=save'
		});
		var grid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled: true},
				{name: 'action_edit', disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', disabled: true},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			selectionModel: 'multiselect',
			autoExpandMin: 150,
			autoLoadData: false,
			scheme: 'dbo',
			obj_isEvn: true,
			border: true,
			dataUrl: '/?c=EvnLabSample&m=loadListForCandiPicker',
			region: 'center',
			object: 'EvnLabSample',
			editformclassname: 'swEvnLabSampleEditWindow',
			id: 'AnalyzerWorksheetEvnLabSamplePickerGrid',
			paging: false,
            onKeyDown1: function (){
                var e = arguments[0][0];

                if ((e.getCharCode() == 9 )||e.getCharCode() == 13) {
                    return;
                }

                that.gridKeyboardInputSequence++;
                var s = that.gridKeyboardInputSequence;
                var pressed = String.fromCharCode(e.getCharCode());
                var alowed_chars = ['0','1','2','3','4','5','6','7','8','9'];
                if ((pressed != '') && (alowed_chars.indexOf(pressed) >= 0)) {
                    that.gridKeyboardInput = that.gridKeyboardInput + String.fromCharCode(e.getCharCode());
                    setTimeout(function () {
                        that.resetGridKeyboardInput(s);
                    }, 500);
                }
            },
            onEnter: function () {
                that.resetGridKeyboardInput(that.gridKeyboardInputSequence);
            },
            onKeyboardInputFinished: function (input){
                var found = grid.getGrid().getStore().findBy(function (el){
                    return (el.get('EvnLabSample_Num').substr(-4) == input);
                });
                if (found >= 0) {
                    that.rowArray.push(found);
                    grid.getGrid().getSelectionModel().selectRows(that.rowArray);
                }
            },
			stringfields: [
				{name: 'EvnLabSample_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnLabSample_Num', type: 'string', header: lang['nomer_probyi_shtrih-kod'], hidden: true},
				{name: 'EvnLabSample_ShortNum', type: 'string', header: lang['nomer_probyi_shtrih-kod'], width: 170},
				{name: 'RefMaterial_Name', type: 'string', header: lang['biomaterial'], width: 120},
				{name: 'EvnLabSample_Comment', type: 'string', header: lang['kommentariy_k_probe'], width: 150},
				{name: 'UslugaComplex_Name', id:'autoexpand', type: 'string', header: lang['issledovanie'], width: 120},
				{name: 'EvnLabSample_setDT', type: 'string', header: lang['data_vzyatiya'], width: 120}
			],
			//title: 'Выбор проб для включения в рабочий список',
			toolbar: false
		});
		Ext.apply(this, {
			layout: 'border',
			buttons: [{
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
					HelpButton(this, 0),//todo проставить табиндексы
					{
						handler: function()
						{
							this.ownerCt.hide();
						},
						iconCls: 'cancel16',
						text: BTN_FRMCANCEL
					}],
			items:[filterForm, form, grid]
		});

        this.grid = grid;
        that.gridKeyboardInput = '';
        that.gridKeyboardInputSequence = 1;
        that.resetGridKeyboardInput = function (sequence) {
            var result = false;
            if (sequence == that.gridKeyboardInputSequence) {
                if (that.gridKeyboardInput.length >= 4) {
                    grid.onKeyboardInputFinished(that.gridKeyboardInput);
                    result = true;
                }
                that.gridKeyboardInput = '';
            }
            return result;
        };
		sw.Promed.swAnalyzerWorksheetEvnLabSampleEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('AnalyzerWorksheetEvnLabSampleEditForm').getForm();
		this.grid = this.findById('AnalyzerWorksheetEvnLabSamplePickerGrid').getGrid();
		this.GridPanel = grid;
		this.filterForm = filterForm;
	}
});