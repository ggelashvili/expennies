<?php

declare(strict_types = 1);

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Exception\ValidationException;
use App\Services\CategoryService;
use Valitron\Validator;

class TransactionRequestValidator implements RequestValidatorInterface
{
    public function __construct(protected readonly CategoryService $categoryService)
    {
    }

    public function validate(array $data): array
    {
        $v = new Validator($data);

        $v->rule('required', ['description', 'amount', 'date', 'category']);
        $v->rule('lengthMax', 'description', 255);
        $v->rule('dateFormat', 'dateFormat', 'm/d/Y g:i A');
        $v->rule('numeric', 'amount');
        $v->rule('integer', 'category');
        $v->rule(
            function($field, $value, $params, $fields) use (&$data) {
                $id = (int) $value;

                if (! $id) {
                    return false;
                }

                $category = $this->categoryService->getById($id);

                if ($category) {
                    $data['category'] = $category;

                    return true;
                }

                return false;
            },
            'category'
        )->message('Category not found');

        if (! $v->validate()) {
            throw new ValidationException($v->errors());
        }

        return $data;
    }
}
