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
 * Helper class.
 * 
 * @package     ActiveGateway
 * @subpackage  Helper
 * @copyright   Samurai Framework Project
 * @author      KIUCHI Satoshinosuke <scholar@hayabusa-lab.jp>
 * @license     http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class ActiveGateway_Helper
{
    /**
     * escape for column.
     *
     * @access  public
     * @param   string  $column_name
     * @return  string  escaped column name.
     */
    public function escapeColumn($column_name)
    {
        return $column_name;
    }



    /**
     * table schema to SQL.
     *
     * @access  public
     * @param   ActiveGateway_Schema_Table  $table
     * @param   array                       &$params
     * @return  string
     */
    public function tableToSQL(ActiveGateway_Schema_Table $table, array &$params)
    {
        throw new ActiveGateway_Exception('implements method. -> tableToSQL');
    }
    
    
    /**
     * column schema to SQL.
     *
     * @access  public
     * @param   ActiveGateway_Schema_Column $column
     * @param   array                       &$params
     * @return  string
     */
    public function columnToSQL(ActiveGateway_Schema_Column $column, array &$params)
    {
        throw new ActiveGateway_Exception('implements method. -> columnToSQL');
    }
    
    
    /**
     * column type to SQL.
     *
     * @access  public
     * @param   string  $type
     * @param   mixed   $length
     * param    array   &$params
     */
    public function columnTypeToSQL($type, $length, &$params)
    {
        $sql = array();
        $sql[] = $this->convertColumnType($type);
        if ( $length !== NULL ) {
            if ( is_array($length) ) {
                $values = array();
                foreach ( $length as $value ) {
                    $bind_key = $this->_generateBindKey('column_length', $type);
                    $params[$bind_key] = $value;
                    $values[] = $bind_key;
                }
                $bind_key = join(',', $values);
            } else {
                $bind_key = $this->_generateBindKey('column_length', $type);
                $params[$bind_key] = $length;
            }
            $sql[] = '(' . $bind_key . ')';
        }
        $sql = join(' ', $sql);
        return $sql;
    }

    
    /**
     * index to SQL.
     *
     * @access  public
     * @param   ActiveGateway_Schema_Index  $index
     * @param   array                       &$params
     * @return  string
     */
    public function indexToSql(ActiveGateway_Schema_Index $index, array &$params)
    {
        throw new ActiveGateway_Exception('implements method. -> primaryIndexToSql');
    }

    /**
     * unique index to SQL.
     *
     * @access  public
     * @param   ActiveGateway_Schema_Unique $index
     * @return  string
     */
    public function uniqueIndexToSql(ActiveGateway_Schema_Unique $index, array &$params)
    {
        throw new ActiveGateway_Exception('implements method. -> uniqueIndexToSql');
    }

    /**
     * primary index to SQL.
     *
     * @access  public
     * @param   ActiveGateway_Schema_Primary    $index
     * @return  string
     */
    public function primaryIndexToSql(ActiveGateway_Schema_Primary $index)
    {
        throw new ActiveGateway_Exception('implements method. -> primaryIndexToSql');
    }




    /**
     * convert column type.
     *
     * @access  public
     * @param   string  $type
     * @return  string
     */
    public function convertColumnType($type)
    {
        return $type;
    }


    /**
     * get default length.
     *
     * @access  public
     * @param   string  $type
     * @return  mixed
     */
    public function getDefaultLength($type)
    {
        $length = NULL;
        switch ( strtolower($type) ) {
        case 'string':
        case 'varchar':
            $length = 255;
            break;
        }
        return $length;
    }


    /**
     * generate bind key
     *
     * @access  protected
     * @param   string  $base_key
     * @param   string  $seed
     * @return  string
     */
    protected function _generateBindKey($base_key, $seed)
    {
        return sprintf(':%s%s', $base_key, md5($seed . uniqid()));
    }



    /**
     * is update query ?
     *
     * @access  public
     * @param   string  $sql
     * @return  boolean
     */
    public function isUpdateQuery($sql)
    {
        $sql = trim($sql);
        return preg_match('/^(INSERT|UPDATE|DELETE|BEGIN|COMMIT|CREATE|DROP|ALTER|GRANT|TRUNCATE)/i', $sql);
    }





    /**
     * get migration files.
     *
     * @access  public
     * @param   string  $path
     * @param   int     $from
     * @param   int     $to
     */
    public function getMigrationFiles($path, $from = NULL, $to = NULL)
    {
        if ( ! is_dir($path) ) throw new ActiveGateway_Exception('Invalid migration path. -> ' . $path);

        $files = array();
        $handle = opendir($path);
        while ( false !== ( $entry = readdir($handle) ) ) {
            if ( $entry === '.' || $entry === '..' ) continue;
            if ( ! preg_match('/^([0-9]+)_\w+.php$/', $entry, $matches) ) continue;

            // compare version.
            $file_version = (int)$matches[1];
            $file_path = $path . '/' . $entry;
            $class_name = 'Migration_' . join('_', array_map('ucfirst', explode('_', str_replace('.php', '', $entry))));
            if ( $from === NULL && $to === NULL ) {
                $files[$file_version] = array('path' => $file_path, 'class' => $class_name, 'version' => $file_version);
            } elseif ( $from !== NULL && $to !== NULL && $from <= $file_version && $file_version <= $to ) {
                $files[$file_version] = array('path' => $file_path, 'class' => $class_name, 'version' => $file_version);
            } elseif ( $from !== NULL && $from <= $file_version ) {
                $files[$file_version] = array('path' => $file_path, 'class' => $class_name, 'version' => $file_version);
            } elseif ( $to !== NULL && $file_version <= $to ) {
                $files[$file_version] = array('path' => $file_path, 'class' => $class_name, 'version' => $file_version);
            }
        }
        ksort($files);

        return $files;
    }
}

