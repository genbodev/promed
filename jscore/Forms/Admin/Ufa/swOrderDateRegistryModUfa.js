/**
* swOrderDateRegistryModUfa - окно фильтра грида.
*  
*
* PromedWeb - The New Generation of Medical Statistic Software
* 
*
* @package      Admin
* @access       public
* @version      25.06.2013
* @author       Васинский Игорь (НПК "Прогресс" г.Уфа)
*/

// Пример
// Task#21710 Внедрение алгоритма изменения отчётного месяца и года для реестра, установка номера пачки xml
   
sw.Promed.swOrderDateRegistryModUfa = Ext.extend(sw.Promed.BaseForm, {
	id    : 'swOrderDateRegistryModUfa', 
	objectName    : 'swOrderDateRegistryModUfa',
	objectSrc     : '/jscore/Forms/Admin/Ufa/swOrderDateRegistryModUfa.js',    
	layout: 'form',
	plain: true,    
	buttonAlign: 'center',
	title : 'Редактирование данных реестра',
	modal : true,
	width : 300,
	height:500,
	closable : false,
	closeAction   : 'close',
	draggable     : true,
	/**formatDate : function(value){
			   return value ? new Date(value).dateFormat('d.m.Y') : '';
	},*/
	initComponent: function() 
	{      
		var form = this;
	 
		var store = new Ext.data.SimpleStore({
			fields: ['num_pack'],
			data :  [[1],[2],[3],[4],[5],[6],[7],[8],[9]]
		});

		var combo = new Ext.form.ComboBox({
			id : 'idCombo',
			readOnly : true,
			lazyInit: false,
			forceSelection : true,
			setLazyRender : false,
			fieldLabel : 'Номер пачки',
			store: store,
			displayField:'num_pack',
			typeAhead: true,
			width: 120,
			mode: 'local',
			triggerAction: 'all',
			emptyText:'не изменять',
			selectOnFocus:true
			//applyTo: 'local-states'
		});
		
		Ext.apply(this, 
		{   
			autoHeight: true,
			buttons : [
					 {
						hidden: false,
						handler: function() 
						{
							form.updateRegistry();
						},
						iconCls: 'ok16',
						text: 'Сохранить'
					 },
					 {
						hidden: false,
						handler: function() 
						{
							form.hide();
						},
						iconCls: 'close16',
						text: 'Отмена'
					 }                     
			],
	 
			items : [
					 {
						xtype : 'fieldset',
						title : '',
						region: 'north',
						height      : 80,
						style : {margin : '10px'},
						width : 2870,
						items : [ 
						 {
							xtype: 'datefield',
							id : 'orderDate',
							fieldLabel : 'Отчётный м/г',
							format : 'd.m.Y',
							width: 120,
							value : form.Registry_orderDate,
							disabledDates : ["^02","^03","^04","^05","^06","^07","^08","^09","^10","^11","^12","^13","^14","^15","^16",
											 "^17","^18","^19","^20","^21","^22","^23","^24","^25","^26","^27","^28","^29","^30","^31"],
							plugins: [ new Ext.ux.InputTextMask('01.99.9999', false) ] 
						 },
						 combo ]
					 }
								   
			], 
			html : '<div style="padding:10px;"><span style="font-size:10px;"><b style="color:red">Внимание: </b> значение по умолчанию для номера пачки, <br/>для реестров "ДДС 2013г." - <b>8</b>, <br/>для ДД, ДВН 2013 г., ВМП - <b>9</b>, <br/>для стационара, пол-ки, СМП, ПМО - <b>1</b></span></div>' 
		 

		});
		sw.Promed.swOrderDateRegistryModUfa.superclass.initComponent.apply(this, arguments);
	},
	listeners: 
	{

	},
	updateRegistry: function () {
		var form = this;
		var val = (!Ext.getCmp('idCombo').value) ? Ext.getCmp('idCombo').emptyText : Ext.getCmp('idCombo').value;
		var regexp = /[0-9]{1}/;
		var idCombo = (val > 0) ? val : val.match(regexp);
		idCombo = (idCombo.length > 0) ? idCombo[0] : idCombo;

		var params = {
			Registry_id: this.Registry_id,
			Registry_orderDate: (typeof(Ext.getCmp('orderDate').value) == 'object')
				? Ext.util.Format.date(Ext.getCmp('orderDate').value, 'd.m.Y')
				: Ext.getCmp('orderDate').value,
			Registry_pack: (!idCombo) ? Ext.getCmp('idCombo').value : idCombo
		}

		Ext.Ajax.request({
			url: '/?c=RegistryUfa&m=updateRegistryTwoCols',
			params: params,
			callback: function (options, success, response) {
				if (success == true) {
					var records = Ext.util.JSON.decode(response.responseText);
					form.callback();
					form.hide();
				}
			}
		});
	},
	show: function() 
	{
		sw.Promed.swOrderDateRegistryModUfa.superclass.show.apply(this, arguments);

		var win = this;

		this.callback = Ext.emptyFn;
		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		this.Registry_id = null;
		if (arguments[0] && arguments[0].Registry_id) {
			this.Registry_id = arguments[0].Registry_id;
		}

		this.Registry_orderDate = null;
		if (arguments[0] && arguments[0].Registry_orderDate) {
			this.Registry_orderDate = arguments[0].Registry_orderDate;
		}

		this.Registry_pack = null;
		if (arguments[0] && arguments[0].Registry_pack) {
			this.Registry_pack = arguments[0].Registry_pack;
		}

		Ext.getCmp('orderDate').setValue(win.Registry_orderDate);
		Ext.getCmp('idCombo').emptyText = 'не изменять ('+win.Registry_pack+')';
		Ext.getCmp('idCombo').applyEmptyText();
		Ext.getCmp('idCombo').clearValue();
	}

});