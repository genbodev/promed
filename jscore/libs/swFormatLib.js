/**
* sw.Promed.Format - Методы, для форматирования тех или иных данных.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      libs
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version      27.09.2009
*/

sw.Promed.Format = function(){
    return {
    	checkColumn: function(v, p, record)
		{
			var qtip = '';
			if (typeof this.qtip == 'function') {
				qtip = this.qtip(v, p, record);
				qtip = (qtip?'ext:qtip="'+qtip+'"':'');
			}
            if(!v){
                return "";
            }
			if ( !p )
			{
				if ( v == 'true' || String(v) == '2')
					return langs('Да')
				else	
					return langs('Нет')
			}
			p.css += ' x-grid3-check-col-td';
			if ( v == 'gray' )
				var style = 'x-grid3-check-col-on-non-border-gray';
			else
				if ( v == 'red' )
					var style = 'x-grid3-check-col-on-non-border-red';
				else if ( v == 'yellow' )
					var style = 'x-grid3-check-col-on-non-border-yellow';
				else if ( v == 'blue' )
					var style = 'x-grid3-check-col-on-non-border-blue';
				else if ( v == 'orange' )
					var style = 'x-grid3-check-col-on-non-border-orange';
				else
					var style = 'x-grid3-check-col-non-border'+((String(v)=='true' || String(v)=='2')?'-on':'');
			return '<div class="'+style+' x-grid3-cc-'+this.id+'" '+qtip+'>&#160;</div>';
		},
		//Столбец с отображением ...
		ItemNameColumn: function(v, p, record)
		{
            if(!record){
                return '';
            }
			var name;
			if (record.get('Item_Key')) {
				name = record.get('Item_Key').split('_')[0];
			}
			switch(name) {
				case 'XmlTemplate':
					return '<div class="x-grid3-xmltemplate-col-'+record.get('accessType')+' x-grid3-cc-'+this.id+'">'+v+'</div>';
				break;
				case 'XmlTemplateCat':
					return '<div class="x-grid3-xmltemplatecat-col-'+record.get('accessType')+' x-grid3-cc-'+this.id+'">'+v+'</div>';
				break;
				default:
					return v;
			}
		},
		//Столбец с отображением номера, даты и иконки эл.направления с хинтом
		dirNumColumn: function(v, p, record)
		{
            if(!record){
                return '';
            }
			var smmp = '';
			var title = '';
			if (record.get('SMMP_exists')) {
				smmp = 'x-grid3-smp-col ';
				title = ' title="'+record.get('SMMP_exists')+'"';
			}
			if(smmp.length > 0){
				return '<div class="'+smmp+'x-grid3-cc-'+this.id+'"'+title+'>&#160;</div>';
			}else if(v && record.get('EvnDirection_Num') && record.get('EvnDirection_setDate')){
				return '<div class="x-grid3-dir-col x-grid3-cc-'+this.id+'" title="'+v+'"><span style="color: #000079;">'+record.get('EvnDirection_setDate')+'</span> <span style="color: #000079; font-weight: bold; text-decoration: underline">'+record.get('EvnDirection_Num')+'</span></div>';
			} else if(record.get('EvnDirection_Num') && record.get('EvnDirection_setDate')){
				return '<div class="'+smmp+'x-grid3-cc-'+this.id+'"'+title+'><span style="color: #000079;">'+record.get('EvnDirection_setDate')+'</span> <span style="font-weight: bold;">'+record.get('EvnDirection_Num')+'</span></div>';
			}
			return '';
		},
		// Столбец с отображением данных о записи на бирку
		recordColumn: function(v, p, record)
		{
            if(!record){
                return '';
            }
			if(!v || !record.get('TimetableStac_insDT')){
				if(record.get('TimetableStac_setDate')) {
					dateStr = record.get('TimetableStac_setDate').slice(-5) == '00:00' ? record.get('TimetableStac_setDate').slice(0, -5) : record.get('TimetableStac_setDate');
					return '<div class="x-grid3-cc-'+this.id+'"><span style="color: #000079;">'+dateStr+'</span></div>';
				}
				if(record.get('EvnQueue_setDate'))
					return '<div class="x-grid3-cc-'+this.id+'">в очереди с <span style="color: #000079;">'+record.get('EvnQueue_setDate')+'</span></div>';
				if(record.get('DirType_id') == 5)
					return '<div class="x-grid3-cc-'+this.id+'">экстренно</div>';
				else
					return '<div class="x-grid3-cc-'+this.id+'">без записи</div>';
			} else {
				return '<div class="x-grid3-cc-'+this.id+'"><span style="color: #000079;">'+record.get('TimetableStac_insDT')+'</span> <b>'+record.get('EvnPS_CodeConv')+'</b></div>';
			}
		},
		//Столбец с отображением есть направление или нет
		dirColumn: function(v, p, record)
		{
			if(!v){
				return "";
			}
			if ( !p )
			{
				if ( v == 'true' )
					return langs('Да')
				else	
					return langs('Нет')
			}
			p.css += ' x-grid3-check-col-td';
			var style = 'x-grid3-dir-col-non-border'+((String(v)=='true')?'-on':'');
			return '<div class="'+style+' x-grid3-cc-'+this.id+'">&#160;</div>';
		},
		//Столбец с отображением часов - ожидание
		waitColumn: function(v, p, record)
		{
			if(!v){
				return "";
			}
			if ( !p )
			{
				if ( v == 'true' )
					return langs('Да')
				else	
					return langs('Нет')
			}
			p.css += ' x-grid3-check-col-td';
			var style = 'x-grid3-wait-col-non-border'+((String(v)=='true')?'-on':'');
			return '<div class="'+style+' x-grid3-cc-'+this.id+'">&#160;</div>';
		},
		/**
		* Форматирует число в рубли
		* @param {Number/String} число. 
		* @return {String} отформатированная строка. При пустой переданной строке будет возвращена пустая строка. 
		*/
		rurMoney: function(v){
			if (v==null) // если нет данных, то и обрабатывать нечего
				return v;
			
			v = (Math.round((v-0)*100))/100;
			v = (v == Math.floor(v)) ? v + ".00" : ((v*10 == Math.floor(v*10)) ? v + "0" : v);
			v = String(v);
			var ps = v.split('.');
			var whole = ps[0];
			var sub = ps[1] ? '.'+ ps[1] : '.00';
			var r = /(\d+)(\d{3})/;
			while (r.test(whole)) {
				whole = whole.replace(r, '$1' + ' ' + '$2');
			}
			v = whole + sub;
			return v;
		},
		/**
		* Используется в гриде, где нужен вывод галочки в одной строке, а в остальных данные выводятся без обработки.
		* Для грида, где есть региональный аптечный склад.
		* В случае, если v - отрицательное, число, то выводится галочка.
		*/
		checkColumnForRas: function(v, p, record)
		{
            if(!v){
                return "";
            }
			// для печатной формы
			if ( v < 0 && !p )
			{
				return langs('Да')
			}
			
			if (v < 0)
			{
				p.css += ' x-grid3-check-col-td';
				var style = 'x-grid3-check-col-non-border-on';
				return '<div class="'+style+' x-grid3-cc-'+this.id+'">&#160;</div>';
			}
			else
			{
				return v;
			}
		},
		/**
		 * Используется для вывода ячеек в матрице рабочего списка
		 */ 
		worksheetColumn: function(v, p, record)
		{
            if(!record){
                return '';
            }
			if(v) { // если значение не установлено
				return '<div class="x-grid3-cc-'+this.id+'" style="/*background-color:#efffef;*/height:40px;text-align:center;"><div style="margin-top:5px; height:100%;width:100%;">'+v+'</div></div>';
			} else {
				return '<div class="x-grid3-cc-'+this.id+'" style="color:#ccc;height:40px;text-align:center;"><span><br/>не занята</span></div>';
			}
		}
	}
}();


