<?php

/*
 * This file is part of the php-phantomjs.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace neokyuubi\PhantomJs\Tests\Unit\Template;

use Twig_Environment;
use Twig_Loader_String;
use neokyuubi\PhantomJs\Http\Request;
use neokyuubi\PhantomJs\Template\TemplateRenderer;

/**
 * PHP PhantomJs
 *
 * @author Jon Wenmoth <contact@neokyuubi.me>
 */
class TemplateRendererTest extends \PHPUnit_Framework_TestCase
{

/** +++++++++++++++++++++++++++++++++++ **/
/** ++++++++++++++ TESTS ++++++++++++++ **/
/** +++++++++++++++++++++++++++++++++++ **/

    /**
     * Test render injects single parameter
     * into template.
     *
     * @access public
     * @return void
     */
    public function testRenderInjectsSingleParameterIntoTemplate()
    {
        $template = 'var param = "{{ test }}"';

        $renderer = $this->getTemplateRenderer();
        $result   = $renderer->render($template, array('test' => 'data'));

        $this->assertSame('var param = "data"', $result);
    }

    /**
     * Test render injects multiple parameters
     * into template.
     *
     * @access public
     * @return void
     */
    public function testRenderInjectsMultipleParametersIntoTemplates()
    {
        $template = 'var param = "{{ test }}", var param2 = "{{ test2 }}"';

        $renderer = $this->getTemplateRenderer();
        $result   = $renderer->render($template, array('test' => 'data', 'test2' => 'more data'));

        $this->assertSame('var param = "data", var param2 = "more data"', $result);
    }

    /**
     * Test render injects parameter into
     * template using object method.
     *
     * @access public
     * @return void
     */
    public function testRenderInjectsParameterIntoTemplateUsingObjectMethod()
    {
        $template = 'var param = {{ request.getTimeout() }}';

        $request = $this->getRequest();
        $request->setTimeout(5000);

        $renderer = $this->getTemplateRenderer();
        $result   = $renderer->render($template, array('request' => $request));

        $this->assertSame('var param = 5000', $result);
    }

    /**
     * Test render injects parameter into
     * template using object method
     * with parameter.
     *
     * @access public
     * @return void
     */
    public function testRenderInjectsParameterIntoTemplateUsingObjectMethodWithParameter()
    {
        $template = 'var param = {{ request.getHeaders("json") }}';

        $request = $this->getRequest();
        $request->addHeader('json', 'test');

        $renderer = $this->getTemplateRenderer();
        $result = $renderer->render($template, array('request' => $request));

        $this->assertSame(htmlspecialchars('var param = {"json":"test"}'), $result);
    }

/** +++++++++++++++++++++++++++++++++++ **/
/** ++++++++++ TEST ENTITIES ++++++++++ **/
/** +++++++++++++++++++++++++++++++++++ **/

    /**
     * Get template renderer instance.
     *
     * @return \neokyuubi\PhantomJs\Message\TemplateRenderer
     */
    protected function getTemplateRenderer()
    {
        $templateRenderer = new TemplateRenderer(
            $this->getTwig()
        );

        return $templateRenderer;
    }

    /**
     * Get request
     *
     * @access protected
     * @return \neokyuubi\PhantomJs\Http\Request
     */
    protected function getRequest()
    {
        $request = new Request();

        return $request;
    }

    /**
     * Get twig
     *
     * @access protected
     * @return \Twig_Environment
     */
    protected function getTwig()
    {
        $twig = new Twig_Environment(
            new Twig_Loader_String()
        );

        return $twig;
    }
}
