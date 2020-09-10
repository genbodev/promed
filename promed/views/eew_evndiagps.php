<div id="EvnDiagPSList_{pid}" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDiagPSList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDiagPSList_{pid}_toolbar').style.display='none'">
    <div class="clear">

        <div class="data-table">
            <div class="caption">
                <h2>Сопутствующие диагнозы отделения</h2>
                <div id="EvnDiagPSList_{pid}_toolbar" class="toolbar">
                    <a id="EvnDiagPSList_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
                </div>
            </div>

            <table>

                <col class="first last" />
                <col class="toolbar"/>

                {items}

            </table>

        </div>

    </div>
</div>