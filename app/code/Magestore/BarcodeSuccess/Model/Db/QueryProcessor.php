<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\BarcodeSuccess\Model\Db;

use Magestore\BarcodeSuccess\Api\Db\QueryProcessorInterface;

class QueryProcessor implements QueryProcessorInterface
{
       
    /**
     * @var array
     */
    private $_queryQueue = [];

    /**
     * @var \Magestore\BarcodeSuccess\Model\ResourceModel\Db\QueryProcessor
     */
    protected $_resource;
    
    /*
     * @var string
     */
    protected $defaultProcess = 'default';


    public function __construct(\Magestore\BarcodeSuccess\Model\ResourceModel\Db\QueryProcessor $resourceQueryProcessor)
    {
        $this->_resource = $resourceQueryProcessor;
    }
    
    /**
     * Add query to processor
     * 
     * @param array $queryData
     * @param string $process
     * @return \Magestore\BarcodeSuccess\Api\Db\QueryProcessorInterface
     */   
    public function addQuery($queryData, $process = null)
    {
        $process = $process ? $process : $this->defaultProcess;
        if(isset($this->_queryQueue[$process])) {
            $this->_queryQueue[$process][] = $queryData;
        } else {
            $this->_queryQueue[$process] = [$queryData];
        }
        return $this;
    }
    
    /**
     * Get queries in queue
     * 
     * @param string $process
     * @return array
     */   
    public function getQueryQueue($process = null)
    {
        $process = $process ? $process : $this->defaultProcess;
        return isset($this->_queryQueue[$process]) ? $this->_queryQueue[$process] : [];
    }
    
    /**
     * Start processing
     * 
     * @param string $process
     * @return \Magestore\BarcodeSuccess\Api\Db\QueryProcessorInterface
     */  
    public function start($process = null)
    {
        $this->resetQueue($process);
        return $this;
    }    

    /**
     * Process queries in queue
     * 
     * @param string $process
     * @return \Magestore\BarcodeSuccess\Api\Db\QueryProcessorInterface
     */  
    public function process($process = null)
    {
        $this->getResource()->processQueries($this->getQueryQueue($process));
        $this->resetQueue($process);
    }

    /**
     * Remove queries in the queue
     * 
     * @param string $process
     * @return \Magestore\BarcodeSuccess\Api\Db\QueryProcessorInterface
     */
    public function resetQueue($process = null)
    {
        $process = $process ? $process : $this->defaultProcess;        
        $this->_queryQueue[$process] = [];
        return $this;
    }
    
    /**
     * 
     * @return \Magestore\BarcodeSuccess\Model\ResourceModel\Db\QueryProcessor 
     */
    public function getResource()
    {
        return $this->_resource;
    }

}

