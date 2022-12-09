<?php

namespace Uneca\Chimera\Traits;

use Illuminate\Support\Facades\Validator;

trait InteractiveCommand
{
    protected function askValid($question, $field, $rules)
    {
        $value = $field == 'password' ? $this->secret($question) : $this->ask($question);
        if($message = $this->validateInput($rules, $field, $value)) {
            $this->error($message);
            return $this->askValid($question, $field, $rules);
        }
        return $value;
    }

    protected function validateInput($rules, $fieldName, $value)
    {
        $validator = Validator::make([
            $fieldName => $value
        ], [
            $fieldName => $rules
        ]);
        return $validator->fails()
            ? $validator->errors()->first($fieldName)
            : null;
    }
}
