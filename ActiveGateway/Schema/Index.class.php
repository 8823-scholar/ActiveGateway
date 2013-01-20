<?php
/**
 * PHP version 5.
 *
 * Copyright (c) Samurai Framework Project, All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright notice,
 *       this list of conditions and the following disclaimer in the documentation
 *       and/or other materials provided with the distribution.
 *     * Neither the name of the Samurai Framework Project nor the names of its
 *       contributors may be used to endorse or promote products derived from this
 *       software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @package     ActiveGateway
 * @copyright   Samurai Framework Project
 * @link        http://samurai-fw.org/
 * @license     http://www.opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * index schema class.
 * 
 * @package     ActiveGateway
 * @subpackage  Schema
 * @copyright   Samurai Framework Project
 * @author      KIUCHI Satoshinosuke <scholar@hayabusa-lab.jp>
 * @license     http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class ActiveGateway_Schema_Index
{
    /**
     * index name.
     *
     * @access  public
     * @var     string
     */
    public $name;

    /**
     * target columns
     *
     * @access  protected
     * @var     array
     */
    protected $_columns = array();

    /**
     * container table name.
     *
     * @access  protected
     * @var     string
     */
    protected $_table_name;

    /**
     * schema.
     *
     * @access  protected
     * @var     ActiveGateway_Schema
     */
    protected $_schema;
    
    /**
     * mode.
     * default is "create".
     *
     * @access  private
     * @var     int
     */
    private $_mode = ActiveGateway_Schema::MODE_CREATE;


    /**
     * constructor.
     *
     * @access  public
     * @param   string  $table
     * @param   string  $column
     */
    public function __construct($table, $column)
    {
        $this->setTableName($table);
        if ( is_array($column) ) {
            foreach ( $column as $c ) $this->append($c);
        } else {
            $this->append($column);
        }
    }


    /**
     * set schema.
     *
     * @access  public
     * @param   ActiveGateway_Schema    $schema
     */
    public function setSchema(ActiveGateway_Schema $schema)
    {
        $this->_schema = $schema;
    }

    /**
     * get schema.
     *
     * @access  public
     * @return  ActiveGateway_Schema
     */
    public function getSchema()
    {
        return $this->_schema;
    }


    /**
     * get table name.
     *
     * @access  public
     * @return  string
     */
    public function getTableName()
    {
        return $this->_table_name;
    }

    /**
     * set table name.
     *
     * @access  public
     * @param   string  $table
     */
    public function setTableName($table)
    {
        $this->_table_name = $table;
    }


    /**
     * get index name.
     *
     * @access  public
     * @return  string
     */
    public function getName()
    {
        if ( $this->name ) return $this->name;

        // auto generate.
        $name = sprintf('index_%s_on_%s', $this->getTableName(), join('_and_', $this->_columns));
        return $name;
    }

    /**
     * set name.
     *
     * @access  public
     * @param   string  $name
     * @return  ActiveGateway_Schema_Index
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    
    
    /**
     * append target column
     *
     * @access  public
     * @param   string  $column_name
     * @return  ActiveGateway_Schema_Index
     */
    public function append($column_name)
    {
        if ( ! is_string($column_name) ) throw new ActiveGateway_Exception('Invalid column name.');
        if ( ! in_array($column_name, $this->_columns) ) {
            $this->_columns[] = $column_name;
        }
        return $this;
    }
    
    
    /**
     * set mode "drop".
     *
     * @access  public
     * @return  ActiveGateway_Schema_Table
     */
    public function drop()
    {
        $this->_mode = ActiveGateway_Schema::MODE_DROP;
        return $this;
    }


    /**
     * define to reverse.
     *
     * @access  public
     */
    public function revert()
    {
        // drop is can't revert.
        if ( $this->isDrop() ) {
            throw new ActiveGateway_Exception('drop is can not revert.');
        }

        $this->drop();
    }
    
    
    
    
    /**
     * convert to SQL.
     *
     * @access  public
     * @param   array   &$params
     */
    public function toSQL(array &$params)
    {
        $helper = $this->_schema->getHelper();
        $sql = $helper->indexToSql($this, $params);
        return $sql;
    }


    /**
     * convert to string.
     *
     * @access  public
     * @return  string
     */
    public function toString()
    {
        if ( $this->isDrop() ) {
            $string = sprintf('remove index: %s from %s', $this->getName(), $this->getTableName());
        } else {
            $string = sprintf('create index: %s to %s', $this->getName(), $this->getTableName());
        }
        return $string;
    }




    /**
     * get all columns.
     *
     * @access  public
     * @return  array
     */
    public function getColumns()
    {
        return $this->_columns;
    }


    /**
     * is drop
     *
     * @access  public
     * @return  boolean
     */
    public function isDrop()
    {
        return $this->_mode === ActiveGateway_Schema::MODE_DROP;
    }
}

