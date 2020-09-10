/**
 * swBSKOKSWindow.js - Форма просмотра ранее проведенных операций (услуг), госпитализаций с ОКС, диагнозов диспансерного наблюдения  
 * Улучшение https://redmine.swan-it.ru/issues/183316
 */

sw.Promed.swBSKOKSWindow = Ext.extend(sw.Promed.BaseForm, {
	title: langs('Форма просмотра ранее проведенных операций (услуг), госпитализаций с ОКС, диагнозов диспансерного наблюдения'),
	id: 'swBSKOKSWindow',
	autoHeight: false,
	layout: 'border',
	width: 800,
	height: 600,
	initComponent: function () {
		var win = this;

		this.panelOperUslug =  new Ext.FormPanel({
			title: 'Операции, услуги (ЧКВ, КАГ, АКШ) за предыдущие три года',
			autoWidth: true,
			id: 'panelOperUslug',
			layout: 'form',
			items: [ 
				win.gridOperUslug = new sw.Promed.ViewFrame({
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 110,
					dataUrl: '/?c=BSK_Register_User&m=getListOperUslug',
					autoLoadData: false,
					id: 'gridOperUslug',
					height: 150, 
					autoWidth: true,
					pageSize: 100,
					contextmenu: false,
					paging: false,
					toolbar: false, 
					border: true, 
					stringfields: [ 
						{name: 'EvnUsluga_id', header: 'Идентификатор услуги', width: 100, type:'int', hidden: true},
						{name: 'EvnUsluga_setDate', header: 'Дата', width: 120, type:'string'},
						{name: 'Usluga_Code', header: 'Код услуги', width: 150, type:'string'},
						{name: 'Usluga_Name', header: 'Наименование', width: 300, type:'string', align:'left'},
						{name: 'Lpu_Nick', header: 'Медицинская организация', type:'string', id: 'autoexpand'}
					]
				})	
			]
		});

		this.panelHospital =  new Ext.FormPanel({
			title: 'Случаи госпитализации с ОКС за предыдущие три года',
			autoWidth: true,
			id: 'panelHospital',
			layout: 'form',
			items: [ 
				win.gridHospital = new sw.Promed.ViewFrame({
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 110,
					dataUrl: '/?c=BSK_Register_User&m=getListHospOKS',
					autoLoadData: false,
					id: 'gridHospital',
					height: 150, 
					autoWidth: true,
					pageSize: 100,
					contextmenu: false,
					paging: false,
					toolbar: false, 
					border: true, 
					stringfields: [ 
						{name: 'EvnPS_id', header: 'Идентификатор КВС', width: 100, type:'int', hidden: true},
						{name: 'EvnPS_setDate', header: 'Дата поступления', width: 120, type:'string'},
						{name: 'EvnPS_disDate', header: 'Дата выписки', width: 150, type:'string'},
						{name: 'Diag_Code', header: 'Основной диагноз', width: 300, type:'string', align:'left'},
						{name: 'Lpu_Nick', header: 'Медицинская организация', type:'string', id: 'autoexpand'}
					]
				})
			]
		});

		this.panelDisp =  new Ext.FormPanel({
			title: 'Диспансерное наблюдение',
			autoWidth: true,
			id: 'panelDisp',
			layout: 'form',
			items: [ 
				win.gridDisp = new sw.Promed.ViewFrame({
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 110,
					dataUrl: '/?c=BSK_Register_User&m=getListDispViewData',
					autoLoadData: false,
					id: 'gridDisp',
					height: 150, 
					autoWidth: true,
					pageSize: 100,
					contextmenu: false,
					paging: false,
					toolbar: false, 
					border: true, 
					stringfields: [ 
						{name: 'PersonDisp_id', header: 'Идентификатор', width: 100, type:'int', hidden: true},
						{name: 'PersonDisp_begDate', header: 'Дата взятия на учёт', width: 120, type:'string'},
						{name: 'Diag_Code', header: 'Диагноз', width: 450, type:'string'},
						{name: 'Lpu_Nick', header: 'Медицинская организация', type:'string', id: 'autoexpand'}
					]
				})
			]
		});

		Ext.apply(this, {
			layout: 'border',
			buttons: [
				'-',
				{
					handler: function() 
					{
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					text: 'Закрыть',
				}
			],
			items:[
				{
					border: false,
					region: 'center',
					items: [
						this.panelOperUslug,
						this.panelHospital,
						this.panelDisp
					]
				}
			]
		});

		sw.Promed.swBSKOKSWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function (params) {
		sw.Promed.swBSKOKSWindow.superclass.show.apply(this, arguments);
		var win = this;
		win.center();
		if ( !arguments[0] || !arguments[0].Person_id) {
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		win.gridOperUslug.getGrid().getStore().load({
			params: {
				Person_id : params.Person_id
			}
		});
		win.gridHospital.getGrid().getStore().load({
			params: {
				Person_id : params.Person_id
			}
		});
		win.gridDisp.getGrid().getStore().load({
			params: {
				Person_id : params.Person_id
			}
		});
	},
	listeners : {
		  'hide': function() {
			  if (this.refresh)
				  this.onHide();
		  },
		  'close': function() {
			  if (this.refresh)
				  this.onHide();
		  } 		
	}
});