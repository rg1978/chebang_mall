<?php
	class syscategory_mdl_prop_values extends dbeav_model {

	    public function _filter($filter,$tableAlias=null,$baseWhere=null)
	    {
		    if (empty($filter['status']))
		    {
	   		    $filter['status'] = 'active';
		    }
		    $filter = parent::_filter($filter);
        	return $filter;
	    }
	}

