/**
 * swResponsibleReplacementOptionsDoctorWindow - окно Параметры замены ответственного врача
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 */

sw.Promed.swResponsibleReplacementOptionsDoctorWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swResponsibleReplacementOptionsDoctorWindow',
	width: 650,
	autoHeight: true,
	modal: true,
	maximizable: false,
	title: 'Параметры замены ответственного врача',
	personDispList: null,
	setResponsibleReplacementOptionsDoctor: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();
		var comboDoctor = base_form.findField('MedStaffFact_id');
		
		if (!base_form.isValid() || !this.personDispList) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.FormPanel.getForm().findField('MedStaffFact_id').onFocus();
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
		}
		
		var params = {
			personDispList: this.personDispList,
			MedStaffFact_id: comboDoctor.getValue(),
			MedPersonal_id: comboDoctor.getFieldValue('MedPersonal_id'),
			LpuSection_id: comboDoctor.getFieldValue('LpuSection_id')
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет загрузка..." });
		loadMask.show();
		Ext.Ajax.request({
			url: '/?c=PersonDisp&m=setResponsibleReplacementOptionsDoctor',
			params: params,
			callback: function(opt, success, response) {
				loadMask.hide();
				var resultSuccessfully = [];
				var resultErr = [];
				if(success && response){
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if(response_obj.data){
						if(response_obj.data.resultSuccessfully && !(response_obj.data.resultSuccessfully instanceof Array)){
							var obj = response_obj.data.resultSuccessfully;
							for (key in obj){
								resultSuccessfully.push(key + ' : ' + obj[key]);
							}
						}
						if(response_obj.data.resultErr && !(response_obj.data.resultErr instanceof Array)){
							var obj = response_obj.data.resultErr;
							for (key in obj){
								resultErr.push(key + ' : ' + obj[key]);
							}
						}
					}
				}
				if(resultSuccessfully.length>0){
					var msg = 'Ответственный врач был успешно изменен в ' + resultSuccessfully.length + ' контрольных картах диспансерного наблюдения.';
					var icon = Ext.Msg.INFO;
				}else{
					var msg = 'Ответственный врач не был изменен ни в одной контрольной карте диспансерного наблюдения.';
					var icon = Ext.Msg.ERROR;
				}
				if(resultSuccessfully.length>0) console.log('setResponsibleReplacementOptionsDoctor (success): ' + resultSuccessfully.join('; '));
				if(resultErr.length>0) console.log('setResponsibleReplacementOptionsDoctor (error): ' + resultErr.join('; '));
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.hide();
					}.createDelegate(this),
					icon: icon,
					msg: msg,
					title: 'Замена ответственного врача'
				});
			}.createDelegate(this),
			failure: function(response, opts){
				loadMask.hide();
				Ext.Msg.alert('Ошибка','Во время замены ответственного врача произошла ошибка');
			}.createDelegate(this)
		});
	},
	show: function() {
		sw.Promed.swResponsibleReplacementOptionsDoctorWindow.superclass.show.apply(this, arguments);
		this.personDispList = null;
		var base_form = this.FormPanel.getForm();
		base_form.reset();

		if(arguments[0] && arguments[0]['personDispArr'] && (arguments[0]['personDispArr'] instanceof Array) &&  arguments[0]['personDispArr'].length > 0){
			this.personDispList = arguments[0]['personDispArr'].join(',');
		}else{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.hide();
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: 'не переданы обязательные параметры',
				title: ERR_INVFIELDS_TIT
			});
		}
		
		this.filterMedStaffFactCombo();
	},
	filterMedStaffFactCombo: function(params) {
		var form = this.FormPanel.getForm();
		var comboDoctor = form.findField('MedStaffFact_id');
		var curDate = getGlobalOptions().date;
		var lpu_id = getGlobalOptions().lpu_id;
		comboDoctor.getStore().removeAll();
		comboDoctor.clearValue();
		
		setMedStaffFactGlobalStoreFilter({
			Lpu_id: lpu_id,
			dateFrom: curDate,
			allowLowLevel: 'yes',
			isPolka: true,
			isDisp: true,
			isDoctor: true
		});
		comboDoctor.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		return true;
	},
	initComponent: function() {
		var wnd = this;

		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			autoWidth: false,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'right',
			labelWidth: 180,
			items: [{
				hiddenName: 'MedStaffFact_id',
				xtype: 'swmedstafffactglobalcombo',
				width: 400,
				name: 'Doctor',
				fieldLabel: 'Новый ответственный врач',
				listWidth: 550,
				allowBlank: false,
				listeners: {
					'change': function(combo, newValue, oldValue){
						this.buttons[0].setDisabled( !(combo.getValue()) );
					}.createDelegate(this),
					select: function(m, d){
						this.buttons[0].setDisabled( !(d.get('MedStaffFact_id')) );
					}.createDelegate(this)
				}
			}]
		});

		Ext.apply(this,{
			buttons: [
				{
					handler: function () {
						this.setResponsibleReplacementOptionsDoctor();
					}.createDelegate(this),
					iconCls: 'replace16',
					disabled: true,
					text: 'Заменить'
				},
				{
					text: '-'
				},
				{
					handler: function()
					{
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: 'Отмена'
				}
			],
			layout: 'form',
			items: [this.FormPanel]
		});

		sw.Promed.swResponsibleReplacementOptionsDoctorWindow.superclass.initComponent.apply(this, arguments);
	}
});