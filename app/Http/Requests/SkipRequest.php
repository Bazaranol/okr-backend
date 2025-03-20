<?php

namespace App\Http\Requests;

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
            'start_date' => 'required|date|date_format:d.m.Y',
            'end_date' => 'required|date|date_format:d.m.Y|after:start_date',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimetypes:text/plain,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document|max:2048',
            'reason' => 'nullable|string',
        ];
    }
    public function messages(): array
    {
        return [
            'start_date.required' => 'Поле "Дата начала" обязательно для заполнения.',
            'start_date.date' => 'Поле "Дата начала" должно быть датой.',
            'start_date.date_format' => 'Поле "Дата начала" должно быть в формате дд.мм.гггг.',
            'end_date.required' => 'Поле "Дата окончания" обязательно для заполнения.',
            'end_date.date' => 'Поле "Дата окончания" должно быть датой.',
            'end_date.date_format' => 'Поле "Дата окончания" должно быть в формате дд.мм.гггг.',
            'end_date.after' => 'Поле "Дата окончания" должно быть после даты начала.',
            'document.array' => 'Поле "Документы" должно быть массивом файлов.',
            'document.*.file' => 'Поле "Документ" должно быть файлом.',
            'document.*.mimetypes' => 'Файл должен быть одного из типов: text/plain, PDF, DOC, DOCX.',
            'document.*.max' => 'Файл не должен превышать 2048 КБ.',
            'reason.string' => 'Причина должна быть строковым значением.'
        ];
    }

}
