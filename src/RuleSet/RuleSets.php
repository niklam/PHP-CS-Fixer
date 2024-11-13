<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\RuleSet;

use PhpCsFixer\RuleSetNameValidator;
use Symfony\Component\Finder\Finder;

/**
 * Set of rule sets to be used by fixer.
 *
 * @internal
 */
final class RuleSets
{
    /**
     * @var array<string, RuleSetDescriptionInterface>
     */
    private static ?array $builtInSetDefinitions = null;

    /**
     * @var array<string, RuleSetDescriptionInterface>
     */
    private static array $customRuleSetDefinitions = [];

    /**
     * @return array<string, RuleSetDescriptionInterface>
     */
    public static function getSetDefinitions(): array
    {
        $allRuleSets = array_merge(
            self::getBuiltInSetDefinitions(),
            self::$customRuleSetDefinitions
        );

        uksort($allRuleSets, static fn (string $x, string $y): int => strnatcmp($x, $y));

        return $allRuleSets;
    }

    /**
     * @return array<string, RuleSetDescriptionInterface>
     */
    public static function getBuiltInSetDefinitions(): array
    {
        if (null === self::$builtInSetDefinitions) {
            self::$builtInSetDefinitions = [];

            foreach (Finder::create()->files()->in(__DIR__.'/Sets') as $file) {
                /** @var class-string<RuleSetDescriptionInterface> $class */
                $class = 'PhpCsFixer\RuleSet\Sets\\'.$file->getBasename('.php');
                $set = new $class();

                if (!RuleSetNameValidator::isValid($set->getName(), false)) {
                    throw new \InvalidArgumentException(\sprintf('Rule set name invalid: %s', $set->getName()));
                }

                self::$builtInSetDefinitions[$set->getName()] = $set;
            }

            uksort(self::$builtInSetDefinitions, static fn (string $x, string $y): int => strnatcmp($x, $y));
        }

        return self::$builtInSetDefinitions;
    }

    /**
     * @return list<string>
     */
    public static function getSetDefinitionNames(): array
    {
        return array_keys(self::getSetDefinitions());
    }

    public static function getSetDefinition(string $name): RuleSetDescriptionInterface
    {
        $definitions = self::getSetDefinitions();

        if (!isset($definitions[$name])) {
            throw new \InvalidArgumentException(\sprintf('Set "%s" does not exist.', $name));
        }

        return $definitions[$name];
    }

    /**
     * @param class-string<RuleSetDescriptionInterface> $class
     */
    public static function registerCustomRuleSet(string $class): void
    {
        if (!class_exists($class)
            || !\in_array(RuleSetDescriptionInterface::class, class_implements($class), true)
        ) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Class "%s" must be an instance of "%s".',
                    $class,
                    RuleSetDescriptionInterface::class
                )
            );
        }

        $ruleset = new $class();
        $name = $ruleset->getName();

        if (!RuleSetNameValidator::isValid($name, true)) {
            throw new \InvalidArgumentException('RuleSet name must begin with "@" and a letter (a-z, A-Z), and can contain only letters (a-z, A-Z), numbers, underscores, slashes, colons, dots and hyphens.');
        }

        if (!class_exists($class, true)) {
            throw new \InvalidArgumentException(\sprintf('Class "%s" does not exist.', $class));
        }

        if (\array_key_exists($name, self::getSetDefinitions())) {
            throw new \InvalidArgumentException(\sprintf('Set "%s" is already defined.', $name));
        }

        self::$customRuleSetDefinitions[$name] = $ruleset;
    }
}
