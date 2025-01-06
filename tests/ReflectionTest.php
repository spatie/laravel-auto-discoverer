<?php


use Spatie\StructureDiscoverer\Data\DiscoveredAttribute;
use Spatie\StructureDiscoverer\Data\DiscoveredClass;
use Spatie\StructureDiscoverer\Data\DiscoveredEnum;
use Spatie\StructureDiscoverer\Data\DiscoveredInterface;
use Spatie\StructureDiscoverer\Data\DiscoveredTrait;
use Spatie\StructureDiscoverer\Discover;
use Spatie\StructureDiscoverer\Enums\DiscoveredEnumType;
use Spatie\StructureDiscoverer\Tests\Fakes\Dependers\FakeClassDepender;
use Spatie\StructureDiscoverer\Tests\Fakes\Dependers\FakeInterfaceDepender;
use Spatie\StructureDiscoverer\Tests\Fakes\FakeAttribute;
use Spatie\StructureDiscoverer\Tests\Fakes\FakeChildClass;
use Spatie\StructureDiscoverer\Tests\Fakes\FakeChildInterface;
use Spatie\StructureDiscoverer\Tests\Fakes\FakeEnum;
use Spatie\StructureDiscoverer\Tests\Fakes\FakeIntEnum;
use Spatie\StructureDiscoverer\Tests\Fakes\FakeRootClass;
use Spatie\StructureDiscoverer\Tests\Fakes\FakeRootInterface;
use Spatie\StructureDiscoverer\Tests\Fakes\FakeStringEnum;
use Spatie\StructureDiscoverer\Tests\Fakes\FakeSubChildClass;
use Spatie\StructureDiscoverer\Tests\Fakes\FakeSubChildInterface;
use Spatie\StructureDiscoverer\Tests\Fakes\FakeTrait;
use Spatie\StructureDiscoverer\Tests\Fakes\FakeWithAnonymousClass;
use Spatie\StructureDiscoverer\Tests\Fakes\FakeWithMultipleClasses;
use Spatie\StructureDiscoverer\Tests\Fakes\Nested\FakeNestedClass;
use Spatie\StructureDiscoverer\Tests\Fakes\Nested\FakeNestedInterface;
use Spatie\StructureDiscoverer\Tests\Fakes\OtherNested\FakeOtherNestedClass;

it('can reflect a class', function () {
    $reflected = DiscoveredClass::fromReflection(new ReflectionClass(FakeChildClass::class));

    expect($reflected)->toEqual(
        new DiscoveredClass(
            name: 'FakeChildClass',
            file: __DIR__.'/Fakes/FakeChildClass.php',
            namespace: 'Spatie\StructureDiscoverer\Tests\Fakes',
            isFinal: false,
            isAbstract: false,
            isReadonly: false,
            extends: FakeRootClass::class,
            implements: [
                FakeRootInterface::class,
                FakeChildInterface::class,
                FakeNestedInterface::class,
            ],
            attributes: [new DiscoveredAttribute(FakeAttribute::class)],
            extendsChain: [FakeRootClass::class],
            implementsChain: [
                FakeRootInterface::class,
                FakeChildInterface::class,
                FakeNestedInterface::class,
            ]
        )
    );
});

it('can reflect an interface', function () {
    $reflected = DiscoveredInterface::fromReflection(new ReflectionClass(FakeSubChildInterface::class));

    expect($reflected)->toEqual(
        new DiscoveredInterface(
            name: 'FakeSubChildInterface',
            file: __DIR__.'/Fakes/FakeSubChildInterface.php',
            namespace: 'Spatie\StructureDiscoverer\Tests\Fakes',
            extends: [FakeChildInterface::class, FakeNestedInterface::class, FakeRootInterface::class],
            attributes: [],
            extendsChain: [FakeChildInterface::class, FakeNestedInterface::class, FakeRootInterface::class],
        )
    );
});

it('can reflect an enum', function () {
    $reflected = DiscoveredEnum::fromReflection(new ReflectionEnum(FakeEnum::class));

    expect($reflected)->toEqual(
        new DiscoveredEnum(
            name: 'FakeEnum',
            file: __DIR__.'/Fakes/FakeEnum.php',
            namespace: 'Spatie\StructureDiscoverer\Tests\Fakes',
            type: DiscoveredEnumType::Unit,
            implements: [FakeChildInterface::class, UnitEnum::class, FakeNestedInterface::class, FakeRootInterface::class],
            attributes: [],
            implementsChain: [FakeChildInterface::class, UnitEnum::class, FakeNestedInterface::class, FakeRootInterface::class],
        )
    );
});

it('can reflect a trait', function () {
    $reflected = DiscoveredTrait::fromReflection(new ReflectionClass(FakeTrait::class));

    expect($reflected)->toEqual(
        new DiscoveredTrait(
            name: 'FakeTrait',
            file: __DIR__.'/Fakes/FakeTrait.php',
            namespace: 'Spatie\StructureDiscoverer\Tests\Fakes',
        )
    );
});

it('can discover using reflection', function () {
    $found = Discover::in(__DIR__ . '/Fakes')
        ->useReflection(__DIR__ . '/Fakes', 'Spatie\StructureDiscoverer\Tests\Fakes')
        ->get();

    expect($found)->toEqualCanonicalizing([
        FakeAttribute::class,
        FakeChildClass::class,
        FakeEnum::class,
        FakeIntEnum::class,
        FakeChildInterface::class,
        FakeRootClass::class,
        FakeStringEnum::class,
        FakeRootInterface::class,
        FakeTrait::class,
        FakeNestedClass::class,
        FakeNestedInterface::class,
        FakeOtherNestedClass::class,
        FakeSubChildInterface::class,
        FakeSubChildClass::class,
        FakeWithMultipleClasses::class,
        FakeClassDepender::class,
        FakeInterfaceDepender::class,
        FakeWithAnonymousClass::class,
    ]);
});
