/**
* swDbfImportWindow - окно импорта Dbf
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       gabdushev
* @version      2013-04
*/
sw.Promed.swDbfImportWindow = Ext.extend(sw.Promed.BaseForm, {
	title:lang['zagruzka_faylov'],
	id: 'DbfImportWindow',
	height: 190,
	width: 500,
	maximized: false,
	maximizable: false,
	resizable: false,
	modal: true,
	shim: false,
	show: function (callback) {
		this.callback = callback;
		sw.Promed.swDbfImportWindow.superclass.show.apply(this, arguments);
		this.RegisterList_id = null;
		this.RegisterList_Name = null;
		this.RegisterList_id = arguments[0].RegisterList_id;
		this.RegisterList_Name = arguments[0].RegisterList_Name;
		this.setTitle(lang['zagruzka_faylov']);
		switch (this.RegisterList_Name) {

			// Федеральный регистр льготников
			case 'PersonPrivilege':
				this.items.items[0].setValue(langs('Для импорта необходимо указать расположение файла с расширением *.MS0, *.MS1, либо файлов C_REGL.DBF и C_REGO.DBF. Для этого нажмите кнопку "Обзор" и выделите эти Файлы. Загрузка начнется после нажатия кнопки "Запуск"'));
				break;

			case 'PersonDead':
				this.items.items[0].setValue(lang['dlya_zagruzki_spiska_umershih_najmite_na_knopku_obzor_i_vyiberite_nujnyiy_fayl_zagruzka_nachnetsya_posle_najatiya_knopki_zapusk']);
				break;

			case 'Org':
				this.items.items[0].setValue(lang['dlya_zagruzki_organizatsiy_najmite_na_knopku_obzor_i_vyiberite_nujnyiy_fayl_s_rasshireniem_*_xml_zagruzka_nachnetsya_posle_najatiya_knopki_zapusk']);
				break;

			case 'CmpCallCard':
				this.setTitle('Импорт карт СМП из DBF');
				this.items.items[0].setValue('Для импорта необходимо указать расположение файла с картами вызова в формате .dbf');
				break;
		}
	},
	initComponent: function() {
		var that = this;
		Ext.apply(this, {
			buttons: [{
					text: '-'
				}, {
					text      : lang['zapusk'],
					tabIndex  : -1,
					tooltip   : lang['zapustit_zagruzku'],
					iconCls   : 'actions16',
					handler   : function() {
						that.getLoadMask(lang['pojaluysta_podojdite_proizvoditsya_import']).show();
						that.findById('DbfImportForm').getForm().findField('RegisterList_id').setValue(that.RegisterList_id);
						that.findById('DbfImportForm').getForm().findField('RegisterList_Name').setValue(that.RegisterList_Name);
						that.findById('DbfImportForm').getForm().submit({
							timeout: 0,
							success: function (){
								that.getLoadMask('').hide();

								Ext.getCmp('ImportWindow').callback();
								if (that.callback && 'function' == typeof(that.callback)) {
									that.callback(arguments);
								}
								that.hide();
							},
							failure: function (form, action){
								that.getLoadMask('').hide();
								that.hide();

								if (!action.result || Ext.isEmpty(action.result.Error_Msg)) {
									var answer = Ext.util.JSON.decode(action.response.responseText);
									var error = !Ext.isEmpty(answer.Error_Msg)?answer.Error_Msg:'Ошибка при импорте данных ФРЛ';

									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										fn: Ext.emptyFn,
										icon: Ext.Msg.WARNING,
										msg: error,
										title: ERR_WND_TIT
									});
								}
							}
						});
					}
				}, {
					text      : lang['otmena'],
					tabIndex  : -1,
					tooltip   : lang['otmena'],
					iconCls   : 'cancel16',
					handler   : function() {
						this.ownerCt.hide();
					}
				}],
			layout: 'border',
			items: [
				{
					id:"anonceText",
					region: 'north',
					xtype: 'textarea',
					value: langs('Для импорта необходимо указать расположение файла с расширением *.MS0 и *.MS1, либо файлов C_REGL.DBF и C_REGO.DBF. Для этого нажмите кнопку "Обзор" и выделите этот файл или оба этих файла, используя кнопки Ctrl или Shift. Загрузка начнется после нажатия кнопки "Запуск"'),
					disabled: true
				},
				new Ext.form.FormPanel({
					region: 'center',
					autoHeight: true,
					bodyStyle: 'padding: 5px',
					border: false,
					buttonAlign: 'left',
					frame: true,
					id: 'DbfImportForm',
					labelAlign: 'right',
					labelWidth: 200,
					fileUpload: true,
					items: [
						{
							xtype: 'textfield',
							inputType: 'file',
							fieldLabel: lang['vyiberite_faylyi_dlya_zagruzki'],
							autoCreate: { tag: 'input', name: 'sourcefiles[]', type: 'text', size: '20', autocomplete: 'off', multiple: 'multiple' }
							
						},
						{
							xtype: 'hidden',
							name:"RegisterList_id",
							value:this.RegisterList_id
						},
						{
							xtype: 'hidden',
							name: "RegisterList_Name",
							value: this.RegisterList_Name
						}
					],
					keys: [{
						alt: true,
						fn: function(inp, e) {
							switch (e.getKey()) {
								case Ext.EventObject.C:
									if (this.action != 'view') {
										this.doSave(false);
									}
									break;
								case Ext.EventObject.J:
									this.hide();
									break;
							}
						},
						key: [ Ext.EventObject.C, Ext.EventObject.J ],
						scope: this,
						stopEvent: true
					}],
					params: {
						RegisterList_id: this.RegisterList_id,
						RegisterList_Name: this.RegisterList_Name
					},
					reader: new Ext.data.JsonReader({
							success: function() {
								//
							}
						},[
							{ name: 'LocalDbList_id' },
							{ name: 'LocalDbList_name' },
							{ name: 'LocalDbList_prefix' },
							{ name: 'LocalDbList_nick' },
							{ name: 'LocalDbList_schema' },
							{ name: 'LocalDbList_key' },
							{ name: 'LocalDbList_module' },
							{ name: 'LocalDbList_sql' }
						]),
					timeout: 6000000,
					url: '/?c=ImportSchema&m=run'
				})
			]});
		sw.Promed.swDbfImportWindow.superclass.initComponent.apply(this, arguments);
                
		this.findById('DbfImportForm').getForm().errorReader = {
			read: function (resp){
				var result = false;
				that.getLoadMask().hide();
				try {
					result = Ext.decode(resp.responseText);
                                        
				} catch (e) {
					sw.swMsg.alert(lang['oshibka_pri_vyipolnenii_importa'],lang['pri_vyipolnenii_importa_proizoshla_oshibka'] +
						'Пожалуйста, обратитесь к разработчкам, сообщив следующую отладочную информацию:<br>' +
						'<pre style="overflow: scroll; height: 200px; width: 100%;" >При отправке формы произошла ошибка. Ответ сервера: ' + resp.responseText + '</pre>')
				}
				return result;
			}
		}
	}
});
