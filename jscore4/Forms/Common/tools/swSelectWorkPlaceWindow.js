/* 
Окно выбора рабочего места по умолчанию
*/
Ext.define('common.tools.swSelectWorkPlaceWindow', {
	alias: 'widget.swSelectWorkPlaceWindow',
	id:'swSelectWorkPlaceWindow',
	extend: 'Ext.window.Window',
	title: 'Выберите место работы (АРМ) по умолчанию',
	width: 800,
	height: 300,
	maximizable: false,
	modal: true,
	layout: {
        align: 'stretch',
        type: 'vbox'
    },
	selectDefaultArm: function() {
		if (getGlobalOptions().IsLocalSMP) {
			// нет LDAPa
			Ext.Msg.alert('Ошибка', 'Функционал выбора места работы по умолчанию не доступен на локальном сервере');
			return false;
		}
		var me = this;
		var sel = this.Grid.getSelectionModel().getSelection();
		if ( sel.length == 1 ) {
			
			var loadMask =  new Ext.LoadMask(this, {msg:"Пожалуйста, подождите..."}); 
				loadMask.show()
			Ext.Ajax.request({
				url: '/?c=User&m=setDefaultWorkPlace',
				params: sel[0].data,
				callback: function(options, success, response) {
					loadMask.hide();
					if (success) {
						// Думаем что все сохранилось в настройках
						var result = Ext.JSON.decode(response.responseText);
						// Устанавливаем правильные глобальные переменные 
						Ext.globalOptions.defaultARM = result;
						sw.Promed.MedStaffFactByUser.selectARM(sel[0].data);
						me.close();
					}
				}
			});
			
			
		}
		else {
			Ext.Msg.alert('Ошибка', 'Выберите необходимое место работы для того, чтобы установить его основным при входе в АРМ.');
		}
	},
	initComponent: function() {
		var me = this
		
		this.Grid = Ext.create('Ext.grid.Panel', {
			flex: 1,
			autoScroll: true,
			stripeRows: true,
			id: me.id+'_GridPanel',
//			refId: '',
			store: sw.Promed.MedStaffFactByUser.store,
			columns: [
				{
				dataIndex: 'ARMNameLpu',
				header: 'АРМ/ЛПУ',
				sortable: false,
				hideable: false,
				width: 250
			}, {
				dataIndex: 'Name',
				header: '<font color="#000">Подразделение / Отделение</font> / <font color="darkblue">Служба</font>',
				sortable: false,
				hideable: false,
				width: 300
			}, {
				dataIndex: 'PostMed_Name',
				header: 'Должность',
				sortable: false,
				hideable: false,
				width: 180
//			}, {
//				dataIndex: 'Timetable_isExists',
//				header: 'Расписание',
//				resizable: false,
//				renderer: sw.Promed.Format.checkColumn,
//				sortable: false,
//				width: 64
			}, {
				dataIndex: 'MedStaffFact_id',
				hidden: true,
				hideable: false,
			}, {
				dataIndex: 'LpuSection_id',
				hidden: true,
				hideable: false,
			}, {
				dataIndex: 'MedPersonal_id',
				hidden: true,
				hideable: false,
			}, {
				dataIndex: 'PostMed_Code',
				hideable: false,
				hidden: true
			}, {
				dataIndex: 'PostMed_id',
				hideable: false,
				hidden: true
			}, {
				dataIndex: 'MedService_id',
				hideable: false,
				hidden: true
			}, {
				dataIndex: 'MedService_Name',
				hideable: false,
				hidden: true
			}, {
				dataIndex: 'MedServiceType_SysNick',
				hideable: false,
				hidden: true
			},
			],
			listeners: {
				'rowdblclick': function(grid, number, obj) {
					me.selectDefaultArm();
				}
			}
		})
		

		Ext.applyIf(me, {
			items: [
				this.Grid
			],
			buttons: [
			{
				iconCls: 'ok16',
				text: 'Выбрать',
				margin: '0 5',
				handler: function(){
					me.selectDefaultArm()
				}
			},'->',
			{
				text: 'Помощь',
				iconCls   : 'help16',
				handler   : function()
				{
					ShowHelp(me.title);
				}
			}
			],
		})
		
		me.callParent()
	}
})

