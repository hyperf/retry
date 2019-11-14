<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Retry\Policy;

use Hyperf\Retry\RetryBudgetInterface;

class BudgetRetryPolicy extends BaseRetryPolicy implements RetryPolicyInterface
{
    private $budget;

    public function __construct(RetryBudgetInterface $retryBudget)
    {
        $this->budget = $retryBudget;
    }

    public function canRetry(array &$retryContext): bool
    {
        if ($this->isFirstTry($retryContext)) {
            return true;
        }
        if ($this->budget->consume(true)) {
            return true;
        }
        $retryContext['retry_exhausted'] = true;
        return false;
    }

    public function beforeRetry(array &$retryContext): void
    {
        $this->budget->consume();
    }

    public function start(array $parentRetryContext = []): array
    {
        $this->budget->produce();
        return $parentRetryContext;
    }
}
