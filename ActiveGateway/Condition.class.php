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
 * ActiveGatewayにおいて、検索条件を保持するクラス
 *
 * <code>
 *     $condition = ActiveGateway::getCondition();
 *     $condition->where->foo = 'bar';
 *     $condition->where->foo = $condition->isNotEqual('bar');
 *     $condition->where->bar = $condition->isGreaterThan(10, true);
 *     $condition->where->bar = $condition->isLessThan(10, false);
 * </code>
 * 
 * @package     ActiveGateway
 * @copyright   Samurai Framework Project
 * @author      KIUCHI Satoshinosuke <scholar@hayabusa-lab.jp>
 * @license     http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class ActiveGatewayCondition
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
     * マーカー
     *
     * @access  public
     * @param   string
     */
    public $marker = '';

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
     * コンストラクタ
     *
     * @access  public
     */
    public function __construct()
    {
        $this->where = new ActiveGatewayCondition_Values();
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
     * リミットをセット
     *
     * @access     public
     * @param      int     $limit   リミット
     */
    public function setLimit($limit)
    {
        $this->limit = (int)$limit;
        $this->limit_over = $this->limit;
    }

    /**
     * オーバーリミットをセット
     *
     * @access  public
     * @param   int     $limit
     */
    public function setOverLimit($limit)
    {
        $this->setLimit($limit);
        $this->limit_over += 1;
    }

    /**
     * リミットを取得
     *
     * @access  public
     * @return  int
     */
    public function getLimit()
    {
        if($this->limit_over !== NULL){
            return $this->limit_over;
        } else {
            return $this->limit;
        }
    }


    /**
     * offset値の設定
     *
     * @access     public
     * @param      int      $offset
     * @param      boolean  $is_pageid
     */
    public function setOffset($offset, $is_pageid = false)
    {
        $offset = (int)$offset;
        if($is_pageid){
            $offset = ($offset > 0) ? $offset - 1 : 0 ;
            $offset = (int)$this->limit * $offset;
        }
        if($offset < 0) $offset = 0;
        $this->offset = $offset;
    }

    /**
     * pageをセット
     *
     * @access  public
     * @param   int     $page
     */
    public function setPage($page)
    {
        $this->setOffset($page, true);
        $this->total_rows = !$this->isOverLimit();
    }


    /**
     * マーカーをセット
     *
     * @access  public
     * @param   string  $marker
     */
    public function setMarker($marker)
    {
        $this->marker = $marker;
    }





    /**
     * 値を追加する
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
     * 値をすべて取得
     *
     * @access  public
     * @retrun  array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * バインドパラムのキーを生成
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
     * 値をクリア
     *
     * @access  public
     */
    public function clearParams()
    {
        $this->params = array();
    }


    /**
     * 条件のついたキーを追加
     *
     * @access  public
     * @param   string  $key
     */
    public function addKey($key)
    {
        $this->_keys[] = $key;
    }

    /**
     * 条件のついたキーを保持しているか
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
     * オーバーリミットかどうか
     *
     * @access  public
     * @return  boolean
     */
    public function isOverLimit()
    {
        return $this->limit_over !== NULL && $this->limit_over > $this->limit;
    }





    /**
     * = 比較
     *
     * @access     public
     * @param      mixed    $value
     * @return     object   ActiveGatewayCondition_Value
     */
    public function isEqual($value)
    {
        $obj = new ActiveGatewayCondition_Value(NULL, $value);
        return $obj;
    }

    /**
     * != 比較
     *
     * @access     public
     * @param      mixed    $value
     * @return     object   ActiveGatewayCondition_Value
     */
    public function isNotEqual($value)
    {
        $obj = new ActiveGatewayCondition_Value(NULL, $value);
        $obj->operator = '!=';
        return $obj;
    }

    /**
     * >= 比較
     *
     * @access     public
     * @param      mixed    $value
     * @param      boolean  $within    =がつくかどうか
     * @return     object   ActiveGatewayCondition_Value
     */
    public function isGreaterThan($value, $within = true)
    {
        $obj = new ActiveGatewayCondition_Value(NULL, $value);
        $obj->operator = '>';
        if($within) $obj->operator = '>=';
        return $obj;
    }

    /**
     * <= 比較
     *
     * @access     public
     * @param      mixed    $value
     * @param      boolean  $within   =がつくかどうか
     */
    public function isLessThan($value, $within = true)
    {
        $obj = new ActiveGatewayCondition_Value(NULL, $value);
        $obj->operator = '<';
        if($within) $obj->operator = '<=';
        return $obj;
    }

    /**
     * LIKE 比較
     *
     * @access     public
     * @param      mixed    $value
     * @return     object   ActiveGatewayCondition_Value
     */
    public function isLike($value)
    {
        $obj = new ActiveGatewayCondition_Value(NULL, $value);
        $obj->operator = 'LIKE';
        return $obj;
    }
    
    /**
     * NOT LIKE 比較
     *
     * @access     public
     * @param      mixed    $value
     * @return     object   ActiveGatewayCondition_Value
     */
    public function isNotLike($value)
    {
        $obj = new ActiveGatewayCondition_Value(NULL, $value);
        $obj->operator = 'NOT LIKE';
        return $obj;
    }

    /**
     * BIT演算 AND
     *
     * @access     public
     * @param      mixed    $value
     * @return     object   ActiveGatewayCondition_Value
     */
    public function isBitAnd($value)
    {
        $obj = new ActiveGatewayCondition_Value(NULL, $value);
        $obj->operator = '&';
        return $obj;
    }


    /**
     * AND連結
     *
     * @access     public
     * @param      mixed     $value   ...
     * @return     object   ActiveGatewayCondition_Values
     */
    public function isAnd()
    {
        $obj = new ActiveGatewayCondition_Values();
        $obj->setCondition($this);
        $obj->_operator = 'AND';
        foreach(func_get_args() as $arg){
            $obj->append($arg);
        }
        return $obj;
    }

    /**
     * OR連結
     *
     * @access     public
     * @param      mixed     $value   ...
     * @return     object   ActiveGatewayCondition_Values
     */
    public function isOr()
    {
        $obj = new ActiveGatewayCondition_Values();
        $obj->setCondition($this);
        $obj->_operator = 'OR';
        foreach(func_get_args() as $arg){
            $obj->append($arg);
        }
        return $obj;
    }

    /**
     * IN連結
     *
     * @access     public
     * @param      mixed     $value   ...
     * @return     object   ActiveGatewayCondition_Values
     */
    public function isIn()
    {
        $obj = new ActiveGatewayCondition_Values();
        $obj->setCondition($this);
        $obj->_operator = 'IN';
        foreach(func_get_args() as $arg){
            $obj->append($arg);
        }
        return $obj;
    }

    /**
     * NOT IN連結
     *
     * @access     public
     * @param      mixed    $value   ...
     * @return     object   ActiveGatewayCondition_Values
     */
    public function isNotIn()
    {
        $obj = new ActiveGatewayCondition_Values();
        $obj->setCondition($this);
        $obj->_operator = 'NOT IN';
        foreach(func_get_args() as $arg){
            $obj->append($arg);
        }
        return $obj;
    }

    /**
     * 素のSQLを書けるように
     *
     * @access  public
     * @param   string  $value
     * @param   array   $params
     */
    public function isNative($value, $params = array())
    {
        $obj = new ActiveGatewayCondition_Value(NULL, NULL);
        $obj->operator = 'NATIVE';
        $obj->key = $value;
        $obj->value = $params;
        return $obj;
    }
}





/**
 * ActiveGatewayの条件の値を体現するクラス
 * 
 * @package    ActiveGateway
 * @copyright  2007-2010 Samurai Framework Project
 * @author     KIUCHI Satoshinosuke <scholar@hayabusa-lab.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class ActiveGatewayCondition_Value
{
    /**
     * キー
     *
     * @access  public
     * @var     string
     */
    public $key = NULL;

    /**
     * 値
     *
     * @access   public
     * @var      mixed
     */
    public $value = NULL;

    /**
     * 比較演算子
     *
     * @access   public
     * @var      string
     */
    public $operator = '=';

    /**
     * 比較演算子とかではなく、値も含めて比較文を総上書きしたい際に使用
     * (IS NULLなど)
     *
     * @access   public
     * @var      string
     */
    public $override = '';


    public function __construct($key = NULL, $value = NULL)
    {
        $this->key = $key;
        $this->value = $value;
    }


    /**
     * キーを保持しているかどうか
     *
     * @access  public
     * @return  boolean
     */
    public function hasKey()
    {
        return $this->key !== NULL;
    }

    /**
     * キー取得
     *
     * @access  public
     * @return  string
     */
    public function getKey()
    {
        return $this->key;
    }


    /**
     * キーを必要とするか
     *
     * @access  public
     */
    public function isNeedKey()
    {
        return $this->operator !== 'NATIVE';
    }


    /**
     * 値を返却
     *
     * @access  public
     * @return  mixed
     */
    public function getValue()
    {
        return $this->value;
    }



    /**
     * 文字列を生成する
     *
     * @access  public
     * @param   object  $driver ActiveGateway_Driver
     * @param   object  $cond   ActiveGatewayCondition
     * @return  string
     */
    public function build(ActiveGateway_Driver $driver, ActiveGatewayCondition $cond)
    {
        if($this->override){
            return $this->override;
        } elseif($this->operator == 'NATIVE'){
            foreach($this->value as $_key => $_val){
                $cond->addParam($_val, $_key);
            }
            return $this->key;
        } elseif($this->value === NULL){
            $key = $driver->escapeColumn($this->key);
            return sprintf('%s %s', $key, $this->operator == '=' ? 'IS NULL' : 'IS NOT NULL');
        } else {
            $bindKey = $cond->addParam($this->value);
            return sprintf('%s %s %s', $driver->escapeColumn($this->key), $this->operator, $bindKey);
        }
    }
}





/**
 * ActiveGatewayの条件の値の集合を体現するクラス
 * 
 * @package    ActiveGateway
 * @copyright  2007-2010 Samurai Framework Project
 * @author     KIUCHI Satoshinosuke <scholar@hayabusa-lab.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class ActiveGatewayCondition_Values
{
    /**
     * キー
     *
     * @access  public
     * @var     string
     */
    public $_key = NULL;

    /**
     * 値
     *
     * @access   public
     * @var      array
     */
    public $_values = array();

    /**
     * 連結演算子
     *
     * @access   public
     * @var      string
     */
    public $_operator = 'AND';

    /**
     * Condition
     *
     * @access  private
     * @var     object  ActiveGatewayCondition
     */
    private $_cond;


    /**
     * コンストラクタ
     *
     * @access     public
     */
    public function __construct()
    {
    }


    /**
     * conditionをセット
     *
     * @access  public
     * @param   object  $cond   ActiveGatewayCondition
     */
    public function setCondition(ActiveGatewayCondition $cond)
    {
        $this->_cond = $cond;
    }


    /**
     * 値を追加
     *
     * @access     public
     * @param      mixed    $value
     * @return     object   ActiveGatewayCondition_Value
     */
    public function append($value)
    {
        $this->_values[] = $value;
        return $value;
    }

    /**
     * 値を追加（同キーがすでに指定されている場合は上書き）
     *
     * @access  public
     * @param   mixed   $value
     * @return  object  ActiveGatewayCondition_Value
     */
    public function appendOverrideByKey($value)
    {
        if($value->hasKey()){
            $this->_values[$value->getKey()] = $value;
        } else {
            $this->append($value);
        }
    }


    /**
     * 値をソート
     *
     * @access  public
     */
    public function sort()
    {
        sort($this->_values);
    }


    /**
     * 条件を保持しているかどうか
     *
     * @access  public
     * @return  boolean
     */
    public function has()
    {
        return count($this->_values) > 0;
    }


    /**
     * キーを保持しているか
     *
     * @access  public
     * @return  boolean
     */
    public function hasKey()
    {
        return $this->_key !== NULL;
    }

    /**
     * キーを取得
     *
     * @access  public
     * @return  string
     */
    public function getKey()
    {
        return $this->_key;
    }


    /**
     * グルーピングかどうか
     *
     * @access  public
     * @return  boolean
     */
    public function isGrouping()
    {
        return in_array($this->_operator, array('AND', 'OR'));
    }



    /**
     * 文字列を生成する
     *
     * @access  public
     * @param   object  $driver ActiveGateway_Driver
     * @return  string
     */
    public function build(ActiveGateway_Driver $driver)
    {
        $values = array();
        if($this->_operator == 'IN' || $this->_operator == 'NOT IN'){
            foreach($this->_values as $value){
                $values[] = $this->_cond->addParam($value);
            }
            return sprintf('%s %s (%s)', $driver->escapeColumn($this->_key), $this->_operator, join(', ', $values));
        } else {
            foreach($this->_values as $value){
                if($value instanceof ActiveGatewayCondition_Values){
                    if($value->isGrouping()){
                        $value = sprintf('( %s )', $value->build($driver));
                    } else {
                        $value = $value->build($driver);
                    }
                    $values[] = $value;
                } elseif($value instanceof ActiveGatewayCondition_Value){
                    if(!$value->hasKey() && $value->isNeedKey()){
                        $value->key = $this->_key;
                    }
                    $values[] = $value->build($driver, $this->_cond);
                } else {
                    $value = $this->_cond->isEqual($value);
                    $value->key = $this->_key;
                    $values[] = $value->build($driver, $this->_cond);
                }
            }
            return join(' ' . $this->_operator . ' ', $values);
        }
    }



    /**
     * 条件を直感的に記述できるように、マジックメソッド対応
     *
     * @access  public
     * @param   string  $key
     * @param   object  $value
     */
    public function __set($key, $value)
    {
        if($value instanceof ActiveGatewayCondition_Value){
            $value->key = $key;
            $this->appendOverrideByKey($value);
        } elseif($value instanceof ActiveGatewayCondition_Values){
            $value->_key = $key;
            $this->appendOverrideByKey($value);
        } else {
            $this->appendOverrideByKey(new ActiveGatewayCondition_Value($key, $value));
        }
        $this->_cond->addKey($key);
    }

    /**
     * 以前の仕様を実現するためのマジックメソッド
     *
     * @access  public
     * @param   string  $key
     */
    public function __get($key)
    {
        $value = isset($this->_values[$key]) ? $this->_values[$key] : NULL;
        if($value instanceof ActiveGatewayCondition_Value){
            return $value;
        } else {
            return $value;
        }
    }
}

