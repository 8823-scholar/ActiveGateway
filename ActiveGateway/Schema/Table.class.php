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
 * table schema class.
 * 
 * @package     ActiveGateway
 * @subpackage  Schema
 * @copyright   Samurai Framework Project
 * @author      KIUCHI Satoshinosuke <scholar@hayabusa-lab.jp>
 * @license     http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class ActiveGateway_Schema_Table
{
    /**
     * table name.
     *
     * @access  public
     * @var     string
     */
    public $name;

    /**
     * columns
     *
     * @access  private
     * @var     array
     */
    private $_columns = array();

    /**
     * keys
     *
     * @access  private
     * @var     array
     */
    private $_keys = array();

    /**
     * engine
     *
     * @access  private
     * @var     string
     */
    private $_engine;

    /**
     * charset
     *
     * @access  private
     * @var     string
     */
    private $_charset;

    /**
     * collation
     *
     * @access  private
     * @var     string
     */
    private $_collate;

    /**
     * schema
     *
     * @access  private
     * @var     ActiveGateway_Schema
     */
    private $_schema;


    /**
     * constructor.
     *
     * @access  public
     * @param   string  $name
     */
    public function __construct($name)
    {
        $this->name = $name;
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
     * getSchema
     *
     * @access  public
     * @return  ActiveGateway_Schema
     */
    public function getSchema()
    {
        return $this->_schema;
    }



    /**
     * add column.
     *
     * @access  public
     * @param   string  $name
     * @return  ActiveGateway_Schema_Column
     */
    public function column($name)
    {
        $column = new ActiveGateway_Schema_Column($name);
        $column->setTable($this);
        $this->_columns[$name] = $column;
        return $column;
    }



    /**
     * add primary key.
     *
     * @access  public
     * @param   string  $column_name
     * @return  ActiveGateway_Schema_Primary
     */
    public function primary($column_name)
    {
        $primary = new ActiveGateway_Schema_Primary($column_name);
        $this->_keys[] = $primary;
        $primary->setTable($this);
        return $primary;
    }


    /**
     * add unique key.
     *
     * @access  public
     * @param   string  $column_name
     * @return  ActiveGateway_Schema_Unique
     */
    public function unique($column_name)
    {
        $unique = new ActiveGateway_Schema_Unique($column_name);
        $this->_keys[] = $unique;
        $unique->setTable($this);
        return $unique;
    }



    /**
     * set engine.
     *
     * @access  public
     * @param   string  $name
     * @return  ActiveGateway_Schema_Table
     */
    public function engine($name)
    {
        $this->_engine = $name;
        return $this;
    }


    /**
     * set charset.
     *
     * @access  public
     * @param   string  $charset
     * @return  ActiveGateway_Schema_Table
     */
    public function charset($charset)
    {
        $this->_charset = $charset;
        return $this;
    }
    
    
    /**
     * set collate.
     *
     * @access  public
     * @param   string  $collate
     * @return  ActiveGateway_Schema_Table
     */
    public function collate($collate)
    {
        $this->_collate = $collate;
        return $this;
    }


    /**
     * set comment.
     *
     * @access  public
     * @param   string  $comment
     * @return  ActiveGateway_Schema_Table
     */
    public function comment($comment)
    {
        $this->_comment = $comment;
        return $this;
    }





    /**
     * convert to SQL.
     *
     * @access  public
     * @return  array   sql, params
     */
    public function toSQL(array &$params)
    {
        $helper = $this->getHelper();
        $sql = $helper->tableToSQL($this, $params);
        return $sql;
    }


    /**
     * get table name.
     *
     * @access  public
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * get defined columns.
     *
     * @access  public
     * @return  array
     */
    public function getColumns()
    {
        return $this->_columns;
    }


    /**
     * get defined keys.
     *
     * @access  public
     * @return  array
     */
    public function getKeys()
    {
        return $this->_keys;
    }


    /**
     * get engine
     *
     * @access  public
     * @return  string
     */
    public function getEngine()
    {
        return $this->_engine;
    }


    /**
     * get charset.
     *
     * @access  public
     * @return  string
     */
    public function getCharset()
    {
        return $this->_charset;
    }


    /**
     * get collate.
     *
     * @access  public
     * @return  string
     */
    public function getCollate()
    {
        return $this->_collate;
    }


    /**
     * get comment.
     *
     * @access  public
     * @return  string
     */
    public function getComment()
    {
        return $this->_comment;
    }


    /**
     * get helper.
     *
     * @access  public
     * @return  ActiveGateway_Helper
     */
    public function getHelper()
    {
        $AGManager = ActiveGateway_Manager::singleton();
        $AG = $AGManager->getActiveGateway($this->getSchema()->getAlias());
        $helper = $AGManager->getHelper($AG);
        return $helper;
    }
}

