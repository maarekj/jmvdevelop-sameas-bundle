<?php

declare(strict_types=1);

namespace JmvDevelop\SameAsBundle\Tests;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

trait AssertViolationTrait
{
    protected function assertViolations(array $expected, ConstraintViolationListInterface $violations): void
    {
        $this->assertCount(\count($expected), $violations);
        foreach ($expected as $index => $expectedViolation) {
            $this->assertViolation($expectedViolation[0], $expectedViolation[1], $violations[$index]);
        }
    }

    protected function assertViolation(string $property, string $message, ConstraintViolationInterface $violation): void
    {
        $this->assertEquals($property, $violation->getPropertyPath());
        $this->assertEquals($message, $violation->getMessage());
    }
}
