<?php

/*
 * This file is part of the php-phantomjs.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace neokyuubi\PhantomJs\Procedure;

/**
 * PHP PhantomJs
 *
 * @author Jon Wenmoth <contact@neokyuubi.me>
 */
interface OutputInterface
{
    /**
     * Import data.
     *
     * @param array $data
     * @access public
     */
    public function import(array $data);
}
