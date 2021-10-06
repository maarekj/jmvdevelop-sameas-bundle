<?php

declare(strict_types=1);

namespace JmvDevelop\SameAsBundle;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for the SameAs validator.
 *
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class SameAs extends Constraint
{
    public string $class;

    public ?string $property = null;

    /** @psalm-var list<string> */
    public ?array $targetGroups = null;

    /**
     * @param string[]|null $groups
     * @param string[]|null $targetGroups
     */
    public function __construct(string $class, ?array $options = null, ?string $property = null, ?array $targetGroups = null, ?array $groups = null, ?array $payload = null)
    {
        parent::__construct($options ?? [], $groups, $payload);

        $this->class = $class;
        $this->property = $property;
        $this->targetGroups = $targetGroups === null ? null : array_values($targetGroups);
    }

    /** {@inheritdoc} */
    public function getDefaultOption()
    {
        return 'class';
    }

    /** {@inheritdoc} */
    public function getRequiredOptions()
    {
        return [];
    }
}
