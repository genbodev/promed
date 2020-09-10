/**
* swYandexMap - окно редактирования адреса.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*/

sw.Promed.swYandexMap = Ext.extend(sw.Promed.BaseForm, {
	id: 'swYandexMap',
	layout: 'border',
	maximizable: false,
	width: 1000,
	height: 400,
	modal: true,
	codeRefresh: true,
	objectName: 'swYandexMap',
	objectSrc: '/jscore/Forms/Yandex/swYandexMap.js',
	title : 'Координаты подразделения на карте',
	returnFunc: function(owner) {},
	Annotation_id: null,
	MedStaffFact_id: null,
	MedService_id: null,
	Resource_id: null,
	coordinates : {
		lat : '0.00',
		lng : '0.00'
	},
	show: function(arguments) {		
		sw.Promed.swYandexMap.superclass.show.apply(this, arguments);

		arguments.lat = parseFloat(arguments.lat);
		arguments.lng = parseFloat(arguments.lng);

		var win = this;
		win.arguments = arguments;

		$('#map').empty();

		$.getScript('//api-maps.yandex.ru/2.1/?lang=ru_RU').done(function() {
  			 	
			ymaps.ready(init);
	
				function init () {

					if(typeof myMap != 'undefined') alert('isset');

				    var myMap = new ymaps.Map("map", {

				            center : [win.arguments.lat, win.arguments.lng],
				            zoom   : 10
				        
				        }, {
				            searchControlProvider: 'yandex#search'
				        }),
				
				    	// Создаем геообъект с типом геометрии "Точка".
				        myGeoObject = new ymaps.GeoObject({
				            // Описание геометрии.
				            geometry: {
				                type: "Point",
				                coordinates: [win.arguments.lat, win.arguments.lng]
				            },
				            // Свойства.
				            properties: {
				                // Контент метки.
				                //iconContent: '',
				                //hintContent: ''
				            }
				        }, {
				            // Опции.
				            // Иконка метки будет растягиваться под размер ее содержимого.
				            //preset: 'islands#blackStretchyIcon',
				            // Метку можно перемещать.
				            //draggable: true
				        });
				
				    myMap.geoObjects.add(new ymaps.Placemark([win.arguments.lat, win.arguments.lng], {
				            //balloonContent: '<strong>content</strong>'
				        }, {
				            preset: 'islands#circleIcon',
				            iconColor: '#52A4FF'
				        }));
				}
  			})
  			.fail(function(){
  			  console.log('script could not load');
  		});

	},

	doSave: function() {

	},

	listeners : {

		close : function () {

		} 
	},

	initComponent: function() {

		var win = this;

		this.MainPanel = new Ext.form.FormPanel({
			id:'mapContainer',
			border: false,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			bodyStyle: 'padding: 10px 5px 0',
			region: 'center',
			labelAlign: 'right',
			labelWidth: 120,
			items:
			[{
				html: '<div id="map" style="height: 300px"></div>',
            	xtype: "panel"
			}],
		});
		
		Ext.apply(this, 
		{
			xtype: 'panel',
			border: false,
			items: [this.MainPanel],
			buttons:
			[{
				text:'-'
			},
			{
				text:'-'
			}, 
			{
				text:'-'
			},
			{
				text: 'Закрыть',
				iconCls: 'cancel16',
				handler: function()
				{
					this.hide();
				}.createDelegate(this)
			}]
		});

		sw.Promed.swYandexMap.superclass.initComponent.apply(this, arguments);
	}
});