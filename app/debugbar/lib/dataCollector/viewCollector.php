<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://club.shopex.cn/ ShopEx License
 */

use DebugBar\Bridge\Twig\TwigCollector;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\DataCollector\Util\ValueExporter;

class debugbar_dataCollector_viewCollector extends TwigCollector
{
    protected $templates = array();
    protected $collect_data;

    /**
     * Create a ViewCollector
     *
     * @param bool $collectData Collects view data when tru
     */
    public function __construct($collectData = true)
    {
        $this->collect_data = $collectData;
        $this->name = 'views';
        $this->templates = array();
        $this->exporter = new ValueExporter();
    }

    public function getName()
    {
        return 'views';
    }

    public function getWidgets()
    {
        return array(
            'views' => array(
                'icon' => 'leaf',
                'widget' => 'PhpDebugBar.Widgets.TemplatesWidget',
                'map' => 'views',
                'default' => '[]'
            ),
            'views:badge' => array(
                'map' => 'views.nb_templates',
                'default' => 0
            )
        );
    }

    /**
     * Add a View instance to the Collector
     *
     * @param \Illuminate\View\View $view
     */
    public function addView(base_view_view $view)
    {
        $name = $view->getName();
        $path = $view->getObject()->getPath();
        

        if (!$this->collect_data) {
            $params = array_keys($view->getData());
        } else {
            $data = array();
            foreach ($view->getData() as $key => $value) {
                $data[$key] = $this->exporter->exportValue($value);
            }
            $params = $data;
        }

        $this->templates[] = array(
            'name' => $path ? sprintf('%s (%s)', $name, $path) : $name,
            'param_count' => count($params),
            'params' => $params,
            //'type' => $type,
        );
    }

    public function collect()
    {
        $templates = $this->templates;

        return array(
            'nb_templates' => count($templates),
            'templates' => $templates,
        );
    }
}

