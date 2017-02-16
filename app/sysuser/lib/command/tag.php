<?php
class sysuser_command_tag extends base_shell_prototype{

        var $command_rebuildindex = "重建根据tag搜索用户的索引";
        public function command_rebuildindex(){
            kernel::single('sysuser_data_tag_index')->rebuildIndex();
            logger::info('Rebuild compelete!');
        }

}

