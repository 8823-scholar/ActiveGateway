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
 * Driver to backend class.
 * 
 * @package     ActiveGateway
 * @subpackage  Driver
 * @copyright   Samurai Framework Project
 * @author      KIUCHI Satoshinosuke <scholar@hayabusa-lab.jp>
 * @license     http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
abstract class ActiveGateway_Driver
{
    /**
     * connection
     *
     * @access   protected
     * @var      resource
     */
    protected $connection;

    /**
     * connection of slave.
     *
     * @access   protected
     * @var      resource
     */
    protected $connection_slave;

    /**
     * in transcation flag.
     *
     * @access   protected
     * @var      boolean
     */
    protected $_in_transaction = false;

    
    /**
     * constructor.
     *
     * @access     public
     */
    public function __construct()
    {
    }





    /**
     * connect.
     *
     * @access  public
     * @param   string  $target
     * @param   string  $dsn    DSN
     */
    public function connect($target, $dsn)
    {
        $AGM = ActiveGateway_Manager::singleton();
        if ( $target === ActiveGateway::TARGET_MASTER && ! $this->hasConnection($target) ) {
            $this->connection = $this->_connect($dsn);
        } elseif( $target === ActiveGateway::TARGET_SLAVE && ! $this->hasConnection($target) ) {
            $this->connection_slave = $this->_connect($dsn);
        }
    }


    /**
     * abstract: connect to backend by driver.
     *
     * @param   string  $dsn
     * @return  resource
     */
    abstract protected function _connect($dsn);


    /**
     * disconnect.
     *
     * @access     public
     */
    public function disconnect()
    {
        $this->connection = NULL;
        $this->connection_master = NULL;
    }


    /**
     * has connection.
     *
     * @access  public
     * @param   string  $target
     * @return  boolean
     */
    public function hasConnection($target)
    {
        if ( $target === ActiveGateway::TARGET_MASTER ) {
            return $this->connection !== NULL;
        } else {
            return $this->connection_slave !== NULL;
        }
    }



    /**
     * query.
     *
     * @access  public
     * @param   staring $target
     * @param   string  $sql
     * @param   array   $params
     */
    public function query($target, $sql, $params = array())
    {
        if ( $target === ActiveGateway::TARGET_MASTER ) {
            $stmt = $this->connection->prepare($sql);
        } else {
            $stmt = $this->connection_slave->prepare($sql);
        }

        // placeholder
        foreach($params as $_key => $_val){
            $param_type = PDO::PARAM_STR;
            if ( is_null($_val) ) {
                $param_type = PDO::PARAM_NULL;
            } elseif ( is_int($_val) ) {
                $param_type = PDO::PARAM_INT;
            } elseif ( is_bool($_val) ) {
                $param_type = PDO::PARAM_BOOL;
            } elseif ( is_resource($_val) ) {
                $param_type = PDO::PARAM_LOB;
            } elseif ( strlen($_val) >= 5120 ) {
                $param_type = PDO::PARAM_LOB;
            }
            $stmt->bindValue($_key, $_val, $param_type);
        }

        // execute.
        $execute_start = microtime(true);
        $stmt->execute();
        $this->_checkError($stmt);
        $execute_end = microtime(true);
        ActiveGateway_Manager::singleton()->poolQuery($stmt->queryString, $execute_end - $execute_start);
        return $stmt;
    }



    /**
     * start transaction
     *
     * @access  public
     */
    public function tx()
    {
        if ( ! $this->_in_transaction ) {
            $this->_in_transaction = true;
            $this->connection->beginTransaction();
        }
    }


    /**
     * rollback.
     *
     * @access  public
     */
    public function rollback()
    {
        if ( $this->_in_transaction ) {
            $this->_in_transaction = false;
            $this->connection->rollback();
        }
    }


    /**
     * commit
     *
     * @access  public
     */
    public function commit()
    {
        if ( $this->_in_transaction ) {
            $this->_in_transaction = false;
            $this->connection->commit();
        }
    }


    /**
     * in transaction ?
     *
     * @access  public
     * @return  boolean
     */
    public function inTx()
    {
        return $this->_in_transaction;
    }


    /**
     * get lastInsertID
     *
     * @access     public
     * @return     int
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }





    /**
     * リミットクエリーの整形
     *
     * @access     public
     * @param      string  $sql      SQL文
     * @param      int     $offset   開始位置
     * @param      int     $limit    作用制限
     * @return     string  SQL文
     */
    abstract public function modifyLimitQuery($sql, $offset = NULL, $limit = NULL);

    /**
     * インサートクエリーの生成
     *
     * @access     public
     * @param      string  $table_name   テーブル名
     * @param      array   $attributes   各種値
     * @param      array   &$params       ブレースフォルダ格納用
     * @return     string  SQL文
     */
    abstract public function modifyInsertQuery($table_name, $attributes, &$params = array());

    /**
     * 更新クエリーの生成
     *
     * @access     public
     * @param      string  $table_name   テーブル名
     * @param      array   $sets         更新値
     * @param      array   $wheres       条件値
     * @param      array   $orders       並び順
     * @return     string  SQL文
     */
    abstract public function modifyUpdateQuery($table_name, $sets, $wheres = array(), $orders = array());

    /**
     * 削除クエリーの生成
     *
     * @access     public
     * @param      string  $table_name   テーブル名
     * @param      array   $wheres       条件値
     * @param      array   $orders       並び順
     * @return     string  SQL文
     */
    abstract public function modifyDeleteQuery($table_name, $wheres = array(), $orders = array());

    /**
     * 更新制限クエリーの整形
     *
     * @access     public
     * @param      string  $sql      SQL文
     * @param      int     $limit    作用制限
     * @return     string  SQL文
     */
    abstract public function modifyUpdateLimitQuery($sql, $limit = NULL);

    /**
     * 総レコード取得用クエリー整形
     *
     * @access     public
     * @param      string  $sql   SQL文
     * @return     string  SQL文
     */
    abstract public function modifyFoundRowsQuery($sql);

    /**
     * 行ロック用クエリー整形
     *
     * @access     public
     * @param      string  $sql   SQL文
     * @return     string  SQL文
     */
    abstract public function modifyForUpdateQuery($sql);

    /**
     * インサート時に内容を調節する
     *
     * @access     public
     */
    public function modifyAttributes($table_info, &$attributes = array())
    {
        
    }

    /**
     * カラム名をエスケープする
     *
     * @access     public
     * @return     string
     */
    public function escapeColumn($column_name)
    {
        return $column_name;
    }



    /**
     * 直前のクエリーの総レコード数の取得
     *
     * @access     public
     * @param      string  $sql      SQL文
     * @param      array   $params   ブレースフォルダ
     * @return     int
     */
    abstract public function getTotalRows($sql, $params = array());



    /**
     * check error.
     *
     * @access  private
     */
    protected function _checkError($stmt)
    {
        @list($code, $driver_code, $message) = $stmt->errorInfo();
        if($code != '00000'){
            throw new ActiveGateway_Exception("Error[{$code}][{$driver_code}]: {$message} -> " . $stmt->queryString);
        }
    }
}

