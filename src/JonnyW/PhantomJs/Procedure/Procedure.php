<?php

/*
 * This file is part of the php-phantomjs.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace neokyuubi\PhantomJs\Procedure;

use neokyuubi\PhantomJs\Engine;
use neokyuubi\PhantomJs\Cache\CacheInterface;
use neokyuubi\PhantomJs\Parser\ParserInterface;
use neokyuubi\PhantomJs\Template\TemplateRendererInterface;
use neokyuubi\PhantomJs\Exception\NotWritableException;
use neokyuubi\PhantomJs\Exception\ProcedureFailedException;
use neokyuubi\PhantomJs\StringUtils;

/**
 * PHP PhantomJs
 *
 * @author Jon Wenmoth <contact@neokyuubi.me>
 */
class Procedure implements ProcedureInterface
{
    /**
     * PhantomJS engine
     *
     * @var \neokyuubi\PhantomJs\Engine
     * @access protected
     */
    protected $engine;

    /**
     * Parser instance.
     *
     * @var \neokyuubi\PhantomJs\Parser\ParserInterface
     * @access protected
     */
    protected $parser;

    /**
     * Cache handler instance.
     *
     * @var \neokyuubi\PhantomJs\Cache\CacheInterface
     * @access protected
     */
    protected $cacheHandler;

    /**
     * Template renderer.
     *
     * @var \neokyuubi\PhantomJs\Template\TemplateRendererInterface
     * @access protected
     */
    protected $renderer;

    /**
     * Procedure template.
     *
     * @var string
     * @access protected
     */
    protected $template;

    /**
     * Internal constructor.
     *
     * @access public
     * @param \neokyuubi\PhantomJs\Engine                             $engine
     * @param \neokyuubi\PhantomJs\Parser\ParserInterface             $parser
     * @param \neokyuubi\PhantomJs\Cache\CacheInterface               $cacheHandler
     * @param \neokyuubi\PhantomJs\Template\TemplateRendererInterface $renderer
     */
    public function __construct(Engine $engine, ParserInterface $parser, CacheInterface $cacheHandler, TemplateRendererInterface $renderer)
    {
        $this->engine       = $engine;
        $this->parser       = $parser;
        $this->cacheHandler = $cacheHandler;
        $this->renderer     = $renderer;
    }

    /**
     * Run procedure.
     *
     * @access public
     * @param  \neokyuubi\PhantomJs\Procedure\InputInterface           $input
     * @param  \neokyuubi\PhantomJs\Procedure\OutputInterface          $output
     * @throws \neokyuubi\PhantomJs\Exception\ProcedureFailedException
     * @throws \neokyuubi\PhantomJs\Exception\NotWritableException
     * @return void
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        try {

            $executable = $this->write(
                $this->compile($input)
            );

            $descriptorspec = array(
                array('pipe', 'r'),
                array('pipe', 'w'),
                array('pipe', 'w')
            );

            $process = proc_open(escapeshellcmd(sprintf('%s %s', $this->engine->getCommand(), $executable)), $descriptorspec, $pipes, null, null);

            if (!is_resource($process)) {
                throw new ProcedureFailedException('proc_open() did not return a resource');
            }

            $result = stream_get_contents($pipes[1]);
            $log    = stream_get_contents($pipes[2]);

            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);

            proc_close($process);

            $output->import(
                $this->parser->parse($result)
            );

            $this->engine->log($log);

            $this->remove($executable);

        } catch (NotWritableException $e) {
            throw $e;
        } catch (\Exception $e) {

            if (isset($executable)) {
                $this->remove($executable);
            }

            throw new ProcedureFailedException(sprintf('Error when executing PhantomJs procedure - %s', $e->getMessage()));
        }
    }

    /**
     * Set procedure template.
     *
     * @access public
     * @param  string                                $template
     * @return \neokyuubi\PhantomJs\Procedure\Procedure
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get procedure template.
     *
     * @access public
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Compile procedure.
     *
     * @access public
     * @param  \neokyuubi\PhantomJs\Procedure\InputInterface $input
     * @return void
     */
    public function compile(InputInterface $input)
    {
       return $this->renderer->render($this->getTemplate(), array('input' => $input));
    }

    /**
     * Write compiled procedure to cache.
     *
     * @access protected
     * @param  string $compiled
     * @return string
     */
    protected function write($compiled)
    {
        return $this->cacheHandler->save(StringUtils::random(20), $compiled);
    }

    /**
     * Remove procedure script cache.
     *
     * @access protected
     * @param  string $filePath
     * @return void
     */
    protected function remove($filePath)
    {
        $this->cacheHandler->delete($filePath);
    }
}
