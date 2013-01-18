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
                $options[] = 'ENGINE = ' . $engine;
            }
            if ( $charset = $table->getCharset() ) {
                $options[] = 'DEFAULT CHARSET = ' . $charset;
            }
            if ( $collate = $table->getCollate() ) {
                $options[] = 'COLLATE = ' . $collate;
            }
            if ( $comment = $table->getComment() ) {
                $bind_key = $this->_generateBindKey('table_comment', $table->getName());
                $options[] = 'COMMENT = ' . $bind_key;
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
        $values = array();
        $values[] = $this->escapeColumn($column->getName());
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
        $sql = join(' ', $values);
        return $sql;
    }
    
    
    /**
     * @implements
     */
    public function indexToSql(ActiveGateway_Schema_Index $index, array &$params)
    {
        $sql = array();
        $sql[] = sprintf('ALTER TABLE %s', $this->escapeColumn($index->getTableName()));
        $sql[] = sprintf('ADD INDEX %s', $index->getName());
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
}

