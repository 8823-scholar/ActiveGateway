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
 * ActiveGateway.
 * 
 * @package     ActiveGateway
 * @copyright   Samurai Framework Project
 * @author      KIUCHI Satoshinosuke <scholar@hayabusa-lab.jp>
 * @license     http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class ActiveGateway
{
    /**
     * DSN
     *
     * @access  private
     * @var     string
     */
    private $_dsn;

    /**
     * DSN for slave.
     *
     * @access  private
     * @var     string
     */
    private $_dsn_slave;

    /**
     * 設定情報
     *
     * @access   private
     * @var      array
     */
    private $_config = array();

    /**
     * 設定ファイル
     *
     * @access   private
     * @var      string
     */
    private $_config_file = '';

    /**
     * フェッチモード
     *
     * @access   private
     * @var      int
     */
    private $_fetch_mode = self::FETCH_OBJ;

    /**
     * テーブル情報
     *
     * @access   private
     * @var      array
     */
    private $_table_info = array();

    /**
     * backend type.
     * mysql, pgsql, sqlite or other.
     *
     * @access  private
     * @var     string
     */
    private $_backend_type = 'mysql';

    /**
     * Driverインスタンス
     *
     * @access   private
     * @var      object
     */
    private $Driver;

    /**
     * const: fetch mode.
     *
     * @const   int
     */
    const FETCH_LAZY = PDO::FETCH_LAZY;
    const FETCH_ASSOC = PDO::FETCH_ASSOC;
    const FETCH_OBJ = PDO::FETCH_OBJ;
    const FETCH_BOTH = PDO::FETCH_BOTH;

    /**
     * const: master or slqve
     *
     * @const   string
     */
    const TARGET_MASTER = 'master';
    const TARGET_SLAVE = 'slave';



    /**
     * constructor.
     *
     * @access  public
     */
    public function __construct()
    {   
    }


    /**
     * set DSN.
     *
     * @access  public
     * @param   string  $dsn
     */
    public function setDsn($dsn)
    {
        $this->_dsn = $dsn;
    }

    /**
     * set DSN for slave.
     *
     * @access  public
     * @param   string  $dsn
     */
    public function setDsnSlave($dsn)
    {
        $this->_dsn_slave = $dsn;
    }


    /**
     * connect to backend.
     *
     * @access  public
     * @param   string  $target
     * @return  boolean 接続の可否
     */
    public function connect($target = ActiveGateway::TARGET_MASTER)
    {
        // already connected.
        if ( $this->hasConnection($target) ) {
            return true;
        }

        // not has define.
        if ( ! $this->hasDsn() ) {
            throw new ActiveGateway_Exception('Not has dsn.');
        }

        // connect.
        if ( $target == ActiveGateway::TARGET_MASTER ) {
            $this->Driver->connect($target, $this->_dsn);
        } else {
            $this->Driver->connect($target, $this->_dsn_slave);
        }
        return true;
    }


    /**
     * disconnect from backend.
     *
     * @access     public
     */
    public function disconnect()
    {
        $this->Driver->disconnect();
    }





    /**
     * findシリーズ、ID検索
     *
     * @access     public
     * @param      string  $alias   テーブル名
     * @param      int     $id      ID
     * @return     object  ActiveGatewayRecord
     */
    public function find($alias, $id)
    {
        $Record = $this->_buildRecord($alias);
        $primary_key = $Record->getPrimaryKey();
        
        $condition = ActiveGateway::getCondition();
        $condition->where->$primary_key = $id;
        return $this->findDetail($alias, $condition);
    }


    /**
     * findシリーズ、指定検索
     *
     * @access     public
     * @param      string  $alias    テーブル名
     * @param      string  $column   カラム名
     * @param      mixed   $value    検索条件(配列も可)
     * @return     object  ActiveGatewayRecord
     */
    public function findBy($alias, $column, $value)
    {
        $condition = ActiveGateway::getCondition();
        $condition->where->$column = $value;
        return $this->findDetail($alias, $condition);
    }


    /**
     * findシリーズ、詳細検索
     *
     * @access     public
     * @param      string  $alias       テーブル名
     * @param      array   $condition   ActiveGateway_Condition
     * @return     object  ActiveGatewayRecord
     */
    public function findDetail($alias, ActiveGateway_Condition $condition)
    {
        $condition->total_rows = false;
        $condition->setLimit(1);
        $ActiveGatewayRecords = $this->findAllDetail($alias, $condition);
        return $ActiveGatewayRecords->getFirstRecord();
    }


    /**
     * findシリーズ、SQL検索
     *
     * @access     public
     * @param      string  $alias    テーブル名
     * @param      string  $sql      SQL文
     * @param      array   $params   ブレースフォルダ
     * @return     object  ActiveGatewayRecord
     */
    public function findSql($alias, $sql, $params = array())
    {
        $ActiveGatewayRecords = $this->findAllSql($alias, $sql, $params, 1, NULL, false);
        return $ActiveGatewayRecords->getFirstRecord();
    }


    /**
     * findAll.
     * synonym of findAllDetail.
     *
     * @access  public
     * @param   string  $alias
     * @param   ActiveGateway_Condition $cond
     * @return  ActiveGateway_Records
     */
    public function findAll($alias, ActiveGateway_Condition $cond = NULL)
    {
        if ( ! $cond ) $cond = ActiveGateway::getCondition();
        return $this->findAllDetail($alias, $cond);
    }


    /**
     * findAllシリーズ、指定検索
     *
     * @access     public
     * @param      string  $alias        テーブル名
     * @param      string  $column       カラム名
     * @param      mixed   $value        検索条件(配列可)
     * @param      object  $condition    ActiveGateway_Condition
     * @return     object  ActiveGateway_Records
     */
    public function findAllBy($alias, $column, $value, $condition = NULL)
    {
        if($condition === NULL) $condition = ActiveGateway::getCondition();
        $condition->where->$column = $value;
        return $this->findAllDetail($alias, $condition);
    }


    /**
     * findAllシリーズ、詳細検索
     *
     * @access     public
     * @param      string  $alias       テーブル名
     * @param      object  $condition   ActiveGateway_Condition
     * @return     object  ActiveGateway_Records
     */
    public function findAllDetail($alias, ActiveGateway_Condition $condition)
    {
        //初期化
        $Record = $this->_buildRecord($alias);
        if($condition->from === NULL) $condition->from = $Record->getTableName();
        //自動付加
        $table_info = $this->getTableInfo($alias, $Record->getTableName());
        if(isset($table_info['active']) && $condition->regard_active && !$condition->hasKey('active')){
            $condition->where->active = '1';
        }
        
        //SQL生成
        $sql = $this->makeSelectQuery($condition);
        
        //SQLから検索
        $result = $this->findAllSql($alias, $sql, $condition->getParams());
        $condition->clearParams();

        return $result;
    }


    /**
     * find all use SQL.
     *
     * @access  public
     * @param   string  $alias
     * @param   string  $sql
     * @param   array   $params
     * @return  ActiveGateway_Records
     */
    public function findAllSql($alias, $sql, $params = array())
    {
        //if($total_rows) $sql = $this->Driver->modifyFoundRowsQuery($sql);
        $res = $this->query($sql, $params);
        //if($total_rows) $_total_rows = $this->Driver->getTotalRows($sql, $params);
        
        $records = new ActiveGateway_Records($res);
        $records->setAlias($alias);
        
        while($row = $res->fetch($this->_fetch_mode)){
            $record = $this->_buildRecord($alias, $row, false);
            $records->addRecord($record);
        }
        
        //$total_rows = ($total_rows) ? $_total_rows : $Records->getSize() ;
        //$records->setTotalRows($total_rows);
        return $records;
    }





    /**
     * insert a record.
     *
     * @access  public
     * @param   string  $alias
     * @param   array   $attributes
     * @return  ActiveGateway_Record
     */
    public function insert($alias, $attributes = array())
    {
        $Record = $this->_buildRecord($alias, $attributes, true);
        $table_name = $Record->getTableName();
        //各種情報の付加
        $table_info = $this->getTableInfo($alias, $table_name);
        if(isset($table_info['created_at']) && !isset($attributes['created_at'])){
            $attributes['created_at'] = time();
        }
        if(isset($table_info['updated_at']) && !isset($attributes['updated_at'])){
            $attributes['updated_at'] = time();
        }
        if(isset($table_info['active']) && !isset($attributes['active'])){
            $attributes['active'] = '1';
        }
        //ディフォルト値調節
        $this->Driver->modifyAttributes($table_info, $attributes);
        //インサート
        $params = array();
        $sql = $this->Driver->modifyInsertQuery($table_name, $attributes, $params);
        $stmt = $this->query($sql, $params);
        $attributes[$Record->getPrimaryKey()] = $this->Driver->lastInsertId();
        $record = $this->_buildRecord($alias, $attributes, false);
        return $record;
    }





    /**
     * updateシリーズ、レコードインスタンスの一つの情報を更新する
     *
     * @access     public
     * @param      object  $record   ActiveGatewayRecord
     * @param      string  $column   カラム名
     * @param      mixed   $value    値
     * @return     boolean
     */
    public function updateAttribute($record, $column, $value)
    {
        $record->$column = $value;
        return $this->save($record);
    }


    /**
     * updateシリーズ、レコードインスタンスの複数の情報を更新する
     *
     * @access     public
     * @param      object  $Record       ActiveGatewayRecord
     * @param      array   $attributes   設定値
     * @return     boolean
     */
    public function updateAttributes($record, $attributes = array())
    {
        foreach((array)$attributes as $_key => $_val){
            if(!preg_match('/^_/', $_key)){
                $record->$_key = $_val;
            }
        }
        return $this->save($record);
    }


    /**
     * updateシリーズ、ID更新
     *
     * @access     public
     * @param      string  $alias        テーブル名
     * @param      int     $id           ID
     * @param      array   $attributes   設定値
     * @return     boolean
     */
    public function update($alias, $id, $attributes = array())
    {
        $record = $this->_buildRecord($alias);
        $primary_key = $record->getPrimaryKey();
        
        $condition = ActiveGateway::getCondition();
        $condition->where->$primary_key = $id;
        return $this->updateDetail($alias, $condition, $attributes);
    }


    /**
     * updateシリーズ、指定更新
     *
     * @access     public
     * @param      string  $alias        テーブル名
     * @param      string  $column       カラム名
     * @param      string  $value        条件
     * @param      array   $attributes   設定値
     * @return     boolean
     */
    public function updateBy($alias, $column, $value, $attributes = array())
    {
        $condition = ActiveGateway::getCondition();
        $condition->where->$column = $value;
        return $this->updateDetail($alias, $condition, $attributes);
    }


    /**
     * updateシリーズ、詳細更新
     *
     * @access     public
     * @param      string  $alias        テーブル名
     * @param      object  $condition    ActiveGateway_Condition
     * @param      array   $attributes   設定値
     * @return     boolean 
     */
    public function updateDetail($alias, ActiveGateway_Condition $condition, $attributes = array())
    {
        return $this->updateAllDetail($alias, $condition, $attributes);
    }


    /**
     * updateシリーズ、SQL更新
     *
     * @access     public
     * @param      string  $sql      SQL文
     * @param      array   $params   ブレースフォルダ
     * @return     boolean
     */
    public function updateSql($sql, $params)
    {
        return $this->updateAllSql($sql, $params, 1);
    }


    /**
     * updateAllシリーズ、ID更新
     *
     * @access     public
     * @param      string  $alias        テーブル名
     * @param      int     $id           ID
     * @param      array   $attributes   設定値
     * @return     boolean
     */
    public function updateAll($alias, $id, $attributes = array())
    {
        $record = $this->_buildRecord($alias);
        $primary_key = $record->getPrimaryKey();
        
        $condition = ActiveGateway::getCondition();
        $condition->where->$primary_key = $id;
        return $this->updateAllDetail($alias, $attributes, $condition);
    }


    /**
     * updateAllシリーズ、指定更新
     *
     * @access     public
     * @param      string  $alias        テーブル名
     * @param      string  $column       カラム名
     * @param      string  $value        条件
     * @param      array   $attributes   設定値
     * @return     boolean
     */
    public function updateAllBy($alias, $column, $value, $attributes = array())
    {
        $condition = ActiveGateway::getCondition();
        $condition->where->$column = $value;
        return $this->updateAllDetail($alias, $attributes, $condition);
    }


    /**
     * updateAllシリーズ、詳細更新
     *
     * @access     public
     * @param      string  $alias        テーブル名
     * @param      object  $condition    ActiveGateway_Condition
     * @param      string  $attributes   設定値
     * @return     boolean
     */
    public function updateAllDetail($alias, ActiveGateway_Condition $condition, $attributes = array())
    {
        //初期化
        $params = array();
        $record = $this->_buildRecord($alias);
        //自動付加
        $attributes = (array)$attributes;
        if($record->hasField('updated_at')){
            $attributes['updated_at'] = time();
        }

        $condition->from = $record->getTableName();
        $sql = $this->makeUpdateQuery($condition, $attributes);

        //SQLから更新
        $result = $this->updateAllSql($sql, $condition->getParams(), $condition->limit);
        $condition->clearParams();

        return $result;
    }


    /**
     * updateAllシリーズ、SQL更新
     *
     * @access     public
     * @param      string   $sql      SQL文
     * @param      array    $params   ブレースフォルダ
     * @param      int      $limit    更新数
     * @return     resource PDOステートメント
     */
    public function updateAllSql($sql, $params = array(), $limit = NULL)
    {
        $stmt = $this->executeUpdate($sql, $params, $limit);
        return $stmt;
    }





    /**
     * プライマリキーにおいて削除を実行する
     *
     * @access     public
     * @param      string  $alias   テーブル名
     * @param      int     $id      ID
     * @return     boolean
     */
    public function delete($alias, $id)
    {
        $record = $this->_buildRecord($alias);
        $primary_key = $record->getPrimaryKey();
        
        $condition = ActiveGateway::getCondition();
        $condition->where->$primary_key = $id;
        return $this->deleteDetail($alias, $condition);
    }


    /**
     * 詳細削除
     *
     * @access     public
     * @param      string  $alias       テーブル名
     * @param      object  $condition   ActiveGateway_Condition
     */
    public function deleteDetail($alias, ActiveGateway_Condition $condition)
    {
        return $this->deleteAllDetail($alias, $condition);
    }


    /**
     * 詳細全削除
     *
     * @access     public
     * @param      string   $alias       テーブル名
     * @param      object   $condition   ActiveGateway_Condition
     */
    public function deleteAllDetail($alias, ActiveGateway_Condition $condition)
    {
        $record = $this->_buildRecord($alias);
        //論理消去できるのであれば論理消去(こちらが望ましい)
        if($condition->regard_active && $record->enableDeleteByLogical()){
            $attributes['active'] = '0';
            if($record->hasField('deleted_at')){
                $attributes['deleted_at'] = time();
            }
            return $this->updateAllDetail($alias, $condition, $attributes);
        }
        //物理消去
        else {
            $condition->from = $record->getTableName();
            $sql = $this->makeDeleteQuery($condition);
            $result = $this->updateAllSql($sql, $condition->getParams(), $condition->limit);
            $condition->clearParams();
            return $result;
        }
    }





    /**
     * build a active gateway record instance.
     *
     * @access  public
     * @param   string  $alias
     * @param   array   $attributes
     * @return  ActiveGateway_Record
     */
    public function build($alias, $attributes = array())
    {
        $record = $this->_buildRecord($alias, $attributes, true);
        return $record;
    }


    /**
     * レコードインスタンスの生成
     *
     * @access     private
     * @param      string  $alias        テーブル名
     * @param      mixed   $row          PDOStatement->fetchの取得結果
     * @param      boolean $new_record   新規レコードかどうかの判断値
     * @return     object  ActiveGatewayRecord
     */
    private function _buildRecord($alias, $row = NULL, $new_record = true)
    {
        $record = new ActiveGateway_Record($row, $new_record, $alias);
        //設定情報の取得
        $config = array();
        if($alias !== NULL && isset($this->_config[$alias])){
            $config = $this->_config[$alias];
        }
        //テーブル名の書き換え
        if(isset($config['table_name'])){
            $record->setTableName($config['table_name']);
        }
        //プライマリキーの書き換え
        if(isset($config['primary_key'])){
            $record->setPrimaryKey($config['primary_key']);
        }
        //テーブル情報の取得
        $table_info = $this->getTableInfo($alias, $record->getTableName());
        $record->setTableInfo($table_info);
        return $record;
    }


    /**
     * テーブル情報の取得
     *
     * ドライバーの取得メソッドを使用し、取得する。
     * ドライバーの取得メソッドは、PEAR_DBのgetTableInfo()と同等であるべきである。
     *
     * @access     public
     * @param      string  $alias        テーブル名
     * @param      string  $table_name   対象となるテーブルの実名
     * @return     array   テーブル情報配列
     */
    public function getTableInfo($alias, $table_name)
    {
        //既に取得済みの場合
        if(isset($this->_table_info[$alias])){
            return $this->_table_info[$alias];
        }

        //キャッシュファイルを検索
        /*
        $cachable = false;
        if ( $cachable ) {
            $cdir = 'temp/scheme_cache';
            if(!is_dir($cdir)){
                mkdir($cdir);
                chmod($cdir, 0777);
            }
            $cfile = $cdir . '/' . $table_name . '.scheme';
            if(file_exists($cfile)){
                $info = unserialize(file_get_contents($cfile));
                $this->_table_info[$alias] = $info;
                return $info;
            }
        }
         */

        //情報の取得
        $this->connect('slave');
        $attributes = $this->Driver->getTableInfo($table_name);
        /*
        if($cachable){
            file_put_contents($cfile, serialize($attributes));
            chmod($cfile, 0777);
        }
         */
        //情報の代入
        $this->_table_info[$alias] = $attributes;
        return $this->_table_info[$alias];
    }





    /**
     * save active gateway record.
     *
     * @access  public
     * @param   ActiveGateway_Record    $record
     */
    public function save(ActiveGateway_Record $record)
    {
        if ( ! $record->isSavable() ) {
            throw new ActiveGateway_Exception('This record is can not save.');
        }

        // new record.
        if ( $record->isNewRecord() ) {
            $record = $this->insert($record->getAlias(), $record->getAttributes());

        // exists record.
        } else {
            if ( $attributes = $record->getAttributes(true) ) {
                $this->update($record->getAlias(), $record->getOriginalValue('primary_key'), $attributes);
                $record->onSaved();
            }
        }
    }


    /**
     * 上記のbuildとsaveの一連の流れを一つのメソッドで完結させてしまう場合はコレ
     *
     * @access  public
     * @param   string  $alias
     * @param   array   $attributes
     * @return  ActiveGateway_Record
     */
    public function create($alias, $attributes = array())
    {
        if ( is_object($attributes) && $attributes instanceof ActiveGateway_Record ) {
            $attributes->{$attributes->getPrimaryKey()} = NULL;
            $attributes = $attributes->toArray();
        }
        $record = $this->build($alias, $attributes);
        $this->save($record);
        return $record;
    }


    /**
     * インスタンスを使用し、データを削除する
     *
     * @access     public
     * @param      object  $record
     * @return     boolean
     */
    public function destroy(ActiveGatewayRecord &$record)
    {
        //新規レコードの場合
        if($record->isNewRecord()){
            $record = NULL;
            return true;
        //既存レコードの場合
        } else {
            return $this->delete($record->getAlias(), $record->getOriginalValue($record->getPrimaryKey()));
        }
    }


    /**
     * execute query.
     *
     * @access  public
     * @param   string  $sql
     * @param   array   $params 
     */
    public function query($sql, array $params = array())
    {
        $helper = $this->getHelper();
        $target = $this->inTx() || $helper->isUpdateQuery($sql)
                        ? ActiveGateway::TARGET_MASTER : ActiveGateway::TARGET_SLAVE;
        $this->connect($target);
        $stmt = $this->Driver->query($target, $sql, $params);
        return $stmt;
    }





    /**
     * start transaction.
     *
     * @access  public
     * @param   string  $name
     */
    public function tx($name = NULL)
    {
        $this->connect(ActiveGateway::TARGET_MASTER);
        $this->Driver->tx($name);
    }

    /**
     * ロールバック処理
     *
     * @access     public
     * @param      string  $name   トランザクション名
     */
    public function rollback($name = NULL)
    {
        $this->connect('master');
        $this->Driver->rollback($name);
    }

    /**
     * コミット処理
     *
     * @access     public
     * @param      string  $name   トランザクション名
     */
    public function commit($name = NULL)
    {
        $this->connect('master');
        $this->Driver->commit($name);
    }





    /**
     * PEAR_DB::getAll()と同等
     *
     * @param      string  $sql      SQL文
     * @param      array   $params   ブレースフォルダ
     * @param      int     $limit    取得数
     * @param      int     $offset   開始位置
     * @return     array   すべての取得結果
     */
    public function getAll($sql, $params = array(), $limit = NULL, $offset = NULL)
    {
        $stmt = $this->executeQuery($sql, $params, $limit, $offset);
        return $stmt->fetchAll($this->_fetch_mode);
    }


    /**
     * PEAR_DB::getRow()と同等
     *
     * @param      string  $sql      SQL文
     * @param      array   $params   ブレースフォルダ
     * @return     array   1レコードの結果
     */
    public function getRow($sql, $params = array())
    {
        $stmt = $this->executeQuery($sql, $params, 1);
        $row  = $stmt->fetch($this->_fetch_mode);
        return $row;
    }


    /**
     * PEAR_DB::getCol()と同等
     *
     * @param      string  $sql      SQL文
     * @param      array   $params   ブレースフォルダ
     * @param      mixed   $column   カラム名指定
     * @return     array   取得結果
     */
    public function getCol($sql, $params = array(), $column = NULL)
    {
        $stmt = $this->executeQuery($sql, $params);
        $rows = $stmt->fetchAll(ActiveGateway::FETCH_BOTH);
        $result = array();
        foreach($rows as $row){
            if($column !== NULL){
                if(isset($row[$column])){
                    $result[] = $row[$column];
                } else {
                    $result[] = $row[0];
                }
            } else {
                $result[] = $row[0];
            }
        }
        return $result;
    }


    /**
     * PEAR_DB::getOne()と同等
     *
     * @param      string  $sql      SQL文
     * @param      array   $params   ブレースフォルダ
     * @param      mixed   $column   カラム名指定
     * @return     mixed   取得結果
     */
    public function getOne($sql, $params = array(), $column = NULL)
    {
        $stmt = $this->executeQuery($sql, $params);
        $row = $stmt->fetch(ActiveGateway::FETCH_BOTH);
        if($column !== NULL){
            if(isset($row[$column])){
                return $row[$column];
            }
        }
        return $row[0];
    }







    /**
     * 設定情報の取り込み
     *
     * @access     public
     * @param      string  $config_file   設定ファイル
     */
    public function import($config_file)
    {
        if($config_file){
            $this->_config = ActiveGatewayUtils::loadYaml($config_file);
            $this->_config_file = $config_file;
        }
    }


    /**
     * ドライバーの格納
     *
     * @access     public
     * @param      object  $Driver   ActiveGateway_Driver
     */
    public function setDriver($Driver)
    {
        $this->Driver = $Driver;
    }


    /**
     * ActiveGatewayの検索用DTOを返却する
     *
     * @access     public
     * @return     object   ActiveGateway_Condition
     */
    public static function getCondition()
    {
        $condition = new ActiveGateway_Condition();
        return $condition;
    }


    /**
     * has connection ?
     *
     * @access  public
     * @param   string  $taregt
     * @return  boolean
     */
    public function hasConnection($target = 'master')
    {
        return $this->Driver->hasConnection($target);
    }


    /**
     * has dsn info ?
     *
     * @access     public
     * @return     boolean
     */
    public function hasDsn()
    {
        return $this->_dsn;
    }
    
    
    /**
     * in transaction ?
     *
     * @access     public
     * @return     boolean
     */
    public function inTx()
    {
        return $this->Driver->inTx();
    }







    /**
     * SELECT文を作成
     *
     * @access  public
     * @param   object  $cond   ActiveGateway_Condition
     * @return  string
     */
    public function makeSelectQuery(ActiveGateway_Condition $cond)
    {
        $sql = array();
        $selects = (array)$cond->select;
        $selects = $this->_chain_select($selects);
        $sql[] = sprintf('SELECT %s FROM %s', join(', ', $selects), $this->Driver->escapeColumn($cond->from));
        if($cond->where->has()){
            if($cond->addtional_where) $cond->where->append($cond->isNative($cond->addtional_where));
            $sql[] = sprintf('WHERE %s', $cond->where->build($this->Driver));
        }

        $groups = (array)$cond->group;
        $groups = $this->_chain_group($cond->group);
        if($groups) $sql[] = sprintf('GROUP BY %s', join(', ', $groups));

        $orders = $this->_chain_order($cond->order);
        if($orders) $sql[] = sprintf('ORDER BY %s', join(', ', $orders));

        return join(' ', $sql);
    }


    /**
     * UPDATE文を作成
     *
     * @access  public
     * @param   object  $cond   ActiveGateway_Condition
     * @param   array   $attributes
     * @return  string
     */
    public function makeUpdateQuery(ActiveGateway_Condition $cond, $attributes = array())
    {
        $sql = array();
        $sql[] = sprintf('UPDATE %s SET', $this->Driver->escapeColumn($cond->from));
        $sets = array();
        foreach($attributes as $_key => $_val){
            $bindkey = $cond->addParam($_val);
            $sets[] = sprintf('%s = %s', $this->Driver->escapeColumn($_key), $bindkey);
        }
        $sql[] = join(', ', $sets);
        if($cond->where->has()){
            $sql[] = sprintf('WHERE %s', $cond->where->build($this->Driver));
        } else {
            throw new Samurai_Exception('No condition update is very danger!!');
        }

        $orders = $this->_chain_order($cond->order);
        if($orders) $sql[] = sprintf('ORDER BY %s', join(', ', $orders));

        return join(' ', $sql);
    }


    /**
     * DELETE文を作成
     *
     * @access  public
     * @param   object  $cond   ActiveGateway_Condition
     * @param   array   $attributes
     * @return  string
     */
    public function makeDeleteQuery(ActiveGateway_Condition $cond)
    {
        $sql = array();
        $sql[] = sprintf('DELETE FROM %s', $this->Driver->escapeColumn($cond->from));
        if($cond->where->has()){
            $sql[] = sprintf('WHERE %s', $cond->where->build($this->Driver));
        } else {
            throw new Samurai_Exception('No condition delete is very danger!!');
        }

        $orders = $this->_chain_order($cond->order);
        if($orders) $sql[] = sprintf('ORDER BY %s', join(', ', $orders));

        return join(' ', $sql);
    }





    /**
     * SELECT句を連結する
     *
     * @access     private
     * @param      mixed   $select
     * @return     array
     */
    private function _chain_select($select)
    {
        $return = array();
        if(!$select){
            $return[] = '*';
        } else {
            $return = (array)$select;
        }
        return $return;
    }


    /**
     * WHERE条件を連結する
     *
     * @access     private
     * @param      mixed   $where    WHERE
     * @param      array   $params   ブレースフォルダ
     * @return     array
     */
    private function _chain_where($where, &$params, $prefix = '', $original_key = NULL)
    {
        $return = array();
        if($where){
            foreach($where as $_key => $_val){
                $column_key = $original_key === NULL ? $_key : $original_key;
                switch((string)$_key){
                case 'range':
                    $place_holder1 = ":range_{$column_key}_1";
                    $place_holder2 = ":range_{$column_key}_2";
                    $return[] = sprintf('%s >= %s AND %s <= %s', $column_key, $place_holder1, $column_key, $place_holder2);
                    $params[$place_holder1] = array_shift($_val);
                    $params[$place_holder2] = array_shift($_val);
                    break;
                default:
                    if(is_array($_val)){
                        $condition = ActiveGateway::getCondition();
                        $_values = $_val;
                        $_val = $condition->isOr();
                        $_val->values = $_values;
                    } elseif(!is_object($_val)){
                        $condition = ActiveGateway::getCondition();
                        $_val = $condition->isEqual($_val);
                    }
                    if($_val instanceof ActiveGateway_Condition_Value){
                        if($_val->override){
                            $return[] = sprintf('%s %s', $column_key, $_val->override);
                        } else {
                            $place_holder = ':' . $prefix . $_key;
                            $params[$place_holder] = $_val->value;
                            $return[] = sprintf('%s %s %s', $this->Driver->escapeColumn($column_key), $_val->operator, $place_holder);
                        }
                    } elseif($_val instanceof ActiveGateway_Condition_Values){
                        if($_val->operator === 'IN' || $_val->operator === 'NOT IN'){
                            $_in = array();
                            foreach($_val->values as $_i => $_val2){
                                $place_holder = ':' . $prefix . $_key . $_i;
                                $_in[] = $place_holder;
                                $params[$place_holder] = $_val2;
                            }
                            $return[] = sprintf('%s %s (%s)',
                                $this->Driver->escapeColumn($column_key), $_val->operator, join(',', $_in));
                        } else {
                            $sub_wheres = $this->_chain_where($_val->values, $params, $prefix . $_key . '_', $column_key);
                            $return[] = sprintf('( %s )', join(sprintf(' %s ', $_val->operator), $sub_wheres));
                        }
                    }
                    break;
                }
            }
        }
        return $return;
    }


    /**
     * ORDER条件を連結する
     *
     * @access     private
     * @param      mixed   ORDER
     * @return     array
     */
    private function _chain_order($order)
    {
        $return = array();
        if($order){
            if(is_string($order)){
                $return[] = preg_match('/\(.*?\)/', $order) ? $order : $this->Driver->escapeColumn($order);
            } else {
                foreach($order as $_key => $_val){
                    if($_val !== NULL){
                        $return[] = sprintf('%s %s',
                            preg_match('/\(.*?\)/', $_key) ? $_key : $this->Driver->escapeColumn($_key), $_val);
                    } else {
                        $return[] = preg_match('/\(.*?\)/', $_key) ? $_key : $this->Driver->escapeColumn($_key);
                    }
                }
            }
        }
        return $return;
    }


    /**
     * GRUP条件を連結する
     *
     * @access     private
     * @return     array
     */
    private function _chain_group($group)
    {
        $return = array();
        if($group){
            if(is_string($group)){
                $return[] = $this->Driver->escapeColumn($group);
            } else {
                foreach($group as $_val){
                    $return[] = $this->Driver->escapeColumn($_val);
                }
            }
        }
        return $return;
    }





    /**
     * get manager.
     *
     * @access  public
     * @return  ActiveGateway_Manager
     */
    public static function getManager()
    {
        return ActiveGateway_Manager::singleton();
    }


    /**
     * get helper.
     *
     * @access  public
     * @return  ActiveGateway_Helper
     */
    public function getHelper()
    {
        $manager = self::getManager();
        return $manager->getHelperByActiveGateway($this);
    }


    /**
     * get backend type.
     *
     * @access  public
     * @return  string
     */
    public function getBackendType()
    {
        return $this->_backend_type;
    }

    /**
     * set backend type.
     *
     * @access  public
     * @param   string  $type
     */
    public function setBackendType($type)
    {
        $this->_backend_type = $type;
    }
}

