<?php

namespace Stepanenko3\LaravelApiSkeleton\Instructions;

use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;

trait Instructionable
{
    /**
     * The instructions that should be linked.
     */
    public function instructions(
        Request $request,
    ): array {
        return [];
    }

    /**
     * Get the resource's instructions.
     */
    public function getInstructions(
        Request $request,
    ): array {
        return $this->instructions(
            request: $request,
        );
    }

    /**
     * Check if a specific instruction exists.
     */
    public function instructionExists(
        Request $request,
        string $instructionKey,
    ): bool {
        return collect(
            value: $this->getInstructions(
                request: $request,
            ),
        )
            ->contains(
                fn (Instruction $instruction) => $instruction->uriKey() === $instructionKey,
            );
    }

    /**
     * Retrieve a specific instruction by its key.
     */
    public function instruction(
        Request $request,
        string $instructionKey,
    ): Instruction | null {
        $instruction = collect(
            value: $this->getInstructions(
                request: $request,
            ),
        )
            ->first(
                fn (Instruction $instruction) => $instruction->uriKey() === $instructionKey,
            );

        if (null !== $instruction) {
            $instruction
                ->resource($this);
        }

        return $instruction;
    }
}
