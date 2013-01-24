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
 * Migration class.
 * 
 * @package     ActiveGateway
 * @subpackage  Migrate
 * @copyright   Samurai Framework Project
 * @author      KIUCHI Satoshinosuke <scholar@hayabusa-lab.jp>
 * @license     http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
abstract class ActiveGateway_Migration extends ActiveGateway_Schema
{
    /**
     * ActiveGateway
     *
     * @access  protected
     * @var     ActiveGateway
     */
    protected $AG;

    /**
     * use dsn alias.
     *
     * @access  protected
     * @var     string
     */
    protected $_dsn = 'base';

    /**
     * start time.
     *
     * @access  protected
     * @var     float
     */
    protected $_time = 0.0;

    /**
     * raped time.
     *
     * @access  protected
     * @var     float
     */
    protected $_rap_time = 0.0;

    /**
     * version upto direction.
     *
     * @access  protected
     * @var     int
     */
    protected $_direction = self::DIRECTION_UP;

    /**
     * reporter
     *
     * @access  protected
     * @var     object
     */
    protected $reporter;

    /**
     * const: version upto up.
     *
     * @const   int
     */
    const DIRECTION_UP = 1;

    /**
     * const: version upto down.
     *
     * @const   int
     */
    const DIRECTION_DOWN = 2;  



    /**
     * constructor.
     *
     * @access  public
     */
    public function __construct()
    {
    }


    /**
     * set a active geatway.
     *
     * @access  public
     * @param   string  $alias
     */
    public function setActiveGateway($alias)
    {
        $this->AG = ActiveGateway::getManager()->get($alias);
    }


    /**
     * set a reporter
     * to need implements "flushMigrationMessage" method.
     *
     * @access  public
     * @param   object  $reporter
     */
    public function setReporter($reporter)
    {
        $this->reporter = $reporter;
    }






    /**
     * execute.
     *
     * @access  public
     */
    public function migrate()
    {
        $this->start();
        $this->setup();
        try {
            $this->change();
        } catch(ActiveGateway_Exception_Migration_NoChangeMethod $E) {
            $this->up();
        }
        $this->apply();
        $this->finish();
    }


    /**
     * revert ( reverse of migrate )
     *
     * @access  public
     */
    public function revert()
    {
        $this->_direction = self::DIRECTION_DOWN;

        $this->start();
        $this->setup();

        try {
            $this->change();
        } catch(ActiveGateway_Exception_Migration_NoChangeMethod $E) {
            $this->down();
        }
        $this->_defines = array_reverse($this->_defines);
        foreach ( $this->_defines as $define ) {
            $define->revert();
        }

        $this->apply();
        $this->finish();
    }




    /**
     * for initialize.
     *
     * @access  public
     */
    public function setup()
    {
        $this->setActiveGateway($this->_dsn);
    }


    /**
     * when version up.
     *
     * @access  public
     */
    public function up()
    {
        $this->flushMessage('Nothing to do.');
    }


    /**
     * when version down.
     *
     * @access  public
     */
    public function down()
    {
        $this->flushMessage('Nothing to do.');
    }


    /**
     * when version change
     *
     * @access  public
     */
    public function change()
    {
        require_once __DIR__ . DS . 'Exception/Migration/NoChangeMethod.class.php';
        throw new ActiveGateway_Exception_Migration_NoChangeMethod();
    }


    /**
     * define apply
     *
     * @access  public
     */
    public function apply()
    {
        foreach ( $this->_defines as $define ) {
            $this->rapTime();

            $params = array();
            $sql = $define->toSQL($params);
            $this->AG->query($sql, $params);

            $string = $define->toString();
            $this->flushMessage('-- ' . $string);
            $this->flushMessage(sprintf('   -> %0.4f sec', $this->getRapTime()));
        }
    }





    /**
     * on start.
     *
     * @access  public
     */
    public function start()
    {
        $this->_time = microtime(true);

        if ( $this->isVersionUp() ) {
            $phrase = 'migrating';
        } else {
            $phrase = 'reverting';
        }
        $message = sprintf('%s: %s', $this->getName(), $phrase);
        $message = $this->_formatMessageH1($message);
        $this->flushMessage($message);
    }


    /**
     * on finish.
     *
     * @access  public
     */
    public function finish()
    {
        // memory version
        if ( $this->isVersionUp() ) {
            $phrase = 'migrated';
            $version = $this->getVersion();
            $this->AG->create(ActiveGateway_Schema::TABLE_SCHEMA_MIGRATIONS, array('version' => $version));
        } else {
            $phrase = 'reverted';
            $version = $this->getVersion();
            $cond = $this->AG->getCondition();
            $cond->where->version = $version;
            $this->AG->deleteDetail(ActiveGateway_Schema::TABLE_SCHEMA_MIGRATIONS, $cond);
        }

        $message = sprintf('%s: %s (%0.4f sec)', $this->getName(), $phrase, $this->getTime());
        $message = $this->_formatMessageH1($message);
        $this->flushMessage($message);
    }



    /**
     * memory rap time.
     *
     * @access  public
     */
    public function rapTime()
    {
        $this->_rap_time = microtime(true);
    }


    /**
     * get pasted time.
     *
     * @access  public
     * @return  float
     */
    public function getTime()
    {
        return microtime(true) - $this->_time;
    }


    /**
     * get rap time.
     *
     * @access  public
     * @return  float
     */
    public function getRapTime()
    {
        return microtime(true) - $this->_rap_time;
    }


    /**
     * get migration name.
     *
     * @access  public
     * @return  string
     */
    public function getName()
    {
        $name = get_class($this);
        $names = explode('_', $name);
        array_shift($names);
        array_shift($names);
        return join('', $names);
    }
    
    
    /**
     * get migration version.
     *
     * @access  public
     * @return  int
     */
    public function getVersion()
    {
        $name = get_class($this);
        $names = explode('_', $name);
        array_shift($names);
        $version = array_shift($names);
        return (int)$version;
    }



    /**
     * is version up
     *
     * @access  public
     * @return  boolean
     */
    public function isVersionUp()
    {
        return $this->_direction === self::DIRECTION_UP;
    }


    /**
     * is version down.
     *
     * @access  public
     * @return  boolean
     */
    public function isVersionDown()
    {
        return $this->_direction === self::DIRECTION_DOWN;
    }




    /**
     * flush message.
     *
     * @access  public
     * @param   string  $message
     */
    public function flushMessage($message)
    {
        if ( $this->reporter ) {
            $this->reporter->flushMigrationMessage($message);
        }
    }


    /**
     * message fomatter for h1.
     *
     * @access  public
     * @param   string  $message
     * @return  strring
     */
    public function _formatMessageH1($message)
    {
        $width = 65;
        $message = '== ' . $message . ' ';
        $message = str_pad($message, $width, '=');
        return $message;
    }
}

