<?php

namespace kafene\Tests;

use PHPUnit_Framework_TestCase;
use function kafene\{jprintf, jvprintf, jsprintf, jvsprintf, jfprintf, jvfprintf};

class jprintfTest extends PHPUnit_Framework_TestCase
{
    /** @dataProvider getTestData */
    public function testjprintf(string $format, array $args, string $expectedOutput, int $expectedReturn)
    {
        ob_start();
        $return = jprintf($format, ...$args);
        $output = ob_get_clean();

        $this->assertSame($expectedOutput, $output);
        $this->assertSame($expectedReturn, $return);
    }

    /** @dataProvider getTestData */
    public function testjvprintf(string $format, array $args, string $expectedOutput, int $expectedReturn)
    {
        ob_start();
        $return = jvprintf($format, $args);
        $output = ob_get_clean();

        $this->assertSame($expectedOutput, $output);
        $this->assertSame($expectedReturn, $return);
    }

    /** @dataProvider getTestData */
    public function testjsprintf(string $format, array $args, string $expectedOutput, int $expectedReturn)
    {
        $output = jsprintf($format, ...$args);

        $this->assertSame($expectedOutput, $output);
    }

    /** @dataProvider getTestData */
    public function testjvsprintf(string $format, array $args, string $expectedOutput, int $expectedReturn)
    {
        $output = jvsprintf($format, $args);

        $this->assertSame($expectedOutput, $output);
    }

    /** @dataProvider getTestData */
    public function testjfprintf(string $format, array $args, string $expectedOutput, int $expectedReturn)
    {
        $fp = fopen('php://memory', 'r+');
        $return = jfprintf($fp, $format, ...$args);
        rewind($fp);
        $output = stream_get_contents($fp);

        $this->assertSame($expectedOutput, $output);
        $this->assertSame($expectedReturn, $return);
    }

    /** @dataProvider getTestData */
    public function testjfvprintf(string $format, array $args, string $expectedOutput, int $expectedReturn)
    {
        $fp = fopen('php://memory', 'r+');
        $return = jvfprintf($fp, $format, $args);
        rewind($fp);
        $output = stream_get_contents($fp);

        $this->assertSame($expectedOutput, $output);
        $this->assertSame($expectedReturn, $return);
    }

    public function testJsonFlags()
    {
        $format = 'Hello %s';
        $args = (object) ['wo\\rld'];
        $expectedOutput = 'Hello {"0":"wo\\\\rld"}';

        $output = jsprintf($format, $args);

        $this->assertSame($expectedOutput, $output);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Inf and NaN cannot be JSON encoded
     */
    public function testThrowsExceptionsOnInvalidInput()
    {
        $output = jvsprintf('Your number is: %.2f', [ INF ]);
    }

    public function testJsonPartialOutputOnError()
    {
        $output = jvsprintf('Your number is: %.2f', [ INF ], JSON_PARTIAL_OUTPUT_ON_ERROR);

        $this->assertSame('Your number is: 0.00', $output);
    }

    public function getTestData()
    {
        return [
            [
                '%s and %s each ate %x %s, they tasted %s, %2.2f%% of people %s %s.',
                [
                    ['name' => 'Sarah', 'age' => 100],
                    (object) ['name' => 'Baxter', 'age' => 50, null],
                    2929,
                    'apples',
                    false,
                    99.5,
                    ['think', 'they', 'taste'],
                    null,
                ],
                '{"name":"Sarah","age":100} and {"name":"Baxter","age":50,"0":null} each ate b71 apples, they tasted false, 99.50% of people ["think","they","taste"] null.',
                154,
            ],
        ];
    }
}
