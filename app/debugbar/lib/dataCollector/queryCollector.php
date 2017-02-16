<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://club.shopex.cn/ ShopEx License
 */

use DebugBar\Bridge\DoctrineCollector;

class debugbar_dataCollector_queryCollector extends DoctrineCollector
{
    protected $explainQuery = false;
    protected $explainTypes = array('SELECT'); // array('SELECT', 'INSERT', 'UPDATE', 'DELETE'); for MySQL 5.6.3+    
    protected $timeCollector;
    protected $showHints = false;

    /**
     * @param TimeDataCollector $timeCollector
     */
    public function __construct($debugStackOrEntityManager, TimeDataCollector $timeCollector = null)
    {
        $this->timeCollector = $timeCollector;
        //        $debugStackOrEntityManager->addLogger();
        parent::__construct($debugStackOrEntityManager);
    }

    /**
     * Show or hide the hints in the parameters
     *
     * @param boolean $enabled
     */
    public function setShowHints($enabled = true)
    {
        $this->showHints = $enabled;
    }
    

    /**
     * {@inheritDoc}
     */
    public function collect()
    {
        $queries = array();
        $totalExecTime = 0;
        $queries = $this->debugStack->queries;
        $statements = [];
        
        foreach ($queries as $q) {
            $q = $this->processQuery($q['sql'], $q['params'], $q['startTime'], $q['endTime']);
            $bindings = $q['bindings'];
            if($q['hints']){
                $bindings['hints'] = $q['hints'];
            }

            $statements[] = array(
                'sql' => $q['query'],
                'params' => (object) $bindings,
                'duration' => $q['time'],
                'duration_str' => $this->formatDuration($q['time']),
            );

            foreach($q['explain'] as $explain){
                $statements[] = array(
                    'sql' => ' - EXPLAIN #' . $explain['id'] . ': `' . $explain['table'] . '` (' . $explain['select_type'] . ')',
                    'params' => $explain,
                    'row_count' => $explain['rows'],
                    'stmt_id' => $explain['id'],
                );
            }
            $totalExecTime += $q['time'];
        }
        return array(
            'nb_statements' => count($queries),
            'nb_failed_statements' => 0,
            'accumulated_duration' => $totalExecTime,
            'accumulated_duration_str' => $this->formatDuration($totalExecTime),
            'statements' => $statements
        );
    }

    /**
     * Enable/disable the EXPLAIN queries
     *
     * @param  bool $enabled
     * @param  array|null $types Array of types to explain queries (select/insert/update/delete)
     */
    public function setExplainSource($enabled, $types)
    {
        $this->explainQuery = $enabled;
        if($types){
            $this->explainTypes = $types;
        }
    }

    public function processQuery($query, $bindings, $startTime, $endTime)
    {
        $hints = $this->performQueryAnalysis($query);
        
        if ($this->explainQuery &&
            preg_match('/^('.implode($this->explainTypes, '|').') /i', $query) &&
            preg_match('/^\s*(?:(delete\s+from)|(insert\s+into)|(replace\s+into)|(update)|(select\s.*from))\s+([]0-9a-z_:"`.@[-]*)/is', $query, $match)) {
            $table = $match[6];
            $app = substr($table, 0, strpos($table, '_'));
            if ($app) {
                $db =  app::get($app)->database();
            } else {
                $db = db::connection();
            }
            $explainResults = $db->executeQuery('Explain '.$query, $bindings)->fetchAll();
        }
        
        if ($this->timeCollector !== null) {
            
            $this->timeCollector->addMeasure($query, $startTime, $endTime);
        }

        $bindings = $this->processBindings($bindings);

        return [
            'query' => $query,
            'bindings' => $this->escapeBindings($bindings),
            'time' => $endTime -$startTime,
            'source' => null,
            'explain' => $explainResults,
            'connection' => null,
            'hints' => $this->showHints ? $hints : null,
            
        ];
    }

    protected function processBindings($bindings)
    {
        $bindings = $this->checkBindings($bindings);
        $bindings = $this->escapeBindings($bindings);
        $processedBindings = [];

        collect($bindings)->each(function($item, $key) use (&$newBindings){
            $newBindings['param-'.$key] = $item;
        });
        return $newBindings;
    }
    


    /**
     * Check bindings for illegal (non UTF-8) strings, like Binary data.
     *
     * @param $bindings
     * @return mixed
     */
    protected function checkBindings($bindings)
    {
        foreach ($bindings as &$binding) {
            if (is_string($binding) && !mb_check_encoding($binding, 'UTF-8')) {
                $binding = '[BINARY DATA]';
            }
        }
        return $bindings;
    }
    

    /**
     * Make the params safe for outputting.
     *
     * @param array $bindings
     * @return array
     */
    protected function escapeBindings($bindings)
    {
        foreach ($bindings as &$binding) {
            $binding = htmlentities($binding, ENT_QUOTES, 'UTF-8', false);
        }
        return $bindings;
    }
    

    /**
     * Explainer::performQueryAnalysis()
     *
     * Perform simple regex analysis on the code
     *
     * @package xplain (https://github.com/rap2hpoutre/mysql-xplain-xplain)
     * @author e-doceo
     * @copyright 2014
     * @version $Id$
     * @access public
     * @param string $query
     * @return string
     */
    protected function performQueryAnalysis($query)
    {
        $hints = array();
        if (preg_match('/^\\s*SELECT\\s*`?[a-zA-Z0-9]*`?\\.?\\*/i', $query)) {
            $hints[] = 'Use <code>SELECT *</code> only if you need all columns from table';
        }
        if (preg_match('/ORDER BY RAND()/i', $query)) {
            $hints[] = '<code>ORDER BY RAND()</code> is slow, try to avoid if you can.
				You can <a href="http://stackoverflow.com/questions/2663710/how-does-mysqls-order-by-rand-work" target="_blank">read this</a>
				or <a href="http://stackoverflow.com/questions/1244555/how-can-i-optimize-mysqls-order-by-rand-function" target="_blank">this</a>';
        }
        if (strpos($query, '!=') !== false) {
            $hints[] = 'The <code>!=</code> operator is not standard. Use the <code>&lt;&gt;</code> operator to test for inequality instead.';
        }
        if (stripos($query, 'WHERE') === false && preg_match('/^(SELECT) /i', $query)) {
            $hints[] = 'The <code>SELECT</code> statement has no <code>WHERE</code> clause and could examine many more rows than intended';
        }
        if (preg_match('/LIMIT\\s/i', $query) && stripos($query, 'ORDER BY') === false) {
            $hints[] = '<code>LIMIT</code> without <code>ORDER BY</code> causes non-deterministic results, depending on the query execution plan';
        }
        if (preg_match('/LIKE\\s[\'"](%.*?)[\'"]/i', $query, $matches)) {
            $hints[] = 	'An argument has a leading wildcard character: <code>' . $matches[1]. '</code>.
								The predicate with this argument is not sargable and cannot use an index if one exists.';
        }
        return implode("<br />", $hints);
    }

        /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'queries';
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets()
    {
        return array(
            "queries" => array(
                "icon" => "inbox",
                "widget" => "PhpDebugBar.Widgets.SQLQueriesWidget",
                "map" => "queries",
                "default" => "[]"
            ),
            "queries:badge" => array(
                "map" => "queries.nb_statements",
                "default" => 0
            )
        );
    }

   
}