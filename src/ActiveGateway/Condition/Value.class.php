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
 * condition value class.
 * 
 * @package    ActiveGateway
 * @copyright  Samurai Framework Project
 * @author     KIUCHI Satoshinosuke <scholar@hayabusa-lab.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class ActiveGateway_Condition_Value
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
     * @param   object  $cond   ActiveGateway_Condition
     * @return  string
     */
    public function build(ActiveGateway_Driver $driver, ActiveGateway_Condition $cond)
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

