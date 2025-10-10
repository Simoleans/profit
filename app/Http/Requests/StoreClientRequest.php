<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Cambiar a true para permitir el acceso
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cli_des' => 'required|string|max:60',
            'rif' => 'required|unique:sqlsrv.clientes_temp,rif',
            'direc1' => 'required|string|max:120',
            'telefonos' => 'required|string|max:30',
            'respons' => 'required|string|max:60',
            'email' => 'required|email|max:120',
            'ciudad' => 'required|string|max:30',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,webp|max:10240', // Max 10MB
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cli_des.required' => 'El nombre o razón social es obligatorio.',
            'cli_des.max' => 'El nombre no puede exceder 60 caracteres.',
            'rif.required' => 'El RIF/Cédula es obligatorio.',
            'rif.unique' => 'Este RIF/Cédula ya está registrado en el sistema.',
            'direc1.required' => 'La dirección es obligatoria.',
            'direc1.max' => 'La dirección no puede exceder 120 caracteres.',
            'telefonos.required' => 'Los teléfonos son obligatorios.',
            'telefonos.max' => 'Los teléfonos no pueden exceder 30 caracteres.',
            'respons.required' => 'El responsable es obligatorio.',
            'respons.max' => 'El responsable no puede exceder 60 caracteres.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.max' => 'El email no puede exceder 120 caracteres.',
            'ciudad.required' => 'La ciudad es obligatoria.',
            'ciudad.max' => 'La ciudad no puede exceder 30 caracteres.',
            'document.file' => 'El documento debe ser un archivo válido.',
            'document.mimes' => 'El documento debe ser un archivo PDF, Word o imagen (JPG, PNG, WEBP).',
            'document.max' => 'El documento no puede exceder 10MB.',
        ];
    }
}
