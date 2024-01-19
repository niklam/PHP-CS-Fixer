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

namespace PhpCsFixer\Tests\Fixtures\ExternalRuleSet;

/**
 * Sample external RuleSet.
 *
 * This class is not implementing the required interface `\PhpCsFixer\RuleSet\RuleSetDescriptionInterface`,
 * so it will not be a valid class to be registered as a RuleSet.
 */
class SampleRulesBad
{
    public function getName(): string
    {
        return '@RulesBad';
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function getDescription(): string
    {
        return 'Description';
    }

    /**
     * @return array<string, array<string, mixed>|bool>
     */
    public function getRules(): array
    {
        return [
            'align_multiline_comment' => false,
        ];
    }
}
