<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrganizacaoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            "nome"              => ['required', 'string', 'min:10', 'max:255', 'regex:/^[A-Za-záâãéêíóôõúçÁÂÃÉÊÍÓÔÕÚÇ\s]+$/'],
            "cnpj"              => ['required', 'cnpj', 'unique:organizacoes_controle_social'],
            "data_fundacao"     => ['required', 'date'],
            "email"             => ['required', 'string', 'email', 'max:255', 'unique:users'],
            "telefone"          => ['required', "celular_com_ddd"],
            "rua"               => ['required', 'string', 'min:3', "max:50"],
            "numero"            => ['required', 'numeric'],
            "cep"               => ['required', 'numeric', 'min:0', 'digits:8'],
            "bairro_id"         => ['required', 'numeric'],
            "associacao_id"     => ['required', 'numeric']
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'cnpj'       => preg_replace('/[^0-9]/', '', $this->cnpj),
            'cep'        => preg_replace('/[^0-9]/', '', $this->cep)
        ]);
    }
}
