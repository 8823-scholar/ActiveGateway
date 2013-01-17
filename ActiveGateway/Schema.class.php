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
 * schema class.
 * 
 * @package     ActiveGateway
 * @subpackage  Schema
 * @copyright   Samurai Framework Project
 * @author      KIUCHI Satoshinosuke <scholar@hayabusa-lab.jp>
 * @license     http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class ActiveGateway_Schema
{
    /**
     * database alias
     *
     * @access  private
     * @var     string
     */
    private $_alias = 'base';

    /**
     * version
     *
     * @access  private
     * @var     int
     */
    private $_version = 0;

    /**
     * defined list.
     *
     * @access  private
     * @var     array
     */
    private $_defines = array();


    /**
     * const: column types 
     *
     * @const
     */
    const COLUMN_TYPE_STRING = 'string';


    /**
     * constructor.
     *
     * @access  public
     * @param   string  $alias
     */
    public function __construct($alias)
    {
        $this->_alias = $alias;
    }



    /**
     * get alias.
     *
     * @access  public
     * @return  string
     */
    public function getAlias()
    {
        return $this->_alias;
    }


    /**
     * set version.
     *
     * @access  public
     * @param   int     $version
     */
    public function setVersion($version)
    {
        $this->_version = $version;
    }



    /**
     * get defined list.
     *
     * @access  public
     * @return  array
     */
    public function getDefines()
    {
        return $this->_defines;
    }




    /**
     * create table.
     * not apply into database immediately.
     *
     * @access  public
     * @param   string  $name
     * @return  ActiveGateway_Schema_Table
     */
    public function createTable($name)
    {
        $table = new ActiveGateway_Schema_Table($name);
        $table->setSchema($this);
        $this->_defines[] = $table;

        // conversion: id is exists.
        $table->column('id')->type('int', 11)->primary();

        return $table;
    }
}

