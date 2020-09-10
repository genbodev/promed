        <div class="row">
          <!-- <div class="col-md-3 left-menu">
            <div class="list-group"><a class="list-group-item list-group-item-action d-flex align-items-center active" href="javascript:void(0);"><span class="icon mr-3"><i class="fe fe-git-pull-request"></i></span>Изменения</a><a class="list-group-item list-group-item-action d-flex align-items-center" href="tags.html"><span class="icon mr-3"><i class="fe fe-hash"></i></span>Теги</a></div>
          </div> -->
          <div class="col-md-9">
            <h3>Новости</h3>
            <div class="card">
              <div class="table-responsive table-responsive--rtmis">
                <div class="dataTables_wrapper no-footer" id="DataTables_Table_0_wrapper">
                  <table class="table table-hover table-outline table-vcenter card-table">
                    <thead>
                      <th>Дата</th>
                      <th>Событие</th>
                      <th class="add-button-th"><a class="dropdown-item" href="?c=portalAdmin&m=news_add" title="Добавить"><i class="dropdown-icon fe fe-plus">   </i></a></th>
                    </thead>
                    <tbody>
						<?php echo $news_entries ?>
                    </tbody>
                  </table>
                  <!-- 
				  <div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite">1 — 10 из 32 записей</div>
                  <div class="dataTables_paginate paging_simple_numbers" id="DataTables_Table_0_paginate"><a class="paginate_button previous disabled" aria-controls="DataTables_Table_0" data-dt-idx="0" tabindex="0" id="DataTables_Table_0_previous">Предыдущая</a><a class="paginate_button current" aria-controls="DataTables_Table_0" data-dt-idx="1" tabindex="0" href="#">1</a><a class="paginate_button" aria-controls="DataTables_Table_0" data-dt-idx="2" tabindex="0" href="#">2</a><a class="paginate_button" aria-controls="DataTables_Table_0" data-dt-idx="2" tabindex="0" href="#">3</a><a class="paginate_button" aria-controls="DataTables_Table_0" data-dt-idx="2" tabindex="0" href="#">4</a><a class="paginate_button next" aria-controls="DataTables_Table_0" data-dt-idx="3" tabindex="0">Следующая</a></div>
					-->
				</div>
              </div>
            </div>
          </div>
        </div>
