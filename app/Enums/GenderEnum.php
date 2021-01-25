<?php

namespace App\Enums;

use PhpEnum\PhpEnum;

/**
 * @method static self MALE
 * @method static self FEMALE
 *
 * @method bool idEquals($id)
 * @method bool nameEquals($name)
 * @method bool valueEquals($value)
 *
 * @method static int containsId($id)
 * @method static int containsName($name)
 * @method static int containsValue($value)
 *
 * @method static self ofId($id)
 * @method static self ofName($name)
 * @method static self ofValue($value)
 */
class GenderEnum extends PhpEnum
{
    private const MALE = [1, 'male', '男'];
    private const FEMALE = [2, 'female', '女'];

    private int $id;
    private string $name;
    private string $value;


    protected function construct(int $id, string $name, string $value)
    {
        $this->id = $id;
        $this->name = $name;
        $this->value = $value;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
