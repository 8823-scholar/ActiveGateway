<?php
/**
 * SPEC: Connection
 * 
 * @package     ActiveGateway
 * @subpackage  Spec
 * @copyright   Samurai Framework Project
 * @author      KIUCHI Satoshinosuke <scholar@hayabusa-lab.jp>
 * @license     http://www.opensource.org/licenses/bsd-license.php The BSD License
 */
class Connection_Spec_Context extends Samurai_Spec_Context_PHPSpec
{
    // @dependencies
    public $AGManager;


    // add spec method.
    // method name is need to start "it".

    public function itShouldBeThrownExceptionIfNotExistsAlias()
    {
        try {
            $AG = $this->AGManager->get('foo');
        } catch(Exception $E) {
            $this->spec($E)->should->beAnInstanceOf('ActiveGateway_Exception_Config');
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

        $this->AGManager = ActiveGateway::getManager();
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

