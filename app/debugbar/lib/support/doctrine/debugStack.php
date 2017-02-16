<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://club.shopex.cn/ ShopEx License
 */

use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Logging\SQLLogger;

class debugbar_support_doctrine_debugStack extends DebugStack
{
    /**
     * Executed SQL queries.
     *
     * @var array
     */
    public $queries = array();

    /**
     * @var integer
     */
    public $currentQuery = 0;

    /**
     * {@inheritdoc}
     */
    
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $start = microtime(true);
        $this->queries[++$this->currentQuery] = ['sql' => $sql, 'params' => $params, 'types' => $types,'startTime' => $start];
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        $end = microtime(true);
        $start = $this->queries[$this->currentQuery]['startTime'];
        $this->queries[$this->currentQuery]['endTime'] = $end;
    }
}

