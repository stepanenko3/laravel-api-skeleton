<?php

namespace Stepanenko3\LaravelApiSkeleton\Services\Performance;

use Stepanenko3\LaravelApiSkeleton\Contracts\PerformanceTrackerContract;
use Sentry\State\HubInterface;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\Span;
use Sentry\Tracing\TransactionContext;
use Sentry\Tracing\SpanContext;

class SentryTracker implements PerformanceTrackerContract
{
    /** @var Transaction[] */
    protected array $activeTransactions = [];

    /** @var Span[] */
    protected array $activeSpans = [];

    protected $sentry;

    public function __construct(
        HubInterface $sentry,
    ) {
        $this->sentry = $sentry;
    }

    public function begin(
        string $name,
        array $params = [],
    ): void {
        $transactionContext = new TransactionContext();

        $transactionContext->setName(
            name: $name,
        );

        if (isset($params['op'])) {
            $transactionContext->setOp(
                op: $params['op'],
            );
        }

        $transaction = $this->sentry->startTransaction(
            context: $transactionContext,
        );

        $spanContext = new SpanContext();
        $spanContext->setOp(
            op: $params['op'] ?? 'task',
        );
        $spanContext->setDescription(
            description: $params['description'] ?? $name,
        );

        $span = $transaction->startChild(
            context: $spanContext,
        );

        $this->activeTransactions[$name] = $transaction;
        $this->activeSpans[$name] = $span;
    }

    public function end(
        string $name,
    ): void {
        if (isset($this->activeSpans[$name])) {
            $this->activeSpans[$name]->finish();

            unset($this->activeSpans[$name]);
        }

        if (isset($this->activeTransactions[$name])) {
            $this->activeTransactions[$name]->finish();

            unset($this->activeTransactions[$name]);
        }
    }
}
