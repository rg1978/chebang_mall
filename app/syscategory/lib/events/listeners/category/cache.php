<?php
class syscategory_events_listeners_category_cache implements base_events_interface_queue
{

    public function clear($data)
    {
        kernel::single('syscategory_data_cat')->cleanCatsCache();
        return true;
    }

}

