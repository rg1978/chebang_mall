<?php

class topdev_menu {

    public function getMenu()
    {
        $menu = array();
        foreach($this->menu as $menuPath => $menuRow)
        {
            $menu = array_merge_recursive($menu, $this->format($menuPath, $menuRow));
        }

        foreach( $menu as $gourpName=>&$row )
        {
            $row = $this->preMenu($row);
        }
        return $menu;
    }

    public function preMenu($menuRow)
    {
        foreach( $menuRow as $key=>$val)
        {
            if( is_string(key) && ! in_array($key,['name','tag','menu','href','icon']) )
            {
                $val = $this->preMenu($val);
                $menuRow['menu'][] = $val;
                unset($menuRow[$key]);
            }
        }

        return $menuRow;
    }

    public function format($menuPath, $menuRow)
    {
        $menuPathArr = explode(':', $menuPath);
        $result = null;
        for($i = count($menuPathArr); $i >= 0; $i--) {
            if( $i === count($menuPathArr) )
            {
                $menuRow['name'] = last(explode(':', $menuRow['name']));
                $result = $menuRow;
            }
            else
            {
                $result = array($menuPathArr[$i] => $result);
            }
        }
        return $result;
    }

	public function group(array $attributes, Closure $callback)
	{
		$this->updateGroupStack($attributes);

		call_user_func($callback, $this);

		array_pop($this->groupStack);

        return $this;
	}

	protected function updateGroupStack(array $attributes)
	{
        if ( ! empty($this->groupStack))
        {
            $attributes = $this->mergeGroup($attributes, last($this->groupStack));
        }

		$this->groupStack[] = $attributes;
	}

	public function mergeGroup($new, $old)
	{
        $oldGroupName = isset($old['group_name']) ? $old['group_name'] : null;

        if (isset($new['group_name']))
        {
            $new['group_name'] = trim($oldGroupName, ':').':'.trim($new['group_name'], ':');
            return $new;
        }

        return $oldGroupName;
	}

    public function add($name, $action, $tag, $icon)
    {
        if ($this->hasGroupStack())
        {
            $groupAttributes = last($this->groupStack);
            $menu['name'] = $name;
            if( is_array($action) )
            {
                $menu['href'] = url::action($action[0], $action[1]);
            }
            else
            {
                $menu['href'] = url::action($action);
            }
            $menu['tag'] = $tag;
            $menu['icon'] = $icon;
            $this->menu[$groupAttributes['group_name']]['name'] = $groupAttributes['group_name'];
            $this->menu[$groupAttributes['group_name']]['tag'] = $groupAttributes['tag'];
            $this->menu[$groupAttributes['group_name']]['icon'] = $groupAttributes['icon'];
            $this->menu[$groupAttributes['group_name']]['menu'][] = $menu;
        }
        return $this;
    }

    public function hasGroupStack()
    {
        return ! empty($this->groupStack);
    }
}
