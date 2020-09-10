/** 
 * this function determines whether the event is the equivalent of the microsoft mouseleave or mouseenter events.
 * http://dynamic-tools.net/toolbox/isMouseLeaveOrEnter/
 * 
 */
function isMouseLeaveOrEnter(e, handler) {
	if (e.type != 'mouseout' && e.type != 'mouseover') return false;
	if (e.type == 'mouseout' && typeof handler.querySelector == 'function') {
		// fix for chrome, не скрываем панель, если внутри есть кнопка с активным меню, иначе меню прячется
		var el = handler.querySelector('.emd-here');
		if (el) {
			var elMenu = el.querySelector('.x6-btn-menu-active');
			if (elMenu) {
				return false;
			}
		}
	}
	var reltg = e.relatedTarget ? e.relatedTarget : e.type == 'mouseout' ? e.toElement : e.fromElement;
	while (reltg && reltg != handler) reltg = reltg.parentNode;
	return (reltg != handler);
}
