/**
 * swAddingRegistryEntriesWindow - окно редактирования, добвление настроек ФЛК
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			30.10.2013
 */

sw.Promed.swAddingRegistryEntriesWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Настройка ФЛК',
	autoHeight: true,
	autoScroll: true,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'swAddingRegistryEntriesWindow',
	maximizable: false,
	modal: true,
	resizable: false,
	width: 600,
	show: function() {
		sw.Promed.swAddingRegistryEntriesWindow.superclass.show.apply(this, arguments);

		this.action = null;

		var form = this;
		var base_form = form.FormPanel.getForm();
		var title = 'Настройка ФЛК';

		if ( arguments && arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments && arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		base_form.reset();

		if ( this.action != 'add' && arguments[0].formParams ) {
			base_form.setValues(arguments[0].formParams);
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		
		switch ( this.action ) {
			case 'add':
				this.enableEdit(true);
				form.setTitle(title + ': Добавление');

				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				var FLKSettings_id = arguments[0]['formParams']['FLKSettings_id'];

				if ( !FLKSettings_id ) {
					loadMask.hide();
					this.hide();
					return false;
				}

				var afterFormLoad = function() {
					loadMask.hide();
					if ( form.action == 'edit' ) {
						form.setTitle(title + ': Редактирование');
						form.enableEdit(true);
					}
					else {
						form.setTitle(title + ': Просмотр');
						form.enableEdit(false);
					}
					base_form.clearInvalid();
					loadMask.hide();
				};
				
				if(FLKSettings_id) this.downloadData(FLKSettings_id, afterFormLoad);
				break;

			default:
				this.hide();
				break;
		}
	},
	downloadData: function(id, callback){
		if( !id ) return false;
		var form = this;
		var base_form = form.FormPanel.getForm();
		base_form.load({
			params: {FLKSettings_id: id},
			failure: function() {
				callback();
			},
			success: function(suc, res) {
				if(res.result && res.response){
					var response_obj = Ext.util.JSON.decode(res.response.responseText);
					var obj = response_obj[0];
					obj.RegistryFileCase = (obj.FLKSettings_EvnData) ? obj.FLKSettings_EvnData : '';
					obj.RegistryFilePersonalData = (obj.FLKSettings_PersonData) ? obj.FLKSettings_PersonData : '';

					base_form.setValues(obj);
				}
				callback();
			},
			url: '/?c=Registry&m=loadRegistryEntiesForm'
		});
	},
	doSave: function() {
		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
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

		var RegistryGroupType_id = base_form.findField('RegistryGroupType_id').getValue();
		var RegistryType_id = base_form.findField('RegistryType_id').getValue();
		var begTime = base_form.findField('FLKSettings_begDate').getValue();
		var endTime = base_form.findField('FLKSettings_endDate').getValue();

		if (endTime && (begTime > endTime) ){
			sw.swMsg.alert(langs('Ошибка'), 'Дата окончания действия не может быть меньше даты начала действия');
			return false;
		}

		if (wnd.showRegistryGroupTypeCombo && wnd.showRegistryTypeCombo && !RegistryGroupType_id && !RegistryType_id) {
			sw.swMsg.alert(langs('Ошибка'), 'Должно быть заполнено одно из полей <b>&laquo;Тип реестра&raquo;</b> или <b>&laquo;Тип объединенного реестра&raquo;</b>');
			return false;
		}

		wnd.getLoadMask().show();

		base_form.submit({
			success: function(frm, resp) {
				wnd.getLoadMask().hide();

				if (resp.response) {
					base_form.reset();
					wnd.callback();
					wnd.hide();
				} else {
					sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки'));
				}
			},
			failure: function(frm, action) {
				wnd.getLoadMask().hide();
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: Ext.emptyFn,
					icon: Ext.Msg.WARNING,
					msg: (action.result.Error_Msg) ? action.result.Error_Msg : langs('При сохранении произошли ошибки'),
					title: ERR_WND_TIT
				});
			}
		});
	},

	initComponent: function () {
		let
			region = getRegionNick(),
			RegistryTypeFilter = [],
			RegistryGroupTypeFilter = [],
			showRegistryGroupTypeCombo = region.inlist(['buryatiya','ekb','kareliya','krym','penza','perm','pskov']),
			showRegistryTypeCombo = region.inlist(['astra','buryatiya','ekb','kareliya','khak','krasnoyarsk','penza','perm','pskov','ufa']);

		if ( showRegistryGroupTypeCombo ) {
			switch ( region ) {
				case 'buryatiya':
					RegistryGroupTypeFilter = [1,2,11];
					break;
				case 'ekb':
					RegistryGroupTypeFilter = [12,13,14];
					break;
				case 'kareliya':
					RegistryGroupTypeFilter = [20,1,2,3,4,5,6,7,8,9,10,15,18,19];
					break;
				case 'krym':
					RegistryGroupTypeFilter = [1,2,3,4,5,6,7,8,9,10,16,17];
					break;
				case 'penza':
					RegistryGroupTypeFilter = [1,2,3,4,5,6,7,8,9,10];
					break;
				case 'perm':
					RegistryGroupTypeFilter = [20];
					break;
				case 'pskov':
					RegistryGroupTypeFilter = [1,2,3,4,5,6,7,8,9,10];
					break;
			}
		}

		if ( showRegistryTypeCombo ) {
			switch ( region ) {
				case 'astra':
					RegistryTypeFilter = [1,2,6,7,9,11,12,14];
					break;
				case 'buryatiya':
					RegistryTypeFilter = [1,2,6,7,9,11,12,14];
					break;
				case 'ekb':
					RegistryTypeFilter = [1,2,6,7,9,11,12,13,15];
					break;
				case 'kareliya':
					RegistryTypeFilter = [1,2,6,7,9,11,12,15];
					break;
				case 'khak':
					RegistryTypeFilter = [1,2];
					break;
				case 'krasnoyarsk':
					RegistryTypeFilter = [1,2,6,7,9,11,12,14];
					break;
				case 'krym':
					RegistryTypeFilter = [2,6];
					break;
				case 'penza':
					RegistryTypeFilter = [1,2,7];
					break;
				case 'perm':
					RegistryTypeFilter = [1,2,6,7,9,11,12,13,14,15,16];
					break;
				case 'pskov':
					RegistryTypeFilter = [1,2,6,7,9,11,12,14,15];
					break;
				case 'ufa':
					RegistryTypeFilter = [1,2,6,7,9,14,17];
					break;
			}
		}

		this.FormPanel = new Ext.form.FormPanel({
			bodyStyle: '{padding-top: 0.5em;}',
			border: false,
			frame: true,
			fileUpload: true,
			labelAlign: 'right',
			labelWidth: 200,
			layout: 'form',
			id: 'Registry',
			url: '/?c=Registry&m=saveRegistryEntries',
			autoLoad: false,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, []),
			items: [{
				name: 'FLKSettings_id',
				xtype: 'hidden'
			},
			{
				name: 'action',
				xtype: 'hidden'
			},
			{
				border: false,
				hidden: !showRegistryTypeCombo,
				layout: 'form',
				xtype: 'panel',
				items: [{
					allowBlank: !showRegistryTypeCombo || showRegistryGroupTypeCombo,
					anchor: '95%',
					comboSubject: 'RegistryType',
					fieldLabel: langs('Тип реестра'),
					hiddenName: 'RegistryType_id',
					lastQuery: '',
					onLoadStore: function(store) {
						store.filterBy(function(rec) {
							return (!Ext.isEmpty(rec.get('RegistryType_id')) && rec.get('RegistryType_id').inlist(RegistryTypeFilter));
						})
					},
					typeCode: 'int',
					xtype: 'swcommonsprcombo'
				}]
			},
			{
				border: false,
				hidden: !showRegistryGroupTypeCombo,
				layout: 'form',
				xtype: 'panel',
				items: [{
					allowBlank: !showRegistryGroupTypeCombo || showRegistryTypeCombo,
					anchor: '95%',
					comboSubject: 'RegistryGroupType',
					fieldLabel: langs('Тип объединённого реестра'),
					hiddenName: 'RegistryGroupType_id',
					lastQuery: '',
					onLoadStore: function(store) {
						store.filterBy(function(rec) {
							return (!Ext.isEmpty(rec.get('RegistryGroupType_id')) && rec.get('RegistryGroupType_id').inlist(RegistryGroupTypeFilter));
						})
					},
					typeCode: 'int',
					xtype: 'swcommonsprcombo'
				}]
			},
			{
				xtype: 'fileuploadfield',
				allowBlank: false,
				allowedExtensions: ['xsd'],
				anchor: '95%',
				emptyText: 'Выберите шаблон файла со случаями',
				fieldLabel: 'Шаблон файла со случаями',
				name: 'RegistryFileCase',
				fileselected: function(elem, fname) {
					let re = /\.[xsd]/i;
					let access = re.test(fname);
					if(!access) {
						sw.swMsg.alert('Ошибка', 'Данный тип загружаемого файла не поддерживается!<br />Поддерживаемый тип: *.xsd');
						elem.reset();
						return false;
					}
				}
			},
			{
				xtype: 'fileuploadfield',
				allowedExtensions: ['xsd'],
				anchor: '95%',
				allowBlank: false,
				emptyText: 'Выберите шаблон файла с персональными данными',
				fieldLabel: 'Шаблон файла с персональными данными',
				name: 'RegistryFilePersonalData',
				listeners: {
					fileselected: function(elem, fname) {
						let re = /\.[xsd]/i;
						let access = re.test(fname);
						if(!access) {
							sw.swMsg.alert('Ошибка', 'Данный тип загружаемого файла не поддерживается!<br />Поддерживаемый тип: *.xsd');
							elem.reset();
							return false;
						}
					}
				}
			},
			{
				allowBlank: false,
				fieldLabel: 'Дата начала действия',
				name: 'FLKSettings_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				xtype: 'swdatefield'
			},
			{
				// allowBlank: false,
				fieldLabel: 'Дата окончания действия',
				name: 'FLKSettings_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				xtype: 'swdatefield'
			}]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'CIEW_SaveButton',
				text: BTN_FRMSAVE
			},
			'-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'CIEW_CancelButton',
				text: BTN_FRMCANCEL
			}]
		});

		sw.Promed.swAddingRegistryEntriesWindow.superclass.initComponent.apply(this, arguments);

		this.showRegistryGroupTypeCombo = showRegistryGroupTypeCombo;
		this.showRegistryTypeCombo = showRegistryTypeCombo;
	}
});
