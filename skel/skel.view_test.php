<?php
/**
 *  {$view_path}
 *
 *  @author     {$author}
 *  @package    {$project_id}
 *  @version    $Id$
 */

/**
 *  {$forward_name}�ӥ塼�μ���
 *
 *  @author     {$author}
 *  @access     public
 *  @package    {$project_id}
 */
class {$view_class}_TestCase extends Ethna_UnitTestCase
{
    /**
     *  @access private
     *  @var    string  �ӥ塼̾
     */
    var $forward_name = '{$forward_name}';

    /**
     *    �ƥ��Ȥν����
     *
     *    @access public
     */
    function setUp()
    {
        $this->createPlainActionForm(); // ���������ե�����κ���
        $this->createViewClass();       // �ӥ塼�κ���
    }

    /**
     *    �ƥ��Ȥθ����
     *
     *    @access public
     */
    function tearDown()
    {
    }

    /**
     *  {$forward_name}�����������Υ���ץ�ƥ��ȥ�����
     *
     *  @access public
     */
    /*
    function test_viewSample()
    {
        // �ե����������
        $this->af->set('id', 1);

        // {$forward_name}����������
        $this->vc->preforward();
        $this->assertNull($this->af->get('data'));
    }
    */
}

