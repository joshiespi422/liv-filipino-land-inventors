<?php

// This file is not executed — it only exists to help Intelephense
// understand the $this context inside Pest test closures.

namespace {
    use Tests\TestCase;
    use Pest\Expectation;

    if (false) {
        /**
         * @param-closure-this TestCase $closure
         */
        function test(string $description, \Closure $closure = null) {}

        /**
         * @param-closure-this TestCase $closure
         */
        function it(string $description, \Closure $closure = null) {}

        /**
         * @param-closure-this TestCase $closure
         */
        function beforeEach(\Closure $closure = null) {}

        /**
         * @param-closure-this TestCase $closure
         */
        function afterEach(\Closure $closure = null) {}

        /**
         * @template TValue
         * @param TValue $value
         * @return Expectation|TValue
         */
        function expect($value = null) {}
    }
}

namespace Pest {
    /**
     * @template TValue
     * @mixin \PHPUnit\Framework\Assert
     */
    class Expectation {
        /**
         * @param-closure-this Expectation $closure
         */
        public static function extend(string $name, \Closure $closure) {}
    }
}