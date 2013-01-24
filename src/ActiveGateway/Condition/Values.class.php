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
 * condition values class.
 * 
 * @package    ActiveGateway
 * @copyright  Samurai Framework Project
 * @author     KIUCHI Satoshinosuke <scholar@hayabusa-lab.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class ActiveGateway_Condition_Values
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
     * @var     object  ActiveGateway_Condition
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
     * @param   object  $cond   ActiveGateway_Condition
     */
    public function setCondition(ActiveGateway_Condition $cond)
    {
        $this->_cond = $cond;
    }


    /**
     * 値を追加
     *
     * @access     public
     * @param      mixed    $value
     * @return     object   ActiveGateway_Condition_Value
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
     * @return  object  ActiveGateway_Condition_Value
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
                if($value instanceof ActiveGateway_Condition_Values){
                    if($value->isGrouping()){
                        $value = sprintf('( %s )', $value->build($driver));
                    } else {
                        $value = $value->build($driver);
                    }
                    $values[] = $value;
                } elseif($value instanceof ActiveGateway_Condition_Value){
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
        if($value instanceof ActiveGateway_Condition_Value){
            $value->key = $key;
            $this->appendOverrideByKey($value);
        } elseif($value instanceof ActiveGateway_Condition_Values){
            $value->_key = $key;
            $this->appendOverrideByKey($value);
        } else {
            $this->appendOverrideByKey(new ActiveGateway_Condition_Value($key, $value));
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
        if($value instanceof ActiveGateway_Condition_Value){
            return $value;
        } else {
            return $value;
        }
    }
}

