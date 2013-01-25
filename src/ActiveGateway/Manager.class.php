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
 * Management ActiveGateway instance, all connection, and others.
 * 
 * This class is singleton.
 * 
 * @package     ActiveGateway
 * @copyright   Samurai Framework Project
 * @author      KIUCHI Satoshinosuke <scholar@hayabusa-lab.jp>
 * @license     http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class ActiveGateway_Manager
{
    /**
     * config.
     *
     * @access  private
     * @var     array
     */
    private $_config = array();

    /**
     * 設定ファイル保持
     *
     * @access   private
     * @var      array
     */
    private $_config_files = array();

    /**
     * ActiveGatewayインスタンス保持
     *
     * @access   private
     * @var      array
     */
    private $_active_gateways = array();

    /**
     * self instance.
     *
     * @access   private
     * @var      object
     */
    private static $_instance;

    /**
     * 発行されたクエリーを保持
     *
     * @access   private
     * @var      array
     */
    private static $_querys = array();

    /**
     * DSNとコネクションの管理
     *
     * @access  private
     * @var     array
     */
    private $_connections = array();


    /**
     * constructor.
     *
     * @access     private
     */
    private function __construct()
    {
    }



    /**
     * get instance (singleton).
     *
     * @access  public
     * @return  ActiveGateway_Manager
     */
    public static function singleton()
    {
        if ( self::$_instance === NULL ) {
            self::$_instance = new ActiveGateway_Manager();
        }
        return self::$_instance;
    }


    /**
     * load config.
     *
     * @access  public
     * @param   string  $config_file
     */
    public function import($config_file)
    {
        $config = ActiveGateway_Utils::loadYaml($config_file);
        foreach($config as $alias => $_val){
            $this->setConfig($alias, $_val);
        }
    }


    /**
     * set config.
     *
     * @access  public
     * @param   string  $alias
     * @param   array   $config
     */
    public function setConfig($alias, array $config)
    {
        $this->_config[$alias] = $config;
    }


    /**
     * get all aliases.
     *
     * @access  public
     * @return  array
     */
    public function getAliases()
    {
        return array_keys($this->_config);
    }




    /**
     * get ActiveGateway instance.
     *
     * @access  public
     * @param   string  $alias
     * @return  ActiveGateway
     */
    public function getActiveGateway($alias)
    {
        // already make instance.
        if ( $this->hasActiveGateway($alias) ) {
            return $this->_active_gateways[$alias];
        }

        // new.
        if ( $this->hasDsn($alias) ) {
            $ActiveGateway = $this->makeActiveGateway($alias);
            $this->_active_gateways[$alias] = $ActiveGateway;
            return $ActiveGateway;
        } else {
            throw new ActiveGateway_Exception_Config('dsn is not found. -> ' . $alias);
        }
    }

    /**
     * synonym of getActiveGateway.
     *
     * @access  public
     * @param   string  $alias
     * @return  ActiveGateway
     */
    public function get($alias)
    {
        return $this->getActiveGateway($alias);
    }


    /**
     * make activegateway instance.
     *
     * @access  public
     * @param   string  $alias
     * @return  ActiveGateway
     */
    public function makeActiveGateway($alias)
    {
        // make.
        $config = $this->_config[$alias];
        $ActiveGateway = new ActiveGateway();

        // dsn.
        $dsn = $config['dsn'];
        $ActiveGateway->setDsn($dsn);
        if ( isset($config['slaves']) ) {
            $ActiveGateway->setDsnSlave($this->_pickSlave($config['slaves']));
        }

        // conf
        if ( isset($config['conf']) ) {
            $ActiveGateway->import($config['conf']);
        }

        // backend.
        $type = $this->_getBackendType($dsn);
        $driver_name = $this->_getDriverName($type);
        $driver_file = $this->_getDriverFile($type);
        if ( file_exists($driver_file) ) {
            require_once $driver_file;
            $Driver = new $driver_name();
            $ActiveGateway->setBackendType($type);
            $ActiveGateway->setDriver($Driver);
        } else {
            throw new ActiveGateway_Exception('driver is not found. -> ' . $type);
        }
        return $ActiveGateway;
    }


    /**
     * has activegateway instance.
     *
     * @access  public
     * @param   string  $alias
     * @return  boolean
     */
    public function hasActiveGateway($alias)
    {
        return isset($this->_active_gateways[$alias]) && is_object($this->_active_gateways[$alias]);
    }


    /**
     * has dsn info.
     *
     * @access  public
     * @param   string  $alias
     * @return  boolean
     */
    public function hasDsn($alias)
    {
        if ( ! isset($this->_config[$alias]) ) return false;
        if ( ! isset($this->_config[$alias]['dsn']) ) return false;
        return true;
    }


    /**
     * 指定のDSNのコネクションを既に保持しているかどうか
     *
     * @acess   public
     * @param   string  $dsn
     * @return  boolean
     */
    public function hasConnection($dsn)
    {
        return isset($this->_connections[$dsn]);
    }

    /**
     * 指定のDSNのコネクションを追加する
     *
     * @access  public
     * @param   string  $dsn
     * @param   resource
     */
    public function setConnection($dsn, $connection)
    {
        $this->_connections[$dsn] = $connection;
    }

    /**
     * 指定のDSNのコネクションを取得する
     *
     * @access  public
     * @param   string  $dsn
     * @return  resource
     */
    public function getConnection($dsn)
    {
        return $this->_connections[$dsn];
    }

    /**
     * コネクションを削除
     *
     * @access  public
     * @param   string  $dsn
     */
    public function delConnection($dsn)
    {
        if(isset($this->_connections[$dsn])){
            unset($this->_connections[$dsn]);
        }
    }



    /**
     * 実行クエリーのプール
     *
     * @access     public
     * @param      string  $query   クエリー文字列
     * @param      int     $time    実行時間
     */
    public function poolQuery($query, $time = 0)
    {
        self::$_querys[] = array(
            'query' => $query,
            'time'  => $time,
        );
    }

    /**
     * プールされた実行クエリーの取得
     *
     * @access     public
     * @return     array
     */
    public static function getPoolQuery()
    {
        return self::$_querys;
    }

    /**
     * プールされたクエリーを解放
     *
     * @access     public
     */
    public function clearPoolQuery()
    {
        self::$_querys = array();
    }


    /**
     * 全ての接続を確立しなおす
     *
     * forkした際、子プロセスの終了時に接続が全て切られてしまうため、
     * 子プロセスの接続リソースは別途確保すべきだから
     *
     * @access     public
     */
    public function reconnectAll()
    {
        foreach($this->_active_gateways as $AG){
            $AG->connect(true);
        }
    }

    /**
     * すべての接続を切断する
     *
     * @access     public
     */
    public function disconnectAll()
    {
        foreach($this->_active_gateways as $AG){
            $AG->disconnect();
        }
    }





    /**
     * get helper.
     *
     * @access  public
     * @return  ActiveGateway_Helper
     */
    public function getHelper()
    {
        $helper = new ActiveGateway_Helper();
        return $helper;
    }

    /**
     * get helper by ActiveGateway.
     *
     * @access  public
     * @param   ActiveGateway   $AG
     * @return  ActiveGateway_Helper
     */
    public function getHelperByActiveGateway(ActiveGateway $AG)
    {
        static $helpers = array();
        
        $type = $AG->getBackendType();
        if ( isset($helpers[$type]) ) {
            return $helpers[$type];
        }

        $class_name = 'ActiveGateway_Helper_' . ucfirst($type);
        $class_file = __DIR__ . '/Helper/' . ucfirst($type) . '.class.php';
        if ( file_exists($class_file) ) {
            require_once $class_file;
            $helper = new $class_name();
        } else {
            require_once __DIR__ . '/Helper.class.php';
            $helper = new ActiveGateway_Helper();
        }
        $helpers[$type] = $helper;
        return $helper;
    }



    /**
     * pick a slave config.
     *
     * @access  private
     * @param   array   $slaves
     * @return  string
     */
    private function _pickSlave(array $slaves = array())
    {
        if ( ! $slaves ) return NULL;
        $config = $slaves[array_rand($slaves)];
        return $config['dsn'];
    }


    /**
     * get backend type.
     *
     * @access  private
     * @param   string  $dsn
     * @return  string
     */
    private function _getBackendType($dsn)
    {
        $info = parse_url($dsn);
        if ( isset($info['scheme']) && $info['scheme'] ) {
            return $info['scheme'];
        } else {
            throw new ActiveGateway_Exception('not found scheme from dsn. -> dsn: ' . $dsn);
        }
    }


    /**
     * get driver class name.
     *
     * @access  private
     * @@aram   string  $backend_type
     * @return  string
     */
    private function _getDriverName($backend_type)
    {
        return 'ActiveGateway_Driver_' . ucfirst($backend_type);
    }

    /**
     * get driver class file.
     *
     * @access  private
     * @@aram   string  $backend_type
     * @return  string
     */
    private function _getDriverFile($backend_type)
    {
        return sprintf('%s/Driver/%s.class.php', __DIR__, ucfirst($backend_type));
    }
}

