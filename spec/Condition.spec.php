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
 * SPEC: Condition
 *
 * @package     ActiveGateway
 * @subpackage  Spec
 * @copyright   Samurai Framework Project
 * @author      KIUCHI Satoshinosuke <scholar@hayabusa-lab.jp>
 * @license     http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class Condition_Spec_Context extends Samurai_Spec_Context_PHPSpec
{
    // @dependencies
    public $AG;


    // add spec method.
    // method name is need to start "it".
    
    public function it基本形()
    {
        $cond = $this->AG->getCondition();
        $this->spec($cond)->should->beAnInstanceOf('ActiveGateway_Condition');
        $this->spec($cond->where)->should->beAnInstanceOf('ActiveGateway_Condition_Values');

        //キー指定
        $cond->select = '*';
        $cond->from = 'some';
        $cond->where->id = 1;
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` WHERE `id` = :param0');
        foreach($cond->getparams() as $_key => $_val){
            switch($_key){
            case ':param0':
                $this->spec($_val)->should->be(1);
                break;
            }
        }
    }

    public function it基本形：否定()
    {
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $cond->where->id = $cond->isNotEqual(1);
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` WHERE `id` != :param0');
        foreach($cond->getparams() as $_key => $_val){
            switch($_key){
            case ':param0':
                $this->spec($_val)->should->be(1);
                break;
            }
        }
    }

    public function itSelectに配列()
    {
        $cond = $this->AG->getCondition();
        $cond->select = array('key1', 'key2');
        $cond->from = 'some';
        $cond->where->id = 1;
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT key1, key2 FROM `some` WHERE `id` = :param0');
    }

    public function itLessAndGreater()
    {
        //小なり
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $cond->where->id = $cond->isLessThan(10);
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` WHERE `id` <= :param0');
        foreach($cond->getparams() as $_key => $_val){
            switch($_key){
            case ':param0':
                $this->spec($_val)->should->be(10);
                break;
            }
        }

        //大なり
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $cond->where->id = $cond->isGreaterThan(10);
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` WHERE `id` >= :param0');
        foreach($cond->getparams() as $_key => $_val){
            switch($_key){
            case ':param0':
                $this->spec($_val)->should->be(10);
                break;
            }
        }
    }

    public function itIsNull()
    {
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $cond->where->key = NULL;
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` WHERE `key` IS NULL');
    }

    public function itIsNotNull()
    {
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $cond->where->key = $cond->isNotEqual(NULL);
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` WHERE `key` IS NOT NULL');
    }

    public function itIsLike()
    {
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $cond->where->mail = $cond->isLike('%@befool.co.jp');
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` WHERE `mail` LIKE :param0');
        foreach($cond->getparams() as $_key => $_val){
            switch($_key){
            case ':param0':
                $this->spec($_val)->should->be('%@befool.co.jp');
                break;
            }
        }
    }

    public function itIsNotLike()
    {
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $cond->where->mail = $cond->isNotLike('%@befool.co.jp');
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` WHERE `mail` NOT LIKE :param0');
        foreach($cond->getparams() as $_key => $_val){
            switch($_key){
            case ':param0':
                $this->spec($_val)->should->be('%@befool.co.jp');
                break;
            }
        }
    }

    public function itIsIn()
    {
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $cond->where->key = $cond->isIn(1, 2, 3);
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` WHERE `key` IN (:param0, :param1, :param2)');
        foreach($cond->getparams() as $_key => $_val){
            switch($_key){
            case ':param0':
                $this->spec($_val)->should->be(1);
                break;
            case ':param0':
                $this->spec($_val)->should->be(2);
                break;
            case ':param0':
                $this->spec($_val)->should->be(3);
                break;
            }
        }
    }

    public function itIsNotIn()
    {
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $cond->where->key = $cond->isNotIn(1, 2, 3);
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` WHERE `key` NOT IN (:param0, :param1, :param2)');
        foreach($cond->getparams() as $_key => $_val){
            switch($_key){
            case ':param0':
                $this->spec($_val)->should->be(1);
                break;
            case ':param0':
                $this->spec($_val)->should->be(2);
                break;
            case ':param0':
                $this->spec($_val)->should->be(3);
                break;
            }
        }
    }

    public function itIsAnd：従来型()
    {
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $cond->where->key = $cond->isAND(1,2);
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` WHERE ( `key` = :param0 AND `key` = :param1 )');
        foreach($cond->getparams() as $_key => $_val){
            switch($_key){
            case ':param0':
                $this->spec($_val)->should->be(1);
                break;
            case ':param0':
                $this->spec($_val)->should->be(2);
                break;
            }
        }

        //少し複雑な例
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $cond->where->key = $cond->isAND($cond->isNotEqual(NULL), 2);
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` WHERE ( `key` IS NOT NULL AND `key` = :param0 )');
        foreach($cond->getparams() as $_key => $_val){
            switch($_key){
            case ':param0':
                $this->spec($_val)->should->be(2);
                break;
            }
        }
    }

    public function itIsAnd：発展型()
    {
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $and = $cond->where->append($cond->isAND());
        $and->key1 = 1;
        $and->key2 = 2;
        $and = $cond->where->append($cond->isAND());
        $and->key1 = 3;
        $and->key2 = 4;
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` WHERE ( `key1` = :param0 AND `key2` = :param1 )'
            . ' AND ( `key1` = :param2 AND `key2` = :param3 )');
        foreach($cond->getparams() as $_key => $_val){
            switch($_key){
            case ':param0':
                $this->spec($_val)->should->be(1);
                break;
            case ':param1':
                $this->spec($_val)->should->be(2);
                break;
            case ':param2':
                $this->spec($_val)->should->be(3);
                break;
            case ':param3':
                $this->spec($_val)->should->be(4);
                break;
            }
        }
    }

    public function itIsOr：従来型()
    {
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $cond->where->key = $cond->isOR(1,2);
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` WHERE ( `key` = :param0 OR `key` = :param1 )');
        foreach($cond->getparams() as $_key => $_val){
            switch($_key){
            case ':param0':
                $this->spec($_val)->should->be(1);
                break;
            case ':param0':
                $this->spec($_val)->should->be(2);
                break;
            }
        }

        //少し複雑な例
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $cond->where->key = $cond->isOR($cond->isEqual(NULL), 2);
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` WHERE ( `key` IS NULL OR `key` = :param0 )');
        foreach($cond->getparams() as $_key => $_val){
            switch($_key){
            case ':param0':
                $this->spec($_val)->should->be(2);
                break;
            }
        }
    }

    public function itIsOr：発展型()
    {
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $or = $cond->where->append($cond->isOR());
        $or->key1 = 1;
        $or->key2 = 2;
        $or = $cond->where->append($cond->isOR());
        $or->key1 = 3;
        $or->key2 = 4;
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` WHERE ( `key1` = :param0 OR `key2` = :param1 )'
            . ' AND ( `key1` = :param2 OR `key2` = :param3 )');
        foreach($cond->getparams() as $_key => $_val){
            switch($_key){
            case ':param0':
                $this->spec($_val)->should->be(1);
                break;
            case ':param1':
                $this->spec($_val)->should->be(2);
                break;
            case ':param2':
                $this->spec($_val)->should->be(3);
                break;
            case ':param3':
                $this->spec($_val)->should->be(4);
                break;
            }
        }
    }

    public function itIsNativeQuery()
    {
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $cond->where->key = 'type';
        $cond->where->append($cond->isNative('`a` > :p1 AND `b` < :p2', array(':p1' => 1, ':p2' => 2)));
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` WHERE `key` = :param0 AND `a` > :p1 AND `b` < :p2');
        foreach($cond->getparams() as $_key => $_val){
            switch($_key){
            case ':param0':
                $this->spec($_val)->should->be('type');
                break;
            case ':p1':
                $this->spec($_val)->should->be(1);
                break;
            case ':p2':
                $this->spec($_val)->should->be(2);
                break;
            }
        }
    }

    // 廃止予定
    public function itAddtionalWhere()
    {
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $cond->where->key = 'type';
        $cond->addtional_where = '`a` > :p1 AND `b` < :p2';
        $cond->addParam(1, ':p1');
        $cond->addParam(2, ':p2');
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` WHERE `key` = :param2 AND `a` > :p1 AND `b` < :p2');
        foreach($cond->getparams() as $_key => $_val){
            switch($_key){
            case ':param2':
                $this->spec($_val)->should->be('type');
                break;
            case ':p1':
                $this->spec($_val)->should->be(1);
                break;
            case ':p2':
                $this->spec($_val)->should->be(2);
                break;
            }
        }
    }
    
    
    public function itOrder()
    {
        //デフォルトはASC
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $cond->setOrder('something');
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` ORDER BY `something` ASC');
        
        //DESC指定
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $cond->setOrder('something', 'DESC');
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` ORDER BY `something` DESC');
    }

    public function itOrder：関数()
    {
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $cond->setOrder('RAND()', NULL);
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` ORDER BY RAND()');

        //併用
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $cond->setOrder('FIELD(id, 1, 2, 3)', NULL);
        $cond->setOrder('sort', 'DESC');
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` ORDER BY FIELD(id, 1, 2, 3), `sort` DESC');
    }


    public function itGroup()
    {
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $cond->setGroup('something');
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` GROUP BY `something`');

        //2つ指定
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $cond->setGroup('key1', 'key2');
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` GROUP BY `key1`, `key2`');
    }


    public function it可能な限り複雑なパターン()
    {
        $cond = $this->AG->getCondition();
        $cond->select = '*';
        $cond->from = 'some';
        $cond->where->key = 'type';
        $and = $cond->where->append($cond->isAND());
        $and->key1 = 1;
        $and->key2 = $cond->isLessThan(10);
        $and->key3 = $cond->isGreaterThan(10);
        $and->key4 = $cond->isOR(1,NULL);
        $and = $cond->where->append($cond->isOR());
        $and->key1 = 1;
        $and->key2 = $cond->isLessThan(10);
        $and->key3 = $cond->isGreaterThan(10);
        $and->key4 = $cond->isAND(1,$cond->isNotEqual(NULL));
        $cond->where->append($cond->isNative('`a` > :p1 AND `b` < :p2', array(':p1' => 1, ':p2' => 2)));
        $sql = $this->AG->makeSelectQuery($cond);
        $this->spec($sql)->should->be('SELECT * FROM `some` WHERE `key` = :param0'
            . ' AND ( `key1` = :param1 AND `key2` <= :param2 AND `key3` >= :param3 AND ( `key4` = :param4 OR `key4` IS NULL ) )'
            . ' AND ( `key1` = :param5 OR `key2` <= :param6 OR `key3` >= :param7 OR ( `key4` = :param8 AND `key4` IS NOT NULL ) )'
            . ' AND `a` > :p1 AND `b` < :p2');
        foreach($cond->getparams() as $_key => $_val){
            switch($_key){
            case ':param0':
                $this->spec($_val)->should->be('type');
                break;
            case ':param1':
            case ':param4':
            case ':param5':
            case ':param8':
                $this->spec($_val)->should->be(1);
                break;
            case ':param2':
            case ':param3':
            case ':param6':
            case ':param7':
                $this->spec($_val)->should->be(10);
                break;
            case ':p1':
                $this->spec($_val)->should->be(1);
                break;
            case ':p2':
                $this->spec($_val)->should->be(2);
                break;
            }
        }
    }



    public function itUpdate用()
    {
        $cond = $this->AG->getCondition();
        $cond->from = 'some';
        $cond->where->id = 1;
        $cond->where->active = '1';
        $attributes = array('name' => 'a', 'level' => 10);
        $sql = $this->AG->makeUpdateQuery($cond, $attributes);
        $this->spec($sql)->should->be('UPDATE `some` SET `name` = :param0, `level` = :param1'
            . ' WHERE `id` = :param2 AND `active` = :param3');
        foreach($cond->getParams() as $_key => $_val){
            switch($_key){
            case ':param0':
                $this->spec($_val)->should->be('a');
                break;
            case ':param1':
                $this->spec($_val)->should->be(10);
                break;
            case ':param2':
                $this->spec($_val)->should->be(1);
                break;
            case ':param3':
                $this->spec($_val)->should->be('1');
                break;
            }
        }
    }
    
    public function itDelete用()
    {
        $cond = $this->AG->getCondition();
        $cond->from = 'some';
        $cond->where->id = 1;
        $cond->where->active = '1';
        $sql = $this->AG->makeDeleteQuery($cond);
        $this->spec($sql)->should->be('DELETE FROM `some` WHERE `id` = :param0 AND `active` = :param1');
        foreach($cond->getParams() as $_key => $_val){
            switch($_key){
            case ':param0':
                $this->spec($_val)->should->be(1);
                break;
            case ':param1':
                $this->spec($_val)->should->be('1');
                break;
            }
        }
    }



    public function it自由()
    {
        $cond = $this->AG->getCondition();
        $cond->from = 'some';
        $cond->where->id = 1;
        $cond->where->active = '1';
    }



    public function it下位互換：同キー指定()
    {
        $cond = $this->AG->getCondition();
        $cond->from = 'some';
        $cond->where->id = 1;
        $cond->where->id = 2;
        $query = $this->AG->makeSelectQuery($cond);
        $this->spec($query)->should->be('SELECT * FROM `some` WHERE `id` = :param0');
        foreach($cond->getParams() as $_key => $_val){
            switch($_key){
            case ':param0':
                $this->spec($_val)->should->be(2);
                break;
            }
        }
    }

    public function it下位互換：使い回し()
    {
        $cond = $this->AG->getCondition();
        $cond->from = 'some';
        for($i = 0; $i < 2; $i++){
            $cond->where->id = $i;
            $query = $this->AG->makeSelectQuery($cond);
            $params = $cond->getParams();
            $this->spec(count($params))->should->be(1);
            $cond->clearParams();
        }
    }

    


    /**
     * before case.
     *
     * @access  public
     */
    public function before()
    {
    }

    /**
     * after case.
     *
     * @access  public
     */
    public function after()
    {
    }

    /**
     * before all cases.
     *
     * @access  public
     */
    public function beforeAll()
    {
        $this->_injectDependencies();
        $this->_setupFixtures();
        $this->AG = ActiveGateway::getManager()->get('base');
    }

    /**
     * after all cases.
     *
     * @access  public
     */
    public function afterAll()
    {
        $this->_clearFixtures();
    }
}

