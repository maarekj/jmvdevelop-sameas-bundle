<?php

declare(strict_types=1);

namespace JmvDevelop\SameAsBundle;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Mapping\ClassMetadataInterface;
use Symfony\Component\Validator\Mapping\PropertyMetadataInterface;

final class SameAsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof SameAs) {
            throw new UnexpectedTypeException($constraint, SameAs::class);
        }

        $class = $constraint->class;
        $property = null === $constraint->property ? $this->context->getPropertyName() : $constraint->property;
        $targetGroups = null === $constraint->targetGroups ? $this->context->getGroup() : $constraint->targetGroups;

        $classMetadata = $this->context->getValidator()->getMetadataFor($class);

        if (!$classMetadata instanceof ClassMetadataInterface) {
            throw new UnexpectedTypeException($classMetadata, ClassMetadataInterface::class);
        }

        if (null === $property) {
            throw new InvalidArgumentException('Property must be not null');
        }

        $propertyMetadatas = $classMetadata->getPropertyMetadata($property);

        $constraints = \array_merge(...\array_map(/** @return \Symfony\Component\Validator\Constraint[] */
        function (PropertyMetadataInterface $propertyMetadata): array {
            return $propertyMetadata->getConstraints();
        },
            $propertyMetadatas
        ));

        $this->context->getValidator()->inContext($this->context)->validate($value, $constraints, $targetGroups);
    }
}
