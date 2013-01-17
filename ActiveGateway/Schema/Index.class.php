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
     * container table.
     *
     * @access  protected
     * @var     ActiveGateway_Schema_Table
     */
    protected $_table;


    /**
     * constructor.
     *
     * @access  public
     * @param   string  $column_name
     */
    public function __construct($column_name)
    {
        $this->append($column_name);
    }


    /**
     * set table.
     *
     * @access  public
     * @param   ActiveGateway_Schema_Table
     */
    public function setTable(ActiveGateway_Schema_Table $table)
    {
        $this->_table = $table;
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
        $name = sprintf('index_%s_on_%s', $this->_table->getName(), join('_and_', $this->_columns));
        return $name;
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
        if ( ! in_array($column_name, $this->_columns) ) {
            $this->_columns[] = $column_name;
        }
    }


    /**
     * index defined end sign.
     *
     * @access  public
     * @return  ActiveGateway_Schema_Table
     */
    public function end()
    {
        return $this->_table;
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
     * called undefined method.
     *
     * @access  public
     * @param   string  $method
     * @param   array   $args
     */
    public function __call($method, array $args = array())
    {
        return call_user_func_array(array($this->_table, $method), $args);
    }
}

