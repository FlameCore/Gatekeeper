<?php
/**
 * Webtools Library
 * Copyright (C) 2014 IceFlame.net
 *
 * Permission to use, copy, modify, and/or distribute this software for
 * any purpose with or without fee is hereby granted, provided that the
 * above copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE
 * FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY
 * DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER
 * IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING
 * OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 *
 * @package  FlameCore\Webtools
 * @version  1.2
 * @link     http://www.flamecore.org
 * @license  ISC License <http://opensource.org/licenses/ISC>
 */

namespace FlameCore\Gatekeeper\Tests;

use FlameCore\Gatekeeper\Check\BlacklistCheck;
use FlameCore\Gatekeeper\Screener;
use FlameCore\Gatekeeper\Visitor;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test class for Screener
 */
class ScreenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \FlameCore\Gatekeeper\Screener
     */
    private $screener;

    protected function setUp()
    {
        $this->screener = new Screener();
        $this->screener->setWhitelist(['127.0.0.1/32', '127.0.0.2']);

        $check = new BlacklistCheck();
        $check->setBlacklist(['127.0.0.3/32']);
        $this->screener->addCheck($check);
    }

    public function testWhitelist()
    {
        /** @var \FlameCore\Gatekeeper\Result\NegativeResult $result */
        $result = $this->runTestScreening('127.0.0.2');

        $this->assertInstanceOf('FlameCore\Gatekeeper\Result\NegativeResult', $result);

        $expected = [get_class($this->screener)];
        $this->assertEquals($expected, $result->getReportingClasses());
    }

    public function testPositive()
    {
        /** @var \FlameCore\Gatekeeper\Result\PositiveResult $result */
        $result = $this->runTestScreening('127.0.0.3');

        $this->assertInstanceOf('FlameCore\Gatekeeper\Result\PositiveResult', $result);

        $this->assertEquals(null, $result->getExplanation());

        $expected = array_map('get_class', $this->screener->getChecks());
        $this->assertEquals($expected, $result->getReportingClasses());
    }

    /**
     * @param string $ip
     * @return \FlameCore\Gatekeeper\Result\ResultInterface
     */
    protected function runTestScreening($ip)
    {
        $request = Request::create('/', null, [], [], [], ['REMOTE_ADDR' => $ip], null);
        $visitor = new Visitor($request);

        return $this->screener->screenVisitor($visitor);
    }
}
