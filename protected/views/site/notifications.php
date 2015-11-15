                                   <ul style="list-style-type: none; padding-left:15px; padding-right:15px;">
                                    <?php
                                    $this->widget('zii.widgets.CListView', array(
                                        'dataProvider'=>$notifications,
                                        'id'=>'red_plum_noti_list2',
                                        'itemView'=>'application.views.site._notification',
                                        'template'=>'{items}{pager}',
                                        'emptyText'=>'<p><center>目前还没有新消息</center></p>',
                                    ));
                                    ?>
                                   </ul>