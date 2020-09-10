<div id="AllergHistory_{pid}" class="data-table component" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('AllergHistory_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('AllergHistory_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h2>Аллергологический анамнез</h2>
        <div id="AllergHistory_{pid}_toolbar" class="toolbar">
            <a id="AllergHistoryList_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
            <a id="AllergHistory_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table>

        <col style="width: 15%" class="first" />
        <col />
        <col />
        <col class="last">
        <col class="toolbar"/>

        <thead>
            <tr>
                <th>Дата возникновения реакции</th>
                <th>Тип аллергической реакции</th>
                <th>Вид аллергена</th>
                <th>Характер аллергической реакции</th>
                <th class="toolbar"></th>
            </tr>
        </thead>

        <tbody id="AllergHistoryList_{pid}">

            {items}

        </tbody>

    </table>

</div>

<!--
	Схема шаблона
    
	AllergHistory_{Person_id} div class="section"
	{
		AllergHistory_{Person_id}_toolbar
		{
			AllergHistory_{Person_id}_print (будет выведено на печать все содердимое ноды AllergHistory_{Person_id})
		}
		AllergHistoryList_{Person_id}_toolbar
		{
			AllergHistoryList_{Person_id}_add (после добавления в ноду AllergHistoryList_{Person_id} будет добавлена дочерная нода - секция нового случая аллергической реакции)
		}
		AllergHistoryList_{Person_id} tbody class="section"
		{
			Шаблон eew_allergic_reaction_item, чтобы была возможность обновить всю секцию PersonAllergicReaction_{PersonAllergicReaction_id}
			PersonAllergicReaction_{PersonAllergicReaction_id} tr class="section"
			{
				PersonAllergicReaction_{PersonAllergicReaction_id}_toolbar
				{
					PersonAllergicReaction_{PersonAllergicReaction_id}_edit (после сохранения должна быть обновлена вся секция PersonAllergicReaction_{PersonAllergicReaction_id}
					PersonAllergicReaction_{PersonAllergicReaction_id}_del (после удаления будет удалена вся секция PersonAllergicReaction_{PersonAllergicReaction_id})
				}
			}
			конец шаблона eew_allergic_reaction_item
		}
	}
-->