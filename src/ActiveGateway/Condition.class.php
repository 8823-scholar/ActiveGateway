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
 * Condition class.
 *
 * $condition = ActiveGateway::getCondition();
 * $condition->where->foo = 'bar';
 * $condition->where->foo = $condition->isNotEqual('bar');
 * $condition->where->bar = $condition->isGreaterThan(10, true);
 * $condition->where->bar = $condition->isLessThan(10, false);
 * 
 * @package     ActiveGateway
 * @copyright   Samurai Framework Project
 * @author      KIUCHI Satoshinosuke <scholar@hayabusa-lab.jp>
 * @license     http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class ActiveGateway_Condition
{
    /**
     * SELECT
     *
     * @access   public
     * @var      mixed
     */
    public $select = '*';

    /**
     * FROM
     *
     * @access   public
     * @var      mixed
     */
    public $from = NULL;

    /**
     * WHERE
     *
     * @access   public
     * @var      mixed
     */
    public $where = NULL;

    /**
     * ORDER
     *
     * @access   public
     * @var      mixed
     */
    public $order = NULL;

    /**
     * GROUP
     *
     * @access   public
     * @var      mixed
     */
    public $group = NULL;

    /**
     * LIMIT
     *
     * @access   public
     * @var      int
     */
    public $limit = NULL;

    /**
     * B3M拡張
     * 実際に使用されるLIMIT
     *
     * @access  public
     * @var     int
     */
    public $limit_over = NULL;

    /**
     * OFFSET
     *
     * @access   public
     * @var      int
     */
    public $offset = NULL;

    /**
     * トータルローズを取得するかどうか
     *
     * @access   public
     * @var      boolean
     */
    public $total_rows = false;

    /**
     * 「active」フィールドを考慮するかどうか
     *
     * @access   public
     * @var      boolean
     */
    public $regard_active = true;

    /**
     * 行ロック
     *
     * @access  public
     * @var     boolean
     */
    public $for_update = false;

    /**
     * 値
     *
     * @access  public
     * @var     array
     */
    public $params = array();

    /**
     * 条件のついたキーを保管
     *
     * @access  private
     */
    private $_keys = array();

    /**
     * WHERE(addtional)
     *
     * このオプションは廃止予定です
     *
     * @access   public
     * @var      mixed
     */
    public $addtional_where = '';
    


    /**
     * constructor.
     *
     * @access  public
     */
    public function __construct()
    {
        $this->where = new ActiveGateway_Condition_Values();
        $this->where->setCondition($this);
    }



    /**
     * オーダーをセット
     *
     * @access  public
     * @param   string  $key
     * @param   string  $sort
     */
    public function setOrder($key, $sort = 'ASC')
    {
        if(!$this->order) $this->order = new stdClass();
        $this->order->$key = $sort;
    }


    /**
     * グループをセット
     *
     * @access  public
     * @param   string  $key
     */
    public function setGroup()
    {
        $keys = func_get_args();
        $this->group = $keys;
    }


    /**
     * set limit.
     *
     * @access  public
     * @param   int     $limit
     */
    public function setLimit($limit)
    {
        $this->limit = (int)$limit;
    }

    /**
     * get limit.
     *
     * @access  public
     * @return  int
     */
    public function getLimit()
    {
        return $this->limit;
    }


    /**
     * set offset.
     *
     * @access  public
     * @param   int     $offset
     */
    public function setOffset($offset, $is_pageid = false)
    {
        $this->offset = (int)$offset;
    }

    /**
     * set page.
     *
     * @access  public
     * @param   int     $page
     */
    public function setPage($page)
    {
        if ( $page < 0 ) $page = 0;
        $page = ( $page > 0 ) ? $page - 1 : 0 ;
        $offset = (int)$this->limit * $page;
        $this->setOffset($offset);
    }





    /**
     * add a bind param.
     *
     * @access  public
     * @param   mixied  $value
     * @param   string  $key
     * @return  string
     */
    public function addParam($value, $key = NULL)
    {
        $key = $key === NULL ? $this->_makeBindKey() : $key;
        $this->params[$key] = $value;
        return $key;
    }

    /**
     * get all bind params.
     *
     * @access  public
     * @retrun  array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * generate bind param key.
     *
     * @access  private
     * @return  string
     */
    private function _makeBindKey()
    {
        $key = sprintf(':param%d', count($this->params));
        return $key;
    }

    /**
     * clear bind params.
     *
     * @access  public
     */
    public function clearParams()
    {
        $this->params = array();
    }


    /**
     * add where key.
     *
     * @access  public
     * @param   string  $key
     */
    public function addKey($key)
    {
        $this->_keys[] = $key;
    }

    /**
     * has where key ?
     *
     * @access  public
     * @param   string  $key
     * @return  boolean
     */
    public function hasKey($key)
    {
        return in_array($key, $this->_keys);
    }





    /**
     * compare by "=".
     *
     * @access  public
     * @param   mixed   $value
     * @return  ActiveGateway_Condition_Value
     */
    public function isEqual($value)
    {
        $obj = new ActiveGateway_Condition_Value(NULL, $value);
        return $obj;
    }

    /**
     * compare by "!="
     *
     * @access  public
     * @param   mixed   $value
     * @return  ActiveGateway_Condition_Value
     */
    public function isNotEqual($value)
    {
        $obj = new ActiveGateway_Condition_Value(NULL, $value);
        $obj->operator = '!=';
        return $obj;
    }

    /**
     * compare by ">="
     *
     * @access  public
     * @param   mixed   $value
     * @param   boolean $within
     * @return  ActiveGateway_Condition_Value
     */
    public function isGreaterThan($value, $within = true)
    {
        $obj = new ActiveGateway_Condition_Value(NULL, $value);
        $obj->operator = '>';
        if($within) $obj->operator = '>=';
        return $obj;
    }

    /**
     * compare by "<="
     *
     * @access  public
     * @param   mixed   $value
     * @param   boolean $within
     * @return  ActiveGateway_Condition_Value
     */
    public function isLessThan($value, $within = true)
    {
        $obj = new ActiveGateway_Condition_Value(NULL, $value);
        $obj->operator = '<';
        if($within) $obj->operator = '<=';
        return $obj;
    }

    /**
     * compare by "LIKE".
     *
     * @access  public
     * @param   mixed    $value
     * @return  ActiveGateway_Condition_Value
     */
    public function isLike($value)
    {
        $obj = new ActiveGateway_Condition_Value(NULL, $value);
        $obj->operator = 'LIKE';
        return $obj;
    }
    
    /**
     * compare by "NOT LIKE".
     *
     * @access  public
     * @param   mixed   $value
     * @return  ActiveGateway_Condition_Value
     */
    public function isNotLike($value)
    {
        $obj = new ActiveGateway_Condition_Value(NULL, $value);
        $obj->operator = 'NOT LIKE';
        return $obj;
    }

    /**
     * compare by "&"
     *
     * @access  public
     * @param   mixed   $value
     * @return  ActiveGateway_Condition_Value
     */
    public function isBitAnd($value)
    {
        $obj = new ActiveGateway_Condition_Value(NULL, $value);
        $obj->operator = '&';
        return $obj;
    }


    /**
     * chain by "AND".
     *
     * @access  public
     * @param   mixed    $value
     * @return  ActiveGateway_Condition_Values
     */
    public function isAnd()
    {
        $obj = new ActiveGateway_Condition_Values();
        $obj->setCondition($this);
        $obj->_operator = 'AND';
        foreach(func_get_args() as $arg){
            $obj->append($arg);
        }
        return $obj;
    }

    /**
     * chain by "OR"
     *
     * @access  public
     * @param   mixed    $value
     * @return  ActiveGateway_Condition_Values
     */
    public function isOr()
    {
        $obj = new ActiveGateway_Condition_Values();
        $obj->setCondition($this);
        $obj->_operator = 'OR';
        foreach(func_get_args() as $arg){
            $obj->append($arg);
        }
        return $obj;
    }

    /**
     * chain by "IN (...)"
     *
     * @access  public
     * @param   mixed    $value
     * @return  ActiveGateway_Condition_Values
     */
    public function isIn()
    {
        $obj = new ActiveGateway_Condition_Values();
        $obj->setCondition($this);
        $obj->_operator = 'IN';
        foreach(func_get_args() as $arg){
            $obj->append($arg);
        }
        return $obj;
    }

    /**
     * chain by "NOT IN (...)"
     *
     * @access  public
     * @param   mixed   $value
     * @return  ActiveGateway_Condition_Values
     */
    public function isNotIn()
    {
        $obj = new ActiveGateway_Condition_Values();
        $obj->setCondition($this);
        $obj->_operator = 'NOT IN';
        foreach(func_get_args() as $arg){
            $obj->append($arg);
        }
        return $obj;
    }

    /**
     * able to use native SQL.
     *
     * @access  public
     * @param   string  $value
     * @param   array   $params
     */
    public function isNative($value, $params = array())
    {
        $obj = new ActiveGateway_Condition_Value(NULL, NULL);
        $obj->operator = 'NATIVE';
        $obj->key = $value;
        $obj->value = $params;
        return $obj;
    }
}

