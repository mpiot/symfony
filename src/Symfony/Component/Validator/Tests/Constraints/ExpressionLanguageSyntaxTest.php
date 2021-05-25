<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\Tests\Constraints;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\ExpressionLanguageSyntax;
use Symfony\Component\Validator\Constraints\ExpressionLanguageSyntaxValidator;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Loader\AnnotationLoader;

class ExpressionLanguageSyntaxTest extends TestCase
{
    public function testValidatedByStandardValidator()
    {
        $constraint = new ExpressionLanguageSyntax();

        self::assertSame(ExpressionLanguageSyntaxValidator::class, $constraint->validatedBy());
    }

    /**
     * @dataProvider provideServiceValidatedConstraints
     */
    public function testValidatedByService(ExpressionLanguageSyntax $constraint)
    {
        self::assertSame('my_service', $constraint->validatedBy());
    }

    public function provideServiceValidatedConstraints(): iterable
    {
        yield 'Doctrine style' => [new ExpressionLanguageSyntax(['service' => 'my_service'])];

        if (\PHP_VERSION_ID < 80000) {
            return;
        }

        yield 'named arguments' => [eval('return new \Symfony\Component\Validator\Constraints\ExpressionLanguageSyntax(service: "my_service");')];

        $metadata = new ClassMetadata(ExpressionLanguageSyntaxDummy::class);
        self::assertTrue((new AnnotationLoader())->loadClassMetadata($metadata));

        yield 'attribute' => [$metadata->properties['b']->constraints[0]];
    }

    /**
     * @requires PHP 8
     */
    public function testAttributes()
    {
        $metadata = new ClassMetadata(ExpressionLanguageSyntaxDummy::class);
        self::assertTrue((new AnnotationLoader())->loadClassMetadata($metadata));

        [$aConstraint] = $metadata->properties['a']->getConstraints();
        self::assertNull($aConstraint->service);
        self::assertNull($aConstraint->allowedVariables);
        self::assertFalse($aConstraint->allowNullAndEmptyString);

        [$bConstraint] = $metadata->properties['b']->getConstraints();
        self::assertSame('my_service', $bConstraint->service);
        self::assertSame('myMessage', $bConstraint->message);
        self::assertSame(['Default', 'ExpressionLanguageSyntaxDummy'], $bConstraint->groups);

        [$cConstraint] = $metadata->properties['c']->getConstraints();
        self::assertSame(['foo', 'bar'], $cConstraint->allowedVariables);
        self::assertSame(['my_group'], $cConstraint->groups);

        [$dConstraint] = $metadata->properties['d']->getConstraints();
        self::assertTrue($dConstraint->allowNullAndEmptyString);
    }
}

class ExpressionLanguageSyntaxDummy
{
    #[ExpressionLanguageSyntax]
    private $a;

    #[ExpressionLanguageSyntax(service: 'my_service', message: 'myMessage')]
    private $b;

    #[ExpressionLanguageSyntax(allowedVariables: ['foo', 'bar'], groups: ['my_group'])]
    private $c;

    #[ExpressionLanguageSyntax(allowNullAndEmptyString: true)]
    private $d;
}
