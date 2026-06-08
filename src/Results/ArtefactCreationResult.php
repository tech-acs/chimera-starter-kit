<?php

namespace Uneca\Chimera\Results;

use Illuminate\Database\Eloquent\Model;

readonly class ArtefactCreationResult
{
    public function __construct(
        public bool $success,
        public ?Model $artefact = null,
        public ?string $filePath = null,
        public ?string $errorMessage = null,
        public ?int $exitCode = null,
    ) {}

    public static function success(Model $artefact, string $filePath): self
    {
        return new self(
            success: true,
            artefact: $artefact,
            filePath: $filePath,
        );
    }

    public static function failed(string $message, ?int $exitCode = null): self
    {
        return new self(
            success: false,
            errorMessage: $message,
            exitCode: $exitCode,
        );
    }

    public function toArray(): array
    {
        if ($this->success) {
            return [
                'success' => true,
                'id' => $this->artefact->id,
                'name' => $this->artefact->name,
                'file_path' => $this->filePath,
            ];
        }

        return [
            'success' => false,
            'error' => $this->errorMessage,
            'exit_code' => $this->exitCode,
        ];
    }
}
