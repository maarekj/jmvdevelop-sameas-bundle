<?php

declare(strict_types=1);

namespace JmvDevelop\SameAsBundle\Tests;

use JmvDevelop\SameAsBundle\SameAs;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

class User
{
    #[NotBlank]
    public ?string $username = null;

    #[NotBlank]
    #[Email]
    public ?string $email = null;

    #[NotBlank]
    public ?string $lastname = null;

    #[NotBlank]
    public ?string $firstname = null;

    #[NotBlank(groups: ["not_blank"])]
    #[Length(min: 10, groups: ["length", "min"])]
    #[Length(max: 5, groups: ["length", "max"])]
    public ?string $withGroup = null;
}

class Command
{
    #[SameAs(class: User::class)]
    public ?string $username = null;

    #[SameAs(class: User::class)]
    public ?string $email = null;

    #[SameAs(class: User::class, property: "lastname")]
    public ?string $nameLast = null;

    #[SameAs(class: User::class, property: "firstname")]
    public ?string $nameFirst = null;
}

class Command2
{
    #[SameAs(class: User::class, property: "withGroup")]
    #[SameAs(class: User::class, property: "withGroup", groups: ["not_blank"])]
    #[SameAs(class: User::class, property: "withGroup", groups: ["min"])]
    #[SameAs(class: User::class, property: "withGroup", groups: ["max"])]
    #[SameAs(class: User::class, property: "withGroup", groups: ["length"])]
    public ?string $withGroup1 = null;

    #[SameAs(class: User::class, property: "withGroup", targetGroups: ["not_blank"], groups: ["not_blank_group"])]
    #[SameAs(class: User::class, property: "withGroup", targetGroups: ["min"], groups: ["min_group"])]
    #[SameAs(class: User::class, property: "withGroup", targetGroups: ["max"], groups: ["max_group"])]
    #[SameAs(class: User::class, property: "withGroup", targetGroups: ["length"], groups: ["length_group"])]
    #[SameAs(class: User::class, property: "withGroup", targetGroups: ["min", "max"], groups: ["min_max_group"])]
    public ?string $withGroup2 = null;
}

/**
 * @covers \JmvDevelop\SameAsBundle\SameAsValidator
 * @covers \JmvDevelop\SameAsBundle\SameAs
 */
final class SameAsValidatorTest extends TestCase
{
    use AssertViolationTrait;

    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $builder = new ValidatorBuilder();
        $builder->enableAnnotationMapping(true);
        $this->validator = $builder->getValidator();
    }

    public function testValidate(): void
    {
        $command = new Command();

        $violations = $this->validator->validate($command);
        $this->assertViolations([
            ['username', 'This value should not be blank.'],
            ['email', 'This value should not be blank.'],
            ['nameLast', 'This value should not be blank.'],
            ['nameFirst', 'This value should not be blank.'],
        ], $violations);
    }

    /**
     * @dataProvider provideValidate_withGroup1
     */
    public function testValidateWithGroup1($value, $groups, $expectedViolations): void
    {
        $command = new Command2();
        $command->withGroup1 = $value;
        $violations = $this->validator->validate($command, null, $groups);
        $this->assertViolations($expectedViolations, $violations);
    }

    public function provideValidate_withGroup1()
    {
        return [
            [null, [], []],
            [null, ['not_blank'], [
                ['withGroup1', 'This value should not be blank.'],
            ]],
            ['abcdefgh', ['min'], [
                ['withGroup1', 'This value is too short. It should have 10 characters or more.'],
            ]],
            ['abcdefgh', ['max'], [
                ['withGroup1', 'This value is too long. It should have 5 characters or less.'],
            ]],
            ['abcdefgh', ['length'], [
                ['withGroup1', 'This value is too short. It should have 10 characters or more.'],
                ['withGroup1', 'This value is too long. It should have 5 characters or less.'],
            ]],
            ['abcdefgh', ['min', 'max'], [
                ['withGroup1', 'This value is too short. It should have 10 characters or more.'],
                ['withGroup1', 'This value is too long. It should have 5 characters or less.'],
            ]],
        ];
    }

    /**
     * @dataProvider provideValidate_withGroup2
     */
    public function testValidateWithGroup2($value, $groups, $expectedViolations): void
    {
        $command = new Command2();
        $command->withGroup2 = $value;
        $violations = $this->validator->validate($command, null, $groups);
        $this->assertViolations($expectedViolations, $violations);
    }

    public function provideValidate_withGroup2(): array
    {
        return [
            [null, [], []],
            [null, ['not_blank_group'], [
                ['withGroup2', 'This value should not be blank.'],
            ]],
            ['abcdefgh', ['min_group'], [
                ['withGroup2', 'This value is too short. It should have 10 characters or more.'],
            ]],
            ['abcdefgh', ['max_group'], [
                ['withGroup2', 'This value is too long. It should have 5 characters or less.'],
            ]],
            ['abcdefgh', ['length_group'], [
                ['withGroup2', 'This value is too short. It should have 10 characters or more.'],
                ['withGroup2', 'This value is too long. It should have 5 characters or less.'],
            ]],
            ['abcdefgh', ['min_max_group'], [
                ['withGroup2', 'This value is too short. It should have 10 characters or more.'],
                ['withGroup2', 'This value is too long. It should have 5 characters or less.'],
            ]],
            ['abcdefgh', ['min_group', 'max_group'], [
                ['withGroup2', 'This value is too short. It should have 10 characters or more.'],
                ['withGroup2', 'This value is too long. It should have 5 characters or less.'],
            ]],
        ];
    }
}
