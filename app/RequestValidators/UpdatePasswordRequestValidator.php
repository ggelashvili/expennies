<?php

declare(strict_types = 1);

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Exception\ValidationException;
use Valitron\Validator;

class UpdatePasswordRequestValidator implements RequestValidatorInterface
{
    public function validate(array $data): array
    {
        $user = $data['user'];
        $v    = new Validator($data);

        $v->rule('required', ['currentPassword', 'newPassword'])->message('Required field');
        $v->rule('lengthMin', 'newPassword', '8')->label('Password');
        $v->rule(
            fn($field, $value, $params, $fields) => password_verify($data['currentPassword'], $user->getPassword()),
            'currentPassword',
        )->message('Invalid current password');

        if (! $v->validate()) {
            throw new ValidationException($v->errors());
        }

        return $data;
    }
}
