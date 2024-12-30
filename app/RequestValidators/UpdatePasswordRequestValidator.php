<?php

declare(strict_types=1);

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Exception\ValidationException;
use Valitron\Validator;

class UpdatePasswordRequestValidator implements RequestValidatorInterface
{
    public function validate(array $data): array
    {
        $v = new Validator($data);
        $user = $data['user'];

        $v->rule('required', ['currentPassword', 'newPassword'])->message('Required field');
        $v->rule('lengthMin', 'newPassword', 8);
        $v->rule('different', 'currentPassword', 'newPassword')->message('New password must differs from current password');
        $v->rule(
            fn ($field, $value, $params, $fields) => password_verify($data['currentPassword'], $user->getPassword()),
            'currentPassword',
        )->message('Incorrect current password');

        if (! $v->validate()) {
            throw new ValidationException($v->errors());
        }

        return $data;
    }
}
