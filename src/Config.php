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

namespace PhpCsFixer;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\RuleSet\RuleSetDescriptionInterface;
use PhpCsFixer\RuleSet\RuleSets;
use PhpCsFixer\Runner\Parallel\ParallelConfig;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Katsuhiro Ogawa <ko.fivestar@gmail.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
class Config implements ConfigInterface, ParallelAwareConfigInterface
{
    /**
     * @var non-empty-string
     */
    private string $cacheFile = '.php-cs-fixer.cache';

    /**
     * @var list<FixerInterface>
     */
    private array $customFixers = [];

    /**
     * @var null|iterable<\SplFileInfo>
     */
    private ?iterable $finder = null;

    private string $format = 'txt';

    private bool $hideProgress = false;

    /**
     * @var non-empty-string
     */
    private string $indent = '    ';

    private bool $isRiskyAllowed = false;

    /**
     * @var non-empty-string
     */
    private string $lineEnding = "\n";

    private string $name;

    private ParallelConfig $parallelConfig;

    /**
     * @var null|string
     */
    private $phpExecutable;

    /**
     * @TODO: 4.0 - update to @PER
     *
     * @var array<string, array<string, mixed>|bool>
     */
    private array $rules;

    private bool $usingCache = true;

    public function __construct(string $name = 'default')
    {
        // @TODO 4.0 cleanup
        if (Utils::isFutureModeEnabled()) {
            $this->name = $name.' (future mode)';
            $this->rules = ['@PER-CS' => true];
        } else {
            $this->name = $name;
            $this->rules = ['@PSR12' => true];
        }

        // @TODO 4.0 cleanup
        if (Utils::isFutureModeEnabled() || filter_var(getenv('PHP_CS_FIXER_PARALLEL'), FILTER_VALIDATE_BOOL)) {
            $this->parallelConfig = ParallelConfigFactory::detect();
        } else {
            $this->parallelConfig = ParallelConfigFactory::sequential();
        }
    }

    /**
     * @return non-empty-string
     */
    public function getCacheFile(): string
    {
        return $this->cacheFile;
    }

    public function getCustomFixers(): array
    {
        return $this->customFixers;
    }

    /**
     * @return Finder
     */
    public function getFinder(): iterable
    {
        $this->finder ??= new Finder();

        return $this->finder;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getHideProgress(): bool
    {
        return $this->hideProgress;
    }

    public function getIndent(): string
    {
        return $this->indent;
    }

    public function getLineEnding(): string
    {
        return $this->lineEnding;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParallelConfig(): ParallelConfig
    {
        return $this->parallelConfig;
    }

    public function getPhpExecutable(): ?string
    {
        return $this->phpExecutable;
    }

    public function getRiskyAllowed(): bool
    {
        return $this->isRiskyAllowed;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function getUsingCache(): bool
    {
        return $this->usingCache;
    }

    public function registerCustomFixers(iterable $fixers): ConfigInterface
    {
        foreach ($fixers as $fixer) {
            $this->addCustomFixer($fixer);
        }

        return $this;
    }

    /**
     * Registers custom rule sets to be used the same way as built-in rule sets
     *
     * `$ruleSets` must follow `'@RuleName' => RuleSetDescriptionInterface::class` convention.
     *
     * @param array<string, class-string<RuleSetDescriptionInterface>> $ruleSets
     *
     * @todo Introduce it in ConfigInterface in 4.0
     */
    public function registerCustomRuleSets(array $ruleSets): ConfigInterface
    {
        foreach ($ruleSets as $name => $class) {
            RuleSets::registerRuleSet($name, $class);
        }

        return $this;
    }

    /**
     * @param non-empty-string $cacheFile
     */
    public function setCacheFile(string $cacheFile): ConfigInterface
    {
        $this->cacheFile = $cacheFile;

        return $this;
    }

    public function setFinder(iterable $finder): ConfigInterface
    {
        $this->finder = $finder;

        return $this;
    }

    public function setFormat(string $format): ConfigInterface
    {
        $this->format = $format;

        return $this;
    }

    public function setHideProgress(bool $hideProgress): ConfigInterface
    {
        $this->hideProgress = $hideProgress;

        return $this;
    }

    /**
     * @param non-empty-string $indent
     */
    public function setIndent(string $indent): ConfigInterface
    {
        $this->indent = $indent;

        return $this;
    }

    /**
     * @param non-empty-string $lineEnding
     */
    public function setLineEnding(string $lineEnding): ConfigInterface
    {
        $this->lineEnding = $lineEnding;

        return $this;
    }

    public function setParallelConfig(ParallelConfig $config): ConfigInterface
    {
        $this->parallelConfig = $config;

        return $this;
    }

    public function setPhpExecutable(?string $phpExecutable): ConfigInterface
    {
        $this->phpExecutable = $phpExecutable;

        return $this;
    }

    public function setRiskyAllowed(bool $isRiskyAllowed): ConfigInterface
    {
        $this->isRiskyAllowed = $isRiskyAllowed;

        return $this;
    }

    public function setRules(array $rules): ConfigInterface
    {
        $this->rules = $rules;

        return $this;
    }

    public function setUsingCache(bool $usingCache): ConfigInterface
    {
        $this->usingCache = $usingCache;

        return $this;
    }

    private function addCustomFixer(FixerInterface $fixer): void
    {
        $this->customFixers[] = $fixer;
    }
}
