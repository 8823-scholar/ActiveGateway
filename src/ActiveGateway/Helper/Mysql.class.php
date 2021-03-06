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

require_once dirname(__DIR__) . '/Helper.class.php';

/**
 * Helper class for MySQL.
 * 
 * @package     ActiveGateway
 * @subpackage  Helper
 * @copyright   Samurai Framework Project
 * @author      KIUCHI Satoshinosuke <scholar@hayabusa-lab.jp>
 * @license     http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class ActiveGateway_Helper_Mysql extends ActiveGateway_Helper
{
    /**
     * @override
     */
    public function escapeColumn($column_name)
    {
        return sprintf('`%s`', $column_name);
    }





    /**
     * @implements
     */
    public function tableToSQL(ActiveGateway_Schema_Table $table, array &$params)
    {
        $sql = array();

        // if drop.
        if ( $table->isDrop() ) {
            $sql[] = sprintf("DROP TABLE IF EXISTS %s;", $this->escapeColumn($table->getName()));

        } else {
            $sql[] = sprintf("CREATE TABLE IF NOT EXISTS %s (", $this->escapeColumn($table->getName()));

            $sql_parts = array();
            foreach ( $table->getColumns() as $column ) {
                $sql_parts[] = '    ' . $column->toSQL($params);
            }
            foreach ( $table->getKeys() as $key ) {
                $sql_parts[] = '    ' . $key->toSQL($params);
            }
            $sql[] = join(",\n", $sql_parts);

            $options = array();
            if ( $engine = $table->getEngine() ) {
                $options[] = 'ENGINE=' . $engine;
            }
            if ( $format = $table->getRowFormat() ) {
                $options[] = 'ROW_FORMAT=' . $format;
            }
            if ( $charset = $table->getCharset() ) {
                $options[] = 'DEFAULT CHARSET=' . $charset;
            }
            if ( $collate = $table->getCollate() ) {
                $options[] = 'COLLATE=' . $collate;
            }
            if ( $comment = $table->getComment() ) {
                $bind_key = $this->_generateBindKey('table_comment', $table->getName());
                $options[] = 'COMMENT=' . $bind_key;
                $params[$bind_key] = $comment;
            }
            $sql[] = ') ' . join(' ', $options) . ';';
        }

        return join("\n", $sql);
    }

    /**
     * @implements
     */
    public function columnToSql(ActiveGateway_Schema_Column $column, array &$params)
    {
        $sql = array();

        // if alter
        $need_column_define = true;
        if ( ! $column->hasTable() ) {
            $sql[] = sprintf('ALTER TABLE %s', $this->escapeColumn($column->getTableName()));
            if ( $column->isDrop() ) {
                $need_column_define = false;
                $sql[] = sprintf('DROP COLUMN', $this->escapeColumn($column->getName()));
            } else {
                $sql[] = sprintf('ADD COLUMN', $this->escapeColumn($column->getName()));
            }
        }

        $values = array();
        $values[] = $this->escapeColumn($column->getName());

        if ( $need_column_define ) {
            $values[] = $this->columnTypeToSQL($column->getType(), $column->getTypeLength(), $params);
            if ( ! $column->isEnableNull() ) {
                $values[] = 'NOT NULL';
            }
            $default = $column->getDefaultValue();
            if ( $default === NULL && $column->isEnableNull() ) {
                $values[] = 'DEFAULT NULL';
            } elseif ( $default !== NULL ) {
                $bind_key = $this->_generateBindKey('column_default', $column->getName());
                $values[] = 'DEFAULT ' . $bind_key;
                $params[$bind_key] = $default;
            }
            if ( $column->isAutoIncrement() ) {
                $values[] = 'AUTO_INCREMENT';
            }
            if ( $collate = $column->getCollate() ) {
                $values[] = 'COLLATE ' . $collate;
            }

            if ( $comment = $column->getComment() ) {
                $bind_key = $this->_generateBindKey('column_comment', $column->getName());
                $values[] = 'COMMENT ' . $bind_key;
                $params[$bind_key] = $comment;
            }

            if ( $after = $column->getAfter() ) {
                $values[] = 'AFTER ' . $this->escapeColumn($after);
            }
        }
        $sql[] = join(' ', $values);

        if ( ! $column->hasTable() ) {
            $sql = join(' ', $sql) . ';';
        } else {
            $sql = join(' ', $sql);
        }

        return $sql;
    }
    
    
    /**
     * @implements
     */
    public function indexToSql(ActiveGateway_Schema_Index $index, array &$params)
    {
        $sql = array();
        
        // if drop.
        $sql[] = sprintf('ALTER TABLE %s', $this->escapeColumn($index->getTableName()));
        if ( $index->isDrop() ) {
            $sql[] = sprintf('DROP INDEX %s', $this->escapeColumn($index->getName()));

        } else {
            $sql[] = sprintf('ADD INDEX %s', $this->escapeColumn($index->getName()));
            $columns = array();
            foreach ( $index->getColumns() as $column ) {
                $columns[] = $this->escapeColumn($column);
            }
            $sql[] = sprintf('(%s)', join(', ', $columns));
        }
        
        $sql = join(' ', $sql) . ';';
        return $sql;
    }

    /**
     * @implements
     */
    public function uniqueIndexToSql(ActiveGateway_Schema_Unique $index, array &$params)
    {
        $sql = array();
        $sql[] = sprintf('ALTER TABLE %s', $this->escapeColumn($index->getTableName()));
        $sql[] = sprintf('ADD UNIQUE %s', $index->getName());
        $columns = array();
        foreach ( $index->getColumns() as $column ) {
            $columns[] = $this->escapeColumn($column);
        }
        $sql[] = sprintf('(%s)', join(', ', $columns));
        $sql = join(' ', $sql) . ';';
        return $sql;
    }

    /**
     * @implements
     */
    public function primaryIndexToSql(ActiveGateway_Schema_Primary $index)
    {
        $values = array();
        $values[] = 'PRIMARY KEY';
        $columns = array();
        foreach ( $index->getColumns() as $column ) {
            $columns[] = $this->escapeColumn($column);
        }
        $values[] = sprintf('(%s)', join(', ', $columns));
        $sql = join(' ', $values);
        return $sql;
    }





    /**
     * @override
     */
    public function convertColumnType($type)
    {
        $converted = $type;

        switch ( $type ) {
        case ActiveGateway_Schema::COLUMN_TYPE_STRING:
            $converted = 'VARCHAR';
            break;
        case ActiveGateway_Schema::COLUMN_TYPE_LIST:
            $converted = 'ENUM';
            break;
        }

        return $converted;
    }
    
    /**
     * @override
     */
    public function reverseConvertColumnType($type)
    {
        $converted = $type;

        switch ( strtoupper($type) ) {
        case 'VARCHAR':
            $converted = ActiveGateway_Schema::COLUMN_TYPE_STRING;
            break;
        case 'ENUM':
            $converted = ActiveGateway_Schema::COLUMN_TYPE_LIST;
            break;
        case 'INT':
        case 'TINYINT':
        case 'SMALLINT':
        case 'MEDIUMINT':
        case 'BIGINT':
            $converted = ActiveGateway_Schema::COLUMN_TYPE_INTEGER;
            break;
        }

        return $converted;
    }





    /**
     * @implements
     */
    public function getTables(ActiveGateway $AG)
    {
        $sql = "SHOW TABLES;";
        $result = $AG->getCol($sql);
        return $result;
    }



    /**
     * @implements
     */
    public function getDefinesByTableName(ActiveGateway $AG, $table_name)
    {
        $sql = sprintf("SHOW CREATE TABLE %s;", $this->escapeColumn($table_name));
        $result = $AG->getOne($sql, array(), 1);
        $schema = new ActiveGateway_Schema();
        foreach ( explode("\n", $result) as $line ) {
            $line = trim($line);
            switch ( true ) {
            case strpos($line, 'CREATE TABLE') === 0:
                list($name) = sscanf($line, 'CREATE TABLE `%[^`]`');
                $table = $schema->createTable($name);
                break;
            case strpos($line, 'PRIMARY KEY') === 0:
                list($keys) = sscanf($line, 'PRIMARY KEY (%[^()])');
                $keys = explode(',', str_replace(array('`', ' '), '', $keys));
                $table->primary($keys);
                break;
            case strpos($line, 'UNIQUE KEY') === 0:
                list($name, $keys) = sscanf($line, 'UNIQUE KEY `%[^`]` (%[^()])');
                $keys = explode(',', str_replace(array('`', ' '), '', $keys));
                $unique = $schema->createUnique($table->getName(), $keys)->setName($name);
                break;
            case strpos($line, 'KEY') === 0:
                list($name, $keys) = sscanf($line, 'KEY `%[^`]` (%[^()])');
                $keys = explode(',', str_replace(array('`', ' '), '', $keys));
                $index = $schema->createIndex($table->getName(), $keys)->setName($name);
                break;
            case strpos($line, ')') === 0:
                if ( preg_match('/ENGINE=(\w+)/', $line, $matches) ) {
                    $table->engine($matches[1]);
                }
                if ( preg_match('/DEFAULT CHARSET=(\w+)/', $line, $matches) ) {
                    $table->charset($matches[1]);
                }
                if ( preg_match('/COLLATE=(\w+)/', $line, $matches) ) {
                    $table->collate($matches[1]);
                }
                if ( preg_match('/COMMENT=\'(.+?)\'/', $line, $matches) ) {
                    $table->comment($matches[1]);
                }
                break;
            default:
                if ( preg_match('/^`(\w+)`/', $line, $matches) ) {
                    $name = $matches[1];
                }
                if ( $name === 'id' ) break;
                $column = $table->column($name);
                if ( preg_match('/(\w+)\((\d+?)\)/', $line, $matches) ) {
                    $type = $this->reverseConvertColumnType($matches[1]);
                    $column->type($type, $matches[2]);
                } elseif ( preg_match('/(\w+)\((.+?)\)/', $line, $matches) ) {
                    $type = $this->reverseConvertColumnType($matches[1]);
                    $length = str_getcsv($matches[2], ',', "'");
                    $column->type($type, $length);
                } else {
                    throw new ActiveGateway_Exception('failed to parse schema line. -> ' . $line);
                }
                $column->enableNull();
                if ( preg_match('/NOT NULL/', $line) ) {
                    $column->notNull();
                }
                if ( preg_match('/DEFAULT \'(.+?)\'/', $line, $matches) ) {
                    $column->defaultValue($matches[1]);
                }
                if ( preg_match('/COLLATE=(\w+)/', $line, $matches) ) {
                    $column->collate($matches[1]);
                }
                if ( preg_match('/COMMENT=\'(.+?)\'/', $line, $matches) ) {
                    $column->comment($matches[1]);
                }
                break;
            }
        }
        $params = array();
        return $schema->getDefines();
    }
}

