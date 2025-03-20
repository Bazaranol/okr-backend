<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class SkipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $formats = ['d.m.Y', 'Y-m-d'];
                    foreach ($formats as $format) {
                        if (Carbon::createFromFormat($format, $value) !== false) {
                            return;
                        }
                    }
                    $fail("Wrong format of date. Use d.m.Y or Y-m-d.");
                },
            ],
            'end_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $formats = ['d.m.Y', 'Y-m-d'];
                    foreach ($formats as $format) {
                        if (Carbon::createFromFormat($format, $value) !== false) {
                            return;
                        }
                    }
                    $fail("Wrong format of date. Use d.m.Y or Y-m-d.");
                },
                'after:start_date',
            ],
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimetypes:text/plain,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png|max:2048',
            'reason' => 'nullable|string',
        ];
    }
    public function messages(): array
    {
        return [
            'start_date.required' => 'Поле "Дата начала" обязательно для заполнения.',
            'start_date.date' => 'Поле "Дата начала" должно быть датой.',
            'end_date.required' => 'Поле "Дата окончания" обязательно для заполнения.',
            'end_date.date' => 'Поле "Дата окончания" должно быть датой.',
            'end_date.after' => 'Поле "Дата окончания" должно быть после даты начала.',
            'documents.array' => 'Поле "Документы" должно быть массивом файлов.',
            'documents.*.file' => 'Поле "Документ" должно быть файлом.',
            'documents.*.mimetypes' => 'Файл должен быть одного из типов: text/plain, PDF, DOC, DOCX, JPEG, PNG.',
            'documents.*.max' => 'Файл не должен превышать 2048 КБ.',
            'reason.string' => 'Причина должна быть строковым значением.',
        ];
    }

}
