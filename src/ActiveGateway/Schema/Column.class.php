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
 * column schema class.
 * 
 * @package     ActiveGateway
 * @subpackage  Schema
 * @copyright   Samurai Framework Project
 * @author      KIUCHI Satoshinosuke <scholar@hayabusa-lab.jp>
 * @license     http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class ActiveGateway_Schema_Column
{
    /**
     * column name.
     *
     * @access  public
     * @var     string
     */
    public $name;

    /**
     * type.
     *
     * @access  private
     * @var     string
     */
    private $_type = 'string';

    /**
     * type length.
     *
     * @access  private
     * @var     mixed
     */
    private $_type_length = NULL;

    /**
     * default value
     *
     * @access  private
     * @var     mixed
     */
    private $_default = NULL;

    /**
     * collation
     *
     * @access  private
     * @var     string
     */
    private $_collate;

    /**
     * enable contain null ?
     *
     * @access  private
     * @var     boolean
     */
    private $_enable_null = false;

    /**
     * auto increment.
     *
     * @access  private
     * @var     boolean
     */
    private $_auto_increment = false;

    /**
     * commnet
     *
     * @access  private
     * @var     string
     */
    private $_comment = '';

    /**
     * after
     *
     * @access  private
     * @var     string
     */
    private $_after = '';

    /**
     * container table.
     *
     * @access  public
     * @var     ActiveGateway_Schema_Table
     */
    private $_table;

    /**
     * container table name.
     *
     * @access  private
     * @var     string
     */
    private $_table_name;

    /**
     * schema
     *
     * @access  private
     * @var     ActiveGateway_Schema
     */
    private $_schema;
    
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
     * @param   string  $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }


    /**
     * get name.
     *
     * @access  public
     * @return  string
     */
    public function getName()
    {
        return $this->name;
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
        $this->setTableName($table->getName());
    }


    /**
     * set table name.
     *
     * @access  public
     * @param   string  $name
     */
    public function setTableName($name)
    {
        $this->_table_name = $name;
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
     * set a schema.
     *
     * @access  public
     * @param   ActiveGateway_Schema    $schema
     */
    public function setSchema(ActiveGateway_Schema $schema)
    {
        $this->_schema = $schema;
    }



    /**
     * set column type.
     * "string", "int", "text", and oathers.
     *
     * @access  public
     * @param   string  $type
     * @param   mixed   $length
     * @return  ActiveGateway_Schema_Column
     */
    public function type($type, $length = NULL)
    {
        $this->_type = $type;
        if ( $length === NULL ) {
            $helper = $this->_table->getHelper();
            $length = $helper->getDefaultLength($type);
        }
        $this->_type_length = $length;
        return $this;
    }


    /**
     * set default value.
     *
     * @access  public
     * @param   mixed   $default
     * @return  ActiveGateway_Schema_Column
     */
    public function defaultValue($default)
    {
        $this->_default = $default;
        return $this;
    }


    /**
     * set collation
     *
     * @access  public
     * @param   string  $collate
     * @return  ActiveGateway_Schema_Column
     */
    public function collate($collate)
    {
        $this->_collate = $collate;
        return $this;
    }


    /**
     * unable null
     *
     * @access  public
     * @return  ActiveGateway_Schema_Column
     */
    public function notNull()
    {
        $this->_enable_null = false;
        return $this;
    }

    /**
     * enable null
     *
     * @access  public
     * @return  ActiveGateway_Schema_Column
     */
    public function enableNull()
    {
        $this->_enable_null = true;
        return $this;
    }


    /**
     * auto increment.
     *
     * @access  public
     * @return  ActiveGateway_Schema_Column
     */
    public function autoIncrement()
    {
        $this->_auto_increment = true;
        return $this;
    }


    /**
     * set a comment
     *
     * @access  public
     * @param   string  $comment
     * @return  ActiveGateway_Schema_Column
     */
    public function comment($comment)
    {
        $this->_comment = str_replace(array("\r", "\n", "\t"), '', $comment);
        return $this;
    }


    /**
     * set after.
     *
     * @access  public
     * @param   string  $name
     */
    public function after($name)
    {
        $this->_after = $name;
    }


    /**
     * set a primary key.
     *
     * @access  public
     * @return  ActiveGateway_Schema_Column
     */
    public function primary()
    {
        $this->notNull();
        $this->autoIncrement();
        $this->_table->primary($this->getName());
        return $this;
    }


    /**
     * column defined end sign.
     *
     * @access  public
     * @return  ActiveGateway_Schema_Table
     */
    public function end()
    {
        return $this->_table;
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
     * @return  array   sql, params
     */
    public function toSQL(array &$params)
    {
        if ( $this->hasTable() ) {
            $helper = $this->_table->getHelper();
        } else {
            $helper = $this->_schema->getHelper();
        }
        $sql = $helper->columnToSql($this, $params);
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
            $string = sprintf('remove column: %s from %s', $this->getName(), $this->getTableName());
        } else {
            $string = sprintf('add column: %s to %s', $this->getName(), $this->getTableName());
        }
        return $string;
    }


    /**
     * convert to code.
     *
     * @access  public
     * @return  string
     */
    public function toCode()
    {
        $code = array();
        if ( $this->hasTable() ) {
            $code[] = sprintf('->column(\'%s\')', $this->getName());
            if ( $length = $this->getTypeLength() ) {
                if ( is_array($length) ) {
                    foreach ( $length as $_key => &$_val ) {
                        if ( $this->isStringTypeLength($this->getType()) ) {
                            $_val = "'" . $_val . "'";
                        }
                    }
                    $length = 'array(' . join(',', $length). ')';
                    $code[] = sprintf('->type(\'%s\', %s)', $this->getType(), $length);
                } else {
                    if ( $this->isStringTypeLength($this->getType()) ) {
                        $length = "'" . $length . "'";
                    }
                    $code[] = sprintf('->type(\'%s\', %s)', $this->getType(), $length);
                }
            } else {
                $code[] = sprintf('->type(\'%s\')', $this->getType());
            }
        }
        if ( $default = $this->getDefaultValue() ) {
            $code[] = sprintf('->defaultValue(\'%s\')', $default);
        }
        if ( $collate = $this->getCollate() ) {
            $code[] = sprintf('->collate(\'%s\')', $collate);
        }
        if ( $comment = $this->getComment() ) {
            $code[] = sprintf('->comment(\'%s\')', $comment);
        }

        return join('', $code);
    }



    /**
     * get type.
     *
     * @access  public
     * @return  string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * get type length.
     *
     * @access  public
     * @return  string
     */
    public function getTypeLength()
    {
        return $this->_type_length;
    }


    /**
     * get default value.
     *
     * @access  public
     * @return  mixed
     */
    public function getDefaultValue()
    {
        return $this->_default;
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
     * get after.
     *
     * @access  public
     * @return  string
     */
    public function getAfter()
    {
        return $this->_after;
    }


    /**
     * enable NULL ?
     *
     * @access  public
     * @return  boolean
     */
    public function isEnableNull()
    {
        return $this->_enable_null;
    }


    /**
     * is auto increment ?
     *
     * @access  public
     * @return  boolean
     */
    public function isAutoIncrement()
    {
        return $this->_auto_increment;
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


    /**
     * has table ?
     *
     * @access  public
     * @return  boolean
     */
    public function hasTable()
    {
        return $this->_table !== NULL;
    }


    /**
     * length is need string format ?
     *
     * @access  public
     * @param   string  $type
     * @return  boolean
     */
    public function isStringTypeLength($type)
    {
        switch ( $type ) {
        case ActiveGateway_Schema::COLUMN_TYPE_LIST:
            return true;
            break;
        default:
            return false;
            break;
        }
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

