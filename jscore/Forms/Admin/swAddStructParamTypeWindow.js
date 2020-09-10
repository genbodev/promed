/**
* swLpuOperEnvWindow - Оперативная обстановка по выбранному ЛПУ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dyomin Dmitry
* @version      03.03.2016
*/

sw.Promed.swAddStructParamTypeWindow = Ext.extend(sw.Promed.BaseForm, {
	
	
	modal: true,
	id:'swAddStructParamTypeWindow',
	width: 450,
	autoHeight: true,
	type:null,
	onCancel: 'hide',
	action:'edit',
	callback: Ext.emptyFn,
	onSelect: Ext.emptyFn,
	closeAction:'destroy',
	closable:false,
	comboSubject:'',
	show: function() {
		sw.Promed.swAddStructParamTypeWindow.superclass.show.apply(this, arguments);
		var base_form = this.FormPanel.getForm();
		this.formStatus = 'edit';
		var win = this;
		win.fieldLabel = 'Параметр'
		base_form.reset();
		
		if(!arguments || !arguments[0] || !arguments[0].comboSubject ){
			sw.swMsg.alert('Сообщение', 'Неверные параметры', win.hide() );
			return false;
		}else{ 
			this.comboSubject = arguments[0].comboSubject;
		}
		
		if(arguments[0].onSelect){
			this.onSelect = arguments[0].onSelect;
		}
		if(arguments[0].callback){
			this.callback = arguments[0].callback;
		}
		if(arguments[0].fieldLabel){
			this.fieldLabel = arguments[0].fieldLabel;
		}
		if(arguments[0].items){
			this.oldIds = arguments[0].items;
		}
		this.FormPanel.removeAll();
		switch(win.comboSubject){
			case'Diag':
				this.FormPanel.add({
					comboSubject: win.comboSubject,
					editable: true,
					fieldLabel: 'Диагноз',
					hiddenName: win.comboSubject+'_id',
					lastQuery: '',
					maxCount:3,
					width: 290,
					xtype: 'swdiagcombo'
				});
				this.setTitle('Диагнозы: добавление');
				break;
			case 'UslugaComplex':
				this.FormPanel.add({
					comboSubject: win.comboSubject,
					editable: true,
					fieldLabel: 'Услуга',
					hiddenName: win.comboSubject+'_id',
					lastQuery: '',
					maxCount:3,
					width: 290,
					xtype: 'swuslugacomplexnewcombo'
				});
				base_form.findField(win.comboSubject+'_id').getStore().load({params: {where: ' where '+win.comboSubject+'_Code not in ('+win.oldIds.join()+')'}});
				this.setTitle('Услуги: добавление');
				break;
			case 'Age':
				this.FormPanel.add(
				{
					layout:'form',
					items:[{
						xtype:'numberfield',
						name:'AgeFrom',
						hiddenName:'AgeFrom',
						id:'AgeFrom',
						fieldLabel:'C'
					},{
						xtype:'numberfield',
						name:'AgeTo',
						hiddenName:'AgeTo',
						id:'AgeTo',
						fieldLabel:'По'
					}]
				});
				
				this.FormPanel.doLayout();
				this.setTitle('Возраст: добавление');
				break;
			default:
				this.FormPanel.add({
					comboSubject: win.comboSubject,
					editable: true,
					fieldLabel: 'Специальность',
					labelWidth: 220,
					hiddenName: win.comboSubject+'_id',
					lastQuery: '',
					maxCount:3,
					width: 290,
					xtype: 'swcommonsprcombo',
					setValue: function(v) {
						var text = v;
						if(this.valueField){
							var r = this.findRecord(this.valueField, v);
							if(r){
								text = r.data[this.displayField];

							} else if(this.valueNotFoundText !== undefined){
								text = this.valueNotFoundText;
							}
						}
						this.lastSelectionText = text;
						if(this.hiddenField){
							this.hiddenField.value = v;
						}
						Ext.form.ComboBox.superclass.setValue.call(this, text);
						this.value = v;
					}
				});
				base_form.findField(win.comboSubject+'_id').getStore().load({params: {where: ' where '+win.comboSubject+'_Code not in ('+win.oldIds.join()+')'}});
				this.setTitle('Специальности: добавление');
				break;
			
		}
		
		this.doLayout();
	},
	doSave: function(){
		var form = this.FormPanel;
		var base_form = form.getForm();
		var objValue = new Object();
		
		

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		if(this.comboSubject != 'Age'){
			if(base_form.findField(this.comboSubject+'_id').getStore().getById(base_form.findField(this.comboSubject+'_id').getValue())){
				var value = base_form.findField(this.comboSubject+'_id').getStore().getById(base_form.findField(this.comboSubject+'_id').getValue());
				switch(this.comboSubject){
					case 'MedSpecOms':
						objValue.Code = value.data.MedSpecOms_Code;
						objValue.Name = value.data.MedSpecOms_Name;
						objValue.added = 1;
						objValue.id = value.id;
						break;
					case 'Diag':
						objValue.Code = value.data.Diag_Code;
						objValue.Name = value.data.Diag_Name;
						objValue.added = 1;
						objValue.id = value.id;
						break;
					case 'UslugaComplex':
						objValue.Code = value.data.UslugaComplex_Code;
						objValue.Name = value.data.UslugaComplex_Name;
						objValue.added = 1;
						objValue.id = value.id;
						break;
				}
			} else {
				sw.swMsg.alert('Ошибка','Значение указано неверно!');
				base_form.findField(this.comboSubject+'_id').clearValue();
				if(this.comboSubject == 'MedSpecOms'){
					base_form.findField(this.comboSubject+'_id').lastQuery = '';
					base_form.findField(this.comboSubject+'_id').getStore().clearFilter();
					base_form.findField(this.comboSubject+'_id').getStore().reload({params: {where: ' where '+this.comboSubject+'_Code not in ('+this.oldIds.join()+')'}});
				}
				return false;
			}
			if(objValue.Code.inlist(this.oldIds)){
				sw.swMsg.alert('Ошибка','Данный пункт уже существует в окне редактирования структурированного параметра!');
				base_form.findField(this.comboSubject+'_id').clearValue();
				return false;
			}
		} else {
			objValue.AgeFrom = this.findById('AgeFrom').getValue();
			objValue.AgeTo = this.findById('AgeTo').getValue();
			objValue.added = 1;
			for (var i = 0; i < this.oldIds.length; i++) {
				if ( (this.oldIds[i][0] || this.oldIds[i][1]) &&
					((objValue.AgeFrom >= this.oldIds[i][0] && objValue.AgeTo <= this.oldIds[i][1])
					||(objValue.AgeFrom < this.oldIds[i][0] && (objValue.AgeTo <= this.oldIds[i][1] && objValue.AgeTo >= this.oldIds[i][0]))
					||((objValue.AgeFrom >= this.oldIds[i][0] && objValue.AgeFrom <= this.oldIds[i][1]) && objValue.AgeTo > this.oldIds[i][1])
					))
				{
					sw.swMsg.alert('Ошибка','Данный возраст пересекается с существующим в окне редактирования структурированного параметра!');
					this.findById('AgeFrom').setValue('');
					this.findById('AgeTo').setValue('');
					return false;
				}
			};
		}
		this.callback(objValue);
		this.hide();
	},
	initComponent: function() {
    	
		var win = this;
		
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight:true,
			bodyStyle: 'padding: 5px',
			buttonAlign: 'left',
			frame: true,
			id: 'swAddStructParamTypeForm',
			labelAlign: 'right',
			labelWidth: 120,
			reader: new Ext.data.JsonReader({success: Ext.emptyFn}, []),
			items: [],
			enableKeyEvents: true,
			keys: []
		});
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'ok16',
				text: 'Добавить'
			}, {
				text: '-'
			},
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [
			this.FormPanel
			]
			
		});
		sw.Promed.swAddStructParamTypeWindow.superclass.initComponent.apply(this, arguments);
	}
});