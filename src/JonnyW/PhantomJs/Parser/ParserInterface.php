<?php

/*
 * This file is part of the php-phantomjs.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace neokyuubi\PhantomJs\Parser;

/**
 * PHP PhantomJs
 *
 * @author Jon Wenmoth <contact@neokyuubi.me>
 */
interface ParserInterface
{
    /**
     * Parse data.
     *
     * @access public
     * @param mixed $data
     */
    public function parse($data);
}
