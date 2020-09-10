/**
* amm_testForm - тестируем
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Vaccine
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-25.06.2009
*/
/*NO PARSE JSON*/

//sw.Promed.amm_testForm = Ext.extend(sw.Promed.BaseForm, {


//=====================
sw.Promed.amm_testForm = Ext.extend(Ext.Window, {
  maximized: true,
  codeRefresh: true,
  objectName: 'amm_testForm',
  objectSrc: '/jscore/Forms/Vaccine/amm_testForm.js',
  buttonAlign: 'left',
  closable: true,
  collapsible: true,
  closeAction: 'hide',
  modal: true,
  onHide: Ext.emptyFn,
  //draggable: true,
  //id: 'UslugaSearchWindow',
  id: 'journalsVaccine',
  layout: 'border',
  onUslugaSelect: Ext.emptyFn,
  Usluga_date: null,
  plain: true,
  resizable: false,
  title: 'Просмотр! журналов вакцинации',
  //width: 800,
  //height: 500,
  
//  doReset: function() {
//    this.findById('USW_UslugaSearchGrid').getStore().removeAll();
//    this.findById('UslugaSearchForm').getForm().reset();
//    this.findById('USW_Usluga_Code').focus(true, 250);
//  },
  
/*  doSearch: function() {
    var grid = this.findById('USW_UslugaSearchGrid');
    var Mask = new Ext.LoadMask(Ext.get('UslugaSearchWindow'), { msg: SEARCH_WAIT });
    var params = this.findById('UslugaSearchForm').getForm().getValues();

    if ( this.allowedCatCode )
      params.allowedCatCode = this.allowedCatCode;

    if ( !params.Usluga_Name && !params.Usluga_Code ) {
      sw.swMsg.alert('Ошибка', 'Введите условия поиска', function() { grid.ownerCt.findById('USW_Usluga_Name').focus(true, 250); });
      return false;
    }

//    if ( !this.Usluga_date ) {
//      sw.swMsg.alert('Сообщение', 'Отсутствует параметр с датой оказания услуги. При поиске дата оказания услуги не будет учитываться!', function() {});
//    }

    grid.getStore().removeAll();
    Mask.show();

    params.Usluga_date = this.Usluga_date;
    grid.getStore().load({
      params: params,
      callback: function() {
        if (grid.getStore().getCount() > 0)
        {
          grid.getView().focusRow(0);
          grid.getSelectionModel().selectFirstRow();
        }
        Mask.hide();
      }
    });
  },*/

  initComponent: function() {

    var form = this;
    
    /*
    * хранилище для основной таблицы
    */
    this.store0 = new Ext.data.JsonStore({
      fields: ['Journal_id', 'Name'],
      url: '/?c=VaccineCtrl&m=loadJournals',
      key: 'Journal_id',
      root: 'data'
    });
    form.load();
    
    //var grid0 = Ext.create('Ext.grid.Panel', {
    this.grid0 = new Ext.grid.GridPanel({
      region: 'west',
      width: 200,
      //autoWidth: true,
      split: true,
      collapsible: true,
      floatable: false,
      store: form.store0,         // определили хранилище
      title: 'Журналы', // Заголовок
      columns:[
        {header: 'Наименование', dataIndex: 'Name', width: 200, sortable: true}
      ],
      listeners: {
        'cellclick': function(grid, rowIndex, columnIndex, e) {
            var record = grid.getStore().getAt(rowIndex);  // Get the Record
            //var fieldName = grid.getColumnModel().getDataIndex(columnIndex); // Get field name
            //var data = record.get(fieldName);
            var data = record.get('Journal_id');
            alert(data);
            
            //sw.Promed.amm_testForm.superclass.show.apply(this, arguments);
            //alert(sw.Promed.amm_testForm.items[1]);
            //grid.columns = columnsVacMap;
            //grid.store = storeVacMap;
            
            //sw.Promed.amm_testForm.items[1] = gridVacMap;
            
//            grid.
        },
        'keydown': function() {
          alert('keydown');
        }
      }
    });
    
    /*
    * Описание хранилища для связанной таблицы
    * ...VacPlan - План прививок
    */
    this.storeVacPlan = new Ext.data.JsonStore({
      fields: [
        'planTmp_id',
        'Date_Plan',
        'SurName',
        'FirName',
        'SecName',
        'sex',
        'BirthDay',
        'group_risk',
        'Lpu_id',
        'Lpu_Name',
        'Age',
        'type_name',
        'Name',
        'SequenceVac',
        'date_S',
        'date_E'
      ],
      url: '/?c=VaccineCtrl&m=loadVacPlan',
      root: 'data'
    });
    form.storeVacPlan.load(); //загружаем данные
    
    //var columnsVacPlan = new Ext.grid.ColumnModel([
    this.columnsVacPlan = [
        {header: 'planTmp_id', dataIndex: 'planTmp_id', sortable: true},
        {header: 'Date_Plan', dataIndex: 'Date_Plan', sortable: true},
        {header: 'SurName', dataIndex: 'SurName', sortable: true},
        {header: 'FirName', dataIndex: 'FirName', sortable: true},
        {header: 'SecName', dataIndex: 'SecName', sortable: true},
        {header: 'sex', dataIndex: 'sex', sortable: true},
        {header: 'BirthDay', dataIndex: 'BirthDay', sortable: true},
        {header: 'group_risk', dataIndex: 'group_risk', sortable: true},
        {header: 'Lpu_id', dataIndex: 'Lpu_id', sortable: true},
        {header: 'Lpu_Name', dataIndex: 'Lpu_Name', sortable: true},
        {header: 'Age', dataIndex: 'Age', sortable: true},
        {header: 'type_name', dataIndex: 'type_name', sortable: true},
        {header: 'Name', dataIndex: 'Name', sortable: true},
        {header: 'SequenceVac', dataIndex: 'SequenceVac', sortable: true},
        {header: 'date_S', dataIndex: 'date_S', sortable: true},
        {header: 'date_E', dataIndex: 'date_E', sortable: true}
    ];

    /*
    * Описание хранилища для связанной таблицы
    * ...VacMap - Список карт проф. прививок
    */
    this.storeVacMap = new Ext.data.JsonStore({
      fields: [
        'Person_id',
        'FirName',
        'SecName',
        'BirthDay',
        'Sex_id',
        'sex',
        'Address',
        'uch',
        'SocStatus_Name',
        'Lpu_id',
        'Lpu_Name',
        'group_risk'
      ],
      url: '/?c=VaccineCtrl&m=loadVacMap',
      root: 'data'
    });
    form.storeVacMap.load(); //загружаем данные
    
    //var columnsVacMap = new Ext.grid.ColumnModel([
    this.columnsVacMap = [
        {header: 'Person_id', dataIndex: 'Person_id', sortable: true},
        {header: 'FirName', dataIndex: 'FirName', sortable: true},
        {header: 'SecName', dataIndex: 'SecName', sortable: true},
        {header: 'BirthDay', dataIndex: 'BirthDay', sortable: true},
        {header: 'Sex_id', dataIndex: 'Sex_id', sortable: true},
        {header: 'sex', dataIndex: 'sex', sortable: true},
        {header: 'Address', dataIndex: 'Address', sortable: true},
        {header: 'uch', dataIndex: 'uch', sortable: true},
        {header: 'SocStatus_Name', dataIndex: 'SocStatus_Name', sortable: true},
        {header: 'Lpu_id', dataIndex: 'Lpu_id', sortable: true},
        {header: 'Lpu_Name', dataIndex: 'Lpu_Name', sortable: true},
        {header: 'group_risk', dataIndex: 'group_risk', sortable: true}
    ];

    this.gridVacPlan = new Ext.grid.GridPanel({
      id: 'journalsVacPlanGrid',
      region: 'center',
      //width: 400,
      maximized: true,
      floatable: false,
      store: form.storeVacPlan, //хранилище
      title: '',           //Заголовок
      columns: form.columnsVacPlan
    });
    
    this.gridVacMap = new Ext.grid.GridPanel({
      region: 'center',
      width: 200,
      floatable: false,
      store: form.storeVacMap, //хранилище
      title: 'Список карт проф. прививок',           //Заголовок
      columns: form.columnsVacMap
    });

//  var wincenter = Ext.extend(Ext.Window, {
//      region: 'center',
//      floatable: false,
//      title: 'Список карт проф. прививок',           //Заголовок
//  });
  
  //var wincenter = new sw.Promed.FormPanel({
//  var wincenter = new Ext.TabPanel({

		this.VacMapFiltersPanel = new Ext.Panel(
		{
			bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
			border: true,
			collapsible: false,
			region: 'north',
			height: 30,
			minSize: 30,
			maxSize: 30,
			layout: 'column',
			//title: 'Ввод',
			id: 'JournalsVacMapFiltersPanel',
//			keys: 
//			[{
//			}],
			items: 
			[]
		});

/*  this.wincenter = new Ext.Panel({
    //id: 'TRW_FormHeader',
    title: 'Список карт',
    region: 'center',
    border: false,
//	activeTab:0,
	//minTabWidth: 140,
	autoScroll: false,
    autoHeight: false,
    height: 400,
    //layout: 'border',
    items: [
       //grid
			{
				title: '1',
                //layout: 'fit',
				//xtype: 'hidden',
                //height: 400,
                maximized: true,
               	autoScroll: true,
                items: [
                  form.gridVacPlan
                ]
			},
			{
				title: '2',
                layout: 'fit',
				//xtype: 'hidden',
                items: [
                  form.gridVacMap
                ]
				//tabIndex: -1,
				//xtype: 'hidden',
				//id: 'lrLpuRegion_id'
			}
      ]
  });
*/
    
    Ext.apply(this, {
      title: 'Просмотр журналов вакцинации',
      closable: true,
      closeAction: 'hide',
      width: 600,
      minWidth: 350,
      height: 350,
      layout: 'border',
      bodyStyle: 'padding: 5px;',
      items: [
        //{name: 'grid0', xtype: 'grid'},
        //{name: 'grid', xtype: 'grid'}
       form.grid0,
//       form.wincenter
       //grid
       form.gridVacMap
      ]
    });
    
    sw.Promed.amm_testForm.superclass.initComponent.apply(this, arguments);

  },

  listeners: {
    'hide': function() {
      alert('hide!!');
      this.onHide();
    }
  },
  
  show: function() {
    sw.Promed.amm_testForm.superclass.show.apply(this, arguments);
  }
});