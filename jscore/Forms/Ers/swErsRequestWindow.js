/**
* Форма редактирования запроса ЭРС
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
*/

sw.Promed.swErsRequestWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Запрос актуальных данных ЭРС из ФСС',
	modal: true,
	resizable: false,
	maximized: false,
	width: 1020,
	height: 250,
	shim: false,
	plain: true,
	layout: 'fit',
	buttonAlign: "right",
	objectName: 'swErsRequestWindow',
	closeAction: 'hide',
	objectSrc: '/jscore/Forms/Common/swErsRequestWindow.js',
	
	doSave: function() {
		var win = this,
			base_form = this.MainPanel.getForm(),
			loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE })
			params = {};
		
		if (!base_form.isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					win.MainPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		params.EvnERS_id = base_form.findField('EvnERSBirthCertificate_id').getValue();

		getWnd('swERSSignatureWindow').show({
			EMDRegistry_ObjectName: 'Запрос актуальных данных ЭРС из ФСС от ' + getGlobalOptions().date,
			isMOSign: true,
			callback: function(data) {
				loadMask.show();	
				base_form.submit({
					params: params,
					failure: function(result_form, action) {
						loadMask.hide();
					},
					success: function(result_form, action) {
						loadMask.hide();
						if (action.result) {
							if (action.result.success) {
								win.hide();
								win.callback();
								sw.swMsg.show({buttons: sw.swMsg.OK, icon: sw.swMsg.INFO, msg: 'Запрос успешно сформирован и отправлен в ФСС'});
							}	
						}
						else {
							Ext.Msg.alert('Ошибка', 'При сохранении произошла ошибка');
						}
					}
				});
			}
		});
	},
	
	show: function() {
		sw.Promed.swErsRequestWindow.superclass.show.apply(this, arguments);
		
		var win = this,
			base_form = this.MainPanel.getForm();
			
		if (!arguments.length) arguments = [{}];
		
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.Person_id = arguments[0].Person_id || null;
		this.Server_id = arguments[0].Server_id || null;
		this.ERSRequest_Snils = arguments[0].ERSRequest_Snils || null;
		this.ERSRequest_ERSNumber = arguments[0].ERSRequest_ERSNumber || null;
		this.EvnERSBirthCertificate_id = arguments[0].EvnERSBirthCertificate_id || null;
		
		base_form.reset();
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		base_form.load({
			url: '/?c=EvnErsBirthCertificate&m=loadPersonData',
			params: {
				Lpu_id: getGlobalOptions().lpu_id
			},
			success: function (form, action) {
				base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
				loadMask.hide();
				win.onLoad();
				base_form.findField('LpuFSSContract_id').focus();
			},
			failure: function (form, action) {
				loadMask.hide();
				if (!action.result.success) {
					Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
					this.hide();
				}
			}
		});	
	},
	
	onLoad: function() {
		
		var win = this,
			base_form = this.MainPanel.getForm();
		
		base_form.findField('ERSRequest_Snils').hideContainer();
		base_form.findField('ERSRequest_ERSNumber').hideContainer();
		base_form.findField('ERSRequest_ERSNumber').setAllowBlank(true);
			
		base_form.findField('EvnERSBirthCertificate_id').setValue(this.EvnERSBirthCertificate_id);
		
		if (!!this.ERSRequest_Snils) {
			base_form.findField('ERSRequest_Snils').showContainer();
			base_form.findField('ERSRequest_Snils').setValue(this.ERSRequest_Snils);
		}
		else {
			base_form.findField('ERSRequest_ERSNumber').showContainer();
			base_form.findField('ERSRequest_ERSNumber').setAllowBlank(!!this.ERSRequest_ERSNumber);
			base_form.findField('ERSRequest_ERSNumber').setDisabled(!!this.ERSRequest_ERSNumber);
			if (!!this.ERSRequest_ERSNumber) {
				base_form.findField('ERSRequest_ERSNumber').setValue(this.ERSRequest_ERSNumber);
			}
		}
		
		this.LpuPanel.loadLpuFSSContractCombo();
	},
	
	initComponent: function() {
		var win = this;
		
		this.LpuPanel = new sw.Promed.ErsLpuPanel;
		
		this.MainPanel = new Ext.form.FormPanel({
			autoScroll: true,
			autoheight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			region: 'center',
			labelAlign: 'right',
			labelWidth: 180,
			items: [{
				name: 'ERSRequest_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnERSBirthCertificate_id',
				value: 0,
				xtype: 'hidden'
			},  {
				name: 'Person_id',
				value: 0,
				xtype: 'hidden'
			}, 
			this.LpuPanel,
			{
				xtype: 'textfield',
				width: 150,
				regex: /^9[\d]{9}$/i,
				maskRe: /\d/i,
				name: 'ERSRequest_ERSNumber',
				fieldLabel: 'Номер ЭРС'
			}, {
				xtype: 'textfield',
				disabled: true,
				width: 150,
				name: 'ERSRequest_Snils',
				fieldLabel: 'СНИЛС'
			}],
			reader: new Ext.data.JsonReader({}, [
				{ name: 'Lpu_id' },
				{ name: 'Org_INN' },
				{ name: 'Org_KPP' },
				{ name: 'Org_OGRN' },
			]),
			url: '/?c=ErsRequest&m=save'
		});
		
		Ext.apply(this,	{
			layout: 'border',
			items: [
				this.MainPanel
			],
			buttons: [{
				handler: function () {
					this.doSave();
				}.createDelegate(this),
				text: 'Отправить в ФСС'
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function () {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}]
		});
		
		sw.Promed.swErsRequestWindow.superclass.initComponent.apply(this, arguments);
	}
});