<div id="BloodData_{pid}" class="data-table component" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('BloodData_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('BloodData_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h2>Группа крови и Rh-фактор</h2>
        <div id="BloodData_{pid}_toolbar" class="toolbar">
            <a id="BloodDataList_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
            <a id="BloodData_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table>

        <col class="first" />
        <col />
        <!--col /-->
        <col class="last" />
        <col class="toolbar"/>

        <thead>
            <tr>
                <th>Группа крови</th>
                <th>Резус-фактор</th>
                <th>Дата определения</th>
                <!--th>Автор</th-->
                <th class="toolbar"></th>
            </tr>
        </thead>

        <tbody id="BloodDataList_{pid}">

            {items}

        </tbody>

    </table>

</div>

<!--
	Схема шаблона
	BloodData_{Person_id} div class="section"
	{
		BloodData_{Person_id}_toolbar
		{
			BloodData_{Person_id}_print (будет выведено на печать все содердимое ноды BloodData_{Person_id})
		}
		BloodDataList_{Person_id}_toolbar
		{
			BloodDataList_{Person_id}_add (после добавления в ноду BloodDataList_{Person_id} будет добавлена дочерная нода - секция нового случая определения группы крови и резус-фактора)
		}
		BloodDataList_{Person_id} tbody class="section"
		{
			Шаблон eew_blooddata_item, чтобы была возможность обновить всю секцию PersonBloodGroup_{PersonBloodGroup_id}
			PersonBloodGroup_{PersonBloodGroup_id} tr class="section"
			{
				PersonBloodGroup_{PersonBloodGroup_id}_toolbar
				{
					PersonBloodGroup_{PersonBloodGroup_id}_edit (после сохранения должна быть обновлена вся секция PersonBloodGroup_{PersonBloodGroup_id}
					PersonBloodGroup_{PersonBloodGroup_id}_del (после удаления будет удалена вся секция PersonBloodGroup_{PersonBloodGroup_id})
				}
			}
			конец шаблона eew_blooddata_item
		}
	}
-->