<?php

declare(strict_types=1);

namespace TomasVotruba\Ctor\Tests\Rules\NewOverSettersRule\Fixture;

use TomasVotruba\Ctor\Tests\Rules\NewOverSettersRule\Source\SomeTimerObject;

final class SkipNoArgSetters
{
    public function first()
    {
        $someTimerObject = new SomeTimerObject();
        $someTimerObject->setStartTime();
        $someTimerObject->setEndTime();
    }

    public function second()
    {
        $someTimerObject = new SomeTimerObject();
        $someTimerObject->setStartTime();
        $someTimerObject->setEndTime();
    }
}
